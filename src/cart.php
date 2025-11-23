<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/products.php';
require_once __DIR__ . '/settings.php';

function cart_items(): array
{
    ensure_session_started();
    return $_SESSION['cart'] ?? [];
}

function add_to_cart(string $productId, int $quantity = 1): void
{
    ensure_session_started();
    $cart = $_SESSION['cart'] ?? [];
    $cart[$productId] = ($cart[$productId] ?? 0) + max(1, $quantity);
    $_SESSION['cart'] = $cart;
}

function update_cart_quantity(string $productId, int $quantity): void
{
    ensure_session_started();
    $cart = $_SESSION['cart'] ?? [];
    if ($quantity <= 0) {
        unset($cart[$productId]);
    } else {
        $cart[$productId] = $quantity;
    }
    $_SESSION['cart'] = $cart;
}

function clear_cart(): void
{
    ensure_session_started();
    $_SESSION['cart'] = [];
}

function shipping_options(): array
{
    $settings = load_settings();
    $options = $settings['shipping']['options'] ?? [];

    return array_filter($options, fn($option) => !empty($option['enabled']));
}

function selected_shipping_method(): ?string
{
    ensure_session_started();
    $options = shipping_options();
    if (empty($options)) {
        return null;
    }

    $settings = load_settings();
    $default = $settings['shipping']['default'] ?? array_key_first($options);
    $selected = $_SESSION['shipping_method'] ?? $default;

    if (!isset($options[$selected])) {
        $selected = $default;
        $_SESSION['shipping_method'] = $selected;
    }

    return $selected;
}

function set_shipping_method(string $method): ?string
{
    ensure_session_started();
    $options = shipping_options();

    if (isset($options[$method])) {
        $_SESSION['shipping_method'] = $method;
        return $method;
    }

    return selected_shipping_method();
}

function cart_totals(): array
{
    $items = cart_items();
    $products = index_products_by_id();
    $lines = [];
    $subtotal = 0;
    $itemCount = 0;
    foreach ($items as $id => $qty) {
        if (!isset($products[$id])) {
            continue;
        }
        $product = $products[$id];
        $lineTotal = $product['price'] * $qty;
        $lines[] = [
            'product' => $product,
            'quantity' => $qty,
            'line_total' => $lineTotal,
        ];
        $subtotal += $lineTotal;
        $itemCount += $qty;
    }
    $options = shipping_options();
    $selectedKey = selected_shipping_method();
    $selected = $selectedKey && isset($options[$selectedKey]) ? $options[$selectedKey] : null;

    $shipping = 0;
    if ($selected && $subtotal > 0) {
        $shipping = $selected['base_rate'] ?? 0;
        if (!empty($selected['per_item'])) {
            $shipping += $selected['per_item'] * $itemCount;
        }
        if (isset($selected['free_over']) && $subtotal >= $selected['free_over']) {
            $shipping = 0;
        }
    }

    $tax = round($subtotal * 0.08, 2);
    $total = $subtotal + $shipping + $tax;
    $shipping_details = $selected ? array_merge($selected, ['key' => $selectedKey]) : null;

    return compact('lines', 'subtotal', 'shipping', 'tax', 'total', 'shipping_details');
}
