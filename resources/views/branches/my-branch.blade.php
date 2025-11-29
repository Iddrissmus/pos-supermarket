@extends('layouts.app')

@section('title', 'My Branch - ' . ($branch->name ?? 'Branch Details'))

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #editMap {
        height: 350px;
        width: 100%;
        border-radius: 0.5rem;
        border: 2px solid #e5e7eb;
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="p-6 space-y-6">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if(!$branch)
        <!-- No Branch Assigned -->
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400 text-2xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-yellow-800">No Branch Assigned</h3>
                    <p class="text-sm text-yellow-700 mt-2">
                        You are not currently assigned to any branch. Please contact the System Administrator to assign you to a branch.
                    </p>
                </div>
            </div>
        </div>
    @else
        <!-- Header Card -->
        <div class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-lg shadow-lg p-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">{{ $branch->name }}</h1>
                    <p class="text-blue-100 text-sm">Business: <span class="font-semibold">{{ $branch->business->name }}</span></p>
                    @if($branch->region)
                        <p class="text-blue-100 text-sm mt-1">
                            <i class="fas fa-map-marked-alt mr-1"></i>Region: {{ $branch->region }}
                        </p>
                    @endif
                </div>
                <div class="text-right">
                    <button type="button" 
                            onclick="showEditBranchModal()"
                            class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transition-all shadow-lg inline-flex items-center">
                        <i class="fas fa-edit mr-2"></i>Edit Branch
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Branches -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Branches</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">
                            {{ $branch->business->branches()->count() }}
                        </p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-4">
                        <i class="fas fa-building text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Staff (All Branches) -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Staff</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">
                            {{ \App\Models\User::where('business_id', $branch->business_id)->whereIn('role', ['manager', 'cashier'])->count() }}
                        </p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-4">
                        <i class="fas fa-users text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Managers (All Branches) -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Managers</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">
                            {{ \App\Models\User::where('business_id', $branch->business_id)->where('role', 'manager')->count() }}
                        </p>
                    </div>
                    <div class="bg-green-100 rounded-full p-4">
                        <i class="fas fa-user-tie text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Cashiers (All Branches) -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Cashiers</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">
                            {{ \App\Models\User::where('business_id', $branch->business_id)->where('role', 'cashier')->count() }}
                        </p>
                    </div>
                    <div class="bg-orange-100 rounded-full p-4">
                        <i class="fas fa-cash-register text-orange-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Branch Details Card -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Contact Information -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-address-card text-blue-600 mr-2"></i>Branch Information
                </h2>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="bg-gray-100 rounded-full p-3 mr-4">
                            <i class="fas fa-map-marker-alt text-gray-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Address</p>
                            <p class="text-gray-800">{{ $branch->address ?: 'Not provided' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-gray-100 rounded-full p-3 mr-4">
                            <i class="fas fa-phone text-gray-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Contact Number</p>
                            <p class="text-gray-800">{{ $branch->contact ?: 'Not provided' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-gray-100 rounded-full p-3 mr-4">
                            <i class="fas fa-map-marked-alt text-gray-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Region</p>
                            <p class="text-gray-800">{{ $branch->region ?: 'Not specified' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Business Information -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-building text-green-600 mr-2"></i>Business Information
                </h2>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="bg-gray-100 rounded-full p-3 mr-4">
                            <i class="fas fa-briefcase text-gray-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Business Name</p>
                            <p class="text-gray-800 font-semibold">{{ $branch->business->name }}</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-gray-100 rounded-full p-3 mr-4">
                            <i class="fas fa-user-shield text-gray-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Business Admin</p>
                            <p class="text-gray-800">{{ $branch->business->primaryBusinessAdmin ? $branch->business->primaryBusinessAdmin->name : 'Not assigned' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-gray-100 rounded-full p-3 mr-4">
                            <i class="fas fa-boxes text-gray-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Products (This Branch)</p>
                            <p class="text-gray-800">{{ $branch->branchProducts()->count() }} items</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Managers Section (All Branches) -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-user-tie text-green-600 mr-2"></i>All Branch Managers
            </h2>
            
            @php
                $managers = \App\Models\User::where('business_id', $branch->business_id)
                    ->where('role', 'manager')
                    ->with('branch')
                    ->orderBy('name')
                    ->get();
            @endphp

            @if($managers->isEmpty())
                <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                    <i class="fas fa-user-tie text-4xl mb-3 text-gray-400"></i>
                    <p class="text-gray-500">No managers assigned yet.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($managers as $manager)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start space-x-3">
                                <div class="bg-green-100 rounded-full p-3 flex-shrink-0">
                                    <i class="fas fa-user-tie text-green-600 text-lg"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="font-semibold text-gray-800 truncate">{{ $manager->name }}</h3>
                                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full whitespace-nowrap">Manager</span>
                                    </div>
                                    <div class="space-y-1 text-sm">
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-building w-4 mr-2 flex-shrink-0"></i>
                                            <span class="truncate">{{ $manager->branch ? $manager->branch->name : 'Unassigned' }}</span>
                                        </div>
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-envelope w-4 mr-2 flex-shrink-0"></i>
                                            <span class="truncate">{{ $manager->email }}</span>
                                        </div>
                                        @if($manager->phone)
                                            <div class="flex items-center text-gray-600">
                                                <i class="fas fa-phone w-4 mr-2 flex-shrink-0"></i>
                                                <span>{{ $manager->phone }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Cashiers Section (All Branches) -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-cash-register text-orange-600 mr-2"></i>All Branch Cashiers
            </h2>
            
            @php
                $cashiers = \App\Models\User::where('business_id', $branch->business_id)
                    ->where('role', 'cashier')
                    ->with('branch')
                    ->orderBy('name')
                    ->get();
            @endphp

            @if($cashiers->isEmpty())
                <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                    <i class="fas fa-cash-register text-4xl mb-3 text-gray-400"></i>
                    <p class="text-gray-500">No cashiers assigned yet.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($cashiers as $cashier)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start space-x-3">
                                <div class="bg-orange-100 rounded-full p-3 flex-shrink-0">
                                    <i class="fas fa-cash-register text-orange-600 text-lg"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="font-semibold text-gray-800 truncate">{{ $cashier->name }}</h3>
                                        <span class="bg-orange-100 text-orange-800 text-xs font-medium px-2 py-1 rounded-full whitespace-nowrap">Cashier</span>
                                    </div>
                                    <div class="space-y-1 text-sm">
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-building w-4 mr-2 flex-shrink-0"></i>
                                            <span class="truncate">{{ $cashier->branch ? $cashier->branch->name : 'Unassigned' }}</span>
                                        </div>
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-envelope w-4 mr-2 flex-shrink-0"></i>
                                            <span class="truncate">{{ $cashier->email }}</span>
                                        </div>
                                        @if($cashier->phone)
                                            <div class="flex items-center text-gray-600">
                                                <i class="fas fa-phone w-4 mr-2 flex-shrink-0"></i>
                                                <span>{{ $cashier->phone }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>

<!-- Edit Branch Modal -->
@if($branch)
<div id="editBranchModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Edit Branch Information</h3>
        </div>
        <form id="editBranchForm" action="{{ route('branches.update', $branch->id) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_latitude" name="latitude" value="{{ $branch->latitude }}">
            <input type="hidden" id="edit_longitude" name="longitude" value="{{ $branch->longitude }}">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Branch Name *</label>
                <input type="text" name="name" value="{{ $branch->name }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Location Selection with Map -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Branch Location
                </label>
                <p class="text-xs text-gray-500 mb-2">
                    <i class="fas fa-info-circle"></i> Click on the map to update location
                </p>
                
                <!-- Search Box -->
                <div class="mb-3">
                    <div class="relative">
                        <input type="text" 
                               id="editSearch" 
                               placeholder="Search for a location in Ghana..."
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="button" 
                                onclick="searchEditLocation()"
                                class="absolute right-2 top-2 text-blue-600 hover:text-blue-800">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <!-- Map -->
                <div id="editMap"></div>
                
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-map-marker-alt text-blue-500"></i> 
                    Selected coordinates: <span id="edit-coords-display">{{ $branch->latitude && $branch->longitude ? number_format($branch->latitude, 6) . ', ' . number_format($branch->longitude, 6) : 'Not selected' }}</span>
                </p>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                <textarea id="edit_address" name="address" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $branch->address }}</textarea>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Contact</label>
                <input type="text" name="contact" value="{{ $branch->contact }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Region</label>
                <select id="edit_region" name="region" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Region</option>
                    <option value="Greater Accra" {{ $branch->region === 'Greater Accra' ? 'selected' : '' }}>Greater Accra</option>
                    <option value="Ashanti" {{ $branch->region === 'Ashanti' ? 'selected' : '' }}>Ashanti</option>
                    <option value="Western" {{ $branch->region === 'Western' ? 'selected' : '' }}>Western</option>
                    <option value="Eastern" {{ $branch->region === 'Eastern' ? 'selected' : '' }}>Eastern</option>
                    <option value="Central" {{ $branch->region === 'Central' ? 'selected' : '' }}>Central</option>
                    <option value="Northern" {{ $branch->region === 'Northern' ? 'selected' : '' }}>Northern</option>
                    <option value="Upper East" {{ $branch->region === 'Upper East' ? 'selected' : '' }}>Upper East</option>
                    <option value="Upper West" {{ $branch->region === 'Upper West' ? 'selected' : '' }}>Upper West</option>
                    <option value="Volta" {{ $branch->region === 'Volta' ? 'selected' : '' }}>Volta</option>
                    <option value="Brong-Ahafo" {{ $branch->region === 'Brong-Ahafo' ? 'selected' : '' }}>Brong-Ahafo</option>
                    <option value="Western North" {{ $branch->region === 'Western North' ? 'selected' : '' }}>Western North</option>
                    <option value="Bono East" {{ $branch->region === 'Bono East' ? 'selected' : '' }}>Bono East</option>
                    <option value="Ahafo" {{ $branch->region === 'Ahafo' ? 'selected' : '' }}>Ahafo</option>
                    <option value="Savannah" {{ $branch->region === 'Savannah' ? 'selected' : '' }}>Savannah</option>
                    <option value="North East" {{ $branch->region === 'North East' ? 'selected' : '' }}>North East</option>
                    <option value="Oti" {{ $branch->region === 'Oti' ? 'selected' : '' }}>Oti</option>
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

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let editMap;
let editMarker;

function showEditBranchModal() {
    document.getElementById('editBranchModal').classList.remove('hidden');
    
    // Initialize map after modal is visible
    setTimeout(() => {
        initEditMap();
    }, 100);
}

function hideEditBranchModal() {
    document.getElementById('editBranchModal').classList.add('hidden');
    
    // Clean up map
    if (editMap) {
        editMap.remove();
        editMap = null;
        editMarker = null;
    }
}

// Initialize map centered on Ghana
function initEditMap() {
    // Don't reinitialize if map already exists
    if (editMap) {
        editMap.invalidateSize();
        return;
    }
    
    // Define Ghana bounds
    const ghanaBounds = [
        [4.5, -3.5],  // Southwest
        [11.5, 1.5]   // Northeast
    ];
    
    const currentLat = parseFloat(document.getElementById('edit_latitude').value) || 7.9465;
    const currentLng = parseFloat(document.getElementById('edit_longitude').value) || -1.0232;
    
    editMap = L.map('editMap', {
        center: [currentLat, currentLng],
        zoom: currentLat !== 7.9465 ? 13 : 7,
        maxBounds: ghanaBounds,
        maxBoundsViscosity: 1.0,
        minZoom: 7,
        maxZoom: 19
    });
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        minZoom: 7,
        maxZoom: 19
    }).addTo(editMap);
    
    // Add existing marker if coordinates exist
    if (currentLat !== 7.9465 && currentLng !== -1.0232) {
        setEditMarker(currentLat, currentLng, false);
    }
    
    // Add click event to map
    editMap.on('click', function(e) {
        setEditMarker(e.latlng.lat, e.latlng.lng);
    });
}

// Set marker on map
function setEditMarker(lat, lng, reverseGeocode = true) {
    // Remove existing marker
    if (editMarker) {
        editMap.removeLayer(editMarker);
    }
    
    // Add new marker
    editMarker = L.marker([lat, lng], { draggable: true }).addTo(editMap);
    
    // Handle marker drag
    editMarker.on('dragend', function(e) {
        const position = e.target.getLatLng();
        setEditMarker(position.lat, position.lng);
    });
    
    // Update hidden fields
    document.getElementById('edit_latitude').value = lat;
    document.getElementById('edit_longitude').value = lng;
    
    // Update display
    document.getElementById('edit-coords-display').textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    
    // Reverse geocode to get address
    if (reverseGeocode) {
        reverseGeocodeEdit(lat, lng);
    }
    
    // Center map on marker
    editMap.setView([lat, lng], 13);
}

// Reverse geocode to get address from coordinates
function reverseGeocodeEdit(lat, lng) {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`, {
        headers: {
            'User-Agent': 'POS-Supermarket-App/1.0'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.display_name) {
            document.getElementById('edit_address').value = data.display_name;
            
            // Try to auto-detect region
            const address = data.address || {};
            let region = address.state || address.region || '';
            
            if (region) {
                const regionSelect = document.getElementById('edit_region');
                for (let option of regionSelect.options) {
                    if (option.text.toLowerCase().includes(region.toLowerCase()) || 
                        region.toLowerCase().includes(option.text.toLowerCase())) {
                        regionSelect.value = option.value;
                        break;
                    }
                }
            }
        }
    })
    .catch(error => console.error('Reverse geocoding error:', error));
}

// Search for location
function searchEditLocation() {
    const searchQuery = document.getElementById('editSearch').value;
    if (!searchQuery) {
        alert('Please enter a location to search');
        return;
    }
    
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery)},Ghana&limit=1`, {
        headers: {
            'User-Agent': 'POS-Supermarket-App/1.0'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.length > 0) {
            const result = data[0];
            setEditMarker(parseFloat(result.lat), parseFloat(result.lon));
        } else {
            alert('Location not found. Please try a different search term.');
        }
    })
    .catch(error => {
        console.error('Search error:', error);
        alert('Error searching for location. Please try again.');
    });
}

// Handle Enter key in search box
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('editSearch');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchEditLocation();
            }
        });
    }
});
</script>
@endif
@endsection
