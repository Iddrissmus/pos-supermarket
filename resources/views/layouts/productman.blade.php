@extends('layouts.app')

@section('title', 'Branch Products')

@section('content')
<div class="p-6">
    <!-- Header Bar -->
    <div class="bg-indigo-600 text-white px-6 py-4 rounded-t-lg mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Branch Products - Assigned Inventory</h1>
                <p class="text-sm text-indigo-100 mt-1">
                    @if(auth()->user()->role === 'superadmin')
                        Viewing all products across all branches
                    @else
                        Viewing products assigned to: <span class="font-semibold">{{ auth()->user()->branch->name ?? 'Your Branch' }}</span>
                    @endif
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('layouts.product') }}" class="bg-indigo-700 hover:bg-indigo-800 px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-warehouse mr-2"></i>Warehouse Products
                </a>
                <a href="{{ route('inventory.assign') }}" class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-plus mr-2"></i>Assign Products
                </a>
            </div>
        </div>
    </div>

    <!-- Success/Info Messages -->
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    
    @if (session('import_info'))
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('import_info') }}</span>
        </div>
    @endif

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-box text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Assigned Products</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_products'] ?? 0}}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-cubes text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Units</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['in_store'] ?? 0}}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Low Stock Items</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['low_stock'] ?? 0}}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-cedi-sign text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Inventory Value</p>
                    <p class="text-2xl font-semibold text-gray-900">₵{{ number_format($stats['total_value'] ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Products Table -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Products by Branch</h2>
                <p class="text-sm text-gray-600 mt-1">Showing all products assigned to branches with current stock levels</p>
            </div>
            <div class="flex space-x-2">
                <input 
                    type="text" 
                    id="searchInput" 
                    placeholder="Search products..." 
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                >
            </div>
        </div>
        <div class="overflow-x-auto">
            @if(isset($products) && count($products) > 0)
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Boxes</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="productsTable">
                        @foreach($products as $branchProduct)
                            <tr class="hover:bg-gray-50 product-row" 
                                data-product-name="{{ strtolower($branchProduct->product->name ?? '') }}"
                                data-branch-name="{{ strtolower($branchProduct->branch->name ?? '') }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $branchProduct->product->name ?? 'N/A' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                Barcode: {{ $branchProduct->product->barcode ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fas fa-store text-indigo-600 mr-2"></i>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $branchProduct->branch->name ?? 'N/A' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $branchProduct->branch->location ?? '' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $branchProduct->product->category->name ?? 'Uncategorized' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm font-bold text-gray-900">{{ number_format($branchProduct->stock_quantity) }}</div>
                                    <div class="text-xs text-gray-500">units</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm text-gray-900">{{ $branchProduct->quantity_of_boxes ?? 0 }}</div>
                                    <div class="text-xs text-gray-500">× {{ $branchProduct->quantity_per_box ?? 1 }}/box</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm font-medium text-green-600">
                                        ₵{{ number_format($branchProduct->price ?? 0, 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm text-gray-600">
                                        ₵{{ number_format($branchProduct->cost_price ?? 0, 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm font-semibold text-purple-600">
                                        ₵{{ number_format(($branchProduct->stock_quantity ?? 0) * ($branchProduct->cost_price ?? 0), 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($branchProduct->stock_quantity == 0)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Out of Stock
                                        </span>
                                    @elseif($branchProduct->stock_quantity <= ($branchProduct->reorder_level ?? 10))
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Low Stock
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
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
                    <i class="fas fa-box-open text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Products Assigned to Branches</h3>
                    <p class="text-gray-500 mb-6">Start by assigning products from your warehouse inventory to branches.</p>
                    <div class="flex justify-center space-x-3">
                        <a href="{{ route('inventory.assign') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                            <i class="fas fa-hand-pointer mr-2"></i>Manual Assignment
                        </a>
                        <a href="{{ route('inventory.bulk-assignment') }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                            <i class="fas fa-file-excel mr-2"></i>Bulk Assignment
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const productsTable = document.getElementById('productsTable');
    
    if (searchInput && productsTable) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = productsTable.querySelectorAll('.product-row');
            
            rows.forEach(row => {
                const productName = row.dataset.productName || '';
                const branchName = row.dataset.branchName || '';
                
                if (productName.includes(searchTerm) || branchName.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>
@endsection 