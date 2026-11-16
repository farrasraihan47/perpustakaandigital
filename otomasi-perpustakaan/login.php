<?php
// =========================================================
//  LOGIN PETUGAS
// =========================================================
require __DIR__ . '/includes/init.php';

// Kalau sudah login, langsung ke panel admin
if (!empty($_SESSION['user_id'])) {
    pindah('admin/');
}

$username = '';
$error = '';

// Form dikirim (tombol Masuk ditekan)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // 1. Cari user berdasarkan username
    $hasil = mysqli_execute_query($koneksi, "SELECT * FROM users WHERE username = ?", [$username]);
    $user = mysqli_fetch_assoc($hasil);

    // 2. Cocokkan password yang diketik dengan password terenkripsi di database
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);   // buat ID session baru supaya tidak bisa dibajak

        // 3. Simpan data login di session
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama']     = $user['nama'];

        pesan('sukses', 'Selamat datang, ' . $user['nama'] . '.');
        pindah('admin/');
    }

    // Pesan dibuat sama untuk username salah maupun password salah,
    // supaya orang lain tidak bisa menebak username mana yang ada.
    $error = 'Username atau password salah.';
}

$judul_halaman = 'Masuk Petugas';
require __DIR__ . '/includes/publik_atas.php';
?>

<div class="row justify-content-center align-items-center g-4 g-lg-5 py-lg-5">
    <div class="col-lg-5">
        <div class="alis">Khusus petugas</div>
        <h1 style="font-size: clamp(2.4rem, 5vw, 3.4rem)">Meja sirkulasi.</h1>
        <p class="text-secondary mt-3" style="max-width: 36ch">
            Masuk untuk mengelola bibliografi, keanggotaan, peminjaman, dan pengembalian buku.
            Pengunjung cukup memakai <a href="<?= url() ?>">katalog (OPAC)</a> tanpa login.
        </p>
    </div>

    <div class="col-md-8 col-lg-5">
        <div class="kertas position-relative">
            <span class="stempel position-absolute" style="top: -.9rem; right: 1.2rem">Petugas</span>

            <?php if ($error): ?>
                <div class="pesan gagal"><span class="stempel">Ditolak</span><div><?= e($error) ?></div></div>
            <?php endif; ?>

            <form method="post" action="<?= url('login.php') ?>">
                <div class="mb-3">
                    <label class="form-label" for="username">Username</label>
                    <input class="form-control form-control-lg" type="text" id="username" name="username"
                           value="<?= e($username) ?>" required autofocus autocomplete="username">
                </div>
                <div class="mb-4">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-control form-control-lg" type="password" id="password" name="password"
                           required autocomplete="current-password">
                </div>
                <button class="btn btn-primary btn-lg w-100" type="submit">Masuk</button>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/publik_bawah.php'; ?>
