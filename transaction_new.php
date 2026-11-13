<?php
require_once __DIR__ . '/auth.php';
require_login();
$pdo=db(); $user=current_user(); $error=''; $success='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $type=$_POST['type']??'';
    $amount=filter_var($_POST['amount']??'', FILTER_VALIDATE_FLOAT);
    $date=$_POST['transaction_date']??date('Y-m-d');
    $note=trim($_POST['note']??'');
    $targetOutlet=is_admin()?(int)($_POST['outlet_id']??0):(int)$user['outlet_id'];
    if (!in_array($type,['income','expense'],true) || $amount===false || $amount<=0 || !$targetOutlet) $error='Periksa kembali jenis transaksi, outlet, dan nominal.';
    elseif ($date>date('Y-m-d')) $error='Tanggal transaksi tidak boleh di masa depan.';
    else {
        $check=$pdo->prepare("SELECT id FROM outlets WHERE id=? AND is_active=1"); $check->execute([$targetOutlet]);
        if (!$check->fetch()) $error='Outlet tidak ditemukan.';
        else {
            $ins=$pdo->prepare("INSERT INTO transactions(outlet_id,user_id,type,amount,note,transaction_date) VALUES(?,?,?,?,?,?)");
            $ins->execute([$targetOutlet,$user['id'],$type,$amount,$note,$date]);
            header('Location: transactions.php?saved=1'); exit;
        }
    }
}
$outlets=is_admin()?$pdo->query("SELECT id,name FROM outlets WHERE is_active=1 ORDER BY name")->fetchAll():[];
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Input transaksi • WASHFLOW</title><link rel="stylesheet" href="assets/style.css"></head><body>
<div class="app-shell"><aside class="sidebar"><a class="brand" href="dashboard.php"><span class="brand-mark small">W</span><span>WASHFLOW<small>OPERATIONS</small></span></a><nav><a href="dashboard.php">▦ <span>Dashboard</span></a><?php if(is_admin()):?><a href="outlets.php">▤ <span>Daftar outlet</span></a><?php endif;?><a href="transactions.php">⇄ <span>Riwayat transaksi</span></a><a class="active" href="transaction_new.php">＋ <span>Input transaksi</span></a></nav><div class="sidebar-bottom"><div class="user-chip"><div class="avatar"><?=e(strtoupper(substr($user['username'],0,1)))?></div><div><b><?=e($user['username'])?></b><small><?=is_admin()?'Administrator':e($user['outlet_name']??'Outlet')?></small></div></div><a class="logout" href="logout.php">Keluar ↗</a></div></aside>
<main class="main-content"><header class="topbar"><div><p class="eyebrow">FINANCE / TRANSAKSI</p><h1>Input transaksi</h1></div><a class="back-link" href="dashboard.php">← Dashboard</a></header>
<div class="form-layout"><section><h2>Catat transaksi baru</h2><p class="muted">Masukkan data sesuai catatan operasional outlet.</p>
<form method="post" class="panel form-panel"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<?php if($error):?><div class="alert"><?=e($error)?></div><?php endif;?>
<?php if(is_admin()):?><label>Outlet<select name="outlet_id" required><option value="">Pilih outlet</option><?php foreach($outlets as $o):?><option value="<?=e($o['id'])?>"><?=e($o['name'])?></option><?php endforeach;?></select></label><?php else:?><div class="readonly-outlet"><small>OUTLET</small><b><?=e($user['outlet_name']??'Outlet kamu')?></b></div><?php endif;?>
<label>Jenis transaksi<div class="type-options"><label class="type-option"><input type="radio" name="type" value="income" checked><span>＋ Pemasukan</span></label><label class="type-option"><input type="radio" name="type" value="expense"><span>− Pengeluaran</span></label></div></label>
<label>Nominal (Rupiah)<input type="number" name="amount" min="1" step="1" required placeholder="Contoh: 150000"></label>
<label>Tanggal transaksi<input type="date" name="transaction_date" value="<?=e(date('Y-m-d'))?>" max="<?=e(date('Y-m-d'))?>" required></label>
<label>Catatan <span class="optional">(opsional)</span><textarea name="note" rows="3" maxlength="250" placeholder="Contoh: pembayaran pelanggan / pembelian deterjen"></textarea></label>
<button class="btn primary full" type="submit">Simpan transaksi →</button></form></section><aside class="hint-card"><div class="hint-icon">✓</div><h3>Catatan penting</h3><p>Pastikan nominal dan tanggal sudah benar sebelum disimpan. Transaksi yang tercatat akan langsung memengaruhi ringkasan dashboard.</p></aside></div>
<footer>WASHFLOW <span>•</span> Laundry operations management</footer></main></div></body></html>
