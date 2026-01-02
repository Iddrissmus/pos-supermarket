<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Business;
use App\Models\Setting;
use App\Notifications\SubscriptionExpiring;
use Illuminate\Support\Facades\Log;
use App\Services\SmsService;

class CheckSubscriptionExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expiring subscriptions and notify business owners';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expiring subscriptions...');
        
        try {
            // Get setting or default to 7 days
            $reminderDays = (int) (Setting::where('key', 'subscription_reminder_days')->value('value') ?? 7);
            
            $targetDate = now()->addDays($reminderDays)->startOfDay();
            $nextTargetDate = now()->addDays($reminderDays)->endOfDay();
            
            // simple check: expires_at between start and end of that target day
            $businesses = Business::whereBetween('subscription_expires_at', [$targetDate, $nextTargetDate])
                ->where('status', 'active')
                ->with('owner') // eager load
                ->get();
    
            $this->info("Found {$businesses->count()} businesses expiring in $reminderDays days.");
    
            foreach ($businesses as $business) {
                $owner = $business->owner;
                if ($owner) {
                    // Send Email Notification
                    try {
                        $owner->notify(new SubscriptionExpiring($business, $reminderDays));
                        $this->info("Notification sent to {$owner->email} for business {$business->name}");
                    } catch (\Exception $e) {
                        Log::error("Failed to send subscription email to {$owner->email}: " . $e->getMessage());
                    }
                    
                    // Send SMS if enabled
                    // Check SMS settings
                    $smsEnabled = Setting::where('key', 'sms_enabled')->value('value') === '1';
                    
                    if ($smsEnabled && $owner->phone) { 
                        try {
                            $smsService = new SmsService(); 
                            $message = "Alert: Your subscription for {$business->name} expires in {$reminderDays} days. Please renew to avoid interruption.";
                            $smsService->sendSms($owner->phone, $message);
                            $this->info("SMS sent to {$owner->phone}");
                        } catch (\Exception $e) {
                             Log::error("Failed to send subscription SMS to {$owner->phone}: " . $e->getMessage());
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            Log::error("Subscription expiry check failed: " . $e->getMessage());
        }
    }
}
