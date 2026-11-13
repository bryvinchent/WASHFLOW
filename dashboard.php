<?php
require_once __DIR__ . '/auth.php';
require_login();
$pdo = db();
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$user = current_user();

function totals(PDO $pdo, string $date, ?int $outletId = null): array {
    $sql = "SELECT
        COALESCE(SUM(CASE WHEN type='income' THEN amount ELSE 0 END),0) income,
        COALESCE(SUM(CASE WHEN type='expense' THEN amount ELSE 0 END),0) expense
        FROM transactions WHERE transaction_date=?";
    $args = [$date];
    if ($outletId !== null) { $sql .= " AND outlet_id=?"; $args[] = $outletId; }
    $st = $pdo->prepare($sql); $st->execute($args);
    return $st->fetch() ?: ['income'=>0,'expense'=>0];
}
$outletId = is_admin() ? null : (int)$user['outlet_id'];
$t = totals($pdo, $today, $outletId);
$y = totals($pdo, $yesterday, $outletId);
$change = ((float)$y['income'] > 0) ? (((float)$t['income'] - (float)$y['income']) / (float)$y['income']) * 100 : null;

if (is_admin()) {
    $outletCount = (int)$pdo->query("SELECT COUNT(*) FROM outlets WHERE is_active=1")->fetchColumn();
    $stmt = $pdo->prepare("SELECT o.id,o.name,o.city,
        COALESCE(SUM(CASE WHEN tr.transaction_date=? AND tr.type='income' THEN tr.amount ELSE 0 END),0) income_today,
        COALESCE(SUM(CASE WHEN tr.transaction_date=? AND tr.type='expense' THEN tr.amount ELSE 0 END),0) expense_today
        FROM outlets o LEFT JOIN transactions tr ON tr.outlet_id=o.id
        WHERE o.is_active=1 GROUP BY o.id,o.name,o.city ORDER BY income_today DESC,o.name LIMIT 100");
    $stmt->execute([$today,$today]); $outlets = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT type,amount,note,transaction_date,created_at FROM transactions WHERE outlet_id=? ORDER BY transaction_date DESC,id DESC LIMIT 8");
    $stmt->execute([$outletId]); $recent = $stmt->fetchAll();
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard • WASHFLOW</title><link rel="stylesheet" href="assets/style.css"></head><body>
<div class="app-shell">
<aside class="sidebar"><a class="brand" href="dashboard.php"><span class="brand-mark small">W</span><span>WASHFLOW<small>OPERATIONS</small></span></a>
<nav><a class="active" href="dashboard.php">▦ <span>Dashboard</span></a>
<?php if (is_admin()): ?><a href="outlets.php">▤ <span>Daftar outlet</span></a><?php endif; ?>
<a href="transactions.php">⇄ <span><?= is_admin() ? 'Semua transaksi' : 'Riwayat transaksi' ?></span></a>
<a href="transaction_new.php">＋ <span>Input transaksi</span></a></nav>
<div class="sidebar-bottom"><div class="user-chip"><div class="avatar"><?= e(strtoupper(substr($user['username'],0,1))) ?></div><div><b><?= e($user['username']) ?></b><small><?= is_admin()?'Administrator':e($user['outlet_name'] ?? 'Outlet') ?></small></div></div><a class="logout" href="logout.php">Keluar ↗</a></div></aside>
<main class="main-content"><header class="topbar"><div><p class="eyebrow">OVERVIEW / <?= is_admin()?'ADMIN':'OUTLET' ?></p><h1>Dashboard</h1></div><div class="top-meta"><span class="status-dot"></span> Sistem aktif <span class="date-pill"><?= e(date('d M Y')) ?></span></div></header>
<section class="welcome-row"><div><h2><?= is_admin()?'Pantau bisnis, dalam satu pandangan.':'Ringkasan operasional outlet.' ?></h2><p class="muted"><?= is_admin()?'Performa seluruh cabang laundry hari ini.':'Catat transaksi dan pantau kondisi outlet kamu.' ?></p></div>
<a class="btn primary" href="transaction_new.php">＋ Input transaksi</a></section>
<section class="stats-grid">
<article class="stat-card featured"><div class="stat-head"><span>Omset hari ini</span><span class="stat-icon">↗</span></div><strong><?= money($t['income']) ?></strong><div class="stat-foot"><?= $change===null?'Belum ada data pembanding':(($change>=0?'+':'').number_format($change,1,',','.').'% dibanding kemarin') ?></div></article>
<article class="stat-card"><div class="stat-head"><span>Omset sebelumnya</span><span class="stat-icon">◷</span></div><strong><?= money($y['income']) ?></strong><div class="stat-foot">Periode: kemarin</div></article>
<article class="stat-card"><div class="stat-head"><span>Pengeluaran hari ini</span><span class="stat-icon">↘</span></div><strong><?= money($t['expense']) ?></strong><div class="stat-foot">Total pengeluaran tercatat</div></article>
<article class="stat-card"><div class="stat-head"><span><?= is_admin()?'Outlet aktif':'Saldo operasional' ?></span><span class="stat-icon">◎</span></div><strong><?= is_admin()?$outletCount:money((float)$t['income']-(float)$t['expense']) ?></strong><div class="stat-foot"><?= is_admin()?'Outlet terdaftar dan aktif':'Omset dikurangi pengeluaran hari ini' ?></div></article>
</section>
<?php if (is_admin()): ?>
<section class="section-heading"><div><h2>Performa outlet</h2><p class="muted">Ringkasan pemasukan dan pengeluaran hari ini.</p></div><a class="text-link" href="outlets.php">Lihat semua outlet →</a></section>
<div class="panel table-panel"><div class="table-scroll"><table><thead><tr><th>Outlet</th><th>Lokasi</th><th>Pemasukan</th><th>Pengeluaran</th><th>Selisih</th></tr></thead><tbody>
<?php foreach($outlets as $o): ?><tr><td><b><?= e($o['name']) ?></b></td><td><?= e($o['city']) ?></td><td class="positive"><?= money($o['income_today']) ?></td><td><?= money($o['expense_today']) ?></td><td><b><?= money((float)$o['income_today']-(float)$o['expense_today']) ?></b></td></tr><?php endforeach; ?>
<?php if (!$outlets): ?><tr><td colspan="5" class="empty">Belum ada outlet. Tambahkan outlet terlebih dahulu.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php else: ?>
<section class="section-heading"><div><h2>Transaksi terbaru</h2><p class="muted">Aktivitas keuangan outlet kamu.</p></div><a class="text-link" href="transactions.php">Lihat riwayat →</a></section>
<div class="panel table-panel"><div class="table-scroll"><table><thead><tr><th>Tanggal</th><th>Jenis</th><th>Catatan</th><th>Nominal</th></tr></thead><tbody>
<?php foreach($recent as $r): ?><tr><td><?= e(date('d M Y',strtotime($r['transaction_date']))) ?></td><td><span class="badge <?= $r['type']==='income'?'badge-green':'badge-red' ?>"><?= $r['type']==='income'?'Pemasukan':'Pengeluaran' ?></span></td><td><?= e($r['note'] ?: '-') ?></td><td><b><?= money($r['amount']) ?></b></td></tr><?php endforeach; ?>
<?php if (!$recent): ?><tr><td colspan="4" class="empty">Belum ada transaksi. Mulai dengan input transaksi pertama.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php endif; ?>
<footer>WASHFLOW <span>•</span> Laundry operations management</footer></main></div></body></html>
