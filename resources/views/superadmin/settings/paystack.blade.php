@extends('layouts.app')

@section('title', 'Paystack Configuration')

@section('content')
<div class="min-h-screen bg-gray-100 py-6">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Paystack Configuration</h1>
                    <p class="text-sm text-gray-500 mt-1">Configure Paystack payment gateway settings</p>
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

            <form method="POST" action="{{ route('superadmin.settings.paystack.update') }}" class="space-y-6">
                @csrf

                <!-- Public Key -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Public Key *
                    </label>
                    <div class="relative">
                        <input type="text" 
                               name="public_key" 
                               id="paystack-public-key"
                               value="{{ old('public_key', $public_key) }}"
                               placeholder="pk_test_xxxxxxxxxxxxx or pk_live_xxxxxxxxxxxxx"
                               class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-indigo-500 transition-colors @error('public_key') border-red-500 @enderror" 
                               required>
                        <button type="button" 
                                onclick="togglePassword('paystack-public-key', 'paystack-public-key-toggle')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                title="Show/Hide key">
                            <i class="fas fa-eye" id="paystack-public-key-toggle"></i>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Your Paystack public key (starts with pk_test_ or pk_live_)</p>
                    @error('public_key')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Secret Key -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Secret Key *
                    </label>
                    <div class="relative">
                        <input type="password" 
                               name="secret_key" 
                               id="paystack-secret-key"
                               value="{{ old('secret_key', $secret_key) }}"
                               placeholder="sk_test_xxxxxxxxxxxxx or sk_live_xxxxxxxxxxxxx"
                               class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-indigo-500 transition-colors @error('secret_key') border-red-500 @enderror" 
                               required>
                        <button type="button" 
                                onclick="togglePassword('paystack-secret-key', 'paystack-secret-key-toggle')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                title="Show/Hide key">
                            <i class="fas fa-eye" id="paystack-secret-key-toggle"></i>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Your Paystack secret key (starts with sk_test_ or sk_live_)</p>
                    @error('secret_key')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Merchant Email -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Merchant Email *
                    </label>
                    <input type="email" 
                           name="merchant_email" 
                           value="{{ old('merchant_email', $merchant_email) }}"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-indigo-500 transition-colors @error('merchant_email') border-red-500 @enderror" 
                           placeholder="merchant@example.com"
                           required>
                    <p class="mt-1 text-xs text-gray-500">The email address associated with your Paystack account</p>
                    @error('merchant_email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Webhook URL -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Webhook URL
                    </label>
                    <input type="url" 
                           name="webhook_url" 
                           value="{{ old('webhook_url', $webhook_url) }}"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-indigo-500 transition-colors @error('webhook_url') border-red-500 @enderror" 
                           placeholder="https://yoursite.com/api/paystack/webhook">
                    <p class="mt-1 text-xs text-gray-500">URL where Paystack will send payment notifications. Configure this in your Paystack dashboard.</p>
                    @error('webhook_url')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Test Mode -->
                <div>
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               name="test_mode" 
                               value="1"
                               {{ old('test_mode', $test_mode) == '1' ? 'checked' : '' }}
                               class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-3 text-sm font-semibold text-gray-700">Enable Test Mode</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500 ml-8">When enabled, uses test keys (pk_test_/sk_test_). Disable for production (pk_live_/sk_live_).</p>
                </div>

                <!-- Enabled -->
                <div>
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               name="enabled" 
                               value="1"
                               {{ old('enabled', $enabled) == '1' ? 'checked' : '' }}
                               class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-3 text-sm font-semibold text-gray-700">Enable Paystack</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500 ml-8">When enabled, Paystack will be available as a payment option.</p>
                </div>

                <!-- Info Note -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>Note:</strong> Get your API keys from your <a href="https://dashboard.paystack.com/#/settings/developer" target="_blank" class="underline">Paystack Dashboard</a>. 
                                Make sure to use test keys for development and live keys for production.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                    <a href="{{ route('superadmin.settings.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">
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
    
    if (passwordInput.type === 'password' || passwordInput.type === 'text') {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        toggleIcon.classList.toggle('fa-eye', !isPassword);
        toggleIcon.classList.toggle('fa-eye-slash', isPassword);
    }
}
</script>
@endsection


