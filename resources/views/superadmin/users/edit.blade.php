@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    <div class="bg-white shadow rounded-lg p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Edit System User</h1>
            <p class="text-sm text-gray-600">Update user information, role, and permissions</p>
        </div>

        <!-- Status Management Section -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">User Status</h2>
                    <p class="text-sm text-gray-600">Manage user access to the system</p>
                </div>
                <div>
                    @if($user->status === 'active')
                        <span class="px-4 py-2 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-2"></i>Active
                        </span>
                    @elseif($user->status === 'inactive')
                        <span class="px-4 py-2 inline-flex text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            <i class="fas fa-pause-circle mr-2"></i>Inactive
                        </span>
                    @else
                        <span class="px-4 py-2 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800">
                            <i class="fas fa-ban mr-2"></i>Blocked
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                @if($user->status !== 'active')
                    <form action="{{ route('system-users.activate', $user->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition">
                            <i class="fas fa-check-circle mr-2"></i>Activate
                        </button>
                    </form>
                @endif

                @if($user->status !== 'inactive')
                    <form action="{{ route('system-users.deactivate', $user->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition">
                            <i class="fas fa-pause-circle mr-2"></i>Deactivate
                        </button>
                    </form>
                @endif

                @if($user->status !== 'blocked' && $user->id !== auth()->id())
                    <form action="{{ route('system-users.block', $user->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition"
                                onclick="return confirm('Are you sure you want to block {{ $user->name }}?')">
                            <i class="fas fa-ban mr-2"></i>Block
                        </button>
                    </form>
                @endif
            </div>

            <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-600">
                    <i class="fas fa-info-circle mr-1 text-gray-500"></i>
                    <strong>Active:</strong> User can log in and access the system normally. 
                    <strong>Inactive:</strong> User account is temporarily disabled. 
                    <strong>Blocked:</strong> User is permanently blocked from accessing the system.
                </p>
            </div>
        </div>

        <form action="{{ route('system-users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $user->name) }}"
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
                           value="{{ old('email', $user->email) }}"
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
                           value="{{ old('phone', $user->phone) }}"
                           placeholder="e.g., 0241234567 or 233241234567"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 @error('phone') border-red-500 @enderror"
                           required>
                    @error('phone')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password (Optional) -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            New Password <span class="text-gray-500 text-xs">(leave blank to keep current)</span>
                        </label>
                        <button type="button" 
                                onclick="generatePassword()"
                                class="text-xs text-purple-600 hover:text-purple-800 font-medium">
                            <i class="fas fa-key mr-1"></i>Generate
                        </button>
                    </div>
                    <div class="relative">
                        <input type="password" 
                               id="password" 
                               name="password"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-purple-500 @error('password') border-red-500 @enderror">
                        <button type="button"
                                onclick="togglePasswordVisibility('password', 'eye-password')"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i id="eye-password" class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirm New Password
                    </label>
                    <div class="relative">
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <button type="button"
                                onclick="togglePasswordVisibility('password_confirmation', 'eye-confirm')"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i id="eye-confirm" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Generated Password Alert -->
                <div id="generated-password-alert" class="hidden md:col-span-2 bg-green-50 border border-green-200 rounded-lg p-3">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-xs text-green-700 font-medium mb-1">Generated Password:</p>
                            <code id="generated-password-display" class="text-sm text-green-900 font-mono break-all"></code>
                        </div>
                        <button type="button"
                                onclick="copyPassword()"
                                class="ml-3 text-green-600 hover:text-green-800"
                                title="Copy to clipboard">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <p class="text-xs text-green-600 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>Save this password - you'll need to share it with the user!
                    </p>
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
                        <option value="superadmin" {{ old('role', $user->role) == 'superadmin' ? 'selected' : '' }}>SuperAdmin (System Administrator)</option>
                        <option value="business_admin" {{ old('role', $user->role) == 'business_admin' ? 'selected' : '' }}>Business Admin (Business Owner)</option>
                        <option value="manager" {{ old('role', $user->role) == 'manager' ? 'selected' : '' }}>Manager (Branch Manager)</option>
                        <option value="cashier" {{ old('role', $user->role) == 'cashier' ? 'selected' : '' }}>Cashier (Sales Person)</option>
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
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 @error('business_id') border-red-500 @enderror"
                            onchange="filterBranches(this.value)">
                        <option value="">Select a Business</option>
                        @foreach($businesses as $business)
                            <option value="{{ $business->id }}" {{ old('business_id', $user->business_id) == $business->id ? 'selected' : '' }}>
                                {{ $business->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('business_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Branch Selection -->
                <div class="md:col-span-2" id="branch-field" style="display: none;">
                    <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Branch <span class="text-red-500" id="branch-required">*</span>
                    </label>
                    <select id="branch_id" 
                            name="branch_id" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 @error('branch_id') border-red-500 @enderror">
                        <option value="">Select a Branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" 
                                    data-business="{{ $branch->business_id }}"
                                    {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }} ({{ $branch->business->name }})
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
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
                    <i class="fas fa-save mr-2"></i>Update User
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const roleDescriptions = {
    superadmin: 'Full system access. Can manage all businesses, users, and system-wide settings.',
    business_admin: 'Manages a specific business and is assigned to one branch. Can manage staff and operations for their branch.',
    manager: 'Manages a specific branch. Can handle day-to-day operations, staff, and inventory for their branch.',
    cashier: 'Makes sales at a specific branch. Limited access to POS terminal and sales functions only.'
};

function handleRoleChange(role) {
    const descElement = document.getElementById('role-description');
    const businessField = document.getElementById('business-field');
    const branchField = document.getElementById('branch-field');
    const businessRequired = document.getElementById('business-required');
    const branchRequired = document.getElementById('branch-required');
    
    // Update description
    descElement.textContent = roleDescriptions[role] || 'Select a role to see its description';
    
    // Show/hide fields based on role
    if (role === 'superadmin') {
        businessField.style.display = 'none';
        branchField.style.display = 'none';
        document.getElementById('business_id').removeAttribute('required');
        document.getElementById('branch_id').removeAttribute('required');
    } else if (role === 'business_admin') {
        businessField.style.display = 'block';
        branchField.style.display = 'block';
        document.getElementById('business_id').setAttribute('required', 'required');
        document.getElementById('branch_id').setAttribute('required', 'required');
        businessRequired.style.display = 'inline';
        branchRequired.style.display = 'inline';
    } else if (role === 'manager' || role === 'cashier') {
        businessField.style.display = 'block';
        branchField.style.display = 'block';
        document.getElementById('business_id').removeAttribute('required');
        document.getElementById('branch_id').setAttribute('required', 'required');
        businessRequired.style.display = 'none';
        branchRequired.style.display = 'inline';
    } else {
        businessField.style.display = 'none';
        branchField.style.display = 'none';
        document.getElementById('business_id').removeAttribute('required');
        document.getElementById('branch_id').removeAttribute('required');
    }
}

function filterBranches(businessId) {
    const branchSelect = document.getElementById('branch_id');
    const options = branchSelect.querySelectorAll('option');
    
    options.forEach(option => {
        if (option.value === '') {
            option.style.display = 'block';
            return;
        }
        
        const optionBusiness = option.getAttribute('data-business');
        if (businessId === '' || optionBusiness === businessId) {
            option.style.display = 'block';
        } else {
            option.style.display = 'none';
        }
    });
    
    // Reset branch selection if it doesn't match business
    const selectedOption = branchSelect.options[branchSelect.selectedIndex];
    if (selectedOption && selectedOption.getAttribute('data-business') !== businessId && businessId !== '') {
        branchSelect.value = '';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    if (roleSelect.value) {
        handleRoleChange(roleSelect.value);
    }
    
    // Initialize branch filtering if business is selected
    const businessSelect = document.getElementById('business_id');
    if (businessSelect.value) {
        filterBranches(businessSelect.value);
    }
});

function generatePassword() {
    const length = 12;
    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let password = '';
    
    for (let i = 0; i < length; i++) {
        password += charset.charAt(Math.floor(Math.random() * charset.length));
    }
    
    document.getElementById('password').value = password;
    document.getElementById('password_confirmation').value = password;
    document.getElementById('generated-password-display').textContent = password;
    document.getElementById('generated-password-alert').classList.remove('hidden');
    
    // Show passwords
    document.getElementById('password').type = 'text';
    document.getElementById('password_confirmation').type = 'text';
    document.getElementById('eye-password').classList.remove('fa-eye');
    document.getElementById('eye-password').classList.add('fa-eye-slash');
    document.getElementById('eye-confirm').classList.remove('fa-eye');
    document.getElementById('eye-confirm').classList.add('fa-eye-slash');
}

function togglePasswordVisibility(fieldId, eyeId) {
    const field = document.getElementById(fieldId);
    const eye = document.getElementById(eyeId);
    
    if (field.type === 'password') {
        field.type = 'text';
        eye.classList.remove('fa-eye');
        eye.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        eye.classList.remove('fa-eye-slash');
        eye.classList.add('fa-eye');
    }
}

function copyPassword() {
    const password = document.getElementById('generated-password-display').textContent;
    navigator.clipboard.writeText(password).then(() => {
        alert('Password copied to clipboard!');
    });
}
</script>
@endsection
