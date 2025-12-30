<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\SmsService;

class SettingsController extends Controller
{
    /**
     * Display a listing of all settings categories.
     */
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        
        // Check which settings are configured
        $configured = [
            'general' => $this->isConfigured('general'),
            'sms' => $this->isConfigured('sms'),
            'email' => $this->isConfigured('email'),
            'paystack' => $this->isConfigured('paystack'),
            'pos' => $this->isConfigured('pos'),
        ];
        
        return view('superadmin.settings.index', compact('configured'));
    }

    /**
     * Show general settings form.
     */
    public function general()
    {
        $settings = Setting::where('group', 'general')->get()->pluck('value', 'key');
        
        // Check if maintenance mode is actually active
        $isMaintenanceActive = file_exists(storage_path('framework/maintenance.php'));
        $maintenanceMode = $settings['maintenance_mode'] ?? '0';
        
        // If maintenance is active but setting says it's off, sync them
        if ($isMaintenanceActive && $maintenanceMode == '0') {
            $maintenanceMode = '1';
        }
        
        // Get bypass token if maintenance is active
        $bypassToken = null;
        $bypassUrl = null;
        if ($isMaintenanceActive || $maintenanceMode == '1') {
            $bypassToken = $settings['maintenance_bypass_token'] ?? null;
            if ($bypassToken) {
                $bypassUrl = url('/') . '?secret=' . $bypassToken;
            }
        }
        
        return view('superadmin.settings.general', [
            'site_name' => $settings['site_name'] ?? config('app.name'),
            'contact_email' => $settings['contact_email'] ?? config('mail.from.address'),
            'app_url' => $settings['app_url'] ?? config('app.url'),
            'timezone' => $settings['timezone'] ?? config('app.timezone', 'UTC'),
            'locale' => $settings['locale'] ?? config('app.locale', 'en'),
            'currency_symbol' => $settings['currency_symbol'] ?? '₵',
            'maintenance_mode' => $maintenanceMode,
            'bypass_url' => $bypassUrl,
        ]);
    }

    /**
     * Update general settings.
     */
    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'app_url' => 'required|url',
            'timezone' => 'required|string|max:50',
            'locale' => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:10',
            'maintenance_mode' => 'nullable|boolean',
        ]);

        Setting::set('site_name', $validated['site_name'], 'general', 'text', 'Application site name');
        Setting::set('contact_email', $validated['contact_email'], 'general', 'text', 'Contact email address');
        Setting::set('app_url', $validated['app_url'], 'general', 'text', 'Application URL');
        Setting::set('timezone', $validated['timezone'], 'general', 'text', 'Application timezone');
        Setting::set('locale', $validated['locale'], 'general', 'text', 'Application locale');
        Setting::set('currency_symbol', $validated['currency_symbol'], 'general', 'text', 'Currency symbol');
        
        // Handle maintenance mode - checkbox may not be in request if unchecked
        $maintenanceMode = isset($validated['maintenance_mode']) && $validated['maintenance_mode'];
        Setting::set('maintenance_mode', $maintenanceMode ? '1' : '0', 'general', 'boolean', 'Maintenance mode status');

        // If maintenance mode changed, update Laravel's maintenance mode
        $bypassToken = null;
        if ($maintenanceMode) {
            // Check if we already have a bypass token stored
            $existingToken = Setting::get('maintenance_bypass_token');
            
            if ($existingToken) {
                // Use existing token
                $bypassToken = $existingToken;
                Artisan::call('down', [
                    '--secret' => $bypassToken,
                    '--render' => 'maintenance'
                ]);
            } else {
                // Generate a new custom secret token for bypassing maintenance mode
                $bypassToken = \Illuminate\Support\Str::random(32);
                Setting::set('maintenance_bypass_token', $bypassToken, 'general', 'text', 'Maintenance mode bypass token');
                Artisan::call('down', [
                    '--secret' => $bypassToken,
                    '--render' => 'maintenance'
                ]);
            }
        } else {
            Artisan::call('up');
            // Optionally clear the bypass token when disabling maintenance mode
            // Setting::where('key', 'maintenance_bypass_token')->delete();
        }

        Log::info('General settings updated', ['updated_by' => Auth::user()?->email ?? 'unknown', 'maintenance_mode' => $maintenanceMode]);

        $message = 'General settings updated successfully!';
        if ($maintenanceMode && $bypassToken) {
            $message = 'General settings updated successfully! Maintenance mode enabled.';
        }

        return redirect()->route('superadmin.settings.general')
            ->with('success', $message)
            ->with('bypass_token', $bypassToken);
    }

    /**
     * Show SMS settings form.
     */
    public function sms()
    {
        $settings = Setting::where('group', 'sms')->get()->pluck('value', 'key');
        
        return view('superadmin.settings.sms', [
            'provider' => $settings['sms_provider'] ?? config('sms.provider', 'deywuro'),
            'base_url' => $settings['sms_base_url'] ?? config('sms.deywuro.base_url'),
            'username' => $settings['sms_username'] ?? config('sms.deywuro.username'),
            'password' => $settings['sms_password'] ?? config('sms.deywuro.password'),
            'source' => $settings['sms_source'] ?? config('sms.deywuro.source'),
            'enabled' => $settings['sms_enabled'] ?? (config('sms.enabled', true) ? '1' : '0'),
        ]);
    }

    /**
     * Update SMS settings.
     */
    public function updateSms(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string|max:50',
            'base_url' => 'required|url',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'source' => 'required|string|max:50',
            'enabled' => 'nullable|boolean',
        ]);

        Setting::set('sms_provider', $validated['provider'], 'sms', 'text', 'SMS provider name');
        Setting::set('sms_base_url', $validated['base_url'], 'sms', 'text', 'SMS API base URL');
        Setting::set('sms_username', $validated['username'], 'sms', 'text', 'SMS API username');
        Setting::set('sms_password', $validated['password'], 'sms', 'text', 'SMS API password');
        Setting::set('sms_source', $validated['source'], 'sms', 'text', 'SMS sender source');
        Setting::set('sms_enabled', isset($validated['enabled']) && $validated['enabled'] ? '1' : '0', 'sms', 'boolean', 'SMS enabled status');

        // Also update .env file if writable
        $this->updateEnvSmsSettings($validated);

        Log::info('SMS settings updated', ['updated_by' => Auth::user()?->email ?? 'unknown']);

        return redirect()->route('superadmin.settings.sms')
            ->with('success', 'SMS settings updated successfully!');
    }

    /**
     * Test SMS configuration.
     */
    public function testSms(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|max:20',
        ]);

        try {
            // Get settings from database
            $settings = Setting::where('group', 'sms')->get()->pluck('value', 'key');
            
            // Create a custom SMS service instance with database settings
            $baseUrl = $settings['sms_base_url'] ?? config('sms.deywuro.base_url');
            $username = $settings['sms_username'] ?? config('sms.deywuro.username');
            $password = $settings['sms_password'] ?? config('sms.deywuro.password');
            $source = $settings['sms_source'] ?? config('sms.deywuro.source');
            $enabled = ($settings['sms_enabled'] ?? config('sms.enabled', true)) == '1';
            
            if (!$enabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'SMS is currently disabled. Please enable it first.',
                ], 422);
            }
            
            // Format phone number
            $phone = preg_replace('/[^0-9]/', '', $validated['phone_number']);
            if (substr($phone, 0, 3) === '233') {
                $phone = '0' . substr($phone, 3);
            }
            if (substr($phone, 0, 1) !== '0') {
                $phone = '0' . $phone;
            }
            
            // Send test SMS
            $response = \Illuminate\Support\Facades\Http::get($baseUrl, [
                'username' => $username,
                'password' => $password,
                'source' => $source,
                'destination' => $phone,
                'message' => 'Test SMS from POS Supermarket. Your SMS configuration is working correctly!',
            ]);

            if ($response->successful()) {
                Log::info("Test SMS sent successfully to {$phone}", ['updated_by' => Auth::user()?->email ?? 'unknown']);
                return response()->json([
                    'success' => true,
                    'message' => 'Test SMS sent successfully!',
                ]);
            } else {
                Log::error("Test SMS failed to {$phone}: " . $response->body());
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send test SMS: ' . $response->body(),
                ], 422);
            }
        } catch (\Exception $e) {
            Log::error('SMS test failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show email settings form.
     */
    public function email()
    {
        $settings = Setting::where('group', 'email')->get()->pluck('value', 'key');
        
        return view('superadmin.settings.email', [
            'mail_driver' => $settings['mail_driver'] ?? config('mail.default', 'smtp'),
            'mail_host' => $settings['mail_host'] ?? config('mail.mailers.smtp.host'),
            'mail_port' => $settings['mail_port'] ?? config('mail.mailers.smtp.port'),
            'mail_username' => $settings['mail_username'] ?? config('mail.mailers.smtp.username'),
            'mail_password' => $settings['mail_password'] ?? config('mail.mailers.smtp.password'),
            'mail_encryption' => $settings['mail_encryption'] ?? 'tls',
            'mail_from_address' => $settings['mail_from_address'] ?? config('mail.from.address'),
            'mail_from_name' => $settings['mail_from_name'] ?? config('mail.from.name'),
        ]);
    }

    /**
     * Update email settings.
     */
    public function updateEmail(Request $request)
    {
        $validated = $request->validate([
            'mail_driver' => 'required|string|in:smtp,mailgun,ses,postmark,resend,sendmail,log,array',
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|in:tls,ssl',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
        ]);

        Setting::set('mail_driver', $validated['mail_driver'], 'email', 'text', 'Mail driver');
        Setting::set('mail_host', $validated['mail_host'], 'email', 'text', 'SMTP host');
        Setting::set('mail_port', $validated['mail_port'], 'email', 'number', 'SMTP port');
        Setting::set('mail_username', $validated['mail_username'] ?? '', 'email', 'text', 'SMTP username');
        Setting::set('mail_password', $validated['mail_password'] ?? '', 'email', 'text', 'SMTP password');
        Setting::set('mail_encryption', $validated['mail_encryption'] ?? 'tls', 'email', 'text', 'SMTP encryption');
        Setting::set('mail_from_address', $validated['mail_from_address'], 'email', 'text', 'From email address');
        Setting::set('mail_from_name', $validated['mail_from_name'], 'email', 'text', 'From name');

        // Also update .env file if writable
        $this->updateEnvEmailSettings($validated);

        Log::info('Email settings updated', ['updated_by' => Auth::user()?->email ?? 'unknown']);

        return redirect()->route('superadmin.settings.email')
            ->with('success', 'Email settings updated successfully!');
    }

    /**
     * Test email configuration.
     */
    public function testEmail(Request $request)
    {
        $validated = $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            Mail::raw('This is a test email from POS Supermarket. Your email configuration is working correctly!', function ($message) use ($validated) {
                $message->to($validated['test_email'])
                        ->subject('Test Email from POS Supermarket');
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully!',
            ]);
        } catch (\Exception $e) {
            Log::error('Email test failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Show Paystack settings form.
     */
    public function paystack()
    {
        $settings = Setting::where('group', 'paystack')->get()->pluck('value', 'key');
        
        return view('superadmin.settings.paystack', [
            'public_key' => $settings['paystack_public_key'] ?? '',
            'secret_key' => $settings['paystack_secret_key'] ?? '',
            'merchant_email' => $settings['paystack_merchant_email'] ?? '',
            'webhook_url' => $settings['paystack_webhook_url'] ?? url('/api/paystack/webhook'),
            'test_mode' => $settings['paystack_test_mode'] ?? '1',
            'enabled' => $settings['paystack_enabled'] ?? '0',
        ]);
    }

    /**
     * Update Paystack settings.
     */
    public function updatePaystack(Request $request)
    {
        $validated = $request->validate([
            'public_key' => 'required|string|max:255',
            'secret_key' => 'required|string|max:255',
        ]);

        Setting::set('paystack_public_key', $validated['public_key'], 'paystack', 'text', 'Paystack public key');
        Setting::set('paystack_secret_key', $validated['secret_key'], 'paystack', 'text', 'Paystack secret key');
        
        // Auto-detect test mode based on key prefix
        $testMode = str_starts_with($validated['public_key'], 'pk_test') || str_starts_with($validated['secret_key'], 'sk_test');
        Setting::set('paystack_test_mode', $testMode ? '1' : '0', 'paystack', 'boolean', 'Paystack test mode');
        
        // Always enable if keys are present (simplified flow)
        $enabled = true;
        Setting::set('paystack_enabled', '1', 'paystack', 'boolean', 'Paystack enabled status');

        // Also update .env file if writable
        $this->updateEnvPaystackSettings([
            'public_key' => $validated['public_key'],
            'secret_key' => $validated['secret_key'],
            'test_mode' => $testMode,
            'enabled' => $enabled,
            // defaulted/removed fields
            'merchant_email' => '', 
            'webhook_url' => url('/api/paystack/webhook'),
        ]);

        Log::info('Paystack settings updated', ['updated_by' => Auth::user()?->email ?? 'unknown']);

        return redirect()->route('superadmin.settings.paystack')
            ->with('success', 'Paystack settings updated successfully!');
    }

    /**
     * Test Paystack connection.
     */
    public function testPaystack(Request $request)
    {
        $validated = $request->validate([
            'public_key' => 'required|string',
            'secret_key' => 'required|string',
        ]);

        try {
            $start = microtime(true);
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $validated['secret_key'],
                'Cache-Control' => 'no-cache',
            ])->get('https://api.paystack.co/bank?perPage=1'); 
            
            $duration = round((microtime(true) - $start) * 1000, 2);

            if ($response->successful()) {
                // Infer mode from keys
                $isTestKey = str_starts_with($validated['public_key'], 'pk_test') || str_starts_with($validated['secret_key'], 'sk_test');
                $mode = $isTestKey ? 'Test Mode' : 'Live Mode';
                
                return response()->json([
                    'success' => true,
                    'message' => 'Connection established successfully.',
                    'details' => [
                        'mode' => $mode,
                        'latency' => $duration . 'ms',
                        'status' => 'Active',
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection failed: ' . $response->json('message', 'Invalid credentials or API error'),
                    'details' => [
                        'latency' => $duration . 'ms',
                        'status_code' => $response->status()
                    ]
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if a settings group is configured.
     */
    private function isConfigured($group)
    {
        $settings = Setting::where('group', $group)->get();
        
        if ($settings->isEmpty()) {
            return false;
        }

        // For each group, check if key settings are filled
        switch ($group) {
            case 'general':
                return $settings->where('key', 'site_name')->whereNotNull('value')->isNotEmpty();
            case 'sms':
                return $settings->whereIn('key', ['sms_username', 'sms_password'])->whereNotNull('value')->count() >= 2;
            case 'email':
                return $settings->whereIn('key', ['mail_host', 'mail_from_address'])->whereNotNull('value')->count() >= 2;
            case 'paystack':
                return $settings->whereIn('key', ['paystack_public_key', 'paystack_secret_key'])->whereNotNull('value')->count() >= 2;
            case 'pos':
                return false; // Not implemented yet
            default:
                return false;
        }
    }

    /**
     * Update email settings in .env file.
     */
    private function updateEnvEmailSettings(array $settings): bool
    {
        $envPath = base_path('.env');
        
        if (!File::exists($envPath) || !File::isWritable($envPath)) {
            Log::warning('.env file not writable, skipping email settings update');
            return false;
        }

        $contents = File::get($envPath);
        
        $envMappings = [
            'MAIL_MAILER' => $settings['mail_driver'] ?? 'smtp',
            'MAIL_HOST' => $settings['mail_host'] ?? '',
            'MAIL_PORT' => $settings['mail_port'] ?? '587',
            'MAIL_USERNAME' => $settings['mail_username'] ?? '',
            'MAIL_PASSWORD' => $settings['mail_password'] ?? '',
            'MAIL_ENCRYPTION' => $settings['mail_encryption'] ?? 'tls',
            'MAIL_FROM_ADDRESS' => $settings['mail_from_address'] ?? '',
            'MAIL_FROM_NAME' => $settings['mail_from_name'] ?? '',
        ];

        foreach ($envMappings as $key => $value) {
            // Escape special characters in value
            $escapedValue = preg_match('/[#\s"\'$]/', $value) ? '"' . addcslashes($value, '"\\') . '"' : $value;
            $line = $key . '=' . $escapedValue;
            
            if (preg_match("/^{$key}=.*$/m", $contents)) {
                $contents = preg_replace("/^{$key}=.*$/m", $line, $contents);
            } else {
                $contents .= PHP_EOL . $line;
            }
        }

        try {
            File::put($envPath, $contents);
            Log::info('Email settings updated in .env file');
            return true;
        } catch (\Throwable $e) {
            Log::warning('.env email settings update failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Update SMS settings in .env file.
     */
    private function updateEnvSmsSettings(array $settings): bool
    {
        $envPath = base_path('.env');
        
        if (!File::exists($envPath) || !File::isWritable($envPath)) {
            Log::warning('.env file not writable, skipping SMS settings update');
            return false;
        }

        $contents = File::get($envPath);
        
        $envMappings = [
            'SMS_PROVIDER' => $settings['provider'] ?? 'deywuro',
            'SMS_BASE_URL' => $settings['base_url'] ?? '',
            'SMS_USERNAME' => $settings['username'] ?? '',
            'SMS_PASSWORD' => $settings['password'] ?? '',
            'SMS_SOURCE' => $settings['source'] ?? '',
            'SMS_ENABLED' => (isset($settings['enabled']) && $settings['enabled']) ? 'true' : 'false',
        ];

        foreach ($envMappings as $key => $value) {
            $escapedValue = preg_match('/[#\s"\'$]/', $value) ? '"' . addcslashes($value, '"\\') . '"' : $value;
            $line = $key . '=' . $escapedValue;
            
            if (preg_match("/^{$key}=.*$/m", $contents)) {
                $contents = preg_replace("/^{$key}=.*$/m", $line, $contents);
            } else {
                $contents .= PHP_EOL . $line;
            }
        }

        try {
            File::put($envPath, $contents);
            Log::info('SMS settings updated in .env file');
            return true;
        } catch (\Throwable $e) {
            Log::warning('.env SMS settings update failed', ['error' => $e->getMessage()]);
            return false;
        }
    }



    /**
     * Update Paystack settings in .env file.
     */
    private function updateEnvPaystackSettings(array $settings): bool
    {
        $envPath = base_path('.env');
        
        if (!File::exists($envPath) || !File::isWritable($envPath)) {
            Log::warning('.env file not writable, skipping Paystack settings update');
            return false;
        }

        $contents = File::get($envPath);
        
        $envMappings = [
            'PAYSTACK_PUBLIC_KEY' => $settings['public_key'] ?? '',
            'PAYSTACK_SECRET_KEY' => $settings['secret_key'] ?? '',
            'PAYSTACK_MERCHANT_EMAIL' => $settings['merchant_email'] ?? '',
            'PAYSTACK_WEBHOOK_URL' => $settings['webhook_url'] ?? '',
            'PAYSTACK_TEST_MODE' => (isset($settings['test_mode']) && $settings['test_mode']) ? 'true' : 'false',
            'PAYSTACK_ENABLED' => (isset($settings['enabled']) && $settings['enabled']) ? 'true' : 'false',
        ];

        foreach ($envMappings as $key => $value) {
            $escapedValue = preg_match('/[#\s"\'$]/', $value) ? '"' . addcslashes($value, '"\\') . '"' : $value;
            $line = $key . '=' . $escapedValue;
            
            if (preg_match("/^{$key}=.*$/m", $contents)) {
                $contents = preg_replace("/^{$key}=.*$/m", $line, $contents);
            } else {
                $contents .= PHP_EOL . $line;
            }
        }

        try {
            File::put($envPath, $contents);
            Log::info('Paystack settings updated in .env file');
            return true;
        } catch (\Throwable $e) {
            Log::warning('.env Paystack settings update failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
