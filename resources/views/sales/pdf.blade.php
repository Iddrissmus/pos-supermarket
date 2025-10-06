<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; font-weight: bold; }
        .summary { margin-bottom: 20px; }
        .summary p { margin: 0 0 6px; }
    </style>
</head>
<body>
    <h1>Sales Report</h1>
    <p class="summary">
        Period: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}<br>
        Total Sales: {{ $summary['total_sales'] }}<br>
        Revenue: ${{ number_format($summary['total_revenue'], 2) }}<br>
        COGS: ${{ number_format($summary['total_cogs'], 2) }}<br>
        Gross Profit: ${{ number_format($summary['total_profit'], 2) }}<br>
        Average Margin: {{ number_format($summary['average_margin'], 2) }}%
    </p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Branch</th>
                <th>Cashier</th>
                <th>Date</th>
                <th>Revenue</th>
                <th>COGS</th>
                <th>Profit</th>
                <th>Margin %</th>
                <th>Items</th>
                <th>Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sales as $sale)
                @php
                    $cogs = $sale->items->sum('total_cost');
                    $profit = $sale->total - $cogs;
                    $margin = $sale->total > 0 ? ($profit / $sale->total) * 100 : 0;
                @endphp
                <tr>
                    <td>{{ $sale->id }}</td>
                    <td>{{ $sale->branch->name ?? 'N/A' }}</td>
                    <td>{{ $sale->cashier->name ?? 'N/A' }}</td>
                    <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                    <td>${{ number_format($sale->total, 2) }}</td>
                    <td>${{ number_format($cogs, 2) }}</td>
                    <td>${{ number_format($profit, 2) }}</td>
                    <td>{{ number_format($margin, 2) }}%</td>
                    <td>{{ $sale->items->count() }}</td>
                    <td>{{ $sale->items->sum('quantity') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>