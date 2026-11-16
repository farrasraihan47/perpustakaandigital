<?php
// =========================================================
//  GANTI PASSWORD (milik petugas yang sedang login)
// =========================================================
require __DIR__ . '/../../includes/init.php';
wajib_login();

$kesalahan = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_lama  = $_POST['password_lama'] ?? '';
    $password_baru  = $_POST['password_baru'] ?? '';
    $password_baru2 = $_POST['password_baru2'] ?? '';

    // Ambil password terenkripsi milik user yang sedang login
    $hasil = mysqli_execute_query($koneksi, "SELECT password_hash FROM users WHERE id = ?", [$_SESSION['user_id']]);
    $user = mysqli_fetch_assoc($hasil);

    // --- Pemeriksaan isian ---
    if (!password_verify($password_lama, $user['password_hash'])) {
        $kesalahan[] = 'Password lama salah.';
    }
    if (strlen($password_baru) < 8) {
        $kesalahan[] = 'Password baru minimal 8 karakter.';
    }
    if ($password_baru !== $password_baru2) {
        $kesalahan[] = 'Ulangi password baru tidak sama.';
    }
    if ($password_baru === $password_lama) {
        $kesalahan[] = 'Password baru harus berbeda dari password lama.';
    }

    // --- Simpan jika tidak ada kesalahan ---
    if (!$kesalahan) {
        $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
        mysqli_execute_query($koneksi, "UPDATE users SET password_hash = ? WHERE id = ?", [$password_hash, $_SESSION['user_id']]);
        pesan('sukses', 'Password berhasil diganti. Gunakan password baru saat login berikutnya.');
        pindah('admin/pengguna/ganti-password.php');
    }
}

$judul_halaman = 'Ganti Password';
$alis = 'Akun · ' . $_SESSION['username'];
$menu_aktif = 'password';
require __DIR__ . '/../../includes/admin_atas.php';
?>

<div class="kertas" style="max-width: 480px">
    <?php if ($kesalahan): ?>
        <div class="pesan gagal">
            <span class="stempel">Periksa</span>
            <ul class="mb-0 ps-3"><?php foreach ($kesalahan as $k): ?><li><?= e($k) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label" for="password_lama">Password lama</label>
            <input class="form-control" type="password" id="password_lama" name="password_lama" required autocomplete="current-password">
        </div>
        <div class="mb-3">
            <label class="form-label" for="password_baru">Password baru</label>
            <input class="form-control" type="password" id="password_baru" name="password_baru" minlength="8" required autocomplete="new-password">
            <div class="form-text">Minimal 8 karakter.</div>
        </div>
        <div class="mb-4">
            <label class="form-label" for="password_baru2">Ulangi password baru</label>
            <input class="form-control" type="password" id="password_baru2" name="password_baru2" minlength="8" required autocomplete="new-password">
        </div>
        <button class="btn btn-primary" type="submit">Ganti Password</button>
    </form>
</div>

<?php require __DIR__ . '/../../includes/admin_bawah.php'; ?>
