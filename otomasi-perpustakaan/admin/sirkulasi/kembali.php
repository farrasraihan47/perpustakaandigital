<?php
// =========================================================
//  PENGEMBALIAN BUKU
//  Stok tersedia bertambah 1, keterlambatan & denda dihitung.
// =========================================================
require __DIR__ . '/../../includes/init.php';
wajib_login();

$hari_ini = date('Y-m-d');

// ---------- Proses tombol "Terima kembali" ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);

    // Ambil data peminjaman yang masih berstatus dipinjam
    $hasil = mysqli_execute_query(
        $koneksi,
        "SELECT p.*, a.nama, a.no_anggota, b.judul, b.kode_buku
         FROM peminjaman p
         JOIN anggota a ON a.id = p.anggota_id
         JOIN buku b    ON b.id = p.buku_id
         WHERE p.id = ? AND p.status = 'dipinjam'",
        [$id]
    );
    $pinjam = mysqli_fetch_assoc($hasil);

    if (!$pinjam) {
        pesan('gagal', 'Data peminjaman tidak ditemukan atau buku sudah dikembalikan.');
        pindah('admin/sirkulasi/kembali.php');
    }

    // Hitung keterlambatan dan denda
    $hari_terlambat = hitung_terlambat($pinjam['jatuh_tempo'], $hari_ini);
    $denda = $hari_terlambat * DENDA_PER_HARI;

    // Transaksi: catat pengembalian + kembalikan stok
    mysqli_begin_transaction($koneksi);

    mysqli_execute_query(
        $koneksi,
        "UPDATE peminjaman SET status = 'dikembalikan', tanggal_kembali = ?, hari_terlambat = ?, denda = ? WHERE id = ?",
        [$hari_ini, $hari_terlambat, $denda, $id]
    );
    mysqli_execute_query($koneksi, "UPDATE buku SET stok_tersedia = stok_tersedia + 1 WHERE id = ?", [$pinjam['buku_id']]);

    mysqli_commit($koneksi);

    // Simpan bukti pengembalian untuk ditampilkan sekali
    $_SESSION['bukti_kembali'] = [
        'judul'          => $pinjam['judul'],
        'kode_buku'      => $pinjam['kode_buku'],
        'nama'           => $pinjam['nama'],
        'no_anggota'     => $pinjam['no_anggota'],
        'tanggal_pinjam' => $pinjam['tanggal_pinjam'],
        'jatuh_tempo'    => $pinjam['jatuh_tempo'],
        'hari_terlambat' => $hari_terlambat,
        'denda'          => $denda,
    ];
    pindah('admin/sirkulasi/kembali.php');
}

// ---------- Bukti pengembalian terakhir (jika ada) ----------
$bukti = $_SESSION['bukti_kembali'] ?? null;
unset($_SESSION['bukti_kembali']);

// ---------- Daftar buku yang masih dipinjam ----------
$cari = trim($_GET['cari'] ?? '');
$pola = '%' . $cari . '%';

$daftar_pinjam = mysqli_execute_query(
    $koneksi,
    "SELECT p.*, a.nama, a.no_anggota, b.judul, b.kode_buku
     FROM peminjaman p
     JOIN anggota a ON a.id = p.anggota_id
     JOIN buku b    ON b.id = p.buku_id
     WHERE p.status = 'dipinjam'
       AND (a.no_anggota LIKE ? OR a.nama LIKE ? OR b.kode_buku LIKE ? OR b.judul LIKE ?)
     ORDER BY p.jatuh_tempo",
    [$pola, $pola, $pola, $pola]
);
$jumlah = mysqli_num_rows($daftar_pinjam);

$judul_halaman = 'Pengembalian';
$alis = 'Sirkulasi · ' . tanggal($hari_ini);
$menu_aktif = 'kembali';
require __DIR__ . '/../../includes/admin_atas.php';
?>

