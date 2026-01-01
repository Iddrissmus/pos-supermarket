<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StockReceiptController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\GuestBusinessSignupController;
use App\Http\Controllers\RequestApprovalController;
use App\Http\Controllers\ProductDashboardController;
use App\Http\Controllers\ProductReportController;
use App\Http\Controllers\SuperAdmin\SystemUserController;
use App\Http\Controllers\SuperAdmin\BusinessSignupRequestController;
use App\Http\Controllers\SuperAdmin\LogViewerController;
use App\Http\Controllers\Admin\BranchAssignmentController;
use App\Http\Controllers\Dashboard\CashierDashboardController;
use App\Http\Controllers\Dashboard\ManagerDashboardController;
use App\Http\Controllers\SetupController;

Route::get('/', function () {
    // If user is logged in, redirect to their dashboard
    if (Auth::check()) {
        $user = Auth::user();
        return match($user->role) {
            'superadmin' => redirect()->route('dashboard.superadmin'),
            'business_admin' => redirect()->route('dashboard.business-admin'),
            'manager' => redirect()->route('dashboard.manager'),
            'cashier' => redirect()->route('sales.terminal'),
            default => view('landing')
        };
    }
    return view('landing');
});

Route::middleware('web')->group(function () {
    Route::get('/setup', [SetupController::class, 'show'])->name('setup.show');
    Route::get('/setup/csrf-token', [SetupController::class, 'csrfToken'])->name('setup.csrf-token');
    Route::post('/setup/test-connection', [SetupController::class, 'testConnection'])->name('setup.test-connection');
    Route::post('/setup/import', [SetupController::class, 'import'])->name('setup.import');
    Route::post('/setup/admin-register', [SetupController::class, 'registerAdmin'])->name('setup.admin-register');
});

// Public route: guest business signup from landing page
Route::post('/business-signup', [GuestBusinessSignupController::class, 'store'])
    ->name('business-signup.store');
Route::get('/business-signup/callback', [GuestBusinessSignupController::class, 'callback'])
    ->name('business-signup.callback');

// Public Invoice Routes
Route::get('/pay/{uuid}', [\App\Http\Controllers\PublicInvoiceController::class, 'show'])->name('public.invoice.show');
Route::post('/pay/{uuid}/process', [\App\Http\Controllers\PublicInvoiceController::class, 'pay'])->name('public.invoice.pay');
Route::get('/pay/{uuid}/callback', [\App\Http\Controllers\PublicInvoiceController::class, 'callback'])->name('public.invoice.callback');

