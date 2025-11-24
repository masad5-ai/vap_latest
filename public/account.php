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
<section class="section">
    <div class="account-hero">
        <div>
            <p class="eyebrow">Welcome back</p>
            <h2>Account cockpit</h2>
            <p class="muted">Manage profile, addresses, notification preferences, and track every order in one clean place.</p>
        </div>
        <div class="pill-row">
            <span class="pill">Orders <?= $orderCount ?></span>
            <span class="pill muted">Lifetime $<?= number_format($totalSpend, 2) ?></span>
            <span class="pill muted">Preferred <?= htmlspecialchars($favoriteShipping) ?></span>
        </div>
    </div>
</section>
<?php if ($message): ?><div class="flash" style="margin:0 0 12px 0;"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<section class="section account-hub">
    <div class="panel">
        <div class="section-header compact">
            <div>
                <p class="eyebrow">Profile</p>
                <h3>Contact & delivery</h3>
            </div>
            <a class="button ghost" href="shop.php">Continue shopping</a>
        </div>
        <form method="post" class="stacked">
            <input type="hidden" name="action" value="update_profile">
            <label>Phone <input name="phone" value="<?= htmlspecialchars($user['profile']['phone'] ?? '') ?>"></label>
            <label>Address <input name="address" value="<?= htmlspecialchars($user['profile']['address'] ?? '') ?>"></label>
            <label>City <input name="city" value="<?= htmlspecialchars($user['profile']['city'] ?? '') ?>"></label>
            <div class="grid grid-2 tight">
                <label class="pill-row">WhatsApp updates <input type="checkbox" name="whatsapp_updates" <?= !empty($user['profile']['whatsapp_updates']) ? 'checked' : '' ?>></label>
                <label class="pill-row">Email updates <input type="checkbox" name="email_updates" <?= !empty($user['profile']['email_updates']) ? 'checked' : '' ?>></label>
            </div>
            <div class="cta-row">
                <button class="button primary" type="submit">Save profile</button>
                <button class="button ghost" type="submit" form="logout-form">Logout</button>
            </div>
        </form>
    </div>
    <div class="panel">
        <div class="section-header compact">
            <div>
                <p class="eyebrow">Orders</p>
                <h3>Recent activity</h3>
            </div>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Shipping</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong>#<?= htmlspecialchars($order['id']) ?></strong></td>
                        <td class="small-text"><?= htmlspecialchars($order['shipping']['shipping_label'] ?? '') ?> · <?= htmlspecialchars($order['shipping']['shipping_eta'] ?? '') ?></td>
                        <td><?= count($order['items'] ?? []) ?></td>
                        <td><strong>$<?= number_format($order['totals']['total'], 2) ?></strong></td>
                        <td><span class="status-pill <?= htmlspecialchars($order['status'] ?? 'processing') ?>"><?= ucfirst($order['status'] ?? 'processing') ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
<form id="logout-form" method="post" style="display:none;">
    <input type="hidden" name="action" value="logout">
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
