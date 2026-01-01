@extends('layouts.app')

@section('title', 'Staff Management - ' . config('app.name'))

@push('styles')
<style>
    /* Custom scrollbar for better aesthetics */
    .custom-scrollbar::-webkit-scrollbar {
        height: 6px;
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1; 
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #d1d5db; 
        border-radius: 3px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #9ca3af; 
    }
</style>
@endpush

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    
    <!-- Modern Header -->
    <div class="relative bg-gradient-to-r from-emerald-600 to-teal-600 rounded-xl shadow-lg overflow-hidden mb-8">
        <div class="absolute inset-0 bg-white/10" style="background-image: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 20%), radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1) 0%, transparent 20%);"></div>
        <div class="relative p-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-bold text-white tracking-tight flex items-center">
                        <i class="fas fa-users-cog mr-4 text-emerald-200"></i>Staff Management
                    </h1>
                    <p class="mt-2 text-emerald-100 text-lg opacity-90">
                        Manage your branch managers and cashiers in one place
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="openCreateModal()" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-white text-emerald-700 font-bold hover:bg-emerald-50 transition-all shadow-md">
                        <i class="fas fa-plus mr-2"></i> New Staff
                    </button>
                    <button onclick="openAssignModal()" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-emerald-800/50 backdrop-blur-sm border border-emerald-400/30 text-white font-medium hover:bg-emerald-800/70 transition-all">
                        <i class="fas fa-user-plus mr-2"></i> Assign Branch
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-8 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-md flex items-center justify-between shadow-sm">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3 text-xl"></i>
                <p class="text-green-700 font-medium">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="mb-8 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-md flex items-center justify-between shadow-sm">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3 text-xl"></i>
                <p class="text-red-700 font-medium">{{ session('error') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Staff</p>
                <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ $managers->count() + $cashiers->count() }}</h3>
            </div>
            <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center text-gray-600">
                <i class="fas fa-users text-xl"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Active Managers</p>
                <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ $managers->count() }}</h3>
            </div>
            <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center text-green-600">
                <i class="fas fa-user-tie text-xl"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Active Cashiers</p>
                <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ $cashiers->count() }}</h3>
            </div>
            <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center text-orange-600">
                <i class="fas fa-cash-register text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Tabs Styling -->
    <div x-data="{ activeTab: 'managers' }" class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden min-h-[500px]">
        <div class="border-b border-gray-200 bg-gray-50/50 px-6 pt-4">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button @click="activeTab = 'managers'" 
                       :class="activeTab === 'managers' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                       class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200">
                    <i :class="activeTab === 'managers' ? 'text-green-500' : 'text-gray-400 group-hover:text-gray-500'" class="fas fa-user-tie mr-2.5 text-lg"></i>
                    Branch Managers
                    <span :class="activeTab === 'managers' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-900'" class="ml-3 py-0.5 px-2.5 rounded-full text-xs font-medium inline-block">{{ $managers->count() }}</span>
                </button>
                
                <button @click="activeTab = 'cashiers'" 
                       :class="activeTab === 'cashiers' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                       class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200">
                    <i :class="activeTab === 'cashiers' ? 'text-orange-500' : 'text-gray-400 group-hover:text-gray-500'" class="fas fa-cash-register mr-2.5 text-lg"></i>
                    Cashiers
                    <span :class="activeTab === 'cashiers' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-900'" class="ml-3 py-0.5 px-2.5 rounded-full text-xs font-medium inline-block">{{ $cashiers->count() }}</span>
                </button>
            </nav>
        </div>

        <!-- Managers Tab Content -->
        <div x-show="activeTab === 'managers'" class="p-6">
            @if($managers->isEmpty())
                <div class="text-center py-16">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
                        <i class="fas fa-user-tie text-4xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">No managers found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new manager account.</p>
                </div>
            @else
                <div class="overflow-x-auto custom-scrollbar rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Manager Details</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($managers as $manager)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <span class="h-10 w-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center font-bold">
                                                {{ substr($manager->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $manager->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $manager->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($manager->branch)
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                                            {{ $manager->branch->name }}
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-500 border border-gray-200">
                                            Unassigned
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $manager->phone ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($manager->status === 'active')
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <form action="{{ route('admin.staff.delete') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this manager?');" class="inline">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $manager->id }}">
                                            <button type="submit" class="text-red-400 hover:text-red-600 transition-colors p-1" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Cashiers Tab Content -->
        <div x-show="activeTab === 'cashiers'" class="p-6" style="display: none;">
             @if($cashiers->isEmpty())
                <div class="text-center py-16">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
                        <i class="fas fa-cash-register text-4xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">No cashiers found</h3>
                    <p class="mt-1 text-sm text-gray-500">Add cashiers to start processing sales.</p>
                </div>
            @else
                <div class="overflow-x-auto custom-scrollbar rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cashier Details</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($cashiers as $cashier)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <span class="h-10 w-10 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center font-bold">
                                                {{ substr($cashier->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $cashier->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $cashier->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($cashier->branch)
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                                            {{ $cashier->branch->name }}
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-500 border border-gray-200">
                                            Unassigned
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $cashier->phone ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($cashier->status === 'active')
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"> <!-- Updated route to match renamed staff routes -->
                                    <form action="{{ route('admin.staff.delete') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this cashier?');" class="inline">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $cashier->id }}">
                                        <button type="submit" class="text-red-400 hover:text-red-600 transition-colors p-1" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Assign Branch Modal -->
<div id="assignModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity"></div>
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 min-h-[400px]">
                <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-4">
                    <h3 class="text-lg font-bold leading-6 text-gray-900">Assign Staff to Branch</h3>
                    <button onclick="closeAssignModal()" class="text-gray-400 hover:text-gray-500"><i class="fas fa-times"></i></button>
                </div>
                <!-- Updated route to match renamed staff routes -->
                <form action="{{ route('admin.staff.assign') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select Staff Member</label>
                            <select name="user_id" required class="tom-select w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">Choose a staff member...</option>
                                <optgroup label="Managers">
                                    @foreach($managers as $manager)
                                        <option value="{{ $manager->id }}">{{ $manager->name }} ({{ $manager->branch ? $manager->branch->name : 'Unassigned' }})</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Cashiers">
                                    @foreach($cashiers as $cashier)
                                        <option value="{{ $cashier->id }}">{{ $cashier->name }} ({{ $cashier->branch ? $cashier->branch->name : 'Unassigned' }})</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select Branch</label>
                            <select name="branch_id" required class="tom-select w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">Choose a branch...</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }} ({{ $branch->region }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" onclick="closeAssignModal()" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">Assign Staff</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Create Staff Modal -->
<div id="createModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity"></div>
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 min-h-[500px]">
                <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-4">
                    <h3 class="text-lg font-bold leading-6 text-gray-900">Create New Staff Account</h3>
                     <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-500"><i class="fas fa-times"></i></button>
                </div>
                <!-- Updated route to match renamed staff routes -->
                <form action="{{ route('admin.staff.create') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" name="name" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="e.g. John Doe">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" name="email" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="john@example.com">
                        </div>
                         <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="text" name="phone" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                <select name="role" required class="tom-select w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="cashier">Cashier</option>
                                    <option value="manager">Branch Manager</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Initial Password</label>
                            <input type="password" name="password" required minlength="8" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Assign to Branch (Optional)</label>
                            <select name="branch_id" class="tom-select w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">Do not assign yet</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                         <button type="button" onclick="closeCreateModal()" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openAssignModal() {
        document.getElementById('assignModal').classList.remove('hidden');
    }
    function closeAssignModal() {
        document.getElementById('assignModal').classList.add('hidden');
    }
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
    }
</script>
@endsection