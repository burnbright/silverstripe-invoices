<?php
/**
 * Invoice payment type
 *
 *	@deprecated
 *
 * This may be a bad idea, because an invoice is not generally something you pay for.
 * An invoice is rather notification that something else needs to be paid for.
 */


class InvoicePayment extends Payment {

	/**
	 * Process the Cheque payment method
	 */
	function processPayment($data, $form) {
		$this->Status = 'Pending';
		$this->Message = '<p class="warningMessage">' . _t('InvoicePayment.MESSAGE', 'Payment accepted via Invoice.') . '</p>';

		$invoice = new Invoice();

		if(isset($data['Email'])) $invoice->Email = $data['Email'];
		if(isset($data['Address'])) $invoice->Address = $data['Address'];

		$invoice->write();

		$this->write();
		return new Payment_Success();
	}

	function getPaymentFormFields() {
		return new FieldSet(
			new TextareaField('Address','Invoice Address')
		);
	}

	function getPaymentFormRequirements() {
		return array(

		);
	}


}

?>
