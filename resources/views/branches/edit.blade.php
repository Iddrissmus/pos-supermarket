@extends('layouts.app')

@section('title', 'Edit Branch')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* Ensure map stays below header but works well in layout */
    #map {
        height: 100%;
        min-height: 500px;
        width: 100%;
        border-radius: 0.75rem;
        z-index: 1; /* Lower z-index context for the map container */
    }
</style>
@endpush

@section('content')
<div class="p-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Edit Branch</h1>
            <p class="text-gray-500 mt-1">Update location and details for <span class="font-semibold text-gray-700">{{ $branch->name }}</span></p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('branches.index') }}" 
               class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors shadow-sm font-medium">
                Cancel
            </a>
            <button form="edit-branch-form" type="submit" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm font-medium flex items-center">
                <i class="fas fa-save mr-2"></i> Save Changes
            </button>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid lg:grid-cols-3 gap-8">
        <!-- User Feedback -->
        <div class="lg:col-span-3">
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded shadow-sm mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded shadow-sm mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Left Column: Map & Location -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden h-full flex flex-col">
                <div class="p-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-map-marked-alt text-blue-500 mr-2"></i> Branch Location
                    </h3>
                    <span class="text-xs text-gray-500 bg-white px-2 py-1 rounded border border-gray-200">
                        Drag marker to update
                    </span>
                </div>
                
                <div class="relative flex-1 bg-gray-100 p-4">
                    <!-- Search Overlay -->
                    <div class="absolute top-6 left-6 right-16 z-[400] max-w-sm">
                        <div class="relative shadow-lg">
                            <input type="text" 
                                   id="search" 
                                   placeholder="Search location in Ghana..."
                                   class="w-full pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-sm">
                            <i class="fas fa-search absolute left-3.5 top-3.5 text-gray-400"></i>
                            <button onclick="searchLocation()" class="absolute right-2 top-2 p-1.5 bg-gray-100 hover:bg-gray-200 rounded text-gray-600 transition-colors">
                                <i class="fas fa-arrow-right text-xs"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div id="map" class="shadow-inner border border-gray-300"></div>
                </div>
                
                <div class="p-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between text-sm">
                   <div class="text-gray-600">
                        <i class="fas fa-compass mr-1"></i> Coordinates:
                        <span id="coords-display" class="font-mono bg-white px-2 py-0.5 rounded border border-gray-200 ml-1">
                            {{ $branch->latitude && $branch->longitude ? number_format($branch->latitude, 6) . ', ' . number_format($branch->longitude, 6) : 'Not selected' }}
                        </span>
                   </div>
                   <div class="text-blue-600 text-xs font-medium">
                        * Address auto-fills on selection
                   </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Details Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 sticky top-24">
                <div class="p-5 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Branch Details</h3>
                </div>
                
                <div class="p-5">
                    <form id="edit-branch-form" action="{{ route('branches.update', $branch->id) }}" method="POST" class="space-y-5">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $branch->latitude) }}">
                        <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $branch->longitude) }}">

                        <!-- Branch Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Branch Name <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name', $branch->name) }}" required
                                   class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                   placeholder="e.g., Head Office">
                            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Address -->
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address <span class="text-red-500">*</span></label>
                            <textarea id="address" name="address" rows="3" required
                                      class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm resize-none"
                                      placeholder="Selected from map...">{{ old('address', $branch->address) }}</textarea>
                            @error('address')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Region -->
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Region <span class="text-red-500">*</span></label>
                            <select id="location" name="region" required
                                    class="tom-select w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                <option value="">Select Region</option>
                                @foreach(['Greater Accra', 'Ashanti', 'Western', 'Eastern', 'Central', 'Northern', 'Upper East', 'Upper West', 'Volta', 'Brong Ahafo', 'Western North', 'Bono East', 'Ahafo', 'Savannah', 'North East', 'Oti'] as $region)
                                    <option value="{{ $region }}" {{ old('region', $branch->region) == $region ? 'selected' : '' }}>{{ $region }}</option>
                                @endforeach
                            </select>
                            @error('region')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Contact -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-phone text-gray-400 text-xs"></i>
                                </div>
                                <input type="text" id="phone" name="contact" value="{{ old('contact', $branch->contact) }}"
                                       class="w-full pl-8 border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                       placeholder="e.g., 024XXXXXXX">
                            </div>
                            @error('contact')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map;
let marker;

// Initialize map centered on Ghana or existing coordinates
function initMap() {
    const ghanaBounds = [[4.5, -3.5], [11.5, 1.5]];
    let center = [7.9465, -1.0232];
    let initialZoom = 7;
    
    // Use existing coordinates if available
    const existingLat = {{ $branch->latitude ?? 'null' }};
    const existingLng = {{ $branch->longitude ?? 'null' }};
    
    if (existingLat && existingLng) {
        center = [existingLat, existingLng];
        initialZoom = 15;
    }
    
    map = L.map('map', {
        center: center,
        zoom: initialZoom,
        maxBounds: ghanaBounds,
        maxBoundsViscosity: 1.0,
        minZoom: 6,
        maxZoom: 19,
        zoomControl: false // Move zoom control if needed, or keep default
    });
    
    // Add zoom control top-right to avoid search bar
    L.control.zoom({
        position: 'bottomright'
    }).addTo(map);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        minZoom: 6,
        maxZoom: 19
    }).addTo(map);
    
    // Add existing marker
    if (existingLat && existingLng) {
        setMarker(existingLat, existingLng);
    }
    
    // Add click event to map
    map.on('click', function(e) {
        setMarker(e.latlng.lat, e.latlng.lng);
        // Also reverse geocode on click
        reverseGeocode(e.latlng.lat, e.latlng.lng);
    });
}

// Function to update fields and marker
function setMarker(lat, lng) {
    if (marker) map.removeLayer(marker);
    marker = L.marker([lat, lng], { draggable: true }).addTo(map);
    
    marker.on('dragend', function(e) {
        const pos = e.target.getLatLng();
        setMarker(pos.lat, pos.lng);
        reverseGeocode(pos.lat, pos.lng);
    });
    
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;
    document.getElementById('coords-display').textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    
    map.setView([lat, lng], map.getZoom());
}

// Reverse geocode
function reverseGeocode(lat, lng) {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`, {
        headers: { 'User-Agent': 'POS-Supermarket-App/1.0' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.display_name) {
            document.getElementById('address').value = data.display_name;
            
            // Auto-detect region
            const address = data.address || {};
            let region = address.state || address.region || '';
            const regionSelect = document.getElementById('location');
            
            if (region && regionSelect) {
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

// Search location
function searchLocation() {
    const searchQuery = document.getElementById('search').value;
    if (!searchQuery) return;
    
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery)},Ghana&limit=1`, {
        headers: { 'User-Agent': 'POS-Supermarket-App/1.0' }
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.length > 0) {
            const result = data[0];
            const lat = parseFloat(result.lat);
            const lng = parseFloat(result.lon);
            map.setView([lat, lng], 15);
            setMarker(lat, lng);
            reverseGeocode(lat, lng);
        } else {
            alert('Location not found in Ghana.');
        }
    })
    .catch(error => console.error('Search error:', error));
}

// Init
document.addEventListener('DOMContentLoaded', function() {
    initMap();
});

// Enter key for search
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        searchLocation();
    }
});
</script>
@endpush
@endsection
