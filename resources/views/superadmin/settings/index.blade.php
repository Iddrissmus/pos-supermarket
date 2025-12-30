@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="min-h-screen bg-gray-100 py-6">
    <div class="max-w-7xl mx-auto px-4 space-y-4">
        <div class="bg-white shadow rounded-lg p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Settings</h1>
            
            @if(session('success'))
                <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <p class="text-green-700 text-sm">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- General Settings -->
                <a href="{{ route('superadmin.settings.general') }}" class="block bg-white border-2 border-gray-200 rounded-lg p-6 hover:border-blue-500 hover:shadow-lg transition-all cursor-pointer">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-cog text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">General Settings</h3>
                                <p class="text-sm text-gray-500">Site name, email, and maintenance mode</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            @if($configured['general'])
                                <span class="w-3 h-3 bg-green-500 rounded-full" title="Configured"></span>
                            @else
                                <span class="w-3 h-3 bg-gray-300 rounded-full" title="Not configured"></span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center text-blue-600 text-sm font-medium">
                        <span>Configure</span>
                        <i class="fas fa-chevron-right ml-2"></i>
                    </div>
                </a>

                <!-- SMS Configuration -->
                <a href="{{ route('superadmin.settings.sms') }}" class="block bg-white border-2 border-gray-200 rounded-lg p-6 hover:border-green-500 hover:shadow-lg transition-all cursor-pointer">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-sms text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">SMS Configuration</h3>
                                <p class="text-sm text-gray-500">Configure SMS sending settings</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            @if($configured['sms'])
                                <span class="w-3 h-3 bg-green-500 rounded-full" title="Configured"></span>
                            @else
                                <span class="w-3 h-3 bg-gray-300 rounded-full" title="Not configured"></span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center text-green-600 text-sm font-medium">
                        <span>Configure</span>
                        <i class="fas fa-chevron-right ml-2"></i>
                    </div>
                </a>

                <!-- Email & SMTP Configuration -->
                <a href="{{ route('superadmin.settings.email') }}" class="block bg-white border-2 border-gray-200 rounded-lg p-6 hover:border-purple-500 hover:shadow-lg transition-all cursor-pointer">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-envelope text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Email & SMTP Configuration</h3>
                                <p class="text-sm text-gray-500">Configure email sending settings</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            @if($configured['email'])
                                <span class="w-3 h-3 bg-green-500 rounded-full" title="Configured"></span>
                            @else
                                <span class="w-3 h-3 bg-gray-300 rounded-full" title="Not configured"></span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center text-purple-600 text-sm font-medium">
                        <span>Configure</span>
                        <i class="fas fa-chevron-right ml-2"></i>
                    </div>
                </a>



                <!-- Paystack Configuration -->
                <a href="{{ route('superadmin.settings.paystack') }}" class="block bg-white border-2 border-gray-200 rounded-lg p-6 hover:border-indigo-500 hover:shadow-lg transition-all cursor-pointer">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-money-bill-wave text-indigo-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Paystack Configuration</h3>
                                <p class="text-sm text-gray-500">Paystack payment gateway settings</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            @if($configured['paystack'])
                                <span class="w-3 h-3 bg-green-500 rounded-full" title="Configured"></span>
                            @else
                                <span class="w-3 h-3 bg-gray-300 rounded-full" title="Not configured"></span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center text-indigo-600 text-sm font-medium">
                        <span>Configure</span>
                        <i class="fas fa-chevron-right ml-2"></i>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

