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
    {{-- If on older WireUI, use: @wireUiScripts --}}
    
    <!-- Global TomSelect CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        /* Global TomSelect Customization */
        .ts-control {
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-color: #d1d5db;
        }
        .ts-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 1px #3b82f6;
        }
        .ts-dropdown {
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            z-index: 50;
        }
        .ts-dropdown .option {
            padding: 0.5rem 1rem;
        }
        .ts-dropdown .active {
            background-color: #f3f4f6;
            color: #111827;
        }
    </style>

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
        
        /* Top bar styles moved to component */

        
        .main-content {
            margin-top: 60px;
            margin-left: 0;
            padding-left: 0;
            transition: padding-left 0.3s ease, margin-left 0.3s ease;
        }
        
        body.auth .main-content {
            margin-left: 64px;
        }
        
        /* Robust CSS-only sidebar expansion */
        body.auth .sidebar:hover ~ .main-content {
            padding-left: 216px;
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
    
    <!-- Global TomSelect JS -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selectSettings = {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                placeholder: 'Select an option...',
                plugins: ['clear_button'],
            };
            
            // Auto-init for any element with .tom-select class
            document.querySelectorAll('.tom-select').forEach((el) => {
                if (!el.tomselect) { // Prevent double init
                    new TomSelect(el, selectSettings);
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>

