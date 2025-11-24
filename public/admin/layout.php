<?php
if (!isset($settings)) {
    require __DIR__ . '/bootstrap.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($adminTitle) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>:root { --accent: <?= htmlspecialchars($settings['branding']['accent']) ?>; }</style>
</head>
<body class="admin">
<div class="admin-shell">
    <aside class="admin-nav">
        <div class="logo-mark">
            <span class="spark"></span>
            <div>
                <strong><?= htmlspecialchars($settings['branding']['store_name']) ?></strong>
                <small>Operations</small>
            </div>
        </div>
        <nav>
            <a href="index.php" class="<?= $adminPage === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="products.php" class="<?= $adminPage === 'products' ? 'active' : '' ?>">Products</a>
            <a href="orders.php" class="<?= $adminPage === 'orders' ? 'active' : '' ?>">Orders</a>
            <a href="categories.php" class="<?= $adminPage === 'categories' ? 'active' : '' ?>">Categories</a>
            <a href="shipping.php" class="<?= $adminPage === 'shipping' ? 'active' : '' ?>">Shipping</a>
            <a href="payments.php" class="<?= $adminPage === 'payments' ? 'active' : '' ?>">Payments</a>
            <a href="notifications.php" class="<?= $adminPage === 'notifications' ? 'active' : '' ?>">Messaging</a>
            <a href="settings.php" class="<?= $adminPage === 'settings' ? 'active' : '' ?>">Branding</a>
            <a href="users.php" class="<?= $adminPage === 'users' ? 'active' : '' ?>">Users</a>
        </nav>
        <div class="admin-support">
            <p class="muted small-text">Storefront</p>
            <a class="button ghost small" href="../index.php">View site</a>
        </div>
    </aside>
    <main class="admin-content">
        <header class="admin-topbar">
            <div>
                <p class="eyebrow">Control center</p>
                <div class="admin-breadcrumbs">
                    <span>Admin</span>
                    <span class="crumb-sep">·</span>
                    <strong><?= htmlspecialchars($adminTitle) ?></strong>
                </div>
            </div>
            <div class="admin-top-actions">
                <a class="button ghost" href="../index.php">View storefront</a>
                <div class="avatar-chip">
                    <div class="avatar-dot"></div>
                    <div>
                        <strong><?= htmlspecialchars($user['name'] ?? 'Admin') ?></strong>
                        <small class="muted">Role: <?= htmlspecialchars($user['role'] ?? 'admin') ?></small>
                    </div>
                </div>
            </div>
        </header>
        <?php if (!empty($message)): ?><div class="flash"><?= htmlspecialchars($message) ?></div><?php endif; ?>
