@extends('layouts.app')

@section('title', 'Payment Settings')

@section('content')
<div class="min-h-screen bg-gray-100 py-6">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Payment Settings</h1>
                    <p class="text-sm text-gray-500 mt-1">Payment methods configuration</p>
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

            <form method="POST" action="{{ route('superadmin.settings.payment.update') }}" class="space-y-6">
                @csrf

                <!-- Cash Payment -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               name="cash_enabled" 
                               value="1"
                               {{ old('cash_enabled', $cash_enabled) == '1' ? 'checked' : '' }}
                               class="w-5 h-5 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500">
                        <div class="ml-3 flex-1">
                            <span class="text-sm font-semibold text-gray-900">Cash Payment</span>
                            <p class="text-xs text-gray-500 mt-1">Allow customers to pay with cash at the POS terminal</p>
                        </div>
                    </label>
                </div>

                <!-- Card Payment -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               name="card_enabled" 
                               value="1"
                               {{ old('card_enabled', $card_enabled) == '1' ? 'checked' : '' }}
                               class="w-5 h-5 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500">
                        <div class="ml-3 flex-1">
                            <span class="text-sm font-semibold text-gray-900">Card Payment</span>
                            <p class="text-xs text-gray-500 mt-1">Allow customers to pay with debit/credit cards</p>
                        </div>
                    </label>
                </div>

                <!-- Mobile Money Payment -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               name="mobile_money_enabled" 
                               value="1"
                               {{ old('mobile_money_enabled', $mobile_money_enabled) == '1' ? 'checked' : '' }}
                               class="w-5 h-5 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500">
                        <div class="ml-3 flex-1">
                            <span class="text-sm font-semibold text-gray-900">Mobile Money Payment</span>
                            <p class="text-xs text-gray-500 mt-1">Allow customers to pay with mobile money (MTN, Vodafone, AirtelTigo, etc.)</p>
                        </div>
                    </label>
                </div>

                <!-- Info Note -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>Note:</strong> At least one payment method must be enabled. Disabled payment methods will not appear in the POS terminal.
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
                            class="px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 font-semibold">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


