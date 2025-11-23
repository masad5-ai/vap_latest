<?php
require __DIR__ . '/includes/bootstrap.php';
$page = 'home';
$hero = true;
$products = load_products();
$featured = array_slice(array_values(array_filter($products, fn($p) => ($p['status'] ?? 'active') === 'active')), 0, 4);
$pageTitle = $settings['branding']['store_name'] . ' | Modern vape boutique';
include __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Featured</p>
            <h2>Signature drops curated for flavor clarity</h2>
            <p class="muted">Shop the kits and e-liquids our community keeps rebuying. Zero hassle, concierge tracked delivery.</p>
        </div>
        <div class="cta-row">
            <a class="button ghost" href="/shop.php">Browse full catalog</a>
            <a class="button primary" href="/cart.php">View cart</a>
        </div>
    </div>
    <div class="grid grid-4">
        <?php foreach ($featured as $product): ?>
            <article class="card">
                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <div class="card-body">
                    <p class="pill"><?= htmlspecialchars($product['category']) ?></p>
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p><?= htmlspecialchars($product['description']) ?></p>
                    <div class="price-row">
                        <strong>$<?= number_format($product['price'], 2) ?></strong>
                        <span class="pill muted">Stock <?= (int)($product['stock'] ?? 0) ?></span>
                    </div>
                    <form method="post" action="/cart.php" class="inline-form">
                        <input type="hidden" name="action" value="add_to_cart">
                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
                        <button type="submit" class="button ghost">Add to cart</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<section class="section muted-panel">
    <div class="panel-grid">
        <div>
            <p class="eyebrow">Shipping</p>
            <h3><?= htmlspecialchars($settings['shipping']['default_label'] ?? 'Express courier') ?></h3>
            <p>Calculated shipping with transparent tiers and live ETAs. <?= htmlspecialchars($settings['shipping']['default_eta'] ?? '24-48h') ?> dispatch windows for metro areas.</p>
        </div>
        <div>
            <p class="eyebrow">Messaging</p>
            <h3>WhatsApp + email alerts</h3>
            <p>Real-time order updates via WhatsApp, plus rich email confirmations you can configure from the admin console.</p>
        </div>
        <div>
            <p class="eyebrow">Payments</p>
            <h3>Custom gateway ready</h3>
            <p>Use the bespoke gateway or toggle additional rails in Settings. Customer checkout remembers preferences for speed.</p>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
