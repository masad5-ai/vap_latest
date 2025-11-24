<?php
$adminPage = 'categories';
require __DIR__ . '/bootstrap.php';
$message = null;
$settingsData = load_settings();
$categories = $settingsData['catalog']['categories'] ?? [];

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
<section class="section admin-dual">
    <div class="panel">
        <p class="eyebrow">Taxonomy</p>
        <h3>Create category</h3>
        <form method="post" class="stacked">
            <input type="hidden" name="action" value="add">
            <label>Slug / ID <input name="id" placeholder="devices, pods, accessories"></label>
            <label>Name <input required name="name" placeholder="Devices"></label>
            <label>Description <textarea name="description" placeholder="What belongs in this collection?"></textarea></label>
            <button class="button primary" type="submit">Add category</button>
        </form>
    </div>
    <div class="panel">
        <div class="section-header compact">
            <div>
                <p class="eyebrow">Collections</p>
                <h3>Manage categories</h3>
            </div>
        </div>
        <div class="stacked">
            <?php foreach ($categories as $category): ?>
                <form method="post" class="card compact">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($category['id']) ?>">
                    <label>Slug / ID <input name="id_display" value="<?= htmlspecialchars($category['id']) ?>" disabled></label>
                    <label>Name <input name="name" value="<?= htmlspecialchars($category['name']) ?>"></label>
                    <label>Description <textarea name="description"><?= htmlspecialchars($category['description']) ?></textarea></label>
                    <div class="cta-row">
                        <button class="button ghost" type="submit">Save</button>
                        <button class="button danger" name="action" value="delete" onclick="return confirm('Delete category?')">Delete</button>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
