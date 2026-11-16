<?php
// =========================================================
//  Potongan kode: membaca & memeriksa isian form anggota.
//  Dipakai bersama oleh tambah.php dan edit.php.
//  Hasil:  $anggota    = data isian
//          $kesalahan  = daftar pesan kesalahan (kosong jika semua benar)
// =========================================================

$anggota = [
    'nama'   => trim($_POST['nama'] ?? ''),
    'alamat' => trim($_POST['alamat'] ?? ''),
    'no_hp'  => trim($_POST['no_hp'] ?? ''),
    'email'  => trim($_POST['email'] ?? ''),
    'status' => $_POST['status'] ?? 'aktif',
];
$kesalahan = [];

if ($anggota['nama'] === '') {
    $kesalahan[] = 'Nama wajib diisi.';
}
if (mb_strlen($anggota['nama']) > 100) {
    $kesalahan[] = 'Nama terlalu panjang (maksimal 100 karakter).';
}

// No. HP boleh kosong; jika diisi: angka, spasi, +, atau tanda hubung, 8–20 karakter
if ($anggota['no_hp'] !== '' && !preg_match('/^[0-9+ -]{8,20}$/', $anggota['no_hp'])) {
    $kesalahan[] = 'No. HP tidak valid (8–20 angka).';
}

// Email boleh kosong; jika diisi harus format email yang benar
if ($anggota['email'] !== '' && !filter_var($anggota['email'], FILTER_VALIDATE_EMAIL)) {
    $kesalahan[] = 'Format email tidak valid.';
}
if (mb_strlen($anggota['email']) > 100) {
    $kesalahan[] = 'Email terlalu panjang (maksimal 100 karakter).';
}

// Status hanya boleh 'aktif' atau 'nonaktif'
if (!in_array($anggota['status'], ['aktif', 'nonaktif'])) {
    $kesalahan[] = 'Status tidak valid.';
}
