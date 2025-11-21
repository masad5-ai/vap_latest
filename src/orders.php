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
        'notifications' => [
            'whatsapp' => !empty($shipping['whatsapp_updates']),
            'email' => true,
        ],
        'history' => [
            [
                'status' => 'pending',
                'note' => 'Order placed',
                'at' => date('c'),
            ],
        ],
        'created_at' => date('c'),
    ];
    $orders[] = $order;
    save_orders($orders);
    return $order;
}

function update_order_status(string $orderId, string $status, string $actor = 'System'): array
{
    $orders = load_orders();
    foreach ($orders as &$order) {
        if ($order['id'] === $orderId) {
            $order['status'] = $status;
            $order['history'] = array_merge($order['history'] ?? [], [
                [
                    'status' => $status,
                    'note' => 'Updated by ' . $actor,
                    'at' => date('c'),
                ],
            ]);
            save_orders($orders);
            return $order;
        }
    }

    throw new RuntimeException('Order not found');
}
