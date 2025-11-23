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

function shipping_option_cost(array $option, float $subtotal, int $itemCount): float
{
    $shipping = $option['base_rate'] ?? 0;
    if (!empty($option['per_item'])) {
        $shipping += ($option['per_item'] * $itemCount);
    }
    if (isset($option['free_over']) && $subtotal >= $option['free_over']) {
        $shipping = 0;
    }
    return round((float)$shipping, 2);
}

function shipping_quotes(float $subtotal, int $itemCount): array
{
    $settings = load_settings();
    $options = $settings['shipping']['options'] ?? [];
    $quotes = [];
    foreach ($options as $key => $option) {
        if (empty($option['enabled'])) {
            continue;
        }
        $quotes[$key] = [
            'id' => $key,
            'label' => $option['label'],
            'eta' => $option['eta'],
            'zone' => $option['zone'] ?? '',
            'price' => shipping_option_cost($option, $subtotal, $itemCount),
        ];
    }

    return $quotes;
}

function selected_shipping_method(array $quotes = []): ?string
{
    ensure_session_started();
    if (empty($quotes)) {
        $quotes = shipping_quotes(0, 0);
    }
    if (empty($quotes)) {
        return null;
    }
    $default = load_settings()['shipping']['default'] ?? array_key_first($quotes);
    $selected = $_SESSION['shipping_method'] ?? $default;
    if (!isset($quotes[$selected])) {
        $selected = $default;
        $_SESSION['shipping_method'] = $selected;
    }
    return $selected;
}

function set_shipping_method(string $method, array $quotes = []): ?string
{
    ensure_session_started();
    if (empty($quotes)) {
        $quotes = shipping_quotes(0, 0);
    }
    if (isset($quotes[$method])) {
        $_SESSION['shipping_method'] = $method;
        return $method;
    }
    return selected_shipping_method($quotes);
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
            'id' => $id,
            'name' => $product['name'],
            'category' => $product['category'] ?? '',
            'price' => $product['price'],
            'quantity' => $qty,
            'total' => $lineTotal,
        ];
        $subtotal += $lineTotal;
        $itemCount += $qty;
    }
    $quotes = shipping_quotes($subtotal, $itemCount);
    $selectedKey = selected_shipping_method($quotes);
    $selected = $selectedKey && isset($quotes[$selectedKey]) ? $quotes[$selectedKey] : null;
    $shipping = $selected['price'] ?? 0;

    $tax = round($subtotal * 0.08, 2);
    $total = $subtotal + $shipping + $tax;
    $shipping_details = $selected ? array_merge($selected, ['key' => $selectedKey]) : null;

    $quotesOut = array_values($quotes);

    return [
        'lines' => $lines,
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'tax' => $tax,
        'total' => $total,
        'shipping_details' => $shipping_details,
        'quotes' => $quotesOut,
        'item_count' => $itemCount,
    ];
}
