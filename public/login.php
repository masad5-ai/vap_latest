<?php
require __DIR__ . '/includes/bootstrap.php';
$page = 'login';
$pageTitle = 'Login | ' . $settings['branding']['store_name'];
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'register') {
            $user = register_user(trim($_POST['name']), trim($_POST['email']), $_POST['password']);
            $_SESSION['user'] = $user;
            header('Location: /account.php');
            exit;
        }
        if ($action === 'login') {
            $user = login_user(trim($_POST['email']), $_POST['password']);
            if (!$user) {
                throw new RuntimeException('Invalid credentials');
            }
            header('Location: /account.php');
            exit;
        }
    } catch (Throwable $e) {
        $message = $e->getMessage();
    }
}
include __DIR__ . '/includes/header.php';
?>
<section class="section auth-grid">
    <div>
        <p class="eyebrow">Return customers</p>
        <h2>Sign in to track orders</h2>
        <?php if ($message): ?><div class="flash"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <form method="post" class="stacked">
            <input type="hidden" name="action" value="login">
            <label>Email <input type="email" required name="email"></label>
            <label>Password <input type="password" required name="password"></label>
            <button class="button primary" type="submit">Login</button>
        </form>
    </div>
    <div class="panel">
        <p class="eyebrow">New to <?= htmlspecialchars($settings['branding']['store_name']) ?>?</p>
        <h3>Create an account</h3>
        <form method="post" class="stacked">
            <input type="hidden" name="action" value="register">
            <label>Name <input required name="name"></label>
            <label>Email <input type="email" required name="email"></label>
            <label>Password <input type="password" required name="password"></label>
            <button class="button ghost" type="submit">Register</button>
        </form>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
