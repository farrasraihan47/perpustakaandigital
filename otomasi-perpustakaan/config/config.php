<?php
// =========================================================
//  PENGATURAN APLIKASI
//  Saat di-upload ke hosting, ganti 4 baris DB_ di bawah
//  dengan data database dari panel hosting.
//  JANGAN upload/commit password hosting ke GitHub.
// =========================================================

// Koneksi database (nilai di bawah untuk Docker di laptop)
define('DB_HOST', 'db');
define('DB_USER', 'perpus_app');
define('DB_PASS', 'perpus_app_lokal');
define('DB_NAME', 'perpustakaan');

// Alamat folder aplikasi. '/' jika aplikasi ada di folder utama website.
// Jika di subfolder, misalnya https://contoh.com/perpus/  maka isi '/perpus/'
define('BASE_URL', '/');

define('NAMA_PERPUSTAKAAN', 'Perpustakaan Laci Katalog');

// Aturan sirkulasi
define('LAMA_PINJAM', 7);        // lama peminjaman dalam hari
define('DENDA_PER_HARI', 1000);  // denda keterlambatan per hari (rupiah)
define('MAKS_PINJAM', 3);        // maksimal buku yang dipinjam bersamaan per anggota
