<?php
/**
 * Customisations to {@link Payment}
 */
class InvoicePaymentDecorator extends DataExtension {
	
	function extraStatics($class = null, $extension = null) {
		
		Payment::$searchable_fields = array(
			'ID','ClassName'
		);
		
		Payment::$default_sort = "Created DESC";
		
		return array(
			'belongs_many_many' => array(
				'Invoices' => 'Invoice'
			)
		);
	}
	
}