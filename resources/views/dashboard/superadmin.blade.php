@extends('layouts.app')

@section('title', 'Overview')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Modern Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-10">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Dashboard Overview</h1>
            <p class="text-sm text-gray-500 mt-1">Welcome back, {{ Auth::user()->name }}. Here's what's happening today.</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-100">
                <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>
                System Operational
            </span>
            <span class="text-sm text-gray-500 bg-white border border-gray-200 px-3 py-1.5 rounded-md shadow-sm">
                {{ now()->format('M d, Y') }}
            </span>
        </div>
    </div>

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Businesses Card -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Businesses</p>
                    <h3 class="text-3xl font-bold text-gray-900 mt-2 tracking-tight">{{ \App\Models\Business::count() }}</h3>
                </div>
                <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-green-600">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                <span class="font-medium">Active</span>
            </div>
        </div>

        <!-- Branches Card -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Branches</p>
                    <h3 class="text-3xl font-bold text-gray-900 mt-2 tracking-tight">{{ \App\Models\Branch::count() }}</h3>
                </div>
                <div class="p-2 bg-pink-50 rounded-lg text-pink-600 group-hover:bg-pink-600 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-gray-500">
                <span class="font-medium">Across all regions</span>
            </div>
        </div>

        <!-- Products Card -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500">System Products</p>
                    <h3 class="text-3xl font-bold text-gray-900 mt-2 tracking-tight">{{ \App\Models\Product::count() }}</h3>
                </div>
                <div class="p-2 bg-amber-50 rounded-lg text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-amber-600">
                <span class="font-medium">Global Catalogue</span>
            </div>
        </div>

        <!-- System Users Card -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Users</p>
                    <h3 class="text-3xl font-bold text-gray-900 mt-2 tracking-tight">{{ \App\Models\User::count() }}</h3>
                </div>
                <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-gray-500">
                <span class="font-medium text-emerald-600">{{ \App\Models\User::where('role', 'superadmin')->count() }}</span>
                <span class="mx-1">admins</span>
            </div>
        </div>
    </div>

    <!-- Main Content Area: Sidebar + Table -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
        
        <!-- Quick Management Sidebar -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 lg:col-span-1 h-fit">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Quick Management</h2>
            <div class="space-y-3">
                <a href="{{ route('businesses.create') }}" class="flex items-center p-3 rounded-lg border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50 transition-all group">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    </div>
                    <div>
                        <span class="block text-sm font-semibold text-gray-900">Add New Business</span>
                        <span class="block text-xs text-gray-500">Onboard a new client organization</span>
                    </div>
                </a>
                
                <a href="{{ route('system-users.index') }}" class="flex items-center p-3 rounded-lg border border-gray-100 hover:border-emerald-200 hover:bg-emerald-50 transition-all group">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    </div>
                    <div>
                        <span class="block text-sm font-semibold text-gray-900">Manage Users</span>
                        <span class="block text-xs text-gray-500">Add or edit system administrators</span>
                    </div>
                </a>

                <a href="{{ route('businesses.index') }}" class="flex items-center p-3 rounded-lg border border-gray-100 hover:border-gray-300 hover:bg-gray-50 transition-all group">
                    <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </div>
                    <div>
                        <span class="block text-sm font-semibold text-gray-900">Manage Businesses</span>
                        <span class="block text-xs text-gray-500">Access full registry</span>
                    </div>
                </a>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-100">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">System Health</h3>
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Database</span>
                        <span class="text-green-600 font-medium">Connected</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Payment Gateway</span>
                        <span class="text-green-600 font-medium">Active</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Last Backup</span>
                        <span class="text-gray-500">Today, 03:00 AM</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Businesses Table -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-0 lg:col-span-2 overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-white">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Recently Onboarded</h2>
                    <p class="text-sm text-gray-500">Latest organizations added to the platform</p>
                </div>
                <a href="{{ route('businesses.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-3 py-1.5 rounded-md hover:bg-indigo-100 transition-colors">
                    View All
                </a>
            </div>
            <div class="overflow-x-auto flex-grow">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Business Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Owner</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Scale</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse(\App\Models\Business::with('owner')->latest()->take(5)->get() as $business)
                            <tr class="hover:bg-gray-50/80 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-lg bg-gray-900 flex items-center justify-center text-white font-bold text-sm uppercase shadow-sm group-hover:bg-indigo-600 transition-colors">
                                            {{ substr($business->name, 0, 2) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $business->name }}</div>
                                            <div class="text-xs text-gray-500">ID: #{{ $business->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="text-sm text-gray-900">{{ $business->owner->name ?? 'Unassigned' }}</div>
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $business->owner->email ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $business->branches->count() }} Branches
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('businesses.show', $business) }}" class="text-gray-400 hover:text-indigo-600 transition-colors inline-block p-2 rounded-full hover:bg-gray-100">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        <p class="text-base font-medium text-gray-900">No businesses found</p>
                                        <p class="text-sm text-gray-500 mt-1">Get started by onboarding a new organization.</p>
                                        <a href="{{ route('businesses.create') }}" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                                            Add Business
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
