<?php
// =========================================================
//  CETAK LABEL PUNGGUNG BUKU + BARCODE
//  label.php?id=5  -> label untuk satu buku (sebanyak jumlah eksemplarnya)
//  label.php       -> label untuk semua buku
// =========================================================
require __DIR__ . '/../../includes/init.php';
wajib_login();

if (isset($_GET['id'])) {
    $daftar_buku = mysqli_execute_query($koneksi, "SELECT * FROM buku WHERE id = ?", [(int) $_GET['id']]);
} else {
    $daftar_buku = mysqli_query($koneksi, "SELECT * FROM buku ORDER BY kode_buku");
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Label Buku</title>
    <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/app.css') ?>">
</head>
<body class="halaman-cetak">

<div class="alat">
    <button class="btn btn-primary" onclick="window.print()">Cetak</button>
    <a class="btn btn-garis" href="<?= url('admin/bibliografi/') ?>">Kembali ke Bibliografi</a>
    <span class="form-text">Satu label untuk setiap eksemplar. Ukuran label 64 × 34 mm.</span>
</div>

<div class="lembar-cetak">
    <?php while ($buku = mysqli_fetch_assoc($daftar_buku)): ?>
        <?php $nomor = nomor_panggil($buku); ?>
        <?php for ($eksemplar = 1; $eksemplar <= $buku['stok_total']; $eksemplar++): ?>
            <div class="label-punggung">
                <div class="nomor-panggil">
                    <b><?= e($nomor[0]) ?></b>
                    <span><?= e($nomor[1]) ?></span>
                    <span><?= e($nomor[2]) ?></span>
                </div>
                <div class="info">
                    <div class="kecil"><?= e(NAMA_PERPUSTAKAAN) ?> · eks. <?= $eksemplar ?>/<?= $buku['stok_total'] ?></div>
                    <div class="judul"><?= e($buku['judul']) ?></div>
                    <svg class="barcode"
                         jsbarcode-value="<?= e($buku['kode_buku']) ?>"
                         jsbarcode-format="CODE128"
                         jsbarcode-height="38"
                         jsbarcode-fontsize="13"
                         jsbarcode-margin="0"></svg>
                </div>
            </div>
        <?php endfor; ?>
    <?php endwhile; ?>
</div>

<script src="<?= url('assets/vendor/JsBarcode.all.min.js') ?>"></script>
<script>
    // Ubah setiap <svg class="barcode"> menjadi gambar barcode
    JsBarcode(".barcode").init();
</script>
</body>
</html>
