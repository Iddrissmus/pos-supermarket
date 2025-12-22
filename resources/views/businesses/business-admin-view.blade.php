@extends('layouts.app')

@section('title', 'My Business')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">My Business</h1>
                <p class="text-sm text-gray-600">Manage your business and branches</p>
            </div>
            <a href="{{ route('branches.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <i class="fas fa-plus mr-2"></i>Add New Branch
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mt-4">
                <p class="text-green-800"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mt-4">
                <p class="text-red-800"><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</p>
            </div>
        @endif
    </div>

    <!-- Card Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Business Details Card -->
        <div class="bg-white shadow rounded-lg p-6 border border-gray-200">
            <div class="flex items-center mb-4 pb-4 border-b border-gray-200">
                <div class="bg-blue-100 rounded-full p-3 mr-4">
                    <i class="fas fa-building text-blue-600 text-xl"></i>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">Business Details</h2>
            </div>

            <div class="space-y-4">
                <!-- Logo/Name -->
                <div class="flex items-center">
                    @if($business->logo)
                        <img src="{{ asset('storage/' . $business->logo) }}" 
                             alt="{{ $business->name }}" 
                             class="h-16 w-16 rounded-lg border-2 border-gray-200 mr-4">
                    @else
                        <div class="h-16 w-16 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-xl mr-4">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">{{ $business->name }}</h3>
                        <p class="text-sm text-gray-500">Business ID: #{{ $business->id }}</p>
                    </div>
                </div>

                <!-- Status -->
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-600">Status</span>
                        @if($business->status === 'active')
                            <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>Active
                            </span>
                        @elseif($business->status === 'inactive')
                            <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-pause-circle mr-1"></i>Inactive
                            </span>
                        @else
                            <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                <i class="fas fa-ban mr-1"></i>Blocked
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-blue-50 rounded-lg p-3">
                        <div class="text-2xl font-bold text-blue-700">{{ $business->branches->count() }}</div>
                        <div class="text-xs text-blue-600 font-medium">Branches</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-3">
                        <div class="text-2xl font-bold text-green-700">{{ \App\Models\User::where('business_id', $business->id)->count() }}</div>
                        <div class="text-xs text-green-600 font-medium">Staff</div>
                    </div>
                </div>

                <!-- Dates -->
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Created</span>
                        <span class="text-gray-900 font-medium">{{ $business->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Last Updated</span>
                        <span class="text-gray-900 font-medium">{{ $business->updated_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Branches Card -->
        <div class="bg-white shadow rounded-lg p-6 border border-gray-200">
            <div class="flex items-center mb-4 pb-4 border-b border-gray-200">
                <div class="bg-green-100 rounded-full p-3 mr-4">
                    <i class="fas fa-store text-green-600 text-xl"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-lg font-semibold text-gray-900">Branches</h2>
                    <p class="text-xs text-gray-500">{{ $business->branches->count() }} {{ Str::plural('location', $business->branches->count()) }}</p>
                </div>
            </div>

            @if($business->branches->isEmpty())
                <div class="text-center py-8">
                    <i class="fas fa-store text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500 mb-3">No branches yet</p>
                    <a href="{{ route('branches.create') }}" 
                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        <i class="fas fa-plus mr-1"></i>Add your first branch
                    </a>
                </div>
            @else
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach($business->branches as $branch)
                        <div class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900">{{ $branch->name }}</h3>
                                    @if($branch->location)
                                        <p class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-map-marker-alt mr-1"></i>{{ Str::limit($branch->location, 30) }}
                                        </p>
                                    @endif
                                </div>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $branch->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($branch->status) }}
                                </span>
                            </div>
                            @if($branch->manager)
                                <div class="text-xs text-gray-600 flex items-center mt-2 pt-2 border-t border-gray-100">
                                    <i class="fas fa-user-tie mr-1 text-gray-400"></i>
                                    <span>{{ $branch->manager->name }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Actions Card -->
        <div class="bg-white shadow rounded-lg p-6 border border-gray-200">
            <div class="flex items-center mb-4 pb-4 border-b border-gray-200">
                <div class="bg-purple-100 rounded-full p-3 mr-4">
                    <i class="fas fa-cog text-purple-600 text-xl"></i>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
            </div>

            <div class="space-y-3">
                <!-- View Details -->
                <a href="{{ route('businesses.show', $business->id) }}" 
                   class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors group">
                    <div class="bg-blue-100 group-hover:bg-blue-200 rounded-lg p-2 mr-3">
                        <i class="fas fa-eye text-blue-600 text-lg"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900 group-hover:text-blue-600">View Full Details</div>
                        <div class="text-xs text-gray-500">See complete business information</div>
                    </div>
                </a>

                <!-- Manage Branches -->
                <a href="{{ route('branches.index') }}" 
                   class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-green-50 hover:border-green-300 transition-colors group">
                    <div class="bg-green-100 group-hover:bg-green-200 rounded-lg p-2 mr-3">
                        <i class="fas fa-store text-green-600 text-lg"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900 group-hover:text-green-600">Manage Branches</div>
                        <div class="text-xs text-gray-500">Edit, view, and control branches</div>
                    </div>
                </a>

                <!-- Add Branch -->
                <a href="{{ route('branches.create') }}" 
                   class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-indigo-50 hover:border-indigo-300 transition-colors group">
                    <div class="bg-indigo-100 group-hover:bg-indigo-200 rounded-lg p-2 mr-3">
                        <i class="fas fa-plus-circle text-indigo-600 text-lg"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900 group-hover:text-indigo-600">Add New Branch</div>
                        <div class="text-xs text-gray-500">Create a new branch location</div>
                    </div>
                </a>

                <!-- Manage Users -->
                <a href="{{ route('admin.cashiers.index') }}" 
                   class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-orange-50 hover:border-orange-300 transition-colors group">
                    <div class="bg-orange-100 group-hover:bg-orange-200 rounded-lg p-2 mr-3">
                        <i class="fas fa-users text-orange-600 text-lg"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900 group-hover:text-orange-600">Manage Staff</div>
                        <div class="text-xs text-gray-500">View and manage employees</div>
                    </div>
                </a>

                <!-- View Map -->
                <a href="{{ route('businesses.myMap') }}" 
                   class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-teal-50 hover:border-teal-300 transition-colors group">
                    <div class="bg-teal-100 group-hover:bg-teal-200 rounded-lg p-2 mr-3">
                        <i class="fas fa-map-marked-alt text-teal-600 text-lg"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900 group-hover:text-teal-600">View on Map</div>
                        <div class="text-xs text-gray-500">See branch locations on map</div>
                    </div>
                </a>

                <!-- Products -->
                <a href="{{ route('layouts.product') }}" 
                   class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-purple-50 hover:border-purple-300 transition-colors group">
                    <div class="bg-purple-100 group-hover:bg-purple-200 rounded-lg p-2 mr-3">
                        <i class="fas fa-box text-purple-600 text-lg"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900 group-hover:text-purple-600">Manage Products</div>
                        <div class="text-xs text-gray-500">View and update inventory</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
