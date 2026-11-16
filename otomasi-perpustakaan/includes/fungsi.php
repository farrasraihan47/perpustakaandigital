<?php
// Kumpulan fungsi bantu yang dipakai di banyak halaman.

// Menampilkan teks dengan aman di HTML (mencegah XSS / kode jahat ikut tampil).
// Semua data dari database atau dari input pengguna WAJIB ditampilkan lewat e().
function e($teks)
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

// Membuat alamat lengkap, contoh: url('admin/') -> /admin/
function url($alamat = '')
{
    return BASE_URL . $alamat;
}

// Pindah ke halaman lain lalu berhenti
function pindah($alamat)
{
    header('Location: ' . url($alamat));
    exit;
}

// Menyimpan pesan untuk ditampilkan sekali di halaman berikutnya.
// $jenis: 'sukses' atau 'gagal'
function pesan($jenis, $teks)
{
    $_SESSION['pesan'] = ['jenis' => $jenis, 'teks' => $teks];
}

// Mengambil pesan (lalu menghapusnya supaya tidak muncul lagi)
function ambil_pesan()
{
    $pesan = $_SESSION['pesan'] ?? null;
    unset($_SESSION['pesan']);
    return $pesan;
}

// Dipanggil di baris atas setiap halaman admin.
// Kalau belum login, pengunjung dikirim ke halaman login.
function wajib_login()
{
    if (empty($_SESSION['user_id'])) {
        pesan('gagal', 'Silakan login terlebih dahulu.');
        pindah('login.php');
    }
}

// 3000 -> Rp3.000
function rupiah($angka)
{
    return 'Rp' . number_format((int) $angka, 0, ',', '.');
}

// 2026-11-16 -> 16 Nov 2026
function tanggal($tgl)
{
    if (!$tgl) {
        return '-';
    }
    $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $waktu = strtotime($tgl);
    return date('j', $waktu) . ' ' . $bulan[(int) date('n', $waktu)] . ' ' . date('Y', $waktu);
}

// Menghitung berapa hari terlambat. Hasilnya 0 jika tidak terlambat.
function hitung_terlambat($jatuh_tempo, $tanggal_kembali)
{
    $selisih_detik = strtotime($tanggal_kembali) - strtotime($jatuh_tempo);
    $hari = (int) floor($selisih_detik / 86400);
    return max(0, $hari);
}

// Mengecek alamat link e-book: harus kosong, atau diawali http:// / https://
function link_valid($link)
{
    if ($link === '') {
        return true;
    }
    return filter_var($link, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $link);
}
