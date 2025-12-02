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
    @stack('styles')
    <style>
        .sidebar {
            width: 64px;
            height: calc(100vh - 60px);
            position: fixed;
            left: 0;
            top: 60px;
            z-index: 40;
            overflow-y: auto;
            overflow-x: hidden;
            transition: width 0.3s ease;
        }
        
        .sidebar:hover {
            width: 280px;
        }
        
        .sidebar-text {
            display: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .sidebar:hover .sidebar-text {
            display: inline;
        }
        
        .sidebar-footer {
            display: none;
        }
        
        .sidebar:hover .sidebar-footer {
            display: block;
        }
        
        .sidebar-item {
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .sidebar:hover .sidebar-item {
            justify-content: flex-start;
        }
        
        .sidebar-icon {
            min-width: 20px;
            text-align: center;
        }
        
        .sidebar-item {
            transition: all 0.3s ease;
        }
        
        .sidebar-item:hover {
            background-color: #e0f2fe;
        }
        
        .sidebar-item.active {
            background-color: #dbeafe;
            border-left: 3px solid #2563eb;
        }
        
        .sidebar-item.active i {
            color: #2563eb;
        }
        
        .sidebar-item.active a,
        .sidebar-item.active span {
            color: #2563eb;
            font-weight: 600;
        }
        
        .badge {
            background-color: #ef4444;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
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
            transition: margin-left 0.3s ease;
        }
        
        body.auth .main-content {
            margin-left: 64px;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50 @auth auth @endauth">
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
        <x-notifications position="top-right"/>
        @yield('content')
    </div>
    @livewireScripts
    @stack('scripts')
</body>
</html>