// Authentication - Role-specific login pages
Route::middleware('guest')->group(function () {
    // Default login route - redirects to role selection or landing
    Route::get('/login', function () {
        return redirect('/');
    })->name('login');
    
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

Route::middleware('auth')->group(function () {
    // User Profile
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // SuperAdmin Dashboard
    Route::middleware('role:superadmin')->group(function () {
        Route::get('/superadmin/dashboard', function () {
            return view('dashboard.superadmin');
        })->name('dashboard.superadmin');

        Route::get('/superadmin/logs', [LogViewerController::class, 'index'])->name('superadmin.logs');
        Route::get('/superadmin/logs/download', [LogViewerController::class, 'download'])->name('superadmin.logs.download');
        Route::post('/superadmin/logs/clear', [LogViewerController::class, 'clear'])->name('superadmin.logs.clear');
        
        // Settings management
        Route::get('/superadmin/settings', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'index'])->name('superadmin.settings.index');
        Route::get('/superadmin/settings/general', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'general'])->name('superadmin.settings.general');
        Route::post('/superadmin/settings/general', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'updateGeneral'])->name('superadmin.settings.general.update');
        Route::get('/superadmin/settings/sms', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'sms'])->name('superadmin.settings.sms');
        Route::post('/superadmin/settings/sms', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'updateSms'])->name('superadmin.settings.sms.update');
        Route::post('/superadmin/settings/sms/test', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'testSms'])->name('superadmin.settings.sms.test');
        Route::get('/superadmin/settings/email', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'email'])->name('superadmin.settings.email');
        Route::post('/superadmin/settings/email', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'updateEmail'])->name('superadmin.settings.email.update');
        Route::post('/superadmin/settings/email/test', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'testEmail'])->name('superadmin.settings.email.test');

        Route::get('/superadmin/settings/paystack', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'paystack'])->name('superadmin.settings.paystack');
        Route::post('/superadmin/settings/paystack', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'updatePaystack'])->name('superadmin.settings.paystack.update');
        Route::post('/superadmin/settings/paystack/test', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'testPaystack'])->name('superadmin.settings.paystack.test');
        
        // SuperAdmin can manage all system users
        Route::resource('system-users', SystemUserController::class);
        
        // User status management
        Route::post('system-users/{systemUser}/activate', [SystemUserController::class, 'activate'])->name('system-users.activate');
        Route::post('system-users/{systemUser}/deactivate', [SystemUserController::class, 'deactivate'])->name('system-users.deactivate');
        Route::post('system-users/{systemUser}/block', [SystemUserController::class, 'block'])->name('system-users.block');
        
        // Bulk delete unassigned users
        Route::post('system-users/bulk-delete', [SystemUserController::class, 'bulkDestroy'])->name('system-users.bulk-delete');
        
        // Map view of all businesses and branches
        Route::get('/map', [BusinessController::class, 'map'])->name('businesses.map');
        
        // Branch request management
        Route::get('/branch-requests', [\App\Http\Controllers\SuperAdmin\BranchRequestController::class, 'index'])->name('superadmin.branch-requests.index');
        Route::get('/branch-requests/{branchRequest}', [\App\Http\Controllers\SuperAdmin\BranchRequestController::class, 'show'])->name('superadmin.branch-requests.show');
        Route::post('/branch-requests/{branchRequest}/approve', [\App\Http\Controllers\SuperAdmin\BranchRequestController::class, 'approve'])->name('superadmin.branch-requests.approve');
        Route::post('/branch-requests/{branchRequest}/reject', [\App\Http\Controllers\SuperAdmin\BranchRequestController::class, 'reject'])->name('superadmin.branch-requests.reject');

        // Business signup requests (from public landing page)
        Route::get('/business-signup/requests', [BusinessSignupRequestController::class, 'index'])
            ->name('superadmin.business-signup-requests.index');
        Route::get('/business-signup/requests/{businessSignupRequest}', [BusinessSignupRequestController::class, 'show'])
            ->name('superadmin.business-signup-requests.show');
        Route::post('/business-signup/requests/{businessSignupRequest}/approve', [BusinessSignupRequestController::class, 'approve'])
            ->name('superadmin.business-signup-requests.approve');
        Route::post('/business-signup/requests/{businessSignupRequest}/reject', [BusinessSignupRequestController::class, 'reject'])
            ->name('superadmin.business-signup-requests.reject');
    });
    
    // Only SuperAdmin can create, delete businesses and update business details
    Route::middleware('role:superadmin')->group(function () {
        Route::get('businesses/create', [BusinessController::class, 'create'])->name('businesses.create');
        Route::post('businesses', [BusinessController::class, 'store'])->name('businesses.store');
        Route::put('businesses/{business}', [BusinessController::class, 'update'])->name('businesses.update');
        Route::patch('businesses/{business}', [BusinessController::class, 'update']);
        Route::delete('businesses/{business}', [BusinessController::class, 'destroy'])->name('businesses.destroy');
        
        // Business status management
        Route::post('businesses/{business}/activate', [BusinessController::class, 'activate'])->name('businesses.activate');
        Route::post('businesses/{business}/disable', [BusinessController::class, 'disable'])->name('businesses.disable');
        Route::post('businesses/{business}/block', [BusinessController::class, 'block'])->name('businesses.block');
    });
    
    // Both SuperAdmin and Business Admin can view and edit (for branch management)
    Route::middleware('role:superadmin,business_admin')->group(function () {
        Route::get('businesses', [BusinessController::class, 'index'])->name('businesses.index');
        Route::get('businesses/{business}', [BusinessController::class, 'show'])->name('businesses.show');
        Route::get('businesses/{business}/edit', [BusinessController::class, 'edit'])->name('businesses.edit');
    });

    // Branch Management (SuperAdmin and Business Admin can manage branches)
    Route::middleware('role:superadmin,business_admin')->group(function () {
        Route::get('branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('branches/create', [BranchController::class, 'create'])->name('branches.create');
        Route::post('branches', [BranchController::class, 'store'])->name('branches.store');
        Route::get('branches/{branch}', [BranchController::class, 'show'])->name('branches.show');
        Route::get('branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
        Route::put('branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::delete('branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');
        
        // Category Management
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
    });

    // Business Admin Dashboard
    Route::middleware('role:business_admin')->group(function () {
        Route::get('/business-admin/dashboard', [\App\Http\Controllers\Dashboard\BusinessAdminDashboardController::class, 'index'])->name('dashboard.business-admin');
        
        // Settings management
        Route::get('/business-admin/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('business-admin.profile.edit');
        Route::patch('/business-admin/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('business-admin.profile.update');
        
        // Business Management
        Route::get('/my-business', [BusinessController::class, 'myBusiness'])->name('my-business');
        Route::put('/my-business', [BusinessController::class, 'updateMyBusiness'])->name('my-business.update');
        
        // Branch Management
        Route::get('/my-branch', [BranchController::class, 'myBranch'])->name('my-branch');
        Route::put('/my-branch/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::get('/my-branches/map', [BusinessController::class, 'myMap'])->name('businesses.myMap');
        
        // Cashier Management


        // Stock Transfer Requests Approval
        Route::get('/requests/approval', [RequestApprovalController::class, 'index'])->name('requests.approval.index');
        Route::post('/requests/approval/{transfer}/approve', [RequestApprovalController::class, 'approve'])->name('requests.approval.approve');
        Route::post('/requests/approval/{transfer}/reject', [RequestApprovalController::class, 'reject'])->name('requests.approval.reject');
        
        // Branch Assignment
        Route::resource('admin/branch-assignments', BranchAssignmentController::class, ['as' => 'admin']);

        // Invoicing System
        Route::resource('invoices', \App\Http\Controllers\InvoiceController::class);
        Route::post('invoices/{invoice}/send', [\App\Http\Controllers\InvoiceController::class, 'send'])->name('invoices.send');
        Route::get('invoices/{invoice}/download-pdf', [\App\Http\Controllers\InvoiceController::class, 'downloadPdf'])->name('invoices.download-pdf');
    });

    // Super Admin ,  Business Admin & Manager shared features
    Route::middleware('role:superadmin,business_admin,manager')->group(function () {
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

    // Business Admin & SuperAdmin & Manager - Inventory & Product Management
    // Business Admin/SuperAdmin: Full control
    // Manager: Can view and manage local suppliers only
    Route::middleware('role:business_admin,superadmin,manager')->group(function () {
        // Inventory management
        Route::resource('stock-receipts', StockReceiptController::class);
        Route::resource('suppliers', SupplierController::class);
        Route::patch('/suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])
            ->name('suppliers.toggle-status');
        Route::get('/api/product-info', [StockReceiptController::class, 'getProductInfo'])
            ->name('api.product.info');
        Route::get('/api/current-cost', [StockReceiptController::class, 'getCurrentCost'])
            ->name('api.current.cost');

        // Product viewing (Business Admin/SuperAdmin can create, Manager can only view)
        Route::get('/product', [ProductController::class, 'index'])->name('layouts.product');
        Route::get('/product/low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');
        Route::get('/product/in-store', [ProductController::class, 'inStore'])->name('products.in-store');
        Route::get('/product/out-of-stock', [ProductController::class, 'outOfStock'])->name('products.out-of-stock');
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    });
    
    // Business Admin & SuperAdmin ONLY - Product Creation/Editing
    Route::middleware('role:business_admin,superadmin')->group(function () {
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
        
        // Bulk Import & Assignment
        Route::get('/inventory/bulk-import', [ProductController::class, 'showBulkImport'])->name('inventory.bulk-import');
        Route::post('/inventory/bulk-import', [ProductController::class, 'importProducts'])->name('inventory.import');
        Route::get('/inventory/template', [ProductController::class, 'downloadTemplate'])->name('inventory.template');
        
        // Bulk Assignment (Excel Upload)
        Route::get('/inventory/bulk-assignment', [ProductController::class, 'showBulkAssignment'])->name('inventory.bulk-assignment');
        Route::post('/inventory/bulk-assignment', [ProductController::class, 'uploadBulkAssignment'])->name('inventory.bulk-assignment-upload');
        Route::get('/inventory/assignment-template', [ProductController::class, 'downloadAssignmentTemplate'])->name('inventory.assignment-template');
        
        // Manual Assignment (Form)
        Route::get('/inventory/assign', [ProductController::class, 'showAssign'])->name('inventory.assign');
        Route::post('/inventory/bulk-assign', [ProductController::class, 'bulkAssign'])->name('inventory.bulk-assign');
    });

    // Business Admin only
    Route::middleware('role:business_admin')->group(function () {
        Route::get('/admin/branch-assignments', [BranchAssignmentController::class, 'index'])
            ->name('admin.branch-assignments.index');
        Route::post('/admin/branch-assignments', [BranchAssignmentController::class, 'store'])
            ->name('admin.branch-assignments.store');
            
        // Business Admin creates and manages staff
        // Staff Management (Managers & Cashiers)
        Route::get('/admin/staff', [\App\Http\Controllers\Admin\StaffController::class, 'index'])
            ->name('admin.staff.index');
        Route::post('/admin/staff/create', [\App\Http\Controllers\Admin\StaffController::class, 'create'])
            ->name('admin.staff.create');
        Route::post('/admin/staff/assign', [\App\Http\Controllers\Admin\StaffController::class, 'assign'])
            ->name('admin.staff.assign');
        Route::post('/admin/staff/unassign', [\App\Http\Controllers\Admin\StaffController::class, 'unassign'])
            ->name('admin.staff.unassign');
        Route::post('/admin/staff/delete', [\App\Http\Controllers\Admin\StaffController::class, 'delete'])
            ->name('admin.staff.delete');
        
        // Staff status management (Business Admin and SuperAdmin)
        Route::post('/admin/staff/activate', [\App\Http\Controllers\Admin\StaffController::class, 'activate'])
            ->name('admin.staff.activate');
        Route::post('/admin/staff/deactivate', [\App\Http\Controllers\Admin\StaffController::class, 'deactivate'])
            ->name('admin.staff.deactivate');
        Route::post('/admin/staff/block', [\App\Http\Controllers\Admin\StaffController::class, 'block'])
            ->name('admin.staff.block');
    });
    
    // SuperAdmin can also manage staff status
    Route::middleware('role:superadmin')->group(function () {
        Route::post('/admin/staff/activate', [\App\Http\Controllers\Admin\StaffController::class, 'activate'])
            ->name('admin.staff.activate');
        Route::post('/admin/staff/deactivate', [\App\Http\Controllers\Admin\StaffController::class, 'deactivate'])
            ->name('admin.staff.deactivate');
        Route::post('/admin/staff/block', [\App\Http\Controllers\Admin\StaffController::class, 'block'])
            ->name('admin.staff.block');
    });
    
    // Business Admin only
    Route::middleware('role:business_admin')->group(function () {
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
    });
    
    // Sales Reports - Business Admin, SuperAdmin, and Manager can view
    Route::middleware('role:business_admin,superadmin,manager')->group(function () {
        Route::get('/sales/report', [SalesController::class, 'report'])->name('sales.report');
        Route::get('/sales/export/csv', [SalesController::class, 'exportCsv'])->name('sales.export.csv');
        Route::get('/sales/export/pdf', [SalesController::class, 'exportPdf'])->name('sales.export.pdf');
        
        // Product Analytics Reports
        Route::prefix('product-reports')->name('product-reports.')->group(function () {
            Route::get('/', [ProductReportController::class, 'index'])->name('index');
            Route::get('/performance', [ProductReportController::class, 'performance'])->name('performance');
            Route::get('/movement', [ProductReportController::class, 'movement'])->name('movement');
            Route::get('/profitability', [ProductReportController::class, 'profitability'])->name('profitability');
            Route::get('/trends', [ProductReportController::class, 'trends'])->name('trends');
            Route::get('/inventory', [ProductReportController::class, 'inventory'])->name('inventory');
        });

        // Activity Logs (Business Admin only - Security monitoring)
        Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('index');
            Route::get('/{activityLog}', [\App\Http\Controllers\ActivityLogController::class, 'show'])->name('show');
        });
    });

    // Manager Dashboard - Can view their own dashboard
    Route::middleware('role:manager')->group(function () {
        Route::get('/manager/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard.manager');
    });

    // Manager only features - Day-to-day operations
    Route::middleware('role:manager')->group(function () {
        // Managers handle staff assignments at their branch
        Route::get('/manager/cashiers', [\App\Http\Controllers\Manager\StaffAssignmentController::class, 'index'])
            ->name('manager.cashiers.index');
        Route::post('/manager/cashiers/assign', [\App\Http\Controllers\Manager\StaffAssignmentController::class, 'assign'])
            ->name('manager.cashiers.assign');
        Route::post('/manager/cashiers/unassign', [\App\Http\Controllers\Manager\StaffAssignmentController::class, 'unassign'])
            ->name('manager.cashiers.unassign');
        Route::post('/manager/cashiers/create', [\App\Http\Controllers\Manager\StaffAssignmentController::class, 'create'])
            ->name('manager.cashiers.create');
            
        // Managers can request items from Business Admin
        Route::get('/manager/item-requests', [\App\Http\Controllers\Manager\ItemRequestController::class, 'index'])
            ->name('manager.item-requests.index');
        Route::post('/manager/item-requests', [\App\Http\Controllers\Manager\ItemRequestController::class, 'store'])
            ->name('manager.item-requests.store');
        Route::patch('/manager/item-requests/{stockTransfer}/cancel', [\App\Http\Controllers\Manager\ItemRequestController::class, 'cancel'])
            ->name('manager.item-requests.cancel');
        Route::get('/manager/item-requests/download-template', [\App\Http\Controllers\Manager\ItemRequestController::class, 'downloadTemplate'])
            ->name('manager.item-requests.download-template');
        Route::post('/manager/item-requests/bulk-upload', [\App\Http\Controllers\Manager\ItemRequestController::class, 'uploadBulkRequests'])
            ->name('manager.item-requests.bulk-upload');
        
        // Manager handles daily sales monitoring (but cannot make sales)
        Route::get('/manager/daily-sales', function () {
            return view('manager.daily-sales');
        })->name('manager.daily-sales');
        
        // Note: Sales reports are in shared section above (business_admin, superadmin, manager)
        // Note: Suppliers, stock-receipts, and product viewing are in shared section above
            
        // Manager can create products from local suppliers
        Route::get('/manager/local-product/create', [\App\Http\Controllers\Manager\LocalProductController::class, 'create'])
            ->name('manager.local-product.create');
        Route::post('/manager/local-product', [\App\Http\Controllers\Manager\LocalProductController::class, 'store'])
            ->name('manager.local-product.store');
    });

    // Business Admin, Manager & Cashier - All can view sales (filtered by controller)
    Route::middleware('role:business_admin,manager,cashier')->group(function () {
        // View sales (filtered in controller by role: cashiers see own, managers see branch, business admins see all in business)
        Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
        Route::get('/sales/{sale}', [SalesController::class, 'show'])->name('sales.show');
        Route::get('/sales/{sale}/receipt', [SalesController::class, 'receipt'])->name('sales.receipt');
    });

    // Cashier only - ONLY role that can make sales
    Route::middleware('role:cashier')->group(function () {
        Route::get('/cashier/dashboard', [CashierDashboardController::class, 'index'])->name('dashboard.cashier');

        // Sales - ONLY cashiers can create sales through terminal
        // Route::get('/sales/create', [SalesController::class, 'create'])->name('sales.create'); // Disabled - use terminal instead
        Route::post('/sales', [SalesController::class, 'store'])->name('sales.store');
        Route::get('/api/product-stock', [SalesController::class, 'getProductStock'])
            ->name('api.product.stock');
        Route::post('/api/calculate-taxes', [SalesController::class, 'calculateTaxes'])
            ->name('api.calculate.taxes');
        Route::get('/terminal', [SalesController::class, 'terminal'])->name('sales.terminal');
        
        // Cash drawer management
        Route::post('/cash-drawer/open', [SalesController::class, 'openDrawer'])->name('cash-drawer.open');
        Route::post('/cash-drawer/close', [SalesController::class, 'closeDrawer'])->name('cash-drawer.close');
        Route::get('/cash-drawer/status', [SalesController::class, 'getDrawerStatus'])->name('cash-drawer.status');
        
        // Note: Cashiers CANNOT access reports - removed these routes:
        // - sales.report
        // - sales.export.csv
        // - sales.export.pdf
    });
});

