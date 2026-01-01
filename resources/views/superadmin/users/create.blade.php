@extends('layouts.app')

@section('title', 'Create User')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Create System User</h1>
        <p class="text-sm text-gray-500 mt-1">Add a new user to the system and configure their access permissions.</p>
    </div>

    @if ($errors->any())
        <div class="rounded-md bg-red-50 p-4 mb-6 border border-red-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
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

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
            <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                <h2 class="text-base font-semibold text-gray-800">Account Details</h2>
                <p class="text-xs text-gray-500">Personal information and login credentials.</p>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base py-3 px-4" required>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base py-3 px-4" required>
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="05X XXX XXXX" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base py-3 px-4" required>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-900">Security</h3>
                        <button type="button" onclick="generatePassword()" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Generate Strong Password</button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                            <input type="password" id="password" name="password" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base py-3 px-4" required>
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base py-3 px-4" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                <h2 class="text-base font-semibold text-gray-800">Access Level</h2>
                <p class="text-xs text-gray-500">Determine what this user can see and do.</p>
            </div>
            
            <div class="p-6 space-y-6">
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">user Role <span class="text-red-500">*</span></label>
                    <select id="role" name="role" class="tom-select w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base py-3 px-4" required onchange="toggleBusinessField()">
                        <option value="">Select a Role...</option>
                        <option value="superadmin" {{ old('role') == 'superadmin' ? 'selected' : '' }}>SuperAdmin (System Administrator)</option>
                        <option value="business_admin" {{ old('role') == 'business_admin' ? 'selected' : '' }}>Business Admin (Business Owner)</option>
                        <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager (Branch Manager)</option>
                        <option value="cashier" {{ old('role') == 'cashier' ? 'selected' : '' }}>Cashier (Sales Person)</option>
                    </select>
                </div>

                <div id="business-field" class="hidden">
                    <label for="business_id" class="block text-sm font-medium text-gray-700 mb-1">Assign to Business <span class="text-red-500">*</span></label>
                    <select id="business_id" name="business_id" class="tom-select w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base py-3 px-4">
                        <option value="">Select a Business...</option>
                        @foreach($businesses as $business)
                            <option value="{{ $business->id }}" {{ old('business_id') == $business->id ? 'selected' : '' }}>{{ $business->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('system-users.index') }}" class="px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cancel
            </a>
            <button type="submit" class="px-8 py-3 border border-transparent shadow-sm text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Create User
            </button>
        </div>
    </form>
</div>

<script>
    function toggleBusinessField() {
        const role = document.getElementById('role').value;
        const businessField = document.getElementById('business-field');
        const businessSelect = document.getElementById('business_id');

        if (['business_admin', 'manager', 'cashier'].includes(role)) {
            businessField.classList.remove('hidden');
            businessSelect.required = true;
        } else {
            businessField.classList.add('hidden');
            businessSelect.required = false;
        }
    }

    function generatePassword() {
        const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
        let password = "";
        for (let i = 0; i < 16; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('password').value = password;
        document.getElementById('password_confirmation').value = password;
        
        // Optional: briefly show password type to text
        const pwd = document.getElementById('password');
        pwd.type = "text";
        setTimeout(() => pwd.type = "password", 2000);
    }

    document.addEventListener('DOMContentLoaded', toggleBusinessField);
</script>
@endsection
