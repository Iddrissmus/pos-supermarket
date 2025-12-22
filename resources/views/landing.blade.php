<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Supermarket - Modern Point of Sale System</title>
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
        .hero-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-white">

    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shopping-cart text-white text-xl"></i>
                    </div>
                    <span class="text-xl font-bold gradient-text">POS Supermarket</span>
                </div>
                
                @auth
                    <!-- If user is logged in, show Dashboard link -->
                    <a href="{{ 
                        match(auth()->user()->role) {
                            'superadmin' => route('dashboard.superadmin'),
                            'business_admin' => route('dashboard.business-admin'),
                            'manager' => route('dashboard.manager'),
                            'cashier' => route('dashboard.cashier'),
                            default => '/'
                        }
                    }}" class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-2 rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-all inline-flex items-center space-x-2">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Go to Dashboard</span>
                    </a>
                @else
                    <!-- If user is NOT logged in, show Sign In dropdown -->
                    <div class="relative group">
                        <button class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-2 rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-all inline-flex items-center space-x-2">
                            <span>Sign In</span>
                            <i class="fas fa-chevron-down text-sm"></i>
                        </button>
                        <div class="hidden group-hover:block absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl py-2 z-50">
                            <a href="{{ route('login.superadmin') }}" class="block px-4 py-3 hover:bg-purple-50 transition-colors">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-crown text-purple-600"></i>
                                    <div>
                                        <div class="font-semibold text-gray-800">SuperAdmin</div>
                                        <div class="text-xs text-gray-500">System access</div>
                                    </div>
                                </div>
                            </a>
                            <a href="{{ route('login.business-admin') }}" class="block px-4 py-3 hover:bg-blue-50 transition-colors">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-briefcase text-blue-600"></i>
                                    <div>
                                        <div class="font-semibold text-gray-800">Business Admin</div>
                                        <div class="text-xs text-gray-500">Manage business</div>
                                    </div>
                                </div>
                            </a>
                            <a href="{{ route('login.manager') }}" class="block px-4 py-3 hover:bg-green-50 transition-colors">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-users-cog text-green-600"></i>
                                    <div>
                                        <div class="font-semibold text-gray-800">Manager</div>
                                        <div class="text-xs text-gray-500">Branch operations</div>
                                    </div>
                                </div>
                            </a>
                            <a href="{{ route('login.cashier') }}" class="block px-4 py-3 hover:bg-orange-50 transition-colors">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-cash-register text-orange-600"></i>
                                    <div>
                                        <div class="font-semibold text-gray-800">Cashier</div>
                                        <div class="text-xs text-gray-500">POS terminal</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-gradient text-white py-20 px-4">
        <div class="max-w-5xl mx-auto text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">
                Modern Point of Sale System
            </h1>
            <p class="text-xl text-purple-100 mb-8 max-w-3xl mx-auto">
                Manage your retail business efficiently with multi-branch support, real-time inventory tracking, and comprehensive sales analytics.
            </p>
            <div class="flex justify-center gap-4">
                <a href="{{ route('login.superadmin') }}" class="inline-block bg-white text-purple-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-all shadow-lg">
                    <i class="fas fa-crown mr-2"></i>SuperAdmin Login
                </a>
                <a href="{{ route('login.business-admin') }}" class="inline-block bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-all shadow-lg">
                    <i class="fas fa-briefcase mr-2"></i>Business Admin Login
                </a>
            </div>
            @guest
                <div class="mt-8">
                    <a href="#business-signup" class="inline-flex items-center text-sm text-purple-100 hover:text-white underline underline-offset-4">
                        <i class="fas fa-building mr-2"></i>
                        Create a new business account (request approval)
                    </a>
                </div>
            @endguest
        </div>
    </section>

    @if (session('success'))
        <div class="max-w-3xl mx-auto mt-6 px-4">
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm flex items-start space-x-3">
                <i class="fas fa-check-circle mt-0.5"></i>
                <p>{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if ($errors->has('general'))
        <div class="max-w-3xl mx-auto mt-6 px-4">
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm flex items-start space-x-3">
                <i class="fas fa-exclamation-triangle mt-0.5"></i>
                <p>{{ $errors->first('general') }}</p>
            </div>
        </div>
    @endif

    <!-- Features Section -->
    <section class="py-16 px-4 bg-gray-50">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">Key Features</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-boxes text-purple-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Inventory Management</h3>
                    <p class="text-gray-600 text-sm">Track stock levels in real-time with automatic alerts for low stock items.</p>
                </div>

                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-store-alt text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Multi-Branch Support</h3>
                    <p class="text-gray-600 text-sm">Manage multiple locations and transfer stock between branches easily.</p>
                </div>

                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-chart-bar text-green-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Sales Analytics</h3>
                    <p class="text-gray-600 text-sm">Comprehensive reports and dashboards for better business insights.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Business Signup Section -->
    @guest
    <section id="business-signup" class="py-16 px-4 bg-white border-t border-gray-100">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Request a Business Account</h2>
                <p class="text-gray-600 text-sm">
                    Fill in your business details below. A System Administrator will review your request, create your account, and send your Business Admin login credentials via SMS.
                </p>
            </div>

            <!-- Collapsible Form Button -->
            <div class="mb-4">
                <button type="button" 
                        onclick="toggleSignupForm()"
                        id="signup-form-toggle"
                        class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-4 rounded-lg font-semibold shadow-lg hover:from-purple-700 hover:to-indigo-700 transition-all flex items-center justify-between">
                    <span class="flex items-center">
                        <i class="fas fa-building mr-2"></i>
                        <span>Click to Request Business Account</span>
                    </span>
                    <i class="fas fa-chevron-down transition-transform duration-200" id="signup-form-icon"></i>
                </button>
            </div>

            <form id="business-signup-form" action="{{ route('business-signup.store') }}" method="POST" enctype="multipart/form-data" class="bg-gray-50 rounded-xl p-6 shadow-sm border border-gray-100 hidden">
                @csrf

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
                </div>

                <div class="mt-6 grid md:grid-cols-2 gap-6">
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

                    <!-- Region -->
                    <div>
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
                </div>

                <!-- Location Selection with Map -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Branch Location <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-2">
                        <i class="fas fa-info-circle"></i> Click on the map to select your branch location, or search for a location
                    </p>
                    
                    <!-- Search Box -->
                    <div class="mb-3">
                        <div class="flex gap-2">
                            <input type="text" 
                                   id="search-location" 
                                   placeholder="Search for a location in Ghana..."
                                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <button type="button" 
                                    onclick="searchLocation()"
                                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Map -->
                    <div id="map" style="height: 300px; width: 100%; border-radius: 0.5rem; border: 2px solid #e5e7eb;"></div>
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fas fa-map-marker-alt text-purple-500"></i> 
                        Selected coordinates: <span id="coords-display">Not selected</span>
                    </p>
                </div>

                <!-- Address (Auto-filled from map) -->
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

                <!-- Hidden fields for coordinates -->
                <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}">
                <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}">

                <div class="mt-6 grid md:grid-cols-2 gap-6">
                    <!-- Branch Contact -->
                    <div>
                        <label for="branch_contact" class="block text-sm font-medium text-gray-700 mb-1">
                            Branch Contact (optional)
                        </label>
                        <input type="text" id="branch_contact" name="branch_contact"
                               value="{{ old('branch_contact') }}"
                               placeholder="Phone for this branch"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 @error('branch_contact') border-red-500 @enderror">
                        @error('branch_contact')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Logo -->
                <div class="mt-6">
                    <label for="logo" class="block text-sm font-medium text-gray-700 mb-1">
                        Business Logo (optional)
                    </label>
                    <input type="file" id="logo" name="logo"
                           accept="image/*"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 @error('logo') border-red-500 @enderror">
                    <p class="text-gray-500 text-xs mt-1">Max 2MB. Supported formats: JPG, PNG, GIF.</p>
                    @error('logo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-8 flex items-center justify-between">
                    <p class="text-xs text-gray-500">
                        By submitting, you agree that a System Administrator may contact you to verify your details before activating your account.
                    </p>
                    <button type="submit"
                            class="inline-flex items-center bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold shadow hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </section>
    @endguest

    <!-- User Roles Section -->
    <section class="py-16 px-4">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">User Roles</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- SuperAdmin -->
                <div class="bg-gradient-to-br from-purple-500 to-purple-700 text-white rounded-lg p-6 text-center">
                    <i class="fas fa-crown text-4xl mb-3"></i>
                    <h3 class="text-xl font-bold mb-2">SuperAdmin</h3>
                    <p class="text-sm text-purple-100">Create businesses, assign roles & manage system-wide settings</p>
                </div>

                <!-- Business Admin -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-700 text-white rounded-lg p-6 text-center">
                    <i class="fas fa-briefcase text-4xl mb-3"></i>
                    <h3 class="text-xl font-bold mb-2">Business Admin</h3>
                    <p class="text-sm text-blue-100">Manage branches, assign managers & view business reports</p>
                </div>

                <!-- Manager -->
                <div class="bg-gradient-to-br from-green-500 to-green-700 text-white rounded-lg p-6 text-center">
                    <i class="fas fa-users-cog text-4xl mb-3"></i>
                    <h3 class="text-xl font-bold mb-2">Manager</h3>
                    <p class="text-sm text-green-100">Day-to-day operations, staff schedules & daily sales monitoring</p>
                </div>

                <!-- Cashier -->
                <div class="bg-gradient-to-br from-orange-500 to-orange-700 text-white rounded-lg p-6 text-center">
                    <i class="fas fa-cash-register text-4xl mb-3"></i>
                    <h3 class="text-xl font-bold mb-2">Cashier</h3>
                    <p class="text-sm text-orange-100">Process sales at POS terminal only</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Demo Access Section -->
    <section class="py-16 px-4 bg-gray-50">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Try the Demo</h2>
            <p class="text-gray-600 mb-8">Login with any of these demo accounts to explore the system</p>
            <div class="grid md:grid-cols-4 gap-4 text-sm">
                <a href="{{ route('login.superadmin') }}" class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-shadow cursor-pointer">
                    <i class="fas fa-crown text-purple-600 text-2xl mb-2"></i>
                    <p class="font-semibold text-gray-800">SuperAdmin</p>
                    <p class="text-gray-600 text-xs mt-1">superadmin@pos.com</p>
                    <p class="text-gray-500 text-xs">password123</p>
                    <div class="mt-3 text-purple-600 text-xs font-semibold">Click to Login →</div>
                </a>
                <a href="{{ route('login.business-admin') }}" class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-shadow cursor-pointer">
                    <i class="fas fa-briefcase text-blue-600 text-2xl mb-2"></i>
                    <p class="font-semibold text-gray-800">Business Admin</p>
                    <p class="text-gray-600 text-xs mt-1">businessadmin@pos.com</p>
                    <p class="text-gray-500 text-xs">password</p>
                    <div class="mt-3 text-blue-600 text-xs font-semibold">Click to Login →</div>
                </a>
                <a href="{{ route('login.manager') }}" class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-shadow cursor-pointer">
                    <i class="fas fa-users-cog text-green-600 text-2xl mb-2"></i>
                    <p class="font-semibold text-gray-800">Manager</p>
                    <p class="text-gray-600 text-xs mt-1">manager@pos.com</p>
                    <p class="text-gray-500 text-xs">password</p>
                    <div class="mt-3 text-green-600 text-xs font-semibold">Click to Login →</div>
                </a>
                <a href="{{ route('login.cashier') }}" class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-shadow cursor-pointer">
                    <i class="fas fa-cash-register text-orange-600 text-2xl mb-2"></i>
                    <p class="font-semibold text-gray-800">Cashier</p>
                    <p class="text-gray-600 text-xs mt-1">cashier@pos.com</p>
                    <p class="text-gray-500 text-xs">password</p>
                    <div class="mt-3 text-orange-600 text-xs font-semibold">Click to Login →</div>
                </a>
            </div>
            <p class="mt-6 text-xs text-gray-500 text-center">Click on any role card above to login directly</p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-8 px-4">
        <div class="max-w-6xl mx-auto text-center">
            <div class="flex items-center justify-center space-x-2 mb-4">
                <div class="w-8 h-8 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-white text-sm"></i>
                </div>
                <span class="text-lg font-bold text-white">POS Supermarket</span>
            </div>
            <p class="text-sm mb-4">Modern point of sale system for retail businesses</p>
            <p class="text-xs">&copy; {{ date('Y') }} POS Supermarket. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Toggle signup form
        function toggleSignupForm() {
            const form = document.getElementById('business-signup-form');
            const icon = document.getElementById('signup-form-icon');
            
            if (form.classList.contains('hidden')) {
                form.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
                // Initialize map when form is opened (wait a bit for form to be visible)
                setTimeout(function() {
                    if (typeof map === 'undefined' && document.getElementById('map')) {
                        initMap();
                    }
                }, 100);
            } else {
                form.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        }

        // Map functionality
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
            document.getElementById('coords-display').textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            
            // Reverse geocode to get address
            reverseGeocode(lat, lng);
            
            // Add drag event
            marker.on('dragend', function(e) {
                const pos = marker.getLatLng();
                document.getElementById('latitude').value = pos.lat;
                document.getElementById('longitude').value = pos.lng;
                document.getElementById('coords-display').textContent = `${pos.lat.toFixed(6)}, ${pos.lng.toFixed(6)}`;
                reverseGeocode(pos.lat, pos.lng);
            });
            
            marker.bindPopup('Selected Location').openPopup();
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
        
        // Search for location
        function searchLocation() {
            const searchQuery = document.getElementById('search-location').value;
            if (!searchQuery) {
                alert('Please enter a location to search');
                return;
            }
            
            // Add Ghana to search query for better results
            const searchQueryWithGhana = searchQuery.includes('Ghana') ? searchQuery : searchQuery + ', Ghana';
            
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQueryWithGhana)}&limit=1`, {
                headers: {
                    'User-Agent': 'POS-Supermarket-App/1.0'
                }
            })
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
                alert('Error searching for location. Please try again.');
            });
        }
        
        // Handle Enter key in search box
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-location');
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        searchLocation();
                    }
                });
            }
        });
    </script>

</body>
</html>
