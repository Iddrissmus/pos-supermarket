<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SMS Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the SMS service using Deywuro API
    |
    */

    'provider' => env('SMS_PROVIDER', 'deywuro'),

    'deywuro' => [
        'base_url' => env('SMS_DEYWURO_BASE_URL', 'https://deywuro.com/api/sms'),
        'username' => env('SMS_DEYWURO_USERNAME'),
        'password' => env('SMS_DEYWURO_PASSWORD'),
        'source' => env('SMS_DEYWURO_SOURCE'),
    ],

    // Enable/disable SMS sending (useful for testing)
    'enabled' => env('SMS_ENABLED', true),

];
