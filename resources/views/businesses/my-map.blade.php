@extends('layouts.app')

@section('title', 'My Branch Locations - ' . $business->name)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<style>
    #map {
        height: calc(100vh - 120px);
        width: 100%;
        border-radius: 0.5rem;
    }
    .leaflet-popup-content {
        min-width: 250px;
    }
    .branch-info {
        font-size: 14px;
    }
    .branch-info img {
        max-width: 60px;
        max-height: 60px;
        object-fit: contain;
    }
    .branch-info h3 {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #1f2937;
    }
    .branch-info p {
        margin: 4px 0;
        color: #4b5563;
    }
    .branch-info .label {
        font-weight: 600;
        color: #6b7280;
    }
    .stats-card {
        background: white;
        border-radius: 0.5rem;
        padding: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .leaflet-tooltip {
        background-color: rgba(0, 0, 0, 0.85);
        color: white;
        border: none;
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 13px;
        font-weight: 500;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }
    .leaflet-tooltip::before {
        border-top-color: rgba(0, 0, 0, 0.85);
    }
    .tooltip-content {
        line-height: 1.6;
    }
    .tooltip-content .business-name {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 4px;
        border-bottom: 1px solid rgba(255,255,255,0.3);
        padding-bottom: 4px;
    }
    .tooltip-content .branch-detail {
        font-size: 12px;
        opacity: 0.95;
    }
</style>
@endpush

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-map-marked-alt text-purple-600 mr-2"></i>
                {{ $business->name }} - Branch Locations
            </h1>
            <p class="text-sm text-gray-600 mt-1">View all your branch locations across Ghana</p>
        </div>
        <a href="{{ route('businesses.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Back to Business
        </a>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="stats-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Branches</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $business->branches->count() }}</p>
                </div>
                <div class="bg-purple-100 rounded-full p-3">
                    <i class="fas fa-store text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="stats-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Mapped Locations</p>
                    <p class="text-2xl font-bold text-green-600">{{ count($mapData) }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-map-pin text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="stats-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Regions Covered</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $business->branches->whereNotNull('region')->unique('region')->count() }}</p>
                </div>
                <div class="bg-orange-100 rounded-full p-3">
                    <i class="fas fa-map text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Container -->
    <div class="bg-white rounded-lg shadow-lg p-4">
        <div id="map"></div>
    </div>

    <!-- Branches Without Coordinates -->
    @if(count($branchesWithoutCoordinates) > 0)
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mt-6">
        <h2 class="text-lg font-semibold text-yellow-800 mb-4">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Branches Without Location Data ({{ count($branchesWithoutCoordinates) }})
        </h2>
        <p class="text-sm text-yellow-700 mb-4">
            The following branches don't have map coordinates. They were created before the map feature was added. 
            You can edit your business to add location data.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($branchesWithoutCoordinates as $branch)
            <div class="bg-white rounded-lg p-4 border border-yellow-300">
                <h3 class="font-semibold text-gray-800">
                    <i class="fas fa-store mr-1"></i>{{ $branch['branch_name'] }}
                </h3>
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-map-marker-alt mr-1"></i>{{ $branch['region'] ?? 'No region' }}
                </p>
                <p class="text-xs text-gray-400 mt-1">{{ $branch['address'] ?? 'No address' }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
    // Map data from controller
    const mapData = @json($mapData);
    
    // Define Ghana bounds (southwest and northeast corners)
    const ghanaBounds = [
        [4.5, -3.5],  // Southwest coordinates
        [11.5, 1.5]   // Northeast coordinates
    ];
    
    // Initialize map centered on Ghana with bounds restriction
    const map = L.map('map', {
        center: [7.9465, -1.0232],
        zoom: 7,
        maxBounds: ghanaBounds,
        maxBoundsViscosity: 1.0,  // Makes bounds solid
        minZoom: 7,
        maxZoom: 19
    });
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        minZoom: 7,
        maxZoom: 19
    }).addTo(map);
    
    // Create marker cluster group
    const markers = L.markerClusterGroup({
        chunkedLoading: true,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true
    });
    
    // Use purple color for all markers (business color)
    const color = '#9333ea';
    
    // Add markers for each branch
    mapData.forEach(function(branch) {
        // Create custom icon
        const icon = L.divIcon({
            html: `<div style="background-color: ${color}; width: 32px; height: 32px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;">
                      <i class="fas fa-store" style="color: white; transform: rotate(45deg); font-size: 14px;"></i>
                   </div>`,
            className: 'custom-marker',
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });
        
        // Create marker
        const marker = L.marker([branch.latitude, branch.longitude], { icon: icon });
        
        // Create tooltip content (for hover)
        const tooltipContent = `
            <div class="tooltip-content">
                <div class="business-name">
                    <i class="fas fa-building mr-1"></i>${branch.business_name}
                </div>
                <div class="branch-detail">
                    <i class="fas fa-store mr-1"></i>${branch.branch_name}
                </div>
                <div class="branch-detail">
                    <i class="fas fa-map-marker-alt mr-1"></i>${branch.region}
                </div>
                ${branch.manager ? `<div class="branch-detail"><i class="fas fa-user-tie mr-1"></i>${branch.manager}</div>` : ''}
            </div>
        `;
        
        // Bind tooltip (shows on hover)
        marker.bindTooltip(tooltipContent, {
            direction: 'top',
            offset: [0, -20],
            opacity: 0.95
        });
        
        // Create popup content
        const popupContent = `
            <div class="branch-info">
                ${branch.business_logo ? `<div style="text-align: center; margin-bottom: 10px;"><img src="${branch.business_logo}" alt="Logo"></div>` : ''}
                <h3><i class="fas fa-building mr-2" style="color: ${color};"></i>${branch.business_name}</h3>
                <div style="border-left: 3px solid ${color}; padding-left: 10px; margin-top: 10px;">
                    <p><span class="label">Branch:</span> ${branch.branch_name}</p>
                    <p><span class="label">Region:</span> ${branch.region}</p>
                    <p><span class="label">Address:</span> ${branch.address}</p>
                    <p><span class="label">Contact:</span> ${branch.contact}</p>
                    <p><span class="label">Manager:</span> ${branch.manager}</p>
                </div>
                <div style="margin-top: 10px; text-align: center;">
                    <a href="https://www.google.com/maps?q=${branch.latitude},${branch.longitude}" 
                       target="_blank" 
                       class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-directions mr-1"></i>Get Directions
                    </a>
                </div>
            </div>
        `;
        
        marker.bindPopup(popupContent);
        markers.addLayer(marker);
    });
    
    // Add marker cluster to map
    map.addLayer(markers);
    
    // Fit map to show all markers
    if (mapData.length > 0) {
        const bounds = markers.getBounds();
        map.fitBounds(bounds, { padding: [50, 50] });
    }
    
    // Add fullscreen control
    const fullscreenBtn = L.control({ position: 'topleft' });
    fullscreenBtn.onAdd = function() {
        const btn = L.DomUtil.create('button', 'bg-white rounded shadow-lg p-2 hover:bg-gray-100');
        btn.innerHTML = '<i class="fas fa-expand text-gray-700"></i>';
        btn.title = 'Toggle Fullscreen';
        btn.onclick = function() {
            if (!document.fullscreenElement) {
                document.getElementById('map').requestFullscreen();
                btn.innerHTML = '<i class="fas fa-compress text-gray-700"></i>';
            } else {
                document.exitFullscreen();
                btn.innerHTML = '<i class="fas fa-expand text-gray-700"></i>';
            }
        };
        return btn;
    };
    fullscreenBtn.addTo(map);
</script>
@endpush
@endsection
