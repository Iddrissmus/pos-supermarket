<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashoard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @livewireStyles
    {{-- WireUI scripts registration (v2 component tag) --}}
    <wireui:scripts />
    {{-- If on older WireUI, use: @wireUiScripts --}}
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        .sidebar-item {
            transition: all 0.3s ease;
        }
        .sidebar-item:hover {
            background-color: #e0f2fe;
        }
        .sidebar-item.active {
            background-color: #e0f2fe;
            color: #1e40af;
        }
        .sidebar-item.active i {
            color: #1e40af;
        }
        .badge {
            background-color: #ef4444;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
        }
        .content-area {
            margin-left: 280px;
        }
        .sidebar {
            width: 280px;
            height: calc(100vh - 60px);
            position: fixed;
            left: 0;
            top: 60px;
            z-index: 40;
            overflow-y: auto;
        }
        .top-bar {
            height: 60px;
            background-color: #374151;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 50;
        }
        .main-content {
            margin-top: 60px;
            margin-left: 0;
            transition: margin-left 0.2s ease;
        }
        body.auth .main-content {
            margin-left: 280px;
        }
        body.collapsed .sidebar {
            width: 64px;
        }
        body.auth.collapsed .main-content {
            margin-left: 64px;
        }
    </style>
</head>
<body class="bg-gray-50 @auth auth @endauth" x-data="{ collapsed: false }" x-bind:class="{'collapsed': collapsed}">
    @auth
        <!-- Top Bar -->
        <x-top-bar />

        <!-- Sidebar -->
        <x-sidebar />
    @endauth

    <!-- Main Content Area -->
    <div class="main-content">
        @if(session('error'))
            <div class="mb-4 text-red-600 bg-red-100 border border-red-400 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif
        @if(session('status'))
            <div class="mb-4 text-green-600 bg-green-100 border border-green-400 px-4 py-3 rounded">
                {{ session('status') }}
            </div>
        @endif
        @yield('content')
    </div>
    @livewireScripts
</body>
</html>

