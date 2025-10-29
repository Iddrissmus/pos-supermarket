<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
    return view('auth.register');
    }

    /**
     * Handle a registration request to the application.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:business_admin,manager,cashier'],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')],
            'business_id' => ['nullable', Rule::exists('businesses', 'id')],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'business_id' => $data['business_id'] ?? null,
            'branch_id' => $data['branch_id'] ?? null,
        ]);

        Auth::login($user);

        // Redirect based on role
        if ($user->role === 'business_admin') {
            return redirect()->route('dashboard.business-admin');
        } elseif ($user->role === 'manager') {
            return redirect()->route('dashboard.manager');
        } elseif ($user->role === 'cashier') {
            return redirect()->route('dashboard.cashier');
        } else {
            return redirect('/dashboard');
        }
    }
}