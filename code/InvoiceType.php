<?php
class InvoiceType extends DataObject{

	static $db = array(
		'Name' => 'Varchar', // eg: NetworkRegistration
		'ReferenceNumber' => 'Varchar',
		'Prefix' => 'Varchar',

		'TaxNumber' => 'Varchar',
		'TaxRate' => 'Percentage', //percentage of total eg 12.5

		'PaymentDays' => 'Int',
		'AddressFrom' => 'Text',

		'Content' => 'HTMLText', //blurb about payment / invoice type
		'TermsConditions' => 'HTMLText', //terms and conditions
		//payment method instructions

		'EmailSubject' => 'Varchar',
		'EmailContent' => 'HTMLText'

		//TODO: default class ... the default kind of object this should be for
			///...could be handy for automatically populating the invoice type field
	);

	static $has_many = array(
		'Invoices' => 'Invoice'
	);

	static $defaults = array(
		'TaxRate' => 12.5
	);

	static $summary_fields = array(
		'Name',
		'ReferenceNumber',
		'Prefix',
		'TaxNumber',
		'TaxRate',
		'PaymentDays'
	);

	function HasTaxRate(){
		return ($this->TaxRate > 0)? $this->TaxRate : false ;
	}

}
?>