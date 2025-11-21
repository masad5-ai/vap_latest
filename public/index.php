<?php
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/products.php';
require_once __DIR__ . '/../src/cart.php';
require_once __DIR__ . '/../src/orders.php';
require_once __DIR__ . '/../src/settings.php';

ensure_session_started();
$config = load_config();
$settings = load_settings();
$user = current_user();
$products = load_products();
$message = null;
$section = $_GET['view'] ?? 'home';
$query = trim($_GET['q'] ?? '');

if ($query) {
    $products = array_values(array_filter($products, function ($product) use ($query) {
        return stripos($product['name'], $query) !== false || stripos($product['category'], $query) !== false;
    }));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        switch ($action) {
            case 'add_to_cart':
                add_to_cart($_POST['product_id'], (int)($_POST['quantity'] ?? 1));
                $message = 'Added to cart';
                break;
            case 'update_cart':
                foreach (($_POST['qty'] ?? []) as $id => $qty) {
                    update_cart_quantity($id, (int)$qty);
                }
                $message = 'Cart updated';
                $section = 'cart';
                break;
            case 'register':
                $user = register_user(trim($_POST['name']), trim($_POST['email']), $_POST['password']);
                $_SESSION['user'] = $user;
                $message = 'Welcome to ' . $config['branding']['store_name'] . '!';
                break;
            case 'login':
                $user = login_user(trim($_POST['email']), $_POST['password']);
                if (!$user) {
                    throw new RuntimeException('Invalid credentials');
                }
                $message = 'Logged in';
                break;
            case 'logout':
                logout_user();
                header('Location: /index.php');
                exit;
            case 'update_profile':
                if (!$user) {
                    throw new RuntimeException('Please sign in first');
                }
                $user = update_user_profile($user['id'], [
                    'phone' => $_POST['phone'] ?? '',
                    'address' => $_POST['address'] ?? '',
                    'city' => $_POST['city'] ?? '',
                    'whatsapp_updates' => !empty($_POST['whatsapp_updates']),
                    'email_updates' => !empty($_POST['email_updates']),
                ]);
                $message = 'Profile updated';
                $section = 'orders';
                break;
            case 'checkout':
                $totals = cart_totals();
                if (empty($totals['lines'])) {
                    throw new RuntimeException('Your cart is empty');
                }
                $paymentMethod = trim($_POST['payment_method'] ?? 'custom_gateway');
                $paymentOptions = $settings['payments'] ?? [];
                if (!isset($paymentOptions[$paymentMethod]) || empty($paymentOptions[$paymentMethod]['enabled'])) {
                    throw new RuntimeException('Selected payment method is unavailable.');
                }
                $shipping = [
                    'name' => trim($_POST['name']),
                    'email' => trim($_POST['email']),
                    'address' => trim($_POST['address']),
                    'city' => trim($_POST['city']),
                    'payment_method' => $paymentMethod,
                    'whatsapp_updates' => !empty($_POST['whatsapp_updates']),
                ];
                $order = create_order($user ?? [], $totals, $shipping);
                clear_cart();
                $message = 'Order #' . $order['id'] . ' placed successfully!';
                $section = 'orders';
                break;
        }
    } catch (Throwable $e) {
        $message = $e->getMessage();
    }
}

$totals = cart_totals();
$orders = array_reverse(load_orders());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['branding']['store_name']) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>:root { --accent: <?= htmlspecialchars($settings['branding']['accent']) ?>; }</style>
</head>
<body>
    <header class="hero">
        <div class="hero-content">
            <p class="eyebrow">Vape Boutique</p>
            <h1><?= htmlspecialchars($settings['branding']['tagline']) ?></h1>
            <p>Ultra-smooth devices, curated e-liquids, and accessories. Elevate your vapor ritual with signature blends and exclusive drops.</p>
            <a class="button primary" href="?view=products">Shop signature drops</a>
        </div>
        <div class="hero-badge">
            <span>New</span>
            <strong>Midnight Nebula</strong>
            <p>Limited reserve release</p>
        </div>
        <nav class="top-nav">
            <div class="brand"><?= htmlspecialchars($settings['branding']['store_name']) ?></div>
            <div class="nav-links">
                <a href="/index.php">Home</a>
                <a href="?view=products">Shop</a>
                <a href="?view=cart">Cart (<?= count(cart_items()) ?>)</a>
                <?php if ($user): ?>
                    <a href="?view=orders">Dashboard</a>
                <?php else: ?>
                    <a href="?view=auth">Login</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <?php if ($message): ?>
        <div class="flash"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <main>
        <?php if ($section === 'products' || $section === 'home'): ?>
            <section class="metrics">
                <div class="metric-card">
                    <p class="muted">Verified flavors</p>
                    <h3>120+</h3>
                    <p>Curated with sommeliers for consistency.</p>
                </div>
                <div class="metric-card">
                    <p class="muted">Express shipping</p>
                    <h3>24-48h</h3>
                    <p>Tracked delivery across major metros.</p>
                </div>
                <div class="metric-card">
                    <p class="muted">Loyalty club</p>
                    <h3>VIP Ember</h3>
                    <p>Exclusive drops and concierge support.</p>
                </div>
            </section>
            <form method="get" class="search-bar">
                <input type="hidden" name="view" value="products">
                <input type="search" name="q" placeholder="Search flavors, kits, pods" value="<?= htmlspecialchars($query) ?>">
                <button class="button ghost" type="submit">Search</button>
            </form>
            <section class="grid">
                <?php foreach ($products as $product): ?>
                    <?php if (($product['status'] ?? 'active') !== 'active') { continue; } ?>
                    <article class="card">
                        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        <div class="card-body">
                            <p class="pill"><?= htmlspecialchars($product['category']) ?></p>
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <p><?= htmlspecialchars($product['description']) ?></p>
                            <div class="price-row">
                                <strong>$<?= number_format($product['price'], 2) ?></strong>
                                <span class="pill muted">Stock <?= (int)($product['stock'] ?? 0) ?></span>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="action" value="add_to_cart">
                                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
                                    <button type="submit" class="button ghost">Add to cart</button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <?php if ($section === 'cart'): ?>
            <section class="panel">
                <h2>Your cart</h2>
                <?php if (empty($totals['lines'])): ?>
                    <p>Your cart is empty.</p>
                <?php else: ?>
                    <form method="post">
                        <input type="hidden" name="action" value="update_cart">
                        <table class="cart-table">
                            <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
                            <tbody>
                                <?php foreach ($totals['lines'] as $line): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($line['product']['name']) ?></td>
                                        <td><input type="number" name="qty[<?= $line['product']['id'] ?>]" value="<?= $line['quantity'] ?>" min="0"></td>
                                        <td>$<?= number_format($line['product']['price'], 2) ?></td>
                                        <td>$<?= number_format($line['line_total'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <button class="button ghost" type="submit">Update cart</button>
                    </form>
                    <div class="totals">
                        <p>Subtotal: $<?= number_format($totals['subtotal'], 2) ?></p>
                        <p>Shipping: $<?= number_format($totals['shipping'], 2) ?></p>
                        <p>Tax: $<?= number_format($totals['tax'], 2) ?></p>
                        <p class="total">Total: $<?= number_format($totals['total'], 2) ?></p>
                    </div>
                    <a class="button primary" href="?view=checkout">Proceed to checkout</a>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if ($section === 'checkout'): ?>
            <section class="panel">
                <h2>Checkout</h2>
                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="checkout">
                    <label>Name<input required name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>"></label>
                    <label>Email<input required type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>"></label>
                    <label>Address<textarea required name="address" rows="3"><?= htmlspecialchars($user['profile']['address'] ?? '') ?></textarea></label>
                    <label>City<input required name="city" value="<?= htmlspecialchars($user['profile']['city'] ?? '') ?>"></label>
                    <label>Payment method
                        <select name="payment_method">
                            <?php foreach ($settings['payments'] as $key => $payment): ?>
                                <?php if (!empty($payment['enabled'])): ?>
                                    <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($payment['label']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="checkbox"><input type="checkbox" name="whatsapp_updates" <?= !empty($user['profile']['whatsapp_updates']) ? 'checked' : '' ?>> Send me WhatsApp updates</label>
                    <button class="button primary" type="submit">Place order</button>
                </form>
            </section>
        <?php endif; ?>

        <?php if ($section === 'auth'): ?>
            <section class="form-grid">
                <div class="panel">
                    <h2>Login</h2>
                    <form method="post">
                        <input type="hidden" name="action" value="login">
                        <label>Email<input type="email" name="email" required></label>
                        <label>Password<input type="password" name="password" required></label>
                        <button class="button primary" type="submit">Login</button>
                    </form>
                </div>
                <div class="panel">
                    <h2>Create account</h2>
                    <form method="post">
                        <input type="hidden" name="action" value="register">
                        <label>Name<input name="name" required></label>
                        <label>Email<input type="email" name="email" required></label>
                        <label>Password<input type="password" name="password" required></label>
                        <button class="button ghost" type="submit">Create account</button>
                    </form>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($section === 'orders' && $user): ?>
            <section class="panel">
                <div class="dashboard">
                    <div>
                        <h2>Your dashboard</h2>
                        <?php $userOrders = array_filter($orders, fn($o) => ($o['customer']['id'] ?? null) === $user['id']); ?>
                        <?php $totalSpend = array_sum(array_map(fn($o) => $o['totals']['total'] ?? 0, $userOrders)); ?>
                        <div class="metric-row">
                            <div class="metric-card">
                                <p class="muted">Orders</p>
                                <h3><?= count($userOrders) ?></h3>
                                <p><?= count($userOrders) ? 'Reorder your favorites anytime.' : 'Start with a curated kit.' ?></p>
                            </div>
                            <div class="metric-card">
                                <p class="muted">Lifetime value</p>
                                <h3>$<?= number_format($totalSpend, 2) ?></h3>
                                <p>Earn rewards with every drop.</p>
                            </div>
                            <div class="metric-card">
                                <p class="muted">Notifications</p>
                                <h3><?= !empty($user['profile']['whatsapp_updates']) ? 'WhatsApp' : 'Email' ?></h3>
                                <p>Delivery alerts and refill nudges.</p>
                            </div>
                        </div>
                        <h3>Recent activity</h3>
                        <?php if (empty($userOrders)): ?>
                            <p>No orders yet.</p>
                        <?php else: ?>
                            <div class="timeline">
                                <?php foreach ($userOrders as $order): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-meta">#<?= $order['id'] ?> • <?= htmlspecialchars($order['status']) ?> • <?= htmlspecialchars($order['payment_method'] ?? 'custom_gateway') ?></div>
                                        <strong>$<?= number_format($order['totals']['total'], 2) ?></strong>
                                        <p><?= count($order['items']) ?> items • <?= htmlspecialchars($order['shipping']['city']) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <aside class="panel">
                        <h3>Profile & preferences</h3>
                        <form method="post" class="form-grid">
                            <input type="hidden" name="action" value="update_profile">
                            <label>Phone<input name="phone" value="<?= htmlspecialchars($user['profile']['phone'] ?? '') ?>" placeholder="WhatsApp ready"></label>
                            <label>Address<textarea name="address" rows="2" placeholder="Delivery address"><?= htmlspecialchars($user['profile']['address'] ?? '') ?></textarea></label>
                            <label>City<input name="city" value="<?= htmlspecialchars($user['profile']['city'] ?? '') ?>"></label>
                            <label class="checkbox"><input type="checkbox" name="whatsapp_updates" <?= !empty($user['profile']['whatsapp_updates']) ? 'checked' : '' ?>> WhatsApp updates</label>
                            <label class="checkbox"><input type="checkbox" name="email_updates" <?= !empty($user['profile']['email_updates']) ? 'checked' : '' ?>> Email updates</label>
                            <button class="button primary" type="submit">Save profile</button>
                        </form>
                        <form method="post" class="inline-form">
                            <input type="hidden" name="action" value="logout">
                            <button class="button ghost" type="submit">Logout</button>
                        </form>
                    </aside>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <div>
            <h3>Customer care</h3>
            <p>Email: <?= htmlspecialchars($settings['branding']['support_email']) ?></p>
            <p>WhatsApp: <?= htmlspecialchars($settings['branding']['whatsapp']) ?></p>
        </div>
        <div>
            <h3>Admin console</h3>
            <p><a href="/admin.php">Manage catalogue & orders</a></p>
        </div>
        <div class="legal">&copy; <?= date('Y') ?> VaporPulse. Crafted for discerning vapers.</div>
    </footer>
</body>
</html>
