<?php
class InvoiceAdmin extends ModelAdmin{

	static $url_segment = 'invoices';
	static $menu_title = 'Invoices';
	static $menu_priority = 1;
	//static $menu_icon = 'invoices/images/invoice-icon.png';

	static $managed_models = array(
		'Invoice',
		'InvoiceType'
	);
	public static $model_importers = array();

	/**
	 * Provides viewable summary section on the left by customising template
	 * @return ViewableData
	 */
	function Stats(){
		$invoices = DataObject::get('Invoice');
		$unpaidcount = $unsentcount = $paidcount = $overduecount = $receivablescount = 0;
		$unpaidmoney = $unsentmoney = $paidmoney = $overduemoney = $recievablesmoney = 0;
		if($invoices){
			foreach($invoices as $invoice){
				if($invoice->Status == 'unpaid'){$unpaidcount++;$unpaidmoney += $invoice->getTotalOutstanding();}
				if(!$invoice->Sent){$unsentcount++;$unsentmoney += $invoice->getTotal(false);}
				if($invoice->Status == 'paid'){$paidcount++;$paidmoney += $invoice->getTotal(false);}

				if($invoice->DueDate && $invoice->Status == 'unpaid' && (strtotime($invoice->DueDate) < time())){
					$overduecount++;$overduemoney += $invoice->getTotalOutstanding();
				}
				if($invoice->Status == 'unpaid' && $invoice->Sent){$receivablescount++;$recievablesmoney += $invoice->getTotalOutstanding();}
			}
		}
		$counts = array(
			'UnpaidCount' => $unpaidcount,
			'UnsentCount'=> $unsentcount,
			'PaidCount' => $paidcount,
			'OverdueCount' => $overduecount,
			'ReceivablesCount' => $receivablescount,
		);
		$monies = array(
			'UnpaidMoney' => $unpaidmoney,
			'UnsentMoney' => $unsentmoney,
			'PaidMoney'=> $paidmoney,
			'OverdueMoney' => $overduemoney,
			'ReceivablesMoney' => $recievablesmoney
		);
		//convert to Currency fields
		foreach($monies as $key => $amount){
			$cur = Currency::create();
			$cur->setValue($amount);
			$monies[$key] = $cur;
		}
		return new ArrayData(array_merge($counts,$monies));
	}

	function getEditForm($id = null, $fields = null) {
		$form = parent::getEditForm($id, $fields);
		if($grid = $form->Fields()->fieldByName("Invoice")){
			$detailform = $grid->getConfig()->getComponentByType('GridFieldDetailForm');
			$detailform->setItemRequestClass('InvoiceGridFieldDetailForm_ItemRequest');
		}
		return $form;
	}
	
	function init(){
		parent::init();
		Requirements::javascript("invoices/javascript/InvoiceAdmin.EditForm.js");
	}
	
}

class InvoiceGridFieldDetailForm_ItemRequest extends GridFieldDetailForm_ItemRequest{

	function ItemEditForm(){
		$form = parent::ItemEditForm();
		if(!$this->record || !$this->record->ID){
			return $form;
		}
		$actions =  $form->Actions();
		
		$actions->push(CompositeField::create(
			LiteralField::create("viewinvoice","<a class=\"ss-ui-button newwindow\" href=\"".$this->Link("viewinvoice")."\"><span class=\"ui-icon btn-icon-preview\" style=\"display:inline-block;\"></span> View</a>")
		));
		
		if(!$this->record->PDFVersionID){
			$form->Actions()->insertAfter(
				LiteralField::create("generateinvoicepdf","<a class=\"ss-ui-button reloader\" href=\"".$this->Link("generateinvoicepdf")."\">Generate PDF</a>"),
				"viewinvoice"
			);
		}
		elseif(!$this->record->Sent && $this->record->Email){
			$link = "mailto:{$this->record->Email}?Subject=".$this->record->getEmailSubject();
			$link .= "&Body=".$this->record->getEmailContent(true);

			$actions->push(CompositeField::create(
				LiteralField::create("emailinvoice","<label>Email:</label> <a class=\"ss-ui-button\" href=\"".$this->Link("emailinvoice")."\">Send</a>"),
				LiteralField::create("mailto","<a class=\"ss-ui-button\" href=\"".$link."\">Compose</a>")
			)->addExtraClass("ss-ui-buttonset nolabel  ui-buttonset"));
		}
		return $form;
	}

	/**
	* Outputs html for viewing an invoice in browser.
	*/
	function viewinvoice(){
		if($invoice = $this->record){
			Requirements::clear();
			Requirements::themedCSS("pagepreview","invoices","screen,projection");
			return $invoice->renderWith('Invoice');
		}
		return _t("InvoiceAdmin.INVOICENOTFOUND","Invoice could not be found");
	}

	/**
	* Creates a PDF version of an invoice.
	*/
	function generateinvoicepdf(){
		Requirements::clear();
		if($invoice = $this->record){
			$invoice->generatePDFInvoice();
			return "Generated: <a href='".$invoice->PDFVersion()->Link()."'>".$invoice->PDFVersion()->Link()."</a>";
		}
		return _t("InvoiceAdmin.INVOICENOTFOUND","Invoice could not be found");
	}

	/**
	 * Sends invoice via email.
	 */
	function emailinvoice(){
		$message = _t("InvoiceAdmin.INVOICENOTFOUND","Invoice could not be found");
		if($invoice = $this->record){
			if($invoice->sendEmail()){
				$message = _t("InvoiceAdmin.EMAILSUCCESSFUL","email sent successfully");
			}else{
				$message = _t("InvoiceAdmin.EMAILUNSUCCESSFUL","email NOT sent");
			}
		}
		$this->response->addHeader('X-Status', rawurlencode($message));
		
		return $this->getResponseNegotiator()->respond($this->request);
	}

	/**
	 * Preview / edit email before sending.
	 * @todo: finish email previewing / editing
	 */
	function previewemail(){
		if($invoice = $this->record){
			//TODO: this email form could become a system-wide tool
			$fields = new FieldList(
				new EmailField("Email","To"),
				new EmailField("CC","CC"),
				new EmailField("From","From"),
				new TextField("Subject"),
				new TextareaField('Content')
			);

			$form = new Form($this,'Form',$fields,new FieldList(new FormAction('emailinvoice','Send')));
			$form->loadDataFrom($invoice);

			//TODO or: return form->forAjaxTemplate() ???
			$email =  new ArrayData(array(
					'EmailContent' => $invoice->getEmailContent(),
					'EmailForm' => $form,
					'Invoice' => $invoice
			));
			
			return $email->renderWith("InvoiceAdmin_previewemail");
		}
		return _t("InvoiceAdmin.INVOICENOTFOUND","Invoice could not be found");
	}

}