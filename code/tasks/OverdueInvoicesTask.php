<?php
/**
 * Checks if any invoices are now overdue
 */
class OverdueInvoicesTask extends CliController{
	
	function process(){
		$date = date("Y-m-d"); //today
		$between_SQL = "\"DueDate\" BETWEEN '$date 00:00:00' AND '$date 23:59:59'";
		$invoices = DataList::create("Invoice")->where($between_SQL);
		if($invoices->exists()){
			$output = new ArrayList();
			foreach($invoices as $invoice){
				$output->push($invoice->customise(array(
					'AdminLink' => "admin/invoices/Invoice/EditForm/field/Invoice/item/{$invoice->ID}/edit" //doesn't work in ss3 templating
				)));
			}
			$data = new ArrayData(array(
				"Invoices" => $output
			));
			$email = new Email(Email::getAdminEmail(),Email::getAdminEmail());
			$email->setFrom(Email::getAdminEmail());
			$email->setSubject("Invoice(s) now overdue");
			$email->setTemplate("GenericEmail");
			$email->setBody($data->renderWith("OverdueInvoiceAdminEmail"));
			$email->send();
		}else{
			echo "no overdue invoices\n";
		}
		
	}
	
}