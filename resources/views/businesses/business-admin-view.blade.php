@extends('layouts.app')

@section('title', 'Business Profile')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    
    <!-- Header Banner -->
    <div class="relative bg-gradient-to-r from-slate-900 to-slate-800 rounded-xl shadow-lg overflow-hidden mb-8">
        <div class="absolute inset-0 bg-white/5" style="background-image: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 20%);"></div>
        <div class="relative p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <div class="relative group">
                    @if($business->logo)
                        <img src="{{ asset('storage/' . $business->logo) }}" alt="{{ $business->name }}" 
                             class="w-24 h-24 rounded-xl object-cover border-4 border-white/20 shadow-md">
                    @else
                        <div class="w-24 h-24 rounded-xl bg-blue-600 flex items-center justify-center text-white font-bold text-3xl border-4 border-white/20 shadow-md">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-white">{{ $business->name }}</h1>
                    <p class="text-slate-400 mt-1 flex items-center">
                        <i class="fas fa-id-badge mr-2"></i> ID: #{{ $business->id }}
                        <span class="mx-3 text-slate-600">|</span>
                        @if($business->status === 'active')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-500/20 text-green-400">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-500/20 text-red-400">
                                {{ ucfirst($business->status) }}
                            </span>
                        @endif
                    </p>
                </div>
            </div>
            
            <div class="flex gap-3">
                 <a href="{{ route('dashboard.business-admin') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors font-medium">
                    <i class="fas fa-arrow-left mr-2"></i> Dashboard
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-8 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-md flex items-center justify-between shadow-sm">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3 text-xl"></i>
                <p class="text-green-700 font-medium">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif
    
    @if($errors->any())
        <div class="mb-8 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-md">
            <div class="flex">
                <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-0.5"></i>
                <div>
                    <h3 class="text-sm font-medium text-red-800">There were problems with your submission</h3>
                    <ul class="list-disc list-inside text-sm text-red-700 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Main Content Column -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Quick Stats Row -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Branches</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $business->branches->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600">
                        <i class="fas fa-store text-xl"></i>
                    </div>
                </div>
                
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Staff</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ \App\Models\User::where('business_id', $business->id)->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-50 rounded-lg flex items-center justify-center text-purple-600">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                </div>
                
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Products</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ \App\Models\Product::where('business_id', $business->id)->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center text-green-600">
                        <i class="fas fa-box text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center">
                        <i class="fas fa-pen-to-square text-blue-600 mr-2"></i> Edit Business Profile
                    </h2>
                </div>
                <div class="p-6">
                    <form action="{{ route('my-business.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Logo Upload -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Business Logo</label>
                                <div class="flex items-center gap-4">
                                    <div class="relative group w-20 h-20 rounded-lg overflow-hidden border-2 border-dashed border-gray-300 bg-gray-50 flex items-center justify-center hover:border-blue-500 transition-colors">
                                        @if($business->logo)
                                            <img src="{{ asset('storage/' . $business->logo) }}" class="w-full h-full object-cover">
                                        @else
                                            <i class="fas fa-image text-gray-400 text-2xl"></i>
                                        @endif
                                        <input type="file" name="logo" class="absolute inset-0 opacity-0 cursor-pointer">
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <p class="font-medium text-gray-900">Click to upload new logo</p>
                                        <p>SVG, PNG, JPG or GIF (max. 2MB)</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Business Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $business->name) }}" 
                                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm" required>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-semibold shadow-sm transition-all flex items-center">
                                <i class="fas fa-save mr-2"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Branches List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center">
                        <i class="fas fa-network-wired text-indigo-600 mr-2"></i> Branch Locations
                    </h2>
                    <a href="{{ route('branches.create') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        + Add Branch
                    </a>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($business->branches as $branch)
                        <div class="p-4 hover:bg-gray-50 transition-colors flex items-center justify-between group">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                                    {{ substr($branch->name, 0, 1) }}
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $branch->name }}</h3>
                                    <p class="text-sm text-gray-500 flex items-center">
                                        <i class="fas fa-map-marker-alt text-gray-400 mr-1.5" style="font-size: 0.8em;"></i>
                                        {{ $branch->location ?? 'No address set' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-right hidden sm:block">
                                    <p class="text-sm font-medium text-gray-700">{{ $branch->manager->name ?? 'No Manager' }}</p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $branch->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($branch->status) }}
                                    </span>
                                </div>
                                <a href="{{ route('branches.edit', $branch->id) }}" class="p-2 text-gray-400 hover:text-blue-600 transition-colors">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-store-slash text-4xl mb-3 text-gray-300"></i>
                            <p>No branches found. <a href="{{ route('branches.create') }}" class="text-blue-600 hover:underline">Create one now</a>.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Sidebar Column -->
        <div class="space-y-6">
            
            <!-- Account Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">Account Overview</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-50">
                        <span class="text-sm text-gray-500">Primary Admin</span>
                        <span class="text-sm font-medium text-gray-900">{{ $business->primaryBusinessAdmin->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between pb-3 border-b border-gray-50">
                        <span class="text-sm text-gray-500">Email</span>
                        <span class="text-sm font-medium text-gray-900 truncate max-w-[150px]">{{ $business->primaryBusinessAdmin->email ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Joined</span>
                        <span class="text-sm font-medium text-gray-900">{{ $business->created_at->format('M Y') }}</span>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-100">
                    <a href="{{ route('business-admin.profile.edit') }}" class="block w-full text-center px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-lg text-sm font-medium transition-colors border border-gray-200">
                        Edit Personal Profile
                    </a>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                <h3 class="font-bold text-lg mb-4 flex items-center">
                    <i class="fas fa-rocket mr-2"></i> Quick Actions
                </h3>
                <div class="space-y-3">
                    <a href="{{ route('branches.create') }}" class="flex items-center p-3 bg-white/10 hover:bg-white/20 rounded-lg transition-colors backdrop-blur-sm">
                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center mr-3">
                            <i class="fas fa-plus"></i>
                        </div>
                        <span class="font-medium">Add New Branch</span>
                    </a>
                    <a href="{{ route('admin.staff.index') }}" class="flex items-center p-3 bg-white/10 hover:bg-white/20 rounded-lg transition-colors backdrop-blur-sm">
                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center mr-3">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <span class="font-medium">Staff Management</span>
                    </a>
                    <a href="{{ route('layouts.product') }}" class="flex items-center p-3 bg-white/10 hover:bg-white/20 rounded-lg transition-colors backdrop-blur-sm">
                         <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center mr-3">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <span class="font-medium">Inventory</span>
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
