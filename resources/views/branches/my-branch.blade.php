@extends('layouts.app')

@section('title', 'My Branch - ' . ($branch->name ?? 'Branch Details'))

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #editMap {
        height: 350px;
        width: 100%;
        border-radius: 0.5rem;
        border: 2px solid #f3f4f6;
    }
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1; 
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #d1d5db; 
        border-radius: 3px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #9ca3af; 
    }
</style>
@endpush

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

    @if(!$branch)
        <!-- No Branch Assigned State -->
        <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="w-20 h-20 bg-yellow-50 text-yellow-500 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">No Branch Assigned</h3>
            <p class="text-gray-500 max-w-md mx-auto">
                You are not currently assigned to any branch. Please contact your System Administrator to get assigned.
            </p>
        </div>
    @else

        <!-- Header Banner -->
        <div class="relative bg-gradient-to-r from-purple-700 to-indigo-800 rounded-xl shadow-lg overflow-hidden mb-8">
            <div class="absolute inset-0 bg-white/10" style="background-image: radial-gradient(circle at 10% 20%, rgba(255,255,255,0.1) 0%, transparent 20%), radial-gradient(circle at 90% 80%, rgba(255,255,255,0.1) 0%, transparent 20%);"></div>
            <div class="relative p-8 flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <div class="flex items-center gap-4 mb-2">
                        <span class="px-2.5 py-0.5 rounded-full bg-white/20 text-white text-xs font-semibold backdrop-blur-sm border border-white/10">
                            {{ $branch->business->name }}
                        </span>
                        @if($branch->region)
                        <span class="px-2.5 py-0.5 rounded-full bg-green-500/20 text-green-100 text-xs font-semibold backdrop-blur-sm border border-white/10 flex items-center">
                            <i class="fas fa-map-marker-alt mr-1 text-[10px]"></i> {{ $branch->region }}
                        </span>
                        @endif
                    </div>
                    <h1 class="text-3xl font-bold text-white tracking-tight flex items-center">
                        {{ $branch->name }}
                    </h1>
                    <p class="mt-2 text-purple-100 text-lg opacity-90 flex items-center">
                        <i class="fas fa-store mr-2 opacity-70"></i> Branch Dashboard
                    </p>
                </div>
                
                 <div class="flex gap-3">
                     <button onclick="showEditBranchModal()" class="px-4 py-2 bg-white text-purple-700 hover:bg-purple-50 rounded-lg transition-colors font-bold shadow-sm flex items-center">
                        <i class="fas fa-pen-to-square mr-2"></i> Edit Branch
                    </button>
                    @if(auth()->user()->role === 'business_admin')
                     <a href="{{ route('my-business') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors font-medium border border-white/20 backdrop-blur-sm">
                        <i class="fas fa-arrow-left mr-2"></i> Business Profile
                    </a>
                    @endif
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-8 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-md flex items-center justify-between shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3 text-xl"></i>
                    <p class="text-green-700 font-medium">{{ session('success') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Branches (Context) -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Business Network</p>
                    <div class="flex items-baseline gap-2">
                         <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ $branch->business->branches()->count() }}</h3>
                         <span class="text-xs text-gray-400">total branches</span>
                    </div>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                    <i class="fas fa-network-wired text-xl"></i>
                </div>
            </div>

            <!-- Total Staff -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Staff Members</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-bold text-gray-900 mt-1">
                            {{ \App\Models\User::where('branch_id', $branch->id)->count() }}
                        </h3>
                        <span class="text-xs text-gray-400">assigned here</span>
                    </div>
                </div>
                <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600">
                    <i class="fas fa-users text-xl"></i>
                </div>
            </div>

            <!-- Managers -->
             <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Managers</p>
                    <h3 class="text-3xl font-bold text-gray-900 mt-1">
                        {{ \App\Models\User::where('branch_id', $branch->id)->where('role', 'manager')->count() }}
                    </h3>
                </div>
                <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center text-green-600">
                    <i class="fas fa-user-tie text-xl"></i>
                </div>
            </div>

            <!-- Cashiers -->
             <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Cashiers</p>
                    <h3 class="text-3xl font-bold text-gray-900 mt-1">
                        {{ \App\Models\User::where('branch_id', $branch->id)->where('role', 'cashier')->count() }}
                    </h3>
                </div>
                <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center text-orange-600">
                    <i class="fas fa-cash-register text-xl"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content: Staff Lists -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Managers Section -->
                 <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center">
                            <i class="fas fa-user-tie text-green-600 mr-2"></i> Branch Managers
                        </h2>
                    </div>
                    
                    @php
                        $managers = \App\Models\User::where('branch_id', $branch->id)
                            ->where('role', 'manager')
                            ->orderBy('name')
                            ->get();
                    @endphp

                    @if($managers->isEmpty())
                        <div class="p-8 text-center bg-gray-50/50">
                            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3 text-gray-400">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <p class="text-gray-500">No managers assigned to this branch yet.</p>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach($managers as $manager)
                                <div class="p-4 hover:bg-gray-50 transition-colors flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-bold text-sm">
                                            {{ substr($manager->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-900 text-sm">{{ $manager->name }}</h3>
                                            <p class="text-xs text-gray-500">{{ $manager->email }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        @if($manager->phone)
                                            <p class="text-xs text-gray-600 flex items-center justify-end">
                                                <i class="fas fa-phone mr-1.5 opacity-50"></i> {{ $manager->phone }}
                                            </p>
                                        @else
                                            <p class="text-xs text-gray-400">No phone</p>
                                        @endif
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-green-50 text-green-700 mt-1">
                                            Manager
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Cashiers Section -->
                 <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center">
                            <i class="fas fa-cash-register text-orange-500 mr-2"></i> Branch Cashiers
                        </h2>
                         <button class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Manage Staff <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                    
                    @php
                        $cashiers = \App\Models\User::where('branch_id', $branch->id)
                            ->where('role', 'cashier')
                            ->orderBy('name')
                            ->get();
                    @endphp

                    @if($cashiers->isEmpty())
                        <div class="p-8 text-center bg-gray-50/50">
                            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3 text-gray-400">
                                <i class="fas fa-cash-register"></i>
                            </div>
                            <p class="text-gray-500">No cashiers assigned to this branch yet.</p>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100">
                             @foreach($cashiers as $cashier)
                                <div class="p-4 hover:bg-gray-50 transition-colors flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-700 flex items-center justify-center font-bold text-sm">
                                            {{ substr($cashier->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-900 text-sm">{{ $cashier->name }}</h3>
                                            <p class="text-xs text-gray-500">{{ $cashier->email }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        @if($cashier->phone)
                                            <p class="text-xs text-gray-600 flex items-center justify-end">
                                                <i class="fas fa-phone mr-1.5 opacity-50"></i> {{ $cashier->phone }}
                                            </p>
                                        @else
                                            <p class="text-xs text-gray-400">No phone</p>
                                        @endif
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-orange-50 text-orange-700 mt-1">
                                            Cashier
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

            <!-- Sidebar: Information & Contact -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-50 pb-2">
                        Branch Information
                    </h3>
                    
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gray-50 text-gray-500 flex items-center justify-center">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium uppercase">Address</p>
                                <p class="text-sm text-gray-900">{{ $branch->address ?: 'Not provided' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gray-50 text-gray-500 flex items-center justify-center">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium uppercase">Contact</p>
                                <p class="text-sm text-gray-900">{{ $branch->contact ?: 'Not provided' }}</p>
                            </div>
                        </div>

                         <div class="flex gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gray-50 text-gray-500 flex items-center justify-center">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium uppercase">Region</p>
                                <p class="text-sm text-gray-900">{{ $branch->region ?: 'Not specified' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Mini Map Preview -->
                    @if($branch->latitude && $branch->longitude)
                    <div class="mt-6 rounded-lg overflow-hidden border border-gray-200 h-32 relative group">
                        <!-- Static image fallback or actual small interactive map could be here. For simplicity, just a placeholder or link -->
                        <div class="absolute inset-0 bg-gray-100 flex items-center justify-center">
                            <a href="https://www.google.com/maps?q={{ $branch->latitude }},{{ $branch->longitude }}" target="_blank" class="text-blue-600 text-sm font-medium hover:underline flex items-center">
                                <i class="fas fa-external-link-alt mr-1"></i> View on Google Maps
                            </a>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl shadow-lg p-6 text-white">
                    <h3 class="font-bold text-lg mb-4 flex items-center">
                        <i class="fas fa-shield-alt mr-2 text-blue-400"></i> Corporate Info
                    </h3>
                    <div class="space-y-3 text-sm text-gray-300">
                        <div class="flex justify-between border-b border-white/10 pb-2">
                            <span>Business</span>
                            <span class="font-medium text-white">{{ $branch->business->name }}</span>
                        </div>
                        <div class="flex justify-between border-b border-white/10 pb-2">
                            <span>Admin</span>
                            <span class="font-medium text-white">{{ $branch->business->primaryBusinessAdmin->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Branch ID</span>
                            <span class="font-mono text-white bg-white/10 px-2 rounded-md">#{{ $branch->id }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    @endif

</div>

<!-- Edit Branch Modal -->
@if($branch)
<div id="editBranchModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
            
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-4">
                    <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title">
                        Edit Branch Details
                    </h3>
                    <button type="button" onclick="hideEditBranchModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="editBranchForm" action="{{ route('branches.update', $branch->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_latitude" name="latitude" value="{{ $branch->latitude }}">
                    <input type="hidden" id="edit_longitude" name="longitude" value="{{ $branch->longitude }}">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Branch Name</label>
                            <input type="text" name="name" value="{{ $branch->name }}" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                        </div>

                         <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Location Map</label>
                            <div class="mb-2 relative">
                                <input type="text" id="editSearch" placeholder="Search location..."
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 pr-10 text-sm">
                                <button type="button" onclick="searchEditLocation()" class="absolute right-2 top-2 text-gray-400 hover:text-purple-600">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <div id="editMap"></div>
                            <p class="text-xs text-gray-500 mt-1 flex justify-between">
                                <span><i class="fas fa-info-circle mr-1"></i> Drag marker to adjust</span>
                                <span id="edit-coords-display" class="font-mono text-gray-400">{{ $branch->latitude ? number_format($branch->latitude, 4) . ', ' . number_format($branch->longitude, 4) : 'No coords' }}</span>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <textarea id="edit_address" name="address" rows="2"
                                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">{{ $branch->address }}</textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contact</label>
                                <input type="text" name="contact" value="{{ $branch->contact }}"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                                <select id="edit_region" name="region" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                    @foreach(['Greater Accra', 'Ashanti', 'Western', 'Eastern', 'Central', 'Northern', 'Upper East', 'Upper West', 'Volta', 'Brong-Ahafo', 'Western North', 'Bono East', 'Ahafo', 'Savannah', 'North East', 'Oti'] as $region)
                                        <option value="{{ $region }}" {{ $branch->region === $region ? 'selected' : '' }}>{{ $region }}</option>
                                    @endforeach
                                    <option value="" {{ !$branch->region ? 'selected' : '' }}>Select...</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" onclick="hideEditBranchModal()"
                                class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-500">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let editMap;
let editMarker;

function showEditBranchModal() {
    document.getElementById('editBranchModal').classList.remove('hidden');
    setTimeout(() => { initEditMap(); }, 100);
}

function hideEditBranchModal() {
    document.getElementById('editBranchModal').classList.add('hidden');
    if (editMap) { editMap.remove(); editMap = null; editMarker = null; }
}

function initEditMap() {
    if (editMap) { editMap.invalidateSize(); return; }
    
    // Default to Ghana center or existing points
    const currentLat = parseFloat(document.getElementById('edit_latitude').value) || 6.6666; // Approx center
    const currentLng = parseFloat(document.getElementById('edit_longitude').value) || -1.6163;
    
    editMap = L.map('editMap', {
        center: [currentLat, currentLng],
        zoom: currentLat !== 6.6666 ? 13 : 6
    });
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(editMap);
    
    if (currentLat !== 6.6666) {
        setEditMarker(currentLat, currentLng, false);
    }
    
    editMap.on('click', function(e) {
        setEditMarker(e.latlng.lat, e.latlng.lng);
    });
}

function setEditMarker(lat, lng, reverseGeocode = true) {
    if (editMarker) editMap.removeLayer(editMarker);
    editMarker = L.marker([lat, lng], { draggable: true }).addTo(editMap);
    
    editMarker.on('dragend', function(e) {
        const pos = e.target.getLatLng();
        setEditMarker(pos.lat, pos.lng);
    });
    
    document.getElementById('edit_latitude').value = lat;
    document.getElementById('edit_longitude').value = lng;
    document.getElementById('edit-coords-display').textContent = `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
    
    editMap.setView([lat, lng], editMap.getZoom()); // Keep zoom level
    
    if (reverseGeocode) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(r => r.json())
            .then(d => {
                if (d.display_name) document.getElementById('edit_address').value = d.display_name;
            });
    }
}

function searchEditLocation() {
    const q = document.getElementById('editSearch').value;
    if (!q) return;
    
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)},Ghana&limit=1`)
        .then(r => r.json())
        .then(d => {
            if (d && d.length > 0) {
                const r = d[0];
                setEditMarker(parseFloat(r.lat), parseFloat(r.lon));
                editMap.setView([r.lat, r.lon], 13);
            } else {
                alert('Location not found');
            }
        });
}
</script>
@endif

@endsection
