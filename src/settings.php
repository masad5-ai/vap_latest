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
            'logo' => $config['branding']['logo'] ?? 'https://images.unsplash.com/photo-1545239351-1141bd82e8a6?auto=format&fit=crop&w=400&q=80',
            'banner' => $config['branding']['banner'] ?? 'https://images.unsplash.com/photo-1523906834658-6e24ef2386f9?auto=format&fit=crop&w=1200&q=80',
            'support_email' => $config['branding']['support_email'] ?? 'support@example.com',
            'support_phone' => $config['branding']['support_phone'] ?? '+61 400 000 000',
            'address' => $config['branding']['address'] ?? 'Vape District, Sydney NSW',
            'hours' => $config['branding']['hours'] ?? 'Daily 9am – 9pm AEST',
            'social' => [
                'instagram' => 'vaporpulse',
                'facebook' => 'vaporpulse',
            ],
            'whatsapp' => $config['branding']['whatsapp'] ?? '',
        ],
        'catalog' => [
            'categories' => [
                ['id' => 'devices', 'name' => 'Devices', 'description' => 'Flagship kits, pens, and disposables.'],
                ['id' => 'pods', 'name' => 'Pods & Coils', 'description' => 'Refills, pods, and mesh coils.'],
                ['id' => 'eliquid', 'name' => 'E-Liquid', 'description' => 'Signature flavors and nic-free blends.'],
                ['id' => 'accessories', 'name' => 'Accessories', 'description' => 'Chargers, cases, and drip tips.'],
            ],
        ],
        'payments' => [
            'custom_gateway' => [
                'enabled' => true,
                'label' => 'VaporPulse SecurePay',
                'instructions' => 'You will be redirected to our secure gateway to complete payment.',
                'merchant_id' => 'vaporpulse-demo',
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
                'smtp_host' => 'smtp.example.com',
                'smtp_user' => 'noreply@example.com',
            ],
            'whatsapp' => [
                'enabled' => true,
                'number' => $config['branding']['whatsapp'] ?? '',
                'signature' => 'Team VaporPulse',
                'api_key' => 'whatsapp-demo-key',
                'template' => 'order_update_v1',
            ],
        ],
        'shipping' => [
            'default' => 'express',
            'options' => [
                'express' => [
                    'enabled' => true,
                    'label' => 'Express Courier',
                    'base_rate' => 14.95,
                    'per_item' => 0.75,
                    'free_over' => 150,
                    'eta' => '1-2 business days',
                    'zone' => 'Metro + Priority',
                ],
                'standard' => [
                    'enabled' => true,
                    'label' => 'Standard Tracked',
                    'base_rate' => 9.95,
                    'per_item' => 0,
                    'free_over' => 90,
                    'eta' => '3-5 business days',
                    'zone' => 'Nationwide',
                ],
                'pickup' => [
                    'enabled' => true,
                    'label' => 'Click & Collect',
                    'base_rate' => 0,
                    'per_item' => 0,
                    'free_over' => 0,
                    'eta' => 'Ready in 2 hours',
                    'zone' => 'Storefront',
                ],
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

function shipping_default_option(?array $settings = null): ?array
{
    $settings = $settings ?? load_settings();
    $options = $settings['shipping']['options'] ?? [];
    $defaultKey = $settings['shipping']['default'] ?? array_key_first($options);
    if ($defaultKey && isset($options[$defaultKey])) {
        return array_merge($options[$defaultKey], ['id' => $defaultKey]);
    }
    return null;
}
