@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="p-6">
    <!-- Success/Info Messages -->
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-800 rounded-lg flex items-center">
            <i class="fas fa-check-circle mr-3"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    
    @if(session('info'))
        <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-800 rounded-lg flex items-center">
            <i class="fas fa-info-circle mr-3"></i>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Notifications</h1>
            @if($notifications->where('read_at', null)->count() > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-check-double mr-2"></i>Mark All as Read
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-white rounded-lg shadow-md">
        @forelse($notifications as $notification)
            <div class="border-b border-gray-200 last:border-b-0 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50' }}">
                <div class="p-6">
                    <div class="flex items-start space-x-4">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center bg-{{ $notification->data['color'] ?? 'blue' }}-100">
                                <i class="fas {{ $notification->data['icon'] ?? 'fa-bell' }} text-{{ $notification->data['color'] ?? 'blue' }}-600 text-lg"></i>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ $notification->data['title'] ?? 'Notification' }}
                                        @if($notification->data['urgency'] ?? '' === 'critical')
                                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                                Critical
                                            </span>
                                        @endif
                                        @if(!$notification->read_at)
                                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                New
                                            </span>
                                        @endif
                                    </h3>
                                    <p class="mt-1 text-gray-600">{{ $notification->data['message'] ?? '' }}</p>

                                    <!-- Additional Details for Low Stock Notifications -->
                                    @if($notification->data['type'] === 'low_stock')
                                        <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                                            <div class="bg-gray-50 p-3 rounded">
                                                <span class="font-medium text-gray-700">Product:</span>
                                                <span class="text-gray-900">{{ $notification->data['product_name'] }}</span>
                                            </div>
                                            <div class="bg-gray-50 p-3 rounded">
                                                <span class="font-medium text-gray-700">Branch:</span>
                                                <span class="text-gray-900">{{ $notification->data['branch_name'] }}</span>
                                            </div>
                                            <div class="bg-gray-50 p-3 rounded">
                                                <span class="font-medium text-gray-700">Current Stock:</span>
                                                <span class="text-red-600 font-semibold">{{ $notification->data['current_stock'] }} units</span>
                                            </div>
                                            <div class="bg-gray-50 p-3 rounded">
                                                <span class="font-medium text-gray-700">Reorder Level:</span>
                                                <span class="text-gray-900">{{ $notification->data['reorder_level'] }} units</span>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <a href="{{ route('layouts.assign') }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                                <i class="fas fa-plus-circle mr-1"></i>Assign Stock Now
                                            </a>
                                        </div>
                                    @endif

                                    <p class="mt-2 text-sm text-gray-500">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>

                                <!-- Actions -->
                                <div class="flex space-x-2 ml-4">
                                    @if(!$notification->read_at)
                                        <form action="{{ route('notifications.mark-read', $notification->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-blue-600 hover:text-blue-800" title="Mark as read">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" onsubmit="return confirm('Delete this notification?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-12 text-center">
                <i class="fas fa-bell-slash text-gray-400 text-5xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No notifications</h3>
                <p class="text-gray-500">You're all caught up! We'll notify you when something important happens.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($notifications->hasPages())
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
