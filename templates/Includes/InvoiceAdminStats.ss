		<div style="background:#fff;border:1px solid #aaa;padding:10px;margin-top:10px;">
			<table width="100%">
				<tr><th align="left">Stats</th><th align="left">Count</th><th align="left">Amount</th></tr>
			<% control Stats %>
				<tr <% if OverdueCount %>style="color:red;"<% end_if %>><td>overdue</td><td>$OverdueCount</td><td>$OverdueMoney.Nice</td></tr>
				<tr><td>un-paid</td><td>$UnpaidCount</td><td>$UnpaidMoney.Nice</td></tr>
				<tr><td>receivables</td><td>$ReceivablesCount</td><td>$ReceivablesMoney.Nice</td></tr>
				<tr><td>un-sent</td><td>$UnsentCount</td><td>$UnsentMoney.Nice</td></tr>
				<tr><td>paid</td><td>$PaidCount</td><td>$PaidMoney.Nice</td></tr>
			<% end_control %>
			</table>
		</div>