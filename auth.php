<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

function require_login(): void {
    if (empty($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}
function current_user(): array {
    return $_SESSION['user'] ?? [];
}
function is_admin(): bool {
    return (current_user()['role'] ?? '') === 'admin';
}
function require_admin(): void {
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        exit('Akses ditolak.');
    }
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verify_csrf(): void {
    $token = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Sesi formulir tidak valid. Muat ulang halaman.');
    }
}
function money(float|int|string $amount): string {
    return 'Rp ' . number_format((float)$amount, 0, ',', '.');
}
