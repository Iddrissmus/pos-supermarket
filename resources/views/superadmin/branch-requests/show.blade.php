@extends('layouts.app')

@section('title', 'Review Branch Request')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .request-map {
        height: 250px;
        width: 100%;
        border-radius: 0.5rem;
        z-index: 10;
    }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
    
    <!-- Top Navigation -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('superadmin.branch-requests.index') }}" class="text-sm text-gray-500 hover:text-gray-900 mb-2 inline-flex items-center transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Requests
            </a>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight mt-1">Review Branch Request</h1>
        </div>
        <div class="mt-4 sm:mt-0">
             @if($branchRequest->status === 'pending')
                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800 border border-yellow-200 shadow-sm">
                    <span class="w-2 h-2 bg-yellow-400 rounded-full mr-2 animate-pulse"></span>
                    Pending Review
                </span>
            @elseif($branchRequest->status === 'approved')
                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-green-100 text-green-800 border border-green-200 shadow-sm">
                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                    Approved
                </span>
            @else
                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-red-100 text-red-800 border border-red-200 shadow-sm">
                    <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                    Rejected
                </span>
            @endif
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Details -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Proposed Branch Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center">
                    <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 mr-3">
                        <i class="fas fa-store"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Proposed Branch Details</h2>
                        <p class="text-xs text-gray-500">Information about the new location submitted by the admin.</p>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Branch Name</label>
                            <div class="text-sm font-medium text-gray-900 bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                {{ $branchRequest->branch_name }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Region / Location</label>
                             <div class="text-sm font-medium text-gray-900 bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                {{ $branchRequest->location }}
                            </div>
                        </div>
                    </div>

                    @if($branchRequest->latitude && $branchRequest->longitude)
                        <div>
                             <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Location Map</label>
                             <div id="request-map" class="request-map border border-gray-200"></div>
                             <p class="mt-1 text-xs text-gray-400 flex items-center justify-end">
                                 <i class="fas fa-map-pin mr-1"></i> {{ number_format($branchRequest->latitude, 6) }}, {{ number_format($branchRequest->longitude, 6) }}
                             </p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Physical Address</label>
                            <p class="text-sm text-gray-700">{{ $branchRequest->address ?: 'Not provided' }}</p>
                        </div>
                        <div>
                             <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Contact Info</label>
                             <div class="space-y-1">
                                 <p class="text-sm text-gray-700"><i class="fas fa-phone text-gray-400 mr-2"></i>{{ $branchRequest->phone ?: 'N/A' }}</p>
                                 <p class="text-sm text-gray-700"><i class="fas fa-envelope text-gray-400 mr-2"></i>{{ $branchRequest->email ?: 'N/A' }}</p>
                             </div>
                        </div>
                    </div>
                    
                    @if($branchRequest->notes)
                    <div class="pt-4 border-t border-gray-100">
                         <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Requester Notes</label>
                         <div class="bg-yellow-50 p-3 rounded-lg border border-yellow-100 text-sm text-yellow-800">
                             "{{ $branchRequest->notes }}"
                         </div>
                    </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Right Column: Context & Actions -->
        <div class="space-y-6">
            
            <!-- Requester Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-sm font-semibold text-gray-900">Request Context</h2>
                </div>
                <div class="p-5">
                    <div class="flex items-center mb-6">
                        <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600 font-bold border border-blue-200">
                            {{ strtoupper(substr($branchRequest->business->name, 0, 2)) }}
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-bold text-gray-900">{{ $branchRequest->business->name }}</h3>
                            <p class="text-xs text-gray-500">Business ID: #{{ $branchRequest->business->id }}</p>
                        </div>
                    </div>
                    
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Requested By</dt>
                            <dd class="font-medium text-gray-900">{{ $branchRequest->requestedBy->name }}</dd>
                        </div>
                         <div class="flex justify-between">
                            <dt class="text-gray-500">Submitted On</dt>
                            <dd class="font-medium text-gray-900">{{ $branchRequest->created_at->format('M d, Y') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Action Panel (Sticky) -->
            @if($branchRequest->status === 'pending')
                <div class="bg-white rounded-xl shadow-lg border border-indigo-100 p-6 sticky top-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Review Action</h3>
                    
                    <form action="{{ route('superadmin.branch-requests.approve', $branchRequest) }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('Approve this request? This will immediately create new branch.')"
                                class="w-full flex justify-center items-center px-4 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                            <i class="fas fa-check-circle mr-2"></i> Approve & Create
                        </button>
                    </form>

                    <button type="button" 
                            onclick="document.getElementById('rejectModal').classList.remove('hidden')"
                            class="w-full flex justify-center items-center px-4 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                        <i class="fas fa-times-circle mr-2 text-red-500"></i> Reject Request
                    </button>
                </div>
            @endif

            <!-- Outcome Info (If not pending) -->
            @if($branchRequest->status !== 'pending')
                 <div class="bg-gray-50 rounded-xl border border-gray-200 p-5">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Outcome Details</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Reviewed By</dt>
                            <dd class="font-medium text-gray-900">{{ $branchRequest->reviewedBy->name ?? 'System' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Date</dt>
                            <dd class="font-medium text-gray-900">{{ $branchRequest->reviewed_at ? $branchRequest->reviewed_at->format('M d, Y') : '-' }}</dd>
                        </div>
                    </dl>
                    
                    @if($branchRequest->status === 'rejected' && $branchRequest->rejection_reason)
                        <div class="mt-4 pt-4 border-t border-gray-200">
                             <p class="text-xs font-semibold text-red-600 mb-1">Rejection Reason:</p>
                             <p class="text-sm text-gray-700 italic">"{{ $branchRequest->rejection_reason }}"</p>
                        </div>
                    @endif
                 </div>
            @endif

        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('rejectModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
            <form action="{{ route('superadmin.branch-requests.reject', $branchRequest) }}" method="POST">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Reject Branch Request</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 mb-3">Please provide a reason for rejecting this request. The requester will be notified.</p>
                                <textarea name="rejection_reason" rows="3" required class="w-full shadow-sm focus:ring-red-500 focus:border-red-500 block sm:text-sm border-gray-300 rounded-md" placeholder="Enter reason..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Confirm Rejection
                    </button>
                    <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@if($branchRequest->latitude && $branchRequest->longitude)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const lat = {{ $branchRequest->latitude }};
        const lng = {{ $branchRequest->longitude }};
        
        const map = L.map('request-map').setView([lat, lng], 13);
        
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CARTO'
        }).addTo(map);
        
        L.marker([lat, lng]).addTo(map)
            .bindPopup("<b>Proposed Location</b><br>{{ $branchRequest->location }}")
            .openPopup();
            
        setTimeout(() => map.invalidateSize(), 500);
    });
</script>
@endif
@endpush
