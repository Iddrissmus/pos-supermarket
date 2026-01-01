@extends('layouts.app')

@section('title', 'System Activity Logs')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Top Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Activity Logs</h1>
            <p class="mt-2 text-sm text-gray-500">Comprehensive audit trail of system events, user actions, and security alerts.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
             <div class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                <span class="flex h-2 w-2 rounded-full bg-indigo-500 mr-2 animate-pulse"></span>
                Monitoring Active
            </div>
             @if(auth()->user()->role === 'superadmin')
                <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                    <i class="fas fa-download mr-2 text-gray-400"></i> Export Logs
                </button>
            @endif
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-50 rounded-lg p-3">
                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide truncate">Total Events</dt>
                            <dd class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_activities']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-50 rounded-lg p-3">
                         <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide truncate">Today</dt>
                            <dd class="text-2xl font-bold text-gray-900">{{ number_format($stats['today_activities']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-50 rounded-lg p-3">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide truncate">Failed Logins (7d)</dt>
                            <dd class="text-2xl font-bold text-gray-900">{{ number_format($stats['failed_logins']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-orange-50 rounded-lg p-3">
                        <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide truncate">Critical Actions</dt>
                            <dd class="text-2xl font-bold text-gray-900">{{ number_format($stats['critical_actions']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8 overflow-hidden">
        <div class="p-5 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-sm font-semibold text-gray-900">Filter Logs</h2>
        </div>
        <div class="p-5">
            <form method="GET" action="{{ route('activity-logs.index') }}">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <!-- Text Search -->
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   class="block w-full pl-10 border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                   placeholder="Search description, IP...">
                        </div>
                    </div>

                    <!-- Action Type -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Action Type</label>
                        <select name="action" class="tom-select block w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Actions</option>
                            @foreach($actionTypes as $type)
                                <option value="{{ $type }}" {{ request('action') == $type ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $type)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- User Filter -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">User</label>
                        <select name="user_id" class="tom-select block w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Business Filter (SuperAdmin) -->
                    @if(auth()->user()->role === 'superadmin' && isset($businesses))
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Business</label>
                         <select name="business_id" class="tom-select block w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Businesses</option>
                            @foreach($businesses as $business)
                                <option value="{{ $business->id }}" {{ request('business_id') == $business->id ? 'selected' : '' }}>
                                    {{ Str::limit($business->name, 20) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Date Range -->
                    <div class="flex space-x-2 lg:col-span-1">
                        <div class="flex-1">
                             <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                             <input type="date" name="start_date" value="{{ request('start_date') }}" class="block w-full border-gray-300 rounded-lg text-xs focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                         <div class="flex-1">
                             <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
                             <input type="date" name="end_date" value="{{ request('end_date') }}" class="block w-full border-gray-300 rounded-lg text-xs focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <a href="{{ route('activity-logs.index') }}" class="text-sm text-gray-500 hover:text-gray-900 mr-4 flex items-center">
                        Clear Filters
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Timeline/Table View -->
    <div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden">
        <ul class="divide-y divide-gray-100">
            @forelse($logs as $log)
                <li class="p-5 hover:bg-gray-50 transition duration-150 ease-in-out">
                    <div class="flex space-x-4">
                        <div class="flex-shrink-0">
                             <span class="h-10 w-10 rounded-full flex items-center justify-center ring-8 ring-white
                                @if(in_array($log->action, ['login', 'create', 'approve'])) bg-emerald-100 text-emerald-600
                                @elseif(in_array($log->action, ['logout', 'update', 'export'])) bg-blue-100 text-blue-600
                                @elseif(in_array($log->action, ['delete', 'failed_login', 'reject'])) bg-red-100 text-red-600
                                @elseif(str_contains($log->action, 'warning')) bg-yellow-100 text-yellow-600
                                @else bg-gray-100 text-gray-500
                                @endif
                             ">
                                @if(str_contains($log->action, 'login'))
                                    <i class="fas fa-sign-in-alt"></i>
                                @elseif(str_contains($log->action, 'logout'))
                                    <i class="fas fa-sign-out-alt"></i>
                                @elseif(str_contains($log->action, 'create'))
                                    <i class="fas fa-plus"></i>
                                @elseif(str_contains($log->action, 'delete'))
                                    <i class="fas fa-trash"></i>
                                @elseif(str_contains($log->action, 'update'))
                                    <i class="fas fa-pen"></i>
                                @else
                                    <i class="fas fa-bolt"></i>
                                @endif
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $log->user ? $log->user->name : 'System' }}
                                    <span class="text-gray-400 font-normal">performed</span>
                                    <span class="font-semibold text-indigo-600">{{ ucwords(str_replace('_', ' ', $log->action)) }}</span>
                                </p>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">{{ $log->created_at->diffForHumans() }}</p>
                                    <p class="text-xs text-gray-400">{{ $log->created_at->format('M d, H:i:s') }}</p>
                                </div>
                            </div>
                            
                            <p class="text-sm text-gray-600 mt-1">{{ $log->description }}</p>

                            <!-- Meta info chips -->
                            <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                @if($log->ip_address)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                        <i class="fas fa-network-wired mr-1.5 text-gray-400"></i>{{ $log->ip_address }}
                                    </span>
                                @endif
                                @if($log->subject_type)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700">
                                        <i class="fas fa-cube mr-1.5 text-indigo-400"></i>{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                    </span>
                                @endif
                                
                                @if($log->properties && (isset($log->properties['old_values']) || isset($log->properties['new_values'])))
                                    <button onclick="toggleDetails('details-{{ $log->id }}')" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 cursor-pointer">
                                        <i class="fas fa-code mr-1.5 text-blue-400"></i>View Data Changes
                                    </button>
                                @endif
                            </div>

                            <!-- Expandable JSON details -->
                            @if($log->properties && (isset($log->properties['old_values']) || isset($log->properties['new_values'])))
                                <div id="details-{{ $log->id }}" class="hidden mt-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @if(isset($log->properties['old_values']))
                                            <div>
                                                <h4 class="text-xs font-bold text-red-600 uppercase mb-2">Old Values</h4>
                                                <pre class="text-xs text-gray-600 whitespace-pre-wrap font-mono bg-white p-2 rounded border border-gray-200">{{ json_encode($log->properties['old_values'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                            </div>
                                        @endif
                                        @if(isset($log->properties['new_values']))
                                            <div>
                                                <h4 class="text-xs font-bold text-green-600 uppercase mb-2">New Values</h4>
                                                <pre class="text-xs text-gray-600 whitespace-pre-wrap font-mono bg-white p-2 rounded border border-gray-200">{{ json_encode($log->properties['new_values'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </li>
            @empty
                <li class="p-10 text-center">
                    <div class="mx-auto h-12 w-12 text-gray-300 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-search text-xl"></i>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900">No logs found</h3>
                    <p class="text-sm text-gray-500 mt-1">Try allowing more lenient filters to see results.</p>
                </li>
            @endforelse
        </ul>
        
        <!-- Pagination -->
        @if($logs->hasPages())
            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $logs->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function toggleDetails(id) {
        document.getElementById(id).classList.toggle('hidden');
    }
</script>
@endsection
