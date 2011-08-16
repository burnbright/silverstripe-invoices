<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  	<head>
		<% base_tag %>
		<style type="text/css">
			@page { /* PDF generation CSS */
			  	size: A4 portrait;
			  	margin: 5% 10mm;
			  	font-size:12pt !important;
				color:#000000;
			}
			body,html{
				background:#fff !important;
			}
			body{
				font-size:12pt;
				color:#000000;
			}
			body h1{font-size:2em;}
			
			table{
				border:1px solid black;
				border-collapse:collapse;
			}
				table td,table th{
					padding:0.2em;
				}
			
			.costsummary{
				border:1px solid black;
				margin: 0 auto;
			}
			table.costsummary td,table.costsummary th{
				border-right:1px solid black;
				border-left:1px solid black;
				text-align:right;
			}
			
			table.costsummary th{
				border-bottom:1px solid black;
			}
			
			table.costsummary .label{
				text-align:left;
			}
			table.costsummary .total{
				font-weight:bold;
			}
			
			.footer{
				position:absolute;
				bottom:20pt;
				left:0px;
				width:100%;
				text-align:center;
			}							
		</style>
	</head>
	
	<body style="line-height:1.2em;font-size:12pt;color:#000000;">
		<h1 style="color:black;font-weight:bold;">TAX INVOICE</h1>
		<table width="40%">
			<% if InvoiceType.TaxNumber %><tr><td>GST Number</td><td>$InvoiceType.TaxNumber</td></tr><% end_if %>
			<tr><td>Invoice Number:</td><td>$InvoiceNumber</td></tr>
			<tr><td>Invoice Date:</td><td>$Created.DayOfMonth $Created.ShortMonth $Created.Year</td></tr>
			<tr><td>Reference Code:</td><td>$ID</td></tr>
		</table>
		<!-- <img src="$ThemeDir/images/logo.gif" style="position:absolute;top:0;right:0;"/>  -->
		<br/>
		<p>
		Bill To:<br/>
		$Address<br/>
		</p>
		$InvoiceType.Content
		<p>
			
		</p>
		<br/>
		<table class="costsummary" cellpadding="5" width="98%" style="border-collapse:collapse;">
			<tr><th class="label">Item</th><th>Quantity</th><th class="rate">Rate</th><th>Total</th></tr>
			<!-- invoice items -->
			<% control InvoiceItems %>
			<tr><td class="label">$Description<br/> </td><td>$Quantity</td><td class="rate">$Cost.Nice</td><td>$TotalCost.Nice</td></tr>
			<% end_control %>
			<!-- totals -->
			<tr><td class="label">Sub Total</td><td></td><td></td><td>$SubTotal.Nice</td></tr>
			<% if InvoiceType.TaxRate %><tr><td class="label">+ GST @ $InvoiceType.TaxRate.Nice</td><td></td><td class="rate">$InvoiceType.TaxRate.Nice</td><td>$Tax.Nice</td></tr><% end_if %>
			<tr class="total"><td class="label">Total Due</td><td></td><td></td><td>$Total</td></tr>
		</table>
		<br/>
		
		<% if DueDate %>
			<p>Please pay invoice by $DueDate.Nice</p><br/>
		<% end_if %>
		<!-- payment information -->
		<div class="footer">$Now.Nice</div>
	</body>
</html>
