<?php

namespace App\Http\Controllers;

use App\Models\BusinessSignupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GuestBusinessSignupController extends Controller
{
    /**
     * Store a new business signup request from the public landing page.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255'],
            'owner_phone' => ['required', 'string', 'max:30'],
            'branch_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'region' => ['required', 'string', 'max:100'],
            'branch_contact' => ['nullable', 'string', 'max:30'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        try {
            if ($request->hasFile('logo')) {
                $validated['logo'] = $request->file('logo')->store('logos', 'public');
            }

            BusinessSignupRequest::create([
                'business_name' => $validated['business_name'],
                'logo' => $validated['logo'] ?? null,
                'owner_name' => $validated['owner_name'],
                'owner_email' => $validated['owner_email'],
                'owner_phone' => $validated['owner_phone'],
                'branch_name' => $validated['branch_name'],
                'address' => $validated['address'],
                'region' => $validated['region'],
                'branch_contact' => $validated['branch_contact'] ?? null,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'status' => 'pending',
            ]);

            return back()->with('success', 'Your business signup request has been submitted. A system administrator will review and contact you via SMS.');
        } catch (\Throwable $e) {
            Log::error('Guest business signup failed: ' . $e->getMessage());

            return back()
                ->withInput()
                ->withErrors(['general' => 'Failed to submit your request. Please try again later.']);
        }
    }
}





