@extends('layouts.app')

@section('title', 'SuperAdmin Dashboard')

@section('content')
<div class="p-6">
    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg shadow-lg p-8 mb-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">System Administrator Dashboard</h1>
                <p class="text-purple-100">Welcome, {{ Auth::user()->name }}! You have full system control.</p>
            </div>
            <div class="text-6xl opacity-50">
                <i class="fas fa-shield"></i>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Businesses</p>
                    <p class="text-3xl font-bold text-purple-600 mt-2">{{ \App\Models\Business::count() }}</p>
                </div>
                <div class="bg-purple-100 rounded-full p-4">
                    <i class="fas fa-building text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Business Admins</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ \App\Models\User::where('role', 'business_admin')->count() }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-4">
                    <i class="fas fa-user-tie text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Branches</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ \App\Models\Branch::count() }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-4">
                    <i class="fas fa-store text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Products</p>
                    <p class="text-3xl font-bold text-orange-600 mt-2">{{ \App\Models\Product::count() }}</p>
                </div>
                <div class="bg-orange-100 rounded-full p-4">
                    <i class="fas fa-box text-orange-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- User Breakdown by Role -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">
            <i class="fas fa-users text-indigo-600 mr-2"></i>Users by Role
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border-l-4 border-purple-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">SuperAdmins</p>
                        <p class="text-2xl font-bold text-purple-700 mt-1">{{ \App\Models\User::where('role', 'superadmin')->count() }}</p>
                    </div>
                    <div class="bg-purple-200 rounded-full p-3">
                        <i class="fas fa-shield-alt text-purple-700 text-xl"></i>
                    </div>
                </div>
                <a href="{{ route('system-users.index') }}" class="text-xs text-purple-700 hover:text-purple-900 mt-2 inline-block">
                    View all <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border-l-4 border-blue-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Business Admins</p>
                        <p class="text-2xl font-bold text-blue-700 mt-1">{{ \App\Models\User::where('role', 'business_admin')->count() }}</p>
                    </div>
                    <div class="bg-blue-200 rounded-full p-3">
                        <i class="fas fa-user-tie text-blue-700 text-xl"></i>
                    </div>
                </div>
                <a href="{{ route('system-users.index') }}" class="text-xs text-blue-700 hover:text-blue-900 mt-2 inline-block">
                    View all <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border-l-4 border-green-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Managers</p>
                        <p class="text-2xl font-bold text-green-700 mt-1">{{ \App\Models\User::where('role', 'manager')->count() }}</p>
                    </div>
                    <div class="bg-green-200 rounded-full p-3">
                        <i class="fas fa-user-cog text-green-700 text-xl"></i>
                    </div>
                </div>
                <a href="{{ route('system-users.index') }}" class="text-xs text-green-700 hover:text-green-900 mt-2 inline-block">
                    View all <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border-l-4 border-orange-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Cashiers</p>
                        <p class="text-2xl font-bold text-orange-700 mt-1">{{ \App\Models\User::where('role', 'cashier')->count() }}</p>
                    </div>
                    <div class="bg-orange-200 rounded-full p-3">
                        <i class="fas fa-cash-register text-orange-700 text-xl"></i>
                    </div>
                </div>
                <a href="{{ route('system-users.index') }}" class="text-xs text-orange-700 hover:text-orange-900 mt-2 inline-block">
                    View all <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600">Total System Users</p>
                <p class="text-xl font-bold text-gray-800">{{ \App\Models\User::count() }}</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">
            <i class="fas fa-bolt text-yellow-500 mr-2"></i>Quick Actions
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <a href="{{ route('businesses.create') }}" class="flex items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors border-2 border-purple-200">
                <div class="bg-purple-600 rounded-full p-3 mr-4">
                    <i class="fas fa-plus text-white"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">Create Business</p>
                    <p class="text-sm text-gray-600">Add a new business to the system</p>
                </div>
            </a>

            <a href="{{ route('businesses.index') }}" class="flex items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors border-2 border-blue-200">
                <div class="bg-blue-600 rounded-full p-3 mr-4">
                    <i class="fas fa-building text-white"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">Manage Businesses</p>
                    <p class="text-sm text-gray-600">View and edit all businesses</p>
                </div>
            </a>

            <a href="{{ route('system-users.index') }}" class="flex items-center p-4 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors border-2 border-indigo-200">
                <div class="bg-indigo-600 rounded-full p-3 mr-4">
                    <i class="fas fa-users-cog text-white"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">System Users</p>
                    <p class="text-sm text-gray-600">Manage all system users</p>
                </div>
            </a>

            {{-- <a href="#" class="flex items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors border-2 border-green-200">
                <div class="bg-green-600 rounded-full p-3 mr-4">
                    <i class="fas fa-chart-line text-white"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">System Reports</p>
                    <p class="text-sm text-gray-600">View system-wide analytics</p>
                </div>
            </a> --}}
        </div>
    </div>

    <!-- Recent Businesses -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">
            <i class="fas fa-building text-purple-600 mr-2"></i>Recent Businesses
        </h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Business Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branches</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse(\App\Models\Business::with('owner')->latest()->take(5)->get() as $business)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $business->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $business->branches->count() }} branches
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $business->owner->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $business->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('businesses.show', $business) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                <a href="{{ route('businesses.edit', $business) }}" class="text-green-600 hover:text-green-900">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3"></i>
                                <p>No businesses created yet</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
