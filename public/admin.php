<?php
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/products.php';
require_once __DIR__ . '/../src/orders.php';
require_once __DIR__ . '/../src/settings.php';

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
$settings = load_settings();
$users = load_users();

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
        } elseif ($action === 'save_settings') {
            $settings['branding']['store_name'] = trim($_POST['store_name']);
            $settings['branding']['tagline'] = trim($_POST['tagline']);
            $settings['branding']['accent'] = trim($_POST['accent']);
            $settings['branding']['support_email'] = trim($_POST['support_email']);
            $settings['branding']['whatsapp'] = trim($_POST['whatsapp']);
            foreach ($settings['payments'] as $key => &$payment) {
                $payment['enabled'] = isset($_POST['payment'][$key]['enabled']);
                $payment['label'] = trim($_POST['payment'][$key]['label'] ?? $payment['label']);
                $payment['instructions'] = trim($_POST['payment'][$key]['instructions'] ?? $payment['instructions']);
            }
            unset($payment);
            $settings['notifications']['email']['enabled'] = isset($_POST['notifications']['email']);
            $settings['notifications']['email']['from'] = trim($_POST['notifications']['email_from']);
            $settings['notifications']['whatsapp']['enabled'] = isset($_POST['notifications']['whatsapp']);
            $settings['notifications']['whatsapp']['number'] = trim($_POST['notifications']['whatsapp_number']);
            $settings['notifications']['whatsapp']['signature'] = trim($_POST['notifications']['whatsapp_signature']);
            save_settings($settings);
            $message = 'Experience settings updated';
        } elseif ($action === 'update_user_role') {
            $updated = set_user_role($_POST['user_id'], $_POST['role']);
            $users = load_users();
            $message = 'Role updated for ' . $updated['name'];
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
    <style>:root { --accent: <?= htmlspecialchars($settings['branding']['accent']) ?>; }</style>
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
        <section class="panel emphasis">
            <h2>Experience settings</h2>
            <form method="post" class="form-grid">
                <input type="hidden" name="action" value="save_settings">
                <label>Store name<input name="store_name" value="<?= htmlspecialchars($settings['branding']['store_name']) ?>" required></label>
                <label>Tagline<input name="tagline" value="<?= htmlspecialchars($settings['branding']['tagline']) ?>" required></label>
                <label>Accent color<input name="accent" value="<?= htmlspecialchars($settings['branding']['accent']) ?>" required></label>
                <label>Support email<input type="email" name="support_email" value="<?= htmlspecialchars($settings['branding']['support_email']) ?>" required></label>
                <label>WhatsApp number<input name="whatsapp" value="<?= htmlspecialchars($settings['branding']['whatsapp']) ?>"></label>
                <h3>Payments</h3>
                <?php foreach ($settings['payments'] as $key => $payment): ?>
                    <fieldset class="fieldset">
                        <label class="checkbox"><input type="checkbox" name="payment[<?= $key ?>][enabled]" <?= !empty($payment['enabled']) ? 'checked' : '' ?>> Enable <?= htmlspecialchars($payment['label']) ?></label>
                        <label>Display label<input name="payment[<?= $key ?>][label]" value="<?= htmlspecialchars($payment['label']) ?>"></label>
                        <label>Instructions<textarea name="payment[<?= $key ?>][instructions]" rows="2"><?= htmlspecialchars($payment['instructions']) ?></textarea></label>
                    </fieldset>
                <?php endforeach; ?>
                <h3>Notifications</h3>
                <label class="checkbox"><input type="checkbox" name="notifications[email]" <?= !empty($settings['notifications']['email']['enabled']) ? 'checked' : '' ?>> Enable email</label>
                <label>From email<input type="email" name="notifications[email_from]" value="<?= htmlspecialchars($settings['notifications']['email']['from']) ?>"></label>
                <label class="checkbox"><input type="checkbox" name="notifications[whatsapp]" <?= !empty($settings['notifications']['whatsapp']['enabled']) ? 'checked' : '' ?>> Enable WhatsApp alerts</label>
                <label>WhatsApp sender<input name="notifications[whatsapp_number]" value="<?= htmlspecialchars($settings['notifications']['whatsapp']['number']) ?>"></label>
                <label>Signature<input name="notifications[whatsapp_signature]" value="<?= htmlspecialchars($settings['notifications']['whatsapp']['signature']) ?>"></label>
                <button class="button primary" type="submit">Save settings</button>
            </form>
        </section>

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

        <section class="panel">
            <h2>User management</h2>
            <div class="list">
                <?php foreach ($users as $u): ?>
                    <div class="list-item">
                        <div>
                            <strong><?= htmlspecialchars($u['name']) ?></strong>
                            <p class="muted"><?= htmlspecialchars($u['email']) ?></p>
                        </div>
                        <form method="post" class="inline-form">
                            <input type="hidden" name="action" value="update_user_role">
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($u['id']) ?>">
                            <select name="role">
                                <option value="customer" <?= $u['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                            <button class="button ghost" type="submit">Save</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</body>
</html>
