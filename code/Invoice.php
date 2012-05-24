<?php

/**
 * Invoice
 */
class Invoice extends DataObject{

	public static $singular_name = "invoice";
	public static $plural_name = "invoices";

	static $db = array(
		'Name' => 'Varchar(255)',
		'Address' => 'Text',

		'Email' => 'Varchar',
		'Sent' => 'Boolean',	//TODO: change to date?

		'DueDate' => 'Date',
		'Status' => 'Enum("paid,unpaid,cancelled","unpaid")',

		//Hacky solution to store parent of unknown classname
		//TODO: change to same as DataObject ClassName Enum
		'ParentID' => 'Int',
		'ParentClassName'  => "Varchar",

		'NoticeLastSent' => 'Date'
	);

	static $has_one = array(
		'InvoiceType' => 'InvoiceType',
		'PDFVersion' => 'File'
		//'Parent' => 'DataObject' //Registration, organisation, member, order etc
	);

	static $has_many = array(
		'InvoiceItems' => 'InvoiceItem'
	);

	static $many_many = array(
		'Payments' => 'Payment' //TODO: shouldn't this be has_many??
	);

	static $casting = array(
		'InvoiceNumber' => 'Varchar',
		'Total' => 'Currency',
		'SubTotal' => 'Currency',
		'Tax' => 'Currency',
		'TotalOutstanding' => 'Currency',
		'TimeOverdue' => 'Varchar'
	);

	static $summary_fields = array(
		'InvoiceNumber' => 'Invoice Number',
		'Name' => 'Name',
		'Created' => 'Date', //TODO: find out how to style better
		'DueDate' => 'Due Date',
		'TimeOverdue' => 'Overdue',
		'InvoiceType.Name' => 'Type',
		'Total' => 'Total',
		'TotalOutstanding' => 'Outstanding',
		'IsSent' => 'Sent?',
		'Status' => 'Status'
	);

	static $searchable_fields = array(
  	   "ID" => array("field" => "NumericField","title" => "Invoice Number"),
  		"Sent" => array(),
  		"Status" => array(),
  		"InvoiceTypeID" => array("title" => "Invoice Type")
	);

	static $defaults = array(
		'Status' => 'unpaid'
	);

	public static $default_sort = 'Created DESC';

	protected static $duedayofnextmonth = null;
	protected static $duedays = null;

	static $modeladmin_actions = array(
			'sendemail' => 'Send email',
			'createpdf' => 'Generate PDF version',
			'statuspaid' => 'Set status to: paid',
			'statuscancelled' => 'Set status to: cancelled'
	);

	function set_due_day_of_month($day = 20){
		self::$duedayofnextmonth = $day;
	}

	function set_due_days($days = 14){
		self::$duedays = $days;
	}

	function getCMSFields(){
		$fields = parent::getCMSFields();

		$adminurl = "admin/invoices/Invoice/$this->ID";

		if($this->ID){
			$fields->addFieldToTab('Root.Main',new ReadonlyField('Total',"Total",$this->Total),'Address');
			if($this->getTotal(false))
				$fields->addFieldToTab('Root.Main',new ReadonlyField('TotalOutstanding',"Total Outstanding",$this->dbObject('TotalOutstanding')->Nice()),'Address');
		}

		$fields->addFieldToTab('Root.Main',$datefield = new PopupDateTimeField('Created',"Invoice Date"),'Address');
		$fields->addFieldToTab('Root.Main',$duedatefield = new CalendarDateField('DueDate',"Due Date"),'Address');

		if($this->ID){
			$fields->addFieldToTab('Root.Main', new LiteralField('InvoiceNumber', '<h3>Invoice #: '.$this->InvoiceNumber().'</h3>'), 'Name');
			$fields->addFieldToTab('Root.Tools',new LiteralField('viewlink',"<a href=\"$adminurl/viewinvoice\" target=\"new\">View invoice</a><br/>"));
			$fields->addFieldToTab('Root.Tools',new LiteralField('generatepdflink',"<a href=\"$adminurl/generateinvoicepdf\" target=\"new\">Generate PDF</a><br/>"));
		}

		if($this->Email){
			$sentmessage = ($this->Sent) ? "Re-send the invoice via email" : "Email this invoice";
			$fields->addFieldToTab('Root.Tools',new LiteralField('sendemaillink',
				"<a href=\"$adminurl/emailinvoice\" target=\"new\">$sentmessage</a>"
			));

			$link = "mailto:$this->Email?Subject=".$this->getEmailSubject();
			$link .= "&Body=".$this->getEmailContent(true);
			$fields->addFieldToTab('Root.Tools',new LiteralField('composeemail',
				"<a href=\"$link\">Compose email</a>"
			));
		}

		$maintab = $fields->findOrMakeTab('Root.Main');
		$maintab->removeByName('ParentID');
		$maintab->removeByName('ParentClassName');

		return $fields;
	}

