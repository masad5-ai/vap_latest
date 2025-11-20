<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/products.php';

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

function cart_totals(): array
{
    $items = cart_items();
    $products = index_products_by_id();
    $lines = [];
    $subtotal = 0;
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
    }
    $shipping = $subtotal > 0 ? 7.5 : 0;
    $tax = round($subtotal * 0.08, 2);
    $total = $subtotal + $shipping + $tax;
    return compact('lines', 'subtotal', 'shipping', 'tax', 'total');
}
