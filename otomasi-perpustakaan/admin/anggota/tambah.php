<?php
// =========================================================
//  TAMBAH ANGGOTA
// =========================================================
require __DIR__ . '/../../includes/init.php';
wajib_login();

// No. anggota otomatis: AG + (id terbesar + 1), contoh AG0006
$hasil = mysqli_query($koneksi, "SELECT MAX(id) AS terakhir FROM anggota");
$nomor_berikut = (int) mysqli_fetch_assoc($hasil)['terakhir'] + 1;
$no_anggota = 'AG' . str_pad($nomor_berikut, 4, '0', STR_PAD_LEFT);

$tanggal_daftar = date('Y-m-d');   // hari ini

$anggota = ['nama' => '', 'alamat' => '', 'no_hp' => '', 'email' => '', 'status' => 'aktif'];
$kesalahan = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/_periksa.php';   // mengisi $anggota dan $kesalahan

    if (!$kesalahan) {
        mysqli_execute_query(
            $koneksi,
            "INSERT INTO anggota (no_anggota, nama, alamat, no_hp, email, tanggal_daftar, status)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $no_anggota, $anggota['nama'], $anggota['alamat'] ?: null, $anggota['no_hp'] ?: null,
                $anggota['email'] ?: null, $tanggal_daftar, $anggota['status'],
            ]
        );
        $id_baru = mysqli_insert_id($koneksi);

        pesan('sukses', 'Anggota "' . $anggota['nama'] . '" terdaftar dengan nomor ' . $no_anggota . '. Kartu anggota siap dicetak.');
        pindah('admin/anggota/kartu.php?id=' . $id_baru);   // langsung ke halaman cetak kartu
    }
}

$judul_halaman = 'Tambah Anggota';
$alis = 'Keanggotaan';
$menu_aktif = 'anggota';
$teks_tombol = 'Simpan & Buat Kartu';
require __DIR__ . '/../../includes/admin_atas.php';
require __DIR__ . '/_form.php';
require __DIR__ . '/../../includes/admin_bawah.php';
