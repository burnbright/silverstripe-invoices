<?php
class InvoiceItem extends DataObject{

	static $db = array(
		'Description' => 'Text',
		'Quantity' => 'Decimal',
		'Cost' => 'Currency',
		//discount?
		'Sort' => 'Int'
	);

	static $has_one = array(
		'Invoice' => 'Invoice'
		//Invoiceable => Event,Product,Etc..
	);

	static $summary_fields = array(
		'Description' => 'Description',
		'Quantity' => 'Quantity',
		'Cost' => 'Cost',
		'TotalCost' => 'Total'
	);

	static $casting = array(
		'TotalCost' => 'Currency'
	);

	/**
	 * Total worked out cost of item.
	 */
	 //TODO: this will need to be modified if there is a specific way of working out the cost.
	 //I want to make this extendable so people custom work out costs
	function TotalCost(){
		$total = $this->Cost * $this->Quantity;
		return ($total >= 0) ? $total : 0;
	}

}