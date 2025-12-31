@extends('layouts.app')

@section('title', 'Product Profitability Analysis')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto space-y-8">
    
    <!-- Modern Header -->
    <div class="relative bg-gradient-to-r from-emerald-600 to-green-700 rounded-xl shadow-lg overflow-hidden">
        <div class="absolute inset-0 bg-white/10" style="background-image: radial-gradient(circle at 10% 20%, rgba(255,255,255,0.1) 0%, transparent 20%), radial-gradient(circle at 90% 80%, rgba(255,255,255,0.1) 0%, transparent 20%);"></div>
        <div class="relative p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight flex items-center">
                    <i class="fas fa-chart-line mr-3 text-emerald-200"></i> Profitability Analysis
                </h1>
                <p class="mt-2 text-emerald-100 text-lg opacity-90 max-w-2xl">
                    Deep dive into profit margins, ROI, and cost efficiency across your product catalog.
                </p>
            </div>
            <div>
                <a href="{{ route('product-reports.index') }}" class="inline-flex items-center px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors font-medium backdrop-blur-sm border border-white/10">
                    <i class="fas fa-arrow-left mr-2 opacity-80"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Overall Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col items-center text-center hover:border-blue-200 transition-colors group">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <i class="fas fa-dollar-sign text-xl"></i>
            </div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Revenue</p>
            <h3 class="text-3xl font-bold text-blue-600 mt-2">GH₵ {{ number_format($overallMetrics->total_revenue ?? 0, 2) }}</h3>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col items-center text-center hover:border-orange-200 transition-colors group">
             <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <i class="fas fa-coins text-xl"></i>
            </div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Cost of Good Sold</p>
            <h3 class="text-3xl font-bold text-orange-600 mt-2">GH₵ {{ number_format($overallMetrics->total_cost ?? 0, 2) }}</h3>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col items-center text-center hover:border-emerald-200 transition-colors group">
             <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <i class="fas fa-chart-pie text-xl"></i>
            </div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Gross Profit</p>
            <h3 class="text-3xl font-bold text-emerald-600 mt-2">GH₵ {{ number_format($overallMetrics->total_profit ?? 0, 2) }}</h3>
             @php
                $overallMargin = ($overallMetrics && $overallMetrics->total_revenue > 0) 
                    ? (($overallMetrics->total_profit / $overallMetrics->total_revenue) * 100) 
                    : 0;
            @endphp
            <div class="mt-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800">
                {{ number_format($overallMargin, 1) }}% Margin
            </div>
        </div>
    </div>

    <!-- Profitability Ranking Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">
                Product Profit Ranking
            </h2>
            <span class="text-sm text-gray-500">Sorted by Profit Margin (High to Low)</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-12">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Revenue</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Cost</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Profit</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Margin %</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">ROI %</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Vol</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($products as $index => $product)
                        @php
                            $rank = $index + $products->firstItem();
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-500">#{{ $rank }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-900">{{ $product->name }}</div>
                                <div class="text-xs text-gray-500 font-mono">{{ $product->barcode }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-medium text-blue-600">GH₵{{ number_format($product->total_revenue, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-medium text-gray-500">GH₵{{ number_format($product->total_cost, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-bold {{ $product->total_profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    GH₵{{ number_format($product->total_profit, 2) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                @php
                                    $marginColor = 'bg-gray-100 text-gray-600';
                                    if ($product->profit_margin >= 30) $marginColor = 'bg-emerald-100 text-emerald-800';
                                    elseif ($product->profit_margin >= 15) $marginColor = 'bg-blue-100 text-blue-800';
                                    elseif ($product->profit_margin >= 5) $marginColor = 'bg-yellow-100 text-yellow-800';
                                    elseif ($product->profit_margin < 0) $marginColor = 'bg-red-100 text-red-800';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ $marginColor }}">
                                    {{ number_format($product->profit_margin, 1) }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-bold {{ $product->roi_percentage >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                                    {{ number_format($product->roi_percentage, 1) }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm text-gray-700">{{ number_format($product->total_quantity_sold) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center text-gray-500">
                                <i class="fas fa-inbox text-3xl mb-3 block text-gray-300"></i>
                                No data available for ranking
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($products->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- Education Card -->
    <div class="bg-blue-50 border border-blue-100 rounded-xl p-6 flex flex-col sm:flex-row gap-4">
        <div class="flex-shrink-0">
             <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                <i class="fas fa-info"></i>
            </div>
        </div>
        <div>
            <h3 class="text-sm font-bold text-blue-900 uppercase tracking-wide mb-2">Profitability Guide</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800">
                <div>
                    <strong class="block mb-1">Profit Margin %</strong>
                    Calculated as <code class="bg-white/50 px-1 rounded text-xs font-mono">Profit / Revenue × 100</code>. Indicates how much of every cedi of sales you keep as earnings.
                </div>
                <div>
                     <strong class="block mb-1">Return on Investment (ROI) %</strong>
                    Calculated as <code class="bg-white/50 px-1 rounded text-xs font-mono">Profit / Cost × 100</code>. Measures the efficiency of inventory investment.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
