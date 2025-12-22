@extends('layouts.app')

@section('title', 'Branch Request Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('superadmin.branch-requests.index') }}" class="text-blue-600 hover:text-blue-800">
            ← Back to Requests
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">Branch Request Details</h1>
                @if($branchRequest->status === 'pending')
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        Pending Review
                    </span>
                @elseif($branchRequest->status === 'approved')
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                        Approved
                    </span>
                @else
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                        Rejected
                    </span>
                @endif
            </div>
        </div>

        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Business Information -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-700 mb-3">Business Information</h2>
                    <div class="space-y-2">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Business Name</label>
                            <p class="text-gray-900">{{ $branchRequest->business->name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Business Admin</label>
                            <p class="text-gray-900">{{ $branchRequest->business->primaryBusinessAdmin->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Request Information -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-700 mb-3">Request Information</h2>
                    <div class="space-y-2">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Requested By</label>
                            <p class="text-gray-900">{{ $branchRequest->requestedBy->name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Request Date</label>
                            <p class="text-gray-900">{{ $branchRequest->created_at->format('F d, Y h:i A') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Branch Details -->
                <div class="md:col-span-2">
                    <h2 class="text-lg font-semibold text-gray-700 mb-3">Proposed Branch Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Branch Name</label>
                            <p class="text-gray-900">{{ $branchRequest->branch_name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Location</label>
                            <p class="text-gray-900">{{ $branchRequest->location }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Address</label>
                            <p class="text-gray-900">{{ $branchRequest->address ?: 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Phone</label>
                            <p class="text-gray-900">{{ $branchRequest->phone ?: 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Email</label>
                            <p class="text-gray-900">{{ $branchRequest->email ?: 'N/A' }}</p>
                        </div>
                        @if($branchRequest->latitude && $branchRequest->longitude)
                            <div>
                                <label class="text-sm font-medium text-gray-500">Coordinates</label>
                                <p class="text-gray-900">{{ $branchRequest->latitude }}, {{ $branchRequest->longitude }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                @if($branchRequest->notes)
                    <div class="md:col-span-2">
                        <label class="text-sm font-medium text-gray-500">Additional Notes</label>
                        <p class="text-gray-900 mt-1">{{ $branchRequest->notes }}</p>
                    </div>
                @endif

                <!-- Review Information -->
                @if($branchRequest->status !== 'pending')
                    <div class="md:col-span-2 border-t pt-4">
                        <h2 class="text-lg font-semibold text-gray-700 mb-3">Review Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Reviewed By</label>
                                <p class="text-gray-900">{{ $branchRequest->reviewedBy->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Review Date</label>
                                <p class="text-gray-900">{{ $branchRequest->reviewed_at ? $branchRequest->reviewed_at->format('F d, Y h:i A') : 'N/A' }}</p>
                            </div>
                            @if($branchRequest->status === 'rejected' && $branchRequest->rejection_reason)
                                <div class="md:col-span-2">
                                    <label class="text-sm font-medium text-red-600">Rejection Reason</label>
                                    <p class="text-gray-900 mt-1 p-3 bg-red-50 rounded">{{ $branchRequest->rejection_reason }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        @if($branchRequest->status === 'pending')
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex gap-4">
                <form action="{{ route('superadmin.branch-requests.approve', $branchRequest) }}" method="POST">
                    @csrf
                    <button type="submit" 
                            onclick="return confirm('Are you sure you want to approve this branch request? This will create a new branch.')"
                            class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                        Approve Request
                    </button>
                </form>

                <button type="button" 
                        onclick="document.getElementById('rejectModal').classList.remove('hidden')"
                        class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">
                    Reject Request
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Branch Request</h3>
            <form action="{{ route('superadmin.branch-requests.reject', $branchRequest) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Reason for Rejection <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="rejection_reason" 
                        name="rejection_reason" 
                        rows="4"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                        placeholder="Please provide a detailed reason for rejection..."></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit" 
                            class="flex-1 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition">
                        Confirm Rejection
                    </button>
                    <button type="button" 
                            onclick="document.getElementById('rejectModal').classList.add('hidden')"
                            class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
