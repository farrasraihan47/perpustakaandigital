<?php
// =========================================================
//  TAMBAH PENGGUNA (PETUGAS BARU)
// =========================================================
require __DIR__ . '/../../includes/init.php';
wajib_login();

$nama = '';
$username = '';
$kesalahan = [];   // daftar pesan kesalahan isian

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = trim($_POST['nama'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // --- Pemeriksaan isian ---
    if ($nama === '') {
        $kesalahan[] = 'Nama wajib diisi.';
    }
    if (mb_strlen($nama) > 100) {
        $kesalahan[] = 'Nama terlalu panjang (maksimal 100 karakter).';
    }
    if (!preg_match('/^[a-zA-Z0-9_.]{3,50}$/', $username)) {
        $kesalahan[] = 'Username 3–50 karakter, hanya huruf, angka, titik, atau garis bawah.';
    }
    if (strlen($password) < 8 || strlen($password) > 72) {
        $kesalahan[] = 'Password 8 sampai 72 karakter.';
    }
    if ($password !== $password2) {
        $kesalahan[] = 'Ulangi password tidak sama.';
    }

    // Username tidak boleh sudah dipakai
    $hasil = mysqli_execute_query($koneksi, "SELECT id FROM users WHERE username = ?", [$username]);
    if (mysqli_num_rows($hasil) > 0) {
        $kesalahan[] = 'Username "' . $username . '" sudah dipakai.';
    }

    // --- Simpan jika tidak ada kesalahan ---
    if (!$kesalahan) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);   // enkripsi password
        mysqli_execute_query(
            $koneksi,
            "INSERT INTO users (username, password_hash, nama) VALUES (?, ?, ?)",
            [$username, $password_hash, $nama]
        );
        pesan('sukses', 'Pengguna "' . $username . '" berhasil ditambahkan.');
        pindah('admin/pengguna/');
    }
}

$judul_halaman = 'Tambah Pengguna';
$alis = 'Akun petugas';
$menu_aktif = 'pengguna';
require __DIR__ . '/../../includes/admin_atas.php';
?>

<div class="kertas" style="max-width: 560px">
    <?php if ($kesalahan): ?>
        <div class="pesan gagal">
            <span class="stempel">Periksa</span>
            <ul class="mb-0 ps-3"><?php foreach ($kesalahan as $k): ?><li><?= e($k) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label" for="nama">Nama lengkap</label>
            <input class="form-control" id="nama" name="nama" value="<?= e($nama) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="username">Username</label>
            <input class="form-control" id="username" name="username" value="<?= e($username) ?>" required autocomplete="off">
            <div class="form-text">Huruf, angka, titik, atau garis bawah. Contoh: dewi.sirkulasi</div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-sm-6">
                <label class="form-label" for="password">Password</label>
                <input class="form-control" type="password" id="password" name="password" minlength="8" maxlength="72" required autocomplete="new-password">
            </div>
            <div class="col-sm-6">
                <label class="form-label" for="password2">Ulangi password</label>
                <input class="form-control" type="password" id="password2" name="password2" minlength="8" maxlength="72" required autocomplete="new-password">
            </div>
        </div>
        <button class="btn btn-primary" type="submit">Simpan Pengguna</button>
        <a class="btn btn-garis" href="<?= url('admin/pengguna/') ?>">Batal</a>
    </form>
</div>

<?php require __DIR__ . '/../../includes/admin_bawah.php'; ?>
