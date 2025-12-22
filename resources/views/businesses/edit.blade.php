@extends('layouts.app')

@section('title', 'Edit Business - ' . $business->name)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .modal-map {
        height: 300px;
        width: 100%;
        border-radius: 0.5rem;
        border: 2px solid #e5e7eb;
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')
<div class="p-6 max-w-2xl mx-auto">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">
                @if(auth()->user()->role === 'superadmin')
                    Edit Business
                @else
                    My Business - {{ $business->name }}
                @endif
            </h1>
            <p class="text-sm text-gray-600">
                @if(auth()->user()->role === 'superadmin')
                    Update business information and ownership
                @else
                    Manage your business branches
                @endif
            </p>
        </div>

        @if(auth()->user()->role === 'superadmin')
        <!-- Business Status Management -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-toggle-on text-indigo-600 mr-2"></i>
                        Business Status
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Manage business access and availability</p>
                </div>
                <div class="flex items-center">
                    <span class="text-xs text-gray-500 mr-2">Current:</span>
                    @if($business->status === 'active')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>Active
                        </span>
                    @elseif($business->status === 'inactive')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                            <i class="fas fa-pause-circle mr-1"></i>Inactive
                        </span>
                    @elseif($business->status === 'blocked')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                            <i class="fas fa-ban mr-1"></i>Blocked
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <!-- Activate Button -->
                <form action="{{ route('businesses.activate', $business->id) }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg {{ $business->status === 'active' ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ $business->status === 'active' ? 'disabled' : '' }}>
                        <i class="fas fa-check-circle mr-2"></i>Activate
                    </button>
                </form>

                <!-- Disable Button -->
                <form action="{{ route('businesses.disable', $business->id) }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-3 px-4 rounded-lg {{ $business->status === 'inactive' ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ $business->status === 'inactive' ? 'disabled' : '' }}>
                        <i class="fas fa-pause-circle mr-2"></i>Disable
                    </button>
                </form>

                <!-- Block Button -->
                <form action="{{ route('businesses.block', $business->id) }}" method="POST" 
                      onsubmit="return confirm('Are you sure you want to block this business?');">
                    @csrf
                    <button type="submit" 
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-4 rounded-lg {{ $business->status === 'blocked' ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ $business->status === 'blocked' ? 'disabled' : '' }}>
                        <i class="fas fa-ban mr-2"></i>Block
                    </button>
                </form>
            </div>

            <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
                <p class="text-xs text-gray-700">
                    <i class="fas fa-info-circle text-blue-600 mr-1"></i>
                    <span class="font-semibold">Active:</span> Full access • 
                    <span class="font-semibold">Inactive:</span> Temporarily disabled • 
                    <span class="font-semibold">Blocked:</span> Permanently restricted
                </p>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'superadmin')
        <form action="{{ route('businesses.update', $business->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Business Name -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Business Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name', $business->name) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                       required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Business Admin Selection -->
            <div class="mb-4">
                <label for="business_admin_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Business Admin (Optional)
                </label>
                <select id="business_admin_id" 
                        name="business_admin_id" 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('business_admin_id') border-red-500 @enderror">
                    <option value="">No Admin Assigned</option>
                    @foreach($availableAdmins as $admin)
                        <option value="{{ $admin->id }}" 
                                {{ old('business_admin_id', $business->primaryBusinessAdmin?->id) == $admin->id ? 'selected' : '' }}
                                @if($admin->business_id && $admin->business_id != $business->id) style="color: #d97706;" @endif>
                            {{ $admin->name }} ({{ $admin->email }})
                            @if($admin->business_id === null)
                                ✓ Available
                            @elseif($admin->business_id == $business->id)
                                ★ Current Admin
                            @else
                                ⚠ Assigned to: {{ $admin->managedBusiness->name ?? 'Business #' . $admin->business_id }}
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('business_admin_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                @if($availableAdmins->isEmpty())
                    <p class="text-amber-600 text-sm mt-1">
                        <i class="fas fa-exclamation-triangle"></i> No business admins found. Create a business admin user first.
                    </p>
                @else
                    <p class="text-blue-600 text-xs mt-1">
                        <i class="fas fa-info-circle"></i> You can assign any business admin. Reassigning will move them from their current business.
                    </p>
                @endif
            </div>

            <!-- Current Logo Display -->
            @if($business->logo)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Logo</label>
                    <div class="flex items-center space-x-4">
                        <img src="{{ asset('storage/' . $business->logo) }}" 
                             alt="{{ $business->name }}" 
                             class="h-20 w-20 rounded-lg border-2 border-gray-200 object-cover">
                        <div class="text-sm text-gray-600">
                            <p class="font-medium">Logo is currently set</p>
                            <p class="text-xs text-gray-500">Upload a new image to replace it</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Logo Upload -->
            <div class="mb-6">
                <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">
                    Business Logo {{ $business->logo ? '(Optional - Replace)' : '(Optional)' }}
                </label>
                <input type="file" 
                       id="logo" 
                       name="logo" 
                       accept="image/*"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('logo') border-red-500 @enderror">
                <p class="text-gray-500 text-xs mt-1">Maximum file size: 2MB. Supported formats: JPG, PNG, GIF</p>
                @error('logo')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Business Info Summary -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>Business Information
                </h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500 font-medium">Business ID</dt>
                        <dd class="text-gray-900 font-semibold">#{{ $business->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Total Branches</dt>
                        <dd class="text-gray-900 font-semibold">{{ $business->branches->count() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Total Staff</dt>
                        <dd class="text-gray-900 font-semibold">{{ \App\Models\User::where('business_id', $business->id)->count() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Created</dt>
                        <dd class="text-gray-900 font-semibold">{{ $business->created_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Branches Management -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-700 flex items-center">
                        <i class="fas fa-building text-green-500 mr-2"></i>Branches Management
                    </h3>
                    <button type="button" 
                            onclick="showAddBranchModal()"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center text-sm">
                        <i class="fas fa-plus mr-2"></i>Add Branch
                    </button>
                </div>

                @if($business->branches->isEmpty())
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-building text-4xl mb-3 opacity-50"></i>
                        <p class="text-sm">No branches yet. Click "Add Branch" to create one.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($business->branches as $branch)
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-800 mb-1">{{ $branch->name }}</h4>
                                        <div class="text-sm text-gray-600 space-y-1">
                                            @if($branch->address)
                                                <p><i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>{{ $branch->address }}</p>
                                            @endif
                                            @if($branch->region)
                                                <p><i class="fas fa-map-marked-alt text-gray-400 mr-2"></i>{{ $branch->region }}</p>
                                            @endif
                                            @if($branch->contact)
                                                <p><i class="fas fa-phone text-gray-400 mr-2"></i>{{ $branch->contact }}</p>
                                            @endif
                                            <p class="text-xs text-gray-500 mt-2">
                                                <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded">
                                                    {{ \App\Models\User::where('branch_id', $branch->id)->whereIn('role', ['manager', 'cashier'])->count() }} Staff
                                                </span>
                                                <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded ml-2">
                                                    {{ $branch->branchProducts()->count() }} Products
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2 ml-4">
                                        <button type="button" 
                                                onclick="editBranch({{ $branch->id }}, '{{ $branch->name }}', '{{ $branch->address }}', '{{ $branch->contact }}', '{{ $branch->region }}', {{ $branch->latitude ?? 'null' }}, {{ $branch->longitude ?? 'null' }})"
                                                class="text-blue-600 hover:text-blue-800 px-3 py-1 rounded text-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button type="button" 
                                                onclick="deleteBranch({{ $branch->id }}, '{{ $branch->name }}')"
                                                class="text-red-600 hover:text-red-800 px-3 py-1 rounded text-sm">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-4 border-t">
                <a href="{{ route('businesses.index') }}" 
                   class="text-gray-600 hover:text-gray-800 inline-flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Businesses
                </a>
                <div class="space-x-3">
                    <a href="{{ route('businesses.show', $business->id) }}" 
                       class="text-blue-600 hover:text-blue-800 inline-flex items-center">
                        <i class="fas fa-eye mr-2"></i>View Details
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg inline-flex items-center"
                            {{ $availableAdmins->isEmpty() ? 'disabled' : '' }}>
                        <i class="fas fa-save mr-2"></i>Update Business
                    </button>
                </div>
            </div>
        </form>
        @else
            <!-- Business Info Summary for Business Admin -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>Business Information
                </h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500 font-medium">Business ID</dt>
                        <dd class="text-gray-900 font-semibold">#{{ $business->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Total Branches</dt>
                        <dd class="text-gray-900 font-semibold">{{ $business->branches->count() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Total Staff</dt>
                        <dd class="text-gray-900 font-semibold">{{ \App\Models\User::where('business_id', $business->id)->count() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Created</dt>
                        <dd class="text-gray-900 font-semibold">{{ $business->created_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Branches Management for Business Admin -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-700 flex items-center">
                        <i class="fas fa-building text-green-500 mr-2"></i>Branches Management
                    </h3>
                    <button type="button" 
                            onclick="showAddBranchModal()"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center text-sm">
                        <i class="fas fa-plus mr-2"></i>Add Branch
                    </button>
                </div>

                @if($business->branches->isEmpty())
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-building text-4xl mb-3 opacity-50"></i>
                        <p class="text-sm">No branches yet. Click "Add Branch" to create one.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($business->branches as $branch)
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-800 mb-1">{{ $branch->name }}</h4>
                                        <div class="text-sm text-gray-600 space-y-1">
                                            @if($branch->address)
                                                <p><i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>{{ $branch->address }}</p>
                                            @endif
                                            @if($branch->region)
                                                <p><i class="fas fa-map-marked-alt text-gray-400 mr-2"></i>{{ $branch->region }}</p>
                                            @endif
                                            @if($branch->contact)
                                                <p><i class="fas fa-phone text-gray-400 mr-2"></i>{{ $branch->contact }}</p>
                                            @endif
                                            <p class="text-xs text-gray-500 mt-2">
                                                <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded">
                                                    {{ \App\Models\User::where('branch_id', $branch->id)->whereIn('role', ['manager', 'cashier'])->count() }} Staff
                                                </span>
                                                <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded ml-2">
                                                    {{ $branch->branchProducts()->count() }} Products
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2 ml-4">
                                        <button type="button" 
                                                onclick="editBranch({{ $branch->id }}, '{{ $branch->name }}', '{{ $branch->address }}', '{{ $branch->contact }}', '{{ $branch->region }}', {{ $branch->latitude ?? 'null' }}, {{ $branch->longitude ?? 'null' }})"
                                                class="text-blue-600 hover:text-blue-800 px-3 py-1 rounded text-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button type="button" 
                                                onclick="deleteBranch({{ $branch->id }}, '{{ $branch->name }}')"
                                                class="text-red-600 hover:text-red-800 px-3 py-1 rounded text-sm">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Actions for Business Admin -->
            <div class="flex items-center justify-between pt-4 border-t">
                <a href="{{ route('businesses.index') }}" 
                   class="text-gray-600 hover:text-gray-800 inline-flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>Back to My Business
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Add Branch Modal -->
<div id="addBranchModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Add New Branch</h3>
        </div>
        <form id="addBranchForm" action="{{ route('branches.store') }}" method="POST">
            @csrf
            <input type="hidden" name="business_id" value="{{ $business->id }}">
            <input type="hidden" id="add_latitude" name="latitude">
            <input type="hidden" id="add_longitude" name="longitude">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Branch Name *</label>
                <input type="text" name="name" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            
            <!-- Map Selection -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Select Location on Map *
                </label>
                <div id="addBranchMap" class="modal-map"></div>
                <p class="text-xs text-gray-600">Click on the map to select the branch location</p>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                <textarea id="add_address" name="address" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Contact</label>
                <input type="text" name="contact"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Region</label>
                <select id="add_region" name="region" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Select Region</option>
                    <option value="Greater Accra">Greater Accra</option>
                    <option value="Ashanti">Ashanti</option>
                    <option value="Western">Western</option>
                    <option value="Eastern">Eastern</option>
                    <option value="Central">Central</option>
                    <option value="Northern">Northern</option>
                    <option value="Upper East">Upper East</option>
                    <option value="Upper West">Upper West</option>
                    <option value="Volta">Volta</option>
                    <option value="Brong-Ahafo">Brong-Ahafo</option>
                    <option value="Western North">Western North</option>
                    <option value="Bono East">Bono East</option>
                    <option value="Ahafo">Ahafo</option>
                    <option value="Savannah">Savannah</option>
                    <option value="North East">North East</option>
                    <option value="Oti">Oti</option>
                </select>
            </div>
            
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="hideAddBranchModal()"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">
                    Cancel
                </button>
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                    Add Branch
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Branch Modal -->
<div id="editBranchModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Edit Branch</h3>
        </div>
        <form id="editBranchForm" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_latitude" name="latitude">
            <input type="hidden" id="edit_longitude" name="longitude">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Branch Name *</label>
                <input type="text" id="edit_name" name="name" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- Map Selection -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Update Location on Map
                </label>
                <div id="editBranchMap" class="modal-map"></div>
                <p class="text-xs text-gray-600">Click on the map to update the branch location</p>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                <textarea id="edit_address" name="address" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Contact</label>
                <input type="text" id="edit_contact" name="contact"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Region</label>
                <select id="edit_region" name="region" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Region</option>
                    <option value="Greater Accra">Greater Accra</option>
                    <option value="Ashanti">Ashanti</option>
                    <option value="Western">Western</option>
                    <option value="Eastern">Eastern</option>
                    <option value="Central">Central</option>
                    <option value="Northern">Northern</option>
                    <option value="Upper East">Upper East</option>
                    <option value="Upper West">Upper West</option>
                    <option value="Volta">Volta</option>
                    <option value="Brong-Ahafo">Brong-Ahafo</option>
                    <option value="Western North">Western North</option>
                    <option value="Bono East">Bono East</option>
                    <option value="Ahafo">Ahafo</option>
                    <option value="Savannah">Savannah</option>
                    <option value="North East">North East</option>
                    <option value="Oti">Oti</option>
                </select>
            </div>
            
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="hideEditBranchModal()"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">
                    Cancel
                </button>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Update Branch
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddBranchModal() {
    document.getElementById('addBranchModal').classList.remove('hidden');
}

function hideAddBranchModal() {
    document.getElementById('addBranchModal').classList.add('hidden');
    document.getElementById('addBranchForm').reset();
}

function editBranch(id, name, address, contact, region) {
    const form = document.getElementById('editBranchForm');
    form.action = `/branches/${id}`;
    
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_address').value = address || '';
    document.getElementById('edit_contact').value = contact || '';
    document.getElementById('edit_region').value = region || '';
    
    document.getElementById('editBranchModal').classList.remove('hidden');
}

function hideEditBranchModal() {
    document.getElementById('editBranchModal').classList.add('hidden');
}

function deleteBranch(id, name) {
    if (confirm(`Are you sure you want to delete the branch "${name}"?\n\nThis will also remove all associated staff assignments and inventory data. This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/branches/${id}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let addBranchMap = null;
let addBranchMarker = null;
let editBranchMap = null;
let editBranchMarker = null;

function initAddBranchMap() {
    if (addBranchMap) {
        addBranchMap.remove();
    }
    
    addBranchMap = L.map('addBranchMap').setView([7.9465, -1.0232], 7); // Ghana center
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(addBranchMap);
    
    addBranchMap.on('click', function(e) {
        setAddBranchMarker(e.latlng.lat, e.latlng.lng);
    });
    
    setTimeout(() => {
        addBranchMap.invalidateSize();
    }, 100);
}

function initEditBranchMap(lat, lng) {
    if (editBranchMap) {
        editBranchMap.remove();
    }
    
    const center = (lat && lng) ? [lat, lng] : [7.9465, -1.0232];
    const zoom = (lat && lng) ? 13 : 7;
    
    editBranchMap = L.map('editBranchMap').setView(center, zoom);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(editBranchMap);
    
    if (lat && lng) {
        setEditBranchMarker(lat, lng);
    }
    
    editBranchMap.on('click', function(e) {
        setEditBranchMarker(e.latlng.lat, e.latlng.lng);
    });
    
    setTimeout(() => {
        editBranchMap.invalidateSize();
    }, 100);
}

function setAddBranchMarker(lat, lng) {
    if (addBranchMarker) {
        addBranchMap.removeLayer(addBranchMarker);
    }
    
    addBranchMarker = L.marker([lat, lng], { draggable: true }).addTo(addBranchMap);
    
    addBranchMarker.on('dragend', function(e) {
        const position = e.target.getLatLng();
        setAddBranchMarker(position.lat, position.lng);
    });
    
    document.getElementById('add_latitude').value = lat;
    document.getElementById('add_longitude').value = lng;
    
    reverseGeocode(lat, lng, 'add');
    
    addBranchMap.setView([lat, lng], 13);
}

function setEditBranchMarker(lat, lng) {
    if (editBranchMarker) {
        editBranchMap.removeLayer(editBranchMarker);
    }
    
    editBranchMarker = L.marker([lat, lng], { draggable: true }).addTo(editBranchMap);
    
    editBranchMarker.on('dragend', function(e) {
        const position = e.target.getLatLng();
        setEditBranchMarker(position.lat, position.lng);
    });
    
    document.getElementById('edit_latitude').value = lat;
    document.getElementById('edit_longitude').value = lng;
    
    reverseGeocode(lat, lng, 'edit');
    
    editBranchMap.setView([lat, lng], 13);
}

function reverseGeocode(lat, lng, prefix) {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
        .then(response => response.json())
        .then(data => {
            if (data.display_name) {
                const addressField = document.getElementById(`${prefix}_address`);
                if (addressField) {
                    addressField.value = data.display_name;
                }
                
                // Try to detect region from address components
                const address = data.address || {};
                let region = address.state || address.region || '';
                
                if (region) {
                    const regionSelect = document.getElementById(`${prefix}_region`);
                    if (regionSelect) {
                        // Try to match with existing options
                        for (let option of regionSelect.options) {
                            if (option.text.toLowerCase().includes(region.toLowerCase()) || 
                                region.toLowerCase().includes(option.text.toLowerCase())) {
                                regionSelect.value = option.value;
                                break;
                            }
                        }
                    }
                }
            }
        })
        .catch(error => console.error('Reverse geocoding error:', error));
}

function showAddBranchModal() {
    document.getElementById('addBranchModal').classList.remove('hidden');
    setTimeout(() => initAddBranchMap(), 100);
}

function hideAddBranchModal() {
    document.getElementById('addBranchModal').classList.add('hidden');
    document.getElementById('addBranchForm').reset();
    if (addBranchMap) {
        addBranchMap.remove();
        addBranchMap = null;
        addBranchMarker = null;
    }
}

function editBranch(id, name, address, contact, region, latitude, longitude) {
    const form = document.getElementById('editBranchForm');
    form.action = `/branches/${id}`;
    
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_address').value = address || '';
    document.getElementById('edit_contact').value = contact || '';
    document.getElementById('edit_region').value = region || '';
    
    document.getElementById('editBranchModal').classList.remove('hidden');
    
    setTimeout(() => {
        const lat = latitude !== null ? parseFloat(latitude) : null;
        const lng = longitude !== null ? parseFloat(longitude) : null;
        initEditBranchMap(lat, lng);
    }, 100);
}

function hideEditBranchModal() {
    document.getElementById('editBranchModal').classList.add('hidden');
    if (editBranchMap) {
        editBranchMap.remove();
        editBranchMap = null;
        editBranchMarker = null;
    }
}

function deleteBranch(id, name) {
    if (confirm(`Are you sure you want to delete the branch "${name}"?\n\nThis will also remove all associated staff assignments and inventory data. This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/branches/${id}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection