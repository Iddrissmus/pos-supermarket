<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Business</label>
        <select wire:model="business_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Select business</option>
            @foreach($businesses as $b)
                <option value="{{ $b->id }}">{{ $b->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Branch name</label>
        <input type="text" wire:model.defer="name" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Pick on map</label>
        <div id="branch-map" class="h-64 rounded border"></div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3">
            <input type="text" wire:model.defer="address" class="border border-gray-300 rounded-lg px-3 py-2" placeholder="Address (auto-filled)" />
            <input type="text" wire:model="latitude" class="border border-gray-300 rounded-lg px-3 py-2" placeholder="Latitude" />
            <input type="text" wire:model="longitude" class="border border-gray-300 rounded-lg px-3 py-2" placeholder="Longitude" />
        </div>
        <p class="text-xs text-gray-500 mt-1">Click on the map to set the location. Hold Ctrl and scroll to zoom the map.</p>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Contact</label>
        <input type="text" wire:model.defer="contact" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
    </div>
    <button type="button" wire:click="save" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        Create Branch
    </button>

    <x-notifications />
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('livewire:navigated', initMap);
document.addEventListener('DOMContentLoaded', initMap);
let branchMap, branchMarker;
function initMap() {
    const mapDiv = document.getElementById('branch-map');
    if (!mapDiv || branchMap) return;
    branchMap = L.map('branch-map').setView([5.6037, -0.1870], 12); // Accra default
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(branchMap);
    // Improve scroll UX: disable wheel zoom unless Ctrl is held
    branchMap.scrollWheelZoom.disable();
    branchMap.on('wheel', function(e) {
        if (e.originalEvent && e.originalEvent.ctrlKey) {
            branchMap.scrollWheelZoom.enable();
        } else {
            branchMap.scrollWheelZoom.disable();
        }
    });
    branchMap.on('mouseout', function() {
        branchMap.scrollWheelZoom.disable();
    });
    // Fix initial sizing issues in dynamic containers
    setTimeout(() => branchMap.invalidateSize(), 100);
    branchMap.on('click', async (e) => {
        const { lat, lng } = e.latlng;
        if (!branchMarker) {
            branchMarker = L.marker([lat, lng], { draggable: true }).addTo(branchMap);
            branchMarker.on('dragend', onDragEnd);
        } else {
            branchMarker.setLatLng([lat, lng]);
        }
        await updatePosition(lat, lng);
    });
}
async function onDragEnd(e) {
    const { lat, lng } = e.target.getLatLng();
    await updatePosition(lat, lng);
}
async function updatePosition(lat, lng) {
    @this.set('latitude', lat);
    @this.set('longitude', lng);
    try {
        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`);
        const data = await res.json();
        if (data && data.display_name) {
            @this.set('address', data.display_name);
        }
    } catch (err) {
        console.error('Reverse geocoding failed', err);
    }
}
</script>
@endpush


