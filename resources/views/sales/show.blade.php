@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
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
                                <span class="font-medium">{{ $sale->branch->name }}</span>
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
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Revenue:</span>
                                <span class="font-medium text-green-600">${{ number_format($totals['revenue'], 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total COGS:</span>
                                <span class="font-medium text-red-600">${{ number_format($totals['cogs'], 2) }}</span>
                            </div>
                            <div class="flex justify-between border-t pt-2">
                                <span class="text-gray-600 font-semibold">Gross Profit:</span>
                                <span class="font-bold text-lg {{ $totals['gross_profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    ${{ number_format($totals['gross_profit'], 2) }}
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
                                        <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                        <div class="text-sm text-gray-500">SKU: {{ $item->product->sku }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $item->quantity }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${{ number_format($item->price, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${{ number_format($item->unit_cost ?? 0, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">${{ number_format($item->total, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${{ number_format($item->total_cost ?? 0, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium {{ ($item->gross_margin ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            ${{ number_format($item->gross_margin ?? 0, 2) }}
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
                                        ${{ number_format($sale->total, 2) }}
                                    </td>
                                    <td class="px-6 py-3 text-sm font-bold text-gray-900">
                                        ${{ number_format($sale->items->sum('total_cost'), 2) }}
                                    </td>
                                    <td class="px-6 py-3 text-sm font-bold {{ $totals['gross_profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        ${{ number_format($totals['gross_profit'], 2) }}
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
                <div class="mt-6 flex justify-end space-x-4">
                    <a href="{{ route('sales.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                        <i class="fas fa-plus mr-2"></i>New Sale
                    </a>
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
    .no-print {
        display: none !important;
    }
    body {
        background: white !important;
    }
}
</style>
@endsection