@extends('layouts.app')

@section('title', 'Sales History')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto space-y-8">
    
    <!-- Modern Header -->
    <div class="relative bg-gradient-to-r from-emerald-700 to-teal-700 rounded-xl shadow-lg overflow-hidden">
        <div class="absolute inset-0 bg-white/10" style="background-image: radial-gradient(circle at 10% 20%, rgba(255,255,255,0.1) 0%, transparent 20%), radial-gradient(circle at 90% 80%, rgba(255,255,255,0.1) 0%, transparent 20%);"></div>
        <div class="relative p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight flex items-center">
                    <i class="fas fa-receipt mr-3 text-emerald-200"></i> Sales History
                </h1>
                <p class="mt-2 text-emerald-100 text-lg opacity-90 max-w-2xl">
                    Track your daily transactions, revenue, and product performance.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                 @if(auth()->user()->role === 'cashier')
                    <a href="{{ route('sales.terminal') }}" class="px-4 py-2 bg-white text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors font-bold shadow-sm flex items-center">
                        <i class="fas fa-cash-register mr-2"></i> POS Terminal
                    </a>
                @endif
                @if(auth()->user()->role !== 'cashier')
                    <a href="{{ route('sales.report') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors font-medium backdrop-blur-sm border border-white/10 flex items-center">
                        <i class="fas fa-chart-line mr-2"></i> View Reports
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
            <i class="fas fa-check-circle text-green-500"></i>
            <p class="text-green-800 font-medium">{{ session('success') }}</p>
        </div>
    @endif

     <!-- Stats Grid -->
    @if($sales->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Revenue -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between group hover:border-emerald-200 transition-colors">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Revenue</p>
            <h3 class="text-3xl font-bold text-emerald-600 mt-2">₵{{ number_format($summary['total_revenue'] , 2) }}</h3>
            <div class="mt-2 text-xs text-emerald-600 bg-emerald-50 px-2 py-1 rounded w-fit font-medium">
                {{ $summary['items_sold'] }} Items Sold
            </div>
        </div>

         <!-- Profit -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between group hover:border-yellow-200 transition-colors">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Gross Profit</p>
            <h3 class="text-3xl font-bold text-yellow-600 mt-2">₵{{ number_format($summary['total_profit'] , 2) }}</h3>
            <p class="text-xs text-gray-400 mt-1">Based on COGS</p>
        </div>

        <!-- COGS -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between group hover:border-purple-200 transition-colors">
             <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Cost of Goods</p>
            <h3 class="text-3xl font-bold text-gray-900 mt-2">₵{{ number_format($summary['total_cogs'] , 2) }}</h3>
             <p class="text-xs text-gray-400 mt-1">Inventory Value</p>
        </div>

        <!-- Sales Count -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-blue-200 transition-colors">
             <div>
                 <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Transactions</p>
                 <h3 class="text-3xl font-bold text-blue-600 mt-2">{{ $summary['total_sales'] }}</h3>
             </div>
             <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-receipt text-xl"></i>
            </div>
        </div>
    </div>
    @endif

    <!-- Sales Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        
        @if($sales->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Sale Info</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Location / Staff</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Summary</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Method</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach($sales as $sale)
                    <tr class="hover:bg-gray-50/80 transition-colors group">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-emerald-50 rounded-lg flex items-center justify-center text-emerald-600 font-bold text-sm">
                                    #{{ $sale->id }}
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900">{{ $sale->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $sale->created_at->format('h:i A') }}</div>
                                </div>
                            </div>
                        </td>
                         <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 font-medium">{{ optional($sale->branch)->display_label ?? 'Unassigned' }}</div>
                            <div class="text-xs text-gray-500">By {{ $sale->cashier->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                             <div class="flex flex-col">
                                 <span>{{ $sale->items->count() }} line items</span>
                                 <span class="text-xs text-gray-400">{{ $sale->items->sum('quantity') }} units total</span>
                             </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                             <div class="text-sm font-bold text-gray-900">₵{{ number_format($sale->total, 2) }}</div>
                             @if($sale->items->sum('total_cost') > 0)
                                <div class="text-[10px] text-green-600 font-medium">
                                    +₵{{ number_format(($sale->subtotal ?? $sale->total) - $sale->items->sum('total_cost'), 2) }} Profit
                                </div>
                             @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @php
                                $method = str_replace('_', ' ', $sale->payment_method);
                                $color = 'gray';
                                if($method == 'cash') $color = 'green';
                                if($method == 'card') $color = 'blue';
                                if($method == 'mobile money') $color = 'yellow';
                            @endphp
                             <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide bg-{{$color}}-100 text-{{$color}}-800">
                                {{ ucfirst($method) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('sales.show', $sale) }}" class="text-gray-400 hover:text-emerald-600 transition-colors font-semibold flex items-center justify-end gap-1">
                                <span>Details</span> <i class="fas fa-chevron-right text-xs"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

         @if($sales->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $sales->links() }}
            </div>
        @endif

        @else
            <div class="text-center py-16">
                 <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                     <i class="fas fa-receipt text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">No sales records found</h3>
                @if(auth()->user()->role === 'cashier')
                    <p class="text-gray-500 mb-6">Process your first sale at the terminal.</p>
                     <a href="{{ route('sales.terminal') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-colors">
                        <i class="fas fa-cash-register mr-2"></i> Go to Terminal
                    </a>
                @else
                    <p class="text-gray-500">Sales will appear here once transactions are recorded.</p>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection