<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $sale->id }} | {{ config('app.name', 'POS Supermarket') }}</title>
    <style>
        :root {
            color-scheme: light;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 24px;
        }
        .receipt-container {
            max-width: 640px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }
        .receipt-header {
            background: linear-gradient(135deg, #047857, #10b981);
            color: #ffffff;
            padding: 32px;
            text-align: center;
        }
        .receipt-header h1 {
            margin: 0 0 8px;
            font-size: 24px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .receipt-header p {
            margin: 0;
            font-size: 14px;
            opacity: 0.85;
        }
        .receipt-body {
            padding: 32px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .info-card {
            background: #f9fafb;
            border-radius: 12px;
            padding: 16px;
        }
        .info-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6b7280;
        }
        .info-value {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            margin-top: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        thead {
            background: #f9fafb;
        }
        th, td {
            padding: 12px;
            text-align: left;
            font-size: 14px;
        }
        th {
            font-weight: 600;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 12px;
        }
        tbody tr + tr {
            border-top: 1px solid #e5e7eb;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            margin-top: 24px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            font-size: 14px;
        }
        .totals-row.total {
            font-size: 18px;
            font-weight: 700;
            color: #047857;
            border-top: 2px solid #e5e7eb;
            margin-top: 8px;
            padding-top: 16px;
        }
        .receipt-footer {
            padding: 24px 32px 32px;
            text-align: center;
            background: #f9fafb;
        }
        .receipt-footer p {
            margin: 8px 0 16px;
            color: #6b7280;
            font-size: 13px;
        }
        .button-group {
            display: inline-flex;
            gap: 12px;
        }
        .button {
            border-radius: 999px;
            border: none;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
        }
        .button-primary {
            background: #047857;
            color: #ffffff;
        }
        .button-secondary {
            background: #e5e7eb;
            color: #111827;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .button-group {
                display: none;
            }
            .receipt-container {
                box-shadow: none;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <h1>{{ config('app.name', 'POS Supermarket') }}</h1>
            <p>Official Receipt</p>
        </div>
        <div class="receipt-body">
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-label">Receipt No.</div>
                    <div class="info-value">#{{ $sale->id }}</div>
                </div>
                <div class="info-card">
                    <div class="info-label">Date &amp; Time</div>
                    <div class="info-value">{{ $sale->created_at->format('M d, Y h:i A') }}</div>
                </div>
                <div class="info-card">
                    <div class="info-label">Branch</div>
                    <div class="info-value">{{ optional($sale->branch)->display_label ?? 'Branch' }}</div>
                </div>
                <div class="info-card">
                    <div class="info-label">Cashier</div>
                    <div class="info-value">{{ $sale->cashier->name ?? 'Cashier' }}</div>
                </div>
                @if($sale->customer)
                <div class="info-card">
                    <div class="info-label">Customer</div>
                    <div class="info-value">{{ $sale->customer->display_name }}</div>
                </div>
                @endif
                <div class="info-card">
                    <div class="info-label">Payment Method</div>
                    <div class="info-value" style="text-transform: capitalize;">{{ str_replace('_', ' ', $sale->payment_method) }}</div>
                </div>
            </div>

            <div>
                <h2 style="font-size: 16px; font-weight: 700; color: #111827; margin-bottom: 12px;">Items Purchased</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Price</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: #111827;">{{ $item->product->name ?? 'Product' }}</div>
                                    <div style="font-size: 12px; color: #6b7280;">SKU: {{ $item->product->sku ?? 'N/A' }}</div>
                                </td>
                                <td class="text-right">{{ $item->quantity }}</td>
                                <td class="text-right">₵{{ number_format($item->price, 2) }}</td>
                                <td class="text-right">₵{{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="totals">
                <div class="totals-row">
                    <span>Subtotal</span>
                    <span>₵{{ number_format($totals['subtotal'] ?? 0, 2) }}</span>
                </div>
                
                @if(!empty($totals['tax_components']))
                    @foreach($totals['tax_components'] as $taxComponent)
                        <div class="totals-row">
                            <span>{{ $taxComponent['name'] }} ({{ number_format($taxComponent['rate'], 1) }}%)</span>
                            <span>₵{{ number_format($taxComponent['amount'], 2) }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="totals-row">
                        <span>Tax</span>
                        <span>₵{{ number_format($totals['tax_amount'] ?? 0, 2) }}</span>
                    </div>
                @endif
                
                <div class="totals-row total">
                    <span>Total Paid</span>
                    <span>₵{{ number_format($totals['total'] ?? $sale->total, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="receipt-footer">
            <p>Thank you for shopping with {{ config('app.name', 'POS Supermarket') }}!</p>
            <div class="button-group">
                <button class="button button-secondary" onclick="window.history.back();">Back</button>
                <button class="button button-primary" onclick="window.print();">Print Receipt</button>
            </div>
        </div>
    </div>
</body>
</html>
