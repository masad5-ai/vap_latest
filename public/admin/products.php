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
                $_POST['status'] ?? 'active'
            );
            $message = 'Product created';
        } elseif ($action === 'update') {
            update_product($_POST['id'], [
                'name' => $_POST['name'],
                'price' => (float)$_POST['price'],
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
$adminTitle = 'Products';
include __DIR__ . '/layout.php';
?>
<section class="section admin-dual">
    <div class="panel">
        <p class="eyebrow">Create</p>
        <h3>Launch a product</h3>
        <form method="post" class="stacked">
            <input type="hidden" name="action" value="create">
            <label>Name <input required name="name"></label>
            <label>Price <input required type="number" step="0.01" name="price"></label>
            <label>Description <textarea name="description"></textarea></label>
            <label>Category
                <select name="category">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Image URL <input name="image"></label>
            <label>Stock <input type="number" name="stock" value="25"></label>
            <label>Status
                <select name="status">
                    <option value="active">Active</option>
                    <option value="draft">Draft</option>
                    <option value="archived">Archived</option>
                </select>
            </label>
            <button class="button primary" type="submit">Create</button>
        </form>
    </div>
    <div class="panel">
        <div class="section-header compact">
            <div>
                <p class="eyebrow">Catalog</p>
                <h3>Manage products</h3>
            </div>
        </div>
        <div class="stacked">
            <?php foreach ($products as $product): ?>
                <form method="post" class="card compact product-row">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($product['id']) ?>">
                    <div class="grid grid-2 tight">
                        <label>Name <input name="name" value="<?= htmlspecialchars($product['name']) ?>"></label>
                        <label>Category
                            <select name="category">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat['id']) ?>" <?= ($product['category'] ?? '') === $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Price <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($product['price']) ?>"></label>
                        <label>Stock <input type="number" name="stock" value="<?= htmlspecialchars($product['stock'] ?? 0) ?>"></label>
                        <label class="full">Description <textarea name="description"><?= htmlspecialchars($product['description']) ?></textarea></label>
                        <label class="full">Image URL <input name="image" value="<?= htmlspecialchars($product['image']) ?>"></label>
                        <label>Status
                            <select name="status">
                                <?php foreach (['active','draft','archived'] as $status): ?>
                                    <option value="<?= $status ?>" <?= ($product['status'] ?? 'active') === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                    <div class="cta-row">
                        <button class="button ghost" type="submit">Save</button>
                        <button class="button danger" name="action" value="delete" onclick="return confirm('Delete product?')">Delete</button>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
