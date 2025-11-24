<?php
$adminPage = 'payments';
require __DIR__ . '/bootstrap.php';
$message = null;
$settingsData = load_settings();
$payments = $settingsData['payments'] ?? [];

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
<section class="section admin-dual">
    <div class="panel">
        <p class="eyebrow">Add gateway</p>
        <h3>Custom, COD, Afterpay</h3>
        <form method="post" class="stacked">
            <input type="hidden" name="action" value="add">
            <label>Key <input name="id" placeholder="custom_gateway, afterpay"></label>
            <label>Label <input required name="label" placeholder="Afterpay"></label>
            <label>Merchant ID <input name="merchant_id" placeholder="merchant-123"></label>
            <label>Customer instructions <textarea name="instructions" placeholder="Shown on checkout"></textarea></label>
            <button class="button primary" type="submit">Add gateway</button>
        </form>
    </div>
    <div class="panel">
        <div class="section-header compact">
            <div>
                <p class="eyebrow">Checkouts</p>
                <h3>Available methods</h3>
            </div>
        </div>
        <div class="stacked">
            <?php foreach ($payments as $key => $payment): ?>
                <form method="post" class="card compact stacked">
                    <input type="hidden" name="action" value="update">
                    <div class="pill-row">
                        <label class="pill-row">Enable <input type="checkbox" name="payments[<?= htmlspecialchars($key) ?>][enabled]" <?= !empty($payment['enabled']) ? 'checked' : '' ?>></label>
                        <span class="pill muted">Key: <?= htmlspecialchars($key) ?></span>
                    </div>
                    <label>Label <input name="payments[<?= htmlspecialchars($key) ?>][label]" value="<?= htmlspecialchars($payment['label'] ?? ucfirst($key)) ?>"></label>
                    <label>Merchant ID <input name="payments[<?= htmlspecialchars($key) ?>][merchant_id]" value="<?= htmlspecialchars($payment['merchant_id'] ?? '') ?>"></label>
                    <label>Customer instructions <textarea name="payments[<?= htmlspecialchars($key) ?>][instructions]"><?= htmlspecialchars($payment['instructions'] ?? '') ?></textarea></label>
                    <div class="cta-row">
                        <button class="button ghost" type="submit">Save</button>
                        <button class="button danger" name="action" value="delete" onclick="return confirm('Delete gateway?')">Delete</button>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($key) ?>">
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
