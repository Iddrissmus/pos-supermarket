@extends('layouts.app')

@section('title', 'Edit Business - ' . $business->name)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .modal-map {
        height: 350px;
        width: 100%;
        border-radius: 0.75rem;
        z-index: 10;
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
            @if(auth()->user()->role === 'superadmin')
                Edit Business
            @else
                Settings: {{ $business->name }}
            @endif
        </h1>
        <p class="text-sm text-gray-500 mt-1">
            @if(auth()->user()->role === 'superadmin')
                Manage configuration, ownership, and branches for this business.
            @else
                Manage your business profile and branch locations.
            @endif
        </p>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 p-4 border border-green-200 flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-400 mt-0.5"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-lg bg-red-50 p-4 border border-red-200 flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-400 mt-0.5"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Main Form -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Business Details Card -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-base font-semibold text-gray-900">Business Profile</h2>
                    <p class="text-xs text-gray-500">Basic information and branding.</p>
                </div>
                
                <div class="p-6">
                    @if(auth()->user()->role === 'superadmin')
                        <form action="{{ route('businesses.update', $business->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
                            <div class="space-y-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Business Name <span class="text-red-500">*</span></label>
                                    <input type="text" id="name" name="name" value="{{ old('name', $business->name) }}" 
                                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base py-3 px-4" required>
                                    @error('name')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="business_admin_id" class="block text-sm font-medium text-gray-700 mb-1">Primary Owner / Admin</label>
                                    <div class="relative">
                                        <select id="business_admin_id" name="business_admin_id" 
                                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base py-3 px-4 appearance-none">
                                            <option value="">No Admin Assigned</option>
                                            @foreach($availableAdmins as $admin)
                                                <option value="{{ $admin->id }}" 
                                                        {{ old('business_admin_id', $business->primaryBusinessAdmin?->id) == $admin->id ? 'selected' : '' }}
                                                        class="{{ ($admin->business_id && $admin->business_id != $business->id) ? 'text-amber-600' : 'text-gray-900' }}">
                                                    {{ $admin->name }} ({{ $admin->email }})
                                                    @if($admin->business_id && $admin->business_id != $business->id) [Assigned Elsewhere] @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                            <i class="fas fa-chevron-down text-xs"></i>
                                        </div>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500">Assigning a user here will give them full control over this business.</p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-start">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Logo</label>
                                        @if($business->logo)
                                            <div class="relative group w-24 h-24 rounded-lg overflow-hidden border border-gray-200">
                                                <img src="{{ asset('storage/' . $business->logo) }}" alt="Logo" class="w-full h-full object-cover">
                                            </div>
                                        @else
                                            <div class="w-24 h-24 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-300">
                                                <i class="fas fa-image text-2xl"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <div>
                                        <label for="logo" class="block text-sm font-medium text-gray-700 mb-1">Update Logo</label>
                                        <input type="file" id="logo" name="logo" accept="image/*" 
                                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors">
                                        <p class="text-xs text-gray-500 mt-2">Recommended: Square JPG/PNG, max 2MB.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium shadow-sm transition-colors">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    @else
                        <!-- Simple view for non-superadmins -->
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Business Name</h3>
                                <p class="text-lg font-semibold text-gray-900 mt-1">{{ $business->name }}</p>
                            </div>
                            <!-- Add more readable fields for owners here if needed -->
                        </div>
                    @endif
                </div>
            </div>

            <!-- Branches Management Card -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Branch Locations</h2>
                        <p class="text-xs text-gray-500">Manage physical stores and warehouses.</p>
                    </div>
                    <button type="button" onclick="showAddBranchModal()" 
                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-emerald-700 bg-emerald-100 hover:bg-emerald-200 transition-colors">
                        <i class="fas fa-plus mr-1.5"></i> Add Branch
                    </button>
                </div>
                
                @if($business->branches->isEmpty())
                    <div class="p-12 text-center">
                        <div class="mx-auto h-12 w-12 text-gray-300 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-store text-xl"></i>
                        </div>
                        <h3 class="text-sm font-medium text-gray-900">No branches yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Add your first location to get started.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($business->branches as $branch)
                            <div class="p-4 hover:bg-gray-50 transition-colors flex items-center justify-between group">
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                                            <i class="fas fa-map-marker-alt text-xs"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-900">{{ $branch->name }}</h4>
                                        <div class="text-xs text-gray-500 space-y-0.5 mt-0.5">
                                            @if($branch->address) <p>{{ $branch->address }}</p> @endif
                                            @if($branch->contact) <p><i class="fas fa-phone-alt text-[10px] mr-1"></i>{{ $branch->contact }}</p> @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button onclick="editBranch({{ $branch->id }}, '{{ $branch->name }}', '{{ $branch->address }}', '{{ $branch->contact }}', '{{ $branch->region }}', {{ $branch->latitude ?? 'null' }}, {{ $branch->longitude ?? 'null' }})" 
                                            class="p-2 text-gray-400 hover:text-indigo-600 transition-colors" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button onclick="deleteBranch({{ $branch->id }}, '{{ $branch->name }}')" 
                                            class="p-2 text-gray-400 hover:text-red-600 transition-colors" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        <!-- Right Column: Status & Info -->
        <div class="space-y-6">
            
            @if(auth()->user()->role === 'superadmin')
            <!-- Status Card -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-sm font-semibold text-gray-900">Account Status</h2>
                </div>
                <div class="p-5">
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-sm text-gray-500">Current Status</span>
                        @if($business->status === 'active')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                Active
                            </span>
                        @elseif($business->status === 'inactive')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                Inactive
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                Blocked
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 gap-3">
                        <form action="{{ route('businesses.activate', $business->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-green-200 text-sm font-medium rounded-lg text-green-700 bg-green-50 hover:bg-green-100 transition-colors {{ $business->status === 'active' ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $business->status === 'active' ? 'disabled' : '' }}>
                                Activate Access
                            </button>
                        </form>
                        <form action="{{ route('businesses.disable', $business->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-yellow-200 text-sm font-medium rounded-lg text-yellow-700 bg-yellow-50 hover:bg-yellow-100 transition-colors {{ $business->status === 'inactive' ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $business->status === 'inactive' ? 'disabled' : '' }}>
                                Suspend Temporarily
                            </button>
                        </form>
                        <form action="{{ route('businesses.block', $business->id) }}" method="POST" onsubmit="return confirm('Block this business? Users will be unable to log in.')">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-red-200 text-sm font-medium rounded-lg text-red-700 bg-red-50 hover:bg-red-100 transition-colors {{ $business->status === 'blocked' ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $business->status === 'blocked' ? 'disabled' : '' }}>
                                Block Permanently
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            <!-- Info Summary -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Quick Stats</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-600">Total Staff</dt>
                        <dd class="text-sm font-bold text-gray-900">{{ \App\Models\User::where('business_id', $business->id)->count() }}</dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-600">Total Properties</dt>
                        <dd class="text-sm font-bold text-gray-900">{{ $business->branches->count() }}</dd>
                    </div>
                    <div class="pt-3 border-t border-gray-100">
                        <dt class="text-xs text-gray-400">Created At</dt>
                        <dd class="text-sm text-gray-600">{{ $business->created_at->format('F j, Y') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Back Link -->
            <div class="text-center">
                <a href="{{ route('businesses.index') }}" class="text-sm text-gray-500 hover:text-gray-900 font-medium">
                    &larr; Return to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Add Branch Modal -->
<div id="addBranchModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="hideAddBranchModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="addBranchForm" action="{{ route('branches.store') }}" method="POST">
                @csrf
                <input type="hidden" name="business_id" value="{{ $business->id }}">
                <input type="hidden" id="add_latitude" name="latitude">
                <input type="hidden" id="add_longitude" name="longitude">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">Add New Branch</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Branch Name</label>
                            <input type="text" name="name" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Location Map</label>
                            <div id="addBranchMap" class="modal-map border border-gray-200"></div>
                            <p class="text-xs text-gray-500 mt-1">Click nearby to set exact coordinates.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Physical Address</label>
                            <textarea id="add_address" name="address" rows="2" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2 px-3"></textarea>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                                <select id="add_region" name="region" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3">
                                    <option value="">Select...</option>
                                    @foreach(['Greater Accra', 'Ashanti', 'Western', 'Eastern', 'Central', 'Northern', 'Upper East', 'Upper West', 'Volta', 'Brong-Ahafo', 'Western North', 'Bono East', 'Ahafo', 'Savannah', 'North East', 'Oti'] as $region)
                                        <option value="{{ $region }}">{{ $region }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input type="text" name="contact" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Create Branch
                    </button>
                    <button type="button" onclick="hideAddBranchModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Branch Modal (Similar Structure) -->
<div id="editBranchModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="hideEditBranchModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="editBranchForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_latitude" name="latitude">
                <input type="hidden" id="edit_longitude" name="longitude">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Edit Branch</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Branch Name</label>
                            <input type="text" id="edit_name" name="name" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Location Map</label>
                            <div id="editBranchMap" class="modal-map border border-gray-200"></div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Physical Address</label>
                            <textarea id="edit_address" name="address" rows="2" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2 px-3"></textarea>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                                <select id="edit_region" name="region" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3">
                                    <option value="">Select...</option>
                                    @foreach(['Greater Accra', 'Ashanti', 'Western', 'Eastern', 'Central', 'Northern', 'Upper East', 'Upper West', 'Volta', 'Brong-Ahafo', 'Western North', 'Bono East', 'Ahafo', 'Savannah', 'North East', 'Oti'] as $region)
                                        <option value="{{ $region }}">{{ $region }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input type="text" id="edit_contact" name="contact" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Update Branch
                    </button>
                    <button type="button" onclick="hideEditBranchModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Logic reused from previous files with minor tweaks
    let addBranchMap = null, addBranchMarker = null;
    let editBranchMap = null, editBranchMarker = null;

    function initAddBranchMap() {
        if (addBranchMap) { addBranchMap.invalidateSize(); return; }
        addBranchMap = L.map('addBranchMap').setView([7.9465, -1.0232], 6);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CARTO'
        }).addTo(addBranchMap);
        addBranchMap.on('click', e => setAddBranchMarker(e.latlng.lat, e.latlng.lng));
    }

    function setAddBranchMarker(lat, lng) {
        if (addBranchMarker) addBranchMap.removeLayer(addBranchMarker);
        addBranchMarker = L.marker([lat, lng], {draggable: true}).addTo(addBranchMap);
        addBranchMarker.on('dragend', e => {
            const pos = e.target.getLatLng();
            setAddBranchMarker(pos.lat, pos.lng);
        });
        document.getElementById('add_latitude').value = lat;
        document.getElementById('add_longitude').value = lng;
        reverseGeocode(lat, lng, 'add');
    }

    function initEditBranchMap(lat, lng) {
        if (editBranchMap) editBranchMap.remove();
        editBranchMap = L.map('editBranchMap').setView([lat || 7.9465, lng || -1.0232], lat ? 13 : 6);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CARTO'
        }).addTo(editBranchMap);
        if(lat && lng) setEditBranchMarker(lat, lng);
        editBranchMap.on('click', e => setEditBranchMarker(e.latlng.lat, e.latlng.lng));
    }

    function setEditBranchMarker(lat, lng) {
        if (editBranchMarker) editBranchMap.removeLayer(editBranchMarker);
        editBranchMarker = L.marker([lat, lng], {draggable: true}).addTo(editBranchMap);
        editBranchMarker.on('dragend', e => {
            const pos = e.target.getLatLng();
            setEditBranchMarker(pos.lat, pos.lng);
        });
        document.getElementById('edit_latitude').value = lat;
        document.getElementById('edit_longitude').value = lng;
    }

    function reverseGeocode(lat, lng, prefix) {
        // Simple client-side reverse geocoding call
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(res => res.json())
            .then(data => {
                if(data.display_name) {
                    document.getElementById(prefix + '_address').value = data.display_name;
                }
            });
    }

    // Modal Controls
    function showAddBranchModal() {
        document.getElementById('addBranchModal').classList.remove('hidden');
        setTimeout(() => {
            initAddBranchMap();
            addBranchMap.invalidateSize();
        }, 200);
    }
    function hideAddBranchModal() { document.getElementById('addBranchModal').classList.add('hidden'); }
    
    function editBranch(id, name, address, contact, region, lat, lng) {
        const form = document.getElementById('editBranchForm');
        form.action = `/branches/${id}`;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_address').value = address || '';
        document.getElementById('edit_contact').value = contact || '';
        document.getElementById('edit_region').value = region || '';
        
        document.getElementById('editBranchModal').classList.remove('hidden');
        setTimeout(() => {
            initEditBranchMap(lat, lng);
            editBranchMap.invalidateSize();
        }, 200);
    }
    function hideEditBranchModal() { document.getElementById('editBranchModal').classList.add('hidden'); }

    function deleteBranch(id, name) {
        if(!confirm(`Delete branch "${name}"? This cannot be undone.`)) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/branches/${id}`;
        form.innerHTML = '@csrf @method("DELETE")';
        document.body.appendChild(form);
        form.submit();
    }
</script>
@endpush