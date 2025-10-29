@extends('layouts.app')

@section('title', 'Item Requests')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Request Items</h1>
                <p class="text-sm text-gray-600">Request stock transfers from other branches for {{ $manager->branch->display_label }}</p>
            </div>
            <a href="{{ route('dashboard.manager') }}" class="text-sm text-blue-600 hover:underline">Back to dashboard</a>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                <p class="text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <p class="text-red-800">{{ session('error') }}</p>
            </div>
        @endif
    </div>

    <!-- Submit New Request -->
    @if($availableProducts->count() > 0)
    <div class="bg-white shadow rounded-lg p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-plus-circle text-blue-600 mr-2"></i>Create New Stock Request
            </h2>
            <span class="text-xs bg-blue-100 text-blue-800 px-3 py-1 rounded-full">Step 1: Submit Request</span>
        </div>
        <p class="text-sm text-gray-600 mb-4">Request stock items from other branches. An administrator will review and approve your request.</p>
        <form method="POST" action="{{ route('manager.item-requests.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                    <select id="product_id" name="product_id" class="w-full border rounded px-3 py-2" required onchange="updateBranchOptions()">
                        <option value="">Select a product</option>
                        @foreach($availableProducts as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="from_branch_id" class="block text-sm font-medium text-gray-700 mb-1">From Branch</label>
                    <select id="from_branch_id" name="from_branch_id" class="w-full border rounded px-3 py-2" required>
                        <option value="">Select source branch</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                    <input type="number" id="quantity" name="quantity" min="1" class="w-full border rounded px-3 py-2" required>
                    <p id="stock-info" class="text-xs text-gray-500 mt-1"></p>
                </div>
                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Reason (Optional)</label>
                    <input type="text" id="reason" name="reason" placeholder="e.g., Low stock, high demand" class="w-full border rounded px-3 py-2" maxlength="500">
                </div>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                <i class="fas fa-paper-plane mr-2"></i>Submit Request
            </button>
        </form>
    </div>
    @else
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">No Products Available</h3>
                <p class="text-sm text-yellow-700 mt-1">
                    There are currently no products available in other branches to request. All products are either in your branch or out of stock elsewhere.
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Pending Requests -->
    <div class="bg-white shadow rounded-lg p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-clock text-yellow-600 mr-2"></i>Pending Requests
            </h2>
            <span class="text-xs bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full">Step 2: Awaiting Admin Approval</span>
        </div>
        <p class="text-sm text-gray-600 mb-4">These requests are waiting for administrator approval.</p>
        
        @if($pendingRequests->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($pendingRequests as $request)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $request->product->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $request->product->sku ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ optional($request->fromBranch)->display_label ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $request->quantity }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $request->reason ?: '—' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $request->created_at->format('M d, Y') }}</div>
                                    <div class="text-sm text-gray-500">{{ $request->created_at->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <form method="POST" action="{{ route('manager.item-requests.cancel', $request) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900 text-sm"
                                                onclick="return confirm('Are you sure you want to cancel this request?')">
                                            Cancel
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
            <div class="text-center py-8">
                <div class="text-gray-400 text-4xl mb-4">
                    <i class="fas fa-inbox"></i>
                </div>
                <p class="text-gray-500 font-medium">No Pending Requests</p>
                @if($availableProducts->count() > 0)
                    <p class="text-sm text-gray-400 mt-2">Submit a new request using the form above. Once submitted, it will appear here while waiting for admin approval.</p>
                @else
                    <p class="text-sm text-gray-400 mt-2">No products are currently available to request from other branches.</p>
                @endif
            </div>
        @endif
    </div>

    <!-- Recent Completed Requests -->
    @if($completedRequests->count() > 0)
    <div class="bg-white shadow rounded-lg p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-check-circle text-green-600 mr-2"></i>Recent Completed Requests
            </h2>
            <span class="text-xs bg-green-100 text-green-800 px-3 py-1 rounded-full">Step 3: Approved & Completed</span>
        </div>
        <p class="text-sm text-gray-600 mb-4">History of your approved and completed stock transfer requests.</p>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($completedRequests as $request)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $request->product->name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ $request->product->sku ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ optional($request->fromBranch)->display_label ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $request->quantity }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $request->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $request->updated_at->format('M d, Y') }}</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($availableProducts->count() === 0)
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">No Products Available for Request</h3>
                <p class="text-sm text-yellow-700 mt-1">
                    There are no products available in other branches that you can request. Products must be available in other branches with stock to be requestable.
                </p>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
const productBranches = @json($availableProducts->mapWithKeys(function ($product) {
    return [$product->id => $product->branchProducts->map(function ($bp) {
        return [
            'branch_id' => $bp->branch_id,
            'branch_name' => $bp->branch->display_label,
            'stock' => $bp->stock_quantity
        ];
    })->values()];
}));

function updateBranchOptions() {
    const productSelect = document.getElementById('product_id');
    const branchSelect = document.getElementById('from_branch_id');
    const stockInfo = document.getElementById('stock-info');
    const quantityInput = document.getElementById('quantity');
    
    const selectedProductId = productSelect.value;
    
    // Clear branch options
    branchSelect.innerHTML = '<option value="">Select source branch</option>';
    stockInfo.textContent = '';
    quantityInput.max = '';
    
    if (selectedProductId && productBranches[selectedProductId]) {
        const branches = productBranches[selectedProductId];
        branches.forEach(branch => {
            const option = document.createElement('option');
            option.value = branch.branch_id;
            option.textContent = `${branch.branch_name} (${branch.stock} available)`;
            branchSelect.appendChild(option);
        });
    }
}

// Update stock info when branch is selected
document.getElementById('from_branch_id').addEventListener('change', function() {
    const productSelect = document.getElementById('product_id');
    const branchSelect = document.getElementById('from_branch_id');
    const stockInfo = document.getElementById('stock-info');
    const quantityInput = document.getElementById('quantity');
    
    const selectedProductId = productSelect.value;
    const selectedBranchId = parseInt(branchSelect.value);
    
    if (selectedProductId && selectedBranchId && productBranches[selectedProductId]) {
        const branch = productBranches[selectedProductId].find(b => b.branch_id === selectedBranchId);
        if (branch) {
            stockInfo.textContent = `Available stock: ${branch.stock}`;
            quantityInput.max = branch.stock;
        }
    } else {
        stockInfo.textContent = '';
        quantityInput.max = '';
    }
});
</script>
@endsection