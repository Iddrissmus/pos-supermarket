@extends('layouts.app')

@section('title', 'Manager Dashboard')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    
    <!-- Dashboard Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-800">Dashboard</h1>
            <p class="text-slate-500 mt-1">
                Welcome back, {{ Auth::user()->name }} 👋
                @if(isset($branch))
                    <span class="mx-2">•</span> <span class="font-medium text-indigo-600">{{ $branch->name }}</span>
                @endif
            </p>
        </div>
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <a href="{{ route('manager.item-requests.index') }}" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 ease-in-out transform hover:-translate-y-0.5">
                <i class="fas fa-plus mr-2"></i> 
                <span>Request Stock</span>
            </a>
        </div>
    </div>

    @if(!isset($branch))
        <div class="mb-8 bg-amber-50 border border-amber-200 rounded-lg p-6 flex items-start">
            <i class="fas fa-exclamation-triangle text-amber-500 text-xl mt-1 mr-4"></i>
            <div>
                <h3 class="text-lg font-bold text-amber-800">No Branch Assigned</h3>
                <p class="text-amber-700 mt-1">You are not currently assigned to any branch. Please contact the Business Administrator to get set up.</p>
            </div>
        </div>
    @else

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <!-- Sales Card -->
        <div class="flex flex-col col-span-1 bg-white shadow-sm rounded-xl border border-slate-200">
            <div class="px-5 pt-5 pb-5">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Today's Sales</h2>
                    <div class="p-2 rounded-full bg-emerald-50">
                        <i class="fas fa-chart-line text-emerald-500"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-slate-800 mb-1">{{ $stats['today_sales'] ?? 0 }}</div>
                <div class="text-xs font-medium text-emerald-600 flex items-center">
                    <i class="fas fa-arrow-up mr-1"></i> <span>12%</span> <span class="text-slate-400 ml-2">vs yesterday</span>
                </div>
            </div>
        </div>

        <!-- Cashiers Card -->
        <div class="flex flex-col col-span-1 bg-white shadow-sm rounded-xl border border-slate-200">
            <div class="px-5 pt-5 pb-5">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Cashiers</h2>
                    <div class="p-2 rounded-full bg-blue-50">
                        <i class="fas fa-user-friends text-blue-500"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-slate-800 mb-1">
                    @php
                        $cashiersCount = \App\Models\User::where('role', 'cashier')->where('branch_id', $branch->id)->count();
                    @endphp
                    {{ $cashiersCount }}
                </div>
                <div class="text-xs font-medium text-blue-600 flex items-center">
                    <span>Manage Staff</span> <i class="fas fa-chevron-right ml-1 text-[10px]"></i>
                </div>
            </div>
        </div>

        <!-- Products Card -->
        <div class="flex flex-col col-span-1 bg-white shadow-sm rounded-xl border border-slate-200">
            <div class="px-5 pt-5 pb-5">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Products</h2>
                    <div class="p-2 rounded-full bg-indigo-50">
                        <i class="fas fa-box text-indigo-500"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-slate-800 mb-1">{{ $stats['total_products'] ?? 0 }}</div>
                <div class="text-xs font-medium text-slate-400">
                   In Stock
                </div>
            </div>
        </div>

        <!-- Pending Requests Card -->
        <div class="flex flex-col col-span-1 bg-white shadow-sm rounded-xl border border-slate-200">
            <div class="px-5 pt-5 pb-5">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pending Requests</h2>
                    <div class="p-2 rounded-full bg-amber-50">
                        <i class="fas fa-clock text-amber-500"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-slate-800 mb-1">
                    {{ \App\Models\StockTransfer::where('to_branch_id', $branch->id)->where('status', 'pending')->count() }}
                </div>
                <div class="text-xs font-medium text-amber-600">
                    Needs Attention
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-12 gap-6">
        
        <!-- Quick Actions & Info -->
        <div class="col-span-12 xl:col-span-8 space-y-6">
            
            <!-- Quick Actions -->
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="{{ route('manager.cashiers.index') }}" class="group flex flex-col items-center justify-center p-4 rounded-xl border border-slate-100 bg-slate-50 hover:bg-white hover:border-blue-200 hover:shadow-md transition-all cursor-pointer">
                        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <i class="fas fa-users-cog text-xl"></i>
                        </div>
                        <span class="text-sm font-semibold text-slate-700">Cashiers</span>
                    </a>

                    <a href="{{ route('manager.item-requests.index') }}" class="group flex flex-col items-center justify-center p-4 rounded-xl border border-slate-100 bg-slate-50 hover:bg-white hover:border-amber-200 hover:shadow-md transition-all cursor-pointer">
                        <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <i class="fas fa-truck-loading text-xl"></i>
                        </div>
                        <span class="text-sm font-semibold text-slate-700">Item Requests</span>
                    </a>

                    <a href="{{ route('suppliers.index') }}" class="group flex flex-col items-center justify-center p-4 rounded-xl border border-slate-100 bg-slate-50 hover:bg-white hover:border-emerald-200 hover:shadow-md transition-all cursor-pointer">
                        <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <i class="fas fa-people-carry text-xl"></i>
                        </div>
                        <span class="text-sm font-semibold text-slate-700">Suppliers</span>
                    </a>

                    <a href="{{ route('sales.report') }}" class="group flex flex-col items-center justify-center p-4 rounded-xl border border-slate-100 bg-slate-50 hover:bg-white hover:border-purple-200 hover:shadow-md transition-all cursor-pointer">
                        <div class="w-12 h-12 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <i class="fas fa-chart-pie text-xl"></i>
                        </div>
                        <span class="text-sm font-semibold text-slate-700">Reports</span>
                    </a>
                </div>
            </div>

            <!-- Recent Activity / Items -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800">Recent Sales</h3>
                    <a href="{{ route('sales.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Cashier</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($stats['recent_sales'] as $sale)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-sm font-medium text-indigo-600">
                                    <a href="#">#{{ $sale->order_id ?? $sale->id }}</a>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $sale->created_at->format('M d, H:i') }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $sale->cashier->name ?? 'Unknown' }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-slate-800 text-right">₵{{ number_format($sale->total, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500 text-sm">No recent sales found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-span-12 xl:col-span-4 space-y-6">
            
            <!-- Branch Details Card -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <i class="fas fa-store text-9xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-4 relative z-10">Branch Details</h3>
                
                <div class="space-y-4 relative z-10">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase">Branch Name</p>
                        <p class="text-base font-medium text-slate-800">{{ $branch->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase">Location</p>
                        <p class="text-base font-medium text-slate-800">{{ $branch->location ?? 'Headquarters' }}</p>
                    </div>
                    <div>
                         <p class="text-xs font-semibold text-slate-400 uppercase">Manager</p>
                        <div class="flex items-center mt-1">
                            <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600 mr-2">
                                {{ substr(Auth::user()->name, 0, 2) }}
                            </div>
                            <span class="text-sm font-medium text-slate-700">{{ Auth::user()->name }}</span>
                        </div>
                    </div>
                    <div class="pt-4 mt-2 border-t border-slate-100 flex items-center justify-between">
                         <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                            <span class="w-2 h-2 mr-1.5 bg-emerald-400 rounded-full"></span>
                            Operational
                        </span>
                        <a href="#" class="text-sm text-indigo-600 font-medium hover:underline">Edit Info</a>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
             <div class="bg-slate-900 rounded-xl shadow-lg p-6 text-white">
                <h3 class="text-lg font-bold mb-4">Support & Help</h3>
                <ul class="space-y-3">
                    <li>
                        <a href="#" class="flex items-center text-slate-300 hover:text-white transition-colors">
                            <i class="fas fa-book-reader w-6"></i>
                            <span class="text-sm">Manager's Handbook</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center text-slate-300 hover:text-white transition-colors">
                            <i class="fas fa-headset w-6"></i>
                            <span class="text-sm">Contact Super Admin</span>
                        </a>
                    </li>
                     <li>
                        <a href="#" class="flex items-center text-slate-300 hover:text-white transition-colors">
                            <i class="fas fa-video w-6"></i>
                            <span class="text-sm">Video Tutorials</span>
                        </a>
                    </li>
                </ul>
            </div>

        </div>
    </div>
    @endif
</div>
@endsection