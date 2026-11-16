<?php
// =========================================================
//  UBAH DATA ANGGOTA
// =========================================================
require __DIR__ . '/../../includes/init.php';
wajib_login();

$id = (int) ($_GET['id'] ?? 0);
$hasil = mysqli_execute_query($koneksi, "SELECT * FROM anggota WHERE id = ?", [$id]);
$anggota_lama = mysqli_fetch_assoc($hasil);

if (!$anggota_lama) {
    pesan('gagal', 'Anggota tidak ditemukan.');
    pindah('admin/anggota/');
}

$anggota = $anggota_lama;   // isi form dengan data lama
$no_anggota = $anggota_lama['no_anggota'];
$tanggal_daftar = $anggota_lama['tanggal_daftar'];
$kesalahan = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/_periksa.php';   // mengisi $anggota dan $kesalahan

    if (!$kesalahan) {
        mysqli_execute_query(
            $koneksi,
            "UPDATE anggota SET nama = ?, alamat = ?, no_hp = ?, email = ?, status = ? WHERE id = ?",
            [
                $anggota['nama'], $anggota['alamat'] ?: null, $anggota['no_hp'] ?: null,
                $anggota['email'] ?: null, $anggota['status'], $id,
            ]
        );
        pesan('sukses', 'Data anggota "' . $anggota['nama'] . '" disimpan.');
        pindah('admin/anggota/');
    }
}

$judul_halaman = 'Ubah Anggota';
$alis = 'Keanggotaan · ' . $no_anggota;
$menu_aktif = 'anggota';
$teks_tombol = 'Simpan Perubahan';
require __DIR__ . '/../../includes/admin_atas.php';
require __DIR__ . '/_form.php';
require __DIR__ . '/../../includes/admin_bawah.php';
