<?php
$adminPage = 'dashboard';
require __DIR__ . '/bootstrap.php';
$orders = load_orders();
$products = load_products();
$revenue = array_sum(array_map(fn($o) => $o['totals']['total'] ?? 0, $orders));
$openOrders = count(array_filter($orders, fn($o) => ($o['status'] ?? 'processing') !== 'delivered'));
$lowStock = count(array_filter($products, fn($p) => ($p['stock'] ?? 0) < 10));
$heroMetrics = [
    ['label' => 'Revenue', 'value' => '$' . number_format($revenue, 2), 'hint' => 'Lifetime sales'],
    ['label' => 'Open orders', 'value' => $openOrders, 'hint' => 'Awaiting fulfillment'],
    ['label' => 'Low stock', 'value' => $lowStock, 'hint' => 'Below 10 units'],
];
$quickLinks = [
    ['title' => 'Configure shipping', 'description' => 'Adjust courier labels, ETAs, and prices', 'href' => 'shipping.php'],
    ['title' => 'Payment gateways', 'description' => 'Update merchant IDs and checkout copy', 'href' => 'payments.php'],
    ['title' => 'Messaging stack', 'description' => 'Email + WhatsApp templates and API keys', 'href' => 'notifications.php'],
    ['title' => 'Add new product', 'description' => 'Launch a device, pod, or flavor drop', 'href' => 'products.php'],
    ['title' => 'Invite staff', 'description' => 'Promote team to admin and control access', 'href' => 'users.php'],
];
$defaultShipping = shipping_default_option($settings);
$adminTitle = 'Admin dashboard';
include __DIR__ . '/layout.php';
?>
<section class="section">
    <div class="panel-grid">
        <?php foreach ($heroMetrics as $metric): ?>
            <div class="stat-card">
                <p class="muted"><?= htmlspecialchars($metric['label']) ?></p>
                <h2><?= htmlspecialchars($metric['value']) ?></h2>
                <p class="muted"><?= htmlspecialchars($metric['hint']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<section class="section admin-dual">
    <div class="panel">
        <div class="section-header compact">
            <div>
                <p class="eyebrow">Action board</p>
                <h3>Keep things moving</h3>
            </div>
        </div>
        <div class="stacked">
            <?php foreach ($quickLinks as $link): ?>
                <a class="card compact link-card" href="<?= htmlspecialchars($link['href']) ?>">
                    <div>
                        <strong><?= htmlspecialchars($link['title']) ?></strong>
                        <p class="muted"><?= htmlspecialchars($link['description']) ?></p>
                    </div>
                    <span class="pill">Open</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="panel">
        <div class="section-header compact">
            <div>
                <p class="eyebrow">Shipping defaults</p>
                <h3><?= htmlspecialchars($defaultShipping['label'] ?? 'Express courier') ?></h3>
            </div>
            <a class="button ghost" href="shipping.php">Edit</a>
        </div>
        <p class="muted">ETA <?= htmlspecialchars($defaultShipping['eta'] ?? '24-48h') ?> · <?= htmlspecialchars($defaultShipping['zone'] ?? '') ?></p>
        <p class="muted">Payments: <?= htmlspecialchars(implode(', ', array_keys(array_filter($settings['payments'] ?? [], fn($p) => !empty($p['enabled']))))) ?></p>
    </div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
