<?php
require_once __DIR__ . '/config.php';
$message='';
try {
    $count=(int)db()->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if($count>0){$message='Instalasi sudah pernah dijalankan. Hapus file install.php sekarang.';}
    else{
        $pdo=db();$pdo->beginTransaction();
        $pdo->exec("INSERT INTO outlets(name,city) VALUES ('Outlet Demo Medan','Medan')");
        $oid=(int)$pdo->lastInsertId();
        $s=$pdo->prepare("INSERT INTO users(username,password_hash,role,outlet_id) VALUES (?,?,?,?)");
        $s->execute(['admin',password_hash('AdminDemo!2026',PASSWORD_DEFAULT),'admin',null]);
        $s->execute(['medan01',password_hash('OutletDemo!2026',PASSWORD_DEFAULT),'outlet',$oid]);
        $pdo->commit();
        $message='Akun demo berhasil dibuat. Admin: admin / AdminDemo!2026. Outlet: medan01 / OutletDemo!2026. SEGERA ubah password dan hapus install.php.';
    }
}catch(Throwable $e){$message='Gagal: '.$e->getMessage();}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Setup WASHFLOW</title><link rel="stylesheet" href="assets/style.css"></head><body class="login-body"><main class="login-wrap"><div class="brand-mark">W</div><h1>Setup WASHFLOW</h1><div class="panel form-panel"><?=e($message)?><p><a class="btn primary full" href="login.php">Ke halaman login</a></p></div></main></body></html>
