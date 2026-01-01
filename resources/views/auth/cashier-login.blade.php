<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Login - POS Supermarket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Back to Home -->
        <div class="mb-6">
            <a href="/" class="text-gray-500 hover:text-gray-800 transition-colors inline-flex items-center space-x-2 text-sm font-medium">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Home</span>
            </a>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-orange-100 rounded-full mb-4">
                    <i class="fas fa-cash-register text-orange-600 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Cashier Login</h2>
            </div>

            @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                        <p class="text-red-700 text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login.cashier.post') }}" class="space-y-5">
                @csrf
                
                <!-- Email Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" 
                               name="email" 
                               id="email"
                               value="{{ old('email') }}"
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors text-sm @error('email') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror" 
                               placeholder="cashier@pos.com"
                               required 
                               autofocus>
                    </div>
                    @error('email') 
                        <p class="mt-1.5 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" 
                               name="password"
                               id="password"
                               class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors text-sm @error('password') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror" 
                               placeholder="••••••••"
                               required>
                        <button type="button" 
                                onclick="togglePassword()"
                                class="absolute right-0 inset-y-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    @error('password') 
                        <p class="mt-1.5 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-orange-600 text-white py-2.5 rounded-lg font-medium hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all shadow-sm hover:shadow flex items-center justify-center space-x-2">
                    <span>Sign In</span>
                    <i class="fas fa-arrow-right text-sm"></i>
                </button>
            </form>

            <!-- Other Logins -->
            <div class="mt-8 pt-6 border-t border-gray-100">
                <p class="text-xs font-medium text-gray-400 text-center uppercase tracking-wider mb-4">Or sign in as</p>
                <div class="grid grid-cols-3 gap-3">
                    <a href="{{ route('login.superadmin') }}" class="flex flex-col items-center justify-center p-3 rounded-lg border border-gray-200 hover:border-purple-500 hover:bg-purple-50 transition-all group">
                        <i class="fas fa-crown text-purple-500 mb-1.5 text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[10px] font-medium text-gray-600 group-hover:text-purple-700 text-center leading-tight">Super<br>Admin</span>
                    </a>
                    <a href="{{ route('login.business-admin') }}" class="flex flex-col items-center justify-center p-3 rounded-lg border border-gray-200 hover:border-blue-500 hover:bg-blue-50 transition-all group">
                        <i class="fas fa-briefcase text-blue-500 mb-1.5 text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[10px] font-medium text-gray-600 group-hover:text-blue-700 text-center leading-tight">Business<br>Admin</span>
                    </a>
                    <a href="{{ route('login.manager') }}" class="flex flex-col items-center justify-center p-3 rounded-lg border border-gray-200 hover:border-green-500 hover:bg-green-50 transition-all group">
                        <i class="fas fa-users-cog text-green-500 mb-1.5 text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[10px] font-medium text-gray-600 group-hover:text-green-700">Manager</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const role = 'cashier';
        
        // Autofill credentials on page load
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const stored = localStorage.getItem(`lastLogin_${role}`);
            
            if (stored && emailInput && passwordInput) {
                try {
                    const credentials = JSON.parse(stored);
                    if (credentials.email && !emailInput.value) {
                        emailInput.value = credentials.email;
                    }
                    if (credentials.password && !passwordInput.value) {
                        passwordInput.value = credentials.password;
                    }
                } catch (e) {
                    console.error('Failed to parse stored credentials', e);
                }
            }
        });
        
        // Store credentials on form submit
        document.querySelector('form').addEventListener('submit', function() {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            
            if (emailInput && passwordInput) {
                const credentials = {
                    email: emailInput.value,
                    password: passwordInput.value,
                    timestamp: Date.now()
                };
                localStorage.setItem(`lastLogin_${role}`, JSON.stringify(credentials));
            }
        });
        
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
