@extends('layouts.app')

@section('title', 'Paystack Configuration')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Paystack Configuration</h1>
            <p class="mt-2 text-sm text-gray-500">Configure Paystack payment gateway keys and test connectivity.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
             @if($public_key && $secret_key)
                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    <i class="fas fa-check-circle mr-2"></i> Configured
                </span>
            @else
                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                    <i class="fas fa-circle mr-2 text-gray-400"></i> Not configured
                </span>
            @endif
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
        <form method="POST" action="{{ route('superadmin.settings.paystack.update') }}" class="space-y-6 p-6 sm:p-8" id="paystack-form">
            @csrf

            <div class="grid grid-cols-1 gap-y-6 gap-x-8 sm:grid-cols-1">
                <!-- Public Key -->
                <div>
                    <label for="paystack-public-key" class="block text-sm font-medium text-gray-700">Public Key</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm"><i class="fas fa-key"></i></span>
                        </div>
                        <input type="text" name="public_key" id="paystack-public-key" value="{{ old('public_key', $public_key) }}" 
                               class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-lg py-3 font-mono" 
                               placeholder="pk_test_xxxxxxxxxxxxxxxxxxxxxxxx">
                    </div>
                </div>

                <!-- Secret Key -->
                <div>
                     <label for="paystack-secret-key" class="block text-sm font-medium text-gray-700">Secret Key</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm"><i class="fas fa-lock"></i></span>
                        </div>
                        <input type="password" name="secret_key" id="paystack-secret-key" value="{{ old('secret_key', $secret_key) }}" 
                               class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-lg py-3 font-mono"
                               placeholder="sk_test_...">
                        <button type="button" onclick="togglePassword('paystack-secret-key', 'secret-key-icon')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="secret-key-icon"></i>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Your secret key is stored securely. Never share it publicly.</p>
                </div>
            </div>

            <!-- Connection Test Result -->
            <div id="connection-result" class="hidden rounded-lg p-4 border text-sm"></div>

            <div class="flex justify-end pt-5 space-x-3">
                <button type="button" onclick="window.location='{{ route('superadmin.settings.index') }}'" class="bg-white py-2 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </button>
                 <button type="button" 
                        id="test-connection-btn"
                        class="bg-white py-2 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-plug mr-2"></i> Test Connection
                </button>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-save mr-2"></i> Save Paystack Settings
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

    document.getElementById('test-connection-btn').addEventListener('click', async function() {
        const btn = this;
        const publicKey = document.getElementById('paystack-public-key').value;
        const secretKey = document.getElementById('paystack-secret-key').value;
        const resultDiv = document.getElementById('connection-result');
        
        if (!publicKey || !secretKey) {
            resultDiv.className = 'rounded-lg p-4 border text-sm bg-yellow-50 border-yellow-200 text-yellow-800 mb-4';
            resultDiv.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i> Please enter both keys to test connection';
            resultDiv.classList.remove('hidden');
            return;
        }

        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Testing...';
        btn.disabled = true;
        resultDiv.classList.add('hidden');

        try {
            const response = await fetch("{{ route('superadmin.settings.paystack.test') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({
                    public_key: publicKey,
                    secret_key: secretKey
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                const details = data.details || {};
                resultDiv.className = 'rounded-lg p-4 border text-sm bg-green-50 border-green-200 text-green-900 mb-6';
                resultDiv.innerHTML = `
                    <div class="flex items-start">
                        <i class="fas fa-check-circle mt-0.5 mr-3 text-green-500"></i>
                        <div>
                            <h4 class="font-semibold mb-1">Connection Successful</h4>
                            <p class="mb-2">${data.message}</p>
                            <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-xs text-green-700">
                                <div><span class="font-medium">Mode:</span> ${details.mode || 'N/A'}</div>
                                <div><span class="font-medium">Latency:</span> ${details.latency || 'N/A'}</div>
                                <div><span class="font-medium">Status:</span> ${details.status || 'Active'}</div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                const details = data.details || {};
                resultDiv.className = 'rounded-lg p-4 border text-sm bg-red-50 border-red-200 text-red-900 mb-6';
                let detailHtml = '';
                if (details.status_code) {
                    detailHtml = `<p class="mt-1 text-xs">Status Code: ${details.status_code}</p>`;
                }
                
                resultDiv.innerHTML = `
                    <div class="flex items-start">
                        <i class="fas fa-times-circle mt-0.5 mr-3 text-red-500"></i>
                        <div>
                            <h4 class="font-semibold mb-1">Connection Failed</h4>
                            <p>${data.message}</p>
                            ${detailHtml}
                        </div>
                    </div>
                `;
            }
            resultDiv.classList.remove('hidden');
        } catch (error) {
            console.error(error);
            resultDiv.className = 'rounded-lg p-4 border text-sm bg-red-50 border-red-200 text-red-900 mb-6';
            resultDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i> System error. Please check console or try again.';
            resultDiv.classList.remove('hidden');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });

</script>
@endsection
