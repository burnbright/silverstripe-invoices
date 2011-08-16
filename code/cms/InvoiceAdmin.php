<?php
class InvoiceAdmin extends ModelAdmin{

	static $url_rule = '/$Action/$ID/$OtherID';

	static $managed_models = array(
		'Invoice',
		'InvoiceType',
		'Payment'
	);

	static $url_segment = 'invoices';
	static $menu_title = 'Invoices';

	public static $collection_controller_class = "InvoiceAdmin_CollectionController";

	function viewinvoice(){
		if($id = Director::urlParam('ID') && $invoice = DataObject::get_by_id('Invoice',Director::urlParam('ID'))){
			Requirements::clear();
			Requirements::themedCSS("layout");
			Requirements::themedCSS("typography");
			Requirements::themedCSS("form");
			$customcss = <<<CSS
				body{
					margin:20px auto;
					padding:60px 30px;
					border:1px solid #ccc;
					width: 800px;
					position:relative;
					background:#fff;
				   -moz-box-shadow:3px 3px 15px #999;
				   -webkit-box-shadow:3px 3px 15px #999;
				   box-shadow:3px 3px 15px #999;
				}
				html{
					background:none;
					background-color:#eee;
				}

CSS;
			Requirements::customCSS($customcss);
			echo $invoice->renderWith('Invoice');
			die();
		}else{
			echo "no id specified";
		}
	}

	function generateinvoice(){
		Requirements::clear();
		if($id = Director::urlParam('ID') && $invoice = DataObject::get_by_id('Invoice',Director::urlParam('ID'))){
			$invoice->generatePDFInvoice();
			echo "Generated: <a href='".$invoice->PDFVersion()->Link()."'>".$invoice->PDFVersion()->Link()."</a>";
			die(); //TODO: fix no sub-urls issue
		}else{
			echo "no id specified";
		}
	}

	function emailinvoice(){
		if($id = Director::urlParam('ID') && $invoice = DataObject::get_by_id('Invoice',Director::urlParam('ID'))){
			if($invoice->sendEmail()){
				echo "email sent successfully";
			}else{
				echo "email NOT sent";
			}
			die(); //TODO: fix no sub-urls issue
		}else{
			echo "no id specified";
		}
	}

	/**
	 * Preview / edit email before sending.
	 * @todo: finish email previewing / editing
	 */
	function previewemail(){

		if($id = Director::urlParam('ID') && $invoice = DataObject::get_by_id('Invoice',Director::urlParam('ID'))){

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
			return array(
				'EmailContent' => $invoice->getEmailContent(),
				'EmailForm' => $form,
				'Invoice' => $invoice
			);
		}else{
			echo "no id specified";
		}
	}

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

class InvoiceAdmin_CollectionController extends ModelAdmin_CollectionController{

	public function AddForm() {
		$newRecord = new $this->modelClass();

		if($newRecord->canCreate()){
			if($newRecord->hasMethod('getCMSAddFormFields')) {
				$fields = $newRecord->getCMSAddFormFields();
			} else {
				$fields = $newRecord->getCMSFields();
			}

			$validator = ($newRecord->hasMethod('getCMSValidator')) ? $newRecord->getCMSValidator() : null;

			$actions = new FieldSet (
				new FormAction("doCreate", _t('ModelAdmin.ADDBUTTON', "Add"))
			);

			$form = new Form($this, "AddForm", $fields, $actions, $validator);
			//$form->loadDataFrom($newRecord);

			return $form;
		}
	}

}