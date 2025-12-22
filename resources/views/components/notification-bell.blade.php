<div x-data="notificationBell()" x-init="init()" class="relative">
    <!-- Notification Bell Icon -->
    <button @click="toggleDropdown" class="relative text-white hover:bg-white/10 p-2 rounded-lg transition-colors focus:outline-none">
        <i class="fas fa-bell text-lg"></i>
        <!-- Unread Count Badge -->
        <span x-show="unreadCount > 0" 
              x-text="unreadCount > 9 ? '9+' : unreadCount"
              class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
        </span>
    </button>

    <!-- Dropdown Menu -->
    <div x-show="showDropdown" 
         @click.away="showDropdown = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-50 border border-gray-200">
        
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-gray-800">Notifications</h3>
            <div class="flex items-center gap-3">
                <a href="{{ route('notifications.index') }}" class="text-xs text-blue-600 hover:text-blue-800">
                    View all
                </a>
                <button @click="markAllAsRead" 
                        x-show="unreadCount > 0"
                        class="text-xs text-blue-600 hover:text-blue-800">
                    Mark all read
                </button>
            </div>
        </div>

        <!-- Notification List -->
        <div class="max-h-96 overflow-y-auto">
            <template x-if="notifications.length === 0">
                <div class="px-4 py-8 text-center text-gray-500">
                    <i class="fas fa-bell-slash text-3xl mb-2"></i>
                    <p class="text-sm">No notifications</p>
                </div>
            </template>

            <template x-for="notification in notifications" :key="notification.id">
                <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors"
                     @click="handleNotificationClick(notification)"
                     :class="{'bg-blue-50': !notification.read_at}">
                    <div class="flex items-start space-x-3">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <div :class="`w-10 h-10 rounded-full flex items-center justify-center`"
                                 :style="`background-color: ${getColorBg(notification.color)}`">
                                <i :class="`fas ${notification.icon}`" 
                                   :style="`color: ${getColorText(notification.color)}`"></i>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                            <p class="text-sm text-gray-600 mt-1" x-text="notification.message"></p>
                            <p class="text-xs text-gray-400 mt-1" x-text="notification.created_at"></p>
                        </div>

                        <!-- Urgency Indicator -->
                        <template x-if="notification.urgency === 'critical'">
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                    Critical
                                </span>
                            </div>
                        </template>
                        <template x-if="notification.urgency === 'warning'">
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                    Warning
                                </span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 border-t border-gray-200 text-center">
            <a href="{{ route('notifications.index') }}" 
               class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                View all notifications
            </a>
        </div>
    </div>
</div>

<script>
function notificationBell() {
    return {
        showDropdown: false,
        unreadCount: 0,
        notifications: [],
        
        init() {
            this.fetchNotifications();
            // Poll for new notifications every 30 seconds
            setInterval(() => this.fetchNotifications(), 30000);
        },
        
        toggleDropdown() {
            this.showDropdown = !this.showDropdown;
            if (this.showDropdown) {
                this.fetchNotifications();
            }
        },
        
        async fetchNotifications() {
            try {
                const response = await fetch('{{ route("notifications.unread") }}');
                const data = await response.json();
                this.unreadCount = data.count;
                this.notifications = data.notifications.map(n => ({
                    ...n,
                    action_url: n.action_url || null
                }));
            } catch (error) {
                console.error('Failed to fetch notifications:', error);
            }
        },
        
        async handleNotificationClick(notification) {
            // Mark as read
            await this.markAsRead(notification.id);
            
            // Navigate to action URL if exists
            if (notification.action_url) {
                window.location.href = notification.action_url;
            }
        },
        
        async markAsRead(notificationId) {
            try {
                await fetch(`/notifications/${notificationId}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                this.fetchNotifications();
            } catch (error) {
                console.error('Failed to mark notification as read:', error);
            }
        },
        
        async markAllAsRead() {
            try {
                await fetch('{{ route("notifications.mark-all-read") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                this.fetchNotifications();
            } catch (error) {
                console.error('Failed to mark all notifications as read:', error);
            }
        },
        
        getColorBg(color) {
            const colors = {
                'red': '#fee2e2',
                'orange': '#ffedd5',
                'yellow': '#fef3c7',
                'green': '#d1fae5',
                'blue': '#dbeafe',
                'purple': '#ede9fe',
                'gray': '#f3f4f6'
            };
            return colors[color] || colors['blue'];
        },
        
        getColorText(color) {
            const colors = {
                'red': '#dc2626',
                'orange': '#ea580c',
                'yellow': '#ca8a04',
                'green': '#059669',
                'blue': '#2563eb',
                'purple': '#7c3aed',
                'gray': '#6b7280'
            };
            return colors[color] || colors['blue'];
        }
    }
}
</script>
