# Invoice Module
    
## Maintainer Contact   

 * Jeremy Shipman (Nickname: Jedateach, jeremy@burnbright.net)

## Requirements

 * SilverStripe 3.1+
 * DOMPDF Module: https://github.com/burnbright/silverstripe-dompdf

## Installation Instructions

Copy module folder into your SilverStripe installation directory.

## Usage Overview

Create invoices. Generate PDF versions. Send via email.

## Future Improvements

 * Allow online payment, with the option to add gateway fees to total.
	eg $PaypalTotalCost = $Amount/(1-$PayPal_percentage) + 0.45; //adds percentage cost to payment amount
 * Add a stats rollover date so that stats show total from last occurrance of rollover to now.
 * Allow composing / editing email in CMS before sending.
 * Trigger emails / actions when invoices go overdue