@extends('layouts.app')

@section('title', 'Item Requests')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="bg-green-600 text-white px-6 py-4 rounded-t-lg mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Stock Transfer Requests</h1>
                <p class="text-sm text-green-100 mt-1">Request stock transfers for {{ $manager->branch->display_label }}</p>
            </div>
            <div class="flex gap-3">
                <button onclick="document.getElementById('bulkUploadModal').classList.remove('hidden')" 
                        class="bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-file-excel mr-2"></i>Bulk Request
                </button>
                <a href="{{ route('dashboard.manager') }}" class="bg-green-700 hover:bg-green-800 px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <div class="flex items-start">
                <i class="fas fa-check-circle text-green-600 mr-3 mt-0.5"></i>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('warning'))
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle text-yellow-600 mr-3 mt-0.5"></i>
                <div class="flex-1">
                    <span class="block sm:inline">{{ session('warning') }}</span>
                    @if(session('import_errors'))
                        <div class="mt-2 text-sm">
                            <details class="cursor-pointer">
                                <summary class="font-semibold">View Errors ({{ count(session('import_errors')) }})</summary>
                                <ul class="list-disc list-inside mt-2 pl-4">
                                    @foreach(session('import_errors') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </details>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <div class="flex items-start">
                <i class="fas fa-times-circle text-red-600 mr-3 mt-0.5"></i>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Submit New Request -->
    @if($availableProducts->count() > 0)
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-plus-circle text-2xl mr-3"></i>
                    <div>
                        <h2 class="text-lg font-bold">Create New Stock Request</h2>
                        <p class="text-sm text-blue-100">Step 1: Submit your request for review</p>
                    </div>
                </div>
                <span class="bg-white bg-opacity-20 text-white px-3 py-1 rounded-full text-xs font-medium">
                    New Request
                </span>
            </div>
        </div>
        <div class="p-6">
            <p class="text-sm text-gray-600 mb-4">
                Request stock items from other branches. An administrator will review and approve your request.
            </p>
            <form method="POST" action="{{ route('manager.item-requests.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="product_id" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-box text-blue-600 mr-1"></i>Product *
                        </label>
                        <select id="product_id" name="product_id" 
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                required onchange="updateBranchOptions()">
                            <option value="">Select a product</option>
                            @foreach($availableProducts as $product)
                                <option value="{{ $product->id }}" data-units-per-box="{{ $product->quantity_per_box ?? 1 }}">
                                    {{ $product->name }} ({{ $product->barcode }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="from_branch_id" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-store text-green-600 mr-1"></i>From Branch *
                        </label>
                        <select id="from_branch_id" name="from_branch_id" 
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                required>
                            <option value="">Select source branch</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="quantity_of_boxes" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-boxes text-purple-600 mr-1"></i>Quantity of Boxes *
                        </label>
                        <input type="number" id="quantity_of_boxes" name="quantity_of_boxes" min="1" 
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               required>
                        <p id="stock-info" class="text-xs text-gray-500 mt-1"></p>
                    </div>
                    <div>
                        <label for="quantity_per_box" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-cubes text-orange-600 mr-1"></i>Units per Box *
                        </label>
                        <input type="number" id="quantity_per_box" name="quantity_per_box" min="1" 
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               required>
                        <p class="text-xs text-gray-500 mt-1">Auto-filled from product, can be adjusted</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-comment text-teal-600 mr-1"></i>Reason (Optional)
                        </label>
                        <textarea id="reason" name="reason" rows="2"
                               placeholder="e.g., Low stock, high demand" 
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               maxlength="500"></textarea>
                    </div>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-2"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-medium mb-1">Total Units Calculation</p>
                            <p id="total-units-info" class="text-blue-700">Enter boxes and units per box to see total units</p>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium transition-colors inline-flex items-center shadow-md">
                        <i class="fas fa-paper-plane mr-2"></i>Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
    @else
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg mb-6 shadow-sm">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-yellow-800 mb-1">No Products Available</h3>
                <p class="text-sm text-yellow-700">
                    There are currently no products available in other branches to request. All products are either in your branch or out of stock elsewhere.
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Pending Requests -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-yellow-500 to-orange-500 px-6 py-4 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-clock text-2xl mr-3"></i>
                    <div>
                        <h2 class="text-lg font-bold">Pending Requests</h2>
                        <p class="text-sm text-yellow-100">Step 2: Awaiting administrator approval</p>
                    </div>
                </div>
                @if($pendingRequests->count() > 0)
                <span class="bg-white bg-opacity-20 text-white px-3 py-1 rounded-full text-xs font-medium">
                    {{ $pendingRequests->total() }} Pending
                </span>
                @endif
            </div>
        </div>
        <div class="p-6">
        @if($pendingRequests->count() > 0)
            <p class="text-sm text-gray-600 mb-4">These requests are waiting for administrator approval.</p>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From Branch</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Boxes</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Units</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($pendingRequests as $request)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-box text-blue-600"></i>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $request->product->name ?? 'N/A' }}</div>
                                            <div class="text-xs text-gray-500">{{ $request->product->barcode ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fas fa-store text-gray-400 mr-2"></i>
                                        <span class="text-sm text-gray-900">{{ optional($request->fromBranch)->display_label ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                        <i class="fas fa-boxes text-xs mr-1"></i>{{ $request->quantity_of_boxes ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm font-medium text-gray-900">{{ $request->quantity ?? 0 }} units</div>
                                    <div class="text-xs text-gray-500">({{ $request->quantity_of_boxes ?? 0 }} × {{ $request->quantity_per_box ?? 1 }})</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $request->reason ?: '—' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $request->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $request->created_at->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <form method="POST" action="{{ route('manager.item-requests.cancel', $request) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900 text-sm font-medium hover:underline"
                                                onclick="return confirm('Are you sure you want to cancel this request?')">
                                            <i class="fas fa-times-circle mr-1"></i>Cancel
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $pendingRequests->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                    <i class="fas fa-inbox text-gray-400 text-3xl"></i>
                </div>
                <p class="text-gray-600 font-medium mb-2">No Pending Requests</p>
                @if($availableProducts->count() > 0)
                    <p class="text-sm text-gray-500">Submit a new request using the form above. Once submitted, it will appear here while waiting for admin approval.</p>
                @else
                    <p class="text-sm text-gray-500">No products are currently available to request from other branches.</p>
                @endif
            </div>
        @endif
        </div>
    </div>

    <!-- Completed Requests -->
    @if($completedRequests->count() > 0)
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="bg-gradient-to-r from-gray-600 to-gray-700 px-6 py-4 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-history text-2xl mr-3"></i>
                    <div>
                        <h2 class="text-lg font-bold">Request History</h2>
                        <p class="text-sm text-gray-200">View approved, rejected, and canceled requests</p>
                    </div>
                </div>
                <span class="bg-white bg-opacity-20 text-white px-3 py-1 rounded-full text-xs font-medium">
                    {{ $completedRequests->total() }} Total
                </span>
            </div>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From Branch</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Boxes</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Units</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($completedRequests as $request)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-box text-gray-600"></i>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $request->product->name ?? 'N/A' }}</div>
                                            <div class="text-xs text-gray-500">{{ $request->product->barcode ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fas fa-store text-gray-400 mr-2"></i>
                                        <span class="text-sm text-gray-900">{{ optional($request->fromBranch)->display_label ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-boxes text-xs mr-1"></i>{{ $request->quantity_of_boxes ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm font-medium text-gray-900">{{ $request->quantity ?? 0 }} units</div>
                                    <div class="text-xs text-gray-500">({{ $request->quantity_of_boxes ?? 0 }} × {{ $request->quantity_per_box ?? 1 }})</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($request->status === 'approved')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Approved
                                        </span>
                                    @elseif($request->status === 'rejected')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i>Rejected
                                        </span>
                                    @elseif($request->status === 'cancelled')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-ban mr-1"></i>Canceled
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $request->updated_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $request->updated_at->format('H:i') }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $completedRequests->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<script>
// Store product branch data
const productBranchData = {};

@foreach($availableProducts as $product)
    productBranchData[{{ $product->id }}] = {
        quantity_per_box: {{ $product->quantity_per_box ?? 1 }},
        branches: [
            @foreach($product->branchProducts as $bp)
            {
                branch_id: {{ $bp->branch_id }},
                branch_name: "{{ $bp->branch->display_label }}",
                stock: {{ $bp->stock_quantity }},
                boxes: {{ $bp->quantity_of_boxes ?? 0 }}
            },
            @endforeach
        ]
    };
@endforeach

function updateBranchOptions() {
    const productSelect = document.getElementById('product_id');
    const branchSelect = document.getElementById('from_branch_id');
    const stockInfo = document.getElementById('stock-info');
    const boxesInput = document.getElementById('quantity_of_boxes');
    const unitsPerBoxInput = document.getElementById('quantity_per_box');
    const totalUnitsInfo = document.getElementById('total-units-info');
    
    const selectedProductId = productSelect.value;
    
    // Clear branch options and stock info
    branchSelect.innerHTML = '<option value="">Select source branch</option>';
    stockInfo.textContent = '';
    boxesInput.max = '';
    // DON'T clear the box quantity that user may have already entered
    // boxesInput.value = '';
    totalUnitsInfo.textContent = 'Enter boxes and units per box to see total units';
    
    if (selectedProductId && productBranchData[selectedProductId]) {
        const productData = productBranchData[selectedProductId];
        
        // Auto-fill units per box from product
        unitsPerBoxInput.value = productData.quantity_per_box;
        
        // Trigger calculation if boxes were already entered
        if (boxesInput.value) {
            updateTotalUnits();
        }
        
        // Populate branch options
        productData.branches.forEach(function(branch) {
            const option = document.createElement('option');
            option.value = branch.branch_id;
            option.textContent = branch.branch_name + ' (' + branch.boxes + ' boxes, ' + branch.stock + ' units)';
            option.setAttribute('data-boxes', branch.boxes);
            option.setAttribute('data-stock', branch.stock);
            branchSelect.appendChild(option);
        });
    }
}

// Update stock info when branch is selected
document.getElementById('from_branch_id').addEventListener('change', function() {
    const branchSelect = document.getElementById('from_branch_id');
    const stockInfo = document.getElementById('stock-info');
    const boxesInput = document.getElementById('quantity_of_boxes');
    
    const selectedOption = branchSelect.options[branchSelect.selectedIndex];
    
    if (selectedOption && selectedOption.getAttribute('data-boxes')) {
        const availableBoxes = parseInt(selectedOption.getAttribute('data-boxes'));
        const availableStock = parseInt(selectedOption.getAttribute('data-stock'));
        stockInfo.textContent = 'Available: ' + availableBoxes + ' boxes (' + availableStock + ' units)';
        boxesInput.max = availableBoxes;
    } else {
        stockInfo.textContent = '';
        boxesInput.max = '';
    }
});

// Calculate total units when boxes change
document.getElementById('quantity_of_boxes').addEventListener('input', updateTotalUnits);
document.getElementById('quantity_per_box').addEventListener('input', updateTotalUnits);

function updateTotalUnits() {
    const boxes = parseInt(document.getElementById('quantity_of_boxes').value) || 0;
    const unitsPerBox = parseInt(document.getElementById('quantity_per_box').value) || 0;
    const totalUnits = boxes * unitsPerBox;
    const totalUnitsInfo = document.getElementById('total-units-info');
    
    if (boxes > 0 && unitsPerBox > 0) {
        totalUnitsInfo.innerHTML = '<strong>' + boxes + ' boxes</strong> × <strong>' + unitsPerBox + ' units/box</strong> = <strong class="text-blue-900">' + totalUnits + ' total units</strong>';
    } else {
        totalUnitsInfo.textContent = 'Enter boxes and units per box to see total units';
    }
}
</script>

<!-- Bulk Upload Modal -->
<div id="bulkUploadModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-lg bg-white">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 -m-5 mb-4 px-6 py-4 rounded-t-lg">
            <div class="flex justify-between items-center text-white">
                <div class="flex items-center">
                    <i class="fas fa-file-excel text-2xl mr-3"></i>
                    <div>
                        <h3 class="text-xl font-bold">Bulk Item Request Upload</h3>
                        <p class="text-sm text-indigo-100 mt-1">Request multiple items at once using Excel</p>
                    </div>
                </div>
                <button onclick="document.getElementById('bulkUploadModal').classList.add('hidden')" 
                        class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>
        
        <div class="mt-4 space-y-4">
            <!-- Instructions -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-semibold text-blue-900 mb-2 flex items-center">
                    <i class="fas fa-info-circle mr-2"></i>How to Use Bulk Upload
                </h4>
                <ol class="list-decimal list-inside text-sm text-blue-800 space-y-1">
                    <li>Download the Excel template below</li>
                    <li>Fill in the product details, source branch, and quantities</li>
                    <li>Save the file and upload it using the form below</li>
                    <li>Review the results and any errors</li>
                </ol>
            </div>

            <!-- Template Download -->
            <div class="flex justify-center">
                <a href="{{ route('manager.item-requests.download-template') }}" 
                   class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors inline-flex items-center shadow-md">
                    <i class="fas fa-download mr-2"></i>Download Template
                </a>
            </div>

            <!-- Upload Form -->
            <form method="POST" action="{{ route('manager.item-requests.bulk-upload') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="bulk_file" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-upload text-indigo-600 mr-1"></i>Upload Filled Template
                    </label>
                    <input type="file" id="bulk_file" name="file" accept=".xlsx,.xls" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-xs text-gray-500 mt-1">Accepted formats: .xlsx, .xls (Max: 2MB)</p>
                </div>

                <!-- Template Format Info -->
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h4 class="font-semibold text-purple-900 mb-2">Expected Columns:</h4>
                    <div class="text-sm text-purple-800 space-y-1">
                        <div class="grid grid-cols-2 gap-2">
                            <div><i class="fas fa-check text-purple-600 mr-1"></i>Product Name or Barcode</div>
                            <div><i class="fas fa-check text-purple-600 mr-1"></i>From Branch</div>
                            <div><i class="fas fa-check text-purple-600 mr-1"></i>Quantity of Boxes</div>
                            <div><i class="fas fa-check text-purple-600 mr-1"></i>Units per Box</div>
                            <div class="col-span-2"><i class="fas fa-check text-purple-600 mr-1"></i>Reason (Optional)</div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" 
                            onclick="document.getElementById('bulkUploadModal').classList.add('hidden')"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg font-medium transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-medium transition-colors inline-flex items-center shadow-md">
                        <i class="fas fa-upload mr-2"></i>Upload & Process
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
