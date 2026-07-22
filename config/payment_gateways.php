<?php

$mockMode = env('PAYMENT_MOCK_MODE', false);

return [
    'mock_mode' => $mockMode,
    'mock_default_status' => env('PAYMENT_MOCK_DEFAULT_STATUS', 'paid'),
    'invoice_prefix' => strtoupper(trim((string) env('PAYMENT_INVOICE_PREFIX', 'TRX'))) ?: 'TRX',

    'order' => ['singapay'],

    'default' => 'singapay',

    'gateways' => [
        'singapay' => [
            'label' => 'SingaPay',
            'enabled' => env('PAYMENT_GATEWAY_SINGAPAY_ENABLED', true),
            'configured' => $mockMode || (
                !empty(env('SINGAPAY_API_KEY'))
                && !empty(env('SINGAPAY_CLIENT_ID'))
                && !empty(env('SINGAPAY_CLIENT_SECRET'))
                && !empty(env('SINGAPAY_ACCOUNT_ID'))
            ),
        ],
    ],
];
