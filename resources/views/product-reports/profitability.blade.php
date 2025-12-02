@extends('layouts.app')

@section('title', 'Product Profitability Analysis')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-emerald-600 to-green-600 rounded-lg shadow-lg p-6 mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Profitability Analysis</h1>
                <p class="text-emerald-100">Analyze profit margins, ROI, and cost efficiency by product</p>
            </div>
            <a href="{{ route('product-reports.index') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Overall Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Revenue</p>
                    <p class="text-3xl font-bold text-blue-600">GH₵ {{ number_format($overallMetrics->total_revenue ?? 0, 2) }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-4">
                    <i class="fas fa-dollar-sign text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Cost</p>
                    <p class="text-3xl font-bold text-orange-600">GH₵ {{ number_format($overallMetrics->total_cost ?? 0, 2) }}</p>
                </div>
                <div class="bg-orange-100 rounded-full p-4">
                    <i class="fas fa-coins text-orange-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Profit</p>
                    <p class="text-3xl font-bold text-green-600">GH₵ {{ number_format($overallMetrics->total_profit ?? 0, 2) }}</p>
                    @php
                        $overallMargin = ($overallMetrics && $overallMetrics->total_revenue > 0) 
                            ? (($overallMetrics->total_profit / $overallMetrics->total_revenue) * 100) 
                            : 0;
                    @endphp
                    <p class="text-sm text-gray-500 mt-1">{{ number_format($overallMargin, 1) }}% margin</p>
                </div>
                <div class="bg-green-100 rounded-full p-4">
                    <i class="fas fa-chart-line text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Profitability Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">
                <i class="fas fa-percentage text-green-500 mr-2"></i>Product Profitability Rankings
            </h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Rank
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Product
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Revenue
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cost
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Profit
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Profit Margin %
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            ROI %
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Units Sold
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $index => $product)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($index + $products->firstItem() == 1)
                                        <span class="text-2xl">🏆</span>
                                    @elseif($index + $products->firstItem() <= 3)
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-600 font-bold text-sm">
                                            {{ $index + $products->firstItem() }}
                                        </span>
                                    @else
                                        <span class="text-gray-500 font-semibold">{{ $index + $products->firstItem() }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                <div class="text-xs text-gray-500">{{ $product->barcode }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-semibold text-blue-600">
                                    GH₵ {{ number_format($product->total_revenue, 2) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm text-gray-600">
                                    GH₵ {{ number_format($product->total_cost, 2) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-semibold {{ $product->total_profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    GH₵ {{ number_format($product->total_profit, 2) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                @php
                                    $marginColor = 'text-gray-600';
                                    if ($product->profit_margin >= 30) $marginColor = 'text-green-600';
                                    elseif ($product->profit_margin >= 15) $marginColor = 'text-blue-600';
                                    elseif ($product->profit_margin >= 5) $marginColor = 'text-yellow-600';
                                    elseif ($product->profit_margin < 0) $marginColor = 'text-red-600';
                                @endphp
                                <div class="flex items-center justify-end">
                                    <span class="text-sm font-bold {{ $marginColor }}">
                                        {{ number_format($product->profit_margin, 2) }}%
                                    </span>
                                    @if($product->profit_margin >= 30)
                                        <span class="ml-2 text-green-500">⭐</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-400">
                                    @if($product->profit_margin >= 30)
                                        Excellent
                                    @elseif($product->profit_margin >= 15)
                                        Good
                                    @elseif($product->profit_margin >= 5)
                                        Fair
                                    @else
                                        Low
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-semibold {{ $product->roi_percentage >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($product->roi_percentage, 2) }}%
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm text-gray-600">
                                    {{ number_format($product->total_quantity_sold) }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3 block text-gray-300"></i>
                                No profitability data available for the selected period
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($products->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- Profitability Guide -->
    <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-6 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 mb-2">Understanding Profitability Metrics</h3>
                <div class="text-sm text-blue-700 space-y-1">
                    <p><strong>Profit Margin:</strong> Shows what percentage of revenue is profit. Higher is better. (Formula: Profit / Revenue × 100)</p>
                    <p><strong>ROI (Return on Investment):</strong> Shows return relative to cost. Higher ROI means better returns. (Formula: Profit / Cost × 100)</p>
                    <p><strong>Rating Guide:</strong> Excellent (≥30%), Good (15-29%), Fair (5-14%), Low (<5%)</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
