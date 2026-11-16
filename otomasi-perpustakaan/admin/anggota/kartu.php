<?php
// =========================================================
//  CETAK KARTU ANGGOTA (ukuran kartu KTP 85,6 × 54 mm)
//  kartu.php?id=3  -> kartu satu anggota
//  kartu.php       -> kartu semua anggota yang aktif
// =========================================================
require __DIR__ . '/../../includes/init.php';
wajib_login();

if (isset($_GET['id'])) {
    $daftar_anggota = mysqli_execute_query($koneksi, "SELECT * FROM anggota WHERE id = ?", [(int) $_GET['id']]);
} else {
    $daftar_anggota = mysqli_query($koneksi, "SELECT * FROM anggota WHERE status = 'aktif' ORDER BY no_anggota");
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Kartu Anggota</title>
    <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/app.css') ?>">
</head>
<body class="halaman-cetak">

<div class="alat">
    <button class="btn btn-primary" onclick="window.print()">Cetak</button>
    <a class="btn btn-garis" href="<?= url('admin/anggota/') ?>">Kembali ke Keanggotaan</a>
    <span class="form-text">Ukuran kartu 85,6 × 54 mm. Aktifkan "Background graphics" di pengaturan cetak.</span>
</div>

<div class="px-3"><?php tampil_pesan(); ?></div>

<div class="lembar-cetak">
    <?php while ($anggota = mysqli_fetch_assoc($daftar_anggota)): ?>
        <div class="kartu-anggota">
            <div class="pita">
                <span class="judul"><?= e(NAMA_PERPUSTAKAAN) ?></span>
                <span class="ketik">KARTU<br>ANGGOTA</span>
            </div>
            <div class="isi">
                <div>
                    <div class="kecil">Nama</div>
                    <div class="nama"><?= e($anggota['nama']) ?></div>
                    <div class="kecil mt-1">No. anggota · terdaftar <?= tanggal($anggota['tanggal_daftar']) ?></div>
                    <div class="nomor"><?= e($anggota['no_anggota']) ?></div>
                </div>
                <svg class="barcode"
                     jsbarcode-value="<?= e($anggota['no_anggota']) ?>"
                     jsbarcode-format="CODE128"
                     jsbarcode-height="34"
                     jsbarcode-displayvalue="false"
                     jsbarcode-margin="0"></svg>
            </div>
            <?php if ($anggota['status'] === 'aktif'): ?>
                <span class="stempel hijau">Aktif</span>
            <?php else: ?>
                <span class="stempel">Nonaktif</span>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
</div>

<script src="<?= url('assets/vendor/JsBarcode.all.min.js') ?>"></script>
<script>
    // Ubah setiap <svg class="barcode"> menjadi gambar barcode
    JsBarcode(".barcode").init();
</script>
</body>
</html>
