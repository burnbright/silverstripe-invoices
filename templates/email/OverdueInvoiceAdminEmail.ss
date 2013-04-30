<p>Invoices overdue:</p>

<% if Invoices %>
<table border="1">
	<tr>
		<th>Number</th>
		<th>Name</th>
		<th>Due Date</th>
		<th>Total Overdue</th>
	</tr>
<% loop Invoices %>
	<tr>
		<td>$InvoiceNumber</td>
		<td>$Name</td>
		<td>$DueDate.Nice ($DueDate.Ago)</td>
		<td>$TotalOutstanding</td>
		<td><a href="{$AdminLink}">View</a></td>
	</tr>
<% end_loop %>
</table>
<% else %>
	None.
<% end_if %>