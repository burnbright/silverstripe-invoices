<p>Hello $Name,</p>

<p>This is an automated message to notify you that invoice #$InvoiceNumber is now <% if TimeOverdue %>$DaysOverdue days <% end_if %>overdue.</p>

<% if InvoiceLink %>
	<p>If you require a copy of the invoice, you may access it securely here: $InvoiceLink</p>
<% end_if %>