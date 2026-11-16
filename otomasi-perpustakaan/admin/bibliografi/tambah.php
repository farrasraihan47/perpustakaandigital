<?php
// =========================================================
//  TAMBAH BUKU
// =========================================================
require __DIR__ . '/../../includes/init.php';
wajib_login();

// Kode buku otomatis: BK + (id terbesar + 1), contoh BK0012
$hasil = mysqli_query($koneksi, "SELECT MAX(id) AS terakhir FROM buku");
$nomor_berikut = (int) mysqli_fetch_assoc($hasil)['terakhir'] + 1;
$kode_buku = 'BK' . str_pad($nomor_berikut, 4, '0', STR_PAD_LEFT);

// Nilai awal form kosong
$buku = [
    'judul' => '', 'pengarang' => '', 'penerbit' => '', 'tahun_terbit' => '', 'isbn' => '',
    'kategori' => '', 'lokasi_rak' => '', 'stok_total' => '1', 'link_ebook' => '',
];
$kesalahan = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/_periksa.php';   // mengisi $buku dan $kesalahan

    if (!$kesalahan) {
        // Buku baru: semua eksemplar tersedia, jadi stok_tersedia = stok_total
        mysqli_execute_query(
            $koneksi,
            "INSERT INTO buku (kode_buku, isbn, judul, pengarang, penerbit, tahun_terbit, kategori, lokasi_rak, stok_total, stok_tersedia, link_ebook)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $kode_buku, $buku['isbn'] ?: null, $buku['judul'], $buku['pengarang'], $buku['penerbit'] ?: null,
                $buku['tahun_terbit'] ?: null, $buku['kategori'], $buku['lokasi_rak'] ?: null,
                $buku['stok_total'], $buku['stok_total'], $buku['link_ebook'] ?: null,
            ]
        );
        pesan('sukses', 'Buku "' . $buku['judul'] . '" disimpan dengan kode ' . $kode_buku . '.');
        pindah('admin/bibliografi/');
    }
}

$judul_halaman = 'Tambah Buku';
$alis = 'Bibliografi';
$menu_aktif = 'bibliografi';
$teks_tombol = 'Simpan Buku';
require __DIR__ . '/../../includes/admin_atas.php';
require __DIR__ . '/_form.php';
require __DIR__ . '/../../includes/admin_bawah.php';
