@extends('layouts.app')

@section('title', 'Manage Staff (Managers & Cashiers)')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Manage Staff (Managers & Cashiers)</h1>
                <p class="text-sm text-gray-600">Create and manage managers and cashiers for your branches</p>
            </div>
            <a href="{{ route('dashboard.business-admin') }}" class="text-sm text-blue-600 hover:underline">Back to dashboard</a>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                <p class="text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <p class="text-red-800">{{ session('error') }}</p>
            </div>
        @endif
    </div>

    <!-- Create New Staff Member -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Create New Staff Member</h2>
            @if($branches->count() > 0)
                <div class="text-sm text-gray-600">
                    <span class="font-medium">Your Branch:</span> 
                    <span class="text-blue-600">{{ $branches->first()->display_label }}</span>
                </div>
            @endif
        </div>
        <form method="POST" action="{{ route('admin.cashiers.create') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" id="name" name="name" class="w-full border rounded px-3 py-2" required>
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" class="w-full border rounded px-3 py-2" required>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" name="password" class="w-full border rounded px-3 py-2" required minlength="6">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="w-full border rounded px-3 py-2" required minlength="6">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select id="role" name="role" class="w-full border rounded px-3 py-2" required onchange="updateBranchOptions()">
                        <option value="">-- Select role --</option>
                        <option value="manager">Manager</option>
                        <option value="cashier">Cashier</option>
                    </select>
                </div>
                <div>
                    <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Assign to Your Branch</label>
                    <select id="branch_id" name="branch_id" class="w-full border rounded px-3 py-2">
                        <option value="">-- Leave unassigned for now --</option>
                        @foreach($branches as $branch)
                            @php
                                $hasManager = $managers->where('branch_id', $branch->id)->count() > 0;
                                $hasCashier = $cashiers->where('branch_id', $branch->id)->count() > 0;
                            @endphp
                            <option value="{{ $branch->id }}" 
                                    data-has-manager="{{ $hasManager ? '1' : '0' }}"
                                    data-has-cashier="{{ $hasCashier ? '1' : '0' }}">
                                {{ $branch->display_label }}
                                @if($hasManager && $hasCashier)
                                    (Manager & Cashier assigned)
                                @elseif($hasManager)
                                    (Manager assigned)
                                @elseif($hasCashier)
                                    (Cashier assigned)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1" id="branch-help">Select a role first. Your branch can have one manager and one cashier.</p>
                </div>
            </div>
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition">
                Create Staff Member
            </button>
        </form>
    </div>

    <script>
    function updateBranchOptions() {
        const roleSelect = document.getElementById('role');
        const branchSelect = document.getElementById('branch_id');
        const helpText = document.getElementById('branch-help');
        const selectedRole = roleSelect.value;
        
        if (!selectedRole) {
            branchSelect.disabled = true;
            helpText.textContent = 'Select a role first. Your branch can have one manager and one cashier.';
            return;
        }
        
        branchSelect.disabled = false;
        
        // Enable/disable options based on role
        Array.from(branchSelect.options).forEach(option => {
            if (option.value === '') return; // Skip the placeholder option
            
            const hasManager = option.dataset.hasManager === '1';
            const hasCashier = option.dataset.hasCashier === '1';
            
            if (selectedRole === 'manager') {
                option.disabled = hasManager;
                helpText.textContent = hasManager ? 'Your branch already has a manager assigned.' : 'You can assign this manager to your branch.';
            } else if (selectedRole === 'cashier') {
                option.disabled = hasCashier;
                helpText.textContent = hasCashier ? 'Your branch already has a cashier assigned.' : 'You can assign this cashier to your branch.';
            }
        });
    }
    </script>

    <!-- All Managers -->
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">
            <span class="inline-flex items-center">
                <span class="bg-blue-100 text-blue-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded">Managers</span>
                All Managers ({{ $managers->count() }})
            </span>
        </h2>
        
        @if($managers->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($managers as $manager)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="bg-blue-100 text-blue-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded">Manager</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $manager->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $manager->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($manager->branch)
                                        <div class="text-sm text-gray-900">{{ $manager->branch->display_label }}</div>
                                        <div class="text-xs text-gray-500">{{ $manager->branch->business->name }}</div>
                                    @else
                                        <span class="text-sm text-gray-400 italic">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $manager->created_at->format('M d, Y') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                    @if(!$manager->branch_id)
                                        <!-- Assign to branch -->
                                        <form method="POST" action="{{ route('admin.cashiers.assign') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $manager->id }}">
                                            <select name="branch_id" class="text-xs border rounded px-2 py-1" required>
                                                <option value="">Select branch</option>
                                                @foreach($branches as $branch)
                                                    @php
                                                        $branchHasManager = $managers->where('branch_id', $branch->id)->count() > 0;
                                                    @endphp
                                                    @if(!$branchHasManager)
                                                        <option value="{{ $branch->id }}">{{ $branch->display_label }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <button type="submit" class="text-green-600 hover:text-green-900 text-xs ml-1">
                                                Assign
                                            </button>
                                        </form>
                                    @else
                                        <!-- Unassign from branch -->
                                        <form method="POST" action="{{ route('admin.cashiers.unassign') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $manager->id }}">
                                            <button type="submit" 
                                                    class="text-orange-600 hover:text-orange-900 text-sm"
                                                    onclick="return confirm('Are you sure you want to unassign {{ $manager->name }} from {{ $manager->branch->display_label }}?')">
                                                Unassign
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <!-- Delete manager -->
                                    <form method="POST" action="{{ route('admin.cashiers.delete') }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $manager->id }}">
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900 text-sm ml-2"
                                                onclick="return confirm('Are you sure you want to permanently delete {{ $manager->name }}? This action cannot be undone.')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <div class="text-gray-400 text-4xl mb-4">
                    <i class="fas fa-user-tie"></i>
                </div>
                <p class="text-gray-500">No managers in the system yet.</p>
                <p class="text-sm text-gray-400 mt-2">Use the form above to create the first manager.</p>
            </div>
        @endif
    </div>

    <!-- All Cashiers -->
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">
            <span class="inline-flex items-center">
                <span class="bg-green-100 text-green-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded">Cashiers</span>
                All Cashiers ({{ $cashiers->count() }})
            </span>
        </h2>
        
        @if($cashiers->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($cashiers as $cashier)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="bg-green-100 text-green-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded">Cashier</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $cashier->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $cashier->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($cashier->branch)
                                        <div class="text-sm text-gray-900">{{ $cashier->branch->display_label }}</div>
                                        <div class="text-xs text-gray-500">{{ $cashier->branch->business->name }}</div>
                                    @else
                                        <span class="text-sm text-gray-400 italic">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $cashier->created_at->format('M d, Y') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                    @if(!$cashier->branch_id)
                                        <!-- Assign to branch -->
                                        <form method="POST" action="{{ route('admin.cashiers.assign') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $cashier->id }}">
                                            <select name="branch_id" class="text-xs border rounded px-2 py-1" required>
                                                <option value="">Select branch</option>
                                                @foreach($branches as $branch)
                                                    @php
                                                        $branchHasCashier = $cashiers->where('branch_id', $branch->id)->count() > 0;
                                                    @endphp
                                                    @if(!$branchHasCashier)
                                                        <option value="{{ $branch->id }}">{{ $branch->display_label }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <button type="submit" class="text-green-600 hover:text-green-900 text-xs ml-1">
                                                Assign
                                            </button>
                                        </form>
                                    @else
                                        <!-- Unassign from branch -->
                                        <form method="POST" action="{{ route('admin.cashiers.unassign') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $cashier->id }}">
                                            <button type="submit" 
                                                    class="text-orange-600 hover:text-orange-900 text-sm"
                                                    onclick="return confirm('Are you sure you want to unassign {{ $cashier->name }} from {{ $cashier->branch->display_label }}?')">
                                                Unassign
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <!-- Delete cashier -->
                                    <form method="POST" action="{{ route('admin.cashiers.delete') }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $cashier->id }}">
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900 text-sm ml-2"
                                                onclick="return confirm('Are you sure you want to permanently delete {{ $cashier->name }}? This action cannot be undone.')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <div class="text-gray-400 text-4xl mb-4">
                    <i class="fas fa-cash-register"></i>
                </div>
                <p class="text-gray-500">No cashiers in the system yet.</p>
                <p class="text-sm text-gray-400 mt-2">Use the form above to create the first cashier.</p>
            </div>
        @endif
    </div>
</div>
@endsection