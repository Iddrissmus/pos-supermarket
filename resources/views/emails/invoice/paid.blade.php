<!DOCTYPE html>
<html>
<head>
    <title>Payment Received</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">

    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #4f46e5;">Payment Received</h2>
        
        <p>Hello {{ $invoice->customer->name ?? $invoice->customer_email }},</p>
        
        <p>Thank you for your payment. We have received <strong>GH₵ {{ number_format($invoice->paid_amount, 2) }}</strong> for Invoice <strong>#{{ $invoice->invoice_number }}</strong>.</p>
        
        <div style="background-color: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Invoice Number:</strong> #{{ $invoice->invoice_number }}</p>
            <p style="margin: 5px 0;"><strong>Date Paid:</strong> {{ $invoice->paid_at->format('M d, Y h:i A') }}</p>
            <p style="margin: 5px 0;"><strong>Amount Paid:</strong> GH₵ {{ number_format($invoice->paid_amount, 2) }}</p>
            <p style="margin: 5px 0;"><strong>Remaining Balance:</strong> GH₵ {{ number_format($invoice->balance_due, 2) }}</p>
        </div>

        <p>You can view the invoice confirmation here:</p>
        <p>
            <a href="{{ route('public.invoice.show', $invoice->uuid) }}" style="display: inline-block; background-color: #4f46e5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Invoice</a>
        </p>
        
        <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
        
        <p style="font-size: 12px; color: #666;">
            If you have any questions, please reply to this email.<br>
            {{ $invoice->branch->business->name }}
        </p>
    </div>

</body>
</html>
