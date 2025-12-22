@extends('layouts.app')

@section('title', 'Manage Business & Branches')

@section('content')
<div class="p-6 space-y-8">
    <!-- Notification Container -->
    <div id="notification-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">
            {{ session('error') }}
        </div>
    @endif
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Create Business</h2>
        <livewire:business.create-business />
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Create Branch</h2>
        <livewire:branch.create-branch />
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Listen for Livewire notification events
    window.addEventListener('notify', function(event) {
        showNotification(event.detail.message, event.detail.type);
    });

    // Function to show notifications
    function showNotification(message, type = 'success') {
        const container = document.getElementById('notification-container');
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification px-6 py-4 rounded-lg shadow-lg text-white transform transition-all duration-300 ease-in-out translate-x-full opacity-0 ${getNotificationClass(type)}`;
        
        notification.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas ${getNotificationIcon(type)} mr-3"></i>
                    <span>${message}</span>
                </div>
                <button onclick="removeNotification(this)" class="ml-4 text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        // Add to container
        container.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full', 'opacity-0');
        }, 100);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            removeNotification(notification.querySelector('button'));
        }, 5000);
    }

    // Function to get notification CSS class based on type
    function getNotificationClass(type) {
        switch(type) {
            case 'success':
                return 'bg-green-500';
            case 'error':
                return 'bg-red-500';
            case 'warning':
                return 'bg-yellow-500';
            case 'info':
                return 'bg-blue-500';
            default:
                return 'bg-green-500';
        }
    }

    // Function to get notification icon based on type
    function getNotificationIcon(type) {
        switch(type) {
            case 'success':
                return 'fa-check-circle';
            case 'error':
                return 'fa-exclamation-circle';
            case 'warning':
                return 'fa-exclamation-triangle';
            case 'info':
                return 'fa-info-circle';
            default:
                return 'fa-check-circle';
        }
    }

    // Make removeNotification globally available
    window.removeNotification = function(button) {
        const notification = button.closest('.notification');
        notification.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            notification.remove();
        }, 300);
    };
});
</script>

<style>
.notification {
    min-width: 300px;
    max-width: 400px;
}
</style>
@endsection


