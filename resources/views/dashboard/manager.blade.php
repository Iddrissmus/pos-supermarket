@extends('layouts.app')

@section('title', 'Manager Dashboard')

@section('content')
<div class="p-6">
    @php
        $branch = Auth::user()->branch;
        $cashiers = $branch ? \App\Models\User::where('role', 'cashier')->where('branch_id', $branch->id)->count() : 0;
    @endphp

    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-lg shadow-lg p-8 mb-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Branch Manager Dashboard</h1>
                <p class="text-green-100">Branch: <span class="font-semibold">{{ $branch->name ?? 'No Branch Assigned' }}</span></p>
                <p class="text-green-100 text-sm mt-1">Welcome back, {{ Auth::user()->name }}!</p>
            </div>
            <div class="text-6xl opacity-50">
                <i class="fas fa-users-cog"></i>
            </div>
        </div>
    </div>

    @if(!$branch)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg mb-8">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">No Branch Assigned</h3>
                    <p class="text-sm text-yellow-700 mt-1">
                        You are not currently assigned to any branch. Please contact the Business Administrator.
                    </p>
                </div>
            </div>
        </div>
    @else
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Branch</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">{{ $branch->name }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-4">
                        <i class="fas fa-store text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Cashiers</p>
                        <p class="text-3xl font-bold text-blue-600 mt-2">{{ $cashiers }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-4">
                        <i class="fas fa-user-friends text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Today's Sales</p>
                        <p class="text-3xl font-bold text-purple-600 mt-2">{{ $stats['today_sales'] ?? 0 }}</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-4">
                        <i class="fas fa-chart-line text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Products</p>
                        <p class="text-3xl font-bold text-orange-600 mt-2">{{ $stats['total_products'] ?? 0 }}</p>
                    </div>
                    <div class="bg-orange-100 rounded-full p-4">
                        <i class="fas fa-box text-orange-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">
                <i class="fas fa-bolt text-yellow-500 mr-2"></i>Quick Actions
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('manager.cashiers.index') }}" class="flex items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors border-2 border-green-200">
                    <div class="bg-green-600 rounded-full p-3 mr-4">
                        <i class="fas fa-user-friends text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Manage Cashiers</p>
                        <p class="text-sm text-gray-600">Assign and manage staff</p>
                    </div>
                </a>

                <a href="{{ route('layouts.product') }}" class="flex items-center p-4 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors border-2 border-indigo-200">
                    <div class="bg-indigo-600 rounded-full p-3 mr-4">
                        <i class="fas fa-boxes text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Products</p>
                        <p class="text-sm text-gray-600">View branch inventory</p>
                    </div>
                </a>

                <a href="{{ route('sales.report') }}" class="flex items-center p-4 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors border-2 border-emerald-200">
                    <div class="bg-emerald-600 rounded-full p-3 mr-4">
                        <i class="fas fa-chart-line text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Sales Reports</p>
                        <p class="text-sm text-gray-600">Analytics & insights</p>
                    </div>
                </a>

                <a href="{{ route('product-reports.index') }}" class="flex items-center p-4 bg-violet-50 hover:bg-violet-100 rounded-lg transition-colors border-2 border-violet-200">
                    <div class="bg-violet-600 rounded-full p-3 mr-4">
                        <i class="fas fa-chart-pie text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Product Analytics</p>
                        <p class="text-sm text-gray-600">Product performance data</p>
                    </div>
                </a>

                <a href="{{ route('manager.item-requests.index') }}" class="flex items-center p-4 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors border-2 border-orange-200">
                    <div class="bg-orange-600 rounded-full p-3 mr-4">
                        <i class="fas fa-box text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Item Requests</p>
                        <p class="text-sm text-gray-600">Request stock items</p>
                    </div>
                </a>

                <a href="{{ route('stock-receipts.index') }}" class="flex items-center p-4 bg-teal-50 hover:bg-teal-100 rounded-lg transition-colors border-2 border-teal-200">
                    <div class="bg-sky-600 rounded-full p-3 mr-4">
                        <i class="fas fa-truck-loading text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Receive Stock</p>
                        <p class="text-sm text-gray-600">Add inventory from suppliers</p>
                    </div>
                </a>

                <a href="{{ route('suppliers.index') }}" class="flex items-center p-4 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors border-2 border-amber-200">
                    <div class="bg-amber-600 rounded-full p-3 mr-4">
                        <i class="fas fa-people-carry text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Local Suppliers</p>
                        <p class="text-sm text-gray-600">Manage local vendors</p>
                    </div>
                </a>

                <a href="{{ route('customers.index') }}" class="flex items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors border-2 border-purple-200">
                    <div class="bg-purple-600 rounded-full p-3 mr-4">
                        <i class="fas fa-address-book text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Customers</p>
                        <p class="text-sm text-gray-600">View customer list</p>
                    </div>
                </a>

                <a href="{{ route('sales.index') }}" class="flex items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors border-2 border-blue-200">
                    <div class="bg-blue-600 rounded-full p-3 mr-4">
                        <i class="fas fa-receipt text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Branch Sales</p>
                        <p class="text-sm text-gray-600">View all transactions</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Branch Info -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">
                <i class="fas fa-info-circle text-green-600 mr-2"></i>Branch Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <div class="bg-green-600 rounded-full p-3 mr-3">
                            <i class="fas fa-store text-white text-xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800 text-lg">Branch Details</h3>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center py-2 border-b border-green-200">
                            <span class="text-gray-600 font-medium">Branch Name:</span>
                            <span class="font-semibold text-gray-800">{{ $branch->name }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-green-200">
                            <span class="text-gray-600 font-medium">Location:</span>
                            <span class="font-semibold text-gray-800">{{ $branch->location ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600 font-medium">Status:</span>
                            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-green-600 text-white">
                                <i class="fas fa-check-circle mr-1"></i>
                                Active
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <div class="bg-blue-600 rounded-full p-3 mr-3">
                            <i class="fas fa-users text-white text-xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800 text-lg">Staff Summary</h3>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center py-2 border-b border-blue-200">
                            <span class="text-gray-600 font-medium">Total Cashiers:</span>
                            <span class="font-semibold text-blue-600 text-lg">{{ $cashiers }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600 font-medium">Branch Manager:</span>
                            <span class="font-semibold text-gray-800">{{ Auth::user()->name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection