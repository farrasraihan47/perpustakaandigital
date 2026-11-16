<?php
// =========================================================
//  Potongan tampilan: form isian buku.
//  Dipakai bersama oleh tambah.php dan edit.php.
//  Butuh variabel: $buku, $kesalahan, $kode_buku, $teks_tombol
// =========================================================

// Daftar kategori yang sudah ada, untuk saran isian
$daftar_kategori = mysqli_query($koneksi, "SELECT DISTINCT kategori FROM buku ORDER BY kategori");
?>

<div class="kertas" style="max-width: 820px">
    <?php if ($kesalahan): ?>
        <div class="pesan gagal">
            <span class="stempel">Periksa</span>
            <ul class="mb-0 ps-3"><?php foreach ($kesalahan as $k): ?><li><?= e($k) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="row g-3">
            <div class="col-sm-4">
                <label class="form-label">Kode buku</label>
                <input class="form-control ketik" value="<?= e($kode_buku) ?>" disabled>
                <div class="form-text">Dibuat otomatis.</div>
            </div>
            <div class="col-sm-8">
                <label class="form-label" for="isbn">ISBN</label>
                <input class="form-control ketik" id="isbn" name="isbn" value="<?= e($buku['isbn']) ?>" maxlength="17" placeholder="978-…">
            </div>

            <div class="col-12">
                <label class="form-label" for="judul">Judul *</label>
                <input class="form-control" id="judul" name="judul" value="<?= e($buku['judul']) ?>" maxlength="255" required>
            </div>

            <div class="col-sm-6">
                <label class="form-label" for="pengarang">Pengarang *</label>
                <input class="form-control" id="pengarang" name="pengarang" value="<?= e($buku['pengarang']) ?>" maxlength="150" required>
            </div>
            <div class="col-sm-6">
                <label class="form-label" for="penerbit">Penerbit</label>
                <input class="form-control" id="penerbit" name="penerbit" value="<?= e($buku['penerbit']) ?>" maxlength="150">
            </div>

            <div class="col-sm-4">
                <label class="form-label" for="tahun_terbit">Tahun terbit</label>
                <input class="form-control" id="tahun_terbit" name="tahun_terbit" value="<?= e($buku['tahun_terbit']) ?>" maxlength="20" placeholder="2024">
            </div>
            <div class="col-sm-4">
                <label class="form-label" for="kategori">Kategori *</label>
                <input class="form-control" id="kategori" name="kategori" value="<?= e($buku['kategori']) ?>" maxlength="50" list="saran-kategori" required>
                <datalist id="saran-kategori">
                    <?php while ($k = mysqli_fetch_assoc($daftar_kategori)): ?>
                        <option value="<?= e($k['kategori']) ?>">
                    <?php endwhile; ?>
                </datalist>
            </div>
            <div class="col-sm-4">
                <label class="form-label" for="lokasi_rak">Lokasi rak</label>
                <input class="form-control ketik" id="lokasi_rak" name="lokasi_rak" value="<?= e($buku['lokasi_rak']) ?>" maxlength="20" placeholder="C-07">
            </div>

            <div class="col-sm-4">
                <label class="form-label" for="stok_total">Jumlah eksemplar *</label>
                <input class="form-control" type="number" id="stok_total" name="stok_total" value="<?= e($buku['stok_total']) ?>" min="1" max="999" required>
            </div>
            <div class="col-sm-8">
                <label class="form-label" for="link_ebook">Link e-book</label>
                <input class="form-control" type="url" id="link_ebook" name="link_ebook" value="<?= e($buku['link_ebook']) ?>" maxlength="255" placeholder="https://…">
                <div class="form-text">Opsional. Ditampilkan di OPAC sebagai tombol "Baca e-book".</div>
            </div>
        </div>

        <div class="mt-4">
            <button class="btn btn-primary" type="submit"><?= e($teks_tombol) ?></button>
            <a class="btn btn-garis" href="<?= url('admin/bibliografi/') ?>">Batal</a>
        </div>
    </form>
</div>
