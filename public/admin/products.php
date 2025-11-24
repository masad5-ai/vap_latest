<?php
$adminPage = 'products';
require __DIR__ . '/bootstrap.php';
$message = null;
$products = load_products();
$categories = $settings['catalog']['categories'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'create') {
            create_product(
                trim($_POST['name']),
                (float)$_POST['price'],
                trim($_POST['description']),
                trim($_POST['category']),
                trim($_POST['image']),
                (int)$_POST['stock'],
                $_POST['status'] ?? 'active',
                $_POST['sale_price'] !== '' ? (float)$_POST['sale_price'] : null,
                trim($_POST['promo'] ?? '')
            );
            $message = 'Product created';
        } elseif ($action === 'update') {
            update_product($_POST['id'], [
                'name' => $_POST['name'],
                'price' => (float)$_POST['price'],
                'sale_price' => $_POST['sale_price'] !== '' ? (float)$_POST['sale_price'] : null,
                'promo' => $_POST['promo'] ?? '',
                'description' => $_POST['description'],
                'category' => $_POST['category'],
                'image' => $_POST['image'],
                'stock' => (int)$_POST['stock'],
                'status' => $_POST['status'] ?? 'active',
            ]);
            $message = 'Product updated';
        } elseif ($action === 'delete') {
            delete_product($_POST['id']);
            $message = 'Product removed';
        }
        $products = load_products();
    } catch (Throwable $e) {
        $message = $e->getMessage();
    }
}

$editId = $_GET['edit'] ?? null;
$editing = null;
foreach ($products as $p) {
    if ((string)($p['id'] ?? '') === (string)$editId) {
        $editing = $p;
        break;
    }
}

$totals = [
    'products' => count($products),
    'active' => count(array_filter($products, fn($p) => ($p['status'] ?? 'active') === 'active')),
    'drafts' => count(array_filter($products, fn($p) => ($p['status'] ?? 'active') === 'draft')),
    'lowStock' => count(array_filter($products, fn($p) => ($p['stock'] ?? 0) < 8)),
];

$adminTitle = 'Products';
include __DIR__ . '/layout.php';
?>
<section class="section">
    <div class="section-header compact">
        <div>
            <p class="eyebrow">Catalog cockpit</p>
            <h3>Merchandising control</h3>
            <p class="muted">Review every SKU, spot low stock, and open a focused editor for edits or launch new items without leaving the grid.</p>
        </div>
        <div class="cta-row">
            <a class="button ghost" href="../shop.php" target="_blank">View shop</a>
            <a class="button primary" href="#editor">New product</a>
        </div>
    </div>
    <div class="panel-grid">
        <div class="metric-card">
            <p class="eyebrow">Catalog</p>
            <h2><?= $totals['products'] ?></h2>
            <p class="muted">SKUs tracked</p>
        </div>
        <div class="metric-card">
            <p class="eyebrow">Selling</p>
            <h2><?= $totals['active'] ?></h2>
            <p class="muted">Active & live</p>
        </div>
        <div class="metric-card">
            <p class="eyebrow">Drafts</p>
            <h2><?= $totals['drafts'] ?></h2>
            <p class="muted">Awaiting go-live</p>
        </div>
        <div class="metric-card">
            <p class="eyebrow">Inventory risk</p>
            <h2><?= $totals['lowStock'] ?></h2>
            <p class="muted">Under 8 units</p>
        </div>
    </div>
</section>

<section class="section admin-grid">
    <div class="panel">
        <div class="section-header compact">
            <div>
                <p class="eyebrow">Catalog</p>
                <h3>Product grid</h3>
                <p class="muted">Sortable grid with quick view of pricing, stock, and status. Jump into an edit drawer or archive in one click.</p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Item</th>
                    <th>Pricing</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <div class="table-product">
                                <div class="thumb" style="background-image:url('<?= htmlspecialchars($product['image'] ?? '') ?>')"></div>
                                <div>
                                    <strong><?= htmlspecialchars($product['name']) ?></strong>
                                    <div class="muted small-text"><?= htmlspecialchars($product['category'] ?? 'Uncategorized') ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="stacked tight">
                                <div><strong>$<?= number_format((float)$product['price'], 2) ?></strong></div>
                                <?php if (!empty($product['sale_price'])): ?>
                                    <span class="pill muted">Sale $<?= number_format((float)$product['sale_price'], 2) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($product['promo'])): ?>
                                    <span class="pill badge-soft"><?= htmlspecialchars($product['promo']) ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><strong><?= (int)($product['stock'] ?? 0) ?></strong></td>
                        <td>
                            <span class="status-pill <?= htmlspecialchars($product['status'] ?? 'active') ?>"><?= ucfirst($product['status'] ?? 'active') ?></span>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a class="button ghost small" href="products.php?edit=<?= urlencode($product['id']) ?>#editor">Edit</a>
                                <a class="button small" href="../shop.php?product=<?= urlencode($product['id']) ?>" target="_blank">View</a>
                                <form method="post" class="inline-form" onsubmit="return confirm('Archive this product?');">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($product['id']) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button class="button danger small" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel" id="editor">
        <p class="eyebrow">Editor</p>
        <h3><?= $editing ? 'Edit product' : 'Launch a product' ?></h3>
        <p class="muted">Dedicated form for pricing, positioning, inventory, and publishing status. Save changes without scrolling the grid.</p>
        <form method="post" class="stacked">
            <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
            <?php if ($editing): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($editing['id']) ?>">
            <?php endif; ?>
            <label>Name <input required name="name" value="<?= htmlspecialchars($editing['name'] ?? '') ?>"></label>
            <label>Price <input required type="number" step="0.01" name="price" value="<?= htmlspecialchars($editing['price'] ?? '') ?>"></label>
            <label>Sale price <input type="number" step="0.01" name="sale_price" value="<?= htmlspecialchars($editing['sale_price'] ?? '') ?>"></label>
            <label>Promo badge <input name="promo" value="<?= htmlspecialchars($editing['promo'] ?? '') ?>"></label>
            <label>Description <textarea name="description"><?= htmlspecialchars($editing['description'] ?? '') ?></textarea></label>
            <label>Category
                <select name="category">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['id']) ?>" <?= ($editing['category'] ?? '') === $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Image URL <input name="image" value="<?= htmlspecialchars($editing['image'] ?? '') ?>"></label>
            <div class="grid grid-2 tight">
                <label>Stock <input type="number" name="stock" value="<?= htmlspecialchars($editing['stock'] ?? 25) ?>"></label>
                <label>Status
                    <select name="status">
                        <?php foreach (['active','draft','archived'] as $status): ?>
                            <option value="<?= $status ?>" <?= ($editing['status'] ?? 'active') === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <div class="cta-row">
                <button class="button primary" type="submit">Save product</button>
                <?php if ($editing): ?>
                    <a class="button ghost" href="products.php">Cancel edit</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
