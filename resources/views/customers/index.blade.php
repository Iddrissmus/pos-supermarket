@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-users mr-3 text-blue-600"></i>Customer Management
                    </h1>
                    <a href="{{ route('customers.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-plus mr-2"></i>Add Customer
                    </a>
                </div>
            </div>

            <div class="p-6">
                @if(session('success'))
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                        <p class="text-green-800">{{ session('success') }}</p>
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                        <ul class="text-red-800 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Filters -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <form method="GET" action="{{ route('customers.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Name, company, email, phone..."
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                <option value="">All Types</option>
                                <option value="individual" {{ request('type') === 'individual' ? 'selected' : '' }}>Individual</option>
                                <option value="business" {{ request('type') === 'business' ? 'selected' : '' }}>Business</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                                <i class="fas fa-search mr-1"></i>Filter
                            </button>
                            <a href="{{ route('customers.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm">
                                <i class="fas fa-times mr-1"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>

                @if($customers->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Customer
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Contact
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Sales
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Credit
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($customers as $customer)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-{{ $customer->customer_type === 'business' ? 'building' : 'user' }} text-blue-600"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $customer->display_name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    #{{ $customer->customer_number }}
                                                </div>
                                                @if($customer->company && $customer->customer_type === 'individual')
                                                    <div class="text-xs text-gray-400">{{ $customer->company }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            @if($customer->email)
                                                <div><i class="fas fa-envelope text-gray-400 mr-1"></i>{{ $customer->email }}</div>
                                            @endif
                                            @if($customer->phone)
                                                <div><i class="fas fa-phone text-gray-400 mr-1"></i>{{ $customer->phone }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ $customer->customer_type === 'business' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                            {{ ucfirst($customer->customer_type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div>{{ $customer->sales_count }} sales</div>
                                        <div class="text-xs text-gray-500">{{ $customer->invoices_count }} invoices</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($customer->credit_limit > 0)
                                            <div>₵{{ number_format($customer->credit_limit, 2) }}</div>
                                            @if($customer->outstanding_balance > 0)
                                                <div class="text-xs text-red-600">-₵{{ number_format($customer->outstanding_balance, 2) }}</div>
                                            @endif
                                        @else
                                            <span class="text-gray-400">No credit</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $customer->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('customers.show', $customer) }}" 
                                               class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('customers.edit', $customer) }}" 
                                               class="text-yellow-600 hover:text-yellow-900">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('customers.toggle-status', $customer) }}" 
                                                  method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="text-{{ $customer->is_active ? 'red' : 'green' }}-600 hover:text-{{ $customer->is_active ? 'red' : 'green' }}-900">
                                                    <i class="fas fa-{{ $customer->is_active ? 'ban' : 'check' }}"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $customers->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="fas fa-users text-gray-400 text-6xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No customers found</h3>
                        <p class="text-gray-500 mb-6">Start by adding your first customer.</p>
                        <a href="{{ route('customers.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg">
                            <i class="fas fa-plus mr-2"></i>Add Customer
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection