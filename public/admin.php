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

$revenue = 0;
$openOrders = 0;
$fulfilledOrders = 0;
$readyToShip = 0;
foreach ($orders as $order) {
    $revenue += $order['totals']['total'] ?? 0;
    if (in_array($order['status'], ['pending', 'paid'], true)) {
        $openOrders++;
    }
    if (($order['status'] ?? '') === 'fulfilled') {
        $fulfilledOrders++;
    }
    if (($order['status'] ?? '') === 'paid') {
        $readyToShip++;
    }
}

$avgOrder = count($orders) ? $revenue / count($orders) : 0;
$activeSkus = count(array_filter($products, fn ($p) => ($p['status'] ?? 'active') === 'active'));
$draftSkus = count(array_filter($products, fn ($p) => ($p['status'] ?? 'active') === 'draft'));
$lowStock = count(array_filter($products, fn ($p) => ($p['stock'] ?? 0) < 6));
$customerCount = count(array_filter($users, fn ($u) => ($u['role'] ?? 'customer') === 'customer'));
$shippingDefault = $settings['shipping']['default'] ?? null;
$shippingDefaultLabel = $shippingDefault && isset($settings['shipping']['options'][$shippingDefault])
    ? $settings['shipping']['options'][$shippingDefault]['label']
    : 'Standard';
$shippingEnabled = count(array_filter($settings['shipping']['options'], fn ($opt) => !empty($opt['enabled'])));

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
    <div class="admin-shell">
        <aside class="sidebar">
            <span class="brandmark"><?= htmlspecialchars($settings['branding']['store_name']) ?></span>
            <span class="tagline"><?= htmlspecialchars($settings['branding']['tagline']) ?></span>
            <div class="nav-group">
                <div class="nav-title">Navigation</div>
                <div class="nav-links">
                    <a href="#dashboard">Dashboard</a>
                    <a href="#settings">Experience & channels</a>
                    <a href="#catalog">Products & inventory</a>
                    <a href="#orders">Orders & shipping</a>
                    <a href="#users">User roles</a>
                </div>
            </div>
            <div class="nav-group">
                <div class="nav-title">Channels</div>
                <div class="nav-links">
                    <span class="badge"><span class="status-dot" style="background: <?= !empty($settings['notifications']['email']['enabled']) ? 'var(--accent-2)' : '#555' ?>"></span>Email <?= !empty($settings['notifications']['email']['enabled']) ? 'enabled' : 'paused' ?></span>
                    <span class="badge"><span class="status-dot" style="background: <?= !empty($settings['notifications']['whatsapp']['enabled']) ? 'var(--accent-2)' : '#555' ?>"></span>WhatsApp <?= !empty($settings['notifications']['whatsapp']['enabled']) ? 'broadcasting' : 'off' ?></span>
                    <span class="badge"><span class="status-dot"></span><?= $shippingEnabled ?> shipping methods</span>
                </div>
            </div>
            <div class="meta">
                <span class="tag">Default: <?= htmlspecialchars($shippingDefaultLabel) ?></span>
                <span class="tag">Support: <?= htmlspecialchars($settings['branding']['support_email']) ?></span>
            </div>
        </aside>

        <main class="admin-body">
            <div class="admin-topbar">
                <div>
                    <div class="muted">Signed in as <?= htmlspecialchars($user['name']) ?></div>
                    <strong>Admin console</strong>
                </div>
                <div class="cta-row">
                    <a class="button ghost" href="/index.php">View storefront</a>
                    <a class="button primary" href="#create-product">Add product</a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="flash"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <section class="admin-hero" id="dashboard">
                <div>
                    <span class="pill warn">Ops cockpit</span>
                    <h1>Monitor, merchandise, and fulfill.</h1>
                    <p>Full-control dashboard for payments, WhatsApp/email notices, shipping SLAs, and merch lifecycle.</p>
                    <div class="cta-row">
                        <span class="tag">Shipping default: <?= htmlspecialchars($shippingDefaultLabel) ?></span>
                        <span class="tag">Active SKUs: <?= $activeSkus ?></span>
                        <span class="tag">Customers: <?= $customerCount ?></span>
                    </div>
                </div>
                <div>
                    <div class="panel" style="margin:0;">
                        <div class="section-heading"><h3>Live health</h3><span class="hint">Realtime pulse</span></div>
                        <div class="list">
                            <div class="list-item"><div><strong>Open orders</strong><p class="muted">Awaiting payment or pick</p></div><span class="pill"><?= $openOrders ?></span></div>
                            <div class="list-item"><div><strong>Ready to ship</strong><p class="muted">Paid &amp; packing</p></div><span class="pill"><?= $readyToShip ?></span></div>
                            <div class="list-item"><div><strong>Fulfilled</strong><p class="muted">Completed journeys</p></div><span class="pill success"><?= $fulfilledOrders ?></span></div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="stat-grid">
                <div class="stat"><div class="label">Revenue to date</div><h3>$<?= number_format($revenue, 2) ?></h3><div class="trend">Across <?= count($orders) ?> orders</div></div>
                <div class="stat"><div class="label">Average order</div><h3>$<?= number_format($avgOrder, 2) ?></h3><div class="trend">Includes shipping &amp; tax</div></div>
                <div class="stat"><div class="label">Active SKUs</div><h3><?= $activeSkus ?></h3><div class="trend">Drafts: <?= $draftSkus ?> • Low stock: <?= $lowStock ?></div></div>
                <div class="stat"><div class="label">Channels</div><h3><?= $shippingEnabled ?> ship lanes</h3><div class="trend">Email + WhatsApp concierge</div></div>
            </div>

            <div class="quick-actions">
                <div class="quick-card"><div><strong>Configure experience</strong><p class="muted">Branding, payments, messaging, shipping</p></div><a class="button ghost" href="#settings">Open</a></div>
                <div class="quick-card"><div><strong>Launch a product</strong><p class="muted">Add art, stock, and CTA copy</p></div><a class="button ghost" href="#create-product">Create</a></div>
                <div class="quick-card"><div><strong>Order board</strong><p class="muted">Update fulfillment &amp; shipping ETA</p></div><a class="button ghost" href="#orders">Review</a></div>
                <div class="quick-card"><div><strong>Staff &amp; permissions</strong><p class="muted">Manage admins vs customers</p></div><a class="button ghost" href="#users">Manage</a></div>
            </div>

            <section class="panel emphasis" id="settings">
                <div class="section-heading">
                    <h2>Experience settings</h2>
                    <span class="hint">Control theme accents, payment methods, shipping tiers, and notification gateways.</span>
                </div>
                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="save_settings">
                    <h3>Branding &amp; support</h3>
                    <label>Store name<input name="store_name" value="<?= htmlspecialchars($settings['branding']['store_name']) ?>" required></label>
                    <label>Tagline<input name="tagline" value="<?= htmlspecialchars($settings['branding']['tagline']) ?>" required></label>
                    <label>Accent color<input name="accent" value="<?= htmlspecialchars($settings['branding']['accent']) ?>" required></label>
                    <label>Support email<input type="email" name="support_email" value="<?= htmlspecialchars($settings['branding']['support_email']) ?>" required></label>
                    <label>WhatsApp<input name="whatsapp" value="<?= htmlspecialchars($settings['branding']['whatsapp']) ?>" required></label>

                    <h3>Payment methods</h3>
                    <?php foreach ($settings['payments'] as $key => $payment): ?>
                        <fieldset class="fieldset">
                            <label class="checkbox">
                                <input type="checkbox" name="payment[<?= $key ?>][enabled]" <?= !empty($payment['enabled']) ? 'checked' : '' ?>>
                                Enable <?= htmlspecialchars($payment['label']) ?>
                            </label>
                            <div class="form-inline compact">
                                <label>Label<input name="payment[<?= $key ?>][label]" value="<?= htmlspecialchars($payment['label']) ?>"></label>
                                <label>Instructions<textarea name="payment[<?= $key ?>][instructions]" rows="2"><?= htmlspecialchars($payment['instructions']) ?></textarea></label>
                            </div>
                        </fieldset>
                    <?php endforeach; ?>

                    <h3>Shipping</h3>
                    <label>Default option
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

            <div class="admin-grid" id="catalog">
                <section class="panel" id="create-product">
                    <div class="section-heading">
                        <h2>Create product</h2>
                        <span class="hint">Launch a new SKU with hero art and pricing.</span>
                    </div>
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
                    <div class="section-heading">
                        <h2>Products</h2>
                        <span class="hint">Edit pricing, stock, and lifecycle status. Drafts stay hidden.</span>
                    </div>
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
                                        <span class="pill <?= ($product['status'] ?? 'active') === 'active' ? 'success' : 'warn' ?>"><?= ucfirst($product['status'] ?? 'active') ?></span>
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
            </div>

            <section class="panel" id="orders">
                <div class="section-heading">
                    <h2>Orders</h2>
                    <span class="hint">Update payment and fulfillment stages, confirm shipping lane, and keep messaging aligned.</span>
                </div>
                <?php if (empty($orders)): ?>
                    <p>No orders yet.</p>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-head">
                                <div>
                                    <strong>#<?= $order['id'] ?></strong>
                                    <div class="muted"><?= htmlspecialchars($order['customer']['name'] ?? 'Guest') ?> — <?= htmlspecialchars($order['shipping']['city']) ?></div>
                                </div>
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
                            <?php $shippingLabel = $order['shipping']['shipping_label'] ?? ($order['shipping_option']['label'] ?? ''); ?>
                            <div class="order-items">
                                <?php foreach ($order['items'] as $item): ?>
                                    <span class="muted"><?= htmlspecialchars($item['product']['name']) ?> × <?= $item['quantity'] ?></span>
                                <?php endforeach; ?>
                            </div>
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

            <section class="panel" id="users">
                <div class="section-heading">
                    <h2>User management</h2>
                    <span class="hint">Control who can access the console vs storefront shoppers.</span>
                </div>
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
    </div>
</body>
</html>
