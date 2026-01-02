@extends('layouts.app')

@section('title', 'Warehouse Inventory')

@section('content')
<div class="space-y-8">
    
    <!-- Hero Section -->
    <div class="rounded-2xl bg-indigo-600 p-8 text-white shadow-xl relative overflow-hidden">
        <!-- Abstract Background Pattern -->
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 rounded-full bg-indigo-500 opacity-30 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-64 h-64 rounded-full bg-blue-500 opacity-30 blur-3xl"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-indigo-500/50 rounded-lg backdrop-blur-sm">
                        <i class="fas fa-cubes text-2xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold tracking-tight">Warehouse Inventory</h1>
                </div>
                <p class="text-indigo-100 max-w-xl text-lg opacity-90">
                    Manage your product catalog, track stock levels, and organize inventory across all branches.
                </p>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('layouts.product') }}" class="px-5 py-2.5 bg-white/10 hover:bg-white/20 border border-white/20 text-white rounded-xl transition-all font-semibold backdrop-blur-sm flex items-center">
                    <i class="fas fa-store-alt mr-2"></i> Branch Stock
                </a>
                <div class="relative group">
                    <button class="px-5 py-2.5 bg-white text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all font-bold shadow-lg shadow-indigo-900/20 flex items-center">
                        <i class="fas fa-plus mr-2"></i> Add Product
                    </button>
                     <!-- Dropdown for Add Product -->
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden hidden group-hover:block z-50 animate-fade-in-up">
                        <a href="{{ route('products.create') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                            <i class="fas fa-box mr-2"></i> New Product
                        </a>
                        <a href="{{ route('inventory.assign') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors border-t border-gray-50">
                            <i class="fas fa-dolly mr-2"></i> Assign Stock
                        </a>
                    </div>
                </div>
                <button class="p-2.5 bg-white/10 hover:bg-white/20 border border-white/20 text-white rounded-xl transition-all backdrop-blur-sm">
                    <i class="fas fa-ellipsis-v w-5 h-5 flex items-center justify-center"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Grid Row 1: Financials -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Sales Value -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Value (Sales)</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">₵{{ number_format($stats['total_sales_value'] ?? 0, 2) }}</h3>
                </div>
                <div class="p-2.5 bg-blue-50 text-blue-600 rounded-lg transform rotate-12">
                    <i class="fas fa-tag text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Cost -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <div>
                     <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Cost</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">₵{{ number_format($stats['total_cost'] ?? 0, 2) }}</h3>
                </div>
                <div class="p-2.5 bg-orange-50 text-orange-600 rounded-lg transform -rotate-12">
                    <i class="fas fa-coins text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Potential Profit -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <div>
                     <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Potential Profit</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">₵{{ number_format($stats['potential_profit'] ?? 0, 2) }}</h3>
                </div>
                <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-lg">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Avg Margin -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <div>
                     <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Avg Margin</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['avg_margin'] ?? 0, 1) }}%</h3>
                </div>
                <div class="p-2.5 bg-purple-50 text-purple-600 rounded-lg">
                    <i class="fas fa-percentage text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid Row 2: Inventory Counts -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- All Products -->
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 text-lg font-bold">
                {{ $stats['total_products'] }}
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase">All Products</p>
            </div>
        </div>

        <!-- In Stock -->
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
             <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-600 text-lg font-bold">
                 <i class="fas fa-check"></i>
            </div>
            <div>
                 <h4 class="text-xl font-bold text-gray-900">{{ $stats['in_store'] }}</h4>
                <p class="text-xs font-bold text-gray-400 uppercase">In Stock</p>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
             <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 text-lg font-bold">
                 !
            </div>
            <div>
                 <h4 class="text-xl font-bold text-gray-900">{{ $stats['low_stock'] }}</h4>
                <p class="text-xs font-bold text-gray-400 uppercase">Low Stock</p>
            </div>
        </div>

        <!-- Out of Stock -->
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
             <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center text-red-600 text-lg font-bold">
                 <i class="fas fa-times"></i>
            </div>
            <div>
                 <h4 class="text-xl font-bold text-gray-900">{{ $stats['out_of_stock'] }}</h4>
                <p class="text-xs font-bold text-gray-400 uppercase">Out of Stock</p>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
        
        <!-- Toolbar -->
        <div class="p-5 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="w-full md:w-96 relative">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="productSearch" 
                    class="w-full pl-11 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm" 
                    placeholder="Search products by name, barcode or SKU...">
            </div>
            
            <div class="flex items-center gap-2">
                <button class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium shadow-sm">All</button>
                <button class="px-4 py-2 bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium transition-colors">Shoes</button>
                <button class="px-4 py-2 bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium transition-colors">Groceries</button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50/50 border-b border-gray-100 text-left">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Product Info</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Price (GHC)</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Cost (GHC)</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-400 uppercase tracking-wider">Stock Status</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50" id="productsTableBody">
                    @forelse($products as $product)
                    <tr class="group hover:bg-gray-50/50 transition-colors product-row" data-name="{{ strtolower($product->product->name) }}" data-barcode="{{ $product->product->barcode }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-gray-900">{{ $product->product->name }}</div>
                                    <div class="text-xs text-gray-500 font-mono">{{ $product->product->barcode ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-medium border border-gray-200">
                                {{ $product->product->category->name ?? 'Uncategorized' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-gray-900">
                            {{ number_format($product->price ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right text-gray-500 font-medium">
                            {{ number_format($product->cost_price ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($product->stock_quantity <= 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                    Out of Stock
                                </span>
                            @elseif($product->stock_quantity <= ($product->reorder_level ?? 10))
                                <div class="flex flex-col items-center">
                                    <span class="font-bold text-orange-600">{{ $product->stock_quantity }} Units</span>
                                    <span class="text-[10px] uppercase font-bold text-orange-400 bg-orange-50 px-2 py-0.5 rounded mt-0.5">Low Stock</span>
                                </div>
                            @else
                                <div class="flex flex-col items-center">
                                    <span class="font-bold text-blue-600">{{ $product->stock_quantity }} Units</span>
                                    @if(isset($product->pending_assignment) && $product->pending_assignment)
                                        <span class="text-[10px] uppercase font-bold text-blue-400 bg-blue-50 px-2 py-0.5 rounded mt-0.5">Pending Assignment</span>
                                    @else
                                        <span class="text-[10px] uppercase font-bold text-green-500 bg-green-50 px-2 py-0.5 rounded mt-0.5">In Stock</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <div class="mx-auto w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-search text-gray-300 text-2xl"></i>
                            </div>
                            <p class="text-lg font-medium text-gray-900">No products found</p>
                            <p class="text-sm">Try adjusting your search terms.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination (Mock for now, can be real if paginated) -->
        <div class="p-4 border-t border-gray-100 flex justify-between items-center text-sm text-gray-500">
            <span>Showing {{ count($products) }} results</span>
            <div class="flex gap-2">
                <button class="px-3 py-1 border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-50" disabled>Previous</button>
                <button class="px-3 py-1 border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-50" disabled>Next</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('productSearch');
    const tableBody = document.getElementById('productsTableBody');
    const rows = tableBody.querySelectorAll('.product-row');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            
            rows.forEach(row => {
                const name = row.dataset.name;
                const barcode = row.dataset.barcode;
                
                if (name.includes(term) || (barcode && barcode.includes(term))) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>
@endpush
@endsection