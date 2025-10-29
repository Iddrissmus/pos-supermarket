<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Login - POS Supermarket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Back to Home -->
        <div class="mb-4">
            <a href="/" class="text-white hover:text-green-200 transition-colors inline-flex items-center space-x-2">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Home</span>
            </a>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-green-500 to-green-700 rounded-full mb-4">
                    <i class="fas fa-users-cog text-white text-2xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Manager Login</h2>
                <p class="text-gray-600">Branch Management Access</p>
            </div>

            @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                        <p class="text-red-700 text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login.manager.post') }}" class="space-y-6">
                @csrf
                
                <!-- Email Field -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-gray-400"></i>Email Address
                    </label>
                    <input type="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-green-500 transition-colors @error('email') border-red-500 @enderror" 
                           placeholder="manager@pos.com"
                           required 
                           autofocus>
                    @error('email') 
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-gray-400"></i>Password
                    </label>
                    <div class="relative">
                        <input type="password" 
                               name="password"
                               id="password"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-green-500 transition-colors @error('password') border-red-500 @enderror" 
                               placeholder="Enter your password"
                               required>
                        <button type="button" 
                                onclick="togglePassword()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    @error('password') 
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               name="remember" 
                               id="remember" 
                               class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                        <span class="ml-2 text-sm text-gray-700">Remember me</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-green-600 to-green-700 text-white py-3 rounded-lg font-semibold hover:from-green-700 hover:to-green-800 transform hover:scale-[1.02] transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2">
                    <span>Sign In</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <!-- Other Logins -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <p class="text-xs text-gray-500 text-center mb-3">Different Role?</p>
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <a href="{{ route('login.superadmin') }}" class="text-center py-2 px-3 bg-purple-50 text-purple-700 rounded hover:bg-purple-100 transition-colors">
                        SuperAdmin
                    </a>
                    <a href="{{ route('login.business-admin') }}" class="text-center py-2 px-3 bg-blue-50 text-blue-700 rounded hover:bg-blue-100 transition-colors">
                        Business Admin
                    </a>
                    <a href="{{ route('login.cashier') }}" class="text-center py-2 px-3 bg-orange-50 text-orange-700 rounded hover:bg-orange-100 transition-colors">
                        Cashier
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
