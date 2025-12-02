@extends('layouts.app')

@section('title', 'Inventory Status Report')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-amber-600 to-yellow-600 rounded-lg shadow-lg p-6 mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Inventory Status Report</h1>
                <p class="text-amber-100">Monitor current stock levels, low stock alerts, and overstock warnings</p>
            </div>
            <a href="{{ route('product-reports.index') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Products</p>
                    <p class="text-3xl font-bold text-blue-600">{{ number_format($summary['total_products']) }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-4">
                    <i class="fas fa-boxes text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Low Stock Items</p>
                    <p class="text-3xl font-bold text-red-600">{{ number_format($summary['low_stock']) }}</p>
                </div>
                <div class="bg-red-100 rounded-full p-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Overstock Items</p>
                    <p class="text-3xl font-bold text-orange-600">{{ number_format($summary['overstock']) }}</p>
                </div>
                <div class="bg-orange-100 rounded-full p-4">
                    <i class="fas fa-layer-group text-orange-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Stock Value</p>
                    <p class="text-3xl font-bold text-green-600">GH₵ {{ number_format($summary['total_stock_value'], 0) }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-4">
                    <i class="fas fa-dollar-sign text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('product-reports.inventory') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status Filter</label>
                <select name="status" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    <option value="">All Stock Levels</option>
                    <option value="low" {{ request('status') == 'low' ? 'selected' : '' }}>Low Stock Only</option>
                    <option value="overstock" {{ request('status') == 'overstock' ? 'selected' : '' }}>Overstock Only</option>
                    <option value="normal" {{ request('status') == 'normal' ? 'selected' : '' }}>Normal Stock</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select name="category_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500">
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                    <select name="branch_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            
            <div class="flex items-end">
                <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-filter mr-2"></i>Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">
                <i class="fas fa-warehouse text-amber-500 mr-2"></i>Current Inventory Status
            </h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Product
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Branch
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Current Stock
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Reorder Level
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Stock Value
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Health
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($inventory as $item)
                        @php
                            $stockValue = $item->stock_quantity * $item->cost_price;
                            $stockPercentage = $item->reorder_level > 0 
                                ? ($item->stock_quantity / $item->reorder_level) * 100 
                                : 100;
                        @endphp
                        <tr class="hover:bg-gray-50 {{ $item->stock_status == 'low' ? 'bg-red-50' : ($item->stock_status == 'overstock' ? 'bg-orange-50' : '') }}">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $item->product_name }}</div>
                                <div class="text-xs text-gray-500">{{ $item->barcode }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $item->branch->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-semibold text-gray-900">{{ number_format($item->stock_quantity) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm text-gray-600">{{ number_format($item->reorder_level) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($item->stock_status == 'low')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        Low Stock
                                    </span>
                                @elseif($item->stock_status == 'overstock')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        <i class="fas fa-layer-group mr-1"></i>
                                        Overstock
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Normal
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-semibold text-green-600">
                                    GH₵ {{ number_format($stockValue, 2) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center justify-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2 max-w-[100px]">
                                        @php
                                            $barColor = 'bg-red-500';
                                            if ($stockPercentage > 100) $barColor = 'bg-orange-500';
                                            elseif ($stockPercentage > 50) $barColor = 'bg-green-500';
                                            elseif ($stockPercentage > 25) $barColor = 'bg-yellow-500';
                                            $barWidth = min($stockPercentage, 100);
                                        @endphp
                                        <div class="{{ $barColor }} h-2 rounded-full" style="width: {{ $barWidth }}%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3 block text-gray-300"></i>
                                No inventory data found for the selected filters
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($inventory->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $inventory->links() }}
            </div>
        @endif
    </div>

    <!-- Status Legend -->
    <div class="mt-6 bg-amber-50 border-l-4 border-amber-400 p-6 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-amber-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-amber-800 mb-2">Stock Status Guide</h3>
                <div class="text-sm text-amber-700 space-y-1">
                    <p><strong class="text-red-600">Low Stock:</strong> Current stock is at or below the reorder level. Action required.</p>
                    <p><strong class="text-orange-600">Overstock:</strong> Current stock is more than 3× the reorder level. Consider reducing orders.</p>
                    <p><strong class="text-green-600">Normal:</strong> Stock levels are within optimal range (above reorder level, below 3× reorder level).</p>
                    <p><strong>Health Bar:</strong> Visual indicator of stock level relative to reorder level (Green = Good, Yellow = Fair, Red = Critical).</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
