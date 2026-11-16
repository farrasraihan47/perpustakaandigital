<?php
// =========================================================
//  RIWAYAT SIRKULASI — semua transaksi peminjaman
// =========================================================
require __DIR__ . '/../../includes/init.php';
wajib_login();

$hari_ini = date('Y-m-d');

// Tab filter: semua | dipinjam | terlambat | dikembalikan
$tab = $_GET['tab'] ?? 'semua';
$daftar_tab = [
    'semua'        => 'Semua',
    'dipinjam'     => 'Sedang dipinjam',
    'terlambat'    => 'Terlambat',
    'dikembalikan' => 'Dikembalikan',
];
if (!isset($daftar_tab[$tab])) {
    $tab = 'semua';
}

$cari = trim($_GET['cari'] ?? '');
$pola = '%' . $cari . '%';

// Syarat tambahan sesuai tab (teks SQL tetap, bukan dari input pengguna)
$syarat_tab = [
    'semua'        => "1 = 1",
    'dipinjam'     => "p.status = 'dipinjam'",
    'terlambat'    => "p.status = 'dipinjam' AND p.jatuh_tempo < ?",
    'dikembalikan' => "p.status = 'dikembalikan'",
];
$parameter = [$pola, $pola, $pola, $pola];
if ($tab === 'terlambat') {
    $parameter[] = $hari_ini;
}

$riwayat = mysqli_execute_query(
    $koneksi,
    "SELECT p.*, a.nama, a.no_anggota, b.judul, b.kode_buku, u.nama AS petugas
     FROM peminjaman p
     JOIN anggota a     ON a.id = p.anggota_id
     JOIN buku b        ON b.id = p.buku_id
     LEFT JOIN users u  ON u.id = p.petugas_id
     WHERE (a.no_anggota LIKE ? OR a.nama LIKE ? OR b.kode_buku LIKE ? OR b.judul LIKE ?)
       AND " . $syarat_tab[$tab] . "
     ORDER BY p.tanggal_pinjam DESC, p.id DESC",
    $parameter
);
$jumlah = mysqli_num_rows($riwayat);

$judul_halaman = 'Riwayat Sirkulasi';
$alis = 'Sirkulasi';
$menu_aktif = 'riwayat';
require __DIR__ . '/../../includes/admin_atas.php';
?>

<form class="d-flex gap-2 mb-4" method="get" style="max-width: 560px">
    <input type="hidden" name="tab" value="<?= e($tab) ?>">
    <input class="form-control" type="search" name="cari" value="<?= e($cari) ?>" placeholder="Cari no. anggota, nama, kode buku, judul…">
    <button class="btn btn-garis" type="submit">Cari</button>
</form>

<nav class="pembatas">
    <?php foreach ($daftar_tab as $kunci => $label): ?>
        <a class="<?= $tab === $kunci ? 'aktif' : '' ?>" href="?tab=<?= $kunci ?>&amp;cari=<?= urlencode($cari) ?>"><?= $label ?></a>
    <?php endforeach; ?>
</nav>

<div class="ledger-wrap">
    <table class="ledger">
        <thead>
            <tr>
                <th>Peminjam</th>
                <th>Buku</th>
                <th>Dipinjam</th>
                <th>Jatuh tempo</th>
                <th>Dikembalikan</th>
                <th>Keterangan</th>
                <th class="angka">Denda</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($jumlah === 0): ?>
                <tr><td colspan="7" class="kosong">Tidak ada transaksi.</td></tr>
            <?php endif; ?>

            <?php $total_denda = 0; ?>
            <?php while ($p = mysqli_fetch_assoc($riwayat)): ?>
                <?php
                    if ($p['status'] === 'dipinjam') {
                        // belum kembali: hitung keterlambatan sampai hari ini
                        $terlambat = hitung_terlambat($p['jatuh_tempo'], $hari_ini);
                    } else {
                        $terlambat = $p['hari_terlambat'];
                        $total_denda += $p['denda'];
                    }
                ?>
                <tr>
                    <td><span class="kode"><?= e($p['no_anggota']) ?></span><br><?= e($p['nama']) ?></td>
                    <td><span class="kode"><?= e($p['kode_buku']) ?></span><br><?= e($p['judul']) ?></td>
                    <td class="kode"><?= tanggal($p['tanggal_pinjam']) ?></td>
                    <td class="kode"><?= tanggal($p['jatuh_tempo']) ?></td>
                    <td class="kode"><?= tanggal($p['tanggal_kembali']) ?></td>
                    <td>
                        <?php if ($p['status'] === 'dipinjam' && $terlambat > 0): ?>
                            <span class="stempel">Terlambat <?= $terlambat ?> hari</span>
                        <?php elseif ($p['status'] === 'dipinjam'): ?>
                            <span class="stempel pensil">Dipinjam</span>
                        <?php elseif ($terlambat > 0): ?>
                            <span class="stempel hijau">Kembali</span> <span class="form-text">telat <?= $terlambat ?> hari</span>
                        <?php else: ?>
                            <span class="stempel hijau">Kembali</span>
                        <?php endif; ?>
                    </td>
                    <td class="angka"><?= $p['status'] === 'dikembalikan' ? rupiah($p['denda']) : '—' ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
        <?php if ($jumlah > 0): ?>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-end form-label pt-3"><?= $jumlah ?> transaksi · total denda diterima</td>
                    <td class="angka pt-3"><b><?= rupiah($total_denda) ?></b></td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>
</div>

<?php require __DIR__ . '/../../includes/admin_bawah.php'; ?>
