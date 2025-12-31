@extends('layouts.app')

@section('title', 'SMS Configuration')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">SMS Configuration</h1>
            <p class="mt-2 text-sm text-gray-500">Configure your SMS provider settings for system notifications.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('superadmin.settings.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-arrow-left mr-2 text-gray-400"></i> Back to Settings
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-md bg-green-50 p-4 border border-green-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form method="POST" action="{{ route('superadmin.settings.sms.update') }}" class="space-y-6 p-6 sm:p-8">
            @csrf

            <div class="grid grid-cols-1 gap-y-6 gap-x-8 sm:grid-cols-2">
                <!-- Provider & Enabled Toggle -->
                <div class="sm:col-span-2 flex items-center justify-between border-b border-gray-100 pb-6 mb-2">
                    <div>
                         <label for="provider" class="block text-sm font-medium text-gray-700">SMS Provider</label>
                         <input type="text" name="provider" id="provider" value="{{ old('provider', $provider) }}" 
                               class="mt-1 focus:ring-green-500 focus:border-green-500 block w-64 sm:text-sm border-gray-300 rounded-lg py-2"
                               placeholder="e.g. deywuro">
                    </div>

                    <div class="flex items-center">
                        <div class="flex items-center h-5">
                            <input id="enabled" name="enabled" type="checkbox" value="1" {{ old('enabled', $enabled) == '1' ? 'checked' : '' }} 
                                   class="focus:ring-green-500 h-5 w-5 text-green-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="enabled" class="font-medium text-gray-700">Enable SMS Sending</label>
                            <p class="text-gray-500 text-xs">Uncheck to disable all SMS notifications</p>
                        </div>
                    </div>
                </div>

                <!-- API Base URL -->
                <div class="sm:col-span-2">
                    <label for="base_url" class="block text-sm font-medium text-gray-700">API Base URL</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                         <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm"><i class="fas fa-link"></i></span>
                        </div>
                        <input type="url" name="base_url" id="base_url" value="{{ old('base_url', $base_url) }}" 
                               class="focus:ring-green-500 focus:border-green-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-lg py-3" 
                               placeholder="https://api.sms-provider.com/v1">
                    </div>
                    @error('base_url') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Username -->
                <div class="sm:col-span-1">
                    <label for="username" class="block text-sm font-medium text-gray-700">API Username / Key</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                         <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm"><i class="fas fa-user-circle"></i></span>
                        </div>
                        <input type="text" name="username" id="username" value="{{ old('username', $username) }}" 
                               class="focus:ring-green-500 focus:border-green-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-lg py-3">
                    </div>
                    @error('username') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Password -->
                <div class="sm:col-span-1">
                    <label for="password" class="block text-sm font-medium text-gray-700">API Password / Secret</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                         <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm"><i class="fas fa-key"></i></span>
                        </div>
                        <input type="password" name="password" id="password" value="{{ old('password', $password) }}" 
                               class="focus:ring-green-500 focus:border-green-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-lg py-3">
                        <button type="button" onclick="togglePassword('password', 'password-icon')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="password-icon"></i>
                        </button>
                    </div>
                    @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Sender ID -->
                <div class="sm:col-span-2">
                    <label for="source" class="block text-sm font-medium text-gray-700">Sender ID (Source)</label>
                    <div class="mt-1 relative rounded-md shadow-sm w-full sm:w-1/2">
                         <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm"><i class="fas fa-broadcast-tower"></i></span>
                        </div>
                        <input type="text" name="source" id="source" value="{{ old('source', $source) }}" 
                               class="focus:ring-green-500 focus:border-green-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-lg py-3"
                               placeholder="Enter Sender ID">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">The name that appears on the recipient's phone (max 11 chars usually).</p>
                    @error('source') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Test Connection -->
             <div class="bg-gray-50 rounded-lg p-6 border border-gray-200 mt-6">
                <h3 class="text-sm font-medium text-gray-900 mb-4">Test SMS Delivery</h3>
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text" id="test-phone" placeholder="Recipient Phone (e.g. 0244123456)" 
                               class="shadow-sm focus:ring-green-500 focus:border-green-500 block w-full sm:text-sm border-gray-300 rounded-lg py-2">
                    </div>
                    <button type="button" onclick="testSms()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        <i class="fas fa-paper-plane mr-2"></i> Send Test
                    </button>
                </div>
                <div id="test-sms-result" class="mt-3 text-sm font-medium hidden"></div>
            </div>

            <div class="flex justify-end pt-5">
                <button type="button" onclick="window.location='{{ route('superadmin.settings.index') }}'" class="bg-white py-2 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3">
                    Cancel
                </button>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function testSms() {
        const phone = document.getElementById('test-phone').value;
        const resultDiv = document.getElementById('test-sms-result');
        
        if (!phone) {
            resultDiv.className = 'mt-3 text-sm font-medium text-red-600';
            resultDiv.textContent = 'Please enter a phone number.';
            resultDiv.classList.remove('hidden');
            return;
        }
        
        resultDiv.className = 'mt-3 text-sm font-medium text-gray-600';
        resultDiv.textContent = 'Sending...';
        resultDiv.classList.remove('hidden');
        
        fetch('{{ route("superadmin.settings.sms.test") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ phone_number: phone })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.className = 'mt-3 text-sm font-medium text-green-600';
                resultDiv.textContent = '✓ ' + data.message;
            } else {
                resultDiv.className = 'mt-3 text-sm font-medium text-red-600';
                resultDiv.textContent = '✗ ' + data.message;
            }
        })
        .catch(error => {
            resultDiv.className = 'mt-3 text-sm font-medium text-red-600';
            resultDiv.textContent = '✗ Error: ' + error.message;
        });
    }
</script>
@endsection
