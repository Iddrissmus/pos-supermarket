@extends('layouts.app')

@section('title', 'Email & SMTP Configuration')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Email & SMTP Configuration</h1>
            <p class="mt-2 text-sm text-gray-500">Manage email drivers, SMTP credentials, and sender details.</p>
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
        <form method="POST" action="{{ route('superadmin.settings.email.update') }}" class="space-y-6 p-6 sm:p-8">
            @csrf

            <div class="grid grid-cols-1 gap-y-6 gap-x-8 sm:grid-cols-2">
                <!-- Driver -->
                <div class="sm:col-span-2">
                    <label for="mail_driver" class="block text-sm font-medium text-gray-700">Mail Driver</label>
                    <div class="mt-1">
                        <select id="mail_driver" name="mail_driver" class="tom-select shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg py-3">
                             <option value="smtp" {{ old('mail_driver', $mail_driver) == 'smtp' ? 'selected' : '' }}>SMTP</option>
                            <option value="mailgun" {{ old('mail_driver', $mail_driver) == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                            <option value="ses" {{ old('mail_driver', $mail_driver) == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                            <option value="postmark" {{ old('mail_driver', $mail_driver) == 'postmark' ? 'selected' : '' }}>Postmark</option>
                            <option value="resend" {{ old('mail_driver', $mail_driver) == 'resend' ? 'selected' : '' }}>Resend</option>
                            <option value="sendmail" {{ old('mail_driver', $mail_driver) == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                            <option value="log" {{ old('mail_driver', $mail_driver) == 'log' ? 'selected' : '' }}>Log (Testing)</option>
                        </select>
                    </div>
                    @error('mail_driver') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Host & Port -->
                <div class="sm:col-span-1">
                    <label for="mail_host" class="block text-sm font-medium text-gray-700">SMTP Host</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                         <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm"><i class="fas fa-server"></i></span>
                        </div>
                        <input type="text" name="mail_host" id="mail_host" value="{{ old('mail_host', $mail_host) }}" 
                               class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-lg py-3" 
                               placeholder="smtp.mailtrap.io">
                    </div>
                    @error('mail_host') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-1">
                    <label for="mail_port" class="block text-sm font-medium text-gray-700">SMTP Port</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                         <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm"><i class="fas fa-plug"></i></span>
                        </div>
                        <input type="number" name="mail_port" id="mail_port" value="{{ old('mail_port', $mail_port) }}" 
                               class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-lg py-3" 
                               placeholder="587">
                    </div>
                    @error('mail_port') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Username & Encryption -->
                 <div class="sm:col-span-1">
                    <label for="mail_username" class="block text-sm font-medium text-gray-700">SMTP Username</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                         <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm"><i class="fas fa-user-circle"></i></span>
                        </div>
                        <input type="text" name="mail_username" id="mail_username" value="{{ old('mail_username', $mail_username) }}" 
                               class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-lg py-3">
                    </div>
                    @error('mail_username') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-1">
                    <label for="mail_encryption" class="block text-sm font-medium text-gray-700">Encryption</label>
                    <div class="mt-1">
                        <select id="mail_encryption" name="mail_encryption" class="tom-select shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg py-3">
                            <option value="tls" {{ old('mail_encryption', $mail_encryption) == 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl" {{ old('mail_encryption', $mail_encryption) == 'ssl' ? 'selected' : '' }}>SSL</option>
                            <option value="null" {{ old('mail_encryption', $mail_encryption) == 'null' ? 'selected' : '' }}>None</option>
                        </select>
                    </div>
                    @error('mail_encryption') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Password -->
                 <div class="sm:col-span-2">
                    <label for="mail_password" class="block text-sm font-medium text-gray-700">SMTP Password</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                         <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm"><i class="fas fa-lock"></i></span>
                        </div>
                        <input type="password" name="mail_password" id="mail_password" value="{{ old('mail_password', $mail_password) }}" 
                               class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-lg py-3"
                               placeholder="{{ $mail_password ? '********' : 'Enter SMTP password' }}">
                        <button type="button" onclick="togglePassword('mail_password', 'password-icon')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="password-icon"></i>
                        </button>
                    </div>
                    @error('mail_password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- From Address & Name -->
                <div class="sm:col-span-1">
                    <label for="mail_from_address" class="block text-sm font-medium text-gray-700">From Address</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                         <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm"><i class="fas fa-at"></i></span>
                        </div>
                        <input type="email" name="mail_from_address" id="mail_from_address" value="{{ old('mail_from_address', $mail_from_address) }}" 
                               class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-lg py-3" 
                               placeholder="noreply@example.com">
                    </div>
                    @error('mail_from_address') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                 <div class="sm:col-span-1">
                    <label for="mail_from_name" class="block text-sm font-medium text-gray-700">From Name</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                         <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm"><i class="fas fa-id-card"></i></span>
                        </div>
                        <input type="text" name="mail_from_name" id="mail_from_name" value="{{ old('mail_from_name', $mail_from_name) }}" 
                               class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-lg py-3" 
                               placeholder="POS Notification">
                    </div>
                    @error('mail_from_name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

             <!-- Test Connection -->
             <div class="bg-gray-50 rounded-lg p-6 border border-gray-200 mt-6">
                <h3 class="text-sm font-medium text-gray-900 mb-4">Test Email Configuration</h3>
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <input type="email" id="test-email" placeholder="Recipient Email (e.g. test@example.com)" 
                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg py-2">
                    </div>
                    <button type="button" onclick="testEmail()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        <i class="fas fa-paper-plane mr-2"></i> Send Test
                    </button>
                </div>
                <div id="test-email-result" class="mt-3 text-sm font-medium hidden"></div>
            </div>


            <div class="flex justify-end pt-5">
                <button type="button" onclick="window.location='{{ route('superadmin.settings.index') }}'" class="bg-white py-2 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3">
                    Cancel
                </button>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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

    function testEmail() {
        const email = document.getElementById('test-email').value;
        const resultDiv = document.getElementById('test-email-result');
        
        if (!email) {
            resultDiv.className = 'mt-3 text-sm font-medium text-red-600';
            resultDiv.textContent = 'Please enter an email address.';
            resultDiv.classList.remove('hidden');
            return;
        }
        
        resultDiv.className = 'mt-3 text-sm font-medium text-gray-600';
        resultDiv.textContent = 'Sending...';
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
