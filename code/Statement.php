<?php

class Statement extends ViewableData{

	protected $lines;
	protected $balance;

	function __construct(){
		$this->lines = new DataObjectSet();
		$this->blance = 0;
	}

	function addInvoices($invoices){
		foreach($invoices as $invoice){
			$this->lines->push(new InvoiceStatementLine($invoice));
		}
	}

	function addPayments($payments){
		foreach($payments as $payment){
			$this->lines->push(new PaymentStatementLine($payment));
		}
	}

	function Date(){
		$d = new Date(null);
		$d->setValue(date("Y-m-d h:i:s"));
		return $d;
	}

	function Lines(){
		return $this->lines;
	}

	function getBalanceDue(){
		$c = new Currency('Balance');
		$c->setValue($this->balance);
		return $c;
	}

	function toTemplate(){

		//add opening line
		$openingline = new StatementLine();
		$openingline->setActivity("opening balance");
		$this->lines->push($openingline);

		//sort
		$this->lines->sort('Date','ASC');

		//generate running balance
		foreach($this->lines as $line){
			$this->balance = $line->updateBalance($this->balance);
		}
		return $this->renderWith('Statement');
	}

}

class StatementLine extends ViewableData{

	//Date, Activity, Due Date, Amount, Payments, Balance
	protected $balance = 0;
	protected $dataobject;
	protected $activityname = "";

	function Debit(){
		return 0;
	}
	function NiceDebit(){
		$c = new Currency('NiceDebit');
		$c->setValue($this->Debit());
		return $c;
	}

	function Credit(){return 0;}
	function NiceCredit(){
		$c = new Currency('NiceCredit');
		$c->setValue($this->Credit());
		return $c;
	}

	function Activity(){
		return $this->activityname;
	}

	function setActivity($activity){
		$this->activityname = $activity;
	}

	function getBalance(){
		$c = new Currency('Balance');
		$c->setValue($this->balance);
		return $c;
	}

	function updateBalance($balance){
		$this->balance = $balance - $this->Credit() + $this->Debit();
		return $this->balance;
	}

	function debug(){
		return $this->Activity()." ".$this->Credit()." ".$this->Debit()." ".$this->getBalance()." ";
	}

	function getDate(){
		if($this->dataobject)
			return $this->dataobject->dbObject('Created');
	}

	function DataObject(){
		return $this->dataobject;
	}

	function CreditDebit(){
		if ($this->Credit()) return "credit";
		if ($this->Debit()) return "debit";
	}

}

class InvoiceStatementLine extends StatementLine{

	function __construct(Invoice $invoice){
		parent::__construct();
		$this->dataobject = $invoice;
	}

	function Debit(){
		return $this->dataobject->getTotal(false);
	}

	function Activity(){
		return "Invoice #".$this->dataobject->InvoiceNumber();
	}

	function DueDate(){
		return $this->dataobject->dbObject('DueDate');
	}

}

class PaymentStatementLine extends StatementLine{

	function __construct(Payment $payment){
		parent::__construct();
		$this->dataobject = $payment;
	}

	function Credit(){
		return $this->dataobject->Amount->Amount;
	}

	function Activity(){
		return "Payment for Invoice #".$this->dataobject->Invoices()->First()->InvoiceNumber();
	}

}