@extends('layouts.app')

@section('title', 'My Branch Locations - ' . $business->name)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<style>
    #map {
        height: 600px; /* Fixed height for consistency */
        width: 100%;
        border-radius: 0.75rem;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 0.75rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .leaflet-popup-content {
        margin: 0;
        min-width: 280px;
    }
    .custom-tooltip {
        background: rgba(17, 24, 39, 0.9);
        border: none;
        color: white;
        border-radius: 0.5rem;
        padding: 8px 12px;
        font-weight: 500;
        font-size: 0.875rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .custom-tooltip::before {
        border-top-color: rgba(17, 24, 39, 0.9);
    }
</style>
@endpush

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    
    <!-- Header Banner -->
    <div class="relative bg-gradient-to-r from-purple-700 to-indigo-800 rounded-xl shadow-lg overflow-hidden mb-8">
        <div class="absolute inset-0 bg-white/10" style="background-image: radial-gradient(circle at 10% 20%, rgba(255,255,255,0.1) 0%, transparent 20%), radial-gradient(circle at 90% 80%, rgba(255,255,255,0.1) 0%, transparent 20%);"></div>
        <div class="relative p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight flex items-center">
                    <i class="fas fa-map-location-dot mr-4 text-purple-200"></i>Branch Network
                </h1>
                <p class="mt-2 text-purple-100 text-lg opacity-90">
                    Geographic distribution of your {{ $business->name }} branches
                </p>
            </div>
            
             <div class="flex gap-3">
                 <a href="{{ route('my-business') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors font-medium border border-white/20">
                    <i class="fas fa-arrow-left mr-2"></i> Business Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Branches -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Branches</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ $business->branches->count() }}</h3>
                    <span class="text-sm text-gray-500">locations</span>
                </div>
            </div>
            <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600">
                <i class="fas fa-store text-xl"></i>
            </div>
        </div>

        <!-- Mapped Locations -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Mapped Locations</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ count($mapData) }}</h3>
                    <span class="text-sm text-green-600 font-medium">
                        ({{ $business->branches->count() > 0 ? round((count($mapData) / $business->branches->count()) * 100) : 0 }}% coverage)
                    </span>
                </div>
            </div>
            <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center text-green-600">
                <i class="fas fa-map-pin text-xl"></i>
            </div>
        </div>

        <!-- Regions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Regions Covered</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ $business->branches->whereNotNull('region')->unique('region')->count() }}</h3>
                    <span class="text-sm text-gray-500">regions</span>
                </div>
            </div>
            <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center text-orange-600">
                <i class="fas fa-globe-africa text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Map Container -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-1 mb-8">
        <div id="map" class="z-0"></div>
    </div>

    <!-- Unmapped Branches Alert -->
    @if(count($branchesWithoutCoordinates) > 0)
    <div class="bg-orange-50 border-l-4 border-orange-400 p-6 rounded-r-xl shadow-sm">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-triangle-exclamation text-orange-400 text-xl mt-0.5"></i>
            </div>
            <div class="ml-4 w-full">
                <h3 class="text-lg font-semibold text-orange-800">
                    {{ count($branchesWithoutCoordinates) }} Branches Missing Coordinates
                </h3>
                <p class="text-orange-700 mt-1">
                    These branches were moved or created before map data was required. Update their location settings to display them on the map.
                </p>
                
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($branchesWithoutCoordinates as $branch)
                    <div class="bg-white/60 p-3 rounded-lg border border-orange-200 flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-800">{{ $branch['branch_name'] }}</p>
                            <p class="text-xs text-gray-500">{{ $branch['region'] ?? 'No region' }}</p>
                        </div>
                        <a href="{{ route('branches.edit', $branch['id']) }}" class="text-sm font-medium text-orange-600 hover:text-orange-800">
                            Edit <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Map data from controller
        const mapData = @json($mapData);
        
        // Define Ghana bounds
        const ghanaBounds = [
            [4.5, -3.5],  // Southwest
            [11.5, 1.5]   // Northeast
        ];
        
        // Initialize map
        const map = L.map('map', {
            center: [7.9465, -1.0232],
            zoom: 7,
            maxBounds: ghanaBounds,
            maxBoundsViscosity: 1.0,
            zoomControl: false // We'll add it to a better position
        });

        // Custom zoom control in bottom right
        L.control.zoom({
            position: 'bottomright'
        }).addTo(map);
        
        // Elegant dark/light map style possibility, keeping Standard OSM for now looking clean
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            minZoom: 6
        }).addTo(map);
        
        // Custom cluster icons
        const markers = L.markerClusterGroup({
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true,
            iconCreateFunction: function (cluster) {
                const childCount = cluster.getChildCount();
                let c = ' marker-cluster-';
                if (childCount < 10) {
                    c += 'small';
                } else if (childCount < 100) {
                    c += 'medium';
                } else {
                    c += 'large';
                }

                return new L.DivIcon({ 
                    html: `<div class="w-10 h-10 rounded-full bg-purple-600 text-white flex items-center justify-center font-bold shadow-lg border-2 border-white text-sm">${childCount}</div>`, 
                    className: 'custom-cluster-icon', 
                    iconSize: new L.Point(40, 40) 
                });
            }
        });
        
        // Add markers
        const businessColor = '#7e22ce'; // purple-700
        
        mapData.forEach(function(branch) {
            // Modern marker pin
            const icon = L.divIcon({
                html: `
                    <div class="relative group">
                        <div class="w-8 h-8 rounded-full bg-purple-600 border-2 border-white shadow-lg flex items-center justify-center transform transition-transform hover:scale-110">
                            <i class="fas fa-store text-white text-xs"></i>
                        </div>
                        <div class="w-2 h-2 bg-purple-600 rotate-45 absolute -bottom-1 left-3 border-r border-b border-white"></div>
                    </div>
                `,
                className: 'bg-transparent border-0',
                iconSize: [32, 40],
                iconAnchor: [16, 40],
                popupAnchor: [0, -40]
            });
            
            const marker = L.marker([branch.latitude, branch.longitude], { icon: icon });
            
            // Modern Popup Content
            const popupContent = `
                <div class="font-sans">
                    <div class="bg-gray-50 p-3 rounded-t-lg border-b border-gray-100 flex items-center gap-3">
                         ${branch.business_logo ? `<img src="${branch.business_logo}" class="w-8 h-8 rounded bg-white object-contain border border-gray-200">` : 
                                                `<div class="w-8 h-8 rounded bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-xs">${branch.business_name.substring(0,2).toUpperCase()}</div>`}
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm leading-tight">${branch.branch_name}</h3>
                            <p class="text-xs text-gray-500">${branch.region}</p>
                        </div>
                    </div>
                    <div class="p-3 space-y-2">
                        <div class="flex items-start gap-2 text-xs text-gray-600">
                            <i class="fas fa-location-dot mt-0.5 text-gray-400 w-4"></i>
                            <span>${branch.address}</span>
                        </div>
                         <div class="flex items-center gap-2 text-xs text-gray-600">
                            <i class="fas fa-phone mt-0.5 text-gray-400 w-4"></i>
                            <span>${branch.contact}</span>
                        </div>
                         <div class="flex items-center gap-2 text-xs text-gray-600">
                            <i class="fas fa-user-tie mt-0.5 text-gray-400 w-4"></i>
                            <span>${branch.manager}</span>
                        </div>
                        <div class="pt-2 mt-2 border-t border-gray-100">
                            <a href="https://www.google.com/maps?q=${branch.latitude},${branch.longitude}" 
                               target="_blank"
                               class="block w-full text-center py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded text-xs font-semibold transition-colors">
                                Get Directions
                            </a>
                        </div>
                    </div>
                </div>
            `;
            
            marker.bindPopup(popupContent, { maxWidth: 280, minWidth: 260 });
            
            // Tooltip
            marker.bindTooltip(`
                <div class="flex flex-col">
                    <span class="font-bold">${branch.branch_name}</span>
                    <span class="text-gray-300 text-xs">${branch.region}</span>
                </div>
            `, {
                className: 'custom-tooltip',
                direction: 'top',
                offset: [0, -45]
            });
            
            markers.addLayer(marker);
        });
        
        map.addLayer(markers);
        
        // Fit bounds if markers exist
        if (mapData.length > 0) {
            map.fitBounds(markers.getBounds(), { padding: [50, 50] });
        }
    });
</script>
@endpush

