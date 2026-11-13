<?php
require_once __DIR__ . '/auth.php';
if (!empty($_SESSION['user'])) { header('Location: dashboard.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = db()->prepare("SELECT u.id,u.username,u.password_hash,u.role,u.outlet_id,o.name AS outlet_name FROM users u LEFT JOIN outlets o ON o.id=u.outlet_id WHERE u.username=? AND u.is_active=1 LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Username atau password tidak cocok.';
}
?>
<!doctype html><html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk • WASHFLOW</title><link rel="stylesheet" href="assets/style.css"></head>
<body class="login-body"><main class="login-wrap">
<div class="brand-mark">W</div><p class="eyebrow">LAUNDRY OPERATIONS</p><h1>WASHFLOW</h1>
<p class="muted">Satu tempat untuk memantau seluruh operasional outlet.</p>
<form method="post" class="panel login-panel">
<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<label>Username<input name="username" autocomplete="username" required placeholder="Masukkan username"></label>
<label>Password<input type="password" name="password" autocomplete="current-password" required placeholder="Masukkan password"></label>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<button class="btn primary full" type="submit">Masuk ke dashboard <span>→</span></button>
</form><p class="tiny muted">Akses hanya untuk akun yang terdaftar.</p>
</main></body></html>
