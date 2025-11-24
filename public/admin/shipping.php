<?php
$adminPage = 'shipping';
require __DIR__ . '/bootstrap.php';
$message = null;
$settingsData = load_settings();
$shipping = $settingsData['shipping'] ?? ['options' => []];
$options = $shipping['options'];
$editId = $_GET['edit'] ?? null;
$editing = $editId && isset($options[$editId]) ? ['id' => $editId] + $options[$editId] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'add') {
            $id = strtolower(trim(preg_replace('/[^a-zA-Z0-9_-]+/', '-', $_POST['id'] ?? '')));
            if ($id === '') {
                $id = substr(generate_id(), 0, 6);
            }
            $options[$id] = [
                'enabled' => true,
                'label' => trim($_POST['label']),
                'eta' => trim($_POST['eta']),
                'base_rate' => (float)$_POST['base_rate'],
                'per_item' => (float)$_POST['per_item'],
                'free_over' => (float)$_POST['free_over'],
                'zone' => trim($_POST['zone']),
            ];
            $shipping['default'] = $shipping['default'] ?? $id;
            $message = 'Shipping method added';
        } elseif ($action === 'update') {
            foreach ($_POST['shipping'] as $key => $payload) {
                if (!isset($options[$key])) continue;
                $options[$key]['enabled'] = !empty($payload['enabled']);
                $options[$key]['label'] = $payload['label'];
                $options[$key]['eta'] = $payload['eta'];
                $options[$key]['base_rate'] = (float)$payload['base_rate'];
                $options[$key]['per_item'] = (float)$payload['per_item'];
                $options[$key]['free_over'] = (float)$payload['free_over'];
                $options[$key]['zone'] = $payload['zone'] ?? '';
            }
            $shipping['default'] = $_POST['shipping_default'] ?? $shipping['default'];
            $message = 'Shipping updated';
        } elseif ($action === 'delete' && isset($_POST['id'])) {
            unset($options[$_POST['id']]);
            if (($shipping['default'] ?? '') === ($_POST['id'] ?? '')) {
                $shipping['default'] = array_key_first($options);
            }
            $message = 'Shipping method removed';
        }
        $shipping['options'] = $options;
        $settingsData['shipping'] = $shipping;
        save_settings($settingsData);
    } catch (Throwable $e) {
        $message = $e->getMessage();
    }
}

$adminTitle = 'Shipping & calculations';
include __DIR__ . '/layout.php';
?>
<section class="section admin-grid">
    <div class="panel">
        <div class="section-header compact">
            <div>
                <p class="eyebrow">Live calculator</p>
                <h3>Shipping options</h3>
                <p class="muted">Grid view with enable toggles, zones, and quick edit actions.</p>
            </div>
            <form method="post" class="inline-form">
                <input type="hidden" name="action" value="update">
                <select name="shipping_default">
                    <?php foreach ($options as $key => $option): ?>
                        <option value="<?= htmlspecialchars($key) ?>" <?= ($shipping['default'] ?? '') === $key ? 'selected' : '' ?>>Default: <?= htmlspecialchars($option['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="button ghost" type="submit">Save default</button>
            </form>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Key</th>
                    <th>Label</th>
                    <th>Zone</th>
                    <th>Rates</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($options as $key => $option): ?>
                    <tr>
                        <td><span class="pill muted"><?= htmlspecialchars($key) ?></span></td>
                        <td>
                            <div class="stacked tight">
                                <strong><?= htmlspecialchars($option['label']) ?></strong>
                                <span class="small-text">ETA <?= htmlspecialchars($option['eta']) ?></span>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($option['zone'] ?? 'All zones') ?></td>
                        <td class="small-text">Base $<?= number_format((float)$option['base_rate'], 2) ?> · +$<?= number_format((float)$option['per_item'], 2) ?>/item · Free over $<?= number_format((float)$option['free_over'], 2) ?></td>
                        <td><span class="status-pill <?= !empty($option['enabled']) ? 'active' : 'draft' ?>"><?= !empty($option['enabled']) ? 'Enabled' : 'Disabled' ?></span></td>
                        <td>
                            <div class="table-actions">
                                <a class="button small ghost" href="shipping.php?edit=<?= urlencode($key) ?>#editor">Edit</a>
                                <form method="post" class="inline-form" onsubmit="return confirm('Delete shipping method?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($key) ?>">
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
        <p class="eyebrow">Method editor</p>
        <h3><?= $editing ? 'Edit shipping method' : 'Add method' ?></h3>
        <p class="muted">Use the editor for courier, pickup, or zone-based rates without scrolling the grid.</p>
        <form method="post" class="stacked">
            <input type="hidden" name="action" value="<?= $editing ? 'update' : 'add' ?>">
            <?php if ($editing): ?>
                <input type="hidden" name="shipping_default" value="<?= htmlspecialchars($shipping['default'] ?? '') ?>">
                <input type="hidden" name="id" value="<?= htmlspecialchars($editing['id']) ?>">
            <?php endif; ?>
            <label>Key <input name="id" value="<?= htmlspecialchars($editing['id'] ?? '') ?>" placeholder="express, same-day" <?= $editing ? 'readonly' : '' ?>></label>
            <label>Label <input required name="<?= $editing ? 'shipping[' . htmlspecialchars($editing['id']) . '][label]' : 'label' ?>" value="<?= htmlspecialchars($editing['label'] ?? '') ?>" placeholder="Same-day metro"></label>
            <label>ETA <input required name="<?= $editing ? 'shipping[' . htmlspecialchars($editing['id']) . '][eta]' : 'eta' ?>" value="<?= htmlspecialchars($editing['eta'] ?? '') ?>" placeholder="Delivered tonight"></label>
            <div class="grid grid-3 tight">
                <label>Base rate <input type="number" step="0.01" name="<?= $editing ? 'shipping[' . htmlspecialchars($editing['id']) . '][base_rate]' : 'base_rate' ?>" value="<?= htmlspecialchars($editing['base_rate'] ?? 12.00) ?>"></label>
                <label>Per item <input type="number" step="0.01" name="<?= $editing ? 'shipping[' . htmlspecialchars($editing['id']) . '][per_item]' : 'per_item' ?>" value="<?= htmlspecialchars($editing['per_item'] ?? 0) ?>"></label>
                <label>Free over <input type="number" step="0.01" name="<?= $editing ? 'shipping[' . htmlspecialchars($editing['id']) . '][free_over]' : 'free_over' ?>" value="<?= htmlspecialchars($editing['free_over'] ?? 150) ?>"></label>
            </div>
            <label>Zone / notes <input name="<?= $editing ? 'shipping[' . htmlspecialchars($editing['id']) . '][zone]' : 'zone' ?>" value="<?= htmlspecialchars($editing['zone'] ?? '') ?>" placeholder="Metro, regional, pickup"></label>
            <?php if ($editing): ?>
                <label class="pill-row">Enable <input type="checkbox" name="shipping[<?= htmlspecialchars($editing['id']) ?>][enabled]" <?= !empty($editing['enabled']) ? 'checked' : '' ?>></label>
            <?php endif; ?>
            <div class="cta-row">
                <button class="button primary" type="submit"><?= $editing ? 'Save changes' : 'Add method' ?></button>
                <?php if ($editing): ?><a class="button ghost" href="shipping.php">Cancel</a><?php endif; ?>
            </div>
        </form>
    </div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