	/**
	 * Set due date, if specificed by configuration.
	 */
	function populateDefaults(){
		if(self::$duedayofnextmonth){
			$nextmonth = strtotime("+1 month");
			$day = self::$duedayofnextmonth - 1;
			$this->DueDate = date('Y-m-d', strtotime(date('Y-m',$nextmonth)."+$day day"));
		}elseif(self::$duedays){
			$this->DueDate = date('Y-m-d', strtotime("+".self::$duedays." day"));
		}
	}

	/**
	 * Add an item to this invoice.
	 *
	 * Note: invoice must have id first for this to work
	 */
	function addItem(string $description, float $cost, $quantity = 1){
		if($this->ID){
			$item = new InvoiceItem();
			$item->Description = $description;
			$item->Cost = $cost;
			$item->Quantity = $quantity;
			$item->write();
			$this->InvoiceItems()->add($item);
		}else{
			//is this good practice, or should I just write the object to db if no id?
			user_error("Invoice must have an ID before items can be added. Call ->write() first.",E_USER_ERROR);
		}
	}

	/**
	 * Subtotal before tax
	 */
	function SubTotal(){
		$subtotal = 0;
		if($items = $this->InvoiceItems()){
			foreach($items as $item){
				$subtotal += $item->TotalCost();
			}
		}
		return $subtotal;
	}

	/**
	 * Work out tax amount.
	 */
	function Tax($subtotal = null){
		if(!$subtotal && $this->InvoiceTypeID && $this->InvoiceType()->TaxRate)
			$subtotal = $this->SubTotal();
		if($this->InvoiceTypeID && $rate = $this->InvoiceType()->TaxRate)
			return $subtotal * $rate;
		return 0;
	}

	/**
	 * Total with tax included
	 */
	function getTotal($nice = true){
		$total = 0;
		$total += $this->SubTotal();
		$total += $this->Tax($total);//add tax
		if($nice)
			return DBField::create('Currency',$total)->Nice();
		return $total;
	}

	/**
	 * Total outstanding based on sum of successful payments.
	 */
	function getTotalOutstanding(){
		$totalpaid = 0;
		if($this->Status != 'unpaid') return (float)0;
		if($payments = $this->Payments()){
			foreach($payments as $payment){
				if($payment->Status == 'Success')
					$totalpaid += $payment->Amount->Amount;
			}
		}
		return (float)($this->getTotal(false) - $totalpaid);
	}

	/**
	 * Invoice number with prefix
	 */
	function InvoiceNumber(){
		$previx = ($this->InvoiceTypeID && $this->InvoiceType()->Prefix) ? $this->InvoiceType()->Prefix."-" : "";
		return $previx.$this->ID;
	}

	function IsSent(){return ($this->Sent) ? "yes" : "no";}

	function IsPaid(){
		if($this->Status == 'paid')
			return true;
		//TODO: check payments if they've been paid
		return false;
	}

