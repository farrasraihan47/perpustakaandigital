<?php
// =========================================================
//  DASHBOARD PETUGAS — ringkasan kondisi perpustakaan hari ini
// =========================================================
require __DIR__ . '/../includes/init.php';
wajib_login();

$hari_ini      = date('Y-m-d');
$awal_bulan    = date('Y-m-01');
$tiga_hari_lagi = date('Y-m-d', strtotime('+3 days'));

// Cek apakah petugas ini masih memakai password bawaan "admin123"
$hasil = mysqli_execute_query($koneksi, "SELECT password_hash FROM users WHERE id = ?", [$_SESSION['user_id']]);
$user = mysqli_fetch_assoc($hasil);
$masih_password_bawaan = password_verify('admin123', $user['password_hash']);

// ---------- Angka statistik (satu query, beberapa sub-query) ----------
$statistik = mysqli_fetch_assoc(mysqli_execute_query(
    $koneksi,
    "SELECT
        (SELECT COUNT(*) FROM buku)                                      AS judul,
        (SELECT COALESCE(SUM(stok_total), 0) FROM buku)                  AS eksemplar,
        (SELECT COUNT(*) FROM anggota WHERE status = 'aktif')            AS anggota_aktif,
        (SELECT COUNT(*) FROM anggota)                                   AS anggota_total,
        (SELECT COUNT(*) FROM peminjaman WHERE status = 'dipinjam')      AS dipinjam,
        (SELECT COUNT(*) FROM peminjaman WHERE status = 'dipinjam' AND jatuh_tempo < ?) AS terlambat,
        (SELECT COALESCE(SUM(denda), 0) FROM peminjaman
          WHERE status = 'dikembalikan' AND tanggal_kembali >= ?)        AS denda_bulan_ini",
    [$hari_ini, $awal_bulan]
));

// ---------- Peminjaman yang sudah lewat jatuh tempo (perlu ditagih) ----------
$daftar_terlambat = mysqli_execute_query(
    $koneksi,
    "SELECT p.jatuh_tempo, a.no_anggota, a.nama, a.no_hp, b.judul
     FROM peminjaman p
     JOIN anggota a ON a.id = p.anggota_id
     JOIN buku b    ON b.id = p.buku_id
     WHERE p.status = 'dipinjam' AND p.jatuh_tempo < ?
     ORDER BY p.jatuh_tempo",
    [$hari_ini]
);

// ---------- Jatuh tempo dalam 3 hari ke depan ----------
$daftar_segera = mysqli_execute_query(
    $koneksi,
    "SELECT p.jatuh_tempo, a.no_anggota, a.nama, b.judul
     FROM peminjaman p
     JOIN anggota a ON a.id = p.anggota_id
     JOIN buku b    ON b.id = p.buku_id
     WHERE p.status = 'dipinjam' AND p.jatuh_tempo BETWEEN ? AND ?
     ORDER BY p.jatuh_tempo",
    [$hari_ini, $tiga_hari_lagi]
);

$judul_halaman = 'Selamat bertugas, ' . $_SESSION['nama'];
$alis = 'Dashboard · ' . tanggal($hari_ini);
$menu_aktif = 'dashboard';
$tombol_kepala = [
    'Pinjamkan Buku' => 'admin/sirkulasi/pinjam.php',
    'Terima Kembali' => 'admin/sirkulasi/kembali.php',
];
require __DIR__ . '/../includes/admin_atas.php';
?>

<?php if ($masih_password_bawaan): ?>
    <div class="peringatan-kuningan">
        <strong>Password Anda masih bawaan (admin123).</strong>
        Segera <a href="<?= url('admin/pengguna/ganti-password.php') ?>">ganti password</a> sebelum aplikasi dipakai.
    </div>
<?php endif; ?>

