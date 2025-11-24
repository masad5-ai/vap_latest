<?php
if (!isset($settings)) {
    require __DIR__ . '/bootstrap.php';
}
$pageTitle = $pageTitle ?? $settings['branding']['store_name'];
$defaultShipping = shipping_default_option($settings);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>:root { --accent: <?= htmlspecialchars($settings['branding']['accent']) ?>; }</style>
</head>
<body class="page-<?= htmlspecialchars($page ?? 'home') ?>">
<div class="topline">
    <span><?= htmlspecialchars($settings['branding']['support_phone'] ?? '') ?> · <?= htmlspecialchars($settings['branding']['support_email'] ?? '') ?></span>
    <span><?= htmlspecialchars($defaultShipping['label'] ?? 'Express courier') ?> · <?= htmlspecialchars($defaultShipping['eta'] ?? '24-48h') ?></span>
</div>
<header class="site-header">
    <div class="logo-mark">
        <?php if (!empty($settings['branding']['logo'])): ?>
            <img class="logo-img" src="<?= htmlspecialchars($settings['branding']['logo']) ?>" alt="<?= htmlspecialchars($settings['branding']['store_name']) ?> logo">
        <?php else: ?>
            <span class="spark"></span>
        <?php endif; ?>
        <div>
            <strong><?= htmlspecialchars($settings['branding']['store_name']) ?></strong>
            <small><?= htmlspecialchars($settings['branding']['tagline']) ?></small>
        </div>
    </div>
    <nav>
        <a href="index.php" class="<?= ($page ?? '') === 'home' ? 'active' : '' ?>">Home</a>
        <a href="shop.php" class="<?= ($page ?? '') === 'shop' ? 'active' : '' ?>">Shop</a>
        <a href="cart.php" class="<?= ($page ?? '') === 'cart' ? 'active' : '' ?>">Cart (<?= count(cart_items()) ?>)</a>
        <?php if ($user): ?>
            <a href="account.php" class="<?= ($page ?? '') === 'account' ? 'active' : '' ?>">Account</a>
        <?php else: ?>
            <a href="login.php" class="<?= ($page ?? '') === 'login' ? 'active' : '' ?>">Login</a>
        <?php endif; ?>
    </nav>
    <div class="pill muted badge-soft">Shipping <?= htmlspecialchars($defaultShipping['eta'] ?? '24-48h') ?></div>
</header>
<?php if (!empty($settings['promotions']['active'])): ?>
    <div class="topline" style="border-bottom:none; justify-content:center;">
        <div class="pill" style="margin-right:10px;"><?= htmlspecialchars($settings['promotions']['badge'] ?? 'Promo') ?></div>
        <strong><?= htmlspecialchars($settings['promotions']['headline'] ?? '') ?></strong>
        <span class="muted" style="margin-left:8px;"><?= htmlspecialchars($settings['promotions']['subtext'] ?? '') ?></span>
    </div>
<?php endif; ?>
<?php if (!empty($hero) && ($page ?? '') === 'home'): ?>
<section class="hero split" style="background-image:url('<?= htmlspecialchars($settings['branding']['banner'] ?? '') ?>'); background-size:cover; background-position:center;">
    <div>
        <p class="eyebrow">Vape Boutique</p>
        <h1><?= htmlspecialchars($settings['branding']['tagline']) ?></h1>
        <p class="lede">Ultra-smooth devices, curated e-liquids, and accessories. Elevate your vapor ritual with signature blends and concierge-grade support.</p>
        <div class="hero-actions">
            <a class="button primary" href="shop.php">Shop signature drops</a>
            <a class="button ghost" href="account.php">Track your orders</a>
        </div>
        <div class="hero-chips">
            <span class="pill">New: Midnight Nebula</span>
            <span class="pill">Nicotine-free options</span>
            <span class="pill">Afterpay-ready</span>
        </div>
    </div>
    <div class="hero-card">
        <div class="floating-badge">Limited</div>
        <h3>Velvet Storm Kit</h3>
        <p>Featherlight chassis, mesh coil clarity, luxe matte finish.</p>
        <div class="hero-card-meta">
            <span class="pill muted">Ships today</span>
            <span class="pill">$129.00</span>
        </div>
    </div>
</section>
<?php endif; ?>
<main class="page">
