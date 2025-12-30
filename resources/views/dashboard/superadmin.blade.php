@extends('layouts.app')

@section('title', 'SuperAdmin Dashboard')

@section('content')
<div class="p-6">
    <!-- Welcome Header -->
    <div class="bg-purple-600 rounded-lg shadow-lg p-8 mb-8 text-white relative overflow-hidden">
        <div class="relative z-10 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">System Administrator Dashboard</h1>
                <p class="text-purple-100">Welcome, {{ Auth::user()->name }}! You have full system control.</p>
            </div>
            <div class="text-6xl opacity-20"> <!-- Reduced opacity for subtlety -->
                <i class="fas fa-shield-alt"></i>
            </div>
        </div>
        <!-- Decorative circle -->
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-white opacity-10 rounded-full"></div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium uppercase tracking-wider">Total Businesses</p>
                    <p class="text-3xl font-bold text-purple-600 mt-2">{{ \App\Models\Business::count() }}</p>
                </div>
                <div class="bg-purple-50 rounded-full p-4 text-purple-600">
                    <i class="fas fa-building text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium uppercase tracking-wider">Business Admins</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ \App\Models\User::where('role', 'business_admin')->count() }}</p>
                </div>
                <div class="bg-blue-50 rounded-full p-4 text-blue-600">
                    <i class="fas fa-user-tie text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium uppercase tracking-wider">Total Branches</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ \App\Models\Branch::count() }}</p>
                </div>
                <div class="bg-green-50 rounded-full p-4 text-green-600">
                    <i class="fas fa-store text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium uppercase tracking-wider">Total Products</p>
                    <p class="text-3xl font-bold text-orange-600 mt-2">{{ \App\Models\Product::count() }}</p>
                </div>
                <div class="bg-orange-50 rounded-full p-4 text-orange-600">
                    <i class="fas fa-box text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- User Breakdown by Role -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
            <span class="w-1 h-6 bg-indigo-600 rounded mr-3"></span>
            Users by Role
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- SuperAdmins -->
            <div class="bg-purple-50 rounded-xl p-5 border border-purple-100 transition-transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-2">
                    <div class="bg-purple-200 rounded-lg p-2 text-purple-700">
                        <i class="fas fa-shield-alt text-lg"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold text-purple-800">{{ \App\Models\User::where('role', 'superadmin')->count() }}</p>
                <p class="text-sm font-medium text-purple-600">SuperAdmins</p>
            </div>

            <!-- Business Admins -->
            <div class="bg-blue-50 rounded-xl p-5 border border-blue-100 transition-transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-2">
                    <div class="bg-blue-200 rounded-lg p-2 text-blue-700">
                        <i class="fas fa-user-tie text-lg"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold text-blue-800">{{ \App\Models\User::where('role', 'business_admin')->count() }}</p>
                <p class="text-sm font-medium text-blue-600">Business Admins</p>
            </div>

            <!-- Managers -->
            <div class="bg-green-50 rounded-xl p-5 border border-green-100 transition-transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-2">
                    <div class="bg-green-200 rounded-lg p-2 text-green-700">
                        <i class="fas fa-user-cog text-lg"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold text-green-800">{{ \App\Models\User::where('role', 'manager')->count() }}</p>
                <p class="text-sm font-medium text-green-600">Managers</p>
            </div>

            <!-- Cashiers -->
            <div class="bg-orange-50 rounded-xl p-5 border border-orange-100 transition-transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-2">
                    <div class="bg-orange-200 rounded-lg p-2 text-orange-700">
                        <i class="fas fa-cash-register text-lg"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold text-orange-800">{{ \App\Models\User::where('role', 'cashier')->count() }}</p>
                <p class="text-sm font-medium text-orange-600">Cashiers</p>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between text-sm">
            <span class="text-gray-500">Total System Users: <strong class="text-gray-900">{{\App\Models\User::count()}}</strong></span>
            <a href="{{ route('system-users.index') }}" class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center hover:underline">
                View all users <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
            <span class="w-1 h-6 bg-yellow-500 rounded mr-3"></span>
            Quick Actions
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <a href="{{ route('businesses.create') }}" class="flex flex-col items-center p-6 bg-gray-50 hover:bg-white hover:shadow-md rounded-xl transition-all border border-gray-100 group">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 mb-3 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                    <i class="fas fa-plus text-xl"></i>
                </div>
                <span class="font-semibold text-gray-800">Create Business</span>
                <span class="text-xs text-gray-500 mt-1">Add new entity</span>
            </a>

            <a href="{{ route('businesses.index') }}" class="flex flex-col items-center p-6 bg-gray-50 hover:bg-white hover:shadow-md rounded-xl transition-all border border-gray-100 group">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 mb-3 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <i class="fas fa-building text-xl"></i>
                </div>
                <span class="font-semibold text-gray-800">Manage Businesses</span>
                <span class="text-xs text-gray-500 mt-1">View all entities</span>
            </a>

            <a href="{{ route('system-users.index') }}" class="flex flex-col items-center p-6 bg-gray-50 hover:bg-white hover:shadow-md rounded-xl transition-all border border-gray-100 group">
                <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 mb-3 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                    <i class="fas fa-users-cog text-xl"></i>
                </div>
                <span class="font-semibold text-gray-800">System Users</span>
                <span class="text-xs text-gray-500 mt-1">Manage accounts</span>
            </a>

            <a href="{{ route('businesses.map') }}" class="flex flex-col items-center p-6 bg-gray-50 hover:bg-white hover:shadow-md rounded-xl transition-all border border-gray-100 group">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-600 mb-3 group-hover:bg-green-600 group-hover:text-white transition-colors">
                    <i class="fas fa-map-marked-alt text-xl"></i>
                </div>
                <span class="font-semibold text-gray-800">Map View</span>
                <span class="text-xs text-gray-500 mt-1">Locations map</span>
            </a>
        </div>
    </div>

    <!-- Recent Businesses -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
            <span class="w-1 h-6 bg-purple-600 rounded mr-3"></span>
            Recent Businesses
        </h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider rounded-l-lg">Business Name</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Branches</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Admin</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider rounded-r-lg">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse(\App\Models\Business::with('owner')->latest()->take(5)->get() as $business)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-semibold text-gray-900">{{ $business->name }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                                    {{ $business->branches->count() }} branches
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="flex items-center">
                                    <div class="h-6 w-6 rounded-full bg-gray-200 flex items-center justify-center text-xs mr-2">
                                        <i class="fas fa-user text-gray-500"></i>
                                    </div>
                                    {{ $business->owner->name ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $business->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('businesses.show', $business) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1 rounded-md hover:bg-indigo-100 transition-colors mr-2">View</a>
                                {{-- <a href="{{ route('businesses.edit', $business) }}" class="text-gray-600 hover:text-gray-900 hover:bg-gray-100 px-2 py-1 rounded transition-colors"><i class="fas fa-edit"></i></a> --}}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <div class="text-gray-300 mb-3">
                                        <i class="fas fa-inbox text-5xl"></i>
                                    </div>
                                    <p class="text-lg font-medium">No businesses created yet</p>
                                    <p class="text-sm text-gray-400">Get started by adding a new business above.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