	/**
	 * Generate a PDF version of this invoice.
	 */
	function generatePDFInvoice($sstemplate = 'Invoice'){
		//find or make $filelocation
		$pdf = new PDFGenerator(); //requires pdfgenerator
		$data = $this->renderWith( array($sstemplate)); // insert populated template
		$filename = preg_replace("/[^a-zA-Z0-9]/", "",strtolower($this->Name)).'_invoice'.$this->ID;//<InvoiceType>-Invoice
		$file = $pdf->sendToFile($data,$filename,'Invoices'); //save to assets
		$this->PDFVersionID = $file;
		$this->write();
	}

	/**
	 * Send this invoice via email.
	 * @param sendpdf - choose invoice format (true for pdf, false for html)
	 * @param from
	 * @param to
	 * @param subject
	 * @param body
	 * @param cc
	 * @param bcc
	 *
	 */
	function sendEmail($sendpdf = true, $from = null, $to = null, $sub = null, $bdy = null, $cc = null, $bcc = null){ //TODO: finish implementing these
		if(!$this->Email) return false; //No email found: //TODO: perhaps send error to webmaster??

		$body = $this->getEmailContent();
		$subject = $this->getEmailSubject();
		$email = new Email(Email::getAdminEmail(),$this->Email,$subject,$body); //TODO: add bounce handler url

		$pdffile = $this->PDFVersion();
		if($sendpdf && $pdffile && is_file($pdffile->getFullPath())){
			$email->attachFile($pdffile->FileName,$pdffile->Title,"application/pdf");
		}else{
			$email->setBody($this->renderWith( array('Invoice')));
		}

		//set via function parameters
		if($from) $email->setFrom($from);
		if($to) $email->setTo($to);
		if($sub) $email->setSubject($sub);
		if($bdy) $email->setBody($bdy);
		if($cc) $email->setCc($cc);
		if($bcc) $email->setBcc($bcc);

		$email->send();
		$this->Sent = true;
		$this->write();
		return true;
	}

	function getEmailContent($urlencode = false){
		$body = _t("Invoice.DEFAULTEMAILMESSAGE","<p>Here is your invoice.</p>");
		$typecontent = ($this->InvoiceType() && $this->InvoiceType()->EmailContent)? $this->InvoiceType()->EmailContent : null;
		$tc = trim(Convert::html2raw($typecontent));
		if($typecontent && !empty($tc)) $body = $typecontent;
		if($urlencode) $body = rawurlencode(str_replace("\n\n","\n",Convert::html2raw($body)));
		return $body;
	}

	function getEmailSubject(){
		$subject = ($this->InvoiceType() && $this->InvoiceType()->EmailSubject)? $this->InvoiceType()->EmailSubject." " : "Invoice ";
		$subject .=  '#'.$this->InvoiceNumber().": ";
		$subject .= $this->Name;
		return $subject;
	}

	function setParent($dataobject){
		if($dataobject->ID){
			$this->ParentID = $dataobject->ID;
			$this->ParentClassName = $dataobject->ClassName;
		}
	}

	function getParent(){
		if($this->ParentID && $this->ParentClass){
			return DataObject::get_by_id($this->ParentClass,$this->ParentID);
		}
		return false;
	}

	function onBeforeDelete(){
		parent::onBeforeDelete();
		foreach($this->InvoiceItems() as $item){
			$item->delete();
			$item->destroy();
		}
	}

	function PaddedID($len = 3){
		return str_pad($this->ID,(int)$len,"0",STR_PAD_LEFT);
	}

	function TimeOverdue(){
		if(!$this->DueDate || $this->IsPaid())
			return '';
		return (strtotime($this->DueDate) < time()) ? strtoupper("DUE ".$this->dbObject('DueDate')->Ago()) : "";
	}

	/**
	 * Outputs number of days overdue
	 */
	function DaysOverdue(){
		return floor((time() - strtotime($this->DueDate))/60/60/24);
	}

	function LastSentDays(){
		return floor((strtotime($this->DueDate) - strtotime($this->NoticeLastSent))/60/60/24);
	}

}