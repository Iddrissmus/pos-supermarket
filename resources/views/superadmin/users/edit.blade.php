@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    <div class="bg-white shadow rounded-lg p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Edit System User</h1>
            <p class="text-sm text-gray-600">Update user information, role, and permissions</p>
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

                <!-- Password (Optional) -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        New Password <span class="text-gray-500 text-xs">(leave blank to keep current)</span>
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirm New Password
                    </label>
                    <input type="password" 
                           id="password_confirmation" 
                           name="password_confirmation"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
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
</script>
@endsection
