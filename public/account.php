<?php
require __DIR__ . '/includes/bootstrap.php';
if (!$user) {
    header('Location: /login.php');
    exit;
}
$page = 'account';
$pageTitle = 'Account | ' . $settings['branding']['store_name'];
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'update_profile') {
            $user = update_user_profile($user['id'], [
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? '',
                'city' => $_POST['city'] ?? '',
                'whatsapp_updates' => !empty($_POST['whatsapp_updates']),
                'email_updates' => !empty($_POST['email_updates']),
            ]);
            $message = 'Profile updated';
        } elseif ($action === 'logout') {
            logout_user();
            header('Location: /index.php');
            exit;
        }
    } catch (Throwable $e) {
        $message = $e->getMessage();
    }
}
$orders = array_reverse(load_orders());
$orders = array_filter($orders, fn($o) => ($o['user']['id'] ?? null) === $user['id']);
$orderCount = count($orders);
$totalSpend = array_sum(array_map(fn($o) => $o['totals']['total'] ?? 0, $orders));
$favoriteShipping = ($orders[array_key_first($orders)]['shipping']['shipping_label'] ?? null) ?: ($defaultShipping['label'] ?? 'Express courier');
include __DIR__ . '/includes/header.php';
?>
<section class="section account-grid">
    <div class="panel">
        <p class="eyebrow">Welcome back</p>
        <h2>Your dashboard</h2>
        <p class="muted">Personalized overview of your orders, shipping preferences, and communication opt-ins.</p>
        <?php if ($message): ?><div class="flash"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <div class="panel-grid">
            <div class="stat-card">
                <p class="muted">Orders placed</p>
                <h2><?= $orderCount ?></h2>
                <p class="muted">Across all time</p>
            </div>
            <div class="stat-card">
                <p class="muted">Lifetime spend</p>
                <h2>$<?= number_format($totalSpend, 2) ?></h2>
                <p class="muted">Including shipping & tax</p>
            </div>
            <div class="stat-card">
                <p class="muted">Preferred shipping</p>
                <h2><?= htmlspecialchars($favoriteShipping) ?></h2>
                <p class="muted">Saved for quick checkout</p>
            </div>
        </div>
        <form method="post" class="stacked">
            <input type="hidden" name="action" value="update_profile">
            <label>Phone <input name="phone" value="<?= htmlspecialchars($user['profile']['phone'] ?? '') ?>"></label>
            <label>Address <input name="address" value="<?= htmlspecialchars($user['profile']['address'] ?? '') ?>"></label>
            <label>City <input name="city" value="<?= htmlspecialchars($user['profile']['city'] ?? '') ?>"></label>
            <label class="pill-row">WhatsApp updates <input type="checkbox" name="whatsapp_updates" <?= !empty($user['profile']['whatsapp_updates']) ? 'checked' : '' ?>></label>
            <label class="pill-row">Email updates <input type="checkbox" name="email_updates" <?= !empty($user['profile']['email_updates']) ? 'checked' : '' ?>></label>
            <div class="cta-row">
                <button class="button primary" type="submit">Save profile</button>
                <a class="button ghost" href="shop.php">Shop featured drops</a>
            </div>
        </form>
        <form method="post" style="margin-top:10px;">
            <input type="hidden" name="action" value="logout">
            <button class="button" type="submit">Logout</button>
        </form>
    </div>
    <div class="panel">
        <div class="section-header compact">
            <div>
                <p class="eyebrow">Orders</p>
                <h3>Recent orders</h3>
            </div>
            <a class="button ghost" href="shop.php">Shop more</a>
        </div>
        <?php foreach ($orders as $order): ?>
            <article class="card compact">
                <div>
                    <strong>Order #<?= htmlspecialchars($order['id']) ?></strong>
                    <p class="muted">Status <?= htmlspecialchars($order['status'] ?? 'processing') ?> · <?= htmlspecialchars($order['shipping']['shipping_label'] ?? '') ?> (<?= htmlspecialchars($order['shipping']['shipping_eta'] ?? '') ?>)</p>
                </div>
                <div class="pill-row">
                    <span class="pill">$<?= number_format($order['totals']['total'], 2) ?></span>
                    <span class="pill muted">Items <?= count($order['items'] ?? []) ?></span>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <div class="panel">
        <div class="section-header compact">
            <div>
                <p class="eyebrow">Perks</p>
                <h3>Promotions & loyalty</h3>
            </div>
        </div>
        <div class="stacked">
            <div class="mini-line"><span>Seasonal offer</span><strong><?= htmlspecialchars($settings['promotions']['headline'] ?? 'Save on bundles') ?></strong></div>
            <div class="mini-line"><span>Messaging</span><span><?= !empty($user['profile']['whatsapp_updates']) ? 'WhatsApp + Email' : 'Email only' ?></span></div>
            <div class="mini-line"><span>Default shipping</span><span><?= htmlspecialchars($favoriteShipping) ?></span></div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
