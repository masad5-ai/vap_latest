<?php
$adminPage = 'categories';
require __DIR__ . '/bootstrap.php';
$message = null;
$settingsData = load_settings();
$categories = $settingsData['catalog']['categories'] ?? [];
$editId = $_GET['edit'] ?? null;
$editing = null;
foreach ($categories as $cat) {
    if ($cat['id'] === $editId) {
        $editing = $cat;
        break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'add') {
            $id = strtolower(trim(preg_replace('/[^a-zA-Z0-9_-]+/', '-', $_POST['id'] ?? '')));
            if ($id === '') {
                $id = substr(generate_id(), 0, 6);
            }
            $categories[] = [
                'id' => $id,
                'name' => trim($_POST['name']),
                'description' => trim($_POST['description']),
            ];
            $message = 'Category added';
        } elseif ($action === 'update') {
            $id = $_POST['id'];
            foreach ($categories as &$category) {
                if ($category['id'] === $id) {
                    $category['name'] = trim($_POST['name']);
                    $category['description'] = trim($_POST['description']);
                }
            }
            unset($category);
            $message = 'Category updated';
        } elseif ($action === 'delete') {
            $categories = array_values(array_filter($categories, fn($c) => $c['id'] !== $_POST['id']));
            $message = 'Category removed';
        }
        $settingsData['catalog']['categories'] = $categories;
        save_settings($settingsData);
    } catch (Throwable $e) {
        $message = $e->getMessage();
    }
}

$adminTitle = 'Categories';
include __DIR__ . '/layout.php';
?>
<section class="section admin-grid">
    <div class="panel">
        <div class="section-header compact">
            <div>
                <p class="eyebrow">Taxonomy</p>
                <h3>Category grid</h3>
                <p class="muted">Professional grid with edit/view/delete actions to keep collections tidy.</p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Slug</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><span class="pill muted"><?= htmlspecialchars($category['id']) ?></span></td>
                        <td><strong><?= htmlspecialchars($category['name']) ?></strong></td>
                        <td class="small-text"><?= htmlspecialchars($category['description']) ?></td>
                        <td>
                            <div class="table-actions">
                                <a class="button small ghost" href="categories.php?edit=<?= urlencode($category['id']) ?>#editor">Edit</a>
                                <form method="post" class="inline-form" onsubmit="return confirm('Delete category?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($category['id']) ?>">
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
        <p class="eyebrow">Category editor</p>
        <h3><?= $editing ? 'Update category' : 'Create category' ?></h3>
        <p class="muted">Focused form for clean IDs, labels, and merchandising copy.</p>
        <form method="post" class="stacked">
            <input type="hidden" name="action" value="<?= $editing ? 'update' : 'add' ?>">
            <?php if ($editing): ?><input type="hidden" name="id" value="<?= htmlspecialchars($editing['id']) ?>"><?php endif; ?>
            <label>Slug / ID <input name="id" value="<?= htmlspecialchars($editing['id'] ?? '') ?>" placeholder="devices, pods, accessories" <?= $editing ? 'readonly' : '' ?>></label>
            <label>Name <input required name="name" value="<?= htmlspecialchars($editing['name'] ?? '') ?>" placeholder="Devices"></label>
            <label>Description <textarea name="description" placeholder="What belongs in this collection?"><?= htmlspecialchars($editing['description'] ?? '') ?></textarea></label>
            <div class="cta-row">
                <button class="button primary" type="submit"><?= $editing ? 'Save changes' : 'Add category' ?></button>
                <?php if ($editing): ?><a class="button ghost" href="categories.php">Cancel</a><?php endif; ?>
            </div>
        </form>
    </div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
