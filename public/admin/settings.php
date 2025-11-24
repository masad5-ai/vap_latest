<?php
$adminPage = 'settings';
require __DIR__ . '/bootstrap.php';
$message = null;
$settingsData = load_settings();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $settingsData['branding']['store_name'] = trim($_POST['store_name']);
        $settingsData['branding']['tagline'] = trim($_POST['tagline']);
        $settingsData['branding']['accent'] = trim($_POST['accent']);
        $settingsData['branding']['logo'] = trim($_POST['logo']);
        $settingsData['branding']['banner'] = trim($_POST['banner']);
        $settingsData['branding']['support_email'] = trim($_POST['support_email']);
        $settingsData['branding']['support_phone'] = trim($_POST['support_phone']);
        $settingsData['branding']['address'] = trim($_POST['address']);
        $settingsData['branding']['hours'] = trim($_POST['hours']);
        $settingsData['promotions']['active'] = !empty($_POST['promo_active']);
        $settingsData['promotions']['headline'] = trim($_POST['promo_headline']);
        $settingsData['promotions']['subtext'] = trim($_POST['promo_subtext']);
        $settingsData['promotions']['badge'] = trim($_POST['promo_badge']);
        save_settings($settingsData);
        $message = 'Settings saved';
    } catch (Throwable $e) {
        $message = $e->getMessage();
    }
}
$adminTitle = 'Branding & storefront';
include __DIR__ . '/layout.php';
?>
<form method="post">
    <section class="section admin-dual">
        <div class="panel">
            <p class="eyebrow">Branding</p>
            <h3>Identity & theme</h3>
            <div class="stacked">
                <label>Store name <input name="store_name" value="<?= htmlspecialchars($settingsData['branding']['store_name'])?>"></label>
                <label>Tagline <input name="tagline" value="<?= htmlspecialchars($settingsData['branding']['tagline']) ?>"></label>
                <label>Accent color <input name="accent" value="<?= htmlspecialchars($settingsData['branding']['accent']) ?>"></label>
                <label>Logo URL <input name="logo" value="<?= htmlspecialchars($settingsData['branding']['logo'] ?? '') ?>"></label>
                <label>Homepage banner <input name="banner" value="<?= htmlspecialchars($settingsData['branding']['banner'] ?? '') ?>"></label>
            </div>
        </div>
        <div class="panel">
            <div class="section-header compact">
                <div>
                    <p class="eyebrow">Store info</p>
                    <h3>Contact & trust</h3>
                </div>
            </div>
            <div class="stacked">
                <label>Support email <input name="support_email" value="<?= htmlspecialchars($settingsData['branding']['support_email'] ?? '') ?>"></label>
                <label>Support phone <input name="support_phone" value="<?= htmlspecialchars($settingsData['branding']['support_phone'] ?? '') ?>"></label>
                <label>Address <input name="address" value="<?= htmlspecialchars($settingsData['branding']['address'] ?? '') ?>"></label>
                <label>Hours <input name="hours" value="<?= htmlspecialchars($settingsData['branding']['hours'] ?? '') ?>"></label>
            </div>
        </div>
    </section>
    <section class="section admin-dual">
        <div class="panel">
            <p class="eyebrow">Promotion</p>
            <h3>Sale banner</h3>
            <div class="stacked">
                <label><input type="checkbox" name="promo_active" value="1" <?= !empty($settingsData['promotions']['active']) ? 'checked' : '' ?>> Enable promo bar</label>
                <label>Headline <input name="promo_headline" value="<?= htmlspecialchars($settingsData['promotions']['headline'] ?? '') ?>"></label>
                <label>Subtext <input name="promo_subtext" value="<?= htmlspecialchars($settingsData['promotions']['subtext'] ?? '') ?>"></label>
                <label>Badge <input name="promo_badge" value="<?= htmlspecialchars($settingsData['promotions']['badge'] ?? '') ?>"></label>
            </div>
        </div>
        <div class="panel muted-panel">
            <p class="eyebrow">Preview</p>
            <h3><?= htmlspecialchars($settingsData['promotions']['headline'] ?? 'Seasonal offer') ?></h3>
            <p class="muted"><?= htmlspecialchars($settingsData['promotions']['subtext'] ?? 'Highlight discounts and bundles sitewide.') ?></p>
            <div class="pill" style="margin-top:10px; display:inline-flex;">Badge: <?= htmlspecialchars($settingsData['promotions']['badge'] ?? 'Limited') ?></div>
        </div>
    </section>
    <section class="section">
        <div class="panel">
            <div class="section-header compact">
                <div>
                    <p class="eyebrow">Resources</p>
                    <h3>Operational quick links</h3>
                </div>
            </div>
            <div class="pill-row">
                <a class="pill" href="shipping.php">Shipping calculator</a>
                <a class="pill" href="payments.php">Payment gateways</a>
                <a class="pill" href="notifications.php">Messaging stack</a>
                <a class="pill" href="categories.php">Categories</a>
            </div>
            <div class="cta-row"><button class="button primary" type="submit">Save branding</button></div>
        </div>
    </section>
</form>
<?php include __DIR__ . '/footer.php'; ?>
