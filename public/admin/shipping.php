<?php
$adminPage = 'shipping';
require __DIR__ . '/bootstrap.php';
$message = null;
$settingsData = load_settings();
$shipping = $settingsData['shipping'] ?? ['options' => []];
$options = $shipping['options'];

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
<section class="section admin-dual">
    <div class="panel">
        <p class="eyebrow">Add method</p>
        <h3>Courier, pickup, or zone</h3>
        <form method="post" class="stacked">
            <input type="hidden" name="action" value="add">
            <label>Key <input name="id" placeholder="express, same-day"></label>
            <label>Label <input required name="label" placeholder="Same-day metro"></label>
            <label>ETA <input required name="eta" placeholder="Delivered tonight"></label>
            <div class="grid grid-3 tight">
                <label>Base rate <input type="number" step="0.01" name="base_rate" value="12.00"></label>
                <label>Per item <input type="number" step="0.01" name="per_item" value="0"></label>
                <label>Free over <input type="number" step="0.01" name="free_over" value="150"></label>
            </div>
            <label>Zone / notes <input name="zone" placeholder="Metro, regional, pickup"></label>
            <button class="button primary" type="submit">Add method</button>
        </form>
    </div>
    <div class="panel">
        <div class="section-header compact">
            <div>
                <p class="eyebrow">Live calculator</p>
                <h3>Shipping options</h3>
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
        <div class="stacked">
            <?php foreach ($options as $key => $option): ?>
                <form method="post" class="card compact stacked">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="shipping_default" value="<?= htmlspecialchars($shipping['default'] ?? '') ?>">
                    <div class="pill-row">
                        <label class="pill-row">Enable <input type="checkbox" name="shipping[<?= htmlspecialchars($key) ?>][enabled]" <?= !empty($option['enabled']) ? 'checked' : '' ?>></label>
                        <span class="pill muted">ETA <?= htmlspecialchars($option['eta']) ?></span>
                    </div>
                    <label>Label <input name="shipping[<?= htmlspecialchars($key) ?>][label]" value="<?= htmlspecialchars($option['label']) ?>"></label>
                    <label>ETA <input name="shipping[<?= htmlspecialchars($key) ?>][eta]" value="<?= htmlspecialchars($option['eta']) ?>"></label>
                    <label>Zone / notes <input name="shipping[<?= htmlspecialchars($key) ?>][zone]" value="<?= htmlspecialchars($option['zone'] ?? '') ?>"></label>
                    <div class="grid grid-3 tight">
                        <label>Base rate <input type="number" step="0.01" name="shipping[<?= htmlspecialchars($key) ?>][base_rate]" value="<?= htmlspecialchars($option['base_rate']) ?>"></label>
                        <label>Per item <input type="number" step="0.01" name="shipping[<?= htmlspecialchars($key) ?>][per_item]" value="<?= htmlspecialchars($option['per_item']) ?>"></label>
                        <label>Free over <input type="number" step="0.01" name="shipping[<?= htmlspecialchars($key) ?>][free_over]" value="<?= htmlspecialchars($option['free_over']) ?>"></label>
                    </div>
                    <div class="cta-row">
                        <button class="button ghost" type="submit">Save changes</button>
                        <button class="button danger" name="action" value="delete" onclick="return confirm('Delete shipping method?')">Delete</button>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($key) ?>">
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
