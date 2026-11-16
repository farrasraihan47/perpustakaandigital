<?php
// =========================================================
//  Potongan kode: membaca & memeriksa isian form buku.
//  Dipakai bersama oleh tambah.php dan edit.php.
//  Hasil:  $buku       = data isian
//          $kesalahan  = daftar pesan kesalahan (kosong jika semua benar)
// =========================================================

$buku = [
    'judul'        => trim($_POST['judul'] ?? ''),
    'pengarang'    => trim($_POST['pengarang'] ?? ''),
    'penerbit'     => trim($_POST['penerbit'] ?? ''),
    'tahun_terbit' => trim($_POST['tahun_terbit'] ?? ''),
    'isbn'         => trim($_POST['isbn'] ?? ''),
    'kategori'     => trim($_POST['kategori'] ?? ''),
    'lokasi_rak'   => trim($_POST['lokasi_rak'] ?? ''),
    'stok_total'   => trim($_POST['stok_total'] ?? ''),
    'link_ebook'   => trim($_POST['link_ebook'] ?? ''),
];
$kesalahan = [];

// Kolom wajib
if ($buku['judul'] === '')     { $kesalahan[] = 'Judul wajib diisi.'; }
if ($buku['pengarang'] === '') { $kesalahan[] = 'Pengarang wajib diisi.'; }
if ($buku['kategori'] === '')  { $kesalahan[] = 'Kategori wajib diisi.'; }

// Jumlah eksemplar harus angka bulat 1 sampai 999
if (!ctype_digit($buku['stok_total']) || $buku['stok_total'] < 1 || $buku['stok_total'] > 999) {
    $kesalahan[] = 'Jumlah eksemplar harus angka 1 sampai 999.';
}

// ISBN boleh kosong; jika diisi hanya angka, huruf X, dan tanda hubung
if ($buku['isbn'] !== '' && !preg_match('/^[0-9Xx-]{10,17}$/', $buku['isbn'])) {
    $kesalahan[] = 'ISBN tidak valid (10–13 digit, boleh memakai tanda hubung).';
}

// Link e-book harus diawali http:// atau https://
if (!link_valid($buku['link_ebook'])) {
    $kesalahan[] = 'Link e-book harus alamat web lengkap, diawali http:// atau https://';
}

// Panjang maksimal sesuai ukuran kolom di database
$batas_panjang = [
    'judul' => 255, 'pengarang' => 150, 'penerbit' => 150, 'tahun_terbit' => 20,
    'kategori' => 50, 'lokasi_rak' => 20, 'link_ebook' => 255,
];
foreach ($batas_panjang as $kolom => $maksimal) {
    if (mb_strlen($buku[$kolom]) > $maksimal) {
        $kesalahan[] = 'Isian ' . str_replace('_', ' ', $kolom) . ' terlalu panjang (maksimal ' . $maksimal . ' karakter).';
    }
}
