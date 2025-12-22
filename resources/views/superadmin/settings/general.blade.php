@extends('layouts.app')

@section('title', 'General Settings')

@section('content')
<div class="min-h-screen bg-gray-100 py-6">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">General Settings</h1>
                    <p class="text-sm text-gray-500 mt-1">Site name, email, and maintenance mode</p>
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

            @if($maintenance_mode == '1' || $bypass_url)
                <div class="mb-4 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mr-3 mt-0.5"></i>
                        <div class="flex-1">
                            <p class="text-yellow-800 font-semibold mb-2">Maintenance Mode is Active</p>
                            <p class="text-yellow-700 text-sm mb-2">The application is currently in maintenance mode. Regular users cannot access the site.</p>
                            @if($bypass_url)
                                <p class="text-yellow-700 text-sm mb-2 font-medium">Bypass URL (save this):</p>
                                <div class="bg-white border border-yellow-200 rounded p-2 mb-2">
                                    <code class="text-sm text-yellow-900 break-all">{{ $bypass_url }}</code>
                                </div>
                                <p class="text-yellow-600 text-xs">SuperAdmins can access settings directly, but other users will need this URL.</p>
                            @else
                                <p class="text-yellow-600 text-xs">A bypass token will be generated when you save settings with maintenance mode enabled.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('superadmin.settings.general.update') }}" class="space-y-6">
                @csrf

                <!-- Site Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Site Name *
                    </label>
                    <input type="text" 
                           name="site_name" 
                           value="{{ old('site_name', $site_name) }}"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 transition-colors @error('site_name') border-red-500 @enderror" 
                           required>
                    @error('site_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contact Email -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Contact Email *
                    </label>
                    <input type="email" 
                           name="contact_email" 
                           value="{{ old('contact_email', $contact_email) }}"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 transition-colors @error('contact_email') border-red-500 @enderror" 
                           required>
                    @error('contact_email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- App URL -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Application URL *
                    </label>
                    <input type="url" 
                           name="app_url" 
                           value="{{ old('app_url', $app_url) }}"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 transition-colors @error('app_url') border-red-500 @enderror" 
                           placeholder="https://example.com"
                           required>
                    @error('app_url')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Timezone -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Timezone *
                    </label>
                    <select name="timezone" 
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 transition-colors @error('timezone') border-red-500 @enderror" 
                            required>
                        <option value="UTC" {{ old('timezone', $timezone) == 'UTC' ? 'selected' : '' }}>UTC</option>
                        <option value="Africa/Accra" {{ old('timezone', $timezone) == 'Africa/Accra' ? 'selected' : '' }}>Africa/Accra (GMT)</option>
                        <option value="America/New_York" {{ old('timezone', $timezone) == 'America/New_York' ? 'selected' : '' }}>America/New_York (EST)</option>
                        <option value="Europe/London" {{ old('timezone', $timezone) == 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT)</option>
                    </select>
                    @error('timezone')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Locale -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Locale *
                    </label>
                    <select name="locale" 
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 transition-colors @error('locale') border-red-500 @enderror" 
                            required>
                        <option value="en" {{ old('locale', $locale) == 'en' ? 'selected' : '' }}>English</option>
                        <option value="fr" {{ old('locale', $locale) == 'fr' ? 'selected' : '' }}>French</option>
                        <option value="es" {{ old('locale', $locale) == 'es' ? 'selected' : '' }}>Spanish</option>
                    </select>
                    @error('locale')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Currency Symbol -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Currency Symbol *
                    </label>
                    <input type="text" 
                           name="currency_symbol" 
                           value="{{ old('currency_symbol', $currency_symbol) }}"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 transition-colors @error('currency_symbol') border-red-500 @enderror" 
                           placeholder="₵"
                           maxlength="10"
                           required>
                    @error('currency_symbol')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Maintenance Mode -->
                <div>
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               name="maintenance_mode" 
                               value="1"
                               {{ old('maintenance_mode', $maintenance_mode) == '1' ? 'checked' : '' }}
                               class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-3 text-sm font-semibold text-gray-700">Enable Maintenance Mode</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500 ml-8">When enabled, the application will be unavailable to all users except SuperAdmins. You will still be able to access settings to disable it.</p>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                    <a href="{{ route('superadmin.settings.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

