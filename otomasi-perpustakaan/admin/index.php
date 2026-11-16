<?php
// =========================================================
//  DASHBOARD PETUGAS
//  (statistik lengkap ditambahkan di Fase 8)
// =========================================================
require __DIR__ . '/../includes/init.php';
wajib_login();

// Cek apakah petugas ini masih memakai password bawaan "admin123"
$hasil = mysqli_execute_query($koneksi, "SELECT password_hash FROM users WHERE id = ?", [$_SESSION['user_id']]);
$user = mysqli_fetch_assoc($hasil);
$masih_password_bawaan = password_verify('admin123', $user['password_hash']);

$judul_halaman = 'Selamat bertugas, ' . $_SESSION['nama'];
$alis = 'Dashboard · ' . tanggal(date('Y-m-d'));
$menu_aktif = 'dashboard';
require __DIR__ . '/../includes/admin_atas.php';
?>

<?php if ($masih_password_bawaan): ?>
    <div class="peringatan-kuningan">
        <strong>Password Anda masih bawaan (admin123).</strong>
        Segera <a href="<?= url('admin/pengguna/ganti-password.php') ?>">ganti password</a> sebelum aplikasi dipakai.
    </div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-sm-6 col-xl-3">
        <a class="depan-laci d-block text-decoration-none text-reset" href="<?= url('admin/sirkulasi/pinjam.php') ?>">
            <span class="label-kuningan">Sirkulasi</span>
            <div class="judul fs-3 mt-3">Pinjamkan buku</div>
            <div class="keterangan">Catat peminjaman baru</div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a class="depan-laci d-block text-decoration-none text-reset" href="<?= url('admin/sirkulasi/kembali.php') ?>">
            <span class="label-kuningan">Sirkulasi</span>
            <div class="judul fs-3 mt-3">Terima kembali</div>
            <div class="keterangan">Hitung keterlambatan &amp; denda</div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a class="depan-laci d-block text-decoration-none text-reset" href="<?= url('admin/bibliografi/tambah.php') ?>">
            <span class="label-kuningan">Koleksi</span>
            <div class="judul fs-3 mt-3">Tambah buku</div>
            <div class="keterangan">Input bibliografi baru</div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a class="depan-laci d-block text-decoration-none text-reset" href="<?= url('admin/anggota/tambah.php') ?>">
            <span class="label-kuningan">Koleksi</span>
            <div class="judul fs-3 mt-3">Daftarkan anggota</div>
            <div class="keterangan">Buat kartu anggota baru</div>
        </a>
    </div>
</div>

<?php require __DIR__ . '/../includes/admin_bawah.php'; ?>
