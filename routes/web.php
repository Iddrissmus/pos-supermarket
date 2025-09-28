<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\OwnerDashboardController;
use App\Http\Controllers\Dashboard\ManagerDashboardController;
use App\Http\Controllers\Dashboard\CashierDashboardController;

Route::get('/', function () {
    return view('welcome');
});

// Authentication
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->name('login.post')->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register')->middleware('guest');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post')->middleware('guest');


// Route::apiResource('businesses', BusinessController::class);

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('layouts.admin');
    })->name('layouts.admin');
    
    Route::get('/owner/dashboard', function () {
        return view('layouts.owner');
    })->name('layouts.owner');
    
    Route::get('/manager/dashboard', function () {
        return view('layouts.manager');
    })->name('layouts.manager');
    
    Route::get('/cashier/dashboard', function () {
        return view('layouts.cashier');
    })->name('layouts.cashier');

    Route::get('/productmanager', function () {
        return view('layouts.productman');
    })->name('layouts.productman');

    Route::get('/assign', function () {
        return view('layouts.assign');
    })->name('layouts.assign');

    Route::get('/dashboard', function () {
        try {
            $user = auth()->user();
            if (!$user) {
                return redirect()->route('login');
            }
            
            switch ($user->role) {
                case 'admin':
                    return redirect()->route('layouts.admin');
                case 'owner':
                    return redirect()->route('layouts.owner');
                case 'manager':
                    return redirect()->route('layouts.manager');
                case 'cashier':
                    return redirect()->route('layouts.cashier');
                default:
                    return view('dashboard');
            }
        } catch (\Exception $e) {
            // Log the error
            \Log::error($e->getMessage());
            return response()->view('errors.500', [], 500);
        }
    })->name('dashboard');
    
    Route::get('/product', function () {
        return view('layouts.product');
    })->name('layouts.product');
    
    Route::get('/manage', function () {
        return view('layouts.manage');
    })->name('layouts.manage');
    Route::post('/product', [ProductController::class, 'store'])->name('product.store');
    Route::put('/product/{product}', [ProductController::class, 'update'])->name('product.update');

    // Pending reorder requests for managers
    Route::get('/reorder-requests', [\App\Http\Controllers\ReorderRequestController::class, 'index'])
        ->name('reorder.requests')
        ->middleware('auth');
});