@extends('layouts.app')

@section('title', 'Email & SMTP Configuration')

@section('content')
<div class="min-h-screen bg-gray-100 py-6">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Email & SMTP Configuration</h1>
                    <p class="text-sm text-gray-500 mt-1">Configure email sending settings</p>
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

            <form method="POST" action="{{ route('superadmin.settings.email.update') }}" class="space-y-6">
                @csrf

                <!-- Mail Driver -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Mail Driver *
                    </label>
                    <select name="mail_driver" 
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-purple-500 transition-colors @error('mail_driver') border-red-500 @enderror" 
                            required>
                        <option value="smtp" {{ old('mail_driver', $mail_driver) == 'smtp' ? 'selected' : '' }}>SMTP</option>
                        <option value="mailgun" {{ old('mail_driver', $mail_driver) == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                        <option value="ses" {{ old('mail_driver', $mail_driver) == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                        <option value="postmark" {{ old('mail_driver', $mail_driver) == 'postmark' ? 'selected' : '' }}>Postmark</option>
                        <option value="resend" {{ old('mail_driver', $mail_driver) == 'resend' ? 'selected' : '' }}>Resend</option>
                        <option value="sendmail" {{ old('mail_driver', $mail_driver) == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                        <option value="log" {{ old('mail_driver', $mail_driver) == 'log' ? 'selected' : '' }}>Log (Testing)</option>
                    </select>
                    @error('mail_driver')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- SMTP Host -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        SMTP Host *
                    </label>
                    <input type="text" 
                           name="mail_host" 
                           value="{{ old('mail_host', $mail_host) }}"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-purple-500 transition-colors @error('mail_host') border-red-500 @enderror" 
                           placeholder="smtp.mailtrap.io"
                           required>
                    @error('mail_host')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- SMTP Port -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        SMTP Port *
                    </label>
                    <input type="number" 
                           name="mail_port" 
                           value="{{ old('mail_port', $mail_port) }}"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-purple-500 transition-colors @error('mail_port') border-red-500 @enderror" 
                           placeholder="2525"
                           min="1"
                           max="65535"
                           required>
                    @error('mail_port')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- SMTP Username -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        SMTP Username
                    </label>
                    <input type="text" 
                           name="mail_username" 
                           value="{{ old('mail_username', $mail_username) }}"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-purple-500 transition-colors @error('mail_username') border-red-500 @enderror">
                    @error('mail_username')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- SMTP Password -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        SMTP Password
                    </label>
                    <div class="relative">
                        <input type="password" 
                               name="mail_password" 
                               id="email-password"
                               value="{{ old('mail_password', $mail_password) }}"
                               placeholder="{{ $mail_password ? 'Click eye icon to view' : 'Enter SMTP password (optional)' }}"
                               class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-purple-500 transition-colors @error('mail_password') border-red-500 @enderror">
                        <button type="button" 
                                onclick="togglePassword('email-password', 'email-password-toggle')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                title="Show/Hide password">
                            <i class="fas fa-eye" id="email-password-toggle"></i>
                        </button>
                    </div>
                    @error('mail_password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Encryption -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Encryption
                    </label>
                    <select name="mail_encryption" 
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-purple-500 transition-colors @error('mail_encryption') border-red-500 @enderror">
                        <option value="tls" {{ old('mail_encryption', $mail_encryption) == 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ old('mail_encryption', $mail_encryption) == 'ssl' ? 'selected' : '' }}>SSL</option>
                    </select>
                    @error('mail_encryption')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- From Address -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        From Email Address *
                    </label>
                    <input type="email" 
                           name="mail_from_address" 
                           value="{{ old('mail_from_address', $mail_from_address) }}"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-purple-500 transition-colors @error('mail_from_address') border-red-500 @enderror" 
                           placeholder="noreply@example.com"
                           required>
                    @error('mail_from_address')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- From Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        From Name *
                    </label>
                    <input type="text" 
                           name="mail_from_name" 
                           value="{{ old('mail_from_name', $mail_from_name) }}"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-purple-500 transition-colors @error('mail_from_name') border-red-500 @enderror" 
                           placeholder="POS Supermarket"
                           required>
                    @error('mail_from_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Test Email -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Test Email Configuration</h3>
                    <div class="flex space-x-2">
                        <input type="email" 
                               id="test-email" 
                               placeholder="test@example.com"
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                        <button type="button" 
                                onclick="testEmail()"
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-semibold">
                            Send Test Email
                        </button>
                    </div>
                    <div id="test-email-result" class="mt-2 text-sm hidden"></div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                    <a href="{{ route('superadmin.settings.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-semibold">
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

function testEmail() {
    const email = document.getElementById('test-email').value;
    const resultDiv = document.getElementById('test-email-result');
    
    if (!email) {
        resultDiv.className = 'mt-2 text-sm text-red-600';
        resultDiv.textContent = 'Please enter an email address';
        resultDiv.classList.remove('hidden');
        return;
    }
    
    resultDiv.className = 'mt-2 text-sm text-gray-600';
    resultDiv.textContent = 'Sending test email...';
    resultDiv.classList.remove('hidden');
    
    fetch('{{ route("superadmin.settings.email.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ test_email: email })
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

