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
            <div class="flex space-x-3">
                <a href="{{ route('product.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>Add Product
                </a>
                {{-- <a href="{{route('layouts.assign')}}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors inline-flex items-center">
                    <i class="fas fa-download mr-2"></i>Assign to Branch
                </a> --}}
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
            <h2 class="text-xl font-semibold text-gray-800">Inventory Summary</h2>
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
                    <button class="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-filter mr-2 text-gray-600"></i>
                        <span class="text-gray-700">Filter</span>
                    </button>
                    <button class="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-sort mr-2 text-gray-600"></i>
                        <span class="text-gray-700">Sort</span>
                    </button>
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
                            Description
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Unit Price
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            In Stock
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cost Price
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
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
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ \Illuminate\Support\Str::limit($product->description ?? 'No description', 80) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">GH₵{{ number_format($branchProduct->price ?? $product->price ?? 0, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $branchProduct->stock_quantity ?? $product->stock ?? 0 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">GH₵{{ number_format($branchProduct->cost_price ?? $product->cost_price ?? 0, 2) }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-right space-x-2">
                                @if($branchProduct)
                                    <span class="text-xs text-gray-500">{{ $branchProduct->branch->name ?? 'Branch' }}</span><br>
                                @endif
                                <button type="button" class="px-3 py-1 border rounded-lg">Edit</button>
                                <button type="button" class="px-3 py-1 border rounded-lg text-red-600">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">No products found.</td>
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
});
</script>

<style>
.notification {
    min-width: 300px;
    max-width: 400px;
}
</style>
@endsection