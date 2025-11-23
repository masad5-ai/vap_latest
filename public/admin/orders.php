<?php
$adminPage = 'orders';
require __DIR__ . '/bootstrap.php';
$message = null;
$orders = array_reverse(load_orders());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $order = update_order_status($_POST['id'], $_POST['status'], $user['name'] ?? 'Admin');
        $message = 'Order #' . $order['id'] . ' updated';
        $orders = array_reverse(load_orders());
    } catch (Throwable $e) {
        $message = $e->getMessage();
    }
}
$adminTitle = 'Orders';
include __DIR__ . '/layout.php';
?>
<section class="section">
    <div class="section-header compact">
        <div>
            <p class="eyebrow">Orders</p>
            <h3>Track and update</h3>
        </div>
    </div>
    <div class="stacked">
        <?php foreach ($orders as $order): ?>
            <form method="post" class="card compact order-row">
                <input type="hidden" name="id" value="<?= htmlspecialchars($order['id']) ?>">
                <div>
                    <strong>Order #<?= htmlspecialchars($order['id']) ?></strong>
                    <p class="muted">Customer <?= htmlspecialchars($order['customer']['name'] ?? '') ?> · <?= htmlspecialchars($order['shipping']['shipping_label'] ?? '') ?> (<?= htmlspecialchars($order['shipping']['shipping_eta'] ?? '') ?>)</p>
                </div>
                <div class="pill-row">
                    <span class="pill">$<?= number_format($order['totals']['total'], 2) ?></span>
                    <select name="status">
                        <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $status): ?>
                            <option value="<?= $status ?>" <?= ($order['status'] ?? 'pending') === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="button ghost" type="submit">Update</button>
                </div>
            </form>
        <?php endforeach; ?>
    </div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
