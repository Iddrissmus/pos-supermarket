@component('mail::message')
# Invoice from {{ optional($invoice->branch->business)->name ?? 'POS Supermarket' }}

Dear {{ $invoice->customer ? $invoice->customer->name : 'Customer' }},

Please find attached the invoice **#{{ $invoice->invoice_number }}** for **{{ $invoice->invoice_date->format('M d, Y') }}**.

### Invoice Summary
- **Invoice Number:** {{ $invoice->invoice_number }}
- **Due Date:** {{ $invoice->due_date->format('M d, Y') }}
- **Total Amount:** ₵{{ number_format($invoice->total_amount, 2) }}
@if($invoice->allow_partial_payment)
- **Partial Payment:** Allowed (Enter amount at checkout)
@endif

You can securely pay this invoice online by clicking the button below:

@component('mail::button', ['url' => $paymentLink])
Pay Invoice Now
@endcomponent

If the button above does not work, please copy and paste this link into your browser:
{{ $paymentLink }}

Thank you for your business.

Regards,<br>
{{ optional($invoice->branch->business)->name ?? 'POS Supermarket' }}<br>
{{ $invoice->branch->location ?? '' }}
@endcomponent
