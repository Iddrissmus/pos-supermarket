@extends('layouts.app')

@section('title', 'Inventory Status Report')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto space-y-8">
    
    <!-- Modern Header -->
    <div class="relative bg-gradient-to-r from-amber-500 to-orange-600 rounded-xl shadow-lg overflow-hidden">
        <div class="absolute inset-0 bg-white/10" style="background-image: radial-gradient(circle at 10% 20%, rgba(255,255,255,0.1) 0%, transparent 20%), radial-gradient(circle at 90% 80%, rgba(255,255,255,0.1) 0%, transparent 20%);"></div>
        <div class="relative p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight flex items-center">
                    <i class="fas fa-warehouse mr-3 text-amber-100"></i> Inventory Health
                </h1>
                <p class="mt-2 text-amber-50 text-lg opacity-90 max-w-2xl">
                    Live monitoring of stock levels, identification of low stock risks, and overstock analysis.
                </p>
            </div>
            <div>
                <a href="{{ route('product-reports.index') }}" class="inline-flex items-center px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors font-medium backdrop-blur-sm border border-white/10">
                    <i class="fas fa-arrow-left mr-2 opacity-80"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total SKU Count</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($summary['total_products']) }}</h3>
            </div>
            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center text-gray-600">
                <i class="fas fa-boxes text-xl"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-red-100 p-6 flex items-center justify-between bg-red-50/50">
            <div>
                <p class="text-xs font-bold text-red-400 uppercase tracking-widest">Low Stock Alerts</p>
                <h3 class="text-3xl font-bold text-red-600 mt-1">{{ number_format($summary['low_stock']) }}</h3>
            </div>
             <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center text-red-600">
                <i class="fas fa-exclamation-triangle text-xl"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-orange-100 p-6 flex items-center justify-between bg-orange-50/50">
            <div>
                <p class="text-xs font-bold text-orange-400 uppercase tracking-widest">Overstock Items</p>
                <h3 class="text-3xl font-bold text-orange-600 mt-1">{{ number_format($summary['overstock']) }}</h3>
            </div>
             <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center text-orange-600">
                <i class="fas fa-layer-group text-xl"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-green-100 p-6 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-emerald-500 uppercase tracking-widest">Total Stock Value</p>
                <h3 class="text-3xl font-bold text-emerald-600 mt-1">GH₵ {{ number_format($summary['total_stock_value'], 0) }}</h3>
            </div>
             <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600">
                <i class="fas fa-money-bill-wave text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('product-reports.inventory') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
             <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Stock Status</label>
                <select name="status" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                    <option value="">All Levels</option>
                    <option value="low" {{ request('status') == 'low' ? 'selected' : '' }}>Low Stock Only</option>
                    <option value="overstock" {{ request('status') == 'overstock' ? 'selected' : '' }}>Overstock Only</option>
                    <option value="normal" {{ request('status') == 'normal' ? 'selected' : '' }}>Normal Stock</option>
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Category</label>
                <select name="category_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
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
                    <select name="branch_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            
            <div class="flex items-end lg:col-span-1">
                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white px-4 py-2.5 rounded-lg transition-colors font-medium text-sm flex items-center justify-center">
                    <i class="fas fa-filter mr-2"></i> Filter Inventory
                </button>
            </div>
        </form>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
             <h2 class="text-lg font-bold text-gray-800">
                Detailed Stock List
            </h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Reorder</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Value</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Health</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($inventory as $item)
                        @php
                            $stockValue = $item->stock_value ?? ($item->stock_quantity * ($item->effective_cost_price ?? 0));
                            $stockPercentage = $item->stock_percentage ?? (
                                $item->reorder_level > 0 
                                ? ($item->stock_quantity / $item->reorder_level) * 100 
                                : 0
                            );
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition-colors {{ $item->stock_status == 'low' ? 'bg-red-50/30' : ($item->stock_status == 'overstock' ? 'bg-orange-50/30' : '') }}">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-900">{{ $item->product_name }}</div>
                                <div class="text-xs text-gray-500 font-mono">{{ $item->barcode }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-700">{{ $item->branch->display_label }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-bold text-gray-900">{{ number_format($item->stock_quantity) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm text-gray-500">{{ number_format($item->reorder_level) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($item->stock_status == 'low')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                                        Low Stock
                                    </span>
                                @elseif($item->stock_status == 'overstock')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-800 border border-orange-200">
                                        Overstock
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Normal
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-bold text-emerald-600">GH₵ {{ number_format($stockValue, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                     @php
                                        $barColor = 'bg-red-500';
                                        if ($stockPercentage > 300) $barColor = 'bg-orange-500'; // Way overstocked
                                        elseif ($stockPercentage >= 100) $barColor = 'bg-emerald-500'; // Healthy
                                        elseif ($stockPercentage >= 50) $barColor = 'bg-yellow-500'; // Getting low
                                        else $barColor = 'bg-red-500'; // Critical
                                        
                                        $displayPercentage = min($stockPercentage, 100);
                                    @endphp
                                    <div class="{{ $barColor }} h-1.5 rounded-full" style="width: {{ $displayPercentage }}%"></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-gray-500">
                                <i class="fas fa-box-open text-3xl mb-3 block text-gray-300"></i>
                                No inventory records match your filter
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($inventory->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $inventory->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
