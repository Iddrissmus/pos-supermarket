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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <style>
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Swiper Custom Styles */
        .swiper {
            width: 100%;
            height: 600px;
        }
        
        .swiper-slide {
            position: relative;
            overflow: hidden;
        }
        
        .slide-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            z-index: 1;
        }
        
        .slide-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Dark overlay */
            z-index: 2;
        }
        
        .slide-content {
            position: relative;
            z-index: 3;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            padding: 0 20px;
        }
        
        /* Text Animations */
        .swiper-slide-active .animate-title {
            animation: fadeInUp 0.8s ease-out forwards;
            opacity: 0;
            transform: translateY(30px);
        }
        
        .swiper-slide-active .animate-subtitle {
            animation: fadeInUp 0.8s ease-out 0.3s forwards;
            opacity: 0;
            transform: translateY(30px);
        }
        
        .swiper-slide-active .animate-btn {
            animation: fadeInUp 0.8s ease-out 0.6s forwards;
            opacity: 0;
            transform: translateY(30px);
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login.business-admin') }}" class="text-gray-600 hover:text-purple-600 font-medium transition-colors">
                            Login
                        </a>
                        <a href="{{ route('business.register') }}" class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-2 rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-all shadow-md">
                            Get Started
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Slider -->
    <div class="swiper mySwiper">
        <div class="swiper-wrapper">
            <!-- Slide 1 -->
            <div class="swiper-slide">
                <div class="slide-bg" style="background-image: url('https://images.unsplash.com/photo-1556742049-0cfed4f7a07d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');"></div>
                <div class="slide-overlay bg-gradient-to-r from-black/70 to-transparent"></div>
                <div class="slide-content max-w-7xl mx-auto px-4 !items-start !text-left">
                    <h1 class="text-4xl md:text-6xl font-bold mb-4 animate-title leading-tight">
                        Revolutionize Your<br>Retail Business
                    </h1>
                    <p class="text-xl md:text-2xl text-gray-200 mb-8 max-w-2xl animate-subtitle">
                        Experience the future of Point of Sale systems. Multi-branch management, real-time analytics, and seamless inventory control.
                    </p>
                    <div class="flex gap-4 animate-btn">
                        <a href="{{ route('business.register') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-full font-bold transition-all transform hover:scale-105 shadow-lg">
                            Get Started
                        </a>
                        <a href="#features" class="bg-transparent border-2 border-white text-white px-8 py-3 rounded-full font-bold hover:bg-white hover:text-indigo-900 transition-all">
                            Learn More
                        </a>
                    </div>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="swiper-slide">
                <div class="slide-bg" style="background-image: url('https://images.unsplash.com/photo-1556740758-90de374c12ad?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');"></div>
                <div class="slide-overlay bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
                <div class="slide-content">
                    <h1 class="text-4xl md:text-6xl font-bold mb-4 animate-title text-center">
                        Powerful Analytics
                    </h1>
                    <p class="text-xl md:text-2xl text-gray-200 mb-8 max-w-2xl text-center animate-subtitle">
                        Turn data into decisions with our comprehensive dashboard and reporting tools.
                    </p>
                     <div class="flex gap-4 animate-btn justify-center">
                        <a href="{{ route('login.business-admin') }}" class="bg-white text-indigo-900 px-8 py-3 rounded-full font-bold transition-all transform hover:scale-105 shadow-lg">
                            Business Login
                        </a>
                    </div>
                </div>
            </div>
            
             <!-- Slide 3 -->
            <div class="swiper-slide">
                <div class="slide-bg" style="background-image: url('https://images.unsplash.com/photo-1542838132-92c53300491e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');"></div>
                <div class="slide-overlay bg-indigo-900/60"></div>
                <div class="slide-content max-w-7xl mx-auto px-4 !items-end !text-right">
                    <h1 class="text-4xl md:text-6xl font-bold mb-4 animate-title">
                        Seamless & Secure
                    </h1>
                    <p class="text-xl md:text-2xl text-gray-200 mb-8 max-w-2xl animate-subtitle ml-auto">
                         Built with enterprise-grade security to protect your transactions and customer data.
                    </p>
                    <div class="animate-btn ml-auto">
                         <a href="{{ route('login.cashier') }}" class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-3 rounded-full font-bold transition-all shadow-lg">
                            <i class="fas fa-cash-register"></i> Cashier Terminal
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="swiper-button-next text-white"></div>
        <div class="swiper-button-prev text-white"></div>
        <div class="swiper-pagination"></div>
    </div>

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

<!-- Pricing, Signup, and Roles sections removed for customer-centric view -->

    <!-- Demo Access Section -->
    <section class="py-16 px-4 bg-gray-50">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Try the Demo</h2>
            <p class="text-gray-600 mb-8">Login with any of these demo accounts to explore the system</p>
            <div class="grid md:grid-cols-3 gap-4 text-sm">

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
        // Initialize Swiper
        var swiper = new Swiper(".mySwiper", {
            spaceBetween: 0,
            centeredSlides: true,
            effect: "fade", // Fade effect for cleaner transitions
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
        });
    </script>

</body>
</html>
