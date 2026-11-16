<?php
// =========================================================
//  BIBLIOGRAFI — daftar koleksi buku
// =========================================================
require __DIR__ . '/../../includes/init.php';
wajib_login();

// Kata kunci pencarian (boleh kosong)
$cari = trim($_GET['cari'] ?? '');
$pola = '%' . $cari . '%';   // % = boleh ada teks apa saja sebelum/sesudahnya

$daftar_buku = mysqli_execute_query(
    $koneksi,
    "SELECT * FROM buku
     WHERE kode_buku LIKE ? OR judul LIKE ? OR pengarang LIKE ? OR isbn LIKE ? OR kategori LIKE ?
     ORDER BY kode_buku",
    [$pola, $pola, $pola, $pola, $pola]
);
$jumlah = mysqli_num_rows($daftar_buku);

$judul_halaman = 'Bibliografi';
$alis = 'Koleksi';
$menu_aktif = 'bibliografi';
$tombol_kepala = [
    '+ Tambah Buku'      => 'admin/bibliografi/tambah.php',
    'Cetak Semua Label'  => 'admin/bibliografi/label.php',
];
require __DIR__ . '/../../includes/admin_atas.php';
?>

<form class="d-flex gap-2 mb-3" method="get" style="max-width: 560px">
    <input class="form-control" type="search" name="cari" value="<?= e($cari) ?>"
           placeholder="Cari kode, judul, pengarang, ISBN, kategori…">
    <button class="btn btn-garis" type="submit">Cari</button>
    <?php if ($cari !== ''): ?>
        <a class="btn btn-garis" href="<?= url('admin/bibliografi/') ?>">Reset</a>
    <?php endif; ?>
</form>

<p class="form-text mb-2"><?= $jumlah ?> judul<?= $cari !== '' ? ' cocok dengan "' . e($cari) . '"' : '' ?></p>

<div class="ledger-wrap">
    <table class="ledger">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Judul</th>
                <th>Pengarang</th>
                <th>Kategori</th>
                <th>Rak</th>
                <th class="angka">Tersedia</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($jumlah === 0): ?>
                <tr><td colspan="7" class="kosong">Tidak ada buku yang cocok.</td></tr>
            <?php endif; ?>

            <?php while ($buku = mysqli_fetch_assoc($daftar_buku)): ?>
                <tr>
                    <td class="kode"><?= e($buku['kode_buku']) ?></td>
                    <td>
                        <?= e($buku['judul']) ?>
                        <?php if ($buku['isbn']): ?><div class="form-text ketik">ISBN <?= e($buku['isbn']) ?></div><?php endif; ?>
                    </td>
                    <td><?= e($buku['pengarang']) ?></td>
                    <td><?= e($buku['kategori']) ?></td>
                    <td class="kode"><?= e($buku['lokasi_rak']) ?></td>
                    <td class="angka">
                        <?php if ($buku['stok_tersedia'] == 0): ?>
                            <span class="stempel">Habis</span>
                        <?php endif; ?>
                        <?= $buku['stok_tersedia'] ?> / <?= $buku['stok_total'] ?>
                    </td>
                    <td class="aksi">
                        <a class="btn btn-sm btn-garis" href="<?= url('admin/bibliografi/label.php?id=' . $buku['id']) ?>">Label</a>
                        <a class="btn btn-sm btn-garis" href="<?= url('admin/bibliografi/edit.php?id=' . $buku['id']) ?>">Ubah</a>
                        <form method="post" action="<?= url('admin/bibliografi/hapus.php') ?>"
                              data-judul="<?= e($buku['judul']) ?>"
                              onsubmit="return confirm('Hapus buku &quot;' + this.dataset.judul + '&quot;?');">
                            <input type="hidden" name="id" value="<?= $buku['id'] ?>">
                            <button class="btn btn-sm btn-hapus" type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../../includes/admin_bawah.php'; ?>
