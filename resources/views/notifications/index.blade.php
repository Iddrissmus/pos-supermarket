@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-5xl mx-auto">
    
    <!-- Header -->
    <div class="mb-8 flex justify-between items-end">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-800">Notifications</h1>
            <p class="text-slate-500 mt-1">Stay updated with activity across your branches</p>
        </div>
        
        @if($notifications->where('read_at', null)->count() > 0)
        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
            @csrf
            <button type="submit" class="btn bg-white border-slate-200 hover:border-slate-300 text-indigo-600 hover:text-indigo-700">
                <i class="fas fa-check-double mr-2"></i> Mark all as read
            </button>
        </form>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg flex items-center shadow-sm">
            <i class="fas fa-check-circle mr-3"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Notifications Container -->
    <div class="space-y-8">
        @php
            // Group notifications by date
            $groupedNotifications = $notifications->groupBy(function($item) {
                if ($item->created_at->isToday()) {
                    return 'Today';
                } elseif ($item->created_at->isYesterday()) {
                    return 'Yesterday';
                } elseif ($item->created_at->diffInDays(now()) <= 7) {
                    return 'Last 7 Days';
                } else {
                    return 'Older';
                }
            });
            
            // Define order for custom sorting if needed, though groupBy usually preserves insertion order usually enough
            $order = ['Today', 'Yesterday', 'Last 7 Days', 'Older'];
        @endphp

        @forelse($order as $group)
            @if(isset($groupedNotifications[$group]))
            <div>
                <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4">{{ $group }}</h3>
                <div class="space-y-3">
                    @foreach($groupedNotifications[$group] as $notification)
                        <div class="group relative flex items-start bg-white p-4 rounded-xl shadow-sm border border-slate-200 transition-all hover:shadow-md {{ !$notification->read_at ? 'border-l-4 border-l-indigo-500 bg-indigo-50/10' : '' }}">
                            
                            <!-- Icon -->
                            <div class="shrink-0 mr-4 mt-1">
                                @php
                                    $color = $notification->data['color'] ?? 'blue';
                                    $bgClass = "bg-{$color}-100"; 
                                    $textClass = "text-{$color}-600";
                                    // Fallback
                                    if(!in_array($color, ['red','green','blue','yellow','indigo','purple','pink','orange','teal','emerald','slate','gray'])) {
                                        $bgClass = 'bg-slate-100';
                                        $textClass = 'text-slate-600';
                                    }
                                @endphp
                                <div class="w-10 h-10 rounded-full {{ $bgClass }} flex items-center justify-center">
                                    <i class="fas {{ $notification->data['icon'] ?? 'fa-bell' }} {{ $textClass }}"></i>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start">
                                    <h4 class="text-base font-semibold text-slate-800 mb-1">
                                        {{ $notification->data['title'] ?? 'Notification' }}
                                        @if(($notification->data['urgency'] ?? '') === 'critical')
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 uppercase tracking-wide">
                                                Critical
                                            </span>
                                        @endif
                                    </h4>
                                    <span class="text-xs text-slate-400 whitespace-nowrap ml-2">
                                        {{ $notification->created_at->format('H:i') }}
                                    </span>
                                </div>
                                <p class="text-sm text-slate-600 mb-2 leading-relaxed">
                                    {{ $notification->data['message'] ?? '' }}
                                </p>

                                <!-- Rich Content: Low Stock -->
                                @if(($notification->data['type'] ?? '') === 'low_stock')
                                    <div class="mt-2 bg-slate-50 rounded-lg p-3 border border-slate-100 text-xs grid grid-cols-2 gap-y-2 gap-x-4 max-w-md">
                                        <div><span class="font-medium text-slate-500">Product:</span> <span class="text-slate-900 block">{{ $notification->data['product_name'] ?? 'N/A' }}</span></div>
                                        <div><span class="font-medium text-slate-500">Branch:</span> <span class="text-slate-900 block">{{ $notification->data['branch_name'] ?? 'N/A' }}</span></div>
                                        <div><span class="font-medium text-slate-500">Stock:</span> <span class="font-bold text-rose-600 block">{{ $notification->data['current_stock'] ?? 0 }}</span></div>
                                        <div><span class="font-medium text-slate-500">Reorder:</span> <span class="text-slate-900 block">{{ $notification->data['reorder_level'] ?? 0 }}</span></div>
                                    </div>
                                    <div class="mt-2">
                                        <a href="{{ route('layouts.assign') }}" class="inline-flex items-center text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                                            Assign Stock <i class="fas fa-arrow-right ml-1"></i>
                                        </a>
                                    </div>
                                @endif

                                <!-- Rich Content: Register Closed -->
                                @if(($notification->data['type'] ?? '') === 'register_closed')
                                    <div class="mt-2 text-xs flex gap-4 text-slate-500">
                                        <div>Expected: <span class="font-medium text-slate-900">GHS {{ $notification->data['expected_amount'] ?? '0.00' }}</span></div>
                                        <div>Actual: <span class="font-medium text-slate-900">GHS {{ $notification->data['actual_amount'] ?? '0.00' }}</span></div>
                                        @if(($notification->data['difference'] ?? 0) != 0)
                                            <div>Diff: <span class="font-bold {{ ($notification->data['difference'] > 0) ? 'text-blue-600' : 'text-rose-600' }}">
                                                {{ ($notification->data['difference'] > 0 ? '+' : '') . $notification->data['difference'] }}
                                            </span></div>
                                        @endif
                                    </div>
                                @endif
                                
                                @if(isset($notification->data['action_url']) && ($notification->data['type'] ?? '') !== 'low_stock')
                                    <div class="mt-2">
                                        <a href="{{ $notification->data['action_url'] }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 hover:underline">
                                            View Details
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="shrink-0 flex flex-col space-y-2 ml-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                @if(!$notification->read_at)
                                <form action="{{ route('notifications.mark-read', $notification->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 hover:bg-indigo-100 flex items-center justify-center transition-colors" title="Mark as Read">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                @endif
                                <form id="delete-form-{{ $notification->id }}" action="{{ route('notifications.destroy', $notification->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="openDeleteModal('delete-form-{{ $notification->id }}')" class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition-colors" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        @empty
            <div class="p-16 text-center bg-white rounded-xl border border-slate-200 shadow-sm border-dashed">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-inbox text-slate-300 text-4xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800">All caught up!</h3>
                <p class="text-slate-500 mt-2">You have no new notifications at the moment.</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
    @endif

</div>
@endsection

<!-- Delete Notification Modal -->
<div id="deleteModal" class="hidden fixed inset-0 z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" onclick="closeDeleteModal()"></div>
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                        <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Delete Notification</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Are you sure you want to delete this notification? This action cannot be undone.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <button type="button" id="confirmDeleteBtn" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">Delete</button>
                <button type="button" onclick="closeDeleteModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
    let deleteFormId = null;

    function openDeleteModal(formId) {
        deleteFormId = formId;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        deleteFormId = null;
    }

    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (deleteFormId) {
            document.getElementById(deleteFormId).submit();
        }
    });

    // Close modal on escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeDeleteModal();
        }
    });
</script>
