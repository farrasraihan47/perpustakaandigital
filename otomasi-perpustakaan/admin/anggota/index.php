<?php
// =========================================================
//  KEANGGOTAAN — daftar anggota perpustakaan
// =========================================================
require __DIR__ . '/../../includes/init.php';
wajib_login();

$cari   = trim($_GET['cari'] ?? '');
$status = $_GET['status'] ?? '';                 // '', 'aktif', atau 'nonaktif'
if (!in_array($status, ['', 'aktif', 'nonaktif'])) {
    $status = '';
}
$pola = '%' . $cari . '%';

// Ambil anggota + hitung berapa buku yang sedang dipinjam masing-masing
$daftar_anggota = mysqli_execute_query(
    $koneksi,
    "SELECT a.*,
            (SELECT COUNT(*) FROM peminjaman p WHERE p.anggota_id = a.id AND p.status = 'dipinjam') AS sedang_pinjam
     FROM anggota a
     WHERE (a.no_anggota LIKE ? OR a.nama LIKE ? OR a.no_hp LIKE ? OR a.email LIKE ?)
       AND (? = '' OR a.status = ?)
     ORDER BY a.no_anggota",
    [$pola, $pola, $pola, $pola, $status, $status]
);
$jumlah = mysqli_num_rows($daftar_anggota);

$judul_halaman = 'Keanggotaan';
$alis = 'Koleksi';
$menu_aktif = 'anggota';
$tombol_kepala = [
    '+ Tambah Anggota'         => 'admin/anggota/tambah.php',
    'Cetak Kartu Anggota Aktif' => 'admin/anggota/kartu.php',
];
require __DIR__ . '/../../includes/admin_atas.php';
?>

<form class="d-flex gap-2 mb-3 flex-wrap" method="get" style="max-width: 720px">
    <input class="form-control" style="flex: 1 1 260px" type="search" name="cari" value="<?= e($cari) ?>"
           placeholder="Cari no. anggota, nama, no. HP, email…">
    <select class="form-select" style="width: auto" name="status">
        <option value="">Semua status</option>
        <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
        <option value="nonaktif" <?= $status === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
    </select>
    <button class="btn btn-garis" type="submit">Cari</button>
    <?php if ($cari !== '' || $status !== ''): ?>
        <a class="btn btn-garis" href="<?= url('admin/anggota/') ?>">Reset</a>
    <?php endif; ?>
</form>

<p class="form-text mb-2"><?= $jumlah ?> anggota</p>

<div class="ledger-wrap">
    <table class="ledger">
        <thead>
            <tr>
                <th>No. Anggota</th>
                <th>Nama</th>
                <th>No. HP</th>
                <th>Terdaftar</th>
                <th class="angka">Sedang pinjam</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($jumlah === 0): ?>
                <tr><td colspan="7" class="kosong">Tidak ada anggota yang cocok.</td></tr>
            <?php endif; ?>

            <?php while ($anggota = mysqli_fetch_assoc($daftar_anggota)): ?>
                <tr>
                    <td class="kode"><?= e($anggota['no_anggota']) ?></td>
                    <td>
                        <?= e($anggota['nama']) ?>
                        <?php if ($anggota['email']): ?><div class="form-text"><?= e($anggota['email']) ?></div><?php endif; ?>
                    </td>
                    <td class="kode"><?= e($anggota['no_hp']) ?></td>
                    <td><?= tanggal($anggota['tanggal_daftar']) ?></td>
                    <td class="angka"><?= $anggota['sedang_pinjam'] ?> / <?= MAKS_PINJAM ?></td>
                    <td>
                        <?php if ($anggota['status'] === 'aktif'): ?>
                            <span class="stempel hijau">Aktif</span>
                        <?php else: ?>
                            <span class="stempel pensil">Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td class="aksi">
                        <a class="btn btn-sm btn-garis" href="<?= url('admin/anggota/kartu.php?id=' . $anggota['id']) ?>">Kartu</a>
                        <a class="btn btn-sm btn-garis" href="<?= url('admin/anggota/edit.php?id=' . $anggota['id']) ?>">Ubah</a>
                        <form method="post" action="<?= url('admin/anggota/hapus.php') ?>"
                              data-nama="<?= e($anggota['nama']) ?>"
                              onsubmit="return confirm('Hapus anggota &quot;' + this.dataset.nama + '&quot;? Riwayat peminjamannya ikut terhapus.');">
                            <input type="hidden" name="id" value="<?= $anggota['id'] ?>">
                            <button class="btn btn-sm btn-hapus" type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../../includes/admin_bawah.php'; ?>
