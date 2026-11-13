<?php
require_once __DIR__ . '/auth.php';
require_admin();
$pdo=db();$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $name=trim($_POST['name']??'');$city=trim($_POST['city']??'');$username=trim($_POST['username']??'');$password=$_POST['password']??'';
    if($name===''||$city===''||$username===''||strlen($password)<8)$error='Lengkapi semua data. Password minimal 8 karakter.';
    else{
        try{$pdo->beginTransaction();$s=$pdo->prepare("INSERT INTO outlets(name,city) VALUES(?,?)");$s->execute([$name,$city]);$oid=(int)$pdo->lastInsertId();
        $u=$pdo->prepare("INSERT INTO users(username,password_hash,role,outlet_id) VALUES(?,?,'outlet',?)");$u->execute([$username,password_hash($password,PASSWORD_DEFAULT),$oid]);$pdo->commit();header('Location: outlets.php?created=1');exit;
        }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$error='Gagal membuat outlet. Pastikan username belum digunakan.';}
    }
}
$outlets=$pdo->query("SELECT o.id,o.name,o.city,o.is_active,u.username FROM outlets o LEFT JOIN users u ON u.outlet_id=o.id AND u.role='outlet' ORDER BY o.name")->fetchAll();
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Daftar outlet • WASHFLOW</title><link rel="stylesheet" href="assets/style.css"></head><body><div class="app-shell"><aside class="sidebar"><a class="brand" href="dashboard.php"><span class="brand-mark small">W</span><span>WASHFLOW<small>OPERATIONS</small></span></a><nav><a href="dashboard.php">▦ <span>Dashboard</span></a><a class="active" href="outlets.php">▤ <span>Daftar outlet</span></a><a href="transactions.php">⇄ <span>Semua transaksi</span></a><a href="transaction_new.php">＋ <span>Input transaksi</span></a></nav><div class="sidebar-bottom"><div class="user-chip"><div class="avatar">A</div><div><b><?=e(current_user()['username'])?></b><small>Administrator</small></div></div><a class="logout" href="logout.php">Keluar ↗</a></div></aside>
<main class="main-content"><header class="topbar"><div><p class="eyebrow">MANAGEMENT / NETWORK</p><h1>Daftar outlet</h1></div></header>
<?php if(isset($_GET['created'])):?><div class="success-banner">✓ Outlet dan akun berhasil dibuat.</div><?php endif;?>
<div class="outlet-layout"><section><div class="section-heading"><div><h2>Seluruh outlet</h2><p class="muted"><?=count($outlets)?> outlet terdaftar</p></div></div><div class="panel table-panel"><div class="table-scroll"><table><thead><tr><th>Nama outlet</th><th>Kota</th><th>Username</th><th>Status</th></tr></thead><tbody><?php foreach($outlets as $o):?><tr><td><b><?=e($o['name'])?></b></td><td><?=e($o['city'])?></td><td><?=e($o['username']??'-')?></td><td><span class="badge badge-green">Aktif</span></td></tr><?php endforeach;?></tbody></table></div></div></section>
<section><h2>Tambah outlet</h2><p class="muted">Buat outlet sekaligus akun login.</p><form method="post" class="panel form-panel"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><?php if($error):?><div class="alert"><?=e($error)?></div><?php endif;?><label>Nama outlet<input name="name" required placeholder="Contoh: Washflow Medan"></label><label>Kota<input name="city" required placeholder="Contoh: Medan"></label><label>Username login<input name="username" required placeholder="Username unik"></label><label>Password sementara<input type="password" name="password" minlength="8" required placeholder="Minimal 8 karakter"></label><button class="btn primary full">Buat outlet + akun</button></form></section></div><footer>WASHFLOW <span>•</span> Laundry operations management</footer></main></div></body></html>
