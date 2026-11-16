<?php
// Bagian ATAS halaman admin (sidebar + judul halaman).
// Sebelum file ini di-include, isi dulu:
//   $judul_halaman = 'Bibliografi';       judul besar halaman
//   $menu_aktif    = 'bibliografi';       menu yang disorot di sidebar
//   $alis          = 'Koleksi';           (opsional) label kecil di atas judul
//   $tombol_kepala = ['+ Tambah Buku' => 'admin/bibliografi/tambah.php'];   (opsional)

$menu_aktif = $menu_aktif ?? '';

// Membuat class "aktif" untuk menu yang sedang dibuka
function aktif($nama_menu)
{
    global $menu_aktif;
    return $menu_aktif === $nama_menu ? 'aktif' : '';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul_halaman ?? 'Panel') ?> · Panel Petugas</title>
    <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/app.css') ?>">
</head>
<body>
<div class="admin">

    <!-- SIDEBAR "laci" -->
    <div class="laci">
        <aside class="offcanvas-lg offcanvas-start" tabindex="-1" id="menu-laci" aria-label="Menu admin">
            <div class="offcanvas-body">
                <div class="d-flex justify-content-between align-items-start">
                    <a class="merek" href="<?= url('admin/') ?>">
                        <?= e(NAMA_PERPUSTAKAAN) ?>
                        <small>Panel Petugas</small>
                    </a>
                    <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#menu-laci" aria-label="Tutup menu"></button>
                </div>

                <nav class="menu">
                    <div class="kelompok">Ringkasan</div>
                    <a class="<?= aktif('dashboard') ?>" href="<?= url('admin/') ?>">Dashboard</a>
                    <a href="<?= url() ?>">Lihat OPAC</a>

                    <div class="kelompok">Koleksi</div>
                    <a class="<?= aktif('bibliografi') ?>" href="<?= url('admin/bibliografi/') ?>">Bibliografi</a>
                    <a class="<?= aktif('anggota') ?>" href="<?= url('admin/anggota/') ?>">Keanggotaan</a>

                    <div class="kelompok">Sirkulasi</div>
                    <a class="<?= aktif('pinjam') ?>" href="<?= url('admin/sirkulasi/pinjam.php') ?>">Peminjaman</a>
                    <a class="<?= aktif('kembali') ?>" href="<?= url('admin/sirkulasi/kembali.php') ?>">Pengembalian</a>
                    <a class="<?= aktif('riwayat') ?>" href="<?= url('admin/sirkulasi/riwayat.php') ?>">Riwayat</a>

                    <div class="kelompok">Akun</div>
                    <a class="<?= aktif('pengguna') ?>" href="<?= url('admin/pengguna/') ?>">Pengguna</a>
                    <a class="<?= aktif('password') ?>" href="<?= url('admin/pengguna/ganti-password.php') ?>">Ganti Password</a>
                </nav>

                <div class="petugas">
                    Bertugas: <b><?= e($_SESSION['nama'] ?? '-') ?></b><br>
                    <a href="<?= url('logout.php') ?>" class="link-light">Keluar (Logout)</a>
                </div>
            </div>
        </aside>
    </div>

    <!-- AREA KERJA -->
    <main class="meja">
        <button class="btn btn-garis btn-sm d-lg-none mb-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#menu-laci" aria-controls="menu-laci">
            &#9776; Menu
        </button>

        <div class="kepala-meja">
            <div>
                <?php if (!empty($alis)): ?><div class="alis"><?= e($alis) ?></div><?php endif; ?>
                <h1><?= e($judul_halaman ?? '') ?></h1>
            </div>
            <?php if (!empty($tombol_kepala)): ?>
                <div class="d-flex gap-2 flex-wrap">
                    <?php $tombol_pertama = true; ?>
                    <?php foreach ($tombol_kepala as $teks => $alamat): ?>
                        <a class="btn <?= $tombol_pertama ? 'btn-primary' : 'btn-garis' ?>" href="<?= url($alamat) ?>"><?= e($teks) ?></a>
                        <?php $tombol_pertama = false; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php tampil_pesan(); ?>
