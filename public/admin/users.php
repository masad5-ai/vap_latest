<?php
$adminPage = 'users';
require __DIR__ . '/bootstrap.php';
$message = null;
$users = load_users();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user = set_user_role($_POST['id'], $_POST['role']);
        $message = $user['name'] . ' updated';
        $users = load_users();
    } catch (Throwable $e) {
        $message = $e->getMessage();
    }
}
$adminTitle = 'User access';
include __DIR__ . '/layout.php';
?>
<section class="section">
    <div class="section-header compact">
        <div>
            <p class="eyebrow">Team</p>
            <h3>Manage roles</h3>
        </div>
    </div>
    <div class="stacked">
        <?php foreach ($users as $member): ?>
            <form method="post" class="card compact">
                <input type="hidden" name="id" value="<?= htmlspecialchars($member['id']) ?>">
                <div>
                    <strong><?= htmlspecialchars($member['name']) ?></strong>
                    <p class="muted"><?= htmlspecialchars($member['email']) ?></p>
                </div>
                <div class="pill-row">
                    <select name="role">
                        <?php foreach (['customer','admin'] as $role): ?>
                            <option value="<?= $role ?>" <?= ($member['role'] ?? 'customer') === $role ? 'selected' : '' ?>><?= ucfirst($role) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="button ghost" type="submit">Save</button>
                </div>
            </form>
        <?php endforeach; ?>
    </div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
