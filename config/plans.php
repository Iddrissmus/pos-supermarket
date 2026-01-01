<?php

return [
    'starter' => [
        'name' => 'Starter (Single Branch)',
        'price' => 1, // GHS
        'max_branches' => 1,
        'features' => [
            'Single Location',
            'Basic Reporting',
            'Standard Support'
        ]
    ],
    'growth' => [
        'name' => 'Growth (Up to 5 Branches)',
        'price' => 3, 
        'max_branches' => 5,
        'features' => [
            'Up to 5 Locations',
            'Advanced Analytics',
            'Stock Transfers',
            'Priority Support'
        ]
    ],
    'enterprise' => [
        'name' => 'Enterprise (Unlimited)',
        'price' => 10,
        'max_branches' => 999,
        'features' => [
            'Unlimited Locations',
            'Dedicated Account Manager',
            'API Access',
            'Custom Reports'
        ]
    ]
];
