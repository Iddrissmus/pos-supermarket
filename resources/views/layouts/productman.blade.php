@extends('layouts.app')

@section('title', 'Branch Products')

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

    <!-- Notifications -->
    <div id="notification-container" class="fixed top-4 right-4 z-50 space-y-2 pointer-events-none"></div>
    @if (session('success')) <div class="hidden" id="flash-success">{{ session('success') }}</div> @endif
    @if (session('import_info')) <div class="hidden" id="flash-info">{{ session('import_info') }}</div> @endif

    <!-- Modern Header -->
    <div class="relative bg-gradient-to-r from-indigo-700 to-purple-800 rounded-xl shadow-lg overflow-hidden">
        <div class="absolute inset-0 bg-white/10" style="background-image: radial-gradient(circle at 10% 20%, rgba(255,255,255,0.1) 0%, transparent 20%), radial-gradient(circle at 90% 80%, rgba(255,255,255,0.1) 0%, transparent 20%);"></div>
        <div class="relative p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight flex items-center">
                    <i class="fas fa-store-alt mr-3 text-indigo-200"></i> Branch Inventory
                </h1>
                <p class="mt-2 text-indigo-100 text-lg opacity-90 max-w-2xl">
                    @if(auth()->user()->role === 'superadmin')
                        Overview of assigned products across all branches.
                    @else
                        Managing inventory assigned to <span class="font-semibold text-white border-b-2 border-white/30">{{ auth()->user()->branch->name ?? 'Your Branch' }}</span>
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                 <a href="{{ route('layouts.product') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors font-medium backdrop-blur-sm border border-white/10 flex items-center">
                    <i class="fas fa-warehouse mr-2 opacity-80"></i> Main Warehouse
                </a>
                <a href="{{ route('inventory.assign') }}" class="px-4 py-2 bg-white text-indigo-700 hover:bg-indigo-50 rounded-lg transition-colors font-bold shadow-sm flex items-center">
                    <i class="fas fa-plus mr-2"></i> Assign Products
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Assigned Products -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-indigo-200 transition-colors">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Assigned Items</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $stats['total_products'] ?? 0}}</h3>
                </div>
                <p class="text-xs text-gray-400 mt-1">Unique products in branch</p>
            </div>
            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-box text-xl"></i>
            </div>
        </div>

        <!-- Total Units -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-green-200 transition-colors">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Units</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $stats['in_store'] ?? 0}}</h3>
                </div>
                 <p class="text-xs text-gray-400 mt-1">Total stock quantity available</p>
            </div>
            <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-cubes text-xl"></i>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-yellow-200 transition-colors">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Low Stock</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $stats['low_stock'] ?? 0}}</h3>
                </div>
                 <p class="text-xs text-gray-400 mt-1">Items below reorder level</p>
            </div>
            <div class="w-12 h-12 bg-yellow-50 text-yellow-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-exclamation-triangle text-xl"></i>
            </div>
        </div>

        <!-- Inventory Value -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-purple-200 transition-colors">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Inventory Value</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-gray-900">₵{{ number_format($stats['total_value'] ?? 0, 2) }}</h3>
                </div>
                 <p class="text-xs text-gray-400 mt-1">Total wholesale cost</p>
            </div>
            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-cedi-sign text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Branch Products Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row gap-4 justify-between items-center">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Assigned Inventory</h2>
                <p class="text-sm text-gray-500">List of all products currently assigned to this branch</p>
            </div>
            <div class="relative w-full md:w-80">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" id="searchInput" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out" placeholder="Search branch products...">
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            @if(isset($products) && count($products) > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Info</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price (₵)</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value (₵)</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="productsTable">
                        @foreach($products as $branchProduct)
                            <tr class="hover:bg-gray-50/80 transition-colors product-row" 
                                data-product-name="{{ strtolower($branchProduct->product->name ?? '') }}"
                                data-branch-name="{{ strtolower($branchProduct->branch->name ?? '') }}">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="ml-0">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $branchProduct->product->name ?? 'N/A' }}
                                            </div>
                                            <div class="text-xs text-gray-500 font-mono">
                                                {{ $branchProduct->product->barcode ?? 'No Barcode' }}
                                            </div>
                                            <span class="inline-flex mt-1 items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ $branchProduct->product->category->name ?? 'Uncategorized' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fas fa-map-marker-alt text-gray-300 mr-2"></i>
                                        <div class="text-sm text-gray-700 font-medium">{{ $branchProduct->branch->name ?? 'N/A' }}</div>
                                    </div>
                                    <div class="text-xs text-gray-400 pl-5">{{ $branchProduct->branch->location ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm font-bold text-gray-900">{{ number_format($branchProduct->stock_quantity) }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $branchProduct->quantity_of_boxes ?? 0 }} boxes × {{ $branchProduct->quantity_per_box ?? 1 }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-green-600">
                                    {{ number_format($branchProduct->price ?? 0, 2) }}
                                    <div class="text-xs text-gray-400 font-normal">Cost: {{ number_format($branchProduct->cost_price ?? 0, 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-700">
                                    {{ number_format(($branchProduct->stock_quantity ?? 0) * ($branchProduct->cost_price ?? 0), 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($branchProduct->stock_quantity == 0)
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Out of Stock
                                        </span>
                                    @elseif($branchProduct->stock_quantity <= ($branchProduct->reorder_level ?? 10))
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Low Stock
                                        </span>
                                    @else
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            In Stock
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                 <div class="p-12 text-center">
                    <div class="mx-auto w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-box-open text-indigo-400 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">No Products Assigned</h3>
                    <p class="mt-1 text-gray-500 mb-6">Start by assigning products from your warehouse inventory to branches.</p>
                    <div class="flex justify-center space-x-3">
                        <a href="{{ route('inventory.assign') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors shadow-sm">
                            <i class="fas fa-hand-pointer mr-2"></i>Manual Assignment
                        </a>
                        <a href="{{ route('inventory.bulk-assignment') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium rounded-lg transition-colors">
                            <i class="fas fa-file-excel mr-2 text-green-600"></i>Bulk Assignment
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search Functionality
    const searchInput = document.getElementById('searchInput');
    const productsTable = document.getElementById('productsTable');
    
    if (searchInput && productsTable) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = productsTable.querySelectorAll('.product-row');
            let hasVisibleRows = false;
            
            rows.forEach(row => {
                const productName = row.dataset.productName || '';
                const branchName = row.dataset.branchName || '';
                
                if (productName.includes(searchTerm) || branchName.includes(searchTerm)) {
                    row.style.display = '';
                    hasVisibleRows = true;
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Notification Logic
    const successMsg = document.getElementById('flash-success');
    const infoMsg = document.getElementById('flash-info');
    
    if(successMsg) showNotification(successMsg.innerText, 'success');
    if(infoMsg) showNotification(infoMsg.innerText, 'info');

    function showNotification(message, type = 'success') {
        const container = document.getElementById('notification-container');
        const notif = document.createElement('div');
        const bg = type === 'success' ? 'bg-green-500' : (type === 'info' ? 'bg-blue-500' : 'bg-red-500');
        
        notif.className = `transform transition-all duration-300 ease-out translate-x-full opacity-0 flex items-center p-4 mb-4 text-sm text-white rounded-lg shadow-lg ${bg}`;
        notif.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-info-circle'} mr-2"></i>
            <span>${message}</span>
        `;
        
        container.appendChild(notif);
        
        // Animate in
        requestAnimationFrame(() => {
            notif.classList.remove('translate-x-full', 'opacity-0');
        });
        
        // Remove after 4s
        setTimeout(() => {
            notif.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => notif.remove(), 300);
        }, 4000);
    }
});
</script>
@endsection