<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the general login form (redirects to role-specific)
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Show SuperAdmin login form
     */
    public function showSuperAdminLoginForm()
    {
        return view('auth.superadmin-login');
    }

    /**
     * Show Business Admin login form
     */
    public function showBusinessAdminLoginForm()
    {
        return view('auth.business-admin-login');
    }

    /**
     * Show Manager login form
     */
    public function showManagerLoginForm()
    {
        return view('auth.manager-login');
    }

    /**
     * Show Cashier login form
     */
    public function showCashierLoginForm()
    {
        return view('auth.cashier-login');
    }

    /**
     * Handle SuperAdmin login
     */
    public function loginSuperAdmin(Request $request)
    {
        return $this->handleLogin($request, 'superadmin', 'dashboard.superadmin');
    }

    /**
     * Handle Business Admin login
     */
    public function loginBusinessAdmin(Request $request)
    {
        return $this->handleLogin($request, 'business_admin', 'dashboard.business-admin');
    }

    /**
     * Handle Manager login
     */
    public function loginManager(Request $request)
    {
        return $this->handleLogin($request, 'manager', 'dashboard.manager');
    }

    /**
     * Handle Cashier login
     */
    public function loginCashier(Request $request)
    {
        return $this->handleLogin($request, 'cashier', 'sales.terminal');
    }

    /**
     * Generic login handler
     */
    private function handleLogin(Request $request, string $expectedRole, string $redirectRoute)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => ['required', 'string'],
        ]);

        // Check if user exists and is active before attempting login
        $user = \App\Models\User::where('email', $credentials['email'])->first();
        
        if ($user) {
            // Check user status
            if ($user->status === 'blocked') {
                ActivityLogger::logFailedLogin($credentials['email']);
                throw ValidationException::withMessages([
                    'email' => ['Your account has been blocked. Please contact the system administrator.'],
                ]);
            }
            
            if ($user->status === 'inactive') {
                ActivityLogger::logFailedLogin($credentials['email']);
                throw ValidationException::withMessages([
                    'email' => ['Your account is inactive. Please contact the system administrator.'],
                ]);
            }
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Verify user has the expected role
            if ($user->role !== $expectedRole) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => ['This account does not have ' . ucfirst(str_replace('_', ' ', $expectedRole)) . ' access.'],
                ]);
            }

            // Role-specific validations
            if ($user->role === 'cashier' && !$user->branch_id) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => ['Your account is not assigned to a branch. Please contact your manager.'],
                ]);
            }

            if ($user->role === 'business_admin' && !$user->business_id) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => ['Your account is not assigned to a business. Please contact the system administrator.'],
                ]);
            }

            // Log successful login
            ActivityLogger::logAuth('login', $user->name . ' logged in as ' . ucfirst(str_replace('_', ' ', $expectedRole)), $user->id);

            return redirect()->route($redirectRoute);
        }

        // Log failed login attempt
        ActivityLogger::logFailedLogin($credentials['email']);

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Handle a general login request (legacy support)
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->role === 'cashier' && !$user->branch_id) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => ['Assign this user to a branch before logging in.'],
                ]);
            }

            // Validate Business Admin has business_id
            if ($user->role === 'business_admin' && !$user->business_id) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => ['This Business Administrator is not assigned to a business.'],
                ]);
            }

            // Log successful login
            ActivityLogger::logAuth('login', $user->name . ' logged in as ' . ucfirst(str_replace('_', ' ', $user->role)), $user->id);

            switch ($user->role) {
                case 'superadmin':
                    return redirect()->route('dashboard.superadmin');
                case 'business_admin':
                    return redirect()->route('dashboard.business-admin');
                case 'manager':
                    return redirect()->route('dashboard.manager');
                case 'cashier':
                    // Redirect cashiers directly to POS terminal
                    return redirect()->route('sales.terminal');
                default:
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    throw ValidationException::withMessages([
                        'email' => ['Your account role is not recognized. Please contact support.'],
                    ]);
            }
        }

        // Log failed login attempt
        ActivityLogger::logFailedLogin($credentials['email']);

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        // Store user info before logging out
        $user = Auth::user();
        $role = $user->role ?? null;
        
        // Log logout activity
        if ($user) {
            ActivityLogger::logAuth('logout', $user->name . ' logged out', $user->id);
        }
        
        Auth::logout();
        
        // Completely destroy the session
        $request->session()->invalidate();
        $request->session()->flush();
        $request->session()->regenerateToken();
        
        // Redirect to role-specific login page
        return match ($role) {
            'superadmin' => redirect()->route('login.superadmin'),
            'business_admin' => redirect()->route('login.business-admin'),
            'manager' => redirect()->route('login.manager'),
            'cashier' => redirect()->route('login.cashier'),
            default => redirect('/'),
        };
    }
}