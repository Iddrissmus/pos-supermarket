@extends('layouts.app')

@section('title', 'Business Dashboard')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    
    <!-- Welcome Banner -->
    <div class="relative bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl shadow-lg overflow-hidden mb-8">
        <div class="absolute inset-0 bg-white/10" style="background-image: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 20%), radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1) 0%, transparent 20%);"></div>
        <div class="relative p-8 sm:p-10">
            <div class="sm:flex sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white tracking-tight">Dashboard Overview</h1>
                    <p class="mt-2 text-blue-100 text-lg">
                        {{ $user->managedBusiness->name ?? 'My Business' }}
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 flex items-center space-x-3">
                    <span class="inline-flex items-center px-4 py-2 rounded-lg bg-white/10 backdrop-blur-sm border border-white/20 text-white font-medium">
                        <i class="fas fa-calendar-alt mr-2"></i> {{ now()->format('M d, Y') }}
                    </span>

                </div>
            </div>
        </div>
    </div>

    @if(isset($error))
        <div class="mb-8 bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ $error }}</p>
                </div>
            </div>
        </div>
    @else

        <!-- Key Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Revenue -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-indigo-50 p-3 rounded-lg">
                        <i class="fas fa-wallet text-indigo-600 text-xl"></i>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">GH₵ {{ number_format($totalRevenue, 2) }}</h3>
                </div>
            </div>

            <!-- Total Orders -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-blue-50 p-3 rounded-lg">
                        <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Orders</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalOrders) }}</h3>
                </div>
            </div>

            <!-- Total Products -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-green-50 p-3 rounded-lg">
                        <i class="fas fa-box text-green-600 text-xl"></i>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Products</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalProducts) }}</h3>
                    <p class="text-xs text-gray-400 mt-1">Across all branches</p>
                </div>
            </div>

            <!-- Low Stock Alert -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-red-50 p-3 rounded-lg">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    @if($lowStockCount > 0)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Attention Needed
                        </span>
                    @endif
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Low Stock Items</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($lowStockCount) }}</h3>
                    <p class="text-xs text-gray-400 mt-1">{{ number_format($outOfStockCount) }} out of stock</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            
            <!-- Recent Sales Feed -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">Recent Transactions</h2>
                    <a href="{{ route('sales.report') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Cashier</th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-right">Amount</th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-right">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($recentSales as $sale)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #{{ $sale->receipt_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $sale->branch->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $sale->cashier->name ?? 'Unknown' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600 text-right">
                                        GH₵ {{ number_format($sale->total, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 text-right">
                                        {{ $sale->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-receipt text-gray-300 text-4xl mb-3"></i>
                                            <p>No recent transactions found.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Products -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900">Top Selling Products</h2>
                </div>
                <div class="p-6">
                    @forelse($topProducts as $index => $product)
                        <div class="flex items-center justify-between mb-6 last:mb-0">
                            <div class="flex items-center min-w-0">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm mr-3 flex-shrink-0">
                                    {{ $index + 1 }}
                                </div>
                                <div class="truncate">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $product->product_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $product->barcode }}</p>
                                </div>
                            </div>
                            <div class="text-right ml-4 flex-shrink-0">
                                <p class="text-sm font-bold text-gray-900">GH₵ {{ number_format($product->total_revenue, 2) }}</p>
                                <p class="text-xs text-gray-500">{{ number_format($product->total_qty) }} sold</p>
                            </div>
                        </div>
                    @empty
                         <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-box-open text-gray-300 text-3xl mb-3"></i>
                            <p>No data available yet.</p>
                         </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Branch Performance -->
         <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-8">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Branch Performance</h2>
            </div>
             <div class="p-6">
                 <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                     @forelse($branchPerformance as $bp)
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="font-bold text-gray-900">{{ $bp->branch->name }}</h3>
                                    <p class="text-xs text-gray-500 mt-1">{{ $bp->branch->address }}</p>
                                </div>
                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-medium">Active</span>
                            </div>
                            <div class="mt-4 pt-4 border-t border-gray-200 grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500">Revenue</p>
                                    <p class="text-sm font-bold text-gray-900">GH₵ {{ number_format($bp->revenue, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Orders</p>
                                    <p class="text-sm font-bold text-gray-900">{{ number_format($bp->orders) }}</p>
                                </div>
                            </div>
                        </div>
                     @empty
                        <div class="col-span-full text-center py-8 text-gray-500">
                            No branch performance data available.
                        </div>
                     @endforelse
                 </div>
             </div>
         </div>

        <!-- Quick Actions Grid -->
        <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Management</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <a href="{{ route('admin.branch-assignments.index') }}" class="group bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:border-blue-500 hover:shadow-md transition-all">
                <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600 mb-4 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <i class="fas fa-network-wired text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-900">Manage Branches</h3>
                <p class="text-sm text-gray-500 mt-1">Add or configure branch locations</p>
            </a>

            <a href="{{ route('admin.staff.index') }}" class="group bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:border-purple-500 hover:shadow-md transition-all">
                <div class="w-12 h-12 bg-purple-50 rounded-lg flex items-center justify-center text-purple-600 mb-4 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-900">Staff Management</h3>
                <p class="text-sm text-gray-500 mt-1">Oversee managers and cashiers</p>
            </a>

            <a href="{{ route('layouts.product') }}" class="group bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:border-green-500 hover:shadow-md transition-all">
                <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center text-green-600 mb-4 group-hover:bg-green-600 group-hover:text-white transition-colors">
                    <i class="fas fa-boxes text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-900">Inventory</h3>
                <p class="text-sm text-gray-500 mt-1">Manage global product catalog</p>
            </a>

            <a href="{{ route('requests.approval.index') }}" class="group bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:border-orange-500 hover:shadow-md transition-all">
                <div class="w-12 h-12 bg-orange-50 rounded-lg flex items-center justify-center text-orange-600 mb-4 group-hover:bg-orange-600 group-hover:text-white transition-colors">
                    <i class="fas fa-clipboard-check text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-900">Requests</h3>
                <p class="text-sm text-gray-500 mt-1">Approve stock and item requests</p>
            </a>
        </div>

    @endif
</div>
@endsection
