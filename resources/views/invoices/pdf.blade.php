<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header {
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        .invoice-details {
            float: right;
            text-align: right;
        }
        .invoice-details h1 {
            margin: 0;
            font-size: 24px;
            color: #7f8c8d;
        }
        .client-details {
            margin-bottom: 30px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th {
            background: #f8f9fa;
            border-bottom: 2px solid #ddd;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        .table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            width: 300px;
            float: right;
        }
        .totals .row {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .totals .row:last-child {
            border-bottom: none;
            font-size: 16px;
            font-weight: bold;
            background: #f8f9fa;
            padding: 10px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            text-transform: uppercase;
            font-weight: bold;
            display: inline-block;
        }
        .status-paid { background: #d4edda; color: #155724; }
        .status-sent { background: #cce5ff; color: #004085; }
        .status-draft { background: #e2e3e5; color: #383d41; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <table width="100%">
                <tr>
                    <td valign="top">
                        <div class="logo">{{ optional($invoice->branch->business)->name ?? 'POS Supermarket' }}</div>
                        <div>{{ $invoice->branch->location ?? '' }}</div>
                        <div>{{ $invoice->branch->phone ?? '' }}</div>
                    </td>
                    <td valign="top" class="text-right">
                        <div class="invoice-details">
                            <h1>INVOICE</h1>
                            <div><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</div>
                            <div><strong>Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</div>
                            <div><strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}</div>
                            <div style="margin-top: 5px;">
                                <span class="status-badge status-{{ $invoice->status }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="client-details">
            <strong>Bill To:</strong><br>
            {{ $invoice->customer ? $invoice->customer->name : $invoice->customer_email }}<br>
            @if($invoice->customer && $invoice->customer->address)
                {{ $invoice->customer->address }}<br>
                {{ $invoice->customer->city }}, {{ $invoice->customer->country }}<br>
            @endif
            @if($invoice->customer_phone)
                Phone: {{ $invoice->customer_phone }}<br>
            @endif
            Email: {{ $invoice->customer_email }}
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th width="50%">Description</th>
                    <th width="15%" class="text-right">Price</th>
                    <th width="15%" class="text-right">Quantity</th>
                    <th width="20%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>
                        {{ $item->product ? $item->product->name : $item->product_name }}
                        @if($item->product && $item->product->sku)
                            <br><small style="color: #999;">SKU: {{ $item->product->sku }}</small>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <table width="100%">
                <tr class="row">
                    <td>Subtotal:</td>
                    <td class="text-right">{{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                @if($invoice->tax_amount > 0)
                <tr class="row">
                    <td>Tax:</td>
                    <td class="text-right">{{ number_format($invoice->tax_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="row">
                    <td><strong>Total:</strong></td>
                    <td class="text-right"><strong>{{ number_format($invoice->total_amount, 2) }}</strong></td>
                </tr>
            </table>
        </div>

        <div style="clear: both;"></div>

        @if($invoice->notes)
        <div style="margin-top: 30px; padding: 15px; background: #fafafa; border-radius: 4px;">
            <strong>Notes:</strong><br>
            {{ $invoice->notes }}
        </div>
        @endif

        <div class="footer">
            <p>Thank you for your business!</p>
            @if($paymentLink)
                <p>You can pay this invoice online securely at:<br>
                <a href="{{ $paymentLink }}">{{ $paymentLink }}</a></p>
            @endif
        </div>
    </div>
</body>
</html>
