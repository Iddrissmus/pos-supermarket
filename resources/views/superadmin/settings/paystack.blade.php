@extends('layouts.app')

@section('title', 'Paystack Configuration')

@section('content')
<div class="min-h-screen bg-gray-100 py-6">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Settings Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Paystack Configuration</h1>
                <p class="text-sm text-gray-500 mt-1">Paystack payment settings</p>
            </div>
            <div class="flex items-center space-x-2">
                @if($public_key && $secret_key)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-1"></i> Configured
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        <i class="fas fa-circle mr-1 text-gray-400"></i> Not configured
                    </span>
                @endif
                <a href="{{ route('superadmin.settings.index') }}" class="text-gray-600 hover:text-gray-900 ml-4">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <p class="text-green-700 text-sm">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('superadmin.settings.paystack.update') }}" class="space-y-6" id="paystack-form">
                @csrf

                <!-- Public Key -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Paystack Public Key
                    </label>
                    <div class="relative">
                        <input type="text" 
                               name="public_key" 
                               id="paystack-public-key"
                               value="{{ old('public_key', $public_key) }}"
                               placeholder="pk_live_..."
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" 
                               required>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Your Paystack public key (e.g., pk_test_xxxx or pk_live_xxxx)</p>
                </div>

                <!-- Secret Key -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Paystack Secret Key *
                    </label>
                    <div class="relative">
                        <input type="password" 
                               name="secret_key" 
                               id="paystack-secret-key"
                               value="{{ old('secret_key', $secret_key) }}"
                               placeholder="sk_live_..."
                               class="w-full px-4 py-3 pr-12 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" 
                               required>
                        <button type="button" 
                                onclick="togglePassword('paystack-secret-key', 'paystack-secret-key-toggle')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye-slash" id="paystack-secret-key-toggle"></i>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Your Paystack secret key (required for payment verification)</p>
                </div>

                <!-- Connection Test Result -->
                <div id="connection-result" class="hidden rounded-lg p-4 border text-sm"></div>

                <!-- Actions -->
                <div class="pt-4 flex items-center gap-4">
                    <button type="button" 
                            id="test-connection-btn"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center">
                        <i class="fas fa-plug mr-2"></i> Test Connection
                    </button>
                    
                    <button type="submit" 
                            class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors flex items-center">
                        <i class="fas fa-save mr-2"></i> Save Paystack Settings
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
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    }
}

document.getElementById('test-connection-btn').addEventListener('click', async function() {
    const btn = this;
    const publicKey = document.getElementById('paystack-public-key').value;
    const secretKey = document.getElementById('paystack-secret-key').value;
    
    const resultDiv = document.getElementById('connection-result');
    
    if (!publicKey || !secretKey) {
        resultDiv.className = 'rounded-lg p-4 border text-sm bg-yellow-50 border-yellow-200 text-yellow-800';
        resultDiv.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i> Please enter both keys to test connection';
        resultDiv.classList.remove('hidden');
        return;
    }

    // specific UI state
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
            resultDiv.className = 'rounded-lg p-4 border text-sm bg-green-50 border-green-200 text-green-900';
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
            resultDiv.className = 'rounded-lg p-4 border text-sm bg-red-50 border-red-200 text-red-900';
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
        resultDiv.className = 'rounded-lg p-4 border text-sm bg-red-50 border-red-200 text-red-900';
        resultDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i> System error. Please check console or try again.';
        resultDiv.classList.remove('hidden');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
});
</script>
@endsection


