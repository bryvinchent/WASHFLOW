<?php
require_once __DIR__ . '/auth.php';
require_login();
$pdo=db(); $user=current_user();
$from=$_GET['from']??date('Y-m-01'); $to=$_GET['to']??date('Y-m-d');
$type=$_GET['type']??'';
$sql="SELECT t.id,t.type,t.amount,t.note,t.transaction_date,o.name outlet_name,u.username created_by FROM transactions t JOIN outlets o ON o.id=t.outlet_id JOIN users u ON u.id=t.user_id WHERE t.transaction_date BETWEEN ? AND ?";
$args=[$from,$to];
if(!is_admin()){ $sql.=" AND t.outlet_id=?"; $args[]=(int)$user['outlet_id']; }
if(in_array($type,['income','expense'],true)){ $sql.=" AND t.type=?"; $args[]=$type; }
$sql.=" ORDER BY t.transaction_date DESC,t.id DESC LIMIT 500";
$st=$pdo->prepare($sql);$st->execute($args);$rows=$st->fetchAll();
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Riwayat transaksi • WASHFLOW</title><link rel="stylesheet" href="assets/style.css"></head><body>
<div class="app-shell"><aside class="sidebar"><a class="brand" href="dashboard.php"><span class="brand-mark small">W</span><span>WASHFLOW<small>OPERATIONS</small></span></a><nav><a href="dashboard.php">▦ <span>Dashboard</span></a><?php if(is_admin()):?><a href="outlets.php">▤ <span>Daftar outlet</span></a><?php endif;?><a class="active" href="transactions.php">⇄ <span>Riwayat transaksi</span></a><a href="transaction_new.php">＋ <span>Input transaksi</span></a></nav><div class="sidebar-bottom"><div class="user-chip"><div class="avatar"><?=e(strtoupper(substr($user['username'],0,1)))?></div><div><b><?=e($user['username'])?></b><small><?=is_admin()?'Administrator':e($user['outlet_name']??'Outlet')?></small></div></div><a class="logout" href="logout.php">Keluar ↗</a></div></aside>
<main class="main-content"><header class="topbar"><div><p class="eyebrow">FINANCE / DATA</p><h1>Riwayat transaksi</h1></div><a class="btn primary" href="transaction_new.php">＋ Input transaksi</a></header>
<?php if(isset($_GET['saved'])):?><div class="success-banner">✓ Transaksi berhasil disimpan.</div><?php endif;?>
<form class="filter-bar panel" method="get"><label>Dari tanggal<input type="date" name="from" value="<?=e($from)?>"></label><label>Sampai tanggal<input type="date" name="to" value="<?=e($to)?>"></label><label>Jenis<select name="type"><option value="">Semua</option><option value="income" <?=$type==='income'?'selected':''?>>Pemasukan</option><option value="expense" <?=$type==='expense'?'selected':''?>>Pengeluaran</option></select></label><button class="btn dark" type="submit">Terapkan</button></form>
<div class="panel table-panel"><div class="table-scroll"><table><thead><tr><th>Tanggal</th><?php if(is_admin()):?><th>Outlet</th><?php endif;?><th>Jenis</th><th>Catatan</th><th>Nominal</th><?php if(is_admin()):?><th>Diinput oleh</th><?php endif;?></tr></thead><tbody>
<?php foreach($rows as $r):?><tr><td><?=e(date('d M Y',strtotime($r['transaction_date'])))?></td><?php if(is_admin()):?><td><b><?=e($r['outlet_name'])?></b></td><?php endif;?><td><span class="badge <?=$r['type']==='income'?'badge-green':'badge-red'?>"><?=$r['type']==='income'?'Pemasukan':'Pengeluaran'?></span></td><td><?=e($r['note']?:'-')?></td><td><b><?=money($r['amount'])?></b></td><?php if(is_admin()):?><td><?=e($r['created_by'])?></td><?php endif;?></tr><?php endforeach;?>
<?php if(!$rows):?><tr><td colspan="<?=is_admin()?6:4?>" class="empty">Tidak ada transaksi pada rentang tanggal ini.</td></tr><?php endif;?>
</tbody></table></div></div><footer>WASHFLOW <span>•</span> Laundry operations management</footer></main></div></body></html>
