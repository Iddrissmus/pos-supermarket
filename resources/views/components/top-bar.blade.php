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

<div class="top-bar flex items-center justify-between px-6 bg-white border-b border-gray-200 h-[60px] z-[1001] fixed w-full top-0 start-0" x-data="{ quickActionsOpen: false, profileOpen: false }">
    <div class="flex items-center gap-x-8">
        <!-- Logo Area -->
        <div class="flex items-center space-x-3 w-[240px]">
            <div class="w-8 h-8 rounded-lg {{ $currentRole['bg'] }} flex items-center justify-center shadow-sm">
                <i class="fas fa-shopping-cart text-white text-sm"></i>
            </div>
            <div>
                <h1 class="font-bold text-lg text-slate-800 leading-tight tracking-tight">ShaaGo</h1>
                <p class="text-slate-500 text-[10px] uppercase font-bold tracking-wider">{{ $currentRole['name'] }}</p>
            </div>
        </div>

        <!-- Search Bar (Visual) -->
        <div class="hidden md:flex items-center relative w-96">
            <i class="fas fa-search absolute left-3 text-gray-400 text-sm"></i>
            <input type="text" placeholder="Search for products, orders, or customers..." 
                   class="w-full pl-10 pr-4 py-2 bg-gray-50 border-none rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all shadow-sm text-gray-600 placeholder-gray-400">
            <div class="absolute right-2 flex items-center space-x-1">
                <span class="text-xs text-gray-400 px-1.5 py-0.5 border border-gray-200 rounded bg-white">Ctrl</span>
                <span class="text-xs text-gray-400 px-1.5 py-0.5 border border-gray-200 rounded bg-white">K</span>
            </div>
        </div>
    </div>
    
    <div class="flex items-center space-x-4">
        <!-- Branch Requests Bell (for SuperAdmin only) -->
        @if($user->role === 'superadmin')
            @php
                $pendingRequestsCount = \App\Models\BranchRequest::where('status', 'pending')->count();
            @endphp
            <div class="relative">
                <a href="{{ route('superadmin.branch-requests.index', ['status' => 'pending']) }}" 
                   class="text-gray-500 hover:bg-gray-100 hover:text-purple-600 p-2 rounded-full transition-all block relative"
                   title="Pending Branch Requests">
                    <i class="fas fa-clipboard-list text-lg"></i>
                    @if($pendingRequestsCount > 0)
                        <span class="absolute top-0 right-0 bg-red-500 text-white text-[10px] rounded-full h-4 w-4 flex items-center justify-center font-bold shadow-sm">
                            {{ $pendingRequestsCount }}
                        </span>
                    @endif
                </a>
            </div>
        @endif
        
        <!-- Notification Bell (for SuperAdmin, Business Admin and Manager) -->
        @if(in_array($user->role, ['superadmin', 'business_admin', 'manager']))
            <div class="relative">
                <x-notification-bell class="text-gray-700 hover:text-indigo-600 hover:bg-gray-100 p-2 rounded-full transition-all" />
            </div>
        @endif
        
        <!-- Quick Actions Dropdown -->
        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <button @click="open = !open" class="text-gray-500 hover:bg-gray-100 hover:text-indigo-600 p-2 rounded-full transition-all">
                <i class="fas fa-th text-lg"></i>
            </button>
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50 overflow-hidden"
                 style="display: none;">
                 <div class="px-4 py-2 bg-gray-50 border-b border-gray-100">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Quick Shortcuts</span>
                 </div>
                @if($user->role === 'superadmin')
                    <a href="{{ route('businesses.index') }}" class="flex items-center px-4 py-3 text-slate-700 hover:bg-purple-50 hover:text-purple-700 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center mr-3 text-purple-600">
                             <i class="fas fa-building"></i>
                        </div>
                        <span class="font-medium text-sm">All Businesses</span>
                    </a>
                @elseif($user->role === 'business_admin')
                    <a href="{{ route('dashboard.business-admin') }}" class="flex items-center px-4 py-3 text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center mr-3 text-blue-600">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <span class="font-medium text-sm">My Business</span>
                    </a>
                @elseif($user->role === 'manager')
                    <a href="{{ route('dashboard.manager') }}" class="flex items-center px-4 py-3 text-slate-700 hover:bg-green-50 hover:text-green-700 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center mr-3 text-green-600">
                            <i class="fas fa-store"></i>
                        </div>
                        <span class="font-medium text-sm">My Branch</span>
                    </a>
                @elseif($user->role === 'cashier')
                    <a href="{{ route('sales.terminal') }}" class="flex items-center px-4 py-3 text-slate-700 hover:bg-orange-50 hover:text-orange-700 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center mr-3 text-orange-600">
                            <i class="fas fa-cash-register"></i>
                        </div>
                        <span class="font-medium text-sm">POS Terminal</span>
                    </a>
                @endif
            </div>
        </div>
        
        <div class="h-8 w-px bg-gray-200 mx-2"></div>

        <!-- User Profile Dropdown -->
        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <button @click="open = !open" class="flex items-center space-x-3 text-slate-700 hover:bg-gray-50 px-2 py-1.5 rounded-full border border-transparent hover:border-gray-200 transition-all">
                <div class="w-8 h-8 {{ $currentRole['bg'] }} rounded-full flex items-center justify-center text-white shadow-sm ring-2 ring-white">
                    <span class="text-xs font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                </div>
                <div class="hidden md:block text-left mr-1">
                    <p class="text-xs font-bold leading-none">{{ $user->name }}</p>
                </div>
                <i class="fas fa-chevron-down text-[10px] text-gray-400"></i>
            </button>
            
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-60 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50"
                 style="display: none;">
                <div class="px-5 py-3 border-b border-gray-50 bg-gray-50/50">
                    <p class="text-sm font-bold text-gray-800">{{ $user->name }}</p>
                    <p class="text-xs text-gray-500 mb-2 truncate">{{ $user->email }}</p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium {{ str_replace('bg-', 'bg-', $currentRole['bg']) }} bg-opacity-10 {{ $currentRole['text'] }}">
                        {{ $currentRole['name'] }}
                    </span>
                </div>
                
                <div class="py-1">
                    @if($user->role === 'business_admin' && $user->managedBusiness)
                        <div class="px-4 py-2">
                            <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Business</p>
                            <p class="text-xs font-medium text-gray-700">{{ $user->managedBusiness->name }}</p>
                        </div>
                    @endif
                    
                    @if(in_array($user->role, ['manager', 'cashier']) && $user->branch)
                        <div class="px-4 py-2">
                            <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Branch</p>
                            <p class="text-xs font-medium text-gray-700">{{ $user->branch->name }}</p>
                        </div>
                    @endif
                </div>

                <div class="border-t border-gray-100 py-1">
                    <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-user-circle w-5 text-gray-400"></i> Profile
                    </a>
                    <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-cog w-5 text-gray-400"></i> Settings
                    </a>
                </div>
                
                <div class="border-t border-gray-100 mt-1 py-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left flex items-center px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 transition-colors">
                            <i class="fas fa-sign-out-alt w-5"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


