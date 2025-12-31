@extends('layouts.app')

@section('title', 'Branch Requests')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Page Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Branch Requests</h1>
            <p class="mt-2 text-sm text-gray-500">Review and manage new branch creation requests from business admins.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-md bg-green-50 p-4 border border-green-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Status Filters -->
    <div class="mb-6">
        <div class="sm:hidden">
            <label for="tabs" class="sr-only">Select a tab</label>
            <select id="tabs" name="tabs" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" onchange="window.location.href=this.value">
                <option value="{{ route('superadmin.branch-requests.index') }}" {{ !request('status') ? 'selected' : '' }}>All Requests ({{ $totalCount }})</option>
                <option value="{{ route('superadmin.branch-requests.index', ['status' => 'pending']) }}" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending ({{ $pendingCount }})</option>
                <option value="{{ route('superadmin.branch-requests.index', ['status' => 'approved']) }}" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved ({{ $approvedCount }})</option>
                <option value="{{ route('superadmin.branch-requests.index', ['status' => 'rejected']) }}" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected ({{ $rejectedCount }})</option>
            </select>
        </div>
        <div class="hidden sm:block">
            <nav class="flex space-x-4" aria-label="Tabs">
                <a href="{{ route('superadmin.branch-requests.index') }}" 
                   class="{{ !request('status') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:text-gray-700' }} px-3 py-2 font-medium text-sm rounded-md transition-colors">
                    All Requests
                    <span class="{{ !request('status') ? 'bg-indigo-200 text-indigo-600' : 'bg-gray-100 text-gray-900' }} hidden ml-3 py-0.5 px-2.5 rounded-full text-xs font-medium md:inline-block">
                        {{ $totalCount }}
                    </span>
                </a>
                <a href="{{ route('superadmin.branch-requests.index', ['status' => 'pending']) }}" 
                   class="{{ request('status') === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'text-gray-500 hover:text-gray-700' }} px-3 py-2 font-medium text-sm rounded-md transition-colors">
                    Pending
                    <span class="{{ request('status') === 'pending' ? 'bg-yellow-200 text-yellow-800' : 'bg-gray-100 text-gray-900' }} hidden ml-3 py-0.5 px-2.5 rounded-full text-xs font-medium md:inline-block">
                        {{ $pendingCount }}
                    </span>
                </a>
                <a href="{{ route('superadmin.branch-requests.index', ['status' => 'approved']) }}" 
                   class="{{ request('status') === 'approved' ? 'bg-green-100 text-green-800' : 'text-gray-500 hover:text-gray-700' }} px-3 py-2 font-medium text-sm rounded-md transition-colors">
                    Approved
                    <span class="{{ request('status') === 'approved' ? 'bg-green-200 text-green-800' : 'bg-gray-100 text-gray-900' }} hidden ml-3 py-0.5 px-2.5 rounded-full text-xs font-medium md:inline-block">
                        {{ $approvedCount }}
                    </span>
                </a>
                <a href="{{ route('superadmin.branch-requests.index', ['status' => 'rejected']) }}" 
                   class="{{ request('status') === 'rejected' ? 'bg-red-100 text-red-800' : 'text-gray-500 hover:text-gray-700' }} px-3 py-2 font-medium text-sm rounded-md transition-colors">
                    Rejected
                    <span class="{{ request('status') === 'rejected' ? 'bg-red-200 text-red-800' : 'bg-gray-100 text-gray-900' }} hidden ml-3 py-0.5 px-2.5 rounded-full text-xs font-medium md:inline-block">
                        {{ $rejectedCount }}
                    </span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Business / Requester</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proposed Branch</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Submitted</th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($requests as $request)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <div class="h-10 w-10 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold border border-indigo-200">
                                            {{ strtoupper(substr($request->business->name, 0, 2)) }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $request->business->name }}</div>
                                        <div class="text-xs text-gray-500">By: {{ $request->requestedBy->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-medium">{{ $request->branch_name }}</div>
                                <div class="text-xs text-gray-500 flex items-center mt-1">
                                    <svg class="flex-shrink-0 h-3 w-3 mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                    </svg>
                                    {{ Str::limit($request->location, 30) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($request->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-yellow-400" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                                        Pending Review
                                    </span>
                                @elseif($request->status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                                        Approved
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-red-400" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                                        Rejected
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $request->created_at->format('M d, Y') }}
                                <span class="block text-xs text-gray-400">{{ $request->created_at->format('h:i A') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('superadmin.branch-requests.show', $request) }}" class="text-indigo-600 hover:text-indigo-900 flex items-center justify-end">
                                    Review Details
                                    <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No requests found</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    @if(request('status'))
                                        No {{ request('status') }} branch requests found.
                                    @else
                                        There are currently no branch requests to display.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
