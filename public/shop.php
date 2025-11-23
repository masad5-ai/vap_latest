<?php
require __DIR__ . '/includes/bootstrap.php';
$page = 'shop';
$pageTitle = 'Shop | ' . $settings['branding']['store_name'];
$query = trim($_GET['q'] ?? '');
$activeCategory = trim($_GET['category'] ?? '');
$products = load_products();
$categories = $settings['catalog']['categories'] ?? [];
if ($query) {
    $products = array_values(array_filter($products, function ($product) use ($query) {
        return stripos($product['name'], $query) !== false || stripos($product['category'], $query) !== false;
    }));
}
$products = array_values(array_filter($products, function ($p) use ($activeCategory) {
    if (($p['status'] ?? 'active') !== 'active') { return false; }
    if ($activeCategory && strcasecmp($p['category'], $activeCategory) !== 0) { return false; }
    return true;
}));
include __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Catalog</p>
            <h2>Shop vapes, pods, and e-liquid staples</h2>
            <p class="muted">Refined layouts, clean product cards, and cart-ready controls. Inspired by Vaperoo’s modern aesthetic.</p>
        </div>
        <form method="get" class="search-bar">
            <input type="search" name="q" placeholder="Search flavors, kits, pods" value="<?= htmlspecialchars($query) ?>">
            <button class="button ghost" type="submit">Search</button>
        </form>
    </div>
    <div class="pill-row">
        <a class="pill <?= $activeCategory === '' ? 'active' : '' ?>" href="/shop.php">All</a>
        <?php foreach ($categories as $category): ?>
            <a class="pill <?= strcasecmp($activeCategory, $category['id']) === 0 ? 'active' : '' ?>" href="/shop.php?category=<?= htmlspecialchars($category['id']) ?>"><?= htmlspecialchars($category['name']) ?></a>
        <?php endforeach; ?>
    </div>
    <div class="grid grid-3">
        <?php foreach ($products as $product): ?>
            <article class="card elevated">
                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <div class="card-body">
                    <div class="pill-row">
                        <span class="pill"><?= htmlspecialchars($product['category']) ?></span>
                        <span class="pill muted">Stock <?= (int)($product['stock'] ?? 0) ?></span>
                    </div>
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p><?= htmlspecialchars($product['description']) ?></p>
                    <div class="price-row">
                        <strong>$<?= number_format($product['price'], 2) ?></strong>
                        <form method="post" action="/cart.php" class="inline-form">
                            <input type="hidden" name="action" value="add_to_cart">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
                            <button type="submit" class="button primary">Add to cart</button>
                        </form>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
