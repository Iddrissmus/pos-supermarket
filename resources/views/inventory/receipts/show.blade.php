@extends('layouts.app')

@section('title', 'Stock Receipt Details')

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
    <div class="bg-green-600 text-white px-6 py-4 rounded-t-lg mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">Stock Receipt #{{ $stockReceipt->receipt_number }}</h1>
            <div class="flex space-x-3">
                <button onclick="window.print()" class="bg-green-700 hover:bg-green-800 px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-print mr-2"></i>Print Receipt
                </button>
                <a href="{{ route('stock-receipts.index') }}" class="bg-green-700 hover:bg-green-800 px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Receipts
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Receipt Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Receipt Information -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Receipt Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Receipt Number</label>
                        <p class="mt-1 text-sm font-medium text-gray-900">{{ $stockReceipt->receipt_number }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Received Date</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $stockReceipt->received_at->format('M d, Y g:i A') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Supplier</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <a href="{{ route('suppliers.show', $stockReceipt->supplier) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $stockReceipt->supplier->name }}
                            </a>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Branch</label>
                        <p class="mt-1 text-sm text-gray-900">{{ optional($stockReceipt->branch)->display_label ?? 'Unassigned branch' }}</p>
                    </div>
                    @if($stockReceipt->createdBy)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Received By</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $stockReceipt->createdBy->name }}</p>
                    </div>
                    @endif
                    @if($stockReceipt->notes)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500">Notes</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $stockReceipt->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Items Received -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Items Received</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Line Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($stockReceipt->items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                    @if($item->product->description)
                                        <div class="text-sm text-gray-500">{{ Str::limit($item->product->description, 50) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($item->quantity) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₵{{ number_format($item->unit_cost, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    ₵{{ number_format($item->line_total, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                    Total Amount:
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    ₵{{ number_format($stockReceipt->total_amount, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Summary Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Summary</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Items Count</span>
                        <span class="text-sm font-medium text-gray-900">{{ $stockReceipt->items->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Total Quantity</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($stockReceipt->items->sum('quantity')) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Total Value</span>
                        <span class="text-sm font-medium text-gray-900">₵{{ number_format($stockReceipt->total_amount, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Average Cost</span>
                        <span class="text-sm font-medium text-gray-900">
                            ₵{{ number_format($stockReceipt->items->avg('unit_cost'), 2) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Receipt Status -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Status</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            Completed
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Created</span>
                        <span class="text-sm text-gray-900">{{ $stockReceipt->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Time Ago</span>
                        <span class="text-sm text-gray-900">{{ $stockReceipt->received_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('stock-receipts.create', ['supplier_id' => $stockReceipt->supplier_id]) }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                        <i class="fas fa-plus mr-2"></i>New Receipt from Supplier
                    </a>
                    <a href="{{ route('suppliers.show', $stockReceipt->supplier) }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                        <i class="fas fa-building mr-2"></i>View Supplier
                    </a>
                    <button onclick="window.print()" 
                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm">
                        <i class="fas fa-print mr-2"></i>Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    body {
        background: white !important;
    }
    
    .shadow-md {
        box-shadow: none !important;
    }
    
    .bg-green-600 {
        background-color: #16a34a !important;
        -webkit-print-color-adjust: exact;
    }
}
</style>
@endsection