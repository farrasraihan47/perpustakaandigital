<?php
// =========================================================
//  PEMINJAMAN BUKU
//  Stok tersedia berkurang 1 setiap ada peminjaman.
// =========================================================
require __DIR__ . '/../../includes/init.php';
wajib_login();

$hari_ini    = date('Y-m-d');
$jatuh_tempo = date('Y-m-d', strtotime('+' . LAMA_PINJAM . ' days'));

$no_anggota = '';
$kode_buku  = '';
$kesalahan  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // strtoupper: "ag0001" dianggap sama dengan "AG0001"
    $no_anggota = strtoupper(trim($_POST['no_anggota'] ?? ''));
    $kode_buku  = strtoupper(trim($_POST['kode_buku'] ?? ''));

    // --- 1. Cek anggota ---
    $hasil = mysqli_execute_query($koneksi, "SELECT * FROM anggota WHERE no_anggota = ?", [$no_anggota]);
    $anggota = mysqli_fetch_assoc($hasil);

    if (!$anggota) {
        $kesalahan[] = 'No. anggota "' . $no_anggota . '" tidak ditemukan.';
    } elseif ($anggota['status'] !== 'aktif') {
        $kesalahan[] = $anggota['nama'] . ' berstatus NONAKTIF, tidak boleh meminjam.';
    }

    // --- 2. Cek buku ---
    $hasil = mysqli_execute_query($koneksi, "SELECT * FROM buku WHERE kode_buku = ?", [$kode_buku]);
    $buku = mysqli_fetch_assoc($hasil);

    if (!$buku) {
        $kesalahan[] = 'Kode buku "' . $kode_buku . '" tidak ditemukan.';
    } elseif ($buku['stok_tersedia'] < 1) {
        $kesalahan[] = 'Stok "' . $buku['judul'] . '" habis, semua eksemplar sedang dipinjam.';
    }

    // --- 3. Cek batas pinjam & buku yang sama ---
    if ($anggota && $buku) {
        $hasil = mysqli_execute_query($koneksi, "SELECT COUNT(*) AS jumlah FROM peminjaman WHERE anggota_id = ? AND status = 'dipinjam'", [$anggota['id']]);
        $sedang_pinjam = (int) mysqli_fetch_assoc($hasil)['jumlah'];

        if ($sedang_pinjam >= MAKS_PINJAM) {
            $kesalahan[] = $anggota['nama'] . ' sudah meminjam ' . $sedang_pinjam . ' buku (batas ' . MAKS_PINJAM . '). Kembalikan dulu salah satunya.';
        }

        $hasil = mysqli_execute_query($koneksi, "SELECT id FROM peminjaman WHERE anggota_id = ? AND buku_id = ? AND status = 'dipinjam'", [$anggota['id'], $buku['id']]);
        if (mysqli_num_rows($hasil) > 0) {
            $kesalahan[] = $anggota['nama'] . ' masih meminjam "' . $buku['judul'] . '" dan belum mengembalikannya.';
        }
    }

    // --- 4. Simpan jika lolos semua pemeriksaan ---
    if (!$kesalahan) {
        // Transaksi: kedua perintah di bawah berhasil bersama, atau gagal bersama
        mysqli_begin_transaction($koneksi);

        mysqli_execute_query(
            $koneksi,
            "INSERT INTO peminjaman (anggota_id, buku_id, petugas_id, tanggal_pinjam, jatuh_tempo, status)
             VALUES (?, ?, ?, ?, ?, 'dipinjam')",
            [$anggota['id'], $buku['id'], $_SESSION['user_id'], $hari_ini, $jatuh_tempo]
        );
        mysqli_execute_query($koneksi, "UPDATE buku SET stok_tersedia = stok_tersedia - 1 WHERE id = ?", [$buku['id']]);

        mysqli_commit($koneksi);

        pesan('sukses', '"' . $buku['judul'] . '" dipinjam oleh ' . $anggota['nama'] . '. Harus kembali paling lambat ' . tanggal($jatuh_tempo) . '.');
        pindah('admin/sirkulasi/pinjam.php');
    }
}

