@extends('layouts.app')

@section('title', 'Warehouse Inventory')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto space-y-8">
    
    <!-- Notifications -->
    <div id="notification-container" class="fixed top-20 right-4 z-[100] space-y-2 pointer-events-none"></div>
    @if (session('success')) <div class="hidden" id="flash-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="hidden" id="flash-error">{{ session('error') }}</div> @endif

    <!-- Modern Header -->
    <div class="relative bg-gradient-to-r from-indigo-800 to-blue-900 rounded-xl shadow-lg overflow-hidden">
        <div class="absolute inset-0 bg-white/10" style="background-image: radial-gradient(circle at 10% 20%, rgba(255,255,255,0.1) 0%, transparent 20%), radial-gradient(circle at 90% 80%, rgba(255,255,255,0.1) 0%, transparent 20%);"></div>
        <div class="relative p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/50 text-indigo-100 border border-indigo-400/30 uppercase tracking-wide">
                        Branch Level
                    </span>
                </div>
                <h1 class="text-3xl font-bold text-white tracking-tight flex items-center">
                    <i class="fas fa-store mr-3 text-indigo-200"></i> Branch Product Manager
                </h1>
                <p class="mt-2 text-indigo-100 text-lg opacity-90 max-w-2xl">
                    View and manage products actively assigned to your branches. Track local stock levels and performance.
                </p>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('layouts.product') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors font-medium backdrop-blur-sm border border-white/10 flex items-center">
                    <i class="fas fa-layer-group mr-2 opacity-80"></i> All Products
                </a>
                <div class="relative group">
                    <button class="px-4 py-2 bg-white text-indigo-900 hover:bg-indigo-50 rounded-lg transition-colors font-bold shadow-sm flex items-center">
                        <i class="fas fa-plus mr-2"></i> Add Product
                    </button>
                     <!-- Dropdown for Add Product -->
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-100 overflow-hidden hidden group-hover:block z-50">
                        <a href="{{ route('product.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-box mr-2 text-indigo-600"></i> New Product
                        </a>
                        <a href="{{ route('inventory.assign') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 border-t border-gray-50">
                            <i class="fas fa-dolly mr-2 text-blue-600"></i> Assign Stock
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid Row 1: Financials -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Sales Value -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-blue-200 transition-colors">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Value (Sales)</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-gray-900">₵{{ number_format($stats['total_sales_value'] ?? 0, 2) }}</h3>
                </div>
            </div>
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-tag text-xl"></i>
            </div>
        </div>

        <!-- Total Cost -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-orange-200 transition-colors">
            <div>
                 <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Cost</p>
                 <div class="mt-1 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-gray-900">₵{{ number_format($stats['total_cost'] ?? 0, 2) }}</h3>
                </div>
            </div>
            <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-coins text-xl"></i>
            </div>
        </div>

        <!-- Potential Profit -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-emerald-200 transition-colors">
            <div>
                 <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Potential Profit</p>
                 <div class="mt-1 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-gray-900">₵{{ number_format($stats['potential_profit'] ?? 0, 2) }}</h3>
                </div>
            </div>
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-chart-line text-xl"></i>
            </div>
        </div>

        <!-- Avg Margin -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-purple-200 transition-colors">
            <div>
                 <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Avg Margin</p>
                 <div class="mt-1 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($stats['avg_margin'] ?? 0, 1) }}%</h3>
                </div>
            </div>
            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-percentage text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Stats Grid Row 2: Inventory Counts -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- All Products -->
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4 hover:border-gray-300 transition-colors">
            <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500 text-lg font-bold">
                {{ $stats['total_products'] }}
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase">All Products</p>
            </div>
        </div>

        <!-- In Stock -->
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4 hover:border-green-300 transition-colors">
             <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center text-green-600 text-lg font-bold">
                 <i class="fas fa-check"></i>
            </div>
            <div>
                 <h4 class="text-xl font-bold text-gray-900">{{ $stats['in_store'] }}</h4>
                <p class="text-xs font-bold text-gray-400 uppercase">In Stock</p>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4 hover:border-orange-300 transition-colors">
             <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center text-orange-600 text-lg font-bold">
                 !
            </div>
            <div>
                 <h4 class="text-xl font-bold text-gray-900">{{ $stats['low_stock'] }}</h4>
                <p class="text-xs font-bold text-gray-400 uppercase">Low Stock</p>
            </div>
        </div>

        <!-- Out of Stock -->
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4 hover:border-red-300 transition-colors">
             <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center text-red-600 text-lg font-bold">
                 <i class="fas fa-times"></i>
            </div>
            <div>
                 <h4 class="text-xl font-bold text-gray-900">{{ $stats['out_of_stock'] }}</h4>
                <p class="text-xs font-bold text-gray-400 uppercase">Out of Stock</p>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        
        <!-- Toolbar -->
        <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="w-full md:w-96 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" id="productSearch" 
                    class="block w-full pl-10 pr-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500" 
                    placeholder="Search products by name, barcode or SKU...">
            </div>
            
            <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0" id="categoryFilters">
                <button class="px-3 py-1.5 bg-gray-900 text-white rounded-full text-xs font-medium shadow-sm active-category transition-colors whitespace-nowrap" data-filter="all">All</button>
                @foreach($categories as $category)
                    <button class="px-3 py-1.5 bg-white text-gray-600 hover:bg-gray-50 border border-gray-300 rounded-full text-xs font-medium transition-colors whitespace-nowrap category-btn" 
                        data-filter="{{ $category->id }}">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto custom-scrollbar">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Info</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price (GHC)</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cost (GHC)</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="productsTableBody">
                    @forelse($products as $product)
                    <tr class="group hover:bg-gray-50/80 transition-colors product-row" 
                        data-name="{{ strtolower($product->product->name) }}" 
                        data-barcode="{{ $product->product->barcode }}"
                        data-category-id="{{ $product->product->category_id }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="ml-0">
                                    <div class="text-sm font-medium text-gray-900">{{ $product->product->name }}</div>
                                    <div class="text-xs text-gray-500 font-mono">{{ $product->product->barcode ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                {{ $product->product->category->name ?? 'Uncategorized' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                            {{ number_format($product->price ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                            {{ number_format($product->cost_price ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($product->stock_quantity <= 0)
                                <div class="flex flex-col items-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                        {{ $product->stock_quantity }} Units
                                    </span>
                                    <span class="text-[10px] uppercase font-bold text-red-400 mt-1">Out of Stock</span>
                                </div>
                            @elseif($product->stock_quantity <= ($product->reorder_level ?? 10))
                                <div class="flex flex-col items-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-700">
                                        {{ $product->stock_quantity }} Units
                                    </span>
                                    <span class="text-[10px] uppercase font-bold text-orange-400 mt-1">Low Stock</span>
                                </div>
                            @else
                                <div class="flex flex-col items-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                        {{ $product->stock_quantity }} Units
                                    </span>
                                    <span class="text-[10px] uppercase font-bold text-green-500 mt-1">In Stock</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="text-gray-400 hover:text-indigo-600 transition-colors" title="View Details">
                                    <i class="fas fa-eye text-lg"></i>
                                </button>
                                <button class="text-gray-400 hover:text-red-600 transition-colors" title="Delete">
                                    <i class="fas fa-trash-alt text-lg"></i>
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
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 flex justify-between items-center text-sm text-gray-500">
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
    const categoryButtons = document.querySelectorAll('#categoryFilters button');

    let currentFilter = 'all';

    function filterProducts() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';

        rows.forEach(row => {
            const name = row.dataset.name;
            const barcode = row.dataset.barcode;
            const categoryId = row.dataset.categoryId;
            
            const matchesSearch = name.includes(searchTerm) || (barcode && barcode.includes(searchTerm));
            const matchesCategory = currentFilter === 'all' || categoryId === currentFilter;

            if (matchesSearch && matchesCategory) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterProducts);
    }

    categoryButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Update UI
            categoryButtons.forEach(b => {
                b.classList.remove('bg-gray-900', 'text-white', 'shadow-sm', 'active-category');
                b.classList.add('bg-white', 'text-gray-600', 'border', 'border-gray-200');
            });
            this.classList.remove('bg-white', 'text-gray-600', 'border', 'border-gray-200');
            this.classList.add('bg-gray-900', 'text-white', 'shadow-sm', 'active-category');

            // Apply filter
            currentFilter = this.dataset.filter;
            filterProducts();
        });
    });
});
</script>
@endpush
@endsection