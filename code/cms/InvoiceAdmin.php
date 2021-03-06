<?php
class InvoiceAdmin extends ModelAdmin{

	static $url_segment = 'invoices';
	static $menu_title = 'Invoices';
	static $menu_priority = 1;

	static $managed_models = array(
		'Invoice' => array(
			'record_controller' => 'InvoiceAdmin_InvoiceRecordController'
		),
		'InvoiceType',
		'Payment'
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
		$data = array(
			'UnpaidCount' => $unpaidcount,
			'UnsentCount'=> $unsentcount,
			'PaidCount' => $paidcount,
			'OverdueCount' => $overduecount,
			'ReceivablesCount' => $receivablescount,

			'UnpaidMoney' => DBField::create('Currency',$unpaidmoney),
			'UnsentMoney' => DBField::create('Currency',$unsentmoney),
			'PaidMoney'=> DBField::create('Currency',$paidmoney),
			'OverdueMoney' => DBField::create('Currency',$overduemoney),
			'ReceivablesMoney' => DBField::create('Currency',$recievablesmoney)
		);
		return new ArrayData($data);
	}

}

class InvoiceAdmin_InvoiceRecordController extends ModelAdmin_RecordController{

	static $allowed_actions = array(
		'viewinvoice',
		'emailinvoice',
		'generateinvoicepdf',
		'previewemail'
	);

	/**
	* Outputs html for viewing an invoice in browser.
	*/
	function viewinvoice(){
		if($invoice = $this->getCurrentRecord()){
			Requirements::clear();
			Requirements::themedCSS("pagepreview","screen,projection");
			return $invoice->renderWith('Invoice');
		}
		return _t("InvoiceAdmin.INVOICENOTFOUND","Invoice could not be found");
	}

	/**
	* Creates a PDF version of an invoice.
	*/
	function generateinvoicepdf(){
		Requirements::clear();
		if($invoice = $this->getCurrentRecord()){
			$invoice->generatePDFInvoice();
			return "Generated: <a href='".$invoice->PDFVersion()->Link()."'>".$invoice->PDFVersion()->Link()."</a>";
		}
		return _t("InvoiceAdmin.INVOICENOTFOUND","Invoice could not be found");
	}

	/**
	 * Sends invoice via email.
	 */
	function emailinvoice(){
		if($invoice = $this->getCurrentRecord()){
			if($invoice->sendEmail()){
				return _t("InvoiceAdmin.EMAILSUCCESSFUL","email sent successfully");
			}else{
				return _t("InvoiceAdmin.EMAILUNSUCCESSFUL","email NOT sent");
			}
		}
		return _t("InvoiceAdmin.INVOICENOTFOUND","Invoice could not be found");
	}

	/**
	 * Preview / edit email before sending.
	 * @todo: finish email previewing / editing
	 */
	function previewemail(){
		if($invoice = $this->getCurrentRecord()){
			//TODO: this email form could become a system-wide tool
			$fields = new FieldSet(
				new EmailField("To","To"),
				new EmailField("CC","CC"),
				new EmailField("From","From"),
				new TextField("Subject"),
				new TextareaField('Content')
			);

			$form = new Form($this,'Form',$fields,new FieldSet(new FormAction('emailinvoice','Send')));
			$form->loadDataFrom($invoice);

			//TODO or: return form->forAjaxTemplate() ???
			return array(
					'EmailContent' => $invoice->getEmailContent(),
					'EmailForm' => $form,
					'Invoice' => $invoice
			);
		}
		return _t("InvoiceAdmin.INVOICENOTFOUND","Invoice could not be found");
	}

}