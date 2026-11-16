<?php
// =========================================================
//  UBAH DATA BUKU
// =========================================================
require __DIR__ . '/../../includes/init.php';
wajib_login();

// Ambil buku yang mau diubah berdasarkan ?id= di alamat
$id = (int) ($_GET['id'] ?? 0);
$hasil = mysqli_execute_query($koneksi, "SELECT * FROM buku WHERE id = ?", [$id]);
$buku_lama = mysqli_fetch_assoc($hasil);

if (!$buku_lama) {
    pesan('gagal', 'Buku tidak ditemukan.');
    pindah('admin/bibliografi/');
}

// Berapa eksemplar buku ini yang sedang dipinjam?
$hasil = mysqli_execute_query($koneksi, "SELECT COUNT(*) AS jumlah FROM peminjaman WHERE buku_id = ? AND status = 'dipinjam'", [$id]);
$sedang_dipinjam = (int) mysqli_fetch_assoc($hasil)['jumlah'];

$buku = $buku_lama;   // isi form dengan data lama
$kode_buku = $buku_lama['kode_buku'];
$kesalahan = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/_periksa.php';   // mengisi $buku dan $kesalahan

    // Jumlah eksemplar tidak boleh lebih sedikit dari yang sedang dipinjam
    if ($buku['stok_total'] < $sedang_dipinjam) {
        $kesalahan[] = 'Jumlah eksemplar tidak boleh kurang dari ' . $sedang_dipinjam . ' (sedang dipinjam).';
    }

    if (!$kesalahan) {
        // Stok tersedia dihitung ulang: total - yang sedang dipinjam
        $stok_tersedia = $buku['stok_total'] - $sedang_dipinjam;

        mysqli_execute_query(
            $koneksi,
            "UPDATE buku SET isbn = ?, judul = ?, pengarang = ?, penerbit = ?, tahun_terbit = ?, kategori = ?,
                             lokasi_rak = ?, stok_total = ?, stok_tersedia = ?, link_ebook = ?
             WHERE id = ?",
            [
                $buku['isbn'] ?: null, $buku['judul'], $buku['pengarang'], $buku['penerbit'] ?: null,
                $buku['tahun_terbit'] ?: null, $buku['kategori'], $buku['lokasi_rak'] ?: null,
                $buku['stok_total'], $stok_tersedia, $buku['link_ebook'] ?: null, $id,
            ]
        );
        pesan('sukses', 'Perubahan buku "' . $buku['judul'] . '" disimpan.');
        pindah('admin/bibliografi/');
    }
}

$judul_halaman = 'Ubah Buku';
$alis = 'Bibliografi · ' . $kode_buku;
$menu_aktif = 'bibliografi';
$teks_tombol = 'Simpan Perubahan';
require __DIR__ . '/../../includes/admin_atas.php';
?>

<?php if ($sedang_dipinjam > 0): ?>
    <p class="form-text mb-3">
        <span class="stempel me-2">Dipinjam <?= $sedang_dipinjam ?></span>
        <?= $sedang_dipinjam ?> eksemplar sedang di tangan peminjam.
    </p>
<?php endif; ?>

<?php
require __DIR__ . '/_form.php';
require __DIR__ . '/../../includes/admin_bawah.php';
