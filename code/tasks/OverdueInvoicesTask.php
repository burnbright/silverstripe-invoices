<?php


class OverdueInvoicesTask extends DailyTask{
	
	//configure string of emails / actions
	/**
	 *  daysoverdue => array(
	 * 		'sendmail' => array(
	 * 			'to' => 'client',
	 * 			'template' => 'HostingOverdueEmail',
	 * 			'subject' => 'Invoice #%d is %d days overdue'
	 * 		),
	 * 		'sendmail' => 'AdminOverdueNotice',
	 * 		'condition' => array(
	 * 			'relation' => 'Client',
	 * 			'callfunction' => 'canSuspend'
	 * 		)
	 * 		'action' => array(
	 * 			'relation' => 'Client',
	 * 			'callfunction' => 'suspendAccount'
	 * 		)
	 * 	)
	 * 
	 */
	public static $overdueoptions = array(
		5 => array(
			'sendmail' => array(
				'to' => 'client',
				'template' => 'InvoiceOverdueReminderEmail',
				'subject' => 'Invoice #%1$s for %2$s is now %3$d days overdue'
			)
		),
		14 => array(
			'sendmail' => array(
				'to' => 'client',
				'template' => 'InvoiceOverdueReminderEmail',
				'subject' => 'Invoice #%1$s for %2$s is now %3$d days overdue.'
			)
		)
	);
	
	static $sendfrom = null;
	
	function process(){
		$nl = (Director::is_cli()) ? "\n" : "</br>";echo $nl; //get correct new line code
		//check for overdue invoices
		if($overdueinvoices = DataObject::get('Invoice',"DueDate < NOW()")){ //i'm not sure why escaping doesn't work in this sql
			foreach($overdueinvoices as $invoice){
				//make sure the same notification isn't sent twice.
//				die($invoice->NoticeLastSent." ".$invoice->DaysOverdue()." ".$invoice->LastSentDays());
				
				if(!$invoice->NoticeLastSent || $this->findBestOption($invoice->DaysOverdue()) > $this->findBestOption($invoice->LastSentDays())){
					
					$this->processOverdueInvoice($invoice);
					//TODO: group invoices by client or email?
				}
			}
		}else{
			echo "No overdue invoices.$nl";
		}
	}
	
	
	protected function processOverdueInvoice($invoice){
		if($moption = $this->findBestOption($invoice->DaysOverdue())){		
			
			foreach(self::$overdueoptions[$moption] as $key => $option){
				switch($key){
					case "sendmail":
						$this->sendMail($option,$invoice);
						
						break;
					case "action":
					break;
				}
			}
		
		}
	}
	
	/**
	 * Helper function for getting the overdue option to send.
	 */
	protected function findBestOption($daysoverdue){
		$bestoption = null;
		foreach(self::$overdueoptions as $key => $option){
			if($key < $daysoverdue && ($key > $bestoption || $bestoption == null)){
				$bestoption = $key;
			}
		}
		return $bestoption;
	}
	
	protected function sendMail($option,$invoice){

		if(!$option || !isset($option['to']) || !isset($option['template']) || !isset($option['subject'])){
			return;
		}
			
		$template = $option['template'];
		
		$from = (self::$sendfrom)? self::$sendfrom : Email::getAdminEmail();
		
		$to = $option['to'];
		if(strtolower($to) == 'admin')
			$to = Email::getAdminEmail();
		if(strtolower($to) == 'client')
			$to = $invoice->Email;
		
		$subject = sprintf($option['subject'],$invoice->InvoiceNumber(),$invoice->Name,$invoice->DaysOverdue());
		
		$body = $invoice->customise(array())->renderWith($template);
		
		$mail = new Email($from,$to,$subject,$body);
		$mail->send();
		
		if(strtolower($option['to']) == 'client'){
			$invoice->NoticeLastSent = date('Y-m-d', time());
			$invoice->write();
		}
		
		echo $subject."<br/>";
		
		
		
	}
	
}

?>
