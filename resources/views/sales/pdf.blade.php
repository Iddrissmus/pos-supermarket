<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            font-size: 10px; 
            color: #333;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #2563eb;
        }
        
        .header h1 { 
            color: #1e40af;
            font-size: 24px;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        .header .subtitle {
            color: #6b7280;
            font-size: 11px;
        }
        
        .period-info {
            background: #eff6ff;
            padding: 12px 15px;
            margin-bottom: 20px;
            border-left: 4px solid #2563eb;
            border-radius: 4px;
        }
        
        .period-info strong {
            color: #1e40af;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }
        
        .summary-row {
            display: table-row;
        }
        
        .summary-box {
            display: table-cell;
            width: 25%;
            padding: 12px;
            text-align: center;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        
        .summary-box.highlight {
            background: #dbeafe;
            border-color: #93c5fd;
        }
        
        .summary-box .label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            display: block;
        }
        
        .summary-box .value {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
        }
        
        .summary-box .value.positive {
            color: #059669;
        }
        
        .summary-box .value.negative {
            color: #dc2626;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            margin: 20px 0 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 25px;
            font-size: 9px;
        }
        
        thead th { 
            background: #1e40af;
            color: white;
            font-weight: bold;
            padding: 8px 6px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        
        tbody tr:hover {
            background: #f9fafb;
        }
        
        tbody tr:nth-child(even) {
            background: #fafafa;
        }
        
        td { 
            padding: 7px 6px;
            text-align: left;
        }
        
        td.number {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        
        .profit-positive {
            color: #059669;
            font-weight: 600;
        }
        
        .profit-negative {
            color: #dc2626;
            font-weight: 600;
        }
        
        .margin-excellent {
            background: #d1fae5;
            color: #065f46;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 600;
        }
        
        .margin-good {
            background: #fef3c7;
            color: #92400e;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 600;
        }
        
        .margin-low {
            background: #fee2e2;
            color: #991b1b;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 600;
        }
        
        tfoot {
            background: #f3f4f6;
            font-weight: bold;
        }
        
        tfoot td {
            padding: 10px 6px;
            border-top: 2px solid #9ca3af;
            font-size: 10px;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 8px;
            color: #6b7280;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        @page {
            margin: 15mm;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 80px;
            color: rgba(0, 0, 0, 0.03);
            z-index: -1;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>📊 SALES PERFORMANCE REPORT</h1>
        <div class="subtitle">Comprehensive Sales Analysis & Financial Summary</div>
    </div>
    
    <!-- Period Information -->
    <div class="period-info">
        <strong>Report Period:</strong> {{ $startDate->format('F d, Y') }} → {{ $endDate->format('F d, Y') }} 
        ({{ $startDate->diffInDays($endDate) + 1 }} days)
    </div>

    <!-- Summary Dashboard -->
    <div class="section-title">Executive Summary</div>
    <div class="summary-grid">
        <div class="summary-row">
            <div class="summary-box">
                <span class="label">Total Sales</span>
                <div class="value">{{ number_format($summary['total_sales']) }}</div>
            </div>
            <div class="summary-box highlight">
                <span class="label">Total Revenue</span>
                <div class="value positive">GH₵{{ number_format($summary['total_revenue'], 2) }}</div>
            </div>
            <div class="summary-box">
                <span class="label">Total COGS</span>
                <div class="value negative">GH₵{{ number_format($summary['total_cogs'], 2) }}</div>
            </div>
            <div class="summary-box highlight">
                <span class="label">Gross Profit</span>
                <div class="value {{ $summary['total_profit'] >= 0 ? 'positive' : 'negative' }}">
                    GH₵{{ number_format($summary['total_profit'], 2) }}
                </div>
            </div>
        </div>
        <div class="summary-row">
            <div class="summary-box">
                <span class="label">Average Margin</span>
                <div class="value">{{ number_format($summary['average_margin'], 2) }}%</div>
            </div>
            <div class="summary-box">
                <span class="label">Total Items Sold</span>
                <div class="value">{{ number_format($sales->sum(fn($s) => $s->items->count())) }}</div>
            </div>
            <div class="summary-box">
                <span class="label">Total Quantity</span>
                <div class="value">{{ number_format($sales->sum(fn($s) => $s->items->sum('quantity'))) }}</div>
            </div>
            <div class="summary-box">
                <span class="label">Avg. Sale Value</span>
                <div class="value">₵{{ $summary['total_sales'] > 0 ? number_format($summary['total_revenue'] / $summary['total_sales'], 2) : '0.00' }}</div>
            </div>
        </div>
    </div>

    <!-- Detailed Sales Table -->
    <div class="section-title">Detailed Sales Transactions</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">ID</th>
                <th style="width: 15%;">Branch</th>
                <th style="width: 12%;">Cashier</th>
                <th style="width: 13%;">Date & Time</th>
                <th style="width: 10%;">Revenue</th>
                <th style="width: 10%;">COGS</th>
                <th style="width: 10%;">Profit</th>
                <th style="width: 8%;">Margin</th>
                <th style="width: 6%;">Items</th>
                <th style="width: 6%;">Qty</th>
                <th style="width: 5%;">Payment</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sales as $sale)
                @php
                    $cogs = $sale->items->sum('total_cost') ?? 0;
                    $profit = $sale->total - $cogs;
                    $margin = $sale->total > 0 ? ($profit / $sale->total) * 100 : 0;
                    
                    $marginClass = 'margin-low';
                    if ($margin >= 30) {
                        $marginClass = 'margin-excellent';
                    } elseif ($margin >= 15) {
                        $marginClass = 'margin-good';
                    }
                @endphp
                <tr>
                    <td style="font-weight: 600;">#{{ $sale->id }}</td>
                    <td>{{ optional($sale->branch)->name ?? 'N/A' }}</td>
                    <td>{{ $sale->cashier->name ?? 'N/A' }}</td>
                    <td style="font-size: 8px;">{{ $sale->created_at->format('M d, Y H:i') }}</td>
                    <td class="number">GH₵{{ number_format($sale->total, 2) }}</td>
                    <td class="number">GH₵{{ number_format($cogs, 2) }}</td>
                    <td class="number {{ $profit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                        GH₵{{ number_format($profit, 2) }}
                    </td>
                    <td class="number">
                        <span class="{{ $marginClass }}">{{ number_format($margin, 1) }}%</span>
                    </td>
                    <td style="text-align: center;">{{ $sale->items->count() }}</td>
                    <td style="text-align: center;">{{ $sale->items->sum('quantity') }}</td>
                    <td style="font-size: 8px; text-transform: capitalize;">
                        {{ str_replace('_', ' ', $sale->payment_method ?? 'N/A') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align: right; padding-right: 10px;">TOTALS:</td>
                <td class="number">GH₵{{ number_format($summary['total_revenue'], 2) }}</td>
                <td class="number">GH₵{{ number_format($summary['total_cogs'], 2) }}</td>
                <td class="number {{ $summary['total_profit'] >= 0 ? 'profit-positive' : 'profit-negative' }}">
                    GH₵{{ number_format($summary['total_profit'], 2) }}
                </td>
                <td class="number">{{ number_format($summary['average_margin'], 1) }}%</td>
                <td style="text-align: center;">{{ $sales->sum(fn($s) => $s->items->count()) }}</td>
                <td style="text-align: center;">{{ $sales->sum(fn($s) => $s->items->sum('quantity')) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p><strong>POS Supermarket System</strong> | Generated on {{ now()->format('F d, Y \a\t H:i:s') }}</p>
        <p style="margin-top: 3px;">This is a computer-generated document. No signature is required.</p>
    </div>
</body>
</html>