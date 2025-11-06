@extends('layouts.app')

@section('title', 'Business Admin Dashboard')

@section('content')
<div class="p-6">
    @php
        $user = Auth::user();
        $business = $user->managedBusiness;
        $branch = $user->branch; // Business admin's assigned branch
        $totalManagers = \App\Models\User::where('role', 'manager')->where('branch_id', $user->branch_id)->count();
        $totalCashiers = \App\Models\User::where('role', 'cashier')->where('branch_id', $user->branch_id)->count();
        // $branchProducts = \App\Models\BranchProduct::where('branch_id', $user->branch_id)->with('product')->get()->count();
    @endphp

    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-lg shadow-lg p-8 mb-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Business Administrator Dashboard</h1>
                <p class="text-blue-100">Business: <span class="font-semibold">{{ $business->name ?? 'No Business Assigned' }}</span></p>
                <p class="text-blue-100">Branch: <span class="font-semibold">{{ $branch->name ?? 'No Branch Assigned' }}</span></p>
                <p class="text-blue-100 text-sm mt-1">Welcome back, {{ Auth::user()->name }}!</p>
            </div>
            <div class="text-6xl opacity-50">
                <i class="fas fa-briefcase"></i>
            </div>
        </div>
    </div>

    @if(!$business || !$branch)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg mb-8">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Account Not Fully Configured</h3>
                    <p class="text-sm text-yellow-700 mt-1">
                        @if(!$business)
                            You are not currently assigned to any business.
                        @elseif(!$branch)
                            You are not currently assigned to any branch.
                        @endif
                        Please contact the System Administrator.
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
                        <p class="text-gray-500 text-sm font-medium">My Branch</p>
                        <p class="text-xl font-bold text-blue-600 mt-2">{{ $branch->name }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $branch->address }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-4">
                        <i class="fas fa-store text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Managers</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">{{ $totalManagers }}</p>
                        <p class="text-xs text-gray-500 mt-1">In my branch</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-4">
                        <i class="fas fa-user-tie text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Cashiers</p>
                        <p class="text-3xl font-bold text-purple-600 mt-2">{{ $totalCashiers }}</p>
                        <p class="text-xs text-gray-500 mt-1">In my branch</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-4">
                        <i class="fas fa-cash-register text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Products</p>
                        <p class="text-3xl font-bold text-orange-600 mt-2">{{ $branch->branchProducts->count() }}</p>

                        <p class="text-xs text-gray-500 mt-1">In business</p>
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
                <a href="{{ route('admin.branch-assignments.index') }}" class="flex items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors border-2 border-blue-200">
                    <div class="bg-blue-600 rounded-full p-3 mr-4">
                        <i class="fas fa-sitemap text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Manage Branches</p>
                        <p class="text-sm text-gray-600">Create and assign branches</p>
                    </div>
                </a>

                <a href="{{ route('admin.cashiers.index') }}" class="flex items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors border-2 border-purple-200">
                    <div class="bg-purple-600 rounded-full p-3 mr-4">
                        <i class="fas fa-users text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Manage Staff</p>
                        <p class="text-sm text-gray-600">Assign managers and cashiers</p>
                    </div>
                </a>

                <a href="{{ route('layouts.product') }}" class="flex items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors border-2 border-green-200">
                    <div class="bg-green-600 rounded-full p-3 mr-4">
                        <i class="fas fa-boxes text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Products & Inventory</p>
                        <p class="text-sm text-gray-600">Manage product catalog</p>
                    </div>
                </a>

                <a href="{{ route('suppliers.index') }}" class="flex items-center p-4 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors border-2 border-orange-200">
                    <div class="bg-orange-600 rounded-full p-3 mr-4">
                        <i class="fas fa-truck text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Suppliers</p>
                        <p class="text-sm text-gray-600">Manage supplier relationships</p>
                    </div>
                </a>

                <a href="{{ route('customers.index') }}" class="flex items-center p-4 bg-cyan-50 hover:bg-cyan-100 rounded-lg transition-colors border-2 border-cyan-200">
                    <div class="bg-cyan-600 rounded-full p-3 mr-4">
                        <i class="fas fa-user-friends text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Customers</p>
                        <p class="text-sm text-gray-600">Manage customer database</p>
                    </div>
                </a>

                <a href="{{ route('requests.approval.index') }}" class="flex items-center p-4 bg-red-50 hover:bg-red-100 rounded-lg transition-colors border-2 border-red-200">
                    <div class="bg-red-600 rounded-full p-3 mr-4">
                        <i class="fas fa-clipboard-check text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Approve Requests</p>
                        <p class="text-sm text-gray-600">Review stock transfer requests</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Branch Overview -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                <i class="fas fa-store text-blue-600 mr-2"></i>My Branch Details
            </h2>
            <div class="grid grid-cols-1 gap-4">
                <div class="border-2 border-blue-400 rounded-lg p-6 bg-blue-50">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">{{ $branch->name }}</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                <i class="fas fa-map-marker-alt mr-1"></i>{{ $branch->address ?? 'No address set' }}
                            </p>
                            @if($branch->contact)
                                <p class="text-sm text-gray-600 mt-1">
                                    <i class="fas fa-phone mr-1"></i>{{ $branch->contact }}
                                </p>
                            @endif
                        </div>
                        {{-- <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>Active
                        </span> --}}
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="bg-white rounded-lg p-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Managers:</span>
                                <span class="font-semibold text-lg text-blue-600">{{ \App\Models\User::where('branch_id', $branch->id)->where('role', 'manager')->count() }}</span>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg p-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Cashiers:</span>
                                <span class="font-semibold text-lg text-blue-600">{{ \App\Models\User::where('branch_id', $branch->id)->where('role', 'cashier')->count() }}</span>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg p-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Products:</span>
                                <span class="font-semibold text-lg text-blue-600">{{ $branch->branchProducts->count() }}</span>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg p-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Total Sales:</span>
                                <span class="font-semibold text-lg text-green-600">
                                    GH₵ {{ number_format(\App\Models\Sale::where('branch_id', $branch->id)->sum('total'), 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
