@php
    $user = auth()->user();
    $roleColors = [
        'superadmin' => ['bg' => 'bg-purple-600', 'text' => 'text-purple-600', 'name' => 'SuperAdmin'],
        'business_admin' => ['bg' => 'bg-blue-600', 'text' => 'text-blue-600', 'name' => 'Business Admin'],
        'manager' => ['bg' => 'bg-green-600', 'text' => 'text-green-600', 'name' => 'Manager'],
        'cashier' => ['bg' => 'bg-orange-600', 'text' => 'text-orange-600', 'name' => 'Cashier'],
    ];
    $currentRole = $roleColors[$user->role] ?? ['bg' => 'bg-gray-600', 'text' => 'text-gray-600', 'name' => 'User'];
@endphp

<div class="top-bar flex items-center justify-between px-6 {{ $currentRole['bg'] }}">
    <div class="flex items-center space-x-4">
        <button type="button" class="text-white focus:outline-none hover:bg-white/10 p-2 rounded-lg transition-colors" @click="collapsed = !collapsed">
            <i class="fas fa-bars text-lg"></i>
        </button>
        
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-shopping-cart text-white text-lg"></i>
            </div>
            <div>
                <h1 class="text-white font-bold text-lg">POS Supermarket</h1>
                <p class="text-white/80 text-xs">{{ $currentRole['name'] }} Portal</p>
            </div>
        </div>
    </div>
    
    <div class="flex items-center space-x-6">
        <!-- Notification Bell (for Business Admin and Manager only) -->
        @if(in_array($user->role, ['business_admin', 'manager']))
            <div class="relative">
                <x-notification-bell />
            </div>
        @endif
        
        <!-- Quick Actions Dropdown -->
        <div class="relative group">
            <button class="text-white hover:bg-white/10 p-2 rounded-lg transition-colors">
                <i class="fas fa-th text-lg"></i>
            </button>
            <div class="hidden group-hover:block absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-50">
                @if($user->role === 'superadmin')
                    <a href="{{ route('businesses.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-purple-50 transition-colors">
                        <i class="fas fa-building text-purple-600 mr-2"></i> Businesses
                    </a>
                @elseif($user->role === 'business_admin')
                    <a href="{{ route('dashboard.business-admin') }}" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 transition-colors">
                        <i class="fas fa-briefcase text-blue-600 mr-2"></i> My Business
                    </a>
                @elseif($user->role === 'manager')
                    <a href="{{ route('dashboard.manager') }}" class="block px-4 py-2 text-gray-700 hover:bg-green-50 transition-colors">
                        <i class="fas fa-store text-green-600 mr-2"></i> My Branch
                    </a>
                @elseif($user->role === 'cashier')
                    <a href="{{ route('sales.terminal') }}" class="block px-4 py-2 text-gray-700 hover:bg-orange-50 transition-colors">
                        <i class="fas fa-cash-register text-orange-600 mr-2"></i> POS Terminal
                    </a>
                @endif
            </div>
        </div>
        
        <!-- User Profile Dropdown -->
        <div class="relative group">
            <button class="flex items-center space-x-3 text-white hover:bg-white/10 px-3 py-2 rounded-lg transition-colors">
                <div class="w-9 h-9 bg-white rounded-full flex items-center justify-center">
                    <span class="{{ $currentRole['text'] }} text-sm font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                </div>
                <div class="hidden md:block text-left">
                    <p class="text-sm font-semibold">{{ $user->name }}</p>
                    <p class="text-xs text-white/80">{{ $currentRole['name'] }}</p>
                </div>
                <i class="fas fa-chevron-down text-xs"></i>
            </button>
            
            <div class="hidden group-hover:block absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl py-2 z-50">
                <div class="px-4 py-3 border-b border-gray-100">
                    <p class="text-sm font-semibold text-gray-800">{{ $user->name }}</p>
                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                    <span class="inline-block mt-1 px-2 py-1 {{ $currentRole['bg'] }} text-white text-xs rounded-full">
                        {{ $currentRole['name'] }}
                    </span>
                </div>
                
                @if($user->role === 'business_admin' && $user->managedBusiness)
                    <div class="px-4 py-2 border-b border-gray-100">
                        <p class="text-xs text-gray-500">Business</p>
                        <p class="text-sm font-medium text-gray-800">{{ $user->managedBusiness->name }}</p>
                    </div>
                @endif
                
                @if(in_array($user->role, ['manager', 'cashier']) && $user->branch)
                    <div class="px-4 py-2 border-b border-gray-100">
                        <p class="text-xs text-gray-500">Branch</p>
                        <p class="text-sm font-medium text-gray-800">{{ $user->branch->name }}</p>
                    </div>
                @endif
                
                <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                    <i class="fas fa-user mr-2 text-gray-400"></i> Profile
                </a>
                <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                    <i class="fas fa-cog mr-2 text-gray-400"></i> Settings
                </a>
                
                <div class="border-t border-gray-100 mt-2 pt-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 transition-colors">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


