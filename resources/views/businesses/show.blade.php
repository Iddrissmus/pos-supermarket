@extends('layouts.app')

@section('title', $business->name . ' - Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    
    <!-- Top Header Navigation -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li><a href="{{ route('businesses.index') }}" class="text-gray-400 hover:text-gray-500">Businesses</a></li>
                    <li><span class="text-gray-300">/</span></li>
                    <li class="text-gray-900 font-medium" aria-current="page">{{ $business->name }}</li>
                </ol>
            </nav>
            <h1 class="mt-2 text-2xl font-bold text-gray-900 tracking-tight">{{ $business->name }}</h1>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="{{ route('businesses.edit', $business->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="h-4 w-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Manage Settings
            </a>
            <a href="{{ url('/') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                View Reports
            </a>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <!-- Branches -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                            <i class="fas fa-store text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide truncate">Total Branches</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-bold text-gray-900">{{ $business->branches->count() }}</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3 border-t border-gray-100">
                <div class="text-xs font-medium text-emerald-600 truncate">
                    {{ $business->branches->where('status', 'active')->count() }} Active locations
                </div>
            </div>
        </div>

        <!-- Staff -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                            <i class="fas fa-users text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide truncate">Total Staff</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-bold text-gray-900">{{ \App\Models\User::where('business_id', $business->id)->count() }}</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3 border-t border-gray-100">
                <a href="{{ route('system-users.index') }}" class="text-xs font-medium text-blue-600 hover:text-blue-500 truncate">
                    View all employees &rarr;
                </a>
            </div>
        </div>

        <!-- Products (Placeholder count) -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-lg bg-purple-100 flex items-center justify-center text-purple-600">
                            <i class="fas fa-box text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide truncate">Inventory Items</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-bold text-gray-900">{{ \App\Models\Product::where('business_id', $business->id)->count() }}</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
             <div class="bg-gray-50 px-5 py-3 border-t border-gray-100">
                <div class="text-xs font-medium text-gray-500 truncate">
                   Across all branches
                </div>
            </div>
        </div>

        <!-- Admin -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        @if($business->primaryBusinessAdmin)
                            <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold border border-indigo-200">
                                {{ strtoupper(substr($business->primaryBusinessAdmin->name, 0, 2)) }}
                            </div>
                        @else
                            <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400">
                                <i class="fas fa-user-slash"></i>
                            </div>
                        @endif
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide truncate">Primary Admin</dt>
                            <dd class="flex items-baseline">
                                <div class="text-base font-bold text-gray-900 truncate">
                                    {{ $business->primaryBusinessAdmin ? Str::limit($business->primaryBusinessAdmin->name, 15) : 'Unassigned' }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3 border-t border-gray-100">
                 @if($business->primaryBusinessAdmin)
                    <div class="text-xs font-medium text-gray-500 truncate">{{ $business->primaryBusinessAdmin->email }}</div>
                 @else
                    <a href="{{ route('businesses.edit', $business->id) }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-500">Assign admin &rarr;</a>
                 @endif
            </div>
        </div>
    </div>

    <!-- Main Content Split -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Branches List -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <div>
                         <h3 class="text-base font-semibold text-gray-900">Branch Network</h3>
                         <p class="text-xs text-gray-500 mt-1">{{ $business->branches->count() }} locations found</p>
                    </div>
                </div>
                
                @if($business->branches->isEmpty())
                     <div class="p-12 text-center">
                        <i class="fas fa-store text-gray-300 text-3xl mb-3"></i>
                        <h3 class="text-sm font-medium text-gray-900">No branches added</h3>
                        <p class="text-sm text-gray-500 mt-1">Go to settings to add your first branch.</p>
                        <a href="{{ route('businesses.edit', $business->id) }}" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200">
                            Add Branch
                        </a>
                    </div>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach($business->branches as $branch)
                            <li class="p-6 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-start space-x-4">
                                        <div class="flex-shrink-0 mt-1">
                                            <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                                                <i class="fas fa-map-marker-alt text-xs"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-gray-900">{{ $branch->name }}</h4>
                                            <div class="mt-1 flex items-center text-xs text-gray-500 space-x-3">
                                                @if($branch->region)
                                                    <span>{{ $branch->region }}</span>
                                                    <span class="text-gray-300">&bull;</span>
                                                @endif
                                                @if($branch->contact)
                                                    <span>{{ $branch->contact }}</span>
                                                @endif
                                            </div>
                                             @if($branch->address)
                                                <p class="mt-1 text-xs text-gray-400">{{ $branch->address }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                         <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <!-- Right: Business Info Summary -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                 <div class="flex items-center space-x-4 mb-6">
                    @if($business->logo)
                         <img src="{{ asset('storage/' . $business->logo) }}" alt="Logo" class="h-16 w-16 rounded-lg object-cover border border-gray-200">
                    @else
                        <div class="h-16 w-16 rounded-lg bg-indigo-600 flex items-center justify-center text-white text-xl font-bold shadow-sm">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">{{ $business->name }}</h2>
                        <p class="text-xs text-gray-500">ID: #{{ $business->id }}</p>
                    </div>
                </div>
                
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Details</h4>
                <dl class="space-y-3">
                    <div class="flex justify-between items-center text-sm">
                        <dt class="text-gray-500">Status</dt>
                        <dd class="font-medium text-green-600">Active</dd>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <dt class="text-gray-500">Registered</dt>
                        <dd class="font-medium text-gray-900">{{ $business->created_at->format('M d, Y') }}</dd>
                    </div>
                     <div class="flex justify-between items-center text-sm">
                        <dt class="text-gray-500">Last Update</dt>
                        <dd class="font-medium text-gray-900">{{ $business->updated_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
