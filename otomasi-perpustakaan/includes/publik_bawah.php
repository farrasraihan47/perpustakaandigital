<?php // Bagian BAWAH halaman publik (OPAC). ?>
</main>

<footer class="kaki">
    <div class="container d-flex justify-content-between flex-wrap gap-2">
        <span><?= e(NAMA_PERPUSTAKAAN) ?> &middot; Sistem Otomasi Perpustakaan</span>
        <span>Lama pinjam <?= LAMA_PINJAM ?> hari &middot; denda <?= rupiah(DENDA_PER_HARI) ?>/hari</span>
    </div>
</footer>

<script src="<?= url('assets/vendor/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
