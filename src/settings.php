<?php
require_once __DIR__ . '/helpers.php';

function settings_path(): string
{
    return __DIR__ . '/../data/settings.json';
}

function default_settings(): array
{
    $config = load_config();
    return [
        'branding' => [
            'store_name' => $config['branding']['store_name'] ?? 'VaporPulse',
            'tagline' => 'Curated vape experiences for modern connoisseurs.',
            'accent' => '#8f5cff',
            'support_email' => $config['branding']['support_email'] ?? 'support@example.com',
            'whatsapp' => $config['branding']['whatsapp'] ?? '',
        ],
        'payments' => [
            'custom_gateway' => [
                'enabled' => true,
                'label' => 'VaporPulse SecurePay',
                'instructions' => 'You will be redirected to our secure gateway to complete payment.',
            ],
            'cod' => [
                'enabled' => true,
                'label' => 'Cash on Delivery',
                'instructions' => 'Pay when your order arrives.',
            ],
        ],
        'notifications' => [
            'email' => [
                'enabled' => true,
                'from' => $config['branding']['support_email'] ?? 'support@example.com',
            ],
            'whatsapp' => [
                'enabled' => true,
                'number' => $config['branding']['whatsapp'] ?? '',
                'signature' => 'Team VaporPulse',
            ],
        ],
    ];
}

function load_settings(): array
{
    $defaults = default_settings();
    $settings = load_json(settings_path());
    return array_replace_recursive($defaults, $settings);
}

function save_settings(array $settings): void
{
    save_json(settings_path(), $settings);
}
