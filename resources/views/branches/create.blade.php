@extends('layouts.app')

@section('title', 'Create New Branch')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: 400px;
        width: 100%;
        border-radius: 0.5rem;
        border: 2px solid #e5e7eb;
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')
<div class="p-6 max-w-3xl mx-auto">
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
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">Create New Branch</h1>
                    <p class="text-sm text-gray-600 mt-1">Add a new branch for {{ $business->name }}</p>
                </div>
                <a href="{{ route('businesses.index') }}" 
                   class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times text-xl"></i>
                </a>
            </div>
        </div>

        <!-- Form -->
        <form action="{{ route('branches.store') }}" method="POST">
            @csrf
            <input type="hidden" name="business_id" value="{{ $business->id }}">
            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">

            <!-- Branch Name -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Branch Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}"
                       required
                       placeholder="e.g., Downtown Branch, Airport Branch"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Location Selection with Map -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Branch Location <span class="text-red-500">*</span>
                </label>
                <p class="text-xs text-gray-500 mb-2">
                    <i class="fas fa-info-circle"></i> Click on the map to select your branch location
                </p>
                
                <!-- Search Box -->
                <div class="mb-3">
                    <div class="relative">
                        <input type="text" 
                               id="search" 
                               placeholder="Search for a location in Ghana..."
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="button" 
                                onclick="searchLocation()"
                                class="absolute right-2 top-2 text-blue-600 hover:text-blue-800">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <!-- Map -->
                <div id="map"></div>
                
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-map-marker-alt text-blue-500"></i> 
                    Selected coordinates: <span id="coords-display">Not selected</span>
                </p>
            </div>

            <!-- Address (Auto-filled from map) -->
            <div class="mb-4">
                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                    Address <span class="text-red-500">*</span>
                </label>
                <textarea id="address" 
                          name="address" 
                          rows="2"
                          required
                          placeholder="Address will be auto-filled when you select location on map"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
                @error('address')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Region (Auto-filled from map) -->
            <div class="mb-4">
                <label for="region" class="block text-sm font-medium text-gray-700 mb-2">
                    Region <span class="text-red-500">*</span>
                </label>
                <select id="region" 
                        name="region" 
                        required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('region') border-red-500 @enderror">
                    <option value="">Select Region</option>
                    <option value="Greater Accra" {{ old('region') == 'Greater Accra' ? 'selected' : '' }}>Greater Accra</option>
                    <option value="Ashanti" {{ old('region') == 'Ashanti' ? 'selected' : '' }}>Ashanti</option>
                    <option value="Western" {{ old('region') == 'Western' ? 'selected' : '' }}>Western</option>
                    <option value="Eastern" {{ old('region') == 'Eastern' ? 'selected' : '' }}>Eastern</option>
                    <option value="Central" {{ old('region') == 'Central' ? 'selected' : '' }}>Central</option>
                    <option value="Northern" {{ old('region') == 'Northern' ? 'selected' : '' }}>Northern</option>
                    <option value="Upper East" {{ old('region') == 'Upper East' ? 'selected' : '' }}>Upper East</option>
                    <option value="Upper West" {{ old('region') == 'Upper West' ? 'selected' : '' }}>Upper West</option>
                    <option value="Volta" {{ old('region') == 'Volta' ? 'selected' : '' }}>Volta</option>
                    <option value="Brong Ahafo" {{ old('region') == 'Brong Ahafo' ? 'selected' : '' }}>Brong Ahafo</option>
                </select>
                @error('region')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Contact -->
            <div class="mb-6">
                <label for="contact" class="block text-sm font-medium text-gray-700 mb-2">
                    Contact Number
                </label>
                <input type="text" 
                       id="contact" 
                       name="contact" 
                       value="{{ old('contact') }}"
                       placeholder="e.g., 0201234567"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('contact') border-red-500 @enderror">
                @error('contact')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <a href="{{ route('businesses.index') }}" 
                   class="text-gray-600 hover:text-gray-800 inline-flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>Cancel
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>Create Branch
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map;
let marker;

// Initialize map centered on Ghana
function initMap() {
    // Define Ghana bounds
    const ghanaBounds = [
        [4.5, -3.5],  // Southwest
        [11.5, 1.5]   // Northeast
    ];
    
    map = L.map('map', {
        center: [7.9465, -1.0232],
        zoom: 7,
        maxBounds: ghanaBounds,
        maxBoundsViscosity: 1.0,
        minZoom: 7,
        maxZoom: 19
    });
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        minZoom: 7,
        maxZoom: 19
    }).addTo(map);
    
    // Add click event to map
    map.on('click', function(e) {
        setMarker(e.latlng.lat, e.latlng.lng);
    });
}

// Set marker on map
function setMarker(lat, lng) {
    // Remove existing marker
    if (marker) {
        map.removeLayer(marker);
    }
    
    // Add new marker
    marker = L.marker([lat, lng], { draggable: true }).addTo(map);
    
    // Handle marker drag
    marker.on('dragend', function(e) {
        const position = e.target.getLatLng();
        setMarker(position.lat, position.lng);
    });
    
    // Update hidden fields
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;
    
    // Update display
    document.getElementById('coords-display').textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    
    // Reverse geocode to get address
    reverseGeocode(lat, lng);
    
    // Center map on marker
    map.setView([lat, lng], 13);
}

// Reverse geocode to get address from coordinates
function reverseGeocode(lat, lng) {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`, {
        headers: {
            'User-Agent': 'POS-Supermarket-App/1.0'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.display_name) {
            document.getElementById('address').value = data.display_name;
            
            // Try to auto-detect region
            const address = data.address || {};
            let region = address.state || address.region || '';
            
            if (region) {
                const regionSelect = document.getElementById('region');
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
function searchLocation() {
    const searchQuery = document.getElementById('search').value;
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
            setMarker(parseFloat(result.lat), parseFloat(result.lon));
        } else {
            alert('Location not found. Please try a different search term.');
        }
    })
    .catch(error => {
        console.error('Search error:', error);
        alert('Error searching for location. Please try again.');
    });
}

// Initialize map when page loads
document.addEventListener('DOMContentLoaded', function() {
    initMap();
});

// Handle Enter key in search box
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        searchLocation();
    }
});
</script>
@endpush
@endsection
