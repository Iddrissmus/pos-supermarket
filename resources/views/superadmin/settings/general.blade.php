@extends('layouts.app')

@section('title', 'General Settings')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">General Settings</h1>
            <p class="mt-2 text-sm text-gray-500">Manage basic application details, localization, and system status.</p>
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
        <form method="POST" action="{{ route('superadmin.settings.general.update') }}" class="space-y-6 p-6 sm:p-8">
            @csrf

            <div class="grid grid-cols-1 gap-y-6 gap-x-8 sm:grid-cols-2">
                <!-- Site Name -->
                <div class="sm:col-span-2">
                    <label for="site_name" class="block text-sm font-medium text-gray-700">Site Name</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm"><i class="fas fa-heading"></i></span>
                        </div>
                        <input type="text" name="site_name" id="site_name" value="{{ old('site_name', $site_name) }}" 
                               class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-lg py-3" 
                               placeholder="e.g. POS Supermarket">
                    </div>
                    @error('site_name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- App URL -->
                <div class="sm:col-span-1">
                    <label for="app_url" class="block text-sm font-medium text-gray-700">Application URL</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm"><i class="fas fa-globe"></i></span>
                        </div>
                        <input type="url" name="app_url" id="app_url" value="{{ old('app_url', $app_url) }}" 
                               class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-lg py-3" 
                               placeholder="https://example.com">
                    </div>
                    @error('app_url') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Contact Email -->
                <div class="sm:col-span-1">
                    <label for="contact_email" class="block text-sm font-medium text-gray-700">Contact Email</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm"><i class="fas fa-envelope"></i></span>
                        </div>
                        <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $contact_email) }}" 
                               class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-lg py-3" 
                               placeholder="admin@example.com">
                    </div>
                    @error('contact_email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Timezone -->
                <div class="sm:col-span-1">
                    <label for="timezone" class="block text-sm font-medium text-gray-700">Timezone</label>
                    <div class="mt-1">
                        <select id="timezone" name="timezone" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg py-3">
                            <option value="UTC" {{ old('timezone', $timezone) == 'UTC' ? 'selected' : '' }}>UTC</option>
                            <option value="Africa/Accra" {{ old('timezone', $timezone) == 'Africa/Accra' ? 'selected' : '' }}>Africa/Accra (GMT)</option>
                            <option value="America/New_York" {{ old('timezone', $timezone) == 'America/New_York' ? 'selected' : '' }}>America/New_York (EST)</option>
                            <option value="Europe/London" {{ old('timezone', $timezone) == 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT)</option>
                        </select>
                    </div>
                    @error('timezone') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Locale -->
                <div class="sm:col-span-1">
                    <label for="locale" class="block text-sm font-medium text-gray-700">Default Language</label>
                    <div class="mt-1">
                        <select id="locale" name="locale" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg py-3">
                            <option value="en" {{ old('locale', $locale) == 'en' ? 'selected' : '' }}>English</option>
                            <option value="fr" {{ old('locale', $locale) == 'fr' ? 'selected' : '' }}>French</option>
                            <option value="es" {{ old('locale', $locale) == 'es' ? 'selected' : '' }}>Spanish</option>
                        </select>
                    </div>
                    @error('locale') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                 <!-- Currency Symbol -->
                 <div class="sm:col-span-2">
                    <label for="currency_symbol" class="block text-sm font-medium text-gray-700">Currency Symbol</label>
                    <div class="mt-1 relative rounded-md shadow-sm max-w-xs">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm"><i class="fas fa-coins"></i></span>
                        </div>
                        <input type="text" name="currency_symbol" id="currency_symbol" value="{{ old('currency_symbol', $currency_symbol) }}" 
                               class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-lg py-3" 
                               placeholder="e.g. ₵">
                    </div>
                    @error('currency_symbol') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Maintenance Mode Section -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200 mt-8">
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="maintenance_mode" name="maintenance_mode" type="checkbox" value="1" {{ old('maintenance_mode', $maintenance_mode) == '1' ? 'checked' : '' }} class="focus:ring-indigo-500 h-5 w-5 text-indigo-600 border-gray-300 rounded">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="maintenance_mode" class="font-medium text-gray-700">Enable Maintenance Mode</label>
                        <p class="text-gray-500">When enabled, only administrators will be able to access the application. A bypass URL will be generated.</p>
                    </div>
                </div>

                @if($maintenance_mode == '1' || $bypass_url)
                    <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <span class="font-medium">Maintenance Mode Active.</span> 
                                    @if($bypass_url)
                                        Use this bypass URL to access the site:
                                        <br>
                                        <code class="bg-yellow-100 px-2 py-1 rounded text-xs break-all mt-1 inline-block select-all">{{ $bypass_url }}</code>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
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
@endsection
