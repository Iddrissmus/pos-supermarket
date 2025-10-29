<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\Admin\BranchAssignmentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Dashboard\CashierDashboardController;
use App\Http\Controllers\Dashboard\ManagerDashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductDashboardController;
use App\Http\Controllers\ReorderRequestController;
use App\Http\Controllers\RequestApprovalController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\StockReceiptController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    // If user is logged in, redirect to their dashboard
    if (Auth::check()) {
        $user = Auth::user();
        return match($user->role) {
            'superadmin' => redirect()->route('dashboard.superadmin'),
            'business_admin' => redirect()->route('dashboard.business-admin'),
            'manager' => redirect()->route('dashboard.manager'),
            'cashier' => redirect()->route('dashboard.cashier'),
            default => view('landing')
        };
    }
    return view('landing');
});

// Authentication - Role-specific login pages
Route::middleware('guest')->group(function () {
    // SuperAdmin login
    Route::get('/login/superadmin', [LoginController::class, 'showSuperAdminLoginForm'])->name('login.superadmin');
    Route::post('/login/superadmin', [LoginController::class, 'loginSuperAdmin'])->name('login.superadmin.post');
    
    // Business Admin login
    Route::get('/login/business-admin', [LoginController::class, 'showBusinessAdminLoginForm'])->name('login.business-admin');
    Route::post('/login/business-admin', [LoginController::class, 'loginBusinessAdmin'])->name('login.business-admin.post');
    
    // Manager login
    Route::get('/login/manager', [LoginController::class, 'showManagerLoginForm'])->name('login.manager');
    Route::post('/login/manager', [LoginController::class, 'loginManager'])->name('login.manager.post');
    
    // Cashier login
    Route::get('/login/cashier', [LoginController::class, 'showCashierLoginForm'])->name('login.cashier');
    Route::post('/login/cashier', [LoginController::class, 'loginCashier'])->name('login.cashier.post');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register')->middleware('guest');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post')->middleware('guest');


// Route::apiResource('businesses', BusinessController::class);

Route::middleware('auth')->group(function () {
    // SuperAdmin Dashboard
    Route::middleware('role:superadmin')->group(function () {
        Route::get('/superadmin/dashboard', function () {
            return view('dashboard.superadmin');
        })->name('dashboard.superadmin');
        
        // SuperAdmin can manage all system users
        Route::resource('system-users', \App\Http\Controllers\SuperAdmin\SystemUserController::class);
    });
    
    // Only SuperAdmin can create, edit, update and delete businesses
    Route::middleware('role:superadmin')->group(function () {
        Route::get('businesses/create', [BusinessController::class, 'create'])->name('businesses.create');
        Route::post('businesses', [BusinessController::class, 'store'])->name('businesses.store');
        Route::get('businesses/{business}/edit', [BusinessController::class, 'edit'])->name('businesses.edit');
        Route::put('businesses/{business}', [BusinessController::class, 'update'])->name('businesses.update');
        Route::patch('businesses/{business}', [BusinessController::class, 'update']);
        Route::delete('businesses/{business}', [BusinessController::class, 'destroy'])->name('businesses.destroy');
    });
    
    // Business Admin can only view businesses (index and show)
    Route::middleware('role:superadmin,business_admin')->group(function () {
        Route::get('businesses', [BusinessController::class, 'index'])->name('businesses.index');
        Route::get('businesses/{business}', [BusinessController::class, 'show'])->name('businesses.show');
    });

    // Branch Management (SuperAdmin and Business Admin can manage branches)
    Route::middleware('role:superadmin,business_admin')->group(function () {
        Route::post('branches', [BranchController::class, 'store'])->name('branches.store');
        Route::put('branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::delete('branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');
    });

    // Business Admin Dashboard
    Route::middleware('role:business_admin')->group(function () {
        Route::get('/business-admin/dashboard', function () {
            return view('dashboard.business-admin');
        })->name('dashboard.business-admin');
        
        // My Branch page
        Route::get('/my-branch', function () {
            $user = Auth::user();
            $branch = $user->branch;
            return view('branches.my-branch', compact('branch'));
        })->name('my-branch');
    });

    // Business Admin & Manager shared features
    Route::middleware('role:business_admin,manager')->group(function () {
        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
        Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

        // Customer Management
        Route::resource('customers', CustomerController::class);
        Route::patch('/customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])
            ->name('customers.toggle-status');
        Route::get('/api/customer-data', [CustomerController::class, 'getCustomerData'])
            ->name('api.customer.data');

        Route::get('/productmanager', [ProductDashboardController::class, 'index'])
            ->name('layouts.productman');
        Route::view('/assign', 'layouts.assign')->name('layouts.assign');
        Route::view('/manage', 'layouts.manage')->name('layouts.manage');
    });

    // Business Admin only - Inventory & Product Management
    Route::middleware('role:business_admin')->group(function () {
        // Only Business Admin can manage regular inventory
        Route::resource('stock-receipts', StockReceiptController::class);
        Route::resource('suppliers', SupplierController::class);
        Route::patch('/suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])
            ->name('suppliers.toggle-status');

        // Only Business Admin can add products to the system
        Route::get('/product', [ProductController::class, 'index'])->name('layouts.product');
        Route::get('/product/create', function () {
            try {
                $user = Auth::user();
                $branches = collect();

                // Business Admin can see all branches in their business
                $branches = \App\Models\Branch::with('business:id,name')
                    ->where('business_id', $user->business_id)
                    ->get();

                return view('layouts.product-create', compact('branches'));
            } catch (\Throwable $e) {
                return view('layouts.product-create');
            }
        })->name('product.create');
        Route::post('/product', [ProductController::class, 'store'])->name('product.store');
        Route::put('/product/{product}', [ProductController::class, 'update'])->name('product.update');
    });

    // Business Admin only
    Route::middleware('role:business_admin')->group(function () {
        Route::get('/api/product-info', [StockReceiptController::class, 'getProductInfo'])
            ->name('api.product.info');
        Route::get('/api/current-cost', [StockReceiptController::class, 'getCurrentCost'])
            ->name('api.current.cost');
        Route::get('/admin/branch-assignments', [BranchAssignmentController::class, 'index'])
            ->name('admin.branch-assignments.index');
        Route::post('/admin/branch-assignments', [BranchAssignmentController::class, 'store'])
            ->name('admin.branch-assignments.store');
            
        // Business Admin creates and manages staff
        Route::get('/admin/cashiers', [\App\Http\Controllers\Admin\CashierController::class, 'index'])
            ->name('admin.cashiers.index');
        Route::post('/admin/cashiers/create', [\App\Http\Controllers\Admin\CashierController::class, 'create'])
            ->name('admin.cashiers.create');
        Route::post('/admin/cashiers/assign', [\App\Http\Controllers\Admin\CashierController::class, 'assign'])
            ->name('admin.cashiers.assign');
        Route::post('/admin/cashiers/unassign', [\App\Http\Controllers\Admin\CashierController::class, 'unassign'])
            ->name('admin.cashiers.unassign');
        Route::post('/admin/cashiers/delete', [\App\Http\Controllers\Admin\CashierController::class, 'delete'])
            ->name('admin.cashiers.delete');
            
        // Business Admin approves manager requests
        Route::get('/requests/approval', [RequestApprovalController::class, 'index'])
            ->name('requests.approval.index');
        Route::post('/requests/{stockTransfer}/approve', [RequestApprovalController::class, 'approve'])
            ->name('requests.approve');
        Route::post('/requests/{stockTransfer}/reject', [RequestApprovalController::class, 'reject'])
            ->name('requests.reject');
        
        // Business Admin views business-wide reports (can add more report routes here)
        Route::get('/business-admin/reports', function () {
            return view('reports.business-admin');
        })->name('business-admin.reports');
        
        // Sales Reports for Business Admin
        Route::get('/sales/report', [SalesController::class, 'report'])->name('sales.report');
        Route::get('/sales/export/csv', [SalesController::class, 'exportCsv'])->name('sales.export.csv');
        Route::get('/sales/export/pdf', [SalesController::class, 'exportPdf'])->name('sales.export.pdf');
    });

    // Manager Dashboard - Can view their own dashboard
    Route::middleware('role:manager')->group(function () {
        Route::get('/manager/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard.manager');
    });

    // Manager only features - Day-to-day operations
    Route::middleware('role:manager')->group(function () {
        // Managers handle staff assignments at their branch
        Route::get('/manager/cashiers', [\App\Http\Controllers\Manager\CashierAssignmentController::class, 'index'])
            ->name('manager.cashiers.index');
        Route::post('/manager/cashiers/assign', [\App\Http\Controllers\Manager\CashierAssignmentController::class, 'assign'])
            ->name('manager.cashiers.assign');
        Route::post('/manager/cashiers/unassign', [\App\Http\Controllers\Manager\CashierAssignmentController::class, 'unassign'])
            ->name('manager.cashiers.unassign');
        Route::post('/manager/cashiers/create', [\App\Http\Controllers\Manager\CashierAssignmentController::class, 'create'])
            ->name('manager.cashiers.create');
            
        // Managers can request items from Business Admin
        Route::get('/manager/item-requests', [\App\Http\Controllers\Manager\ItemRequestController::class, 'index'])
            ->name('manager.item-requests.index');
        Route::post('/manager/item-requests', [\App\Http\Controllers\Manager\ItemRequestController::class, 'store'])
            ->name('manager.item-requests.store');
        Route::patch('/manager/item-requests/{stockTransfer}/cancel', [\App\Http\Controllers\Manager\ItemRequestController::class, 'cancel'])
            ->name('manager.item-requests.cancel');
            
        // Manager views reorder requests for their branch
        Route::get('/reorder-requests', [ReorderRequestController::class, 'index'])
            ->name('reorder.requests');
        
        // Manager handles daily sales monitoring (but cannot make sales)
        Route::get('/manager/daily-sales', function () {
            return view('manager.daily-sales');
        })->name('manager.daily-sales');
        
        // Manager can view sales reports for their branch
        Route::get('/manager/sales/report', [SalesController::class, 'report'])->name('manager.sales.report');
        Route::get('/manager/sales/export/csv', [SalesController::class, 'exportCsv'])->name('manager.sales.export.csv');
        Route::get('/manager/sales/export/pdf', [SalesController::class, 'exportPdf'])->name('manager.sales.export.pdf');
    });

    // Cashier only - ONLY role that can make sales
    Route::middleware('role:cashier')->group(function () {
        Route::get('/cashier/dashboard', [CashierDashboardController::class, 'index'])->name('dashboard.cashier');

        // Sales - ONLY cashiers can create sales
        Route::resource('sales', SalesController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('/sales/{sale}/receipt', [SalesController::class, 'receipt'])->name('sales.receipt');
        Route::get('/api/product-stock', [SalesController::class, 'getProductStock'])
            ->name('api.product.stock');
        Route::post('/api/calculate-taxes', [SalesController::class, 'calculateTaxes'])
            ->name('api.calculate.taxes');
        Route::get('/terminal', [SalesController::class, 'terminal'])->name('sales.terminal');
        
        // Note: Cashiers CANNOT access reports - removed these routes:
        // - sales.report
        // - sales.export.csv
        // - sales.export.pdf
    });
});