<?php
// File ini di-require di baris paling atas SETIAP halaman.
// Isinya: memuat pengaturan, fungsi bantu, koneksi database, dan session login.

require __DIR__ . '/../config/config.php';
require __DIR__ . '/fungsi.php';

date_default_timezone_set('Asia/Jakarta');

// Pengunjung nakal bisa mengirim input berbentuk daftar, misalnya ?q[]=x,
// yang membuat fungsi seperti trim() error. Semua input yang bukan teks
// diubah menjadi teks kosong, jadi setiap halaman cukup mengurus teks biasa.
$_GET  = array_map(fn($nilai) => is_string($nilai) ? $nilai : '', $_GET);
$_POST = array_map(fn($nilai) => is_string($nilai) ? $nilai : '', $_POST);

// Jika query error, PHP langsung berhenti dan menampilkan pesan (memudahkan mencari kesalahan)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
mysqli_set_charset($koneksi, 'utf8mb4');

// Session menyimpan status login petugas
session_start();
