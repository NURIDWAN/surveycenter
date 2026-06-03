<?php

$normalizeGateway = static function (?string $value): string {
    $gateway = strtolower(trim((string) $value));

    return $gateway === 'fastpay' ? 'faspay' : $gateway;
};

$configuredOrder = array_values(array_filter(array_map('trim', explode(',', (string) env('PAYMENT_GATEWAY_ORDER', 'faspay,singapay')))));
$normalizedOrder = [];
$mockMode = env('PAYMENT_MOCK_MODE', false);

foreach ($configuredOrder as $gateway) {
    $normalized = $normalizeGateway($gateway);
    if ($normalized !== '' && !in_array($normalized, $normalizedOrder, true)) {
        $normalizedOrder[] = $normalized;
    }
}

return [
    'mock_mode' => $mockMode,
    'mock_default_status' => env('PAYMENT_MOCK_DEFAULT_STATUS', 'paid'),

    // Unified invoice prefix for all payment gateways (SingaPay, Faspay, etc.)
    'invoice_prefix' => strtoupper(trim((string) env('PAYMENT_INVOICE_PREFIX', 'TRX'))) ?: 'TRX',

    'order' => $normalizedOrder,

    'default' => $normalizeGateway(env('PAYMENT_GATEWAY_DEFAULT', 'faspay')),

    'gateways' => [
        'singapay' => [
            'label' => 'SingaPay',
            'enabled' => env('PAYMENT_GATEWAY_SINGAPAY_ENABLED', true),
            'configured' => $mockMode || (!empty(env('SINGAPAY_API_KEY'))
                && !empty(env('SINGAPAY_CLIENT_ID'))
                && !empty(env('SINGAPAY_CLIENT_SECRET'))
                && !empty(env('SINGAPAY_ACCOUNT_ID'))),
        ],
        'faspay' => [
            'label' => 'Faspay',
            'enabled' => env('PAYMENT_GATEWAY_FASPAY_ENABLED', env('PAYMENT_GATEWAY_FASTPAY_ENABLED', true)),
            'configured' => $mockMode || (!empty(env('FASPAY_MERCHANT_ID'))
                && !empty(env('FASPAY_USER_ID'))
                && !empty(env('FASPAY_PASSWORD'))),
        ],
    ],
];
