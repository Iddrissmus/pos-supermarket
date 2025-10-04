@extends('layouts.app')

@section('title', 'Supplier Details')

@section('content')
<div class="p-6">
    <!-- Notification Container -->
    <div id="notification-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Header -->
    <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">{{ $supplier->name }}</h1>
            <div class="flex space-x-3">
                <a href="{{ route('suppliers.edit', $supplier) }}" class="bg-blue-700 hover:bg-blue-800 px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('suppliers.index') }}" class="bg-blue-700 hover:bg-blue-800 px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Supplier Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Supplier Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Supplier Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $supplier->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Type</label>
                        <p class="mt-1">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                @if($supplier->type === 'warehouse') bg-yellow-100 text-yellow-800
                                @elseif($supplier->type === 'manufacturer') bg-purple-100 text-purple-800
                                @else bg-blue-100 text-blue-800 @endif">
                                {{ ucfirst($supplier->type) }}
                            </span>
                        </p>
                    </div>
                    @if($supplier->address)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500">Address</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $supplier->address }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Contact Info Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($supplier->contact_person)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Contact Person</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $supplier->contact_person }}</p>
                    </div>
                    @endif
                    @if($supplier->phone)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Phone</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <a href="tel:{{ $supplier->phone }}" class="text-blue-600 hover:text-blue-800">
                                {{ $supplier->phone }}
                            </a>
                        </p>
                    </div>
                    @endif
                    @if($supplier->email)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Email</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <a href="mailto:{{ $supplier->email }}" class="text-blue-600 hover:text-blue-800">
                                {{ $supplier->email }}
                            </a>
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            @if($supplier->notes)
            <!-- Notes Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Notes</h3>
                <p class="text-sm text-gray-700">{{ $supplier->notes }}</p>
            </div>
            @endif

            <!-- Recent Stock Receipts -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Stock Receipts</h3>
                        <a href="{{ route('stock-receipts.create', ['supplier_id' => $supplier->id]) }}" 
                           class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                            <i class="fas fa-plus mr-2"></i>New Receipt
                        </a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    @if($recentReceipts->count() > 0)
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Cost</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($recentReceipts as $receipt)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $receipt->receipt_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $receipt->received_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $receipt->items->count() }} item(s)
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($receipt->total_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('stock-receipts.show', $receipt) }}" 
                                           class="text-blue-600 hover:text-blue-900">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-12 text-center text-gray-500">
                            <i class="fas fa-receipt text-4xl mb-4 text-gray-300"></i>
                            <p class="text-lg mb-2">No stock receipts yet</p>
                            <p class="text-sm mb-4">This supplier hasn't delivered any products yet</p>
                            <a href="{{ route('stock-receipts.create', ['supplier_id' => $supplier->id]) }}" 
                               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <i class="fas fa-plus mr-2"></i>Create First Receipt
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Status</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            @if($supplier->is_active) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                            {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Created</span>
                        <span class="text-sm text-gray-900">{{ $supplier->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Last Updated</span>
                        <span class="text-sm text-gray-900">{{ $supplier->updated_at->format('M d, Y') }}</span>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-6 space-y-2">
                    <form action="{{ route('suppliers.toggle-status', $supplier) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="w-full px-4 py-2 text-sm font-medium rounded-lg border 
                                @if($supplier->is_active) 
                                    border-red-300 text-red-700 bg-red-50 hover:bg-red-100
                                @else 
                                    border-green-300 text-green-700 bg-green-50 hover:bg-green-100
                                @endif transition-colors">
                            @if($supplier->is_active)
                                <i class="fas fa-pause mr-2"></i>Deactivate
                            @else
                                <i class="fas fa-play mr-2"></i>Activate
                            @endif
                        </button>
                    </form>
                    
                    @if($supplier->stockReceipts()->count() === 0)
                        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this supplier? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full px-4 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-300 rounded-lg hover:bg-red-100 transition-colors">
                                <i class="fas fa-trash mr-2"></i>Delete Supplier
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Total Receipts</span>
                        <span class="text-sm font-medium text-gray-900">{{ $supplier->stockReceipts()->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Total Value</span>
                        <span class="text-sm font-medium text-gray-900">
                            ${{ number_format($supplier->stockReceipts()->sum('total_amount'), 2) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Last Delivery</span>
                        <span class="text-sm font-medium text-gray-900">
                            @if($supplier->stockReceipts()->count() > 0)
                                {{ $supplier->stockReceipts()->latest('received_at')->first()->received_at->diffForHumans() }}
                            @else
                                Never
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection