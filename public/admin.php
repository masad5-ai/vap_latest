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
                trim($_POST['image']),
                (int)($_POST['stock'] ?? 25),
                $_POST['status'] ?? 'active'
            );
            $message = 'Product ' . $product['name'] . ' created';
            $products = load_products();
        } elseif ($action === 'update_product') {
            $updated = update_product($_POST['product_id'], [
                'name' => trim($_POST['name']),
                'price' => (float)$_POST['price'],
                'description' => trim($_POST['description']),
                'category' => trim($_POST['category']),
                'image' => trim($_POST['image']),
                'stock' => (int)$_POST['stock'],
                'status' => $_POST['status'] ?? 'active',
            ]);
            $message = 'Updated ' . $updated['name'];
            $products = load_products();
        } elseif ($action === 'delete_product') {
            delete_product($_POST['product_id']);
            $message = 'Product removed';
            $products = load_products();
        } elseif ($action === 'update_order_status') {
            $updated = update_order_status($_POST['order_id'], $_POST['status'], $user['name'] ?? 'Admin');
            $message = 'Order #' . $updated['id'] . ' now ' . $updated['status'];
            $orders = array_reverse(load_orders());
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
            foreach ($settings['shipping']['options'] as $key => &$option) {
                $option['enabled'] = isset($_POST['shipping'][$key]['enabled']);
                $option['label'] = trim($_POST['shipping'][$key]['label'] ?? $option['label']);
                $option['base_rate'] = (float)($_POST['shipping'][$key]['base_rate'] ?? $option['base_rate']);
                $option['per_item'] = (float)($_POST['shipping'][$key]['per_item'] ?? $option['per_item']);
                $option['free_over'] = (float)($_POST['shipping'][$key]['free_over'] ?? $option['free_over']);
                $option['eta'] = trim($_POST['shipping'][$key]['eta'] ?? $option['eta']);
            }
            unset($option);
            $defaultShipping = $_POST['shipping_default'] ?? ($settings['shipping']['default'] ?? null);
            if ($defaultShipping && isset($settings['shipping']['options'][$defaultShipping])) {
                $settings['shipping']['default'] = $defaultShipping;
            }
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
                <h3>Shipping</h3>
                <label>Default method
                    <select name="shipping_default">
                        <?php foreach ($settings['shipping']['options'] as $key => $option): ?>
                            <option value="<?= htmlspecialchars($key) ?>" <?= ($settings['shipping']['default'] ?? '') === $key ? 'selected' : '' ?>><?= htmlspecialchars($option['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <?php foreach ($settings['shipping']['options'] as $key => $option): ?>
                    <fieldset class="fieldset">
                        <label class="checkbox"><input type="checkbox" name="shipping[<?= $key ?>][enabled]" <?= !empty($option['enabled']) ? 'checked' : '' ?>> Enable <?= htmlspecialchars($option['label']) ?></label>
                        <div class="form-inline compact">
                            <label>Label<input name="shipping[<?= $key ?>][label]" value="<?= htmlspecialchars($option['label']) ?>"></label>
                            <label>Base rate<input type="number" step="0.01" name="shipping[<?= $key ?>][base_rate]" value="<?= htmlspecialchars($option['base_rate']) ?>"></label>
                            <label>Per item<input type="number" step="0.01" name="shipping[<?= $key ?>][per_item]" value="<?= htmlspecialchars($option['per_item']) ?>"></label>
                            <label>Free over<input type="number" step="0.01" name="shipping[<?= $key ?>][free_over]" value="<?= htmlspecialchars($option['free_over']) ?>"></label>
                        </div>
                        <label>ETA / Note<textarea name="shipping[<?= $key ?>][eta]" rows="2"><?= htmlspecialchars($option['eta']) ?></textarea></label>
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
                <label>Stock<input type="number" name="stock" min="0" value="25"></label>
                <label>Status
                    <select name="status">
                        <option value="active">Active</option>
                        <option value="draft">Draft</option>
                        <option value="archived">Archived</option>
                    </select>
                </label>
                <button class="button primary" type="submit">Create</button>
            </form>
        </section>

        <section class="panel">
            <h2>Products</h2>
            <div class="list">
                <?php foreach ($products as $product): ?>
                    <div class="list-item">
                        <form method="post" class="form-inline">
                            <input type="hidden" name="action" value="update_product">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
                            <div>
                                <label>Name<input name="name" value="<?= htmlspecialchars($product['name']) ?>" required></label>
                                <label>Price<input type="number" step="0.01" name="price" value="<?= htmlspecialchars($product['price']) ?>" required></label>
                                <label>Category<input name="category" value="<?= htmlspecialchars($product['category']) ?>" required></label>
                                <label>Image<input name="image" value="<?= htmlspecialchars($product['image']) ?>"></label>
                            </div>
                            <div class="stacked">
                                <label>Stock<input type="number" name="stock" min="0" value="<?= htmlspecialchars($product['stock'] ?? 0) ?>"></label>
                                <label>Status
                                    <select name="status">
                                        <?php foreach (['active','draft','archived'] as $status): ?>
                                            <option value="<?= $status ?>" <?= ($product['status'] ?? 'active') === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label>Description<textarea name="description" rows="2"><?= htmlspecialchars($product['description']) ?></textarea></label>
                            </div>
                            <div class="actions">
                                <span class="pill">ID <?= htmlspecialchars($product['id']) ?></span>
                                <span class="pill muted">Stock: <?= htmlspecialchars($product['stock'] ?? 0) ?></span>
                                <button class="button ghost" type="submit">Save</button>
                            </div>
                        </form>
                        <form method="post" class="inline-form">
                            <input type="hidden" name="action" value="delete_product">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
                            <button class="button danger" type="submit">Remove</button>
                        </form>
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
                        <?php $shippingLabel = $order['shipping']['shipping_label'] ?? ($order['shipping_option']['label'] ?? ''); ?>
                        <div class="order-meta">$<?= number_format($order['totals']['total'], 2) ?> • <?= htmlspecialchars($order['payment_method']) ?><?= $shippingLabel ? ' • ' . htmlspecialchars($shippingLabel) : '' ?></div>
                        <div class="order-history">
                            <?php foreach ($order['history'] ?? [] as $entry): ?>
                                <span class="pill muted"><?= htmlspecialchars($entry['status']) ?> • <?= date('M j, H:i', strtotime($entry['at'])) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <p class="muted small">Ship to: <?= htmlspecialchars($order['shipping']['address']) ?>, <?= htmlspecialchars($order['shipping']['city']) ?> • Shipping: <?= $shippingLabel ? htmlspecialchars($shippingLabel) : 'N/A' ?><?= !empty($order['shipping']['shipping_eta']) ? ' (' . htmlspecialchars($order['shipping']['shipping_eta']) . ')' : '' ?> • Cost: $<?= number_format($order['shipping']['shipping_cost'] ?? ($order['totals']['shipping'] ?? 0), 2) ?> • WhatsApp: <?= !empty($order['shipping']['whatsapp_updates']) ? 'Enabled' : 'Off' ?></p>
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
