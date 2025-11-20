<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/cart.php';

function orders_path(): string
{
    return __DIR__ . '/../data/orders.json';
}

function load_orders(): array
{
    return load_json(orders_path());
}

function save_orders(array $orders): void
{
    save_json(orders_path(), $orders);
}

function create_order(array $customer, array $totals, array $shipping): array
{
    $orders = load_orders();
    $order = [
        'id' => strtoupper(substr(generate_id(), 0, 6)),
        'customer' => [
            'id' => $customer['id'] ?? null,
            'name' => $customer['name'] ?? $shipping['name'],
            'email' => $customer['email'] ?? $shipping['email'],
        ],
        'shipping' => $shipping,
        'items' => $totals['lines'],
        'totals' => $totals,
        'status' => 'pending',
        'payment_method' => $shipping['payment_method'] ?? 'custom_gateway',
        'created_at' => date('c'),
    ];
    $orders[] = $order;
    save_orders($orders);
    return $order;
}