<?php if ($bukti): ?>
    <!-- Bukti pengembalian -->
    <div class="kertas mb-4 position-relative" style="max-width: 640px">
        <div class="alis">Bukti pengembalian</div>
        <h2 class="mt-1 mb-3"><?= e($bukti['judul']) ?></h2>
        <dl class="daftar-ketik row mb-0">
            <dt class="col-5">Buku</dt><dd class="col-7"><?= e($bukti['kode_buku']) ?></dd>
            <dt class="col-5">Peminjam</dt><dd class="col-7"><?= e($bukti['no_anggota']) ?> · <?= e($bukti['nama']) ?></dd>
            <dt class="col-5">Dipinjam</dt><dd class="col-7"><?= tanggal($bukti['tanggal_pinjam']) ?></dd>
            <dt class="col-5">Jatuh tempo</dt><dd class="col-7"><?= tanggal($bukti['jatuh_tempo']) ?></dd>
            <dt class="col-5">Dikembalikan</dt><dd class="col-7"><?= tanggal($hari_ini) ?></dd>
            <dt class="col-5">Terlambat</dt><dd class="col-7"><?= $bukti['hari_terlambat'] ?> hari</dd>
            <dt class="col-5">Denda</dt><dd class="col-7"><b><?= rupiah($bukti['denda']) ?></b></dd>
        </dl>
        <div class="position-absolute" style="right: 1.6rem; bottom: 1.6rem">
            <?php if ($bukti['hari_terlambat'] > 0): ?>
                <span class="stempel besar jatuh">Terlambat <?= $bukti['hari_terlambat'] ?> hari<br>Denda <?= rupiah($bukti['denda']) ?></span>
            <?php else: ?>
                <span class="stempel hijau besar jatuh">Kembali<br>tepat waktu</span>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<form class="d-flex gap-2 mb-3" method="get" style="max-width: 560px">
    <input class="form-control" type="search" name="cari" value="<?= e($cari) ?>"
           placeholder="Cari no. anggota, nama, kode buku, judul…" autofocus>
    <button class="btn btn-garis" type="submit">Cari</button>
    <?php if ($cari !== ''): ?><a class="btn btn-garis" href="<?= url('admin/sirkulasi/kembali.php') ?>">Reset</a><?php endif; ?>
</form>

<p class="form-text mb-2"><?= $jumlah ?> buku masih dipinjam</p>

<div class="ledger-wrap">
    <table class="ledger">
        <thead>
            <tr>
                <th>Peminjam</th>
                <th>Buku</th>
                <th>Dipinjam</th>
                <th>Jatuh tempo</th>
                <th>Keterangan</th>
                <th class="angka">Denda jika kembali hari ini</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($jumlah === 0): ?>
                <tr><td colspan="7" class="kosong">Tidak ada buku yang sedang dipinjam<?= $cari !== '' ? ' dengan kata kunci itu' : '' ?>.</td></tr>
            <?php endif; ?>

            <?php while ($p = mysqli_fetch_assoc($daftar_pinjam)): ?>
                <?php
                    $terlambat = hitung_terlambat($p['jatuh_tempo'], $hari_ini);
                    $perkiraan_denda = $terlambat * DENDA_PER_HARI;
                ?>
                <tr>
                    <td><span class="kode"><?= e($p['no_anggota']) ?></span><br><?= e($p['nama']) ?></td>
                    <td><span class="kode"><?= e($p['kode_buku']) ?></span><br><?= e($p['judul']) ?></td>
                    <td class="kode"><?= tanggal($p['tanggal_pinjam']) ?></td>
                    <td class="kode"><?= tanggal($p['jatuh_tempo']) ?></td>
                    <td>
                        <?php if ($terlambat > 0): ?>
                            <span class="stempel">Terlambat <?= $terlambat ?> hari</span>
                        <?php else: ?>
                            <span class="stempel hijau">Belum jatuh tempo</span>
                        <?php endif; ?>
                    </td>
                    <td class="angka"><?= rupiah($perkiraan_denda) ?></td>
                    <td class="aksi">
                        <form method="post"
                              data-judul="<?= e($p['judul']) ?>" data-denda="<?= rupiah($perkiraan_denda) ?>"
                              onsubmit="return confirm('Terima kembali &quot;' + this.dataset.judul + '&quot;?\nDenda: ' + this.dataset.denda);">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button class="btn btn-sm btn-laci" type="submit">Terima kembali</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../../includes/admin_bawah.php'; ?>
