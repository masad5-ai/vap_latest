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
            <a href="/admin/index.php" class="<?= $adminPage === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="/admin/products.php" class="<?= $adminPage === 'products' ? 'active' : '' ?>">Products</a>
            <a href="/admin/orders.php" class="<?= $adminPage === 'orders' ? 'active' : '' ?>">Orders</a>
            <a href="/admin/categories.php" class="<?= $adminPage === 'categories' ? 'active' : '' ?>">Categories</a>
            <a href="/admin/shipping.php" class="<?= $adminPage === 'shipping' ? 'active' : '' ?>">Shipping</a>
            <a href="/admin/payments.php" class="<?= $adminPage === 'payments' ? 'active' : '' ?>">Payments</a>
            <a href="/admin/notifications.php" class="<?= $adminPage === 'notifications' ? 'active' : '' ?>">Messaging</a>
            <a href="/admin/settings.php" class="<?= $adminPage === 'settings' ? 'active' : '' ?>">Branding</a>
            <a href="/admin/users.php" class="<?= $adminPage === 'users' ? 'active' : '' ?>">Users</a>
        </nav>
    </aside>
    <main class="admin-content">
        <header class="admin-topbar">
            <div>
                <p class="eyebrow">Control</p>
                <h1><?= htmlspecialchars($adminTitle) ?></h1>
            </div>
            <a class="button ghost" href="/index.php">View storefront</a>
        </header>
        <?php if (!empty($message)): ?><div class="flash"><?= htmlspecialchars($message) ?></div><?php endif; ?>
