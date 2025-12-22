@extends('layouts.app')

@section('title', 'System Users Management')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">System Users Management</h1>
                <p class="text-sm text-gray-600">Manage all users across the system</p>
            </div>
            <a href="{{ route('system-users.create') }}" 
               class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <i class="fas fa-user-plus mr-2"></i>Create New User
            </a>
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
    </div>

    <!-- Users Grouped by Business -->
    @if($usersByBusiness->isEmpty())
        <div class="bg-white shadow rounded-lg p-12 text-center">
            <i class="fas fa-users text-5xl text-gray-300 mb-4"></i>
            <p class="text-xl text-gray-500">No users found</p>
        </div>
    @else
        <div class="space-y-4">
            <!-- Users by Business (Collapsible) -->
            @foreach($businesses->sortBy('name') as $business)
                @if(isset($usersByBusiness[$business->id]))
                    <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
                        <button type="button" 
                                onclick="toggleBusinessSection('business-{{ $business->id }}')"
                                class="w-full bg-white hover:bg-gray-50 px-6 py-4 transition-all shadow-sm hover:shadow-md">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    @if($business->logo)
                                        <img src="{{ asset('storage/' . $business->logo) }}" 
                                             alt="{{ $business->name }}" 
                                             class="h-10 w-10 rounded-lg mr-3 border border-gray-200">
                                    @else
                                        <div class="h-10 w-10 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold mr-3">
                                            {{ strtoupper(substr($business->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div class="text-left">
                                        <h2 class="text-lg font-semibold text-gray-900">{{ $business->name }}</h2>
                                        <p class="text-sm text-gray-500">Business ID: #{{ $business->id }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-sm font-semibold">
                                        {{ $usersByBusiness[$business->id]->count() }} {{ Str::plural('user', $usersByBusiness[$business->id]->count()) }}
                                    </span>
                                    <i class="fas fa-chevron-down text-gray-600 transition-transform duration-200" id="icon-business-{{ $business->id }}"></i>
                                </div>
                            </div>
                        </button>
                        <div id="business-{{ $business->id }}" class="hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($usersByBusiness[$business->id] as $user)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center">
                                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br 
                                                            @if($user->role === 'superadmin') from-purple-500 to-indigo-600
                                                            @elseif($user->role === 'business_admin') from-blue-500 to-cyan-600
                                                            @elseif($user->role === 'manager') from-green-500 to-emerald-600
                                                            @else from-orange-500 to-amber-600
                                                            @endif
                                                            flex items-center justify-center text-white font-semibold mr-3">
                                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                                        </div>
                                                        <div>
                                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                                        @if($user->role === 'superadmin') bg-purple-100 text-purple-800
                                                        @elseif($user->role === 'business_admin') bg-blue-100 text-blue-800
                                                        @elseif($user->role === 'manager') bg-green-100 text-green-800
                                                        @else bg-orange-100 text-orange-800
                                                        @endif">
                                                        @if($user->role === 'business_admin')
                                                            Business Admin
                                                        @else
                                                            {{ ucfirst($user->role) }}
                                                        @endif
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($user->status === 'active')
                                                        <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                            <i class="fas fa-check-circle mr-1"></i>Active
                                                        </span>
                                                    @elseif($user->status === 'inactive')
                                                        <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                            <i class="fas fa-pause-circle mr-1"></i>Inactive
                                                        </span>
                                                    @else
                                                        <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                            <i class="fas fa-ban mr-1"></i>Blocked
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                    @if($user->branch)
                                                        <span class="text-gray-900">{{ $user->branch->name }}</span>
                                                    @else
                                                        <span class="text-gray-400">—</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $user->created_at->format('M d, Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                                    <a href="{{ route('system-users.edit', $user->id) }}" 
                                                       class="text-indigo-600 hover:text-indigo-900"
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <!-- Status Management -->
                                                    @if($user->status !== 'active')
                                                        <form action="{{ route('system-users.activate', $user->id) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="text-green-600 hover:text-green-900"
                                                                    title="Activate">
                                                                <i class="fas fa-check-circle"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    
                                                    @if($user->status !== 'inactive')
                                                        <form action="{{ route('system-users.deactivate', $user->id) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="text-yellow-600 hover:text-yellow-900"
                                                                    title="Deactivate">
                                                                <i class="fas fa-pause-circle"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    
                                                    @if($user->status !== 'blocked' && $user->id !== auth()->id())
                                                        <form action="{{ route('system-users.block', $user->id) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="text-red-600 hover:text-red-900"
                                                                    title="Block"
                                                                    onclick="return confirm('Are you sure you want to block {{ $user->name }}?')">
                                                                <i class="fas fa-ban"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    
                                                    @if($user->id !== auth()->id())
                                                        <form action="{{ route('system-users.destroy', $user->id) }}" 
                                                              method="POST" 
                                                              class="inline"
                                                              onsubmit="return confirm('Are you sure you want to delete {{ $user->name }}?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-gray-400" title="You cannot delete yourself">
                                                            <i class="fas fa-lock"></i>
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

            <!-- Unassigned Users (Collapsible) -->
            @if(isset($usersByBusiness['unassigned']))
                <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
                    <button type="button" 
                            onclick="toggleBusinessSection('unassigned')"
                            class="w-full bg-white hover:bg-gray-50 px-6 py-4 transition-all shadow-sm hover:shadow-md">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-users text-gray-600 text-xl mr-3"></i>
                                <h2 class="text-lg font-semibold text-gray-900">Unassigned Users</h2>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm font-semibold">
                                    {{ $usersByBusiness['unassigned']->count() }} {{ Str::plural('user', $usersByBusiness['unassigned']->count()) }}
                                </span>
                                <i class="fas fa-chevron-down text-gray-600 transition-transform duration-200" id="icon-unassigned"></i>
                            </div>
                        </div>
                    </button>
                    <div id="unassigned" class="hidden">
                        <!-- Bulk Actions Bar -->
                        <div id="bulk-actions-bar" class="hidden bg-red-50 border-b border-red-200 px-6 py-3 flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <span id="selected-count" class="text-sm font-semibold text-red-800">0 users selected</span>
                            </div>
                            <form id="bulk-delete-form" action="{{ route('system-users.bulk-delete') }}" method="POST" class="inline">
                                @csrf
                                <div id="bulk-delete-user-ids-container"></div>
                                <button type="submit" 
                                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold inline-flex items-center"
                                        onclick="return confirm('Are you sure you want to delete the selected users? This action cannot be undone.');">
                                    <i class="fas fa-trash mr-2"></i>
                                    Delete Selected
                                </button>
                            </form>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <input type="checkbox" 
                                                   id="select-all-unassigned" 
                                                   class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                                                   onchange="toggleAllUnassignedUsers(this)">
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($usersByBusiness['unassigned'] as $user)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($user->id !== auth()->id())
                                                    <input type="checkbox" 
                                                           name="user_ids[]" 
                                                           value="{{ $user->id }}"
                                                           class="user-checkbox rounded border-gray-300 text-red-600 focus:ring-red-500"
                                                           onchange="updateBulkActions()">
                                                @else
                                                    <span class="text-gray-400" title="You cannot delete yourself">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br 
                                                        @if($user->role === 'superadmin') from-purple-500 to-indigo-600
                                                        @elseif($user->role === 'business_admin') from-blue-500 to-cyan-600
                                                        @elseif($user->role === 'manager') from-green-500 to-emerald-600
                                                        @else from-orange-500 to-amber-600
                                                        @endif
                                                        flex items-center justify-center text-white font-semibold mr-3">
                                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if($user->role === 'superadmin') bg-purple-100 text-purple-800
                                                    @elseif($user->role === 'business_admin') bg-blue-100 text-blue-800
                                                    @elseif($user->role === 'manager') bg-green-100 text-green-800
                                                    @else bg-orange-100 text-orange-800
                                                    @endif">
                                                    @if($user->role === 'business_admin')
                                                        Business Admin
                                                    @else
                                                        {{ ucfirst($user->role) }}
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($user->status === 'active')
                                                    <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                        <i class="fas fa-check-circle mr-1"></i>Active
                                                    </span>
                                                @elseif($user->status === 'inactive')
                                                    <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-pause-circle mr-1"></i>Inactive
                                                    </span>
                                                @else
                                                    <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                        <i class="fas fa-ban mr-1"></i>Blocked
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $user->created_at->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                                <a href="{{ route('system-users.edit', $user->id) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900"
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <!-- Status Management -->
                                                @if($user->status !== 'active')
                                                    <form action="{{ route('system-users.activate', $user->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="text-green-600 hover:text-green-900"
                                                                title="Activate">
                                                            <i class="fas fa-check-circle"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                @if($user->status !== 'inactive')
                                                    <form action="{{ route('system-users.deactivate', $user->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="text-yellow-600 hover:text-yellow-900"
                                                                title="Deactivate">
                                                            <i class="fas fa-pause-circle"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                @if($user->status !== 'blocked' && $user->id !== auth()->id())
                                                    <form action="{{ route('system-users.block', $user->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="text-red-600 hover:text-red-900"
                                                                title="Block"
                                                                onclick="return confirm('Are you sure you want to block {{ $user->name }}?')">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                @if($user->id !== auth()->id())
                                                    <form action="{{ route('system-users.destroy', $user->id) }}" 
                                                          method="POST" 
                                                          class="inline"
                                                          onsubmit="return confirm('Are you sure you want to delete {{ $user->name }}?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-gray-400" title="You cannot delete yourself">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>

<script>
function toggleBusinessSection(sectionId) {
    const section = document.getElementById(sectionId);
    const icon = document.getElementById('icon-' + sectionId);
    
    if (section.classList.contains('hidden')) {
        section.classList.remove('hidden');
        icon.style.transform = 'rotate(180deg)';
    } else {
        section.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    }
}

// Bulk delete functionality for unassigned users
function toggleAllUnassignedUsers(checkbox) {
    const checkboxes = document.querySelectorAll('#unassigned .user-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkActions();
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('#unassigned .user-checkbox:checked');
    const selectedCount = checkboxes.length;
    const bulkActionsBar = document.getElementById('bulk-actions-bar');
    const selectedCountSpan = document.getElementById('selected-count');
    const bulkDeleteUserIdsContainer = document.getElementById('bulk-delete-user-ids-container');
    
    if (selectedCount > 0) {
        bulkActionsBar.classList.remove('hidden');
        selectedCountSpan.textContent = selectedCount + ' user' + (selectedCount > 1 ? 's' : '') + ' selected';
        
        // Create hidden inputs for each selected user ID (Laravel expects array format)
        bulkDeleteUserIdsContainer.innerHTML = '';
        checkboxes.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = checkbox.value;
            bulkDeleteUserIdsContainer.appendChild(input);
        });
    } else {
        bulkActionsBar.classList.add('hidden');
        bulkDeleteUserIdsContainer.innerHTML = '';
    }
    
    // Update select all checkbox state
    const allCheckboxes = document.querySelectorAll('#unassigned .user-checkbox');
    const selectAllCheckbox = document.getElementById('select-all-unassigned');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = allCheckboxes.length > 0 && checkboxes.length === allCheckboxes.length;
        selectAllCheckbox.indeterminate = checkboxes.length > 0 && checkboxes.length < allCheckboxes.length;
    }
}
</script>
@endsection
