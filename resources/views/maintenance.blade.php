<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode - POS Supermarket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
        .animate-pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full">
        <!-- Main Container -->
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            <!-- Header Section with Gradient -->
            <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 px-8 py-12 text-center">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-white/20 backdrop-blur-sm rounded-full mb-6 animate-float">
                    <i class="fas fa-tools text-white text-5xl"></i>
                </div>
                <h1 class="text-4xl font-bold text-white mb-3">We'll Be Right Back!</h1>
                <p class="text-blue-100 text-lg">We're performing scheduled maintenance</p>
            </div>

            <!-- Content Section -->
            <div class="px-8 py-10">
                <!-- Status Indicator -->
                <div class="flex items-center justify-center mb-8">
                    <div class="flex items-center space-x-3 bg-yellow-50 border border-yellow-200 rounded-full px-6 py-3">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full animate-pulse-slow"></div>
                        <span class="text-yellow-800 font-semibold text-sm">Maintenance in Progress</span>
                    </div>
                </div>

                <!-- Message -->
                <div class="text-center mb-8">
                    <p class="text-gray-700 text-lg leading-relaxed mb-4">
                        Our system is currently undergoing scheduled maintenance to improve your experience.
                    </p>
                    <p class="text-gray-600">
                        We apologize for any inconvenience and appreciate your patience.
                    </p>
                </div>

                <!-- Features List -->
                <div class="bg-gray-50 rounded-xl p-6 mb-8">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-sparkles text-indigo-600 mr-2"></i>
                        What We're Working On
                    </h3>
                    <ul class="space-y-3">
                        <li class="flex items-start text-sm text-gray-600">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-0.5"></i>
                            <span>System updates and performance improvements</span>
                        </li>
                        <li class="flex items-start text-sm text-gray-600">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-0.5"></i>
                            <span>Security enhancements</span>
                        </li>
                        <li class="flex items-start text-sm text-gray-600">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-0.5"></i>
                            <span>New features and bug fixes</span>
                        </li>
                    </ul>
                </div>

                <!-- Estimated Time -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center space-x-2 text-gray-600">
                        <i class="fas fa-clock text-indigo-600"></i>
                        <span class="text-sm">Estimated completion: Soon</span>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="border-t border-gray-200 pt-6 text-center">
                    <p class="text-sm text-gray-600 mb-3">Need immediate assistance?</p>
                    <div class="flex items-center justify-center space-x-4">
                        <a href="mailto:support@possupermarket.com" 
                           class="inline-flex items-center space-x-2 text-indigo-600 hover:text-indigo-700 font-medium text-sm transition-colors">
                            <i class="fas fa-envelope"></i>
                            <span>Contact Support</span>
                        </a>
                    </div>
                </div>

                <!-- Auto Refresh Notice -->
                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-500">
                        <i class="fas fa-sync-alt mr-1"></i>
                        This page will automatically refresh when maintenance is complete
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-8 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-shield-alt text-indigo-600"></i>
                        <span>POS Supermarket System</span>
                    </div>
                    <div>
                        <span id="current-time"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Decorative Elements -->
        <div class="absolute top-10 left-10 w-20 h-20 bg-blue-200 rounded-full opacity-20 blur-xl"></div>
        <div class="absolute bottom-10 right-10 w-32 h-32 bg-purple-200 rounded-full opacity-20 blur-xl"></div>
    </div>

    <script>
        // Update current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit'
            });
            const timeElement = document.getElementById('current-time');
            if (timeElement) {
                timeElement.textContent = timeString;
            }
        }
        updateTime();
        setInterval(updateTime, 1000);

        // Auto-refresh every 30 seconds
        setTimeout(function() {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>

