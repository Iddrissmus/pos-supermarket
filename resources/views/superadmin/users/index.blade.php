@extends('layouts.app')

@section('title', 'System Users')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">System Users</h1>
            <p class="mt-2 text-sm text-gray-500">Manage access and roles for all users across the platform.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <a href="{{ route('system-users.create') }}" 
               class="inline-flex items-center justify-center rounded-lg border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add User
            </a>
        </div>
    </div>

    <!-- Notifications -->
    @if(session('success'))
        <div class="rounded-md bg-green-50 p-4 border border-green-200 mb-6 flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if($usersByBusiness->isEmpty())
        <div class="text-center py-16 bg-white rounded-xl border border-gray-200 border-dashed">
            <div class="mx-auto h-12 w-12 text-gray-300">
                <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <p class="mt-4 text-sm text-gray-500">No users found in the system.</p>
        </div>
    @else
        <div class="space-y-6">
            @foreach($businesses->sortBy('name') as $business)
                @if(isset($usersByBusiness[$business->id]))
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: true }">
                        <!-- Collapsible Header -->
                        <button type="button" 
                                class="w-full px-6 py-4 flex items-center justify-between bg-gray-50/50 hover:bg-gray-50 transition-colors border-b border-gray-100"
                                onclick="toggleSection('business-{{ $business->id }}')">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0 h-8 w-8 rounded-lg bg-indigo-100 flex items-center justify-center border border-indigo-200">
                                    <span class="text-xs font-bold text-indigo-700">{{ strtoupper(substr($business->name, 0, 2)) }}</span>
                                </div>
                                <div class="text-left">
                                    <h2 class="text-sm font-bold text-gray-900">{{ $business->name }}</h2>
                                    <p class="text-xs text-gray-500">ID: #{{ $business->id }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $usersByBusiness[$business->id]->count() }} users
                                </span>
                                <svg id="icon-business-{{ $business->id }}" class="w-5 h-5 text-gray-400 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </button>

                        <div id="business-{{ $business->id }}" class="block">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Assigned Branch</th>
                                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($usersByBusiness[$business->id] as $user)
                                            <tr class="hover:bg-gray-50/80 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-xs font-bold text-gray-600 border border-gray-200">
                                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                                        </div>
                                                        <div class="ml-3">
                                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                            <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @php
                                                        $roleStyles = [
                                                            'superadmin' => 'bg-purple-100 text-purple-700',
                                                            'business_admin' => 'bg-blue-100 text-blue-700',
                                                            'manager' => 'bg-emerald-100 text-emerald-700',
                                                            'cashier' => 'bg-orange-100 text-orange-700',
                                                        ];
                                                        $style = $roleStyles[$user->role] ?? 'bg-gray-100 text-gray-700';
                                                    @endphp
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $style }}">
                                                        {{ ucwords(str_replace('_', ' ', $user->role)) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($user->status === 'active')
                                                        <span class="inline-flex items-center text-xs font-medium text-green-700">
                                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-2"></span>
                                                            Active
                                                        </span>
                                                    @elseif($user->status === 'inactive')
                                                        <span class="inline-flex items-center text-xs font-medium text-gray-600">
                                                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-2"></span>
                                                            Inactive
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center text-xs font-medium text-red-700">
                                                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full mr-2"></span>
                                                            Blocked
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $user->branch ? $user->branch->name : '—' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                                    <a href="{{ route('system-users.edit', $user->id) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors">Edit</a>
                                                    
                                                    @if($user->id !== auth()->id())
                                                        <span class="text-gray-300">|</span>
                                                        
                                                        @if($user->status !== 'active')
                                                            <form action="{{ route('system-users.activate', $user->id) }}" method="POST" class="inline">
                                                                @csrf
                                                                <button type="submit" class="text-green-600 hover:text-green-900 transition-colors" title="Activate">Enable</button>
                                                            </form>
                                                        @elseif($user->status !== 'blocked')
                                                            <form action="{{ route('system-users.block', $user->id) }}" method="POST" class="inline">
                                                                @csrf
                                                                <button type="submit" class="text-red-600 hover:text-red-900 transition-colors" onclick="return confirm('Block this user?')" title="Block">Block</button>
                                                            </form>
                                                        @endif
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

            <!-- Unassigned Section -->
            @if(isset($usersByBusiness['unassigned']))
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mt-6">
                    <button type="button" 
                            class="w-full px-6 py-4 flex items-center justify-between bg-gray-50/50 hover:bg-gray-50 transition-colors border-b border-gray-100"
                            onclick="toggleSection('unassigned-section')">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-users text-gray-400"></i>
                            <h2 class="text-sm font-bold text-gray-900">Unassigned Users</h2>
                        </div>
                        <div class="flex items-center space-x-3">
                             <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $usersByBusiness['unassigned']->count() }}
                            </span>
                             <svg id="icon-unassigned-section" class="w-5 h-5 text-gray-400 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </button>

                    <div id="unassigned-section" class="hidden">
                         <!-- Bulk Actions & Table (Simplified for brevity, similar structure to above) -->
                         <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($usersByBusiness['unassigned'] as $user)
                                        <tr class="hover:bg-gray-50/80 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-600">
                                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                        <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                    {{ ucwords(str_replace('_', ' ', $user->role)) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($user->status === 'active')
                                                    <span class="inline-flex items-center text-xs font-medium text-green-700">Active</span>
                                                @else
                                                    <span class="inline-flex items-center text-xs font-medium text-gray-500">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('system-users.edit', $user->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
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
function toggleSection(id) {
    const el = document.getElementById(id);
    const icon = document.getElementById('icon-' + id);
    if (el.classList.contains('hidden')) {
        el.classList.remove('hidden');
        icon.classList.add('rotate-180');
    } else {
        el.classList.add('hidden');
        icon.classList.remove('rotate-180');
    }
}
</script>
@endsection
