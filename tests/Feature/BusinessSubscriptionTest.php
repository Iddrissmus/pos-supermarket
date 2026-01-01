<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\BusinessSignupRequest;
use App\Models\User;
use App\Models\Business;
use Illuminate\Support\Facades\Http;
use Mockery;

class BusinessSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_shows_pricing()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Starter');
        $response->assertSee('Growth');
        $response->assertSee('Enterprise');
    }

    public function test_signup_redirects_to_paystack()
    {
        // Mock Paystack Service
        $this->mock(\App\Services\PaystackService::class, function ($mock) {
            $mock->shouldReceive('initializeTransaction')
                ->once()
                ->andReturn([
                    'status' => true,
                    'data' => ['authorization_url' => 'https://checkout.paystack.com/test-url']
                ]);
        });

        $data = [
            'business_name' => 'Test Biz',
            'owner_name' => 'Owner Test',
            'owner_email' => 'owner@test.com',
            'owner_phone' => '0240000000',
            'branch_name' => 'Main Branch',
            'address' => 'Accra',
            'region' => 'Greater Accra',
            'plan_type' => 'starter',
        ];

        $response = $this->post(route('business-signup.store'), $data);

        $response->assertRedirect('https://checkout.paystack.com/test-url');
        
        $this->assertDatabaseHas('business_signup_requests', [
            'owner_email' => 'owner@test.com',
            'status' => 'pending_payment',
            'plan_type' => 'starter',
            'amount_paid' => 1 // Starter price
        ]);
    }

    public function test_callback_verifies_payment_and_creates_business()
    {
        // Create a pending request
        $request = BusinessSignupRequest::create([
            'business_name' => 'Callback Biz',
            'owner_name' => 'Callback Owner',
            'owner_email' => 'callback@test.com',
            'owner_phone' => '0500000000',
            'branch_name' => 'Callback Branch',
            'address' => 'Kumasi',
            'region' => 'Ashanti',
            'status' => 'pending_payment',
            'plan_type' => 'growth',
            'amount_paid' => 3
        ]);

        // Mock Paystack Verify
        $this->mock(\App\Services\PaystackService::class, function ($mock) {
            $mock->shouldReceive('verifyTransaction')
                ->with('REF_123')
                ->once()
                ->andReturn([
                    'status' => true,
                    'data' => ['status' => 'success']
                ]);
        });
        
        // Disable middleware that might block (like auth checks? controller is public but checks login status for redirect)
        // Public controller is fine.

        $response = $this->get(route('business-signup.callback', [
            'reference' => 'REF_123',
            'request_id' => $request->id
        ]));

        // Should redirect to business admin dashboard
        $response->assertRedirect(route('dashboard.business-admin'));
        
        // Assert Business Created
        $this->assertDatabaseHas('businesses', [
            'name' => 'Callback Biz',
            'plan_type' => 'growth',
            'max_branches' => 5,
            'status' => 'active'
        ]);

        // Assert User Created
        $this->assertDatabaseHas('users', [
            'email' => 'callback@test.com',
            'role' => 'business_admin'
        ]);
        
        // Assert User Logged In
        $this->assertAuthenticatedAs(User::where('email', 'callback@test.com')->first());
    }
}
