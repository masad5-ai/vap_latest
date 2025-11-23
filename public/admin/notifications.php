<?php
$adminPage = 'notifications';
require __DIR__ . '/bootstrap.php';
$message = null;
$settingsData = load_settings();
$notifications = $settingsData['notifications'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $notifications['email']['enabled'] = !empty($_POST['notifications']['email']['enabled']);
        $notifications['email']['from'] = trim($_POST['notifications']['email']['from']);
        $notifications['email']['smtp_host'] = trim($_POST['notifications']['email']['smtp_host']);
        $notifications['email']['smtp_user'] = trim($_POST['notifications']['email']['smtp_user']);
        $notifications['whatsapp']['enabled'] = !empty($_POST['notifications']['whatsapp']['enabled']);
        $notifications['whatsapp']['number'] = trim($_POST['notifications']['whatsapp']['number']);
        $notifications['whatsapp']['signature'] = trim($_POST['notifications']['whatsapp']['signature']);
        $notifications['whatsapp']['api_key'] = trim($_POST['notifications']['whatsapp']['api_key']);
        $notifications['whatsapp']['template'] = trim($_POST['notifications']['whatsapp']['template']);
        $settingsData['notifications'] = $notifications;
        save_settings($settingsData);
        $message = 'Notification settings saved';
    } catch (Throwable $e) {
        $message = $e->getMessage();
    }
}

$adminTitle = 'Messaging & alerts';
include __DIR__ . '/layout.php';
?>
<form method="post">
    <section class="section admin-dual">
        <div class="panel">
            <p class="eyebrow">Email</p>
            <h3>Transactional SMTP</h3>
            <div class="stacked">
                <input type="hidden" name="notifications[email][enabled]" value="0">
                <label class="pill-row">Enable <input type="checkbox" name="notifications[email][enabled]" <?= !empty($notifications['email']['enabled']) ? 'checked' : '' ?>></label>
                <label>From address <input name="notifications[email][from]" value="<?= htmlspecialchars($notifications['email']['from'] ?? '') ?>"></label>
                <label>SMTP host <input name="notifications[email][smtp_host]" value="<?= htmlspecialchars($notifications['email']['smtp_host'] ?? '') ?>"></label>
                <label>SMTP user <input name="notifications[email][smtp_user]" value="<?= htmlspecialchars($notifications['email']['smtp_user'] ?? '') ?>"></label>
            </div>
        </div>
        <div class="panel">
            <div class="section-header compact">
                <div>
                    <p class="eyebrow">WhatsApp / SMS</p>
                    <h3>Delivery rails</h3>
                </div>
            </div>
            <div class="stacked">
                <input type="hidden" name="notifications[whatsapp][enabled]" value="0">
                <label class="pill-row">Enable <input type="checkbox" name="notifications[whatsapp][enabled]" <?= !empty($notifications['whatsapp']['enabled']) ? 'checked' : '' ?>></label>
                <label>Number <input name="notifications[whatsapp][number]" value="<?= htmlspecialchars($notifications['whatsapp']['number'] ?? '') ?>"></label>
                <label>Signature <input name="notifications[whatsapp][signature]" value="<?= htmlspecialchars($notifications['whatsapp']['signature'] ?? '') ?>"></label>
                <label>API key <input name="notifications[whatsapp][api_key]" value="<?= htmlspecialchars($notifications['whatsapp']['api_key'] ?? '') ?>"></label>
                <label>Template ID <input name="notifications[whatsapp][template]" value="<?= htmlspecialchars($notifications['whatsapp']['template'] ?? '') ?>"></label>
            </div>
        </div>
    </section>
    <section class="section">
        <div class="panel">
            <div class="cta-row">
                <button class="button primary" type="submit">Save messaging stack</button>
            </div>
        </div>
    </section>
</form>
<?php include __DIR__ . '/footer.php'; ?>
