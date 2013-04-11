<?php
/**
 * Customisations to {@link Payment}
 */
class InvoicePaymentDecorator extends DataExtension {
	
	static $belongs_many_many = array(
		'Invoices' => 'Invoice'
	);
	
	static $searchable_fields = array(
		'ID','ClassName'
	);
	
	static $default_sort = "Created DESC";
	
}