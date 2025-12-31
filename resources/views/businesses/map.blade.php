@extends('layouts.app')

@section('title', 'Geography')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<style>
    /* Custom Marker Cluster Styles */
    .marker-cluster-small {
        background-color: rgba(99, 102, 241, 0.6); /* Indigo-500 */
    }
    .marker-cluster-small div {
        background-color: rgba(79, 70, 229, 0.9); /* Indigo-600 */
        color: white;
    }
    .marker-cluster-medium {
        background-color: rgba(245, 158, 11, 0.6); /* Amber-500 */
    }
    .marker-cluster-medium div {
        background-color: rgba(217, 119, 6, 0.9); /* Amber-600 */
        color: white;
    }
    .leaflet-container {
        font-family: 'Inter', sans-serif;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 0.75rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        padding: 0;
        overflow: hidden;
    }
    .leaflet-popup-content {
        margin: 0;
        width: 300px !important;
    }
</style>
@endpush

@section('content')
<div class="relative h-[calc(100vh-64px)] w-full overflow-hidden">
    <!-- Map Canvas -->
    <div id="map" class="absolute inset-0 z-0"></div>

    <!-- Floating Control Panel (Top Left) -->
    <div class="absolute top-4 left-4 z-[400] w-80 bg-white/90 backdrop-blur-md shadow-lg rounded-xl border border-gray-200/50 p-5 transition-all hover:bg-white">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-lg font-bold text-gray-900 tracking-tight">Geographic Overview</h1>
                <p class="text-xs text-gray-500">Live deployment status</p>
            </div>
            <div class="h-8 w-8 bg-indigo-50 rounded-full flex items-center justify-center text-indigo-600">
                <i class="fas fa-globe"></i>
            </div>
        </div>

        <!-- Mini Stats -->
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div class="bg-white border border-gray-100 p-3 rounded-lg text-center shadow-sm">
                <span class="block text-2xl font-bold text-gray-800">{{ count($mapData) }}</span>
                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-semibold">Active Locations</span>
            </div>
            <div class="bg-white border border-gray-100 p-3 rounded-lg text-center shadow-sm">
                <span class="block text-2xl font-bold text-indigo-600">{{ \App\Models\Branch::distinct('region')->count('region') ?? 4 }}</span>
                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-semibold">Regions</span>
            </div>
        </div>

        <div class="space-y-2">
            <a href="{{ route('businesses.index') }}" class="block w-full py-2 px-3 bg-gray-900 hover:bg-gray-800 text-white text-center rounded-lg text-sm font-medium transition-colors">
                Manage Businesses
            </a>
            @if(count($branchesWithoutCoordinates) > 0)
                <button onclick="document.getElementById('missing-coords-modal').classList.remove('hidden')" class="block w-full py-2 px-3 bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 text-center rounded-lg text-xs font-medium transition-colors">
                    <i class="fas fa-exclamation-triangle mr-1"></i> {{ count($branchesWithoutCoordinates) }} locations missing data
                </button>
            @endif
        </div>
    </div>

    <!-- Legend (Bottom Right) -->
    <div class="absolute bottom-6 right-6 z-[400] bg-white/90 backdrop-blur-md shadow-lg rounded-xl border border-gray-200/50 p-4 max-h-60 overflow-y-auto w-64">
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Organizations</h3>
        <div id="legend-content" class="space-y-2">
            <!-- Populated via JS -->
        </div>
    </div>
</div>

