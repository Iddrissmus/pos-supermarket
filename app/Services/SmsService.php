<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $baseUrl;
    protected $username;
    protected $password;
    protected $source;
    protected $enabled;

    public function __construct()
    {
        $this->baseUrl = config('sms.deywuro.base_url');
        $this->username = config('sms.deywuro.username');
        $this->password = config('sms.deywuro.password');
        $this->source = config('sms.deywuro.source');
        $this->enabled = config('sms.enabled');
    }

    /**
     * Send SMS to a phone number
     * 
     * @param string $phoneNumber Phone number (will be formatted to 0XXXXXXXXX)
     * @param string $message Message content
     * @return bool Success status
     */
    public function sendSms($phoneNumber, $message)
    {
        // Check if SMS is enabled
        if (!$this->enabled) {
            Log::info("SMS disabled. Would have sent to {$phoneNumber}: {$message}");
            return true;
        }

        try {
            // Format phone number - ensure it starts with 233
            $formattedPhone = $this->formatPhoneNumber($phoneNumber);
            
            $response = Http::get($this->baseUrl, [
                'username' => $this->username,
                'password' => $this->password,
                'source' => $this->source,
                'destination' => $formattedPhone,
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info("SMS sent successfully to {$formattedPhone}");
                return true;
            }

            Log::error("SMS failed to {$formattedPhone}: " . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error("SMS Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format phone number - keep it in 0XXXXXXXXX format for Ghana
     * 
     * @param string $phone
     * @return string
     */
    protected function formatPhoneNumber($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If starts with 233, convert to 0XXXXXXXXX
        if (substr($phone, 0, 3) === '233') {
            $phone = '0' . substr($phone, 3);
        }
        
        // If doesn't start with 0, add it
        if (substr($phone, 0, 1) !== '0') {
            $phone = '0' . $phone;
        }
        
        return $phone;
    }

    /**
     * Send welcome SMS with user credentials
     * 
     * @param string $name User's name
     * @param string $email User's email
     * @param string $password Plain text password
     * @param string $role User's role
     * @param string $phoneNumber User's phone number
     * @return bool
     */
    public function sendWelcomeSms($name, $email, $password, $role, $phoneNumber)
    {
        $roleName = ucfirst(str_replace('_', ' ', $role));
        
        $message = "Welcome {$name}!\n\n"
                 . "Your POS account has been created.\n\n"
                 . "Role: {$roleName}\n"
                 . "Email: {$email}\n"
                 . "Password: {$password}\n\n"
                 . "Please login and change your password.\n\n"
                 . "- POS System";

        return $this->sendSms($phoneNumber, $message);
    }
}
