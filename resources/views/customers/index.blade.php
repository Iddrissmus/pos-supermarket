@extends('layouts.app')

@section('title', 'Customer Management')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto space-y-8">

    <!-- Modern Header -->
    <div class="relative bg-gradient-to-r from-blue-700 to-indigo-800 rounded-xl shadow-lg overflow-hidden">
        <div class="absolute inset-0 bg-white/10" style="background-image: radial-gradient(circle at 10% 20%, rgba(255,255,255,0.1) 0%, transparent 20%), radial-gradient(circle at 90% 80%, rgba(255,255,255,0.1) 0%, transparent 20%);"></div>
        <div class="relative p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight flex items-center">
                    <i class="fas fa-users mr-3 text-blue-200"></i> Customer Management
                </h1>
                <p class="mt-2 text-blue-100 text-lg opacity-90 max-w-2xl">
                    Manage customer profiles, credit limits, and view purchase history.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                 <a href="{{ route('customers.create') }}" class="px-4 py-2 bg-white text-blue-700 hover:bg-blue-50 rounded-lg transition-colors font-bold shadow-sm flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add Customer
                </a>
            </div>
        </div>
    </div>

    <!-- Notification Container -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
            <i class="fas fa-check-circle text-green-500"></i>
            <p class="text-green-800 font-medium">{{ session('success') }}</p>
        </div>
    @endif
    
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3">
            <i class="fas fa-times-circle text-red-500"></i>
            <p class="text-red-800 font-medium">{{ session('error') }}</p>
        </div>
    @endif

     <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-blue-200 transition-colors">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Customers</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $customers->total() }}</h3>
                </div>
            </div>
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-users text-xl"></i>
            </div>
        </div>

        <!-- Active Business -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-indigo-200 transition-colors">
             <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Business Accounts</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $customers->where('customer_type', 'business')->count() }}</h3>
                </div>
            </div>
            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-building text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        
        <!-- Filters -->
        <div class="p-5 border-b border-gray-100 bg-gray-50/50">
            <form method="GET" action="{{ route('customers.index') }}" class="flex flex-col md:flex-row gap-4">
                <div class="relative flex-grow">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by name, company, or phone..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="w-full md:w-48">
                    <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500">
                        <option value="">All Types</option>
                        <option value="individual" {{ request('type') === 'individual' ? 'selected' : '' }}>Individual</option>
                        <option value="business" {{ request('type') === 'business' ? 'selected' : '' }}>Business</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a href="{{ route('customers.index') }}" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        @if($customers->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Customer Info</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Activity</th>

                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach($customers as $customer)
                    <tr class="hover:bg-gray-50/80 transition-colors group">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-lg font-bold">
                                    {{ substr($customer->display_name, 0, 1) }}
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900 group-hover:text-blue-600 transition-colors">
                                        {{ $customer->display_name }}
                                    </div>
                                    <div class="text-xs text-gray-500 font-mono">
                                        #{{ $customer->customer_number }}
                                    </div>
                                    @if($customer->company && $customer->customer_type === 'individual')
                                        <div class="text-[10px] text-gray-400 mt-0.5">{{ $customer->company }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                @if($customer->phone)
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-phone text-gray-300 text-xs"></i> {{ $customer->phone }}
                                    </div>
                                @endif
                                @if($customer->email)
                                    <div class="flex items-center gap-2 mt-1">
                                        <i class="fas fa-envelope text-gray-300 text-xs"></i> {{ $customer->email }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                             <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide
                                {{ $customer->customer_type === 'business' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $customer->customer_type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                             <div class="text-sm text-gray-900 font-medium">{{ $customer->sales_count }} sales</div>
                             <div class="text-xs text-gray-500">{{ $customer->invoices_count }} invoices</div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($customer->is_active)
                                <span class="inline-flex h-2.5 w-2.5 rounded-full bg-green-500"></span>
                            @else
                                <span class="inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                           <div class="flex justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('customers.show', $customer) }}" class="text-gray-400 hover:text-blue-600 transition-colors" title="View Profile">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('customers.edit', $customer) }}" class="text-gray-400 hover:text-amber-600 transition-colors" title="Edit Customer">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('customers.toggle-status', $customer) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-gray-400 hover:text-indigo-600 transition-colors" title="Toggle Status">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                </form>
                           </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($customers->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $customers->links() }}
            </div>
        @endif

        @else
            <div class="text-center py-16">
                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                     <i class="fas fa-user-plus text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">No customers found</h3>
                <p class="text-gray-500 mb-6">Start building your client base today.</p>
                <a href="{{ route('customers.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i> Add First Customer
                </a>
            </div>
        @endif
    </div>
</div>
@endsection