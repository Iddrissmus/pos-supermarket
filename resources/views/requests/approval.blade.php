@extends('layouts.app')

@section('title', 'Request Approvals')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Pending Request Approvals</h1>
                <p class="text-sm text-gray-600">
                    @if(auth()->user()->role === 'business_admin')
                        Review and approve/reject all pending item requests for your business
                    @else
                        Review requests for items from your branch
                    @endif
                </p>
            </div>
            <a href="{{ route('dashboard.business-admin') }}" class="text-sm text-blue-600 hover:underline">Back to dashboard</a>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                <p class="text-green-800"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <p class="text-red-800"><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</p>
            </div>
        @endif
        
        @if(session('info'))
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <p class="text-blue-800"><i class="fas fa-info-circle mr-2"></i>{{ session('info') }}</p>
            </div>
        @endif
    </div>

    <!-- Pending Requests -->
    <div class="bg-white shadow rounded-lg p-6">
        @if($pendingRequests->count() > 0)
            @php
                $insufficientStockCount = 0;
                foreach($pendingRequests as $req) {
                    $bp = $req->fromBranch->branchProducts->where('product_id', $req->product_id)->first();
                    $available = $bp ? $bp->stock_quantity : 0;
                    if ($available < $req->quantity) {
                        $insufficientStockCount++;
                    }
                }
            @endphp
            
            @if($insufficientStockCount > 0)
                <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                        <div>
                            <h4 class="text-yellow-800 font-semibold">Insufficient Stock Warning</h4>
                            <p class="text-yellow-700 text-sm mt-1">
                                {{ $insufficientStockCount }} request(s) cannot be approved due to insufficient stock at the source branch. 
                                Please assign more stock to the source branch first or reject these requests.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transfer Route</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($pendingRequests as $request)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $request->product->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">SKU: {{ $request->product->sku ?? 'N/A' }}</div>
                                    @if($request->reason)
                                        <div class="text-xs text-blue-600 mt-1 bg-blue-50 px-2 py-1 rounded">
                                            <i class="fas fa-comment-dots mr-1"></i>{{ $request->reason }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <div class="flex items-center">
                                            <span class="font-medium">{{ optional($request->fromBranch)->display_label ?? 'N/A' }}</span>
                                            <span class="mx-2 text-gray-400">→</span>
                                            <span>{{ optional($request->toBranch)->display_label ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $request->quantity }}</div>
                                    @php
                                        $sourceBranchProduct = $request->fromBranch->branchProducts
                                            ->where('product_id', $request->product_id)->first();
                                        $availableStock = $sourceBranchProduct ? $sourceBranchProduct->stock_quantity : 0;
                                    @endphp
                                    <div class="text-xs {{ $availableStock >= $request->quantity ? 'text-green-600' : 'text-red-600' }}">
                                        Available: {{ $availableStock }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($request->requestedByUser)
                                        <div class="text-sm text-gray-900">{{ $request->requestedByUser->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $request->requestedByUser->email }}</div>
                                    @else
                                        <div class="text-sm text-gray-900 flex items-center">
                                            <i class="fas fa-robot mr-2 text-blue-500"></i>
                                            <span>System Generated</span>
                                        </div>
                                        <div class="text-xs text-gray-500">Auto-created (Low Stock)</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $request->created_at->format('M d, Y') }}</div>
                                    <div class="text-sm text-gray-500">{{ $request->created_at->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex justify-end space-x-2">
                                        <!-- Approve Button -->
                                        @php
                                            $sourceBranchProduct = $request->fromBranch->branchProducts
                                                ->where('product_id', $request->product_id)->first();
                                            $availableStock = $sourceBranchProduct ? $sourceBranchProduct->stock_quantity : 0;
                                            $canApprove = $availableStock >= $request->quantity;
                                        @endphp
                                        
                                        <button onclick="openApprovalModal({{ $request->id }}, 'approve')" 
                                                class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 transition {{ !$canApprove ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                {{ !$canApprove ? 'disabled title="Insufficient stock at source branch"' : '' }}>
                                            <i class="fas fa-check mr-1"></i>Approve
                                        </button>
                                        
                                        <!-- Reject Button -->
                                        <button onclick="openApprovalModal({{ $request->id }}, 'reject')" 
                                                class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 transition">
                                            <i class="fas fa-times mr-1"></i>Reject
                                        </button>
                                    </div>
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
                    <i class="fas fa-check-circle"></i>
                </div>
                <p class="text-gray-500">No pending requests to review.</p>
                <p class="text-sm text-gray-400 mt-2">All caught up! New requests will appear here when submitted.</p>
            </div>
        @endif
    </div>
</div>

<!-- Approval/Rejection Modal -->
<div id="approvalModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 m-4 max-w-md w-full">
        <div class="mb-4">
            <h3 id="modalTitle" class="text-lg font-medium text-gray-900"></h3>
            <p id="modalDescription" class="text-sm text-gray-600 mt-1"></p>
        </div>
        
        <form id="approvalForm" method="POST">
            @csrf
            <div class="mb-4">
                <label for="note" id="noteLabel" class="block text-sm font-medium text-gray-700 mb-1"></label>
                <textarea id="note" name="note" rows="3" 
                          class="w-full border rounded px-3 py-2 text-sm" 
                          placeholder="Enter your note here..."></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeApprovalModal()" 
                        class="px-4 py-2 text-sm text-gray-700 bg-gray-200 rounded hover:bg-gray-300 transition">
                    Cancel
                </button>
                <button type="submit" id="submitButton" 
                        class="px-4 py-2 text-sm text-white rounded transition">
                    Confirm
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openApprovalModal(requestId, action) {
    console.log('Opening modal for request:', requestId, 'action:', action);
    
    const modal = document.getElementById('approvalModal');
    const form = document.getElementById('approvalForm');
    const title = document.getElementById('modalTitle');
    const description = document.getElementById('modalDescription');
    const noteLabel = document.getElementById('noteLabel');
    const note = document.getElementById('note');
    const submitButton = document.getElementById('submitButton');
    
    if (!modal || !form) {
        console.error('Modal or form not found!');
        alert('Error: Modal elements not found. Please refresh the page.');
        return;
    }
    
    if (action === 'approve') {
        form.action = `/requests/${requestId}/approve`;
        title.textContent = 'Approve Request';
        description.textContent = 'Are you sure you want to approve this request? Stock will be transferred automatically.';
        noteLabel.textContent = 'Approval Note (Optional)';
        note.name = 'approval_note';
        note.required = false;
        submitButton.textContent = 'Approve Request';
        submitButton.className = 'px-4 py-2 text-sm text-white bg-green-600 rounded hover:bg-green-700 transition';
    } else if (action === 'reject') {
        form.action = `/requests/${requestId}/reject`;
        title.textContent = 'Reject Request';
        description.textContent = 'Please provide a reason for rejecting this request.';
        noteLabel.textContent = 'Rejection Reason';
        note.name = 'rejection_reason';
        note.required = true;
        submitButton.textContent = 'Reject Request';
        submitButton.className = 'px-4 py-2 text-sm text-white bg-red-600 rounded hover:bg-red-700 transition';
    }
    
    note.value = '';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    console.log('Modal opened successfully');
}

function closeApprovalModal() {
    const modal = document.getElementById('approvalModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        console.log('Modal closed');
    }
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('approvalModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeApprovalModal();
            }
        });
    }
    console.log('Approval page JavaScript loaded');
});
</script>
@endsection