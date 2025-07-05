<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Inventory Management System')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    @auth
        <!-- Navigation -->
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <h1 class="text-xl font-bold text-gray-800">IMS</h1>
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a href="{{ route('dashboard') }}" class="text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium hover:border-gray-300">
                                Dashboard
                            </a>
                            @if(auth()->user()->role === 'admin')
                                <a href="#" class="text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium hover:border-gray-300">
                                    Users
                                </a>
                                <a href="#" class="text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium hover:border-gray-300">
                                    Businesses
                                </a>
                            @endif
                            @if(in_array(auth()->user()->role, ['owner', 'manager']))
                                <a href="#" class="text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium hover:border-gray-300">
                                    Inventory
                                </a>
                                <a href="#" class="text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium hover:border-gray-300">
                                    Sales
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="ml-3 relative">
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-700">{{ auth()->user()->name }}</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ ucfirst(auth()->user()->role) }}
                                </span>
                                <form method="POST" action="{{ route('logout') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-900">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    @endauth

    <main class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="mb-4 text-red-600">
                    {{ session('error') }}
                </div>
            @endif
            @if(session('status'))
                <div class="mb-4 text-green-600">
                    {{ session('status') }}
                </div>
            @endif
            @yield('content')
        </div>
    </main>
</body>
</html>

