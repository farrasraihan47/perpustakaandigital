<?php
// =========================================================
//  LOGOUT — menghapus semua data login di session
// =========================================================
require __DIR__ . '/includes/init.php';

$_SESSION = [];       // kosongkan isi session
session_destroy();    // hapus session dari server

session_start();      // session baru (kosong) hanya untuk membawa pesan
pesan('sukses', 'Anda sudah keluar. Sampai jumpa!');
pindah('login.php');