<!-- Missing Coordinates Modal -->
<div id="missing-coords-modal" class="hidden fixed inset-0 z-[1200] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('missing-coords-modal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-amber-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Missing Location Data</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 mb-4">The following branches need to be updated with coordinates:</p>
                            <div class="bg-gray-50 rounded-md p-2 max-h-60 overflow-y-auto divide-y divide-gray-200">
                                @foreach($branchesWithoutCoordinates as $branch)
                                    <div class="py-2">
                                        <p class="text-sm font-medium text-gray-900">{{ $branch['business_name'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $branch['branch_name'] }} • {{ $branch['region'] ?? 'No Region' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="document.getElementById('missing-coords-modal').classList.add('hidden')">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
    const mapData = @json($mapData);
    
    // Initialize Map
    const map = L.map('map', {
        zoomControl: false, // Reposition later
        center: [7.9465, -1.0232],
        zoom: 7,
        minZoom: 6,
        maxZoom: 18
    });

    // Add Zoom Control to Bottom Right (above legend?) or Top Right
    L.control.zoom({ position: 'topright' }).addTo(map);

    // Carto Voyager Tiles (Clean, modern)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CARTO',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

    // Marker Clusters
    const markers = L.markerClusterGroup({
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true,
        maxClusterRadius: 50
    });

    // Color Palette
    const colors = ['#6366f1', '#ec4899', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4', '#f43f5e', '#14b8a6']; // Tailwind colors
    const businessColors = {};
    let colorIndex = 0;

    // Populate Map
    mapData.forEach(branch => {
        // Assign Color
        if (!businessColors[branch.business_name]) {
            businessColors[branch.business_name] = colors[colorIndex % colors.length];
            colorIndex++;
        }
        const color = businessColors[branch.business_name];

        // Custom Marker
        const icon = L.divIcon({
            html: `
                <div class="relative w-8 h-8 group">
                    <div style="background-color: ${color}" class="w-full h-full rounded-full border-[3px] border-white shadow-lg flex items-center justify-center transform transition-transform group-hover:scale-110">
                        <i class="fas fa-store text-white text-xs"></i>
                    </div>
                    <div style="background-color: ${color}" class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-2 h-2 rotate-45"></div>
                </div>
            `,
            className: 'bg-transparent border-0',
            iconSize: [32, 32],
            iconAnchor: [16, 36],
            popupAnchor: [0, -36]
        });

        const marker = L.marker([branch.latitude, branch.longitude], { icon: icon });

        // Modern Popup
        const popupContent = `
            <div class="bg-white">
                <div style="background-color: ${color}" class="h-2 w-full"></div>
                <div class="p-4">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Branch</span>
                        <div class="flex items-center space-x-1">
                             <div class="w-2 h-2 rounded-full bg-green-500"></div>
                             <span class="text-xs text-green-600 font-medium">Active</span>
                        </div>
                    </div>
                    <h3 class="text-gray-900 font-bold text-lg leading-tight mb-1">${branch.business_name}</h3>
                    <p class="text-gray-600 text-sm mb-4">${branch.branch_name}</p>
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex items-start text-xs text-gray-500">
                            <i class="fas fa-map-marker-alt w-4 mt-0.5 text-gray-400"></i>
                            <span class="flex-1">${branch.address}, ${branch.region}</span>
                        </div>
                        <div class="flex items-center text-xs text-gray-500">
                            <i class="fas fa-phone w-4 text-gray-400"></i>
                            <span>${branch.contact}</span>
                        </div>
                        <div class="flex items-center text-xs text-gray-500">
                            <i class="fas fa-user-tie w-4 text-gray-400"></i>
                            <span>${branch.manager ?? 'No Manager'}</span>
                        </div>
                    </div>

                    <a href="https://www.google.com/maps?q=${branch.latitude},${branch.longitude}" target="_blank" class="block w-full py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 text-center rounded-lg text-xs font-medium border border-gray-200 transition-colors">
                        Navigate
                    </a>
                </div>
            </div>
        `;

        marker.bindPopup(popupContent);
        markers.addLayer(marker);
    });

    map.addLayer(markers);

    // Fit Bounds
    if (mapData.length > 0) {
        map.fitBounds(markers.getBounds(), { padding: [50, 50] });
    }

    // Populate Legend
    const legendContainer = document.getElementById('legend-content');
    Object.keys(businessColors).forEach(name => {
        const item = document.createElement('div');
        item.className = 'flex items-center group cursor-pointer p-1 rounded hover:bg-gray-50';
        item.innerHTML = `
            <div style="background-color: ${businessColors[name]}" class="w-2.5 h-2.5 rounded-full mr-2"></div>
            <span class="text-xs text-gray-600 font-medium group-hover:text-gray-900 truncate">${name}</span>
        `;
        legendContainer.appendChild(item);
    });
</script>
@endpush
@endsection
