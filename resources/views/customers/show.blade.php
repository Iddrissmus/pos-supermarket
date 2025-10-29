@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <p class="text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-{{ $customer->customer_type === 'business' ? 'building' : 'user' }} text-blue-600 text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">{{ $customer->display_name }}</h1>
                            <p class="text-gray-600">Customer #{{ $customer->customer_number }}</p>
                            @if($customer->customer_type === 'individual' && $customer->company)
                                <p class="text-sm text-gray-500">{{ $customer->company }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('customers.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Customers
                        </a>
                        <a href="{{ route('customers.edit', $customer) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-edit mr-2"></i>Edit Customer
                        </a>
                        <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg" onclick="window.print()">
                            <i class="fas fa-print mr-2"></i>Print
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Customer Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Customer Type</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $customer->customer_type === 'business' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ucfirst($customer->customer_type) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Status</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $customer->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                        </div>
                        @if($customer->email)
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <a href="mailto:{{ $customer->email }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $customer->email }}
                                </a>
                            </p>
                        </div>
                        @endif
                        @if($customer->phone)
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Phone</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <a href="tel:{{ $customer->phone }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $customer->phone }}
                                </a>
                            </p>
                        </div>
                        @endif
                        @if($customer->full_address)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500">Address</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $customer->full_address }}</p>
                        </div>
                        @endif
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Payment Terms</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ str_replace('_', ' ', ucfirst($customer->payment_terms)) }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Member Since</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $customer->created_at->format('M d, Y') }}</p>
                        </div>
                        @if($customer->notes)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500">Notes</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $customer->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Sales -->
                <div class="bg-white rounded-lg shadow-md">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Purchases</h3>
                    </div>
                    @if($recentSales->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale #</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentSales as $sale)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            #{{ $sale->id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $sale->created_at->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ optional($sale->branch)->display_label ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $sale->items->count() }} items
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            ₵{{ number_format($sale->total, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('sales.show', $sale) }}" class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-eye mr-1"></i>View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-6 text-center">
                            <i class="fas fa-shopping-cart text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500">No purchases yet</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Sales Summary -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Sales Summary</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Total Sales</span>
                            <span class="text-sm font-medium text-gray-900">{{ $salesSummary['total_sales'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Total Amount</span>
                            <span class="text-sm font-medium text-gray-900">₵{{ number_format($salesSummary['total_amount'], 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Average Order</span>
                            <span class="text-sm font-medium text-gray-900">₵{{ number_format($salesSummary['average_order'], 2) }}</span>
                        </div>
                        @if($salesSummary['last_purchase'])
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Last Purchase</span>
                            <span class="text-sm font-medium text-gray-900">{{ $salesSummary['last_purchase']->diffForHumans() }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Credit Information -->
                @if($customer->credit_limit > 0)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Credit Information</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Credit Limit</span>
                            <span class="text-sm font-medium text-gray-900">₵{{ number_format($customer->credit_limit, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Outstanding</span>
                            <span class="text-sm font-medium {{ $customer->outstanding_balance > 0 ? 'text-red-600' : 'text-gray-900' }}">
                                ₵{{ number_format($customer->outstanding_balance, 2) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Available Credit</span>
                            <span class="text-sm font-medium text-green-600">₵{{ number_format($customer->available_credit, 2) }}</span>
                        </div>
                        <div class="mt-4">
                            <div class="bg-gray-200 rounded-full h-2">
                                <div class="bg-{{ $customer->outstanding_balance > ($customer->credit_limit * 0.8) ? 'red' : 'green' }}-600 h-2 rounded-full" 
                                     style="width: {{ $customer->credit_limit > 0 ? ($customer->outstanding_balance / $customer->credit_limit * 100) : 0 }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $customer->credit_limit > 0 ? number_format(($customer->outstanding_balance / $customer->credit_limit * 100), 1) : 0 }}% of credit used
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-2">
                        <a href="{{ route('customers.edit', $customer) }}" 
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                            <i class="fas fa-edit mr-2"></i>Edit Customer
                        </a>
                        <button onclick="createNewSale({{ $customer->id }})" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                            <i class="fas fa-shopping-cart mr-2"></i>New Sale
                        </button>
                        <form action="{{ route('customers.toggle-status', $customer) }}" method="POST" class="w-full">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-{{ $customer->is_active ? 'red' : 'green' }}-600 text-white rounded-lg hover:bg-{{ $customer->is_active ? 'red' : 'green' }}-700 transition-colors text-sm">
                                <i class="fas fa-{{ $customer->is_active ? 'ban' : 'check' }} mr-2"></i>
                                {{ $customer->is_active ? 'Deactivate' : 'Activate' }} Customer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function createNewSale(customerId) {
    // Redirect to sales creation with customer pre-selected
    window.location.href = "{{ route('sales.create') }}?customer_id=" + customerId;
}
</script>

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
}
</style>
@endsection