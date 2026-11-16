<?php
// =========================================================
//  OPAC — DETAIL BUKU (publik)
// =========================================================
require __DIR__ . '/includes/init.php';

$id = (int) ($_GET['id'] ?? 0);
$hasil = mysqli_execute_query($koneksi, "SELECT * FROM buku WHERE id = ?", [$id]);
$buku = mysqli_fetch_assoc($hasil);

if (!$buku) {
    pesan('gagal', 'Buku tidak ditemukan di katalog.');
    pindah('');
}

$hari_ini = date('Y-m-d');

// Jika semua eksemplar sedang dipinjam: kapan paling cepat ada yang kembali?
$perkiraan_tersedia = null;
if ($buku['stok_tersedia'] == 0) {
    $hasil = mysqli_execute_query(
        $koneksi,
        "SELECT MIN(jatuh_tempo) AS tercepat FROM peminjaman WHERE buku_id = ? AND status = 'dipinjam'",
        [$id]
    );
    $perkiraan_tersedia = mysqli_fetch_assoc($hasil)['tercepat'];
}

// Buku lain di kategori yang sama (maksimal 4)
$buku_serupa = mysqli_execute_query(
    $koneksi,
    "SELECT * FROM buku WHERE kategori = ? AND id <> ? ORDER BY judul LIMIT 4",
    [$buku['kategori'], $id]
);

$nomor = nomor_panggil($buku);
$judul_halaman = $buku['judul'];
require __DIR__ . '/includes/publik_atas.php';
?>

<p class="mb-4"><a class="ketik" href="<?= url() ?>">&larr; Kembali ke katalog</a></p>

<div class="row g-4 g-lg-5 align-items-start">
    <!-- Kartu katalog besar -->
    <div class="col-lg-7">
        <div class="kartu-katalog besar">
            <div class="kepala-kartu">
                <div class="nomor-panggil"><b><?= e($nomor[0]) ?></b><br><?= e($nomor[1]) ?><br><?= e($nomor[2]) ?></div>
                <span class="ketik" style="color: var(--pensil); font-size: .8rem"><?= e($buku['kode_buku']) ?></span>
            </div>
            <h1 class="judul-buku"><?= e($buku['judul']) ?></h1>
            <p class="baris"><?= e($buku['pengarang']) ?></p>
            <p class="baris">Penerbit&nbsp;&nbsp;: <?= e($buku['penerbit'] ?: '—') ?></p>
            <p class="baris">Tahun&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?= e($buku['tahun_terbit'] ?: '—') ?></p>
            <p class="baris">ISBN&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?= e($buku['isbn'] ?: '—') ?></p>
            <p class="baris">Kategori&nbsp;&nbsp;: <?= e($buku['kategori']) ?></p>
            <p class="baris">Lokasi&nbsp;&nbsp;&nbsp;&nbsp;: Rak <?= e($buku['lokasi_rak'] ?: '—') ?></p>
        </div>
    </div>

    <!-- Ketersediaan -->
    <div class="col-lg-5">
        <div class="alis mb-2">Ketersediaan</div>
        <?php if ($buku['stok_tersedia'] > 0): ?>
            <span class="stempel hijau besar jatuh">Tersedia di rak</span>
            <p class="mt-4 mb-1 fs-5"><b><?= $buku['stok_tersedia'] ?></b> dari <?= $buku['stok_total'] ?> eksemplar ada di rak <span class="ketik"><?= e($buku['lokasi_rak'] ?: '—') ?></span>.</p>
            <p class="form-text">Bawa kartu anggota ke meja sirkulasi untuk meminjam selama <?= LAMA_PINJAM ?> hari.</p>
        <?php else: ?>
            <span class="stempel besar jatuh">Sedang dipinjam</span>
            <p class="mt-4 mb-1 fs-5">Semua <?= $buku['stok_total'] ?> eksemplar sedang dipinjam.</p>
            <?php if ($perkiraan_tersedia && $perkiraan_tersedia >= $hari_ini): ?>
                <p class="form-text">Perkiraan tersedia kembali: <b class="ketik"><?= tanggal($perkiraan_tersedia) ?></b></p>
            <?php elseif ($perkiraan_tersedia): ?>
                <p class="form-text">Peminjam sudah melewati jatuh tempo (<?= tanggal($perkiraan_tersedia) ?>). Buku diharapkan segera kembali.</p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($buku['link_ebook']): ?>
            <hr class="my-4">
            <div class="alis mb-2">Versi digital</div>
            <a class="btn btn-laci" href="<?= e($buku['link_ebook']) ?>" target="_blank" rel="noopener noreferrer">Baca e-book &nearr;</a>
            <p class="form-text mt-2">Dibuka di tab baru, tanpa perlu meminjam.</p>
        <?php endif; ?>
    </div>
</div>

<?php if (mysqli_num_rows($buku_serupa) > 0): ?>
    <h2 class="mt-5 mb-4 fs-3">Di laci yang sama: <?= e($buku['kategori']) ?></h2>
    <div class="grid-kartu">
        <?php $urutan = 0; ?>
        <?php while ($b = mysqli_fetch_assoc($buku_serupa)): ?>
            <?php $n = nomor_panggil($b); ?>
            <a class="kartu-katalog" href="<?= url('detail.php?id=' . $b['id']) ?>" style="--i: <?= $urutan++ ?>">
                <div class="kepala-kartu">
                    <div class="nomor-panggil"><b><?= e($n[0]) ?></b><br><?= e($n[1]) ?><br><?= e($n[2]) ?></div>
                    <?php if ($b['stok_tersedia'] > 0): ?>
                        <span class="stempel hijau">Tersedia <?= $b['stok_tersedia'] ?>/<?= $b['stok_total'] ?></span>
                    <?php else: ?>
                        <span class="stempel">Dipinjam semua</span>
                    <?php endif; ?>
                </div>
                <h3 class="judul-buku"><?= e($b['judul']) ?></h3>
                <p class="baris"><?= e($b['pengarang']) ?></p>
                <p class="baris"><?= e($b['tahun_terbit'] ?: '') ?></p>
            </a>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/publik_bawah.php'; ?>
