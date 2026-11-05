<?php

/*
|--------------------------------------------------------------------------
| SaaS Plans (single source of truth for seeding)
|--------------------------------------------------------------------------
|
| PlansAndFeaturesSeeder reads this file and idempotently upserts plans,
| prices, and features. Placeholder prices from docs/04-billing.md —
| replace with real values before launch (open decision #8).
|
| Money is integer minor units + ISO currency. Annual = 10x monthly.
| Gateway routing: USD → stripe/paddle, BDT → sslcommerz.
|
*/

return [
    'features' => [
        ['key' => 'max_seats', 'name' => 'Team Seats', 'type' => 'limit', 'unit' => 'seats'],
        ['key' => 'api_access', 'name' => 'API Access', 'type' => 'boolean', 'unit' => null],
        ['key' => 'api_calls_per_month', 'name' => 'API Calls / Month', 'type' => 'metered', 'unit' => 'calls'],
    ],

    'plans' => [
        [
            'name' => 'Free',
            'slug' => 'free',
            'description' => 'Get started for free.',
            'is_public' => true,
            'features' => ['max_seats' => '1', 'api_access' => 'false', 'api_calls_per_month' => '0'],
            'prices' => [],
        ],
        [
            'name' => 'Starter',
            'slug' => 'starter',
            'description' => 'For small teams.',
            'is_public' => true,
            'features' => ['max_seats' => '3', 'api_access' => 'true', 'api_calls_per_month' => '2500'],
            'prices' => [
                ['gateway' => 'stripe', 'interval' => 'month', 'currency' => 'USD', 'amount_minor' => 1200],
                ['gateway' => 'stripe', 'interval' => 'year', 'currency' => 'USD', 'amount_minor' => 12000],
                ['gateway' => 'sslcommerz', 'interval' => 'month', 'currency' => 'BDT', 'amount_minor' => 99000],
                ['gateway' => 'sslcommerz', 'interval' => 'year', 'currency' => 'BDT', 'amount_minor' => 990000],
            ],
        ],
        [
            'name' => 'Pro',
            'slug' => 'pro',
            'description' => 'For growing teams.',
            'is_public' => true,
            'features' => ['max_seats' => '10', 'api_access' => 'true', 'api_calls_per_month' => '25000'],
            'prices' => [
                ['gateway' => 'stripe', 'interval' => 'month', 'currency' => 'USD', 'amount_minor' => 2900],
                ['gateway' => 'stripe', 'interval' => 'year', 'currency' => 'USD', 'amount_minor' => 29000],
                ['gateway' => 'sslcommerz', 'interval' => 'month', 'currency' => 'BDT', 'amount_minor' => 249000],
                ['gateway' => 'sslcommerz', 'interval' => 'year', 'currency' => 'BDT', 'amount_minor' => 2490000],
            ],
        ],
    ],
];
