# WASHFLOW — MVP

Website manajemen franchise laundry dengan dua role: admin dan outlet.

## Fitur saat ini
- Login admin dan outlet.
- Dashboard admin: omset hari ini, perbandingan kemarin, pengeluaran, jumlah outlet, ringkasan 100 outlet.
- Dashboard outlet: hanya menampilkan data outlet miliknya.
- Input pemasukan/pengeluaran.
- Riwayat transaksi dan filter tanggal/jenis.
- Admin dapat membuat outlet beserta akun loginnya.
- UI responsif untuk HP dan desktop.
- Query menggunakan PDO prepared statements, password di-hash, dan form POST memakai token CSRF.

## Menjalankan di hosting PHP + MySQL
1. Buat database MySQL bernama `washflow` (atau sesuaikan nama pada `config.php`).
2. Import `database.sql` melalui phpMyAdmin.
3. Edit `config.php` dengan host, nama database, username, dan password database hosting.
4. Upload seluruh isi folder ini ke `public_html` hosting.
5. Buka `https://domain-kamu/install.php` satu kali untuk membuat akun demo.
6. Login, lalu segera ubah password demo melalui pengelolaan akun (fitur pengelolaan password belum ada di MVP ini).
7. Hapus `install.php` dari hosting setelah instalasi.
8. Buat outlet tambahan melalui menu Daftar outlet.

## Akun demo
- Admin: `admin` / `AdminDemo!2026`
- Outlet: `medan01` / `OutletDemo!2026`

**Penting:** Ini starter MVP untuk pengembangan dan demonstrasi sekolah, belum layak dipakai mengelola uang bisnis sungguhan sebelum ditambah fitur ubah password, audit log, edit/koreksi transaksi dengan jejak audit, backup otomatis, pembatasan percobaan login, dan pengujian keamanan.

## Catatan GitHub
Jangan masukkan password database hosting ke repository publik. Untuk tugas sekolah, repository bisa dibuat private. File `config.php` berisi kredensial database sehingga jangan pernah commit konfigurasi produksi yang berisi password asli.
