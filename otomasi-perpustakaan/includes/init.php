<?php
// File ini di-require di baris paling atas SETIAP halaman.
// Isinya: memuat pengaturan, fungsi bantu, koneksi database, dan session login.

require __DIR__ . '/../config/config.php';
require __DIR__ . '/fungsi.php';

date_default_timezone_set('Asia/Jakarta');

// Jika query error, PHP langsung berhenti dan menampilkan pesan (memudahkan mencari kesalahan)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
mysqli_set_charset($koneksi, 'utf8mb4');

// Session menyimpan status login petugas
session_start();
