@extends('layouts.app')

@section('title', 'Product Performance Report')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto space-y-8">
    
    <!-- Modern Header -->
    <div class="relative bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-lg overflow-hidden">
        <div class="absolute inset-0 bg-white/10" style="background-image: radial-gradient(circle at 10% 20%, rgba(255,255,255,0.1) 0%, transparent 20%), radial-gradient(circle at 90% 80%, rgba(255,255,255,0.1) 0%, transparent 20%);"></div>
        <div class="relative p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight flex items-center">
                    <i class="fas fa-trophy mr-3 text-blue-200"></i> Performance Analysis
                </h1>
                <p class="mt-2 text-blue-100 text-lg opacity-90 max-w-2xl">
                    Detailed sales performance metrics, revenue ranking, and efficiency stats per product.
                </p>
            </div>
            <div>
                <a href="{{ route('product-reports.index') }}" class="inline-flex items-center px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors font-medium backdrop-blur-sm border border-white/10">
                    <i class="fas fa-arrow-left mr-2 opacity-80"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('product-reports.performance') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date', $dateRange['start_formatted']) }}" 
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date', $dateRange['end_formatted']) }}" 
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Category</label>
                <select name="category_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            @if(auth()->user()->role === 'business_admin' && $branches->count() > 0)
                <div>
                     <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Branch</label>
                    <select name="branch_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            
            <div class="flex items-end lg:col-span-{{ auth()->user()->role === 'business_admin' ? '1' : '2' }}">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg transition-colors font-medium text-sm flex items-center justify-center">
                    <i class="fas fa-filter mr-2"></i> Update Report
                </button>
            </div>
        </form>
    </div>

    <!-- Performance Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">
                Performance Ranking
            </h2>
            <span class="text-sm text-gray-500">Sorted by Revenue (High to Low)</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-12">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Qty Sold</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Revenue</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Cost</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Profit</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Txns</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Avg Price</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($products as $index => $product)
                        @php
                            $rank = $index + $products->firstItem();
                            $profitMargin = $product->total_revenue > 0 
                                ? (($product->total_profit / $product->total_revenue) * 100) 
                                : 0;
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $rank <= 3 ? 'bg-yellow-100 text-yellow-700 font-bold' : 'bg-gray-100 text-gray-500 font-medium' }}">
                                        {{ $rank }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-900">{{ $product->name }}</div>
                                <div class="text-xs text-gray-500 font-mono mt-0.5">{{ $product->barcode }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-semibold text-gray-900">{{ number_format($product->total_quantity_sold) }}</div>
                                <div class="text-xs text-gray-400">units</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-bold text-blue-600">GH₵ {{ number_format($product->total_revenue, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm text-gray-600">GH₵ {{ number_format($product->total_cost, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-bold {{ $product->total_profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    GH₵ {{ number_format($product->total_profit, 2) }}
                                </div>
                                <div class="text-[10px] font-semibold {{ $profitMargin >= 30 ? 'text-emerald-500' : 'text-gray-400' }}">
                                    {{ number_format($profitMargin, 1) }}% margin
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm text-gray-700">{{ number_format($product->number_of_transactions) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm text-gray-700 font-numeric">GH₵ {{ number_format($product->average_selling_price, 2) }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center text-gray-500">
                                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-search text-gray-300 text-2xl"></i>
                                </div>
                                <p class="font-medium">No sales data found</p>
                                <p class="text-sm mt-1">Try adjusting your date range or filters.</p>
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
</div>
@endsection