// Saran isian: anggota aktif & buku yang masih tersedia
$saran_anggota = mysqli_query($koneksi, "SELECT no_anggota, nama FROM anggota WHERE status = 'aktif' ORDER BY no_anggota");
$saran_buku    = mysqli_query($koneksi, "SELECT kode_buku, judul FROM buku WHERE stok_tersedia > 0 ORDER BY kode_buku");

// Daftar peminjaman hari ini
$pinjaman_hari_ini = mysqli_execute_query(
    $koneksi,
    "SELECT p.jatuh_tempo, a.no_anggota, a.nama, b.kode_buku, b.judul
     FROM peminjaman p
     JOIN anggota a ON a.id = p.anggota_id
     JOIN buku b    ON b.id = p.buku_id
     WHERE p.tanggal_pinjam = ?
     ORDER BY p.id DESC",
    [$hari_ini]
);

$judul_halaman = 'Peminjaman';
$alis = 'Sirkulasi · ' . tanggal($hari_ini);
$menu_aktif = 'pinjam';
require __DIR__ . '/../../includes/admin_atas.php';
?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="kertas">
            <h2 class="mb-3">Catat peminjaman</h2>

            <?php if ($kesalahan): ?>
                <div class="pesan gagal">
                    <span class="stempel">Ditolak</span>
                    <ul class="mb-0 ps-3"><?php foreach ($kesalahan as $k): ?><li><?= e($k) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label" for="no_anggota">No. anggota</label>
                    <input class="form-control form-control-lg ketik" id="no_anggota" name="no_anggota"
                           value="<?= e($no_anggota) ?>" list="daftar-anggota" placeholder="AG0001" required autofocus autocomplete="off">
                    <datalist id="daftar-anggota">
                        <?php while ($a = mysqli_fetch_assoc($saran_anggota)): ?>
                            <option value="<?= e($a['no_anggota']) ?>"><?= e($a['nama']) ?></option>
                        <?php endwhile; ?>
                    </datalist>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="kode_buku">Kode buku</label>
                    <input class="form-control form-control-lg ketik" id="kode_buku" name="kode_buku"
                           value="<?= e($kode_buku) ?>" list="daftar-buku" placeholder="BK0001" required autocomplete="off">
                    <datalist id="daftar-buku">
                        <?php while ($b = mysqli_fetch_assoc($saran_buku)): ?>
                            <option value="<?= e($b['kode_buku']) ?>"><?= e($b['judul']) ?></option>
                        <?php endwhile; ?>
                    </datalist>
                    <div class="form-text">Bisa diketik, dipilih dari saran, atau di-scan dengan barcode scanner.</div>
                </div>

                <dl class="daftar-ketik row mb-4">
                    <dt class="col-6">Tanggal pinjam</dt><dd class="col-6 text-end"><?= tanggal($hari_ini) ?></dd>
                    <dt class="col-6">Jatuh tempo</dt><dd class="col-6 text-end"><b><?= tanggal($jatuh_tempo) ?></b></dd>
                    <dt class="col-6">Denda terlambat</dt><dd class="col-6 text-end"><?= rupiah(DENDA_PER_HARI) ?>/hari</dd>
                </dl>

                <button class="btn btn-primary btn-lg w-100" type="submit">Pinjamkan</button>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <h2 class="fs-4 mb-3">Dipinjam hari ini</h2>
        <div class="ledger-wrap">
            <table class="ledger">
                <thead>
                    <tr><th>Anggota</th><th>Buku</th><th>Jatuh tempo</th></tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($pinjaman_hari_ini) === 0): ?>
                        <tr><td colspan="3" class="kosong">Belum ada peminjaman hari ini.</td></tr>
                    <?php endif; ?>
                    <?php while ($p = mysqli_fetch_assoc($pinjaman_hari_ini)): ?>
                        <tr>
                            <td><span class="kode"><?= e($p['no_anggota']) ?></span><br><?= e($p['nama']) ?></td>
                            <td><span class="kode"><?= e($p['kode_buku']) ?></span><br><?= e($p['judul']) ?></td>
                            <td class="kode"><?= tanggal($p['jatuh_tempo']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/admin_bawah.php'; ?>
