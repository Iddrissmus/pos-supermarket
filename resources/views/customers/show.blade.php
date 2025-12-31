@extends('layouts.app')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

    <!-- Breadcrumb & Header -->
    <div class="mb-8">
        <div class="mb-4">
            <a href="{{ route('customers.index') }}" class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-600 hover:text-indigo-600">
                <i class="fas fa-arrow-left mr-2"></i> Back to Customers
            </a>
        </div>
        <div class="flex items-center text-sm text-slate-500 mb-2">
            <span class="text-slate-400">Customers</span>
            <span class="mx-2">/</span>
            <span class="text-slate-800">{{ $customer->display_name }}</span>
        </div>
        <div class="sm:flex sm:justify-between sm:items-center">
            <div>
                 <h1 class="text-2xl md:text-3xl font-bold text-slate-800">{{ $customer->display_name }}</h1>
                 <div class="flex items-center mt-1 space-x-2">
                     <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $customer->customer_type == 'business' ? 'bg-indigo-100 text-indigo-800' : 'bg-green-100 text-green-800' }}">
                         {{ ucfirst($customer->customer_type) }}
                     </span>
                     <span class="text-slate-400">|</span>
                     <span class="text-sm text-slate-500">Customer #{{ $customer->customer_number }}</span>
                 </div>
            </div>
            
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2 mt-4 sm:mt-0">
                 <a href="{{ route('customers.edit', $customer) }}" class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-600">
                    <i class="fas fa-edit mr-2 text-slate-400"></i> Edit Profile
                 </a>
                 @if(auth()->user()->role === 'cashier')
                    <a href="{{ route('sales.terminal', ['customer_id' => $customer->id]) }}" class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                        <i class="fas fa-cash-register mr-2"></i> New Sale
                    </a>
                 @endif
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <!-- Total Spent -->
        <div class="bg-white border border-slate-200 rounded-sm p-4 shadow-sm">
            <div class="text-sm font-semibold text-slate-500 uppercase mb-1">Total Spent</div>
            <div class="flex items-center justify-between">
                <div class="text-2xl font-bold text-slate-800">₵{{ number_format($salesSummary['total_amount'], 2) }}</div>
                <div class="p-2 bg-indigo-50 rounded-full">
                    <i class="fas fa-wallet text-indigo-500"></i>
                </div>
            </div>
        </div>
        
        <!-- Total Orders -->
        <div class="bg-white border border-slate-200 rounded-sm p-4 shadow-sm">
             <div class="text-sm font-semibold text-slate-500 uppercase mb-1">Total Orders</div>
             <div class="flex items-center justify-between">
                <div class="text-2xl font-bold text-slate-800">{{ $salesSummary['total_sales'] }}</div>
                <div class="p-2 bg-emerald-50 rounded-full">
                    <i class="fas fa-shopping-bag text-emerald-500"></i>
                </div>
            </div>
        </div>

        <!-- Average Order -->
         <div class="bg-white border border-slate-200 rounded-sm p-4 shadow-sm">
             <div class="text-sm font-semibold text-slate-500 uppercase mb-1">Avg. Order</div>
             <div class="flex items-center justify-between">
                <div class="text-2xl font-bold text-slate-800">₵{{ number_format($salesSummary['average_order'], 2) }}</div>
                <div class="p-2 bg-amber-50 rounded-full">
                    <i class="fas fa-chart-bar text-amber-500"></i>
                </div>
            </div>
        </div>
        
        <!-- Last Visit -->
         <div class="bg-white border border-slate-200 rounded-sm p-4 shadow-sm">
             <div class="text-sm font-semibold text-slate-500 uppercase mb-1">Last Visit</div>
             <div class="flex items-center justify-between">
                <div class="text-xl font-bold text-slate-800">
                    {{ $salesSummary['last_purchase'] ? $salesSummary['last_purchase']->diffForHumans() : 'Never' }}
                </div>
                <div class="p-2 bg-slate-50 rounded-full">
                    <i class="fas fa-history text-slate-500"></i>
                </div>
            </div>
        </div>
    </div>


    <div class="grid grid-cols-12 gap-6">
        
        <!-- Sidebar Info -->
        <div class="col-span-12 xl:col-span-4">
            <div class="bg-white border border-slate-200 rounded-sm shadow-sm">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="font-semibold text-slate-800">Contact & Info</h2>
                </div>
                <div class="p-5">
                    <div class="space-y-4">
                        <div>
                            <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Email</div>
                            <div class="text-slate-800">{{ $customer->email ?: 'Not provided' }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Phone</div>
                            <div class="text-slate-800">{{ $customer->phone ?: 'Not provided' }}</div>
                        </div>
                        <div>
                             <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Address</div>
                             <div class="text-slate-800">{{ $customer->full_address ?: 'Not provided' }}</div>
                        </div>
                        <div>
                             <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Payment Terms</div>
                             <div class="text-slate-800">{{ str_replace('_', ' ', ucfirst($customer->payment_terms)) }}</div>
                        </div>
                         <div>
                             <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Status</div>
                             <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $customer->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                 {{ $customer->is_active ? 'Active' : 'Inactive' }}
                             </span>
                        </div>
                    </div>
                    
                    @if($customer->notes)
                    <div class="mt-6 pt-4 border-t border-slate-100">
                        <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Notes</div>
                        <div class="text-sm text-slate-600 italic">
                            {{ $customer->notes }}
                        </div>
                    </div>
                    @endif

                    <div class="mt-6 pt-4 border-t border-slate-100">
                         <form action="{{ route('customers.toggle-status', $customer) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-sm font-medium {{ $customer->is_active ? 'text-rose-500 hover:text-rose-600' : 'text-emerald-500 hover:text-emerald-600' }} transition-colors">
                                {{ $customer->is_active ? 'Deactivate Customer' : 'Activate Customer' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="col-span-12 xl:col-span-8">
            <div class="bg-white border border-slate-200 rounded-sm shadow-sm">
                <header class="px-5 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="font-semibold text-slate-800">Transaction History</h2>
                    <span class="text-xs text-slate-500">Last {{ $recentSales->count() }} transactions</span>
                </header>
                <div class="overflow-x-auto">
                    <table class="table-auto w-full">
                        <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-3 text-left">Order #</th>
                                <th class="px-5 py-3 text-left">Date</th>
                                <th class="px-5 py-3 text-left">Branch</th>
                                <th class="px-5 py-3 text-right">Items</th>
                                <th class="px-5 py-3 text-right">Total</th>
                                <th class="px-5 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            @forelse($recentSales as $sale)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-5 py-3 font-medium text-slate-800">
                                    #{{ $sale->id }}
                                </td>
                                <td class="px-5 py-3 text-slate-600">
                                    {{ $sale->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-5 py-3 text-slate-600">
                                    {{ optional($sale->branch)->name ?? '-' }}
                                </td>
                                <td class="px-5 py-3 text-right text-slate-600">
                                    {{ $sale->items->count() }}
                                </td>
                                <td class="px-5 py-3 text-right font-bold text-slate-800">
                                    ₵{{ number_format($sale->total, 2) }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <a href="{{ route('sales.show', $sale) }}" class="text-indigo-500 hover:text-indigo-600 font-medium text-sm">View</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-slate-500">
                                    No transactions found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection