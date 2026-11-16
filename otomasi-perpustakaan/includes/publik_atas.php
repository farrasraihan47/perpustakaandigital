<?php
// Bagian ATAS halaman publik (OPAC).
// Sebelum file ini di-include, isi dulu: $judul_halaman = '...';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul_halaman ?? 'Katalog') ?> · <?= e(NAMA_PERPUSTAKAAN) ?></title>
    <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/app.css') ?>">
</head>
<body>

<header class="kepala">
    <div class="container d-flex justify-content-between align-items-end flex-wrap gap-2">
        <a class="merek" href="<?= url() ?>">
            <?= e(NAMA_PERPUSTAKAAN) ?>
            <small>Katalog daring · OPAC</small>
        </a>
        <nav>
            <a href="<?= url() ?>">Katalog</a>
            <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="<?= url('admin/') ?>">Panel Petugas &rarr;</a>
            <?php else: ?>
                <a href="<?= url('login.php') ?>">Masuk Petugas &rarr;</a>
            <?php endif; ?>
        </nav>
    </div>
    <div class="container"><div class="garis-ganda"></div></div>
</header>

<main class="container py-4">
    <?php tampil_pesan(); ?>
