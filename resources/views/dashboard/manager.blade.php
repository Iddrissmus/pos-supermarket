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
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                <i class="fas fa-bolt text-yellow-500 mr-2"></i>Quick Actions
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('manager.cashiers.index') }}" class="flex items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors border-2 border-green-200">
                    <div class="bg-green-600 rounded-full p-3 mr-4">
                        <i class="fas fa-user-friends text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Manage Cashiers</p>
                        <p class="text-sm text-gray-600">Assign and manage staff</p>
                    </div>
                </a>

                <a href="{{ route('manager.item-requests.index') }}" class="flex items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors border-2 border-blue-200">
                    <div class="bg-blue-600 rounded-full p-3 mr-4">
                        <i class="fas fa-box-open text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Request Items</p>
                        <p class="text-sm text-gray-600">Request stock from admin</p>
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

                <a href="{{ route('reorder.requests') }}" class="flex items-center p-4 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors border-2 border-orange-200">
                    <div class="bg-orange-600 rounded-full p-3 mr-4">
                        <i class="fas fa-redo text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Reorder Requests</p>
                        <p class="text-sm text-gray-600">View stock requests</p>
                    </div>
                </a>

                <a href="{{ route('notifications.index') }}" class="flex items-center p-4 bg-cyan-50 hover:bg-cyan-100 rounded-lg transition-colors border-2 border-cyan-200">
                    <div class="bg-cyan-600 rounded-full p-3 mr-4">
                        <i class="fas fa-bell text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Notifications</p>
                        <p class="text-sm text-gray-600">View alerts</p>
                    </div>
                </a>

                <a href="{{ route('manager.daily-sales') }}" class="flex items-center p-4 bg-red-50 hover:bg-red-100 rounded-lg transition-colors border-2 border-red-200">
                    <div class="bg-red-600 rounded-full p-3 mr-4">
                        <i class="fas fa-cash-register text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Daily Sales</p>
                        <p class="text-sm text-gray-600">Monitor sales activity</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Branch Info -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                <i class="fas fa-info-circle text-green-600 mr-2"></i>Branch Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="border-2 border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-3">Branch Details</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Name:</span>
                            <span class="font-medium">{{ $branch->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Location:</span>
                            <span class="font-medium">{{ $branch->location ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                    </div>
                </div>

                <div class="border-2 border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-3">Staff Summary</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Cashiers:</span>
                            <span class="font-medium">{{ $cashiers }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Manager:</span>
                            <span class="font-medium">{{ Auth::user()->name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection 