@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-history text-blue-600 mr-3"></i>Activity Logs
                </h1>
                <p class="text-gray-600 mt-2">Comprehensive audit trail of all system activities</p>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500">Security Monitoring</div>
                <div class="text-lg font-semibold text-blue-600">
                    <i class="fas fa-shield-alt mr-2"></i>
                    @if(auth()->user()->role === 'superadmin')
                        System-Wide
                    @else
                        Business Admin Only
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-blue-600 font-medium">Total Activities</p>
                    <p class="text-3xl font-bold text-blue-900 mt-2">{{ number_format($stats['total_activities']) }}</p>
                </div>
                <div class="bg-blue-500 rounded-full p-4">
                    <i class="fas fa-chart-line text-white text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-green-600 font-medium">Today's Activities</p>
                    <p class="text-3xl font-bold text-green-900 mt-2">{{ number_format($stats['today_activities']) }}</p>
                </div>
                <div class="bg-green-500 rounded-full p-4">
                    <i class="fas fa-calendar-day text-white text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-red-600 font-medium">Failed Logins (7d)</p>
                    <p class="text-3xl font-bold text-red-900 mt-2">{{ number_format($stats['failed_logins']) }}</p>
                </div>
                <div class="bg-red-500 rounded-full p-4">
                    <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-orange-600 font-medium">Critical Actions (30d)</p>
                    <p class="text-3xl font-bold text-orange-900 mt-2">{{ number_format($stats['critical_actions']) }}</p>
                </div>
                <div class="bg-orange-500 rounded-full p-4">
                    <i class="fas fa-shield-alt text-white text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ auth()->user()->role === 'superadmin' ? '6' : '5' }} gap-4">
                <!-- Business Filter (SuperAdmin only) -->
                @if(auth()->user()->role === 'superadmin' && isset($businesses))
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-building mr-2"></i>Business
                        </label>
                        <select name="business_id" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Businesses</option>
                            @foreach($businesses as $business)
                                <option value="{{ $business->id }}" {{ request('business_id') == $business->id ? 'selected' : '' }}>
                                    {{ $business->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-2"></i>Search
                    </label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search description..."
                           class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- User Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2"></i>User
                    </label>
                    <select name="user_id" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-bolt mr-2"></i>Action Type
                    </label>
                    <select name="action" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Actions</option>
                        @foreach($actionTypes as $actionType)
                            <option value="{{ $actionType }}" {{ request('action') == $actionType ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $actionType)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Start Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-2"></i>Start Date
                    </label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                           class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- End Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-2"></i>End Date
                    </label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                           class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('activity-logs.index') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-redo mr-2"></i>Reset
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-filter mr-2"></i>Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Activity Timeline -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6">
            <i class="fas fa-stream mr-2 text-blue-600"></i>Activity Timeline
        </h2>

        @if($logs->count() > 0)
            <div class="space-y-4">
                @foreach($logs as $log)
                    <div class="border-l-4 {{ $log->action === 'failed_login' ? 'border-red-500' : ($log->action === 'login' ? 'border-green-500' : 'border-blue-500') }} bg-gray-50 hover:bg-gray-100 rounded-r-lg p-4 transition-all">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <!-- Action Badge -->
                                <div class="flex items-center mb-2">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                        @if($log->action === 'login') bg-green-100 text-green-800
                                        @elseif($log->action === 'logout') bg-gray-100 text-gray-800
                                        @elseif($log->action === 'failed_login') bg-red-100 text-red-800
                                        @elseif($log->action === 'create') bg-blue-100 text-blue-800
                                        @elseif($log->action === 'update') bg-yellow-100 text-yellow-800
                                        @elseif($log->action === 'delete') bg-red-100 text-red-800
                                        @elseif($log->action === 'export') bg-purple-100 text-purple-800
                                        @elseif(str_contains($log->action, 'stock')) bg-indigo-100 text-indigo-800
                                        @elseif(str_contains($log->action, 'price')) bg-orange-100 text-orange-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        <i class="fas 
                                            @if($log->action === 'login') fa-sign-in-alt
                                            @elseif($log->action === 'logout') fa-sign-out-alt
                                            @elseif($log->action === 'failed_login') fa-exclamation-circle
                                            @elseif($log->action === 'create') fa-plus-circle
                                            @elseif($log->action === 'update') fa-edit
                                            @elseif($log->action === 'delete') fa-trash
                                            @elseif($log->action === 'export') fa-file-export
                                            @elseif(str_contains($log->action, 'stock')) fa-boxes
                                            @elseif(str_contains($log->action, 'price')) fa-tag
                                            @else fa-circle
                                            @endif mr-2"></i>
                                        {{ ucwords(str_replace('_', ' ', $log->action)) }}
                                    </span>

                                    @if($log->properties && isset($log->properties['metadata']['severity']) && $log->properties['metadata']['severity'] === 'critical')
                                        <span class="ml-2 px-2 py-1 text-xs font-bold bg-red-600 text-white rounded-full">
                                            <i class="fas fa-shield-alt mr-1"></i>CRITICAL
                                        </span>
                                    @endif
                                </div>

                                <!-- Description -->
                                <p class="text-gray-800 font-medium mb-2">{{ $log->description }}</p>

                                <!-- User & Meta Info -->
                                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                                    <span>
                                        <i class="fas fa-user mr-1"></i>
                                        <strong>{{ $log->user ? $log->user->name : 'System' }}</strong>
                                        @if($log->user)
                                            <span class="text-gray-500">({{ $log->user->email }})</span>
                                        @endif
                                    </span>
                                    <span>
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $log->created_at->format('M d, Y h:i A') }}
                                    </span>
                                    <span>
                                        <i class="fas fa-network-wired mr-1"></i>
                                        {{ $log->ip_address }}
                                    </span>
                                </div>

                                <!-- Additional Details (if any) -->
                                @if($log->properties && (isset($log->properties['old_values']) || isset($log->properties['new_values'])))
                                    <div class="mt-3 pt-3 border-t border-gray-200">
                                        <button type="button" 
                                                onclick="toggleDetails('details-{{ $log->id }}')"
                                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            <i class="fas fa-chevron-down mr-1"></i>View Details
                                        </button>
                                        <div id="details-{{ $log->id }}" class="hidden mt-3 space-y-2">
                                            @if(isset($log->properties['old_values']))
                                                <div class="bg-red-50 rounded p-3">
                                                    <p class="text-xs font-semibold text-red-800 mb-2">
                                                        <i class="fas fa-minus-circle mr-1"></i>Old Values:
                                                    </p>
                                                    <pre class="text-xs text-red-700">{{ json_encode($log->properties['old_values'], JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                            @endif
                                            @if(isset($log->properties['new_values']))
                                                <div class="bg-green-50 rounded p-3">
                                                    <p class="text-xs font-semibold text-green-800 mb-2">
                                                        <i class="fas fa-plus-circle mr-1"></i>New Values:
                                                    </p>
                                                    <pre class="text-xs text-green-700">{{ json_encode($log->properties['new_values'], JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Subject Info (if available) -->
                            @if($log->subject_type)
                                <div class="ml-4 text-right">
                                    <span class="inline-block px-3 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 rounded">
                                        <i class="fas fa-cube mr-1"></i>
                                        {{ class_basename($log->subject_type) }}
                                    </span>
                                    @if($log->subject_id)
                                        <p class="text-xs text-gray-500 mt-1">ID: {{ $log->subject_id }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $logs->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-history text-gray-400 text-6xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No activity logs found</h3>
                <p class="text-gray-500">Try adjusting your filters or check back later.</p>
            </div>
        @endif
    </div>
</div>

<script>
function toggleDetails(elementId) {
    const element = document.getElementById(elementId);
    element.classList.toggle('hidden');
    
    const button = element.previousElementSibling;
    const icon = button.querySelector('i');
    
    if (element.classList.contains('hidden')) {
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
        button.innerHTML = '<i class="fas fa-chevron-down mr-1"></i>View Details';
    } else {
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
        button.innerHTML = '<i class="fas fa-chevron-up mr-1"></i>Hide Details';
    }
}
</script>
@endsection
