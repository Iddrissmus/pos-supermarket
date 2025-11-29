@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md">
            <!-- Print-only receipt header -->
            <div class="receipt-header" style="display: none;">
                <h1 style="font-size: 18pt; font-weight: bold; margin: 0;">{{ optional($sale->branch)->name ?? 'POS System' }}</h1>
                <p style="margin: 2px 0; font-size: 10pt;">{{ optional($sale->branch)->address ?? 'Address Not Available' }}</p>
                <p style="margin: 2px 0; font-size: 10pt;">Tel: {{ optional($sale->branch)->phone ?? 'N/A' }}</p>
                <p style="margin: 5px 0 0 0; font-size: 11pt; font-weight: bold;">SALES RECEIPT</p>
            </div>

            <div class="p-6 border-b border-gray-200 no-print">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-receipt mr-3 text-green-600"></i>Sale #{{ $sale->id }}
                    </h1>
                    <div class="flex space-x-4">
                        <a href="{{ route('sales.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Sales
                        </a>
                        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-print mr-2"></i>Print Receipt
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <!-- Sale Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Sale Information</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Sale ID:</span>
                                <span class="font-medium">#{{ $sale->id }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Date:</span>
                                <span class="font-medium">{{ $sale->created_at->format('M d, Y h:i A') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Branch:</span>
                                <span class="font-medium">{{ optional($sale->branch)->display_label ?? 'Unassigned branch' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Cashier:</span>
                                <span class="font-medium">{{ $sale->cashier->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Payment Method:</span>
                                <span class="font-medium capitalize">{{ str_replace('_', ' ', $sale->payment_method) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Financial Summary</h3>
                        <div class="space-y-2">
                            @if(isset($totals['subtotal']))
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-medium text-blue-600">₵{{ number_format($totals['subtotal'], 2) }}</span>
                                </div>
                                @if(isset($totals['tax_components']) && is_array($totals['tax_components']) && !empty($totals['tax_components']))
                                    @foreach($totals['tax_components'] as $taxComponent)
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">{{ $taxComponent['name'] ?? 'Tax' }} ({{ number_format($taxComponent['rate'] ?? 0, 1) }}%):</span>
                                            <span class="font-medium text-orange-600">₵{{ number_format($taxComponent['amount'] ?? 0, 2) }}</span>
                                        </div>
                                    @endforeach
                                @endif
                                @if(isset($totals['tax_amount']) && $totals['tax_amount'] > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Total Tax:</span>
                                        <span class="font-medium text-orange-600">₵{{ number_format($totals['tax_amount'], 2) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <span class="text-gray-600 font-semibold">Total Revenue (incl. tax):</span>
                                    <span class="font-bold text-lg text-green-600">₵{{ number_format($totals['total'] ?? 0, 2) }}</span>
                                </div>
                            @else
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total Revenue:</span>
                                    <span class="font-medium text-green-600">₵{{ number_format($totals['total'] ?? $totals['revenue'] ?? 0, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total COGS:</span>
                                <span class="font-medium text-red-600">₵{{ number_format($totals['cogs'], 2) }}</span>
                            </div>
                            <div class="flex justify-between border-t pt-2">
                                <span class="text-gray-600 font-semibold">Gross Profit:</span>
                                <span class="font-bold text-lg {{ $totals['gross_profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    ₵{{ number_format($totals['gross_profit'], 2) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Margin:</span>
                                <span class="font-medium {{ $totals['margin_percent'] >= 30 ? 'text-green-600' : ($totals['margin_percent'] >= 15 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ number_format($totals['margin_percent'], 1) }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sale Items -->
                <div class="border border-gray-200 rounded-lg">
                    <div class="p-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Sale Items</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Product
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Quantity
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Unit Price
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Unit Cost
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Revenue
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        COGS
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Profit
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Margin %
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($sale->items as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">{{ $item->product->name }}</div>
                                        <div class="text-sm text-gray-500">Barcode: {{ $item->product->barcode }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $item->quantity }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">₵{{ number_format($item->price, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">₵{{ number_format($item->unit_cost ?? 0, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">₵{{ number_format($item->total, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">₵{{ number_format($item->total_cost ?? 0, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium {{ ($item->gross_margin ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            ₵{{ number_format($item->gross_margin ?? 0, 2) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            @if(($item->margin_percent ?? 0) >= 30) bg-green-100 text-green-800
                                            @elseif(($item->margin_percent ?? 0) >= 15) bg-yellow-100 text-yellow-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ number_format($item->margin_percent ?? 0, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-900">
                                        Totals:
                                    </td>
                                    <td class="px-6 py-3 text-sm font-bold text-gray-900">
                                        ₵{{ number_format($sale->total, 2) }}
                                    </td>
                                    <td class="px-6 py-3 text-sm font-bold text-gray-900">
                                        ₵{{ number_format($sale->items->sum('total_cost'), 2) }}
                                    </td>
                                    <td class="px-6 py-3 text-sm font-bold {{ $totals['gross_profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        ₵{{ number_format($totals['gross_profit'], 2) }}
                                    </td>
                                    <td class="px-6 py-3 text-sm font-bold text-gray-900">
                                        {{ number_format($totals['margin_percent'], 1) }}%
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 flex justify-end space-x-4 no-print">
                    @if(auth()->user()->role === 'cashier')
                        <a href="{{ route('sales.terminal') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                            <i class="fas fa-cash-register mr-2"></i>Go to Terminal
                        </a>
                    @endif
                    <a href="{{ route('sales.report') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                        <i class="fas fa-chart-bar mr-2"></i>Sales Report
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    /* Hide all non-essential elements */
    .no-print,
    nav,
    aside,
    header,
    .sidebar,
    button:not(.print-show),
    a[href]:not(.print-show) {
        display: none !important;
    }
    
    /* Show receipt header only in print */
    .receipt-header {
        display: block !important;
    }
    
    /* Reset body and page */
    body {
        background: white !important;
        margin: 0;
        padding: 0;
        font-family: 'Courier New', monospace;
    }
    
    .min-h-screen {
        min-height: auto !important;
        padding: 0 !important;
        background: white !important;
    }
    
    .max-w-4xl {
        max-width: 80mm !important; /* Standard receipt width */
        margin: 0 auto !important;
        padding: 10mm !important;
    }
    
    /* Receipt styling */
    .bg-white {
        box-shadow: none !important;
        border: none !important;
    }
    
    .rounded-lg,
    .rounded {
        border-radius: 0 !important;
    }
    
    /* Receipt header */
    .receipt-header {
        text-align: center;
        border-bottom: 2px dashed #000;
        padding-bottom: 10px;
        margin-bottom: 10px;
    }
    
    /* Make text smaller and more compact */
    * {
        font-size: 11pt !important;
        line-height: 1.3 !important;
        color: #000 !important;
    }
    
    h1, h2, h3 {
        font-size: 14pt !important;
        font-weight: bold !important;
        margin: 5px 0 !important;
    }
    
    /* Remove colors and backgrounds */
    .bg-gray-50,
    .bg-gray-100,
    .bg-blue-50,
    .bg-green-50 {
        background: white !important;
        border: none !important;
    }
    
    .text-blue-600,
    .text-green-600,
    .text-red-600,
    .text-orange-600,
    .text-gray-600,
    .text-gray-800 {
        color: #000 !important;
    }
    
    /* Table styling */
    table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin: 10px 0 !important;
    }
    
    thead {
        border-bottom: 1px solid #000 !important;
    }
    
    th, td {
        padding: 4px 2px !important;
        text-align: left !important;
        border: none !important;
    }
    
    th {
        font-weight: bold !important;
    }
    
    tbody tr {
        border-bottom: 1px dotted #999 !important;
    }
    
    /* Receipt sections */
    .border-t {
        border-top: 2px dashed #000 !important;
        margin-top: 10px !important;
        padding-top: 10px !important;
    }
    
    .grid {
        display: block !important;
    }
    
    .grid > div {
        margin-bottom: 10px !important;
        page-break-inside: avoid !important;
    }
    
    /* Financial summary emphasis */
    .font-bold {
        font-weight: bold !important;
    }
    
    .text-lg {
        font-size: 13pt !important;
    }
    
    /* Remove padding and margins from containers */
    .p-6, .p-4, .px-4, .py-2 {
        padding: 0 !important;
    }
    
    .m-6, .mb-6, .mt-6 {
        margin: 5px 0 !important;
    }
    
    /* Receipt footer */
    .receipt-footer {
        text-align: center;
        border-top: 2px dashed #000;
        padding-top: 10px;
        margin-top: 10px;
        font-size: 10pt !important;
    }
    
    /* Hide icons in print */
    i.fas, i.fa {
        display: none !important;
    }
    
    /* Page breaks */
    .page-break {
        page-break-after: always;
    }
    
    /* Ensure single page */
    @page {
        size: 80mm auto;
        margin: 5mm;
    }
}
</style>
@endsection