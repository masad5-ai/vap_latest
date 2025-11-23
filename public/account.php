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
include __DIR__ . '/includes/header.php';
?>
<section class="section account-grid">
    <div>
        <p class="eyebrow">Welcome back</p>
        <h2>Your dashboard</h2>
        <?php if ($message): ?><div class="flash"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <form method="post" class="stacked">
            <input type="hidden" name="action" value="update_profile">
            <label>Phone <input name="phone" value="<?= htmlspecialchars($user['profile']['phone'] ?? '') ?>"></label>
            <label>Address <input name="address" value="<?= htmlspecialchars($user['profile']['address'] ?? '') ?>"></label>
            <label>City <input name="city" value="<?= htmlspecialchars($user['profile']['city'] ?? '') ?>"></label>
            <label class="pill-row">WhatsApp updates <input type="checkbox" name="whatsapp_updates" <?= !empty($user['profile']['whatsapp_updates']) ? 'checked' : '' ?>></label>
            <label class="pill-row">Email updates <input type="checkbox" name="email_updates" <?= !empty($user['profile']['email_updates']) ? 'checked' : '' ?>></label>
            <div class="cta-row">
                <button class="button ghost" type="submit">Save profile</button>
            </div>
        </form>
        <form method="post">
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
            <a class="button ghost" href="/shop.php">Shop more</a>
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
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
