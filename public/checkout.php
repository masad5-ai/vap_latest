<?php
require __DIR__ . '/includes/bootstrap.php';
$page = 'checkout';
$pageTitle = 'Checkout | ' . $settings['branding']['store_name'];
$message = null;
$totals = cart_totals();
$shippingOptions = $totals['quotes'];
$selectedShipping = $totals['shipping_details'] ?? null;
$defaultShipping = shipping_default_option($settings);
$quoteLookup = [];
foreach ($shippingOptions as $quote) {
    $quoteLookup[$quote['id']] = $quote;
}
$selectedMethod = selected_shipping_method($quoteLookup);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (empty($totals['lines'])) {
            throw new RuntimeException('Your cart is empty');
        }
        $quoteLookup = [];
        foreach ($shippingOptions as $quote) {
            $quoteLookup[$quote['id']] = $quote;
        }
        $shippingMethod = set_shipping_method($_POST['shipping_method'] ?? selected_shipping_method($quoteLookup), $quoteLookup);
        $paymentMethod = trim($_POST['payment_method'] ?? 'custom_gateway');
        $paymentOptions = $settings['payments'] ?? [];
        if (!isset($paymentOptions[$paymentMethod]) || empty($paymentOptions[$paymentMethod]['enabled'])) {
            throw new RuntimeException('Selected payment method is unavailable.');
        }
        $totals = cart_totals();
        $shippingOptions = $totals['quotes'];
        $quoteLookup = [];
        foreach ($shippingOptions as $quote) {
            $quoteLookup[$quote['id']] = $quote;
        }
        $selectedMethod = selected_shipping_method($quoteLookup);
        $selectedShipping = $totals['shipping_details'] ?? null;
        $selectedShipping = $shippingMethod && isset($totals['shipping_details']) ? $totals['shipping_details'] : null;
        $shipping = [
            'name' => trim($_POST['name']),
            'email' => trim($_POST['email']),
            'address' => trim($_POST['address']),
            'city' => trim($_POST['city']),
            'payment_method' => $paymentMethod,
            'shipping_method' => $shippingMethod,
            'shipping_label' => $selectedShipping['label'] ?? '',
            'shipping_eta' => $selectedShipping['eta'] ?? '',
            'shipping_cost' => $totals['shipping'],
            'whatsapp_updates' => !empty($_POST['whatsapp_updates']),
        ];
        $order = create_order($user ?? [], $totals, $shipping);
        clear_cart();
        header('Location: /account.php?placed=' . urlencode($order['id']));
        exit;
    } catch (Throwable $e) {
        $message = $e->getMessage();
    }
}
include __DIR__ . '/includes/header.php';
?>
<section class="section checkout-grid">
    <div>
        <p class="eyebrow">Checkout</p>
        <h2>Confirm details</h2>
        <?php if ($message): ?><div class="flash"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <form method="post" class="stacked">
            <label>Name <input required name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>"></label>
            <label>Email <input type="email" required name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>"></label>
            <label>Address <input required name="address" value="<?= htmlspecialchars($user['profile']['address'] ?? '') ?>"></label>
            <label>City <input required name="city" value="<?= htmlspecialchars($user['profile']['city'] ?? '') ?>"></label>
            <label class="pill-row">WhatsApp updates <input type="checkbox" name="whatsapp_updates" <?= !empty($user['profile']['whatsapp_updates']) ? 'checked' : '' ?>></label>
            <label>Shipping method
                <select name="shipping_method">
                    <?php foreach ($shippingOptions as $option): ?>
                        <option value="<?= htmlspecialchars($option['id']) ?>" <?= $selectedMethod === $option['id'] ? 'selected' : '' ?>><?= htmlspecialchars($option['label']) ?> (<?= htmlspecialchars($option['eta']) ?> · $<?= number_format($option['price'], 2) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Payment method
                <select name="payment_method">
                    <?php foreach ($settings['payments'] as $key => $payment): if (empty($payment['enabled'])) continue; ?>
                        <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($payment['label'] ?? ucfirst($key)) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="button primary" type="submit">Place order</button>
        </form>
    </div>
    <aside class="summary">
        <h3>Order summary</h3>
        <?php foreach ($totals['lines'] as $line): ?>
            <div class="mini-line">
                <div>
                    <strong><?= htmlspecialchars($line['name']) ?></strong>
                    <p class="muted">Qty <?= (int)$line['quantity'] ?> · $<?= number_format($line['price'], 2) ?></p>
                </div>
                <span>$<?= number_format($line['total'], 2) ?></span>
            </div>
        <?php endforeach; ?>
        <dl class="totals">
            <div><dt>Subtotal</dt><dd>$<?= number_format($totals['subtotal'], 2) ?></dd></div>
            <div><dt>Shipping</dt><dd>$<?= number_format($totals['shipping'], 2) ?></dd></div>
            <div><dt>Total</dt><dd><strong>$<?= number_format($totals['total'], 2) ?></strong></dd></div>
        </dl>
        <p class="muted">Shipping: <?= htmlspecialchars($selectedShipping['label'] ?? ($defaultShipping['label'] ?? 'Express courier')) ?> · <?= htmlspecialchars($selectedShipping['eta'] ?? ($defaultShipping['eta'] ?? '24-48h')) ?></p>
    </aside>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
