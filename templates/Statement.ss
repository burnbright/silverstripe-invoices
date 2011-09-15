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
			body{
				font-size:12pt;
				font-family:arial;
				color:#000000;
			}
			body h1{font-size:1.8em;margin-bottom:0.5em;}
			body h2{font-size:1.2em;magin-top:0.5em;}
			
			#Header{
				padding-bottom:20px;
			}
			
			img.logo{
				float:right;
				width:200px;
			}
			
			.addressbox{
				float:left;
				margin-right:2em;
			}
			
			li{list-style-position:inside;}			
			
			table{
				border:1px solid black;
				border-collapse:collapse;
			}
				table td,table th{
					padding:0.2em;
					border:none;
				}
				
			table.details{
				float:right;
				border:none;
				width:300px;
			}
				table.details .label{
					text-align:right;
					width:100px;
					text-transform:uppercase;
					font-weight:bold;
				}
			
			.costsummary{
				border:1px solid black;
				margin: 0 auto;
				margin-top:20px;
			}
			table.costsummary td,
			table.costsummary th{
				border-right:1px solid black;
				border-left:1px solid black;
				text-align:left;
				font-size:80%;
			}
				table.costsummary tbody tr.even td{
					background:#eee;
				}
				table.costsummary tbody tr.paid td.activity{
					text-decoration: line-through;
				}
				table.costsummary tbody tr.credit td{
					color:#666;
				}
			
			table.costsummary th{
				border:1px solid black;
				text-align:center;
				text-transform:uppercase;
				font-weight:bold;
				background:#d8e4e8;
			}
			
			table.costsummary th.balancelabel{
				text-align:right;
			}
			
			table.costsummary tfoot td{
				border:1px solid black;
				font-weight:bold;
			}
			
			table.costsummary td.money{
				text-align:right;
			}
			
			table.costsummary .label{
				text-align:left;
			}
			
			table.costsummary tbody tr.filler{
				height:100px;
			}
			
			table.costsummary tr.subtotal td{
				border-top:1px solid #000;
			}
				table.costsummary tr.subtotal td.label{
					text-align:right;
				}
			table.costsummary tr.total td{
				border:1px solid #000;
				background:#ccc;
			}
				table.costsummary tr.total td.label{
					text-align:right;
					text-transform:uppercase;
					font-weight:bold;
					background:#fff;
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
	<body>
		<div id="Header">
			<h1 style="color:black;font-weight:bold;">Statement of Accounts</h1>
			<div style="clear:both;"><!--  --></div>
		</div>
		<p class="date"><em>Date: $Date.Long</em></p>
		<div class="addressbox">
		</div>
		<div class="addressbox">
			<p><strong>Bill To:</strong><br/>
				<% if Address %>$Address<br/><% end_if %>
			</p>
		</div>
		<div style="clear:both;"><!--  --></div>
		<div>
			$Comments
		</div>
		<table class="costsummary" cellpadding="5" width="98%" style="border-collapse:collapse;">
			<thead>
				<tr>
					<th>Date</th>
					<th>Activity</th>
					<th>Status</th>
					<th>Due Date</th>
					<th>Amount</th>
					<th>Payments</th>
					<th>Balance</th>
				</tr>
			</thead>
			<tbody>
				<% control Lines %>
					<tr class="$EvenOdd <% if DataObject.IsPaid %>paid<% end_if %> $CreditDebit">
						<td class="date">$Date.Long</td>
						<td class="activity">$Activity</td>
						<td class="status">$Status</td>
						<td class="due"><% if DueDate %>$DueDate.Long<% end_if %></td>
						<td class="debit money"><% if Debit %>$NiceDebit.Nice<% end_if %></td>
						<td class="credit money"><% if Credit %>$NiceCredit.Nice<% end_if %></td>
						<td class="balance money">$Balance.Nice</td>
					</tr>
				<% end_control %>
			</tbody>
			<tfoot>
				<tr>
					<th colspan="5" class="balancelabel">Balance Due:</th>
					<td class="money">$BalanceDue.Nice</td>
				</tr>
			</tfoot>
		</table>	
		<% if InvoiceType %>
		<div class="toc">
			$InvoiceType.TermsConditions
		</div>
		<% end_if %>
	</body>
</html>