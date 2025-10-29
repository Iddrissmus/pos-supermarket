<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Supermarket - Modern Point of Sale System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
        </div>
    </section>

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

</body>
</html>
