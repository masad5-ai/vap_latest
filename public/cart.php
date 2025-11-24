<?php
require __DIR__ . '/includes/bootstrap.php';
$page = 'cart';
$pageTitle = 'Cart | ' . $settings['branding']['store_name'];
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'add_to_cart') {
            add_to_cart($_POST['product_id'], 1);
            $message = 'Added to cart';
        } elseif ($action === 'update_cart') {
            foreach (($_POST['qty'] ?? []) as $id => $qty) {
                update_cart_quantity($id, (int)$qty);
            }
            $pendingTotals = cart_totals();
            $quoteLookup = [];
            foreach ($pendingTotals['quotes'] as $quote) {
                $quoteLookup[$quote['id']] = $quote;
            }
            if (!empty($_POST['shipping_method'])) {
                set_shipping_method($_POST['shipping_method'], $quoteLookup);
            }
            $message = 'Cart updated';
        } elseif ($action === 'select_shipping') {
            $pendingTotals = cart_totals();
            $quoteLookup = [];
            foreach ($pendingTotals['quotes'] as $quote) {
                $quoteLookup[$quote['id']] = $quote;
            }
            $selectedMethod = set_shipping_method($_POST['shipping_method'] ?? '', $quoteLookup);
            $message = $selectedMethod ? 'Shipping refreshed' : 'Select a shipping option';
        }
    } catch (Throwable $e) {
        $message = $e->getMessage();
    }
}

$totals = cart_totals();
$shippingOptions = $totals['quotes'];
$quoteLookup = [];
foreach ($shippingOptions as $quote) {
    $quoteLookup[$quote['id']] = $quote;
}
$selectedMethod = selected_shipping_method($quoteLookup);
include __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Your bag</p>
            <h2>Cart + shipping calculator</h2>
            <p class="muted">Live shipping costs mirror the Vaperoo flow. Update quantities, swap shipping, then glide into checkout.</p>
        </div>
        <a class="button primary" href="checkout.php">Proceed to checkout</a>
    </div>
    <?php if ($message): ?><div class="flash"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="post" class="cart-grid">
        <input type="hidden" name="action" value="update_cart">
        <div class="cart-lines">
            <?php foreach ($totals['lines'] as $line): ?>
                <article class="card compact">
                    <div>
                        <h4><?= htmlspecialchars($line['name']) ?></h4>
                        <p class="muted">$<?= number_format($line['price'], 2) ?> · <?= htmlspecialchars($line['category'] ?? '') ?></p>
                        <label class="pill muted">Qty <input type="number" name="qty[<?= htmlspecialchars($line['id']) ?>]" value="<?= (int)$line['quantity'] ?>" min="1"></label>
                    </div>
                    <div class="pill-row">
                        <span class="pill">$<?= number_format($line['total'], 2) ?></span>
                        <form method="post" action="cart.php" class="inline-form">
                            <input type="hidden" name="action" value="update_cart">
                            <input type="hidden" name="qty[<?= htmlspecialchars($line['id']) ?>]" value="0">
                            <button class="button ghost" type="submit">Remove</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <aside class="summary">
            <h3>Summary</h3>
            <p class="muted">Calculate shipping then head to checkout.</p>
            <div class="stacked">
                <?php foreach ($shippingOptions as $option): ?>
                    <label class="card radio">
                        <input type="radio" name="shipping_method" value="<?= htmlspecialchars($option['id']) ?>" <?= $selectedMethod === $option['id'] ? 'checked' : '' ?>>
                        <div>
                            <strong><?= htmlspecialchars($option['label']) ?></strong>
                            <p class="muted">ETA <?= htmlspecialchars($option['eta']) ?> · <?= htmlspecialchars($option['zone'] ?? '') ?></p>
                        </div>
                        <span>$<?= number_format($option['price'], 2) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <dl class="totals">
                <div><dt>Subtotal</dt><dd>$<?= number_format($totals['subtotal'], 2) ?></dd></div>
                <div><dt>Shipping</dt><dd>$<?= number_format($totals['shipping'], 2) ?></dd></div>
                <div><dt>Total</dt><dd><strong>$<?= number_format($totals['total'], 2) ?></strong></dd></div>
            </dl>
            <div class="cta-row">
                <button type="submit" class="button ghost">Update cart</button>
                <a class="button primary" href="checkout.php">Checkout</a>
            </div>
        </aside>
    </form>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
