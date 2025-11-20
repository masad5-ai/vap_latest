<?php
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/products.php';
require_once __DIR__ . '/../src/orders.php';

ensure_session_started();
$user = current_user();
if (!$user) {
    header('Location: /index.php?view=auth');
    exit;
}
require_admin();
$message = null;
$products = load_products();
$orders = array_reverse(load_orders());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'create_product') {
            $product = create_product(
                trim($_POST['name']),
                (float)$_POST['price'],
                trim($_POST['description']),
                trim($_POST['category']),
                trim($_POST['image'])
            );
            $message = 'Product ' . $product['name'] . ' created';
            $products = load_products();
        } elseif ($action === 'update_order_status') {
            $orders = load_orders();
            foreach ($orders as &$order) {
                if ($order['id'] === $_POST['order_id']) {
                    $order['status'] = $_POST['status'];
                }
            }
            unset($order);
            save_orders($orders);
            $message = 'Order status updated';
        }
    } catch (Throwable $e) {
        $message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin • VaporPulse</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header class="admin-bar">
        <div>Admin console</div>
        <div class="muted">Signed in as <?= htmlspecialchars($user['name']) ?></div>
    </header>

    <?php if ($message): ?>
        <div class="flash"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <main class="admin-grid">
        <section class="panel">
            <h2>Create product</h2>
            <form method="post" class="form-grid">
                <input type="hidden" name="action" value="create_product">
                <label>Name<input name="name" required></label>
                <label>Price<input type="number" step="0.01" name="price" required></label>
                <label>Category<input name="category" required></label>
                <label>Image URL<input name="image" placeholder="/assets/img/device.png"></label>
                <label>Description<textarea name="description" rows="3"></textarea></label>
                <button class="button primary" type="submit">Create</button>
            </form>
        </section>

        <section class="panel">
            <h2>Products</h2>
            <div class="list">
                <?php foreach ($products as $product): ?>
                    <div class="list-item">
                        <div>
                            <strong><?= htmlspecialchars($product['name']) ?></strong>
                            <p class="muted">$<?= number_format($product['price'], 2) ?> • <?= htmlspecialchars($product['category']) ?></p>
                        </div>
                        <span class="pill">ID <?= htmlspecialchars($product['id']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="panel">
            <h2>Orders</h2>
            <?php if (empty($orders)): ?>
                <p>No orders yet.</p>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-head">
                            <strong>#<?= $order['id'] ?></strong>
                            <form method="post" class="inline-form">
                                <input type="hidden" name="action" value="update_order_status">
                                <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                                <select name="status">
                                    <?php foreach (['pending','paid','fulfilled','cancelled'] as $status): ?>
                                        <option value="<?= $status ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="button ghost" type="submit">Update</button>
                            </form>
                        </div>
                        <p class="muted"><?= htmlspecialchars($order['customer']['name'] ?? 'Guest') ?> — <?= htmlspecialchars($order['shipping']['city']) ?></p>
                        <ul>
                            <?php foreach ($order['items'] as $item): ?>
                                <li><?= htmlspecialchars($item['product']['name']) ?> x <?= $item['quantity'] ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="order-meta">$<?= number_format($order['totals']['total'], 2) ?> • <?= htmlspecialchars($order['payment_method']) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
