@php
    // Redirect to role-specific dashboard
    $user = auth()->user();
    $redirectRoute = match ($user->role) {
        'superadmin' => route('dashboard.superadmin'),
        'business_admin' => route('dashboard.business-admin'),
        'manager' => route('dashboard.manager'),
        'cashier' => route('dashboard.cashier'),
        default => route('login'),
    };
    
    return redirect($redirectRoute);
@endphp 