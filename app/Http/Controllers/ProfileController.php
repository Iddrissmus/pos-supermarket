<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        return view('profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            // Email validation to ensure uniqueness but ignore current user
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        // We only update name and phone for now as per requirements
        // Email update is included in validation but we might want to restrict it 
        // if the requirement says "change some details" but usually email is identity.
        // However, standard profile updates often allow email changes. 
        // The plan said "Email field read-only". So I should enforce that or just not update it.
        // Let's stick to the plan: "Email field read-only".
        
        $user->forceFill([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
        ])->save();

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }
}
