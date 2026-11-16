<?php
// =========================================================
//  Potongan tampilan: form isian anggota.
//  Dipakai bersama oleh tambah.php dan edit.php.
//  Butuh variabel: $anggota, $kesalahan, $no_anggota, $tanggal_daftar, $teks_tombol
// =========================================================
?>

<div class="kertas" style="max-width: 720px">
    <?php if ($kesalahan): ?>
        <div class="pesan gagal">
            <span class="stempel">Periksa</span>
            <ul class="mb-0 ps-3"><?php foreach ($kesalahan as $k): ?><li><?= e($k) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="row g-3">
            <div class="col-sm-6">
                <label class="form-label">No. anggota</label>
                <input class="form-control ketik" value="<?= e($no_anggota) ?>" disabled>
                <div class="form-text">Dibuat otomatis.</div>
            </div>
            <div class="col-sm-6">
                <label class="form-label">Tanggal daftar</label>
                <input class="form-control" value="<?= tanggal($tanggal_daftar) ?>" disabled>
            </div>

            <div class="col-12">
                <label class="form-label" for="nama">Nama lengkap *</label>
                <input class="form-control" id="nama" name="nama" value="<?= e($anggota['nama']) ?>" maxlength="100" required>
            </div>

            <div class="col-12">
                <label class="form-label" for="alamat">Alamat</label>
                <textarea class="form-control" id="alamat" name="alamat" rows="2"><?= e($anggota['alamat']) ?></textarea>
            </div>

            <div class="col-sm-6">
                <label class="form-label" for="no_hp">No. HP</label>
                <input class="form-control ketik" id="no_hp" name="no_hp" value="<?= e($anggota['no_hp']) ?>" maxlength="20" placeholder="0812…">
            </div>
            <div class="col-sm-6">
                <label class="form-label" for="email">Email</label>
                <input class="form-control" type="email" id="email" name="email" value="<?= e($anggota['email']) ?>" maxlength="100">
            </div>

            <div class="col-sm-6">
                <label class="form-label" for="status">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="aktif" <?= $anggota['status'] === 'aktif' ? 'selected' : '' ?>>Aktif — boleh meminjam</option>
                    <option value="nonaktif" <?= $anggota['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif — tidak boleh meminjam</option>
                </select>
            </div>
        </div>

        <div class="mt-4">
            <button class="btn btn-primary" type="submit"><?= e($teks_tombol) ?></button>
            <a class="btn btn-garis" href="<?= url('admin/anggota/') ?>">Batal</a>
        </div>
    </form>
</div>
