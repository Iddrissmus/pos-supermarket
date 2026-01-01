<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start Your Business - POS Supermarket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ url('/') }}" class="flex items-center space-x-2">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shopping-cart text-white text-xl"></i>
                    </div>
                    <span class="text-xl font-bold gradient-text">POS Supermarket</span>
                </a>
                
                <a href="{{ route('login') }}" class="text-gray-600 hover:text-purple-600 font-medium">
                    Already have an account? Login
                </a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Start Your Journey</h1>
            <p class="text-xl text-gray-600">Choose a plan to register your business</p>
        </div>

        @if (session('success'))
            <div class="max-w-3xl mx-auto mb-8">
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm flex items-start space-x-3">
                    <i class="fas fa-check-circle mt-0.5"></i>
                    <p>{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if ($errors->has('general'))
            <div class="max-w-3xl mx-auto mb-8">
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm flex items-start space-x-3">
                    <i class="fas fa-exclamation-triangle mt-0.5"></i>
                    <p>{{ $errors->first('general') }}</p>
                </div>
            </div>
        @endif

        <!-- Pricing Section -->
        <div id="pricing-section" class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto mb-16">
            <!-- Starter Plan -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 flex flex-col hover:shadow-md transition-shadow cursor-pointer ring-2 ring-transparent hover:ring-purple-200" onclick="selectPlan('starter')">
                <h3 class="text-xl font-bold text-gray-800">Starter</h3>
                <p class="text-gray-500 text-sm mt-1">Perfect for single shops</p>
                <div class="my-6">
                    <span class="text-4xl font-bold text-gray-900">GHS {{ config('plans.starter.price') }}</span>
                    <span class="text-gray-500">/mo</span>
                </div>
                <ul class="space-y-3 mb-8 flex-1">
                    <li class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i> {{ config('plans.starter.max_branches') }} Branch Limit
                    </li>
                    <li class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i> Basic Reporting
                    </li>
                    <li class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i> Standard Support
                    </li>
                </ul>
                <button class="w-full py-3 bg-gray-50 hover:bg-gray-100 text-gray-800 font-semibold rounded-xl transition-colors border border-gray-200 plan-btn" data-plan="starter">
                    Select Starter
                </button>
            </div>

            <!-- Growth Plan -->
            <div class="bg-white rounded-2xl shadow-xl border border-purple-100 p-8 flex flex-col relative transform md:-translate-y-2 cursor-pointer ring-2 ring-transparent hover:ring-purple-400" onclick="selectPlan('growth')">
                <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-purple-600 text-white px-4 py-1 rounded-full text-xs font-bold uppercase">
                    Most Popular
                </div>
                <h3 class="text-xl font-bold text-gray-800">Growth</h3>
                <p class="text-gray-500 text-sm mt-1">For expanding businesses</p>
                <div class="my-6">
                    <span class="text-4xl font-bold text-gray-900">GHS {{ config('plans.growth.price') }}</span>
                    <span class="text-gray-500">/mo</span>
                </div>
                <ul class="space-y-3 mb-8 flex-1">
                    <li class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i> Up to {{ config('plans.growth.max_branches') }} Branches
                    </li>
                    <li class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i> Advanced Analytics
                    </li>
                    <li class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i> Stock Transfers
                    </li>
                </ul>
                <button class="w-full py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-xl transition-colors shadow-lg plan-btn" data-plan="growth">
                    Select Growth
                </button>
            </div>

            <!-- Enterprise Plan -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 flex flex-col hover:shadow-md transition-shadow cursor-pointer ring-2 ring-transparent hover:ring-purple-200" onclick="selectPlan('enterprise')">
                <h3 class="text-xl font-bold text-gray-800">Enterprise</h3>
                <p class="text-gray-500 text-sm mt-1">For large chains</p>
                <div class="my-6">
                    <span class="text-4xl font-bold text-gray-900">GHS {{ config('plans.enterprise.price') }}</span>
                    <span class="text-gray-500">/mo</span>
                </div>
                <ul class="space-y-3 mb-8 flex-1">
                    <li class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i> Unlimited Branches
                    </li>
                    <li class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i> Dedicated Account Manager
                    </li>
                    <li class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i> API Access
                    </li>
                </ul>
                <button class="w-full py-3 bg-gray-50 hover:bg-gray-100 text-gray-800 font-semibold rounded-xl transition-colors border border-gray-200 plan-btn" data-plan="enterprise">
                    Select Enterprise
                </button>
            </div>
        </div>

        <!-- Registration Form (Hidden initially) -->
        <div id="registration-container" class="max-w-4xl mx-auto hidden transition-all duration-500 ease-in-out opacity-0 translate-y-10">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
                <div class="border-b border-gray-100 pb-6 mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Complete Registration</h2>
                    <p class="text-gray-500 mt-1">Setup your business account for the <span id="selected-plan-name" class="font-bold text-purple-600 uppercase">Starter</span> plan.</p>
                </div>

                <form id="business-signup-form" action="{{ route('business-signup.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="plan_type" name="plan_type" value="starter">

                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Business Name -->
                        <div class="md:col-span-2">
                            <label for="business_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Business Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="business_name" name="business_name"
                                   value="{{ old('business_name') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 @error('business_name') border-red-500 @enderror"
                                   required>
                            @error('business_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Owner Name -->
                        <div>
                            <label for="owner_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Owner / Contact Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="owner_name" name="owner_name"
                                   value="{{ old('owner_name') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 @error('owner_name') border-red-500 @enderror"
                                   required>
                            @error('owner_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Owner Email -->
                        <div>
                            <label for="owner_email" class="block text-sm font-medium text-gray-700 mb-1">
                                Owner Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="owner_email" name="owner_email"
                                   value="{{ old('owner_email') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 @error('owner_email') border-red-500 @enderror"
                                   required>
                            @error('owner_email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Owner Phone -->
                        <div>
                            <label for="owner_phone" class="block text-sm font-medium text-gray-700 mb-1">
                                Owner Phone (for SMS) <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="owner_phone" name="owner_phone"
                                   value="{{ old('owner_phone') }}"
                                   placeholder="e.g., 0241234567"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 @error('owner_phone') border-red-500 @enderror"
                                   required>
                            @error('owner_phone')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Branch Name -->
                        <div>
                            <label for="branch_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Main Branch Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="branch_name" name="branch_name"
                                   value="{{ old('branch_name') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 @error('branch_name') border-red-500 @enderror"
                                   required>
                            @error('branch_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="region" class="block text-sm font-medium text-gray-700 mb-1">
                            Region <span class="text-red-500">*</span>
                        </label>
                        <select id="region" name="region"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 @error('region') border-red-500 @enderror"
                                required>
                            <option value="">Select Region</option>
                            @foreach(['Greater Accra', 'Ashanti', 'Western', 'Eastern', 'Central', 'Northern', 'Upper East', 'Upper West', 'Volta', 'Brong-Ahafo', 'Western North', 'Bono East', 'Ahafo', 'Savannah', 'North East', 'Oti'] as $ghanaRegion)
                                <option value="{{ $ghanaRegion }}" {{ old('region') == $ghanaRegion ? 'selected' : '' }}>
                                    {{ $ghanaRegion }}
                                </option>
                            @endforeach
                        </select>
                         @error('region')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Location Selection with Map -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Branch Location (Click on map) <span class="text-red-500">*</span>
                        </label>
                        
                        <div class="mb-3 flex gap-2">
                            <input type="text" id="search-location" placeholder="Search for a location..." class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <button type="button" onclick="searchLocation()" class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm">Search</button>
                        </div>

                        <div id="map" style="height: 300px; width: 100%; border-radius: 0.5rem; border: 2px solid #e5e7eb;"></div>
                        <p class="text-xs text-gray-500 mt-2">
                            Selected: <span id="coords-display">None</span>
                        </p>
                        <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}">
                        <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}">
                    </div>

                    <!-- Address -->
                    <div class="mt-6">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                            Branch Address <span class="text-red-500">*</span>
                        </label>
                        <textarea id="address" name="address" rows="2"
                                  placeholder="Address will be filled automatically from map"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 @error('address') border-red-500 @enderror"
                                  required>{{ old('address') }}</textarea>
                         @error('address')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Logo -->
                    <div class="mt-6">
                         <label for="logo" class="block text-sm font-medium text-gray-700 mb-1">
                            Business Logo (optional)
                        </label>
                        <input type="file" id="logo" name="logo" accept="image/*" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 flex items-center justify-between">
                        <p class="text-xs text-gray-500 max-w-xs">
                            Secure payment processing by Paystack. Your account will be created immediately after payment.
                        </p>
                        <button type="submit" class="inline-flex items-center bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-8 py-3 rounded-lg text-sm font-bold shadow hover:from-purple-700 hover:to-indigo-700 transition-all transform hover:scale-105">
                            Proceed to Payment
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Select Plan Function
        function selectPlan(plan) {
            // Update hidden input
            document.getElementById('plan_type').value = plan;
            document.getElementById('selected-plan-name').textContent = plan;
            
            // Visual feedback
            document.querySelectorAll('.plan-btn').forEach(btn => {
                if(btn.dataset.plan === plan) {
                    btn.classList.remove('bg-gray-50', 'text-gray-800');
                    btn.classList.add('bg-purple-600', 'text-white');
                    btn.innerHTML = 'Selected';
                } else {
                    // Reset others (simplified logic, ideally restore original classes based on plan)
                    if(btn.dataset.plan !== 'growth') {
                         btn.classList.add('bg-gray-50', 'text-gray-800');
                         btn.classList.remove('bg-purple-600', 'text-white');
                         btn.innerHTML = 'Select ' + btn.dataset.plan.charAt(0).toUpperCase() + btn.dataset.plan.slice(1);
                    }
                }
            });

            // Show form
            const formContainer = document.getElementById('registration-container');
            formContainer.classList.remove('hidden');
            // Small delay to allow display:block to apply before opacity transition
            setTimeout(() => {
                formContainer.classList.remove('opacity-0', 'translate-y-10');
            }, 10);

            // Scroll to form
            formContainer.scrollIntoView({ behavior: 'smooth' });

            // Initialize map if needed
            if (typeof map === 'undefined') {
                initMap();
            }
        }

        // Map Functionality
        let map, marker;
        function initMap() {
            const center = [6.6885, -1.6244]; // Ghana center
            map = L.map('map').setView(center, 7);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);
            
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

        function setMarker(lat, lng) {
            if (marker) map.removeLayer(marker);
            marker = L.marker([lat, lng], {draggable: true}).addTo(map);
            
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            document.getElementById('coords-display').textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            
            reverseGeocode(lat, lng);
            
            marker.on('dragend', function(e) {
                const pos = marker.getLatLng();
                setMarker(pos.lat, pos.lng); // Update inputs/address without recreating marker logic recursively loop
                // Actually simplified: just update inputs
                document.getElementById('latitude').value = pos.lat;
                document.getElementById('longitude').value = pos.lng;
                document.getElementById('coords-display').textContent = `${pos.lat.toFixed(6)}, ${pos.lng.toFixed(6)}`;
                reverseGeocode(pos.lat, pos.lng);
            });
        }

        function reverseGeocode(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(res => res.json())
                .then(data => {
                    if(data.display_name) {
                        document.getElementById('address').value = data.display_name;
                    }
                });
        }
        
        function searchLocation() {
            const query = document.getElementById('search-location').value;
            if(!query) return;
            
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=gh`)
                .then(res => res.json())
                .then(data => {
                    if(data && data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lon = parseFloat(data[0].lon);
                        map.setView([lat, lon], 13);
                        setMarker(lat, lon);
                    }
                });
        }
    </script>
</body>
</html>
