<?php
// =========================================================
//  DAFTAR PENGGUNA (PETUGAS)
// =========================================================
require __DIR__ . '/../../includes/init.php';
wajib_login();

$daftar_user = mysqli_query($koneksi, "SELECT id, username, nama, created_at FROM users ORDER BY nama");

$judul_halaman = 'Pengguna';
$alis = 'Akun petugas';
$menu_aktif = 'pengguna';
$tombol_kepala = ['+ Tambah Pengguna' => 'admin/pengguna/tambah.php'];
require __DIR__ . '/../../includes/admin_atas.php';
?>

<div class="ledger-wrap">
    <table class="ledger">
        <thead>
            <tr>
                <th>Username</th>
                <th>Nama</th>
                <th>Terdaftar sejak</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = mysqli_fetch_assoc($daftar_user)): ?>
                <tr>
                    <td class="kode"><?= e($user['username']) ?></td>
                    <td><?= e($user['nama']) ?></td>
                    <td><?= tanggal($user['created_at']) ?></td>
                    <td class="aksi">
                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                            <span class="stempel hijau">Anda</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<p class="form-text mt-3">
    Setiap petugas mengganti password miliknya sendiri lewat menu
    <a href="<?= url('admin/pengguna/ganti-password.php') ?>">Ganti Password</a>.
</p>

<?php require __DIR__ . '/../../includes/admin_bawah.php'; ?>
