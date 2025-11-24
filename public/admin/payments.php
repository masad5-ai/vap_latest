<?php
$adminPage = 'payments';
require __DIR__ . '/bootstrap.php';
$message = null;
$settingsData = load_settings();
$payments = $settingsData['payments'] ?? [];
$editId = $_GET['edit'] ?? null;
$editing = $editId && isset($payments[$editId]) ? ['id' => $editId] + $payments[$editId] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'add') {
            $id = strtolower(trim(preg_replace('/[^a-zA-Z0-9_-]+/', '-', $_POST['id'] ?? '')));
            if ($id === '') {
                $id = substr(generate_id(), 0, 6);
            }
            $payments[$id] = [
                'enabled' => true,
                'label' => trim($_POST['label']),
                'instructions' => trim($_POST['instructions']),
                'merchant_id' => trim($_POST['merchant_id']),
            ];
            $message = 'Gateway added';
        } elseif ($action === 'update') {
            foreach ($_POST['payments'] as $key => $payload) {
                if (!isset($payments[$key])) continue;
                $payments[$key]['enabled'] = !empty($payload['enabled']);
                $payments[$key]['label'] = trim($payload['label']);
                $payments[$key]['instructions'] = trim($payload['instructions']);
                $payments[$key]['merchant_id'] = trim($payload['merchant_id']);
            }
            $message = 'Payments saved';
        } elseif ($action === 'delete' && isset($_POST['id'])) {
            unset($payments[$_POST['id']]);
            $message = 'Gateway removed';
        }
        $settingsData['payments'] = $payments;
        save_settings($settingsData);
    } catch (Throwable $e) {
        $message = $e->getMessage();
    }
}

$adminTitle = 'Payment gateways';
include __DIR__ . '/layout.php';
?>
<section class="section admin-grid">
    <div class="panel">
        <div class="section-header compact">
            <div>
                <p class="eyebrow">Checkouts</p>
                <h3>Available methods</h3>
                <p class="muted">Grid-based control for COD, Afterpay, custom bank details, and more.</p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Key</th>
                    <th>Label</th>
                    <th>Merchant</th>
                    <th>Customer copy</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($payments as $key => $payment): ?>
                    <tr>
                        <td><span class="pill muted"><?= htmlspecialchars($key) ?></span></td>
                        <td><strong><?= htmlspecialchars($payment['label'] ?? ucfirst($key)) ?></strong></td>
                        <td class="small-text"><?= htmlspecialchars($payment['merchant_id'] ?? 'Not set') ?></td>
                        <td class="small-text"><?= htmlspecialchars(substr($payment['instructions'] ?? 'Shown on checkout receipt', 0, 120)) ?><?= strlen($payment['instructions'] ?? '') > 120 ? '…' : '' ?></td>
                        <td><span class="status-pill <?= !empty($payment['enabled']) ? 'active' : 'draft' ?>"><?= !empty($payment['enabled']) ? 'Enabled' : 'Disabled' ?></span></td>
                        <td>
                            <div class="table-actions">
                                <a class="button small ghost" href="payments.php?edit=<?= urlencode($key) ?>#editor">Edit</a>
                                <form method="post" class="inline-form" onsubmit="return confirm('Delete gateway?');">
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
        <p class="eyebrow">Gateway editor</p>
        <h3><?= $editing ? 'Edit gateway' : 'Add gateway' ?></h3>
        <p class="muted">Update API keys, checkout copy, and availability without scrolling the grid.</p>
        <form method="post" class="stacked">
            <input type="hidden" name="action" value="<?= $editing ? 'update' : 'add' ?>">
            <?php if ($editing): ?><input type="hidden" name="id" value="<?= htmlspecialchars($editing['id']) ?>"><?php endif; ?>
            <label>Key <input name="id" value="<?= htmlspecialchars($editing['id'] ?? '') ?>" placeholder="custom_gateway, afterpay" <?= $editing ? 'readonly' : '' ?>></label>
            <label>Label <input required name="<?= $editing ? 'payments[' . htmlspecialchars($editing['id']) . '][label]' : 'label' ?>" value="<?= htmlspecialchars($editing['label'] ?? '') ?>" placeholder="Afterpay"></label>
            <label>Merchant ID <input name="<?= $editing ? 'payments[' . htmlspecialchars($editing['id']) . '][merchant_id]' : 'merchant_id' ?>" value="<?= htmlspecialchars($editing['merchant_id'] ?? '') ?>" placeholder="merchant-123"></label>
            <label>Customer instructions <textarea name="<?= $editing ? 'payments[' . htmlspecialchars($editing['id']) . '][instructions]' : 'instructions' ?>" placeholder="Shown on checkout"><?= htmlspecialchars($editing['instructions'] ?? '') ?></textarea></label>
            <?php if ($editing): ?>
                <label class="pill-row">Enable <input type="checkbox" name="payments[<?= htmlspecialchars($editing['id']) ?>][enabled]" <?= !empty($editing['enabled']) ? 'checked' : '' ?>></label>
            <?php endif; ?>
            <div class="cta-row">
                <button class="button primary" type="submit"><?= $editing ? 'Save gateway' : 'Add gateway' ?></button>
                <?php if ($editing): ?><a class="button ghost" href="payments.php">Cancel</a><?php endif; ?>
            </div>
        </form>
    </div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
