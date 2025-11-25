@extends('layouts.app')

@section('title', 'Create New User')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    <div class="bg-white shadow rounded-lg p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Create New System User</h1>
            <p class="text-sm text-gray-600">Add a new user to the system with specific role and permissions</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">There were errors with your submission:</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('system-users.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 @error('name') border-red-500 @enderror"
                           required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="md:col-span-2">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 @error('email') border-red-500 @enderror"
                           required>
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone Number -->
                <div class="md:col-span-2">
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Phone Number <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="phone" 
                           name="phone" 
                           value="{{ old('phone') }}"
                           placeholder="e.g., 0241234567 or 233241234567"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 @error('phone') border-red-500 @enderror"
                           required>
                    @error('phone')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>Login credentials will be sent to this number via SMS
                    </p>
                </div>

                <!-- Password -->
                <div class="md:col-span-2">
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <button type="button" 
                                onclick="generateStrongPassword()"
                                class="text-sm text-purple-600 hover:text-purple-800 font-medium inline-flex items-center">
                            <i class="fas fa-key mr-1"></i>Generate Strong Password
                        </button>
                    </div>
                    <div class="relative">
                        <input type="password" 
                               id="password" 
                               name="password"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-purple-500 @error('password') border-red-500 @enderror"
                               required
                               oninput="checkPasswordStrength(this.value)">
                        <button type="button"
                                onclick="togglePasswordVisibility('password', 'password-eye')"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i id="password-eye" class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <!-- Password Strength Indicator -->
                    <div id="password-strength" class="mt-2 hidden">
                        <div class="flex items-center space-x-2">
                            <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div id="strength-bar" class="h-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                            <span id="strength-text" class="text-xs font-medium"></span>
                        </div>
                    </div>
                    <!-- Generated Password Display -->
                    <div id="generated-password-display" class="mt-2 hidden bg-green-50 border border-green-200 rounded-lg p-3">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-xs text-green-700 font-medium mb-1">Generated Password:</p>
                                <code id="generated-password-text" class="text-sm text-green-900 font-mono break-all"></code>
                            </div>
                            <button type="button"
                                    onclick="copyPasswordToClipboard()"
                                    class="ml-3 text-green-600 hover:text-green-800"
                                    title="Copy to clipboard">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <p class="text-xs text-green-600 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>Save this password - the user will need it to log in!
                        </p>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="md:col-span-2">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirm Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-purple-500"
                               required>
                        <button type="button"
                                onclick="togglePasswordVisibility('password_confirmation', 'confirm-eye')"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i id="confirm-eye" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Role -->
                <div class="md:col-span-2">
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        User Role <span class="text-red-500">*</span>
                    </label>
                    <select id="role" 
                            name="role" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 @error('role') border-red-500 @enderror"
                            required
                            onchange="handleRoleChange(this.value)">
                        <option value="">Select a Role</option>
                        <option value="superadmin" {{ old('role') == 'superadmin' ? 'selected' : '' }}>SuperAdmin (System Administrator)</option>
                        <option value="business_admin" {{ old('role') == 'business_admin' ? 'selected' : '' }}>Business Admin (Business Owner)</option>
                        <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager (Branch Manager)</option>
                        <option value="cashier" {{ old('role') == 'cashier' ? 'selected' : '' }}>Cashier (Sales Person)</option>
                    </select>
                    @error('role')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1" id="role-description">Select a role to see its description</p>
                </div>

                <!-- Business Selection -->
                <div class="md:col-span-2" id="business-field" style="display: none;">
                    <label for="business_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Business <span class="text-red-500" id="business-required">*</span>
                    </label>
                    <select id="business_id" 
                            name="business_id" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 @error('business_id') border-red-500 @enderror">
                        <option value="">Select a Business</option>
                        @foreach($businesses as $business)
                            <option value="{{ $business->id }}" {{ old('business_id') == $business->id ? 'selected' : '' }}>
                                {{ $business->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('business_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>Branch assignment will be handled by the Business Admin
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-6 mt-6 border-t">
                <a href="{{ route('system-users.index') }}" 
                   class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-arrow-left mr-2"></i>Cancel
                </a>
                <button type="submit" 
                        class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-user-plus mr-2"></i>Create User
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const roleDescriptions = {
    superadmin: 'Full system access. Can manage all businesses, users, and system-wide settings.',
    business_admin: 'Manages their assigned business. Can create branches, assign staff, and handle business operations.',
    manager: 'Will be assigned to a branch by Business Admin. Manages branch operations, staff, and inventory.',
    cashier: 'Will be assigned to a branch by Business Admin. Handles sales at their assigned branch.'
};

function handleRoleChange(role) {
    const descElement = document.getElementById('role-description');
    const businessField = document.getElementById('business-field');
    const businessRequired = document.getElementById('business-required');
    
    // Update description
    descElement.textContent = roleDescriptions[role] || 'Select a role to see its description';
    
    // Show/hide business field based on role
    if (role === 'superadmin') {
        businessField.style.display = 'none';
        document.getElementById('business_id').removeAttribute('required');
    } else if (role === 'business_admin' || role === 'manager' || role === 'cashier') {
        businessField.style.display = 'block';
        document.getElementById('business_id').setAttribute('required', 'required');
        businessRequired.style.display = 'inline';
    } else {
        businessField.style.display = 'none';
        document.getElementById('business_id').removeAttribute('required');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    if (roleSelect.value) {
        handleRoleChange(roleSelect.value);
    }
});

// Password Generation Functions
function generateStrongPassword() {
    const length = 16;
    const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const lowercase = 'abcdefghijklmnopqrstuvwxyz';
    const numbers = '0123456789';
    const specialChars = '!@#$%^&*()_+-=[]{}|;:,.<>?';
    
    // Ensure at least one character from each category
    let password = '';
    password += uppercase[Math.floor(Math.random() * uppercase.length)];
    password += lowercase[Math.floor(Math.random() * lowercase.length)];
    password += numbers[Math.floor(Math.random() * numbers.length)];
    password += specialChars[Math.floor(Math.random() * specialChars.length)];
    
    // Fill the rest randomly
    const allChars = uppercase + lowercase + numbers + specialChars;
    for (let i = password.length; i < length; i++) {
        password += allChars[Math.floor(Math.random() * allChars.length)];
    }
    
    // Shuffle the password
    password = password.split('').sort(() => Math.random() - 0.5).join('');
    
    // Set the password fields
    const passwordField = document.getElementById('password');
    const confirmField = document.getElementById('password_confirmation');
    
    passwordField.value = password;
    confirmField.value = password;
    
    // Show the generated password display
    document.getElementById('generated-password-text').textContent = password;
    document.getElementById('generated-password-display').classList.remove('hidden');
    
    // Check strength
    checkPasswordStrength(password);
}

function togglePasswordVisibility(fieldId, eyeIconId) {
    const field = document.getElementById(fieldId);
    const eyeIcon = document.getElementById(eyeIconId);
    
    if (field.type === 'password') {
        field.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}

function checkPasswordStrength(password) {
    const strengthContainer = document.getElementById('password-strength');
    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');
    
    if (!password) {
        strengthContainer.classList.add('hidden');
        return;
    }
    
    strengthContainer.classList.remove('hidden');
    
    let strength = 0;
    const checks = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        numbers: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password)
    };
    
    // Calculate strength
    strength += checks.length ? 20 : 0;
    strength += checks.uppercase ? 20 : 0;
    strength += checks.lowercase ? 20 : 0;
    strength += checks.numbers ? 20 : 0;
    strength += checks.special ? 20 : 0;
    
    // Update bar and text
    strengthBar.style.width = strength + '%';
    
    if (strength <= 40) {
        strengthBar.className = 'h-full transition-all duration-300 bg-red-500';
        strengthText.textContent = 'Weak';
        strengthText.className = 'text-xs font-medium text-red-600';
    } else if (strength <= 60) {
        strengthBar.className = 'h-full transition-all duration-300 bg-yellow-500';
        strengthText.textContent = 'Fair';
        strengthText.className = 'text-xs font-medium text-yellow-600';
    } else if (strength <= 80) {
        strengthBar.className = 'h-full transition-all duration-300 bg-blue-500';
        strengthText.textContent = 'Good';
        strengthText.className = 'text-xs font-medium text-blue-600';
    } else {
        strengthBar.className = 'h-full transition-all duration-300 bg-green-500';
        strengthText.textContent = 'Strong';
        strengthText.className = 'text-xs font-medium text-green-600';
    }
}

function copyPasswordToClipboard() {
    const passwordText = document.getElementById('generated-password-text').textContent;
    
    // Modern clipboard API
    if (navigator.clipboard) {
        navigator.clipboard.writeText(passwordText).then(() => {
            // Show feedback
            const copyBtn = event.target.closest('button');
            const icon = copyBtn.querySelector('i');
            icon.classList.remove('fa-copy');
            icon.classList.add('fa-check');
            copyBtn.classList.add('text-green-700');
            
            setTimeout(() => {
                icon.classList.remove('fa-check');
                icon.classList.add('fa-copy');
                copyBtn.classList.remove('text-green-700');
            }, 2000);
        });
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = passwordText;
        textArea.style.position = 'fixed';
        textArea.style.opacity = '0';
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Password copied to clipboard!');
    }
}
</script>
@endsection
