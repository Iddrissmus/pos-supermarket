@extends('layouts.app')

@section('title', 'Create New Business')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: 400px;
        width: 100%;
        border-radius: 0.5rem;
        border: 2px solid #e5e7eb;
    }
    .leaflet-popup-content {
        font-size: 14px;
    }
</style>
@endpush

@section('content')
<div class="p-6 max-w-2xl mx-auto">
    <div class="bg-white shadow rounded-lg p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Create New Business</h1>
            <p class="text-sm text-gray-600">Add a new business to the system</p>
        </div>

        <form action="{{ route('businesses.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Business Name -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Business Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                       required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Location Information -->
            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h3 class="text-sm font-semibold text-blue-900 mb-3">
                    <i class="fas fa-map-marker-alt mr-2"></i>Main Branch Location
                </h3>
                <p class="text-xs text-blue-700 mb-3">This will be created as your first branch automatically</p>
                
                <!-- Branch Name -->
                <div class="mb-3">
                    <label for="branch_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Branch Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="branch_name" 
                           name="branch_name" 
                           value="{{ old('branch_name') }}"
                           placeholder="e.g., Main Branch, Headquarters"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('branch_name') border-red-500 @enderror"
                           required>
                    @error('branch_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Map Selection -->
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select Location on Map <span class="text-red-500">*</span>
                    </label>
                    <div id="map"></div>
                    <p class="text-xs text-gray-600 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Click on the map to select your business location. You can also search for a location or drag the marker.
                    </p>
                </div>

                <!-- Search Location -->
                <div class="mb-3">
                    <label for="search-location" class="block text-sm font-medium text-gray-700 mb-2">
                        Search Location (Optional)
                    </label>
                    <div class="flex gap-2">
                        <input type="text" 
                               id="search-location" 
                               placeholder="Search for a location..."
                               class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="button" 
                                id="search-btn"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <!-- Address (Auto-filled) -->
                <div class="mb-3">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                        Address <span class="text-red-500">*</span>
                    </label>
                    <textarea id="address" 
                              name="address" 
                              rows="2"
                              placeholder="Address will be filled automatically from map"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('address') border-red-500 @enderror"
                              required>{{ old('address') }}</textarea>
                    @error('address')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Hidden fields for coordinates -->
                <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}">
                <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}">

                <!-- Region and Contact -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label for="region" class="block text-sm font-medium text-gray-700 mb-2">
                            Region <span class="text-red-500">*</span>
                        </label>
                        <select id="region" 
                                name="region" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('region') border-red-500 @enderror"
                                required>
                            <option value="">Select Region</option>
                            @foreach(['Greater Accra', 'Ashanti', 'Western', 'Eastern', 'Central', 'Northern', 'Upper East', 'Upper West', 'Volta', 'Brong-Ahafo', 'Western North', 'Bono East', 'Ahafo', 'Savannah', 'North East', 'Oti'] as $ghanaRegion)
                                <option value="{{ $ghanaRegion }}" {{ old('region') == $ghanaRegion ? 'selected' : '' }}>
                                    {{ $ghanaRegion }}
                                </option>
                            @endforeach
                        </select>
                        @error('region')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="contact" class="block text-sm font-medium text-gray-700 mb-2">
                            Contact Number <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="contact" 
                               name="contact" 
                               value="{{ old('contact') }}"
                               placeholder="e.g., 0241234567"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('contact') border-red-500 @enderror"
                               required>
                        @error('contact')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Logo Upload -->
            <div class="mb-6">
                <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">
                    Business Logo (Optional)
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

            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-sm text-green-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Note:</strong> After creating the business, you can create a Business Admin user and assign them to manage this business.
                </p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-4 border-t">
                <a href="{{ route('businesses.index') }}" 
                   class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-arrow-left mr-2"></i>Cancel
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-save mr-2"></i>Create Business
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let map, marker;
    
    // Initialize map centered on Ghana
    function initMap() {
        map = L.map('map').setView([6.6885, -1.6244], 7); // Ghana center
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);
        
        // Add click event to map
        map.on('click', function(e) {
            setMarker(e.latlng.lat, e.latlng.lng);
        });
        
        // Try to get user's location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                map.setView([lat, lng], 13);
                setMarker(lat, lng);
            });
        }
    }
    
    // Set marker and reverse geocode
    function setMarker(lat, lng) {
        // Remove existing marker
        if (marker) {
            map.removeLayer(marker);
        }
        
        // Add new marker
        marker = L.marker([lat, lng], {
            draggable: true
        }).addTo(map);
        
        // Update coordinates
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
        
        // Reverse geocode to get address
        reverseGeocode(lat, lng);
        
        // Add drag event
        marker.on('dragend', function(e) {
            const pos = marker.getLatLng();
            document.getElementById('latitude').value = pos.lat;
            document.getElementById('longitude').value = pos.lng;
            reverseGeocode(pos.lat, pos.lng);
        });
        
        marker.bindPopup('Selected Location').openPopup();
    }
    
    // Reverse geocode to get address from coordinates
    function reverseGeocode(lat, lng) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(response => response.json())
            .then(data => {
                if (data.display_name) {
                    document.getElementById('address').value = data.display_name;
                    
                    // Try to extract and match region from address components
                    if (data.address) {
                        const regionSelect = document.getElementById('region');
                        
                        // Check various address fields for Ghana regions
                        const addressFields = [
                            data.address.state,
                            data.address.region,
                            data.address.county,
                            data.address.state_district
                        ];
                        
                        // Also check the full display name
                        const searchText = (data.display_name + ' ' + addressFields.join(' ')).toLowerCase();
                        
                        // Try to match with Ghana regions
                        let matched = false;
                        for (let option of regionSelect.options) {
                            if (option.value) {
                                const regionName = option.value.toLowerCase();
                                const regionWords = regionName.split(' ');
                                
                                // Check if region name or any significant word from it appears in the address
                                if (searchText.includes(regionName) || 
                                    regionWords.some(word => word.length > 3 && searchText.includes(word))) {
                                    regionSelect.value = option.value;
                                    matched = true;
                                    break;
                                }
                            }
                        }
                        
                        // If no match found, try partial matching
                        if (!matched) {
                            for (let field of addressFields) {
                                if (field) {
                                    for (let option of regionSelect.options) {
                                        if (option.value && 
                                            (field.toLowerCase().includes(option.value.toLowerCase()) ||
                                             option.value.toLowerCase().includes(field.toLowerCase()))) {
                                            regionSelect.value = option.value;
                                            matched = true;
                                            break;
                                        }
                                    }
                                    if (matched) break;
                                }
                            }
                        }
                        
                        // Visual feedback
                        if (matched) {
                            regionSelect.classList.add('border-green-500');
                            setTimeout(() => regionSelect.classList.remove('border-green-500'), 2000);
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Reverse geocoding error:', error);
                document.getElementById('address').value = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
            });
    }
    
    // Search location
    document.getElementById('search-btn').addEventListener('click', function() {
        const query = document.getElementById('search-location').value;
        if (!query) return;
        
        // Add Ghana to search query for better results
        const searchQuery = query.includes('Ghana') ? query : query + ', Ghana';
        
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery)}`)
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    const result = data[0];
                    const lat = parseFloat(result.lat);
                    const lng = parseFloat(result.lon);
                    map.setView([lat, lng], 15);
                    setMarker(lat, lng);
                } else {
                    alert('Location not found. Please try a different search term.');
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                alert('Error searching location. Please try again.');
            });
    });
    
    // Allow search on Enter key
    document.getElementById('search-location').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('search-btn').click();
        }
    });
    
    // Initialize map when page loads
    document.addEventListener('DOMContentLoaded', initMap);
</script>
@endpush
@endsection
