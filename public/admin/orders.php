<?php
$adminPage = 'orders';
require __DIR__ . '/bootstrap.php';
$message = null;
$orders = array_reverse(load_orders());
$statusBuckets = ['pending'=>0,'processing'=>0,'shipped'=>0,'delivered'=>0,'cancelled'=>0];
foreach ($orders as $o) {
    $statusBuckets[$o['status'] ?? 'pending'] = ($statusBuckets[$o['status'] ?? 'pending'] ?? 0) + 1;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $order = update_order_status($_POST['id'], $_POST['status'], $user['name'] ?? 'Admin');
        $message = 'Order #' . $order['id'] . ' updated';
        $orders = array_reverse(load_orders());
    } catch (Throwable $e) {
        $message = $e->getMessage();
    }
}
$adminTitle = 'Orders & fulfillment';
include __DIR__ . '/layout.php';
?>
<section class="section">
    <div class="panel-grid">
        <?php foreach ($statusBuckets as $label => $count): ?>
            <div class="stat-card">
                <p class="muted"><?= ucfirst($label) ?></p>
                <h2><?= $count ?></h2>
                <p class="muted">Orders</p>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<section class="section admin-table">
    <div class="section-header compact">
        <div>
            <p class="eyebrow">Fulfillment board</p>
            <h3>Orders grid</h3>
            <p class="muted">Sortable overview of customers, totals, shipping promises, and live status controls.</p>
        </div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Shipping</th>
                <th>Items</th>
                <th>Total</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><strong>#<?= htmlspecialchars($order['id']) ?></strong></td>
                    <td>
                        <div class="stacked tight">
                            <strong><?= htmlspecialchars($order['customer']['name'] ?? 'Guest') ?></strong>
                            <span class="muted small-text"><?= htmlspecialchars($order['customer']['email'] ?? '') ?></span>
                        </div>
                    </td>
                    <td>
                        <div class="stacked tight">
                            <span class="pill muted"><?= htmlspecialchars($order['shipping']['shipping_label'] ?? '') ?></span>
                            <span class="small-text">ETA <?= htmlspecialchars($order['shipping']['shipping_eta'] ?? '') ?></span>
                        </div>
                    </td>
                    <td><?= count($order['items'] ?? []) ?></td>
                    <td><strong>$<?= number_format($order['totals']['total'], 2) ?></strong></td>
                    <td>
                        <span class="status-pill <?= htmlspecialchars($order['status'] ?? 'pending') ?>"><?= ucfirst($order['status'] ?? 'pending') ?></span>
                    </td>
                    <td>
                        <form method="post" class="table-actions">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($order['id']) ?>">
                            <select name="status">
                                <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $status): ?>
                                    <option value="<?= $status ?>" <?= ($order['status'] ?? 'pending') === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="button small primary" type="submit">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
