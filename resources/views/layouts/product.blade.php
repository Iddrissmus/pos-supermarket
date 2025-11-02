@extends('layouts.app')

@section('title', 'Inventory Management')

@section('content')
<div class="p-6 space-y-6">
    <!-- Notification Container -->
    <div id="notification-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Inventory Management</h1>
                <p class="text-sm text-gray-600 mt-1">Manage your products and track inventory across branches</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('product.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>Add Product
                </a>
                <a href="{{ route('inventory.bulk-import') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors inline-flex items-center">
                    <i class="fas fa-file-excel mr-2"></i>Bulk Import
                </a>
                <a href="{{ route('inventory.bulk-assignment') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition-colors inline-flex items-center">
                    <i class="fas fa-tasks mr-2"></i>Bulk Assign
                </a>
            </div>
        </div>
    </div>

    <!-- Financial Metrics Cards -->
    @php
        $totalSellingPrice = 0;
        $totalCostPrice = 0;
        foreach($products as $item) {
            $branchProduct = $item->product ? $item : null;
            $sellingPrice = $branchProduct->price ?? 0;
            $costPrice = $branchProduct->cost_price ?? 0;
            $quantity = $branchProduct->stock_quantity ?? 0;
            
            $totalSellingPrice += ($sellingPrice * $quantity);
            $totalCostPrice += ($costPrice * $quantity);
        }
        $totalMargin = $totalSellingPrice - $totalCostPrice;
        $marginPercentage = $totalCostPrice > 0 ? (($totalMargin / $totalCostPrice) * 100) : 0;
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Selling Price -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Selling Price</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">
                        GH₵{{ number_format($totalSellingPrice, 2) }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Value of all inventory at selling price</p>
                </div>
                <div class="bg-blue-100 rounded-full p-4">
                    <i class="fas fa-tag text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Cost Price -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Cost Price</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">
                        GH₵{{ number_format($totalCostPrice, 2) }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Total cost of all inventory</p>
                </div>
                <div class="bg-orange-100 rounded-full p-4">
                    <i class="fas fa-dollar-sign text-orange-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Margin -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Margin</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">
                        GH₵{{ number_format($totalMargin, 2) }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Potential profit from inventory</p>
                </div>
                <div class="bg-green-100 rounded-full p-4">
                    <i class="fas fa-chart-line text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Margin Percentage -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Margin Percentage</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">
                        {{ number_format($marginPercentage, 1) }}%
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Average profit margin</p>
                </div>
                <div class="bg-purple-100 rounded-full p-4">
                    <i class="fas fa-percent text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Total Products -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Products</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{$stats['total_products'] ?? 0}}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-boxes text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- In Store -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">In Store</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{$stats['in_store_products'] ?? 0}}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Low Stock</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">{{$stats['low_stock_products'] ?? 0}}</p>
                </div>
                <div class="bg-red-100 rounded-full p-3">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-500 text-sm font-medium mb-3">Quick Actions</p>
            <div class="space-y-2">
                <a href="{{route('stock-receipts.index')}}" class="block text-center bg-purple-100 hover:bg-purple-200 text-purple-700 py-2 px-3 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-truck mr-1"></i>Receive Stock
                </a>
                <a href="{{route('sales.report')}}" class="block text-center bg-yellow-100 hover:bg-yellow-200 text-yellow-700 py-2 px-3 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-chart-bar mr-1"></i>Sales Report
                </a>
            </div>
        </div>
    </div>

    {{-- <livewire:products.manage-products /> --}}

    <!-- Inventory Summary Section -->
    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Inventory Summary</h2>
                    @if($selectedCategory)
                        @php
                            $selectedCat = $categories->firstWhere('id', $selectedCategory);
                        @endphp
                        <p class="text-sm text-gray-600 mt-1">
                            Filtered by: 
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="fas {{ $selectedCat->icon ?? 'fa-tag' }} mr-1"></i>
                                {{ $selectedCat->name ?? 'Category' }}
                            </span>
                        </p>
                    @endif
                </div>
                <div class="text-sm text-gray-600">
                    Showing {{ $products->total() }} {{ $selectedCategory ? 'filtered' : 'total' }} products
                </div>
            </div>
        </div>
        
        <!-- Action Bar -->
        <div class="p-6 border-b border-gray-200 bg-gray-50">
            <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" placeholder="Search" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
                <div class="flex space-x-3">
                    <!-- Category filter-->
                    <form method="GET" action="{{ route('layouts.product') }}" id="categoryFilterForm">
                        <select name="category_id" id="categoryFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @if($selectedCategory == $cat->id) selected @endif>
                                    {{ $cat->name }} ({{ $cat->products_count }})
                                </option>
                            @endforeach
                        </select>
                    </form>
                    @if($selectedCategory)
                        <a href="{{ route('layouts.product') }}" class="flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors">
                            <i class="fas fa-times mr-2"></i>
                            <span>Clear Filter</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Product Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            SKU
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Category
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Description
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Selling Price
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cost Price
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Stock Qty
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $item)
                        @php
                            // Handle both BranchProduct and Product objects
                            $product = $item->product ?? $item;
                            $branchProduct = $item->product ? $item : null;
                            $stockQty = $branchProduct->stock_quantity ?? $product->stock ?? 0;
                            $stockClass = $stockQty <= 10 ? 'text-red-600 font-semibold' : ($stockQty <= 50 ? 'text-yellow-600' : 'text-green-600');
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors" data-category-id="{{ $product->category->id ?? '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-10 h-10 rounded-lg object-cover mr-3">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center mr-3">
                                            <i class="fas fa-box text-gray-400"></i>
                                        </div>
                                    @endif
                                    <span class="text-sm font-medium text-gray-900">{{ $product->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-600 font-mono">{{ $product->sku ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($product->category)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $product->category->color ?? 'gray' }}-100 text-{{ $product->category->color ?? 'gray' }}-800">
                                        <i class="fas {{ $product->category->icon ?? 'fa-tag' }} mr-1"></i>
                                        {{ $product->category->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">Uncategorized</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                <span class="text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($product->description ?? 'No description', 50) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-medium text-gray-900">₵{{ number_format($branchProduct->price ?? $product->price ?? 0, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm text-gray-600">₵{{ number_format($branchProduct->cost_price ?? $product->cost_price ?? 0, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm {{ $stockClass }}">{{ $stockQty }}</span>
                                @if($stockQty <= 10)
                                    <span class="block text-xs text-red-500">Low stock!</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    @if($branchProduct)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                                            <i class="fas fa-store mr-1"></i>
                                            {{ \Illuminate\Support\Str::limit($branchProduct->branch->name ?? 'Branch', 15) }}
                                        </span>
                                    @endif
                                    <button type="button" class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="text-red-600 hover:text-red-800 transition-colors" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <i class="fas fa-box-open text-6xl mb-4 text-gray-300"></i>
                                    <p class="text-lg font-medium">No products found</p>
                                    <p class="text-sm mt-1">Try adjusting your filters or add a new product</p>
                                    @if($selectedCategory)
                                        <a href="{{ route('layouts.product') }}" class="mt-4 text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-times-circle mr-1"></i>Clear category filter
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing <span class="font-medium">{{ $products->firstItem() }}</span> 
                        to <span class="font-medium">{{ $products->lastItem() }}</span> 
                        of <span class="font-medium">{{ $products->total() }}</span> results
                    </div>
                    <div>
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="text-sm text-gray-700 text-center">
                    Showing all <span class="font-medium">{{ $products->count() }}</span> results
                </div>
            </div>
        @endif
    </div>
</div>




<script>
// Add interactive functionality
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar item click handling
    const sidebarItems = document.querySelectorAll('.sidebar-item');
    sidebarItems.forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all items
            sidebarItems.forEach(i => i.classList.remove('active'));
            // Add active class to clicked item
            this.classList.add('active');
        });
    });

    // Table row selection
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            if (this.checked) {
                row.classList.add('bg-blue-50');
            } else {
                row.classList.remove('bg-blue-50');
            }
        });
    });

    // Search functionality
    const searchInput = document.querySelector('input[placeholder="Search"]');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});

    // Modal/form handlers moved to dedicated product-create page

    // Listen for Livewire notification events
    window.addEventListener('notify', function(event) {
        showNotification(event.detail.message, event.detail.type);
    });

    // Function to show notifications
    function showNotification(message, type = 'success') {
        const container = document.getElementById('notification-container');
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification px-6 py-4 rounded-lg shadow-lg text-white transform transition-all duration-300 ease-in-out translate-x-full opacity-0 ${getNotificationClass(type)}`;
        
        notification.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas ${getNotificationIcon(type)} mr-3"></i>
                    <span>${message}</span>
                </div>
                <button onclick="removeNotification(this)" class="ml-4 text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        // Add to container
        container.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full', 'opacity-0');
        }, 100);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            removeNotification(notification.querySelector('button'));
        }, 5000);
    }

    // Function to get notification CSS class based on type
    function getNotificationClass(type) {
        switch(type) {
            case 'success':
                return 'bg-green-500';
            case 'error':
                return 'bg-red-500';
            case 'warning':
                return 'bg-yellow-500';
            case 'info':
                return 'bg-blue-500';
            default:
                return 'bg-green-500';
        }
    }

    // Function to get notification icon based on type
    function getNotificationIcon(type) {
        switch(type) {
            case 'success':
                return 'fa-check-circle';
            case 'error':
                return 'fa-exclamation-circle';
            case 'warning':
                return 'fa-exclamation-triangle';
            case 'info':
                return 'fa-info-circle';
            default:
                return 'fa-check-circle';
        }
    }

    // Make removeNotification globally available
    window.removeNotification = function(button) {
        const notification = button.closest('.notification');
        notification.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            notification.remove();
        }, 300);
    };

    // Legacy functions for backward compatibility
    function showSuccess(message) {
        showNotification(message, 'success');
    }

    function showError(message) {
        showNotification(message, 'error');
    }

    // Remove the old client-side category filter since we're now using server-side filtering

</script>

<style>
.notification {
    min-width: 300px;
    max-width: 400px;
}
</style>
@endsection