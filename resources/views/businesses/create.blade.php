@extends('layouts.app')

@section('title', 'Onboard Organization')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: 400px;
        width: 100%;
        border-radius: 0.75rem;
        z-index: 0; /* Ensure map sits behind other elements */
    }
    .leaflet-popup-content {
        font-family: inherit;
        font-size: 13px;
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Onboard New Organization</h1>
        <p class="text-sm text-gray-500 mt-1">Configure the business entity and initial branch location.</p>
    </div>

    <form action="{{ route('businesses.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Main Card -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
            <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                <h2 class="text-base font-semibold text-gray-800">Business Identity</h2>
                <p class="text-xs text-gray-500">Basic information about the organization.</p>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Business Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Organization Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}"
                           placeholder="e.g. Acme Supermarkets Ltd."
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base py-3 px-4 @error('name') border-red-500 @enderror"
                           required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Logo Upload -->
                <div>
                    <label for="logo" class="block text-sm font-medium text-gray-700 mb-1">Business Logo</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-indigo-400 transition-colors bg-gray-50">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label for="logo" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                    <span>Upload a file</span>
                                    <input id="logo" name="logo" type="file" class="sr-only" accept="image/*">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                        </div>
                    </div>
                    @error('logo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Location Card -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <div>
                    <h2 class="text-base font-semibold text-gray-800">Headquarters / Main Branch</h2>
                    <p class="text-xs text-gray-500">This will be set as the primary operating location.</p>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                    Required
                </span>
            </div>

            <div class="p-6 space-y-6">
                <!-- Branch Name -->
                <div>
                    <label for="branch_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Location Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="branch_name" 
                           name="branch_name" 
                           value="{{ old('branch_name', 'Main Office') }}"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base py-3 px-4"
                           required>
                </div>

                <!-- Map Section -->
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700">Geographic Location</label>
                    <div class="relative rounded-xl overflow-hidden shadow-sm border border-gray-200">
                        <div class="absolute top-2 left-2 z-[400] bg-white rounded-md shadow-md p-1 min-w-[200px]">
                            <div class="flex">
                                <input type="text" id="search-location" placeholder="Search location..." class="border-0 text-xs w-full focus:ring-0 rounded-l-md py-2">
                                <button type="button" id="search-btn" class="px-2 text-indigo-600 hover:text-indigo-800 py-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </button>
                            </div>
                        </div>
                        <div id="map" class="h-64 sm:h-96 w-full"></div>
                    </div>
                </div>

                <!-- Address Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                            Physical Address <span class="text-red-500">*</span>
                        </label>
                        <textarea id="address" 
                                  name="address" 
                                  rows="2"
                                  class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base py-3 px-4 bg-gray-50"
                                  placeholder="Select on map to auto-fill..."
                                  readonly
                                  required>{{ old('address') }}</textarea>
                    </div>

                    <div>
                        <label for="region" class="block text-sm font-medium text-gray-700 mb-1">
                            Region <span class="text-red-500">*</span>
                        </label>
                        <select id="region" 
                                name="region" 
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base py-3 px-4"
                                required>
                            <option value="">Select Region...</option>
                            @foreach(['Greater Accra', 'Ashanti', 'Western', 'Eastern', 'Central', 'Northern', 'Upper East', 'Upper West', 'Volta', 'Brong-Ahafo', 'Western North', 'Bono East', 'Ahafo', 'Savannah', 'North East', 'Oti'] as $ghanaRegion)
                                <option value="{{ $ghanaRegion }}" {{ old('region') == $ghanaRegion ? 'selected' : '' }}>
                                    {{ $ghanaRegion }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="contact" class="block text-sm font-medium text-gray-700 mb-1">
                            Contact Phone <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="contact" 
                               name="contact" 
                               value="{{ old('contact') }}"
                               placeholder="05X XXX XXXX"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base py-3 px-4"
                               required>
                    </div>
                </div>

                <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}">
                <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}">
            </div>
        </div>

        <!-- Action Bar -->
        <div class="mt-8 flex items-center justify-end space-x-4">
            <a href="{{ route('businesses.index') }}" 
               class="px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cancel
            </a>
            <button type="submit" 
                    class="px-8 py-3 border border-transparent shadow-sm text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Create Business
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let map, marker;
    
    // Initialize map centered on Ghana
    function initMap() {
        map = L.map('map', { alignControl: false }).setView([6.6885, -1.6244], 7); 
        
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            maxZoom: 19
        }).addTo(map);
        
        map.on('click', function(e) {
            setMarker(e.latlng.lat, e.latlng.lng);
        });
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                map.setView([lat, lng], 13);
                setMarker(lat, lng);
            });
        }
    }
    
    function setMarker(lat, lng) {
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        
        updateCoords(lat, lng);
        reverseGeocode(lat, lng);
        
        marker.on('dragend', function(e) {
            const pos = marker.getLatLng();
            updateCoords(pos.lat, pos.lng);
            reverseGeocode(pos.lat, pos.lng);
        });
    }

    function updateCoords(lat, lng) {
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
    }
    
    // Using Nominatim for reverse geocoding
    function reverseGeocode(lat, lng) {
        // Debounce or visual feedback could be added here
        const addressInput = document.getElementById('address');
        addressInput.classList.add('opacity-50');
        
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(res => res.json())
            .then(data => {
                addressInput.classList.remove('opacity-50');
                if (data.display_name) {
                    addressInput.value = data.display_name;
                    autoSelectRegion(data);
                }
            })
            .catch(() => {
                addressInput.classList.remove('opacity-50');
                addressInput.value = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
            });
    }

    function autoSelectRegion(data) {
        const regionSelect = document.getElementById('region');
        if (!data.address) return;
        
        // Simple heuristic for Ghana regions
        const possibleRegions = [data.address.state, data.address.region, data.address.county].filter(Boolean);
        const searchText = possibleRegions.join(' ').toLowerCase();
        
        for (let option of regionSelect.options) {
            if (option.value && searchText.includes(option.value.toLowerCase())) {
                regionSelect.value = option.value;
                break;
            }
        }
    }
    
    // Search functionality
    document.getElementById('search-btn').addEventListener('click', performSearch);
    document.getElementById('search-location').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') { e.preventDefault(); performSearch(); }
    });

    function performSearch() {
        const query = document.getElementById('search-location').value;
        if (!query) return;
        
        const searchQuery = query.includes('Ghana') ? query : query + ', Ghana';
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery)}`)
            .then(res => res.json())
            .then(data => {
                if (data.length > 0) {
                    const { lat, lon } = data[0];
                    map.setView([lat, lon], 15);
                    setMarker(lat, lon);
                } else {
                    alert('Location not found.');
                }
            });
    }
    
    document.addEventListener('DOMContentLoaded', initMap);
</script>
@endpush
@endsection
