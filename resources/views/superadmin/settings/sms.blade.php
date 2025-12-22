@extends('layouts.app')

@section('title', 'SMS Configuration')

@section('content')
<div class="min-h-screen bg-gray-100 py-6">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">SMS Configuration</h1>
                    <p class="text-sm text-gray-500 mt-1">Configure SMS sending settings</p>
                </div>
                <a href="{{ route('superadmin.settings.index') }}" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            @if(session('success'))
                <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <p class="text-green-700 text-sm">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('superadmin.settings.sms.update') }}" class="space-y-6">
                @csrf

                <!-- Provider -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        SMS Provider *
                    </label>
                    <input type="text" 
                           name="provider" 
                           value="{{ old('provider', $provider) }}"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-green-500 transition-colors @error('provider') border-red-500 @enderror" 
                           placeholder="deywuro"
                           required>
                    @error('provider')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Base URL -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        API Base URL *
                    </label>
                    <input type="url" 
                           name="base_url" 
                           value="{{ old('base_url', $base_url) }}"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-green-500 transition-colors @error('base_url') border-red-500 @enderror" 
                           placeholder="https://deywuro.com/api/sms"
                           required>
                    @error('base_url')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Username -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        API Username *
                    </label>
                    <input type="text" 
                           name="username" 
                           value="{{ old('username', $username) }}"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-green-500 transition-colors @error('username') border-red-500 @enderror" 
                           required>
                    @error('username')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        API Password *
                    </label>
                    <div class="relative">
                        <input type="password" 
                               name="password" 
                               id="sms-password"
                               value="{{ old('password', $password) }}"
                               placeholder="{{ $password ? 'Click eye icon to view' : 'Enter SMS API password' }}"
                               class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-green-500 transition-colors @error('password') border-red-500 @enderror" 
                               required>
                        <button type="button" 
                                onclick="togglePassword('sms-password', 'sms-password-toggle')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                title="Show/Hide password">
                            <i class="fas fa-eye" id="sms-password-toggle"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Source -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Sender Source *
                    </label>
                    <input type="text" 
                           name="source" 
                           value="{{ old('source', $source) }}"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-green-500 transition-colors @error('source') border-red-500 @enderror" 
                           placeholder="POS System"
                           required>
                    @error('source')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Enabled -->
                <div>
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               name="enabled" 
                               value="1"
                               {{ old('enabled', $enabled) == '1' ? 'checked' : '' }}
                               class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                        <span class="ml-3 text-sm font-semibold text-gray-700">Enable SMS Sending</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500 ml-8">When disabled, SMS sending will be skipped (useful for testing).</p>
                </div>

                <!-- Test SMS -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Test SMS Configuration</h3>
                    <div class="flex space-x-2">
                        <input type="text" 
                               id="test-phone" 
                               placeholder="Phone number (e.g., 0244123456)"
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                        <button type="button" 
                                onclick="testSms()"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold">
                            Send Test SMS
                        </button>
                    </div>
                    <div id="test-sms-result" class="mt-2 text-sm hidden"></div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                    <a href="{{ route('superadmin.settings.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = document.getElementById(iconId);
    
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

function testSms() {
    const phone = document.getElementById('test-phone').value;
    const resultDiv = document.getElementById('test-sms-result');
    
    if (!phone) {
        resultDiv.className = 'mt-2 text-sm text-red-600';
        resultDiv.textContent = 'Please enter a phone number';
        resultDiv.classList.remove('hidden');
        return;
    }
    
    resultDiv.className = 'mt-2 text-sm text-gray-600';
    resultDiv.textContent = 'Sending test SMS...';
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
            resultDiv.className = 'mt-2 text-sm text-green-600';
            resultDiv.textContent = '✓ ' + data.message;
        } else {
            resultDiv.className = 'mt-2 text-sm text-red-600';
            resultDiv.textContent = '✗ ' + data.message;
        }
    })
    .catch(error => {
        resultDiv.className = 'mt-2 text-sm text-red-600';
        resultDiv.textContent = '✗ Error: ' + error.message;
    });
}
</script>
@endsection

