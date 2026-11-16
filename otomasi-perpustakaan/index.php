<?php
// =========================================================
//  OPAC (Online Public Access Catalog) — halaman publik
//  Pengunjung mencari buku tanpa perlu login.
// =========================================================
require __DIR__ . '/includes/init.php';

// ---------- Kriteria pencarian ----------
// kunci di alamat (?kriteria=...)  =>  [label tab, kolom database]
$daftar_kriteria = [
    'semua'     => ['Semua', null],
    'judul'     => ['Judul', 'judul'],
    'pengarang' => ['Pengarang', 'pengarang'],
    'penerbit'  => ['Penerbit', 'penerbit'],
    'isbn'      => ['ISBN', 'isbn'],
    'kategori'  => ['Kategori', 'kategori'],
    'tahun'     => ['Tahun', 'tahun_terbit'],
];

$q = trim($_GET['q'] ?? '');
$kriteria = $_GET['kriteria'] ?? 'semua';
if (!isset($daftar_kriteria[$kriteria])) {
    $kriteria = 'semua';   // nilai aneh dari alamat diabaikan
}

$pola = '%' . $q . '%';
$pola_isbn = '%' . str_replace('-', '', $q) . '%';   // ISBN dicari tanpa tanda hubung

// ---------- Susun syarat WHERE ----------
// Nama kolom diambil dari daftar di atas (bukan dari ketikan pengunjung), jadi aman.
if ($kriteria === 'semua') {
    $syarat = "(judul LIKE ? OR pengarang LIKE ? OR penerbit LIKE ? OR kategori LIKE ? OR tahun_terbit LIKE ?
                OR REPLACE(isbn, '-', '') LIKE ?)";
    $parameter = [$pola, $pola, $pola, $pola, $pola, $pola_isbn];
} elseif ($kriteria === 'isbn') {
    $syarat = "REPLACE(isbn, '-', '') LIKE ?";
    $parameter = [$pola_isbn];
} else {
    $kolom = $daftar_kriteria[$kriteria][1];
    $syarat = "$kolom LIKE ?";
    $parameter = [$pola];
}

// ---------- Paginasi: 12 kartu per halaman ----------
$per_halaman = 12;
$hasil = mysqli_execute_query($koneksi, "SELECT COUNT(*) AS jumlah FROM buku WHERE $syarat", $parameter);
$jumlah_hasil = (int) mysqli_fetch_assoc($hasil)['jumlah'];
$jumlah_halaman = max(1, (int) ceil($jumlah_hasil / $per_halaman));

$halaman = (int) ($_GET['halaman'] ?? 1);
$halaman = min(max(1, $halaman), $jumlah_halaman);
$mulai = ($halaman - 1) * $per_halaman;

$daftar_buku = mysqli_execute_query(
    $koneksi,
    "SELECT * FROM buku WHERE $syarat ORDER BY judul LIMIT $per_halaman OFFSET $mulai",
    $parameter
);

// ---------- Data untuk "rak kategori" & ringkasan koleksi ----------
$daftar_kategori = mysqli_query($koneksi, "SELECT kategori, COUNT(*) AS jumlah FROM buku GROUP BY kategori ORDER BY kategori");
$ringkasan = mysqli_fetch_assoc(mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS judul, COALESCE(SUM(stok_total), 0) AS eksemplar, COUNT(DISTINCT kategori) AS kategori FROM buku"
));

// Membuat alamat dengan parameter pencarian yang sama, contoh: ?q=...&kriteria=...&halaman=2
function alamat_opac($ubah)
{
    global $q, $kriteria;
    $data = array_merge(['q' => $q, 'kriteria' => $kriteria], $ubah);
    return '?' . http_build_query($data);
}

$judul_halaman = 'Katalog';
require __DIR__ . '/includes/publik_atas.php';
?>

<section class="pembuka-opac row align-items-end g-3 mb-4">
    <div class="col-lg-8">
        <div class="alis">Katalog daring · OPAC</div>
        <h1>Cari di laci katalog kami.</h1>
    </div>
    <div class="col-lg-4 text-lg-end">
        <p class="ketik mb-1" style="color: var(--pensil)">
            <?= $ringkasan['judul'] ?> judul · <?= $ringkasan['eksemplar'] ?> eksemplar · <?= $ringkasan['kategori'] ?> kategori
        </p>
    </div>
</section>

