@extends('layouts.app')

@section('title', 'Inventory Management')

@push('styles')
<style>
    .custom-scrollbar::-webkit-scrollbar {
        height: 6px;
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1; 
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #d1d5db; 
        border-radius: 3px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #9ca3af; 
    }
</style>
@endpush

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto space-y-8">
    
    @php
        $lowStockOnly = $lowStockOnly ?? request()->boolean('low_stock');
        $inStoreOnly = $inStoreOnly ?? request()->boolean('in_store');
        $outOfStockOnly = $outOfStockOnly ?? request()->boolean('out_of_stock');
        $activeFilterCount = ($lowStockOnly ? 1 : 0) + ($inStoreOnly ? 1 : 0) + ($outOfStockOnly ? 1 : 0) + ($selectedCategory ? 1 : 0);
    @endphp

    <!-- Notifications -->
    <div id="notification-container" class="fixed top-4 right-4 z-50 space-y-2 pointer-events-none"></div>
    @if (session('success')) <div class="hidden" id="flash-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="hidden" id="flash-error">{{ session('error') }}</div> @endif

    <!-- Modern Header -->
    <div class="relative bg-gradient-to-r from-blue-700 to-indigo-800 rounded-xl shadow-lg overflow-hidden">
        <div class="absolute inset-0 bg-white/10" style="background-image: radial-gradient(circle at 10% 20%, rgba(255,255,255,0.1) 0%, transparent 20%), radial-gradient(circle at 90% 80%, rgba(255,255,255,0.1) 0%, transparent 20%);"></div>
        <div class="relative p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight flex items-center">
                    <i class="fas fa-boxes mr-3 text-blue-200"></i> Warehouse Inventory
                </h1>
                <p class="mt-2 text-blue-100 text-lg opacity-90 max-w-2xl">
                    Manage your product catalog, track stock levels, and organize inventory across all branches.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                 <a href="{{ route('layouts.productman') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors font-medium backdrop-blur-sm border border-white/10 flex items-center">
                    <i class="fas fa-store mr-2 opacity-80"></i> Branch Stock
                </a>
                <a href="{{ route('product.create') }}" class="px-4 py-2 bg-white text-blue-700 hover:bg-blue-50 rounded-lg transition-colors font-bold shadow-sm flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add Product
                </a>
                <div class="relative group" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false" class="px-3 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors backdrop-blur-sm border border-white/10">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div x-show="open" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl z-50 py-1 border border-gray-100" style="display: none;">
                        <a href="{{ route('inventory.bulk-import') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-file-excel mr-2 text-green-600"></i> Bulk Import
                        </a>
                        <a href="{{ route('inventory.assign') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-hand-pointer mr-2 text-purple-600"></i> Assign Products
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Value -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-blue-200 transition-colors">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Value (Sales)</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-gray-900">GH₵{{ number_format($financialMetrics['total_selling_price'], 2) }}</h3>
                </div>
            </div>
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-tags text-xl"></i>
            </div>
        </div>

        <!-- Total Cost -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-orange-200 transition-colors">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Cost</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-gray-900">GH₵{{ number_format($financialMetrics['total_cost_price'], 2) }}</h3>
                </div>
            </div>
            <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-coins text-xl"></i>
            </div>
        </div>

        <!-- Potential Profit -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-green-200 transition-colors">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Potential Profit</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-gray-900">GH₵{{ number_format($financialMetrics['total_margin'], 2) }}</h3>
                </div>
            </div>
            <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-chart-line text-xl"></i>
            </div>
        </div>

        <!-- Margin % -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-purple-200 transition-colors">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Avg Margin</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($financialMetrics['margin_percentage'], 1) }}%</h3>
                </div>
            </div>
            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-percent text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Inventory Status Bar -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
         <a href="{{ route('layouts.product') }}" class="bg-white rounded-xl p-4 border {{ !$lowStockOnly && !$inStoreOnly && !$outOfStockOnly ? 'border-blue-500 ring-1 ring-blue-200' : 'border-gray-200 hover:border-blue-300' }} shadow-sm flex items-center gap-3 transition-all">
            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                <i class="fas fa-cubes"></i>
            </div>
            <div>
                <span class="block text-2xl font-bold text-gray-900">{{$stats['total_products'] ?? 0}}</span>
                <span class="text-xs text-gray-500 font-medium uppercase">All Products</span>
            </div>
        </a>

        <a href="{{ route('products.in-store') }}" class="bg-white rounded-xl p-4 border {{ $inStoreOnly ? 'border-green-500 ring-1 ring-green-200' : 'border-gray-200 hover:border-green-300' }} shadow-sm flex items-center gap-3 transition-all">
            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                <i class="fas fa-check"></i>
            </div>
             <div>
                <span class="block text-2xl font-bold text-gray-900">{{$stats['in_store_products'] ?? 0}}</span>
                <span class="text-xs text-gray-500 font-medium uppercase">In Stock</span>
            </div>
        </a>

        <a href="{{ route('products.low-stock') }}" class="bg-white rounded-xl p-4 border {{ $lowStockOnly ? 'border-orange-500 ring-1 ring-orange-200' : 'border-gray-200 hover:border-orange-300' }} shadow-sm flex items-center gap-3 transition-all">
            <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-600">
                <i class="fas fa-exclamation"></i>
            </div>
             <div>
                <span class="block text-2xl font-bold text-gray-900">{{$stats['low_stock_products'] ?? 0}}</span>
                <span class="text-xs text-gray-500 font-medium uppercase">Low Stock</span>
            </div>
        </a>

        <a href="{{ route('products.out-of-stock') }}" class="bg-white rounded-xl p-4 border {{ $outOfStockOnly ? 'border-red-500 ring-1 ring-red-200' : 'border-gray-200 hover:border-red-300' }} shadow-sm flex items-center gap-3 transition-all">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                <i class="fas fa-times"></i>
            </div>
             <div>
                <span class="block text-2xl font-bold text-gray-900">{{$stats['out_of_stock_products'] ?? 0}}</span>
                <span class="text-xs text-gray-500 font-medium uppercase">Out of Stock</span>
            </div>
        </a>
    </div>

    <!-- Main Content Area -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        
        <!-- Toolbar & Filters -->
        <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex flex-col lg:flex-row gap-4 justify-between items-center">
            
            <!-- Search -->
            <div class="relative w-full lg:w-96">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" id="tableSearch" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out" placeholder="Search products by name, barcode or SKU...">
            </div>

            <!-- Category Filter Actions -->
            <div class="w-full lg:w-auto overflow-x-auto pb-1 lg:pb-0">
                 <form method="GET" action="{{ route('layouts.product') }}" id="categoryButtonForm" class="flex items-center gap-2">
                    <input type="hidden" name="category_id" id="category_id_input" value="{{ $selectedCategory }}">
                    <button type="button" onclick="filterByCategory('')" class="whitespace-nowrap px-3 py-1.5 rounded-full text-xs font-medium border {{ !$selectedCategory ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-600 border-gray-300 hover:border-gray-400' }} transition-colors">
                        All
                    </button>
                    @foreach($categories as $cat)
                        <button type="button" onclick="filterByCategory('{{ $cat->id }}')" class="whitespace-nowrap px-3 py-1.5 rounded-full text-xs font-medium border flex items-center gap-1.5 {{ $selectedCategory == $cat->id ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300 hover:border-gray-400' }} transition-colors">
                            @if($cat->icon) <i class="fas {{ $cat->icon }}"></i> @endif
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </form>
            </div>
        </div>
        
        <!-- Active Filter Indicator -->
        @if($activeFilterCount > 0)
        <div class="px-5 py-2 bg-blue-50 border-b border-blue-100 flex items-center justify-between">
            <div class="flex items-center gap-2 text-sm text-blue-700">
                <i class="fas fa-filter"></i>
                <span class="font-medium">Active Filters:</span>
                @if($selectedCategory)
                    @php $catName = $categories->firstWhere('id', $selectedCategory)->name ?? 'Category'; @endphp
                    <span class="px-2 py-0.5 bg-white rounded text-xs border border-blue-200">Category: {{ $catName }}</span>
                @endif
                @if($lowStockOnly) <span class="px-2 py-0.5 bg-white rounded text-xs border border-blue-200">Low Stock</span> @endif
                @if($inStoreOnly) <span class="px-2 py-0.5 bg-white rounded text-xs border border-blue-200">In Store</span> @endif
                @if($outOfStockOnly) <span class="px-2 py-0.5 bg-white rounded text-xs border border-blue-200">Out of Stock</span> @endif
            </div>
            <a href="{{ route('layouts.product') }}" class="text-xs font-medium text-blue-600 hover:text-blue-800 hover:underline">Clear All</a>
        </div>
        @endif

        <!-- Product Table -->
        <div class="overflow-x-auto custom-scrollbar">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Info</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price (GH₵)</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cost (GH₵)</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $item)
                        @php
                            $product = $item->product ?? $item;
                            $branchProduct = $item->product ? $item : null;
                            $stockQty = $branchProduct->stock_quantity ?? $product->stock ?? 0;
                            $price = $branchProduct->price ?? $product->price ?? 0;
                            $cost = $branchProduct->cost_price ?? $product->cost_price ?? 0;
                            
                            $stockStatus = 'In Stock';
                            $stockColor = 'green';
                            if ($stockQty <= 0) {
                                $stockStatus = 'Out of Stock';
                                $stockColor = 'red';
                            } elseif ($stockQty <= 10) { // Assuming 10 is low stock threshold
                                $stockStatus = 'Low Stock';
                                $stockColor = 'orange';
                            }
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400">
                                         @if($product->image)
                                            <img class="h-10 w-10 rounded-lg object-cover" src="{{ asset('storage/'.$product->image) }}" alt="">
                                         @else
                                            <i class="fas fa-box"></i>
                                         @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 group-hover:text-blue-600 transition-colors">
                                            <a href="{{ route('products.show', $product->id) }}">{{ $product->name }}</a>
                                        </div>
                                        <div class="text-xs text-gray-500 font-mono">{{ $product->barcode ?? 'No Barcode' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($product->category)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $product->category->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 italic">Uncategorized</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                                {{ number_format($price, 2) }}
                            </td>
                             <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                {{ number_format($cost, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $stockColor }}-100 text-{{ $stockColor }}-800">
                                    {{ $stockQty }} Units
                                </span>
                                @if($stockStatus !== 'In Stock')
                                    <div class="text-[10px] text-{{ $stockColor }}-600 mt-1 font-medium uppercase tracking-wide">{{ $stockStatus }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('products.show', $product->id) }}" class="text-blue-600 hover:text-blue-900 transition-colors mr-3" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                {{-- Add Edit/Delete actions if needed, sticking to view for now --}}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-search text-gray-400 text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">No products found</h3>
                                <p class="mt-1">Try adjusting your search or filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($products->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $products->links() }}
        </div>
        @endif
    </div>

</div>

<!-- Alpine.js for dropdowns -->
<script src="//unpkg.com/alpinejs" defer></script>

<script>
    // Search Functionality
    document.getElementById('tableSearch').addEventListener('keyup', function() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("tableSearch");
        filter = input.value.toUpperCase();
        table = document.querySelector("table");
        tr = table.getElementsByTagName("tr");
        
        for (i = 0; i < tr.length; i++) {
            // Check Name (Index 0/td inside) and Barcode
            // Actually, name is in td index 0, barcode inside same td
            // Let's just search all text in the row for simplicity or target specific columns
            if (tr[i].getElementsByTagName("td").length > 0) {
                txtValue = tr[i].textContent || tr[i].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    });

    function filterByCategory(id) {
        document.getElementById('category_id_input').value = id;
        document.getElementById('categoryButtonForm').submit();
    }
    
    // Notification logic
    const successMsg = document.getElementById('flash-success');
    const errorMsg = document.getElementById('flash-error');
    
    if(successMsg) showNotification(successMsg.innerText, 'success');
    if(errorMsg) showNotification(errorMsg.innerText, 'error');

    function showNotification(message, type = 'success') {
        const container = document.getElementById('notification-container');
        const notif = document.createElement('div');
        notif.className = `transform transition-all duration-300 ease-out translate-x-full opacity-0 flex items-center p-4 mb-4 text-sm text-white rounded-lg shadow-lg ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
        notif.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
            <span>${message}</span>
        `;
        
        container.appendChild(notif);
        
        // Animate in
        requestAnimationFrame(() => {
            notif.classList.remove('translate-x-full', 'opacity-0');
        });
        
        // Remove after 3s
        setTimeout(() => {
            notif.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => notif.remove(), 300);
        }, 4000);
    }
</script>
@endsection