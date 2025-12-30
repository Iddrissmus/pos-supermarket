@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Profile Settings</h1>
            <p class="mt-1 text-sm text-gray-500">Manage your account settings and preferences.</p>
        </div>

        @if (session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Sidebar: Profile Overview -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative">
                    <!-- Background Pattern/Gradient -->
                    <div class="h-32 bg-gradient-to-r from-gray-800 to-gray-900 relative">
                        <div class="absolute inset-0 opacity-20">
                            {{-- Optional pattern svg can go here --}}
                        </div>
                    </div>

                    <div class="px-6 pb-6 relative">
                        <!-- Avatar -->
                        <div class="relative -mt-16 mb-4 flex justify-center">
                            @php
                                $roleColors = [
                                    'superadmin' => 'bg-purple-600',
                                    'business_admin' => 'bg-blue-600',
                                    'manager' => 'bg-green-600',
                                    'cashier' => 'bg-orange-600',
                                ];
                                $roleBg = $roleColors[$user->role] ?? 'bg-gray-600';
                            @endphp
                            <div class="h-32 w-32 rounded-full border-4 border-white shadow-md flex items-center justify-center {{ $roleBg }} text-white text-4xl font-bold">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        </div>

                        <!-- User Info -->
                        <div class="text-center">
                            <h2 class="text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                            <p class="text-sm text-gray-500 mb-4">{{ $user->email }}</p>

                            <!-- Role Badge -->
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium 
                                {{ $user->role === 'superadmin' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $user->role === 'business_admin' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $user->role === 'manager' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $user->role === 'cashier' ? 'bg-orange-100 text-orange-800' : '' }}
                                bg-gray-100 text-gray-800"> <!-- default fallback -->
                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                            </div>
                        </div>

                        <!-- Context Info -->
                        <div class="mt-6 pt-6 border-t border-gray-100 space-y-3">
                            @if($user->business)
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-building w-5 text-gray-400"></i>
                                    <span class="ml-2">{{ $user->business->name }}</span>
                                </div>
                            @endif
                            
                            @if($user->branch)
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-store w-5 text-gray-400"></i>
                                    <span class="ml-2">{{ $user->branch->name }}</span>
                                </div>
                            @endif

                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-clock w-5 text-gray-400"></i>
                                <span class="ml-2">Joined {{ $user->created_at->format('M Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Content: Edit Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Personal Details</h3>
                        <span class="text-xs text-gray-500 bg-white px-2 py-1 rounded border border-gray-200">Editable</span>
                    </div>

                    <div class="p-6">
                        <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                            @csrf
                            @method('PATCH')

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Name -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required 
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm transition duration-150 ease-in-out py-2.5">
                                    @error('name')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                    <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" 
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm transition duration-150 ease-in-out py-2.5">
                                    @error('phone')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Separator -->
                             <div class="border-t border-gray-100 pt-6 mt-2">
                                <h4 class="text-sm font-medium text-gray-900 mb-4">Account Information</h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Email (Read Only) -->
                                    <div class="col-span-1 md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                        <div class="flex rounded-md shadow-sm">
                                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                                <i class="fas fa-envelope"></i>
                                            </span>
                                            <input type="email" value="{{ $user->email }}" disabled
                                                class="flex-1 w-full rounded-none rounded-r-lg border-gray-300 bg-gray-50 text-gray-500 cursor-not-allowed sm:text-sm py-2.5">
                                        </div>
                                        <p class="text-xs text-gray-400 mt-1">To change your email, please contact a system administrator.</p>
                                    </div>
                                    
                                    <!-- Role (Read Only) -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                         <input type="text" value="{{ ucfirst(str_replace('_', ' ', $user->role)) }}" disabled
                                            class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-500 cursor-not-allowed shadow-sm py-2.5">
                                    </div>

                                     <!-- Status (Read Only) -->
                                     <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Account Status</label>
                                         <input type="text" value="Active" disabled
                                            class="w-full rounded-lg border-gray-300 bg-gray-50 text-green-600 font-medium cursor-not-allowed shadow-sm py-2.5">
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                                <button type="button" onclick="window.history.back()" class="bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 font-medium py-2.5 px-6 rounded-lg mr-3 shadow-sm transition duration-150">
                                    Cancel
                                </button>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-8 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out flex items-center transform hover:-translate-y-0.5">
                                    <i class="fas fa-save mr-2"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