<!-- Tab kriteria (pembatas laci) -->
<nav class="pembatas" aria-label="Kriteria pencarian">
    <?php foreach ($daftar_kriteria as $kunci => $info): ?>
        <a class="<?= $kriteria === $kunci ? 'aktif' : '' ?>" href="<?= e(alamat_opac(['kriteria' => $kunci, 'halaman' => 1])) ?>"><?= $info[0] ?></a>
    <?php endforeach; ?>
</nav>

<!-- Kotak pencarian -->
<form class="laci-cari mb-3" method="get" role="search">
    <input type="hidden" name="kriteria" value="<?= e($kriteria) ?>">
    <div class="d-flex gap-3 align-items-end">
        <input class="form-control" type="search" name="q" value="<?= e($q) ?>"
               placeholder="Cari berdasarkan <?= strtolower($daftar_kriteria[$kriteria][0]) ?><?= $kriteria === 'semua' ? ' kolom' : '' ?>…"
               aria-label="Kata kunci">
        <button class="btn btn-primary" type="submit">Cari</button>
    </div>
</form>

<!-- Rak kategori -->
<div class="rak-kategori mb-4">
    <?php while ($k = mysqli_fetch_assoc($daftar_kategori)): ?>
        <?php $aktif = $kriteria === 'kategori' && $q === $k['kategori']; ?>
        <a class="<?= $aktif ? 'aktif' : '' ?>" href="<?= e('?' . http_build_query(['kriteria' => 'kategori', 'q' => $k['kategori']])) ?>">
            <?= e($k['kategori']) ?> <span class="ketik">(<?= $k['jumlah'] ?>)</span>
        </a>
    <?php endwhile; ?>
</div>

<!-- Keterangan hasil -->
<p class="ketik mb-3" style="color: var(--pensil)">
    <?php if ($q === ''): ?>
        Menampilkan semua <?= $jumlah_hasil ?> kartu, urut judul.
    <?php else: ?>
        <?= $jumlah_hasil ?> kartu cocok dengan "<b style="color: var(--tinta)"><?= e($q) ?></b>"
        di <?= strtolower($daftar_kriteria[$kriteria][0]) ?>.
        <a href="<?= url() ?>">Hapus pencarian</a>
    <?php endif; ?>
</p>

<?php if ($jumlah_hasil === 0): ?>
    <div class="kertas text-center py-5">
        <span class="stempel pensil besar mb-3">Kosong</span>
        <p class="mb-1 mt-3">Tidak ada kartu yang cocok di laci ini.</p>
        <p class="form-text">Coba kata kunci lain, atau pilih tab <b>Semua</b>.</p>
    </div>
<?php endif; ?>

<!-- Hasil: kartu katalog -->
<div class="grid-kartu">
    <?php $urutan = 0; ?>
    <?php while ($buku = mysqli_fetch_assoc($daftar_buku)): ?>
        <?php $nomor = nomor_panggil($buku); ?>
        <a class="kartu-katalog" href="<?= url('detail.php?id=' . $buku['id']) ?>" style="--i: <?= $urutan++ ?>">
            <div class="kepala-kartu">
                <div class="nomor-panggil"><b><?= e($nomor[0]) ?></b><br><?= e($nomor[1]) ?><br><?= e($nomor[2]) ?></div>
                <?php if ($buku['stok_tersedia'] > 0): ?>
                    <span class="stempel hijau">Tersedia <?= $buku['stok_tersedia'] ?>/<?= $buku['stok_total'] ?></span>
                <?php else: ?>
                    <span class="stempel">Dipinjam semua</span>
                <?php endif; ?>
            </div>
            <h3 class="judul-buku"><?= e($buku['judul']) ?></h3>
            <p class="baris"><?= e($buku['pengarang']) ?></p>
            <p class="baris"><?= e($buku['penerbit'] ?: '—') ?><?= $buku['tahun_terbit'] ? ', ' . e($buku['tahun_terbit']) : '' ?></p>
            <p class="baris"><?= e($buku['kategori']) ?><?= $buku['link_ebook'] ? ' · e-book' : '' ?></p>
        </a>
    <?php endwhile; ?>
</div>

<!-- Paginasi -->
<?php if ($jumlah_halaman > 1): ?>
    <nav class="mt-5" aria-label="Halaman hasil">
        <ul class="pagination justify-content-center">
            <?php for ($h = 1; $h <= $jumlah_halaman; $h++): ?>
                <li class="page-item <?= $h === $halaman ? 'active' : '' ?>">
                    <a class="page-link" href="<?= e(alamat_opac(['halaman' => $h])) ?>"><?= $h ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>

<?php require __DIR__ . '/includes/publik_bawah.php'; ?>
