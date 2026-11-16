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

// Menampilkan pesan dalam bentuk slip + stempel (dipanggil di layout)
function tampil_pesan()
{
    $pesan = ambil_pesan();
    if (!$pesan) {
        return;
    }
    $sukses = $pesan['jenis'] === 'sukses';
    echo '<div class="pesan ' . ($sukses ? 'sukses' : 'gagal') . '" role="status">';
    echo '<span class="stempel jatuh ' . ($sukses ? 'hijau' : '') . '">' . ($sukses ? 'Berhasil' : 'Ditolak') . '</span>';
    echo '<div>' . e($pesan['teks']) . '</div>';
    echo '</div>';
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

// Nomor panggil sederhana seperti di label punggung buku, 3 baris:
//   1. lokasi rak                               contoh: C-07
//   2. 3 huruf awal nama belakang pengarang     contoh: ORW  (George Orwell)
//   3. huruf awal judul (huruf kecil)           contoh: n    (Nineteen...) / 1 (1984)
function nomor_panggil($buku)
{
    $kata_pengarang = explode(' ', trim($buku['pengarang']));
    $nama_belakang = end($kata_pengarang);
    return [
        $buku['lokasi_rak'] ?: '—',
        mb_strtoupper(mb_substr($nama_belakang, 0, 3)),
        mb_strtolower(mb_substr(trim($buku['judul']), 0, 1)),
    ];
}

// Mengecek alamat link e-book: harus kosong, atau diawali http:// / https://
function link_valid($link)
{
    if ($link === '') {
        return true;
    }
    return filter_var($link, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $link);
}