<!-- Depan laci: angka-angka utama -->
<div class="row row-cols-2 row-cols-lg-3 row-cols-xl-5 g-3 mb-5">
    <div class="col">
        <a class="depan-laci d-block text-decoration-none text-reset" href="<?= url('admin/bibliografi/') ?>">
            <span class="label-kuningan">Koleksi</span>
            <div class="angka-besar"><?= $statistik['judul'] ?></div>
            <div class="keterangan">judul · <?= $statistik['eksemplar'] ?> eksemplar</div>
        </a>
    </div>
    <div class="col">
        <a class="depan-laci d-block text-decoration-none text-reset" href="<?= url('admin/anggota/?status=aktif') ?>">
            <span class="label-kuningan">Anggota</span>
            <div class="angka-besar"><?= $statistik['anggota_aktif'] ?></div>
            <div class="keterangan">aktif dari <?= $statistik['anggota_total'] ?> terdaftar</div>
        </a>
    </div>
    <div class="col">
        <a class="depan-laci d-block text-decoration-none text-reset" href="<?= url('admin/sirkulasi/riwayat.php?tab=dipinjam') ?>">
            <span class="label-kuningan">Dipinjam</span>
            <div class="angka-besar"><?= $statistik['dipinjam'] ?></div>
            <div class="keterangan">eksemplar di luar</div>
        </a>
    </div>
    <div class="col">
        <a class="depan-laci d-block text-decoration-none text-reset <?= $statistik['terlambat'] > 0 ? 'merah' : '' ?>" href="<?= url('admin/sirkulasi/riwayat.php?tab=terlambat') ?>">
            <span class="label-kuningan">Terlambat</span>
            <div class="angka-besar"><?= $statistik['terlambat'] ?></div>
            <div class="keterangan">perlu ditagih</div>
        </a>
    </div>
    <div class="col">
        <a class="depan-laci d-block text-decoration-none text-reset" href="<?= url('admin/sirkulasi/riwayat.php?tab=dikembalikan') ?>">
            <span class="label-kuningan">Denda</span>
            <div class="angka-besar" style="font-size: 2.1rem; padding: .45rem 0"><?= rupiah($statistik['denda_bulan_ini']) ?></div>
            <div class="keterangan">diterima bulan ini</div>
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Perlu ditagih -->
    <div class="col-xl-7">
        <h2 class="fs-4 mb-3">Perlu ditagih</h2>
        <div class="ledger-wrap">
            <table class="ledger">
                <thead>
                    <tr><th>Peminjam</th><th>Buku</th><th>Keterlambatan</th><th class="angka">Denda s.d. hari ini</th></tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($daftar_terlambat) === 0): ?>
                        <tr><td colspan="4" class="kosong">Tidak ada yang terlambat. Rak aman.</td></tr>
                    <?php endif; ?>
                    <?php while ($p = mysqli_fetch_assoc($daftar_terlambat)): ?>
                        <?php $telat = hitung_terlambat($p['jatuh_tempo'], $hari_ini); ?>
                        <tr>
                            <td>
                                <span class="kode"><?= e($p['no_anggota']) ?></span><br><?= e($p['nama']) ?>
                                <?php if ($p['no_hp']): ?><div class="form-text ketik"><?= e($p['no_hp']) ?></div><?php endif; ?>
                            </td>
                            <td><?= e($p['judul']) ?></td>
                            <td><span class="stempel">Terlambat <?= $telat ?> hari</span></td>
                            <td class="angka"><?= rupiah($telat * DENDA_PER_HARI) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Jatuh tempo segera -->
    <div class="col-xl-5">
        <h2 class="fs-4 mb-3">Jatuh tempo 3 hari ke depan</h2>
        <div class="ledger-wrap">
            <table class="ledger">
                <thead>
                    <tr><th>Peminjam</th><th>Buku</th><th>Jatuh tempo</th></tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($daftar_segera) === 0): ?>
                        <tr><td colspan="3" class="kosong">Tidak ada.</td></tr>
                    <?php endif; ?>
                    <?php while ($p = mysqli_fetch_assoc($daftar_segera)): ?>
                        <tr>
                            <td><span class="kode"><?= e($p['no_anggota']) ?></span><br><?= e($p['nama']) ?></td>
                            <td><?= e($p['judul']) ?></td>
                            <td class="kode"><?= $p['jatuh_tempo'] === $hari_ini ? 'Hari ini' : tanggal($p['jatuh_tempo']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/admin_bawah.php'; ?>
