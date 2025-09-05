@extends('layouts.app')

@section('title', 'Inventory Management')

@section('content')
<div class="p-6">
    <!-- Green Header Bar -->
    <div class="bg-green-600 text-white px-6 py-4 rounded-t-lg mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">Inventory Management</h1>
            <div class="flex space-x-3">
                <button class="bg-green-700 hover:bg-green-800 px-4 py-2 rounded-lg font-medium transition-colors">
                    Add
                </button>
                <button class="bg-green-700 hover:bg-green-800 px-4 py-2 rounded-lg font-medium transition-colors">
                    Edit
                </button>
            </div>
        </div>
    </div>

    <!-- Product Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- All Products Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Product Summary</h3>
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">350</div>
                    <div class="text-sm text-gray-600">All Products</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">102</div>
                    <div class="text-sm text-gray-600">In Store</div>
                </div>
                <div class="text-center border-2 border-red-500 rounded-lg p-2">
                    <div class="text-2xl font-bold text-red-600">12</div>
                    <div class="text-sm text-gray-600">Low In Stock</div>
                </div>
            </div>
        </div>

        <!-- Categories Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Categories</h3>
            <div class="text-gray-500 text-center py-8">
                <i class="fas fa-folder-open text-4xl mb-2"></i>
                <p>No categories available</p>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Quick Actions</h3>
            <div class="space-y-2">
                <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add New Product
                </button>
                <button class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-download mr-2"></i>Export Inventory
                </button>
                <button class="w-full bg-yellow-600 hover:bg-yellow-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-chart-bar mr-2"></i>Generate Report
                </button>
            </div>
        </div>
    </div>

    <!-- Inventory Summary Section -->
    <div class="bg-white rounded-lg shadow-md">
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

        <!-- Product Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Product Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Category
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
                            Total Value
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            In Store
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-200 rounded-lg mr-3 flex items-center justify-center">
                                    <i class="fas fa-mobile-alt text-gray-500"></i>
                                </div>
                                <div class="text-sm font-medium text-gray-900">iPhone 14</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">iPhone</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$799</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">12</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$10</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$10,165</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-xs font-medium transition-colors">
                                Unpublish
                            </button>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-200 rounded-lg mr-3 flex items-center justify-center">
                                    <i class="fas fa-mobile-alt text-gray-500"></i>
                                </div>
                                <div class="text-sm font-medium text-gray-900">iPhone 13</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">iPhone</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$699</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">12</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$10</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$10,165</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg text-xs font-medium transition-colors">
                                Publish
                            </button>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-200 rounded-lg mr-3 flex items-center justify-center">
                                    <i class="fas fa-laptop text-gray-500"></i>
                                </div>
                                <div class="text-sm font-medium text-gray-900">MacBook Pro</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Laptop</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$1,299</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">8</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$15</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$15,165</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg text-xs font-medium transition-colors">
                                Publish
                            </button>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-200 rounded-lg mr-3 flex items-center justify-center">
                                    <i class="fas fa-tablet-alt text-gray-500"></i>
                                </div>
                                <div class="text-sm font-medium text-gray-900">iPad Air</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Tablet</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$599</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">5</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$8</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$8,165</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-xs font-medium transition-colors">
                                Unpublish
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span class="font-medium">1</span> to <span class="font-medium">4</span> of <span class="font-medium">350</span> results
                </div>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Previous</button>
                    <button class="px-3 py-1 bg-blue-600 text-white rounded-lg text-sm">1</button>
                    <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">2</button>
                    <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">3</button>
                    <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Next</button>
                </div>
            </div>
        </div>
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
</script>
@endsection 