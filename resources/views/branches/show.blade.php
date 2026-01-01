@extends('layouts.app')

@section('title', 'Branch Details')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: 100%;
        min-height: 500px;
        width: 100%;
        border-radius: 0.75rem;
        z-index: 1;
    }
</style>
@endpush

@section('content')
<div class="p-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ $branch->name }}</h1>
            <p class="text-gray-500 mt-1">
                Branch Details for <span class="font-semibold text-gray-700">{{ $branch->business->name }}</span>
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('branches.index') }}" 
               class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors shadow-sm font-medium">
                <i class="fas fa-arrow-left mr-2"></i> Back to List
            </a>
            <a href="{{ route('branches.edit', $branch->id) }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm font-medium flex items-center">
                <i class="fas fa-edit mr-2"></i> Edit Branch
            </a>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid lg:grid-cols-3 gap-8">
        <!-- User Feedback (Flash messages typically shown in layout, but good to have context here if needed) -->
        
        <!-- Left Column: Map & Location -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden h-full flex flex-col">
                <div class="p-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-map-marked-alt text-blue-500 mr-2"></i> Location
                    </h3>
                </div>
                
                <div class="relative flex-1 bg-gray-100 p-4">
                    <div id="map" class="shadow-inner border border-gray-300"></div>
                </div>
                
                <div class="p-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between text-sm">
                   <div class="text-gray-600">
                        <i class="fas fa-compass mr-1"></i> Coordinates:
                        <span class="font-mono bg-white px-2 py-0.5 rounded border border-gray-200 ml-1">
                            {{ $branch->latitude && $branch->longitude ? number_format($branch->latitude, 6) . ', ' . number_format($branch->longitude, 6) : 'Not set' }}
                        </span>
                   </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Details -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 sticky top-24">
                <div class="p-5 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Branch Information</h3>
                </div>
                
                <div class="p-5 space-y-6">
                    <!-- Status Badge -->
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $branch->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($branch->status ?? 'Active') }}
                        </span>
                    </div>

                    <!-- Address -->
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Address</h4>
                        <p class="text-sm text-gray-700 leading-relaxed">
                            {{ $branch->address ?? 'No address provided' }}
                        </p>
                    </div>

                    <!-- Region -->
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Region</h4>
                        <p class="text-sm text-gray-700">
                            {{ $branch->region ?? 'Not specified' }}
                        </p>
                    </div>

                    <!-- Contact -->
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Contact</h4>
                        <p class="text-sm text-gray-700 flex items-center">
                            <i class="fas fa-phone-alt text-gray-400 mr-2 text-xs"></i>
                            {{ $branch->contact ?? 'No contact number' }}
                        </p>
                    </div>

                    <!-- Manager -->
                    <div class="pt-4 border-t border-gray-100">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Manager</h4>
                        @if($branch->manager)
                            <div class="flex items-center">
                                <div class="bg-green-100 text-green-600 rounded-full h-8 w-8 flex items-center justify-center text-xs font-bold mr-3">
                                    {{ substr($branch->manager->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $branch->manager->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $branch->manager->email }}</p>
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-gray-500 italic">No manager assigned</p>
                            <a href="{{ route('admin.staff.index') }}" class="text-xs text-blue-600 hover:underline mt-1 inline-block">Assign Staff</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initMap();
});

function initMap() {
    const ghanaBounds = [[4.5, -3.5], [11.5, 1.5]];
    // Default center
    let center = [7.9465, -1.0232]; 
    let initialZoom = 7;
    
    // Existing coords
    const lat = {{ $branch->latitude ?? 'null' }};
    const lng = {{ $branch->longitude ?? 'null' }};
    
    if (lat && lng) {
        center = [lat, lng];
        initialZoom = 15;
    }
    
    const map = L.map('map', {
        center: center,
        zoom: initialZoom,
        maxBounds: ghanaBounds,
        maxBoundsViscosity: 1.0,
        zoomControl: true 
    });
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    if (lat && lng) {
        L.marker([lat, lng]).addTo(map)
         .bindPopup("<b>{{ $branch->name }}</b><br>{{ Str::limit($branch->address, 30) }}")
         .openPopup();
    }
}
</script>
@endpush
@endsection
