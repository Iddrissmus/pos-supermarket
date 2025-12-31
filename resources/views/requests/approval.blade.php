@extends('layouts.app')

@section('title', 'Request Approvals')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto space-y-8">
    
    <!-- Modern Header -->
    <div class="relative bg-gradient-to-r from-amber-600 to-orange-600 rounded-xl shadow-lg overflow-hidden">
        <div class="absolute inset-0 bg-white/10" style="background-image: radial-gradient(circle at 10% 20%, rgba(255,255,255,0.1) 0%, transparent 20%), radial-gradient(circle at 90% 80%, rgba(255,255,255,0.1) 0%, transparent 20%);"></div>
        <div class="relative p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight flex items-center">
                    <i class="fas fa-tasks mr-3 text-amber-200"></i> Request Approvals
                </h1>
                <p class="mt-2 text-amber-100 text-lg opacity-90 max-w-2xl">
                    @if(auth()->user()->role === 'business_admin')
                        Review usage and approve stock transfers for your business branches.
                    @else
                        Manage incoming stock requests from your branch.
                    @endif
                </p>
            </div>
             <div class="flex flex-wrap gap-3">
                 <a href="{{ route('dashboard.business-admin') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors font-medium backdrop-blur-sm border border-white/10 flex items-center">
                    <i class="fas fa-arrow-left mr-2 opacity-80"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Stats & Warnings -->
    @php
        $pendingCount = $pendingRequests->count();
        $insufficientStockCount = 0;
        foreach($pendingRequests as $req) {
            $bp = $req->fromBranch->branchProducts->where('product_id', $req->product_id)->first();
            $available = $bp ? $bp->stock_quantity : 0;
            if ($available < $req->quantity) {
                $insufficientStockCount++;
            }
        }
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Pending Stats -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pending Requests</p>
                <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ $pendingCount }}</h3>
            </div>
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-clock text-xl"></i>
            </div>
        </div>

        @if($insufficientStockCount > 0)
        <!-- Warning Card -->
        <div class="bg-red-50 rounded-xl shadow-sm border border-red-100 p-6 flex items-start gap-4 col-span-2">
            <div class="w-10 h-10 bg-red-100 text-red-600 rounded-lg flex-shrink-0 flex items-center justify-center">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <h4 class="text-lg font-bold text-red-900">Insufficient Stock Warning</h4>
                <p class="text-red-700 mt-1 text-sm">
                    <strong>{{ $insufficientStockCount }} request(s)</strong> cannot be fulfilled because the source branch has insufficient stock. 
                    These requests are highlighted in red below.
                </p>
            </div>
        </div>
        @endif
    </div>

    <!-- Notification Container -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
            <i class="fas fa-check-circle text-green-500"></i>
            <p class="text-green-800 font-medium">{{ session('success') }}</p>
        </div>
    @endif
    
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3">
            <i class="fas fa-times-circle text-red-500"></i>
            <p class="text-red-800 font-medium">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Requests Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($pendingRequests->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Product Info</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Route</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Requester</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($pendingRequests as $request)
                            @php
                                $sourceBranchProduct = $request->fromBranch->branchProducts
                                    ->where('product_id', $request->product_id)->first();
                                $availableStock = $sourceBranchProduct ? $sourceBranchProduct->stock_quantity : 0;
                                $canApprove = $availableStock >= $request->quantity;
                            @endphp
                            <tr class="group hover:bg-gray-50/80 transition-colors {{ !$canApprove ? 'bg-red-50/30' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="ml-0">
                                            <div class="text-sm font-bold text-gray-900 group-hover:text-amber-600 transition-colors">
                                                {{ $request->product->name ?? 'Unknown Product' }}
                                            </div>
                                            <div class="text-xs text-gray-500 font-mono">
                                                {{ $request->product->barcode ?? 'No Barcode' }}
                                            </div>
                                            @if($request->reason)
                                                <div class="mt-1 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] bg-blue-50 text-blue-600 font-medium">
                                                    <i class="fas fa-comment mr-1"></i> {{ Str::limit($request->reason, 20) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2 text-sm text-gray-700">
                                        <div class="flex flex-col items-center min-w-[3rem]">
                                            <span class="font-bold text-gray-900">{{ optional($request->fromBranch)->display_label ?? 'N/A' }}</span>
                                            <span class="text-[10px] text-gray-400">Source</span>
                                        </div>
                                        <i class="fas fa-long-arrow-alt-right text-gray-400"></i>
                                        <div class="flex flex-col items-center min-w-[3rem]">
                                            <span class="font-bold text-gray-900">{{ optional($request->toBranch)->display_label ?? 'N/A' }}</span>
                                            <span class="text-[10px] text-gray-400">Dest</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="inline-flex flex-col items-center justify-center p-2 rounded-lg bg-gray-50 border border-gray-100 min-w-[4rem]">
                                        <span class="text-lg font-bold text-gray-800">{{ $request->quantity }}</span>
                                        <span class="text-[10px] uppercase text-gray-500 font-semibold tracking-wide">Requested</span>
                                    </div>
                                    <div class="mt-2 text-xs font-medium {{ $canApprove ? 'text-green-600' : 'text-red-600' }}">
                                        Available: {{ $availableStock }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($request->requestedByUser)
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 font-bold text-xs mr-2">
                                                {{ substr($request->requestedByUser->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $request->requestedByUser->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $request->created_at->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center text-gray-500">
                                            <i class="fas fa-robot text-lg mr-2"></i>
                                            <div class="text-xs">
                                                <p class="font-bold text-gray-700">System Auto-Request</p>
                                                <p>{{ $request->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <button onclick="openApprovalModal({{ $request->id }}, 'approve')" 
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                                {{ !$canApprove ? 'disabled title="Insufficient Stock"' : '' }}>
                                            <i class="fas fa-check mr-1.5"></i> Approve
                                        </button>
                                        <button onclick="openApprovalModal({{ $request->id }}, 'reject')" 
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                            <i class="fas fa-times mr-1.5"></i> Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($pendingRequests->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $pendingRequests->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-16">
                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-check-double text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">No Pending Requests</h3>
                <p class="text-gray-500 max-w-sm mx-auto">
                    All set! There are no stock transfer requests pending approval at this time.
                </p>
            </div>
        @endif
    </div>

</div>

<!-- Modern Modal -->
<div id="approvalModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" onclick="closeApprovalModal()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <form id="approvalForm" method="POST">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div id="modalIconBg" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i id="modalIcon" class="fas fa-check text-green-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-bold text-gray-900" id="modalTitle">
                                Approve Request
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" id="modalDescription">
                                    Are you sure you want to approve this request?
                                </p>
                            </div>
                            
                            <div class="mt-4">
                                <label for="note" id="noteLabel" class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                                <textarea id="note" name="note" rows="3" 
                                        class="shadow-sm focus:ring-amber-500 focus:border-amber-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-lg p-3" 
                                        placeholder="Add a note..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" id="submitButton" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Confirm
                    </button>
                    <button type="button" onclick="closeApprovalModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openApprovalModal(requestId, action) {
        const modal = document.getElementById('approvalModal');
        const form = document.getElementById('approvalForm');
        const title = document.getElementById('modalTitle');
        const description = document.getElementById('modalDescription');
        const noteLabel = document.getElementById('noteLabel');
        const note = document.getElementById('note');
        const submitButton = document.getElementById('submitButton');
        const iconBg = document.getElementById('modalIconBg');
        const icon = document.getElementById('modalIcon');
        
        if (action === 'approve') {
            form.action = `/requests/approval/${requestId}/approve`;
            title.textContent = 'Approve Stock Transfer';
            description.innerHTML = 'This will immediately transfer stock from the source branch. <br><strong>This action cannot be undone.</strong>';
            
            noteLabel.textContent = 'Approval Note (Optional)';
            note.name = 'approval_note';
            note.required = false;
            note.placeholder = "e.g., Approved via phone confirm";
            
            submitButton.textContent = 'Confirm Approval';
            submitButton.className = 'w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm';
            
            iconBg.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10';
            icon.className = 'fas fa-check text-green-600';
            
        } else if (action === 'reject') {
            form.action = `/requests/approval/${requestId}/reject`;
            title.textContent = 'Reject Request';
            description.textContent = 'Please provide a reason for rejecting this request. The requester will be notified.';
            
            noteLabel.textContent = 'Rejection Reason (Required)';
            note.name = 'rejection_reason';
            note.required = true;
            note.placeholder = "e.g., Use existing stock first";
            
            submitButton.textContent = 'Reject Request';
            submitButton.className = 'w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm';
            
            iconBg.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10';
            icon.className = 'fas fa-times text-red-600';
        }
        
        note.value = '';
        modal.classList.remove('hidden');
    }

    function closeApprovalModal() {
        document.getElementById('approvalModal').classList.add('hidden');
    }
</script>
@endsection