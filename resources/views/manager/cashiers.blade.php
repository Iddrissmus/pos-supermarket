@extends('layouts.app')

@section('title', 'Manage Cashiers')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Manage Cashiers</h1>
                <p class="text-sm text-gray-600">Assign and manage cashiers for {{ $managedBranch->display_label }}</p>
            </div>
            <a href="{{ route('dashboard.manager') }}" class="text-sm text-blue-600 hover:underline">Back to dashboard</a>
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

    <!-- Create New Cashier - Only show if no cashier assigned -->
    @if($assignedCashiers->count() === 0)
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Create New Cashier for Your Branch</h2>
        <form method="POST" action="{{ route('manager.cashiers.create') }}" class="space-y-4">
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
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition">
                Create Cashier
            </button>
        </form>
    </div>
    @else
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Cashier Limit Reached</h3>
                <p class="text-sm text-blue-700 mt-1">
                    Your branch can only have one cashier at a time. To assign a new cashier, please remove the current one first.
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Assign Existing Cashier - Only show if no cashier assigned and unassigned cashiers exist -->
    @if($unassignedCashiers->count() > 0 && $assignedCashiers->count() === 0)
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Assign Existing Cashier to Your Branch</h2>
        <form method="POST" action="{{ route('manager.cashiers.assign') }}" class="flex items-end gap-4">
            @csrf
            <div class="flex-1">
                <label for="cashier_id" class="block text-sm font-medium text-gray-700 mb-1">Available Cashiers</label>
                <select id="cashier_id" name="cashier_id" class="w-full border rounded px-3 py-2" required>
                    <option value="">Select a cashier to assign</option>
                    @foreach($unassignedCashiers as $cashier)
                        <option value="{{ $cashier->id }}">{{ $cashier->name }} ({{ $cashier->email }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                Assign to Branch
            </button>
        </form>
    </div>
    @endif

    <!-- Currently Assigned Cashiers -->
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">
            Cashiers Assigned to {{ $managedBranch->display_label }}
        </h2>
        
        @if($assignedCashiers->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($assignedCashiers as $cashier)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $cashier->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $cashier->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $cashier->updated_at->format('M d, Y') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <form method="POST" action="{{ route('manager.cashiers.unassign') }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="cashier_id" value="{{ $cashier->id }}">
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900 text-sm"
                                                onclick="return confirm('Are you sure you want to remove {{ $cashier->name }} from this branch? This will permanently delete the cashier account.')">
                                            Remove Cashier
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
                    <i class="fas fa-users"></i>
                </div>
                <p class="text-gray-500">No cashiers are currently assigned to your branch.</p>
                @if($unassignedCashiers->count() > 0)
                    <p class="text-sm text-gray-400 mt-2">Use the form above to assign available cashiers.</p>
                @endif
            </div>
        @endif
    </div>

    @if($unassignedCashiers->count() === 0 && $assignedCashiers->count() === 0)
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">No Cashiers Available</h3>
                <p class="text-sm text-yellow-700 mt-1">
                    There are no cashier accounts available in the system. Please contact your administrator to create cashier accounts.
                </p>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection