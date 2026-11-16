<?php
// =========================================================
//  HAPUS ANGGOTA
//  Hanya menerima POST (dari tombol Hapus), bukan dari link biasa.
// =========================================================
require __DIR__ . '/../../includes/init.php';
wajib_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    pindah('admin/anggota/');
}

$id = (int) ($_POST['id'] ?? 0);

$hasil = mysqli_execute_query($koneksi, "SELECT nama FROM anggota WHERE id = ?", [$id]);
$anggota = mysqli_fetch_assoc($hasil);

if (!$anggota) {
    pesan('gagal', 'Anggota tidak ditemukan.');
    pindah('admin/anggota/');
}

// Tolak jika anggota masih membawa buku
$hasil = mysqli_execute_query($koneksi, "SELECT COUNT(*) AS jumlah FROM peminjaman WHERE anggota_id = ? AND status = 'dipinjam'", [$id]);
$sedang_pinjam = (int) mysqli_fetch_assoc($hasil)['jumlah'];

if ($sedang_pinjam > 0) {
    pesan('gagal', $anggota['nama'] . ' masih meminjam ' . $sedang_pinjam . ' buku. Selesaikan pengembalian dulu, atau ubah statusnya menjadi nonaktif.');
    pindah('admin/anggota/');
}

// Hapus anggota (riwayat peminjaman lamanya ikut terhapus otomatis)
mysqli_execute_query($koneksi, "DELETE FROM anggota WHERE id = ?", [$id]);

pesan('sukses', 'Anggota "' . $anggota['nama'] . '" sudah dihapus.');
pindah('admin/anggota/');
