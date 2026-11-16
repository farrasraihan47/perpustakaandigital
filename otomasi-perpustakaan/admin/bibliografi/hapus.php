<?php
// =========================================================
//  HAPUS BUKU
//  Hanya menerima POST (dari tombol Hapus), bukan dari link biasa.
// =========================================================
require __DIR__ . '/../../includes/init.php';
wajib_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    pindah('admin/bibliografi/');
}

$id = (int) ($_POST['id'] ?? 0);

$hasil = mysqli_execute_query($koneksi, "SELECT judul FROM buku WHERE id = ?", [$id]);
$buku = mysqli_fetch_assoc($hasil);

if (!$buku) {
    pesan('gagal', 'Buku tidak ditemukan.');
    pindah('admin/bibliografi/');
}

// Tolak jika masih ada eksemplar yang sedang dipinjam
$hasil = mysqli_execute_query($koneksi, "SELECT COUNT(*) AS jumlah FROM peminjaman WHERE buku_id = ? AND status = 'dipinjam'", [$id]);
$sedang_dipinjam = (int) mysqli_fetch_assoc($hasil)['jumlah'];

if ($sedang_dipinjam > 0) {
    pesan('gagal', 'Buku "' . $buku['judul'] . '" tidak bisa dihapus karena ' . $sedang_dipinjam . ' eksemplar masih dipinjam.');
    pindah('admin/bibliografi/');
}

// Hapus buku (riwayat peminjaman lamanya ikut terhapus otomatis)
mysqli_execute_query($koneksi, "DELETE FROM buku WHERE id = ?", [$id]);

pesan('sukses', 'Buku "' . $buku['judul'] . '" sudah dihapus.');
pindah('admin/bibliografi/');
