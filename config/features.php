<?php

return [
    'groups' => [
        'sales' => [
            'name' => 'Sales & Point of Sale',
            'features' => [
                'pos_interface' => 'POS Interface',
                'sales_history' => 'Sales History',
                'returns_refunds' => 'Returns & Refunds',
                'discounts' => 'Discounts Management',
                'receipt_customization' => 'Receipt Customization',
            ],
        ],
        'inventory' => [
            'name' => 'Inventory Management',
            'features' => [
                'product_management' => 'Product Management',
                'stock_tracking' => 'Stock Quantity Tracking',
                'low_stock_alerts' => 'Low Stock Alerts',
                'stock_transfers' => 'Stock Transfers (Multi-branch)',
                'suppliers' => 'Supplier Management',
                'categories' => 'Categories & Brands',
                'expiry_tracking' => 'Expiry Date Tracking',
                'barcode_generation' => 'Barcode Generation',
            ],
        ],
        'reporting' => [
            'name' => 'Business Intelligence',
            'features' => [
                'sales_reports' => 'Sales Reports',
                'profit_loss' => 'Profit & Loss Analysis',
                'inventory_reports' => 'Inventory Valuation Reports',
                'staff_performance' => 'Staff Performance Tracking',
                'export_data' => 'Export Data (PDF/Excel)',
            ],
        ],
        'multi_branch' => [
            'name' => 'Multi-Branch Operations',
            'features' => [
                'multi_branch_mgmt' => 'Multiple Branch Management',
                'central_inventory' => 'Centralized Inventory View',
                'staff_reassignment' => 'Inter-branch Staff Reassignment',
            ],
        ],
        'users' => [
            'name' => 'User Management',
            'features' => [
                'staff_accounts' => 'Unlimited Staff Accounts',
                'role_permissions' => 'Role-Based Access Control',
                'activity_logs' => 'User Activity Logs',
            ],
        ],
        'support' => [
            'name' => 'Support & Security',
            'features' => [
                'priority_support' => 'Priority Support',
                'dedicated_manager' => 'Dedicated Account Manager',
                'api_access' => 'API Access',
                'daily_backups' => 'Daily Database Backups',
            ],
        ],
    ],
];
