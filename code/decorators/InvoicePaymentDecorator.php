<?php
/**
 * Customisations to {@link Payment}
 */
class InvoicePaymentDecorator extends DataExtension {
	
	function extraStatics($class = null, $extension = null) {
		
		//Customise model admin summary fields
		//warning - can't do this because it affects DataObject
		
		//Payment::$summary_fields['ID'] = 'ID';
		//Payment::$summary_fields['Created'] = 'Created';
		//Payment::$summary_fields['ClassName'] = 'Type';
		//Payment::$summary_fields['Status'] = 'Status';
		
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
	
	function updateCMSFields(FieldList $fields){
		
		$fields->addFieldToTab('Root.Main',new PopupDateTimeField('Created','Date'));
		
	}
	
}
?>