# Rancangan: Sistem Otomasi Perpustakaan (PHP Native + MySQL)

Tugas Besar Praktikum Aplikasi Teknologi Informasi 2026.
Status: **rancangan — belum ada kode yang ditulis.**

---

## 1. Ringkasan

Aplikasi web otomasi perpustakaan dengan menu wajib: **Login, OPAC, Bibliografi, Keanggotaan, Sirkulasi, Logout**.
Dibangun dengan PHP native (tanpa framework) + MySQL, UI Bootstrap 5 yang dikustomisasi.
Lokasi: folder `otomasi-perpustakaan/` di repo ini (situs GitHub Pages yang lama tetap utuh).

Prinsip utama: **setiap anggota kelompok harus bisa menjelaskan kodenya saat responsi.**
Maka: satu halaman = satu file PHP, tanpa router/MVC, query ditulis langsung dan terbaca.

Kategori laporan: **Bantuan AI** (dibangun dari nol dengan bantuan AI).

---

## 2. Keputusan yang sudah diambil

| Hal | Keputusan |
|---|---|
| Lokasi | Folder baru `otomasi-perpustakaan/` di repo ini |
| Cakupan | Semua checklist dosen (tingkat tertinggi) + bonus ringan |
| Arsitektur | Satu halaman = satu file, bagian bersama di `includes/` |
| Akses DB | `mysqli` + prepared statement |
| UI | Bootstrap 5 dikustomisasi penuh (lihat §6), file disimpan lokal di `assets/vendor/` agar demo tetap jalan tanpa internet |
| Dev env | Docker Compose (tidak ada XAMPP) |
| Deploy | Disiapkan untuk hosting gratis (mis. InfinityFree), dilakukan di fase akhir |

---

## 3. Struktur folder

```
otomasi-perpustakaan/
├── docker-compose.yml          PHP 8.4.27 + Apache, MySQL 8.4, phpMyAdmin (dev saja)
├── docker/php.ini              hardening php.ini (lihat §7)
├── .htaccess                   security header + blokir folder sensitif
├── database/
│   ├── 01-schema.sql           struktur tabel
│   └── 02-seed.sql             data contoh (11 judul dari folder ebook/, 5 anggota, 1 admin)
├── config/config.php           kredensial DB, BASE_URL, aturan pinjam   (diblokir dari web)
├── includes/                   (diblokir dari web)
│   ├── init.php                koneksi database + mulai session (di-require tiap halaman)
│   ├── fungsi.php              e(), pesan(), wajib_login(), rupiah(), tanggal(), hitung_terlambat()
│   ├── publik_atas.php / publik_bawah.php   layout OPAC
│   └── admin_atas.php / admin_bawah.php     layout admin (sidebar)
├── assets/
│   ├── css/app.css             token desain + override Bootstrap
│   ├── fonts/                  font self-host
│   └── vendor/                 bootstrap, jsbarcode (versi dikunci)
├── index.php                   OPAC — pencarian (publik)
├── detail.php                  OPAC — detail buku
├── login.php / logout.php
└── admin/
    ├── index.php               dashboard statistik
    ├── pengguna/   index.php, tambah.php, ganti-password.php
    ├── bibliografi/ index.php, tambah.php, edit.php, hapus.php, label.php
    ├── anggota/    index.php, tambah.php, edit.php, hapus.php, kartu.php
    └── sirkulasi/  pinjam.php, kembali.php, riwayat.php
```

Setiap folder di `admin/` cocok dipegang satu anggota kelompok → bagian **Job Description** laporan langsung terisi.

---

## 4. Database

Semua tabel `InnoDB`, charset `utf8mb4`.

**users**
| kolom | tipe | catatan |
|---|---|---|
| id | INT PK AI | |
| username | VARCHAR(50) UNIQUE | |
| password_hash | VARCHAR(255) | `password_hash()` |
| nama | VARCHAR(100) | |
| created_at | DATETIME | |

**buku**
| kolom | tipe | catatan |
|---|---|---|
| id | INT PK AI | |
| kode_buku | VARCHAR(20) UNIQUE | juga isi barcode, mis. `BK0001` |
| isbn | VARCHAR(20) NULL | |
| judul | VARCHAR(255) | |
| pengarang | VARCHAR(150) | |
| penerbit | VARCHAR(150) NULL | |
| tahun_terbit | VARCHAR(20) NULL | teks, karena ada "Abad ke-8 SM" |
| kategori | VARCHAR(50) | |
| lokasi_rak | VARCHAR(20) NULL | |
| stok_total | INT UNSIGNED | |
| stok_tersedia | INT UNSIGNED | 0 ≤ tersedia ≤ total |
| link_ebook | VARCHAR(255) NULL | URL, bukan upload file |
| created_at | DATETIME | |

**anggota**
| kolom | tipe | catatan |
|---|---|---|
| id | INT PK AI | |
| no_anggota | VARCHAR(20) UNIQUE | juga isi barcode kartu, mis. `AG0001` |
| nama | VARCHAR(100) | |
| alamat | TEXT NULL | |
| no_hp | VARCHAR(20) NULL | |
| email | VARCHAR(100) NULL | |
| tanggal_daftar | DATE | |
| status | ENUM('aktif','nonaktif') | |

**peminjaman**
| kolom | tipe | catatan |
|---|---|---|
| id | INT PK AI | |
| anggota_id | INT FK → anggota (CASCADE) | |
| buku_id | INT FK → buku (CASCADE) | |
| petugas_id | INT NULL FK → users (SET NULL) | |
| tanggal_pinjam | DATE | |
| jatuh_tempo | DATE | pinjam + `LAMA_PINJAM` |
| tanggal_kembali | DATE NULL | |
| hari_terlambat | INT UNSIGNED DEFAULT 0 | |
| denda | INT UNSIGNED DEFAULT 0 | rupiah |
| status | ENUM('dipinjam','dikembalikan') | |

Aturan di `config.php`: `LAMA_PINJAM = 7` hari, `DENDA_PER_HARI = 1000`, `MAKS_PINJAM = 3`.

---

## 5. Fitur & pemetaan ke checklist dosen

| Modul | Checklist wajib | Fitur tambahan (diisi di kolom Keterangan) |
|---|---|---|
| **Login** | Login berfungsi; ganti password (wajib isi password lama); tambah username | Password disimpan terenkripsi (hash), peringatan jika password bawaan belum diganti |
| **OPAC** (publik) | Search berdasar: **semua kolom, judul, pengarang, penerbit, ISBN, kategori, tahun** | Status ketersediaan ("2 dari 3 tersedia"), halaman detail + perkiraan tanggal tersedia, jelajah per kategori, link baca e-book, paginasi |
| **Bibliografi** | Tampil, tambah, update, delete | **Cetak label barcode buku** (JsBarcode → print) |
| **Keanggotaan** | Tampil, tambah, update, delete | **Cetak kartu anggota** dengan barcode |
| **Sirkulasi** | Pinjam (stok −1), kembali (stok +1), hitung hari terlambat & denda | Riwayat peminjaman, daftar terlambat, validasi (stok habis / batas 3 buku / anggota nonaktif / buku sama sedang dipinjam) |
| **Logout** | Hapus session | |
| **Tambahan** | — | Dashboard statistik; bisa diakses online via link |

### Alur penting

**Pinjam** — input no. anggota + kode buku → cek: anggota ada & aktif, stok_tersedia > 0, pinjaman aktif < 3, belum meminjam buku yang sama →
`INSERT peminjaman` + `UPDATE buku SET stok_tersedia = stok_tersedia - 1`, dibungkus `mysqli_begin_transaction` / `mysqli_commit` supaya keduanya terjadi bersama atau tidak sama sekali.

**Kembali** — pilih peminjaman aktif → `hari_terlambat = max(0, tanggal_kembali − jatuh_tempo)`, `denda = hari_terlambat × DENDA_PER_HARI` →
update peminjaman (status, tanggal, denda) + `stok_tersedia + 1`. Perkiraan denda sudah tampil di daftar sebelum tombol ditekan.

**Hapus buku/anggota** — ditolak dengan pesan jika masih ada peminjaman berstatus `dipinjam`. Jika hanya ada riwayat lama, riwayatnya ikut terhapus (FK `ON DELETE CASCADE`). Hapus lewat tombol (form POST) + konfirmasi `confirm()`.

**Edit stok buku** — `stok_total` tidak boleh lebih kecil dari jumlah eksemplar yang sedang dipinjam; `stok_tersedia` dihitung ulang = total − sedang dipinjam.

**Pengguna** — admin bisa menambah admin lain; tiap admin mengganti password sendiri (verifikasi password lama, minimal 8 karakter, konfirmasi).

---

## 6. Arah desain (anti "AI slop")

### Konsep: **"Laci Katalog"**
Perpustakaan punya bahasa visual sendiri yang jarang dipakai web modern: **kartu katalog**, **stempel tanggal kembali**, **label punggung buku**, dan **buku besar sirkulasi**. Seluruh UI diturunkan dari benda-benda itu — bukan dari template dashboard SaaS.

Yang membuatnya diingat: **hasil OPAC tampil sebagai kartu katalog** (kertas krem, garis-garis tipis, nomor panggil diketik mesin tik di pojok kiri atas, lubang tali di bawah), dan **status sirkulasi tampil sebagai stempel** (miring, tinta merah "TERLAMBAT", tinta hijau "KEMBALI").

### Token
| Token | Nilai | Dipakai untuk |
|---|---|---|
| `--kertas` | `#F3EDE2` | latar utama (krem kertas katalog) |
| `--kartu` | `#FBF8F1` | permukaan kartu |
| `--tinta` | `#1D1A16` | teks utama |
| `--pensil` | `#6B6358` | teks sekunder, garis |
| `--stempel` | `#A3291C` | aksen utama: tombol utama, status terlambat, denda |
| `--laci` | `#24452F` | hijau lampu baca: sidebar admin, status tersedia/kembali |
| `--kuningan` | `#B08A3E` | detail kecil: pegangan laci, garis aktif |

Tidak ada gradasi ungu, tidak ada glassmorphism, tidak ada biru default Bootstrap. Warna Bootstrap (`--bs-primary`, dll.) di-override ke token di atas.

### Tipografi
- **Judul:** *Libre Caslon Display* — huruf buku klasik, cocok untuk judul halaman & judul buku.
- **Isi & UI:** *Public Sans* — sans netral yang sangat terbaca untuk form dan tabel.
- **Kode/nomor:** *Courier Prime* — gaya mesin tik untuk `kode_buku`, `no_anggota`, nomor panggil, tanggal di stempel.

Font disimpan lokal di `assets/fonts/` (tanpa Google Fonts saat runtime).

### Komponen kunci
- **Kartu katalog** (OPAC): rasio kartu indeks 5:3, garis horizontal tipis via `repeating-linear-gradient`, garis merah di atas, lubang tali di tengah bawah.
- **Stempel** (status): border ganda, rotasi −4°, huruf kapital Courier, sedikit tekstur tinta via mask.
- **Ledger** (tabel admin): baris padat, angka rata kanan dengan `tabular-nums`, header sticky, tanpa bayangan kartu.
- **Laci** (sidebar admin): hijau tua, menu seperti label laci dengan bingkai kuningan untuk menu aktif.
- **Kartu anggota & label barcode**: dirancang untuk dicetak (`@media print`, ukuran mm nyata: kartu 85,6 × 54 mm).

### Gerak
Hemat dan bermakna: hasil OPAC muncul bertahap (stagger 40 ms), stempel "jatuh" (scale 1.2 → 1) saat pengembalian berhasil. Semua dimatikan di `prefers-reduced-motion`.

### Tata letak
- **OPAC**: kolom pencarian besar di atas seperti laci katalog yang ditarik, filter kriteria sebagai tab pembatas laci, hasil dalam grid kartu.
- **Admin**: sidebar laci + area kerja ledger. Responsif sampai lebar ponsel.

---

## 7. Keamanan & CVE PHP terbaru

### 7.1 Status CVE (dicek 16 Nov 2026 dari php.net)

**PHP2Shell — CVE-2026-109618 & CVE-2026-109619 (kritis, CVSS 9.2, sudah dieksploitasi).**
RCE tanpa autentikasi di siklus request inti PHP; mengenai **semua** instalasi PHP yang melayani web (mod_php, PHP-FPM, php-cgi, dll.), bahkan **sebelum kode aplikasi berjalan**. Menurut php.net: *"There are no complete mitigations, updating is the only safe choice."*
Versi aman: **8.5.12, 8.4.27, 8.3.36, 8.2.35** atau lebih baru. PHP 8.1 ke bawah tidak dapat rilis resmi.

**CVE-2026-115560 (session upload progress)** — belum ada fix; php.net menyarankan `session.upload_progress.enabled=0` dan **jangan pernah mengganti `session.serialize_handler` saat runtime**.

**CVE-2025-1218 (mysqlnd packet overread)** — relevan karena kita memakai mysqli; sudah tercakup di 8.4.26+.

**CVE-2024-4577 / 8926 / 8927 (php-cgi di Windows)** — relevan jika teman memakai **XAMPP di Windows** dengan PHP lama.

### 7.2 Konsekuensi untuk proyek ini
| Lingkungan | Tindakan |
|---|---|
| Docker (dev) | Image dikunci ke **`php:8.4.27-apache`** (sudah dicek tersedia). Jangan pakai tag tanpa versi patch. |
| Mac kamu | PHP Homebrew saat ini **8.4.14 → rentan**. Jalankan `brew upgrade php` (tidak dipakai untuk serve, tapi tetap perbarui). |
| Laptop teman (XAMPP/Laragon) | Cek `php -v` ≥ 8.4.27 / 8.3.36 / 8.2.35. XAMPP sering tertinggal versi → lebih aman pakai Docker yang sama. |
| Hosting gratis | Sebelum deploy, cek versi PHP hosting (file `phpinfo` sementara, lalu **hapus**). Jika di bawah versi aman → cari hosting lain atau tetap demo lokal, dan tulis di laporan. |

### 7.3 Hardening `php.ini` (docker/php.ini; ditiru via `.htaccess`/panel di hosting jika diizinkan)
```
expose_php = Off
display_errors = Off            ; dev boleh On, produksi Off
log_errors = On
session.upload_progress.enabled = 0   ; mitigasi parsial CVE-2026-109619 + CVE-2026-115560
post_max_size = 8M              ; tidak ada upload file (mitigasi parsial CVE-2026-109618)
file_uploads = Off
memory_limit = 128M             ; + limit memori container di docker-compose
allow_url_include = Off
allow_url_fopen = Off
session.use_strict_mode = 1
session.cookie_httponly = 1
session.cookie_samesite = Strict
session.cookie_secure = 1       ; hanya saat HTTPS (hosting)
```

### 7.4 Keamanan — dibagi dua lapis

Mahasiswanya Ilmu Perpustakaan, bukan IT. Maka keamanan dibagi:

**Lapis "mesin" — dipasang sekali, tidak perlu dijelaskan saat responsi**
- Versi PHP terkunci di versi yang sudah di-patch (§7.2) + `php.ini` (§7.3).
- `session.cookie_samesite = Strict` — browser tidak mengirim cookie login dari situs lain, jadi serangan CSRF tertahan **tanpa** perlu token di kode.
- `.htaccess` memblokir folder `config/`, `includes/`, `database/`, `docker/` dan file berawalan `_`; header `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`.
- Docker: port hanya bind ke `127.0.0.1`; phpMyAdmin **tidak pernah** di-deploy. Aplikasi pakai user MySQL sendiri, bukan root.
- Bootstrap, JsBarcode, font disimpan lokal dengan versi terkunci.
- **Tidak ada upload file** dan tidak memakai fitur PHP yang sering kena CVE (`unserialize`, `eval`, phar, SOAP, GD).

**Lapis kode — pola standar yang memang ada di tutorial PHP, mudah dijelaskan**
- **Query pakai tanda `?`** lewat `mysqli_execute_query($koneksi, $sql, [$nilai])` (bawaan PHP 8.2+, satu baris) → aman dari SQL injection.
- **Tampilkan data lewat `e()`** = `htmlspecialchars` → aman dari XSS.
- **Password** pakai `password_hash` / `password_verify`.
- **Halaman admin** diawali `wajib_login();`.
- **Login** memanggil `session_regenerate_id(true)`; **logout** menghapus session.
- **Link e-book** dicek harus diawali `http://` atau `https://`.

Kelemahan yang **sengaja** tidak ditangani (untuk bagian Kelemahan laporan): semua admin setara (tidak ada role), tidak ada pembatasan percobaan login, tidak ada audit log, tidak ada notifikasi jatuh tempo, daftar di admin tanpa paginasi.

---

## 8. Fase pengerjaan

Setiap fase punya kriteria **selesai** yang bisa dicek. Fase 3–7 bisa dibagi ke anggota berbeda setelah Fase 0–2 selesai.

### Fase 0 — Lingkungan aman
`docker-compose.yml` (php:8.4.27-apache + mysqli, mysql:8.4, phpmyadmin), `docker/php.ini`, `.htaccess`, kerangka folder kosong.
**Selesai jika:** `docker compose up` jalan; `http://localhost:8088` merespons; `php -v` di container ≥ 8.4.27; `session.upload_progress.enabled` = 0; akses `/config/config.php` → 403.

### Fase 1 — Database
`schema.sql` + `seed.sql` (11 judul dari folder `ebook/` lengkap link e-book, 5 anggota, 1 admin, beberapa peminjaman termasuk satu yang terlambat untuk demo denda), user MySQL khusus aplikasi.
**Selesai jika:** container baru otomatis berisi tabel & data; stok_tersedia di data contoh konsisten dengan peminjaman aktif.

### Fase 2 — Fondasi & sistem desain
`includes/*` (init, fungsi, layout), `assets/css/app.css` dengan token §6, font & vendor lokal, komponen: kartu katalog, stempel, ledger, sidebar laci, flash message.
**Selesai jika:** halaman kosong publik & admin tampil dengan identitas "Laci Katalog" di desktop dan ponsel; tidak ada request ke domain luar (cek tab Network).

### Fase 3 — Login & Pengguna  *(anggota 1)*
`login.php`, `logout.php`, `admin/index.php` (kerangka), `admin/pengguna/*`.
**Selesai jika:** login benar/salah ditangani; halaman admin tidak bisa diakses tanpa login; tambah admin baru lalu login dengan akun itu; ganti password menolak password lama yang salah; logout benar-benar mengakhiri session.

### Fase 4 — Bibliografi  *(anggota 2)*
`admin/bibliografi/*` termasuk cetak label barcode.
**Selesai jika:** CRUD jalan dengan validasi; kode buku duplikat ditolak; hapus buku yang sedang dipinjam ditolak; label tercetak rapi di print preview.

### Fase 5 — Keanggotaan  *(anggota 3)*
`admin/anggota/*` termasuk kartu anggota.
**Selesai jika:** CRUD jalan; no. anggota otomatis; status aktif/nonaktif; kartu tercetak ukuran 85,6 × 54 mm dengan barcode terbaca.

### Fase 6 — Sirkulasi  *(anggota 4; butuh Fase 4 & 5)*
`admin/sirkulasi/*`.
**Selesai jika:** pinjam mengurangi stok; kembali menambah stok; denda benar untuk 0, 1, dan banyak hari terlambat; semua validasi pinjam menolak dengan pesan jelas; riwayat & daftar terlambat bisa difilter.

### Fase 7 — OPAC  *(anggota 5; butuh Fase 4)*
`index.php`, `detail.php`.
**Selesai jika:** pencarian bekerja untuk setiap kriteria (semua, judul, pengarang, penerbit, ISBN, kategori, tahun); ketersediaan ikut berubah setelah pinjam/kembali; input berisi `<script>` / `' OR 1=1` tampil aman sebagai teks.

### Fase 8 — Dashboard & pemeriksaan keamanan
Statistik dashboard; review keamanan menyeluruh (§7.4 dicentang satu per satu); **cek ulang php.net untuk CVE baru** sebelum deploy.
**Selesai jika:** semua butir §7.4 terverifikasi; tidak ada error PHP di log saat uji.

### Fase 9 — Uji checklist, deploy & bahan laporan
Uji manual mengikuti tabel checklist dosen baris per baris; deploy ke hosting (setelah cek versi PHP hosting); screenshot tiap fitur; draf **user manual**, **kelebihan**, **kelemahan**, **simpulan**.
**Selesai jika:** semua baris checklist bisa dicentang "Bisa" dengan bukti screenshot; link online dapat diakses.

---

## 9. Catatan untuk laporan
- Kategori **Bantuan AI**: percakapan perancangan ini = contoh prompt & hasilnya.
- Hambatan nyata yang bisa ditulis: tidak punya XAMPP → beralih ke Docker; PHP lokal ternyata versi rentan (PHP2Shell) → harus dikunci ke versi patch; AI perlu diarahkan agar desain tidak generik.
- Pembagian job description mengikuti Fase 3–7 (satu modul per anggota) + Fase 0–2, 8–9 dikerjakan bersama.

---

## 10. Hasil pemeriksaan keamanan (Fase 8, 16 Nov 2026)

### Versi komponen
| Komponen | Versi | Status |
|---|---|---|
| PHP | 8.4.27 | Rilis terbaru 8.4.x; sudah menambal PHP2Shell (CVE-2026-109618/109619). Belum ada rilis 8.4.28. |
| MySQL | 8.4.13 | Lebih baru dari 8.4.12 (patch keamanan Agustus 2026). Port database tidak dibuka ke luar container. |
| Apache | 2.4.69 | Versi disembunyikan (`Server: Apache`). |
| phpMyAdmin | 5.2.3 | Hanya bisa dibuka dari laptop sendiri (127.0.0.1), tidak di-deploy. |

### Pemeriksaan kode (otomatis)
- Semua 17 halaman admin memanggil `wajib_login()`.
- Tidak ada variabel yang ditempel langsung ke SQL; dua tempat yang menyusun SQL (OPAC, Riwayat) hanya memakai nama kolom dari daftar tetap dan angka.
- Semua output teks lewat `e()`; yang tidak lewat `e()` hanya angka, konstanta, atau label dari daftar tetap.
- Tidak ada `eval`, `unserialize`, `exec`, upload file, `ini_set`, atau pergantian `serialize_handler` (syarat CVE-2026-115560).
- Semua perubahan data hanya lewat POST.

### Uji serangan (dinamis)
| Uji | Hasil |
|---|---|
| CSRF: situs lain (127.0.0.1:8099) mengirim form hapus anggota saat petugas login | **Ditolak** — cookie `SameSite=Strict` tidak ikut terkirim. Pembanding dari situs sendiri berhasil, membuktikan form serangan valid. |
| Cookie dibaca JavaScript | Tidak bisa (`HttpOnly`) |
| Session fixation (ID session buatan penyerang) | Ditolak, server membuat ID baru |
| Cookie lama dipakai setelah logout | Tidak berlaku lagi |
| SQL injection di pencarian OPAC & riwayat | Tidak berefek |
| XSS di judul, nama, kata kunci | Tampil sebagai teks |
| Akses langsung `config/`, `includes/`, `database/`, `docker/`, `_form.php`, `.htaccess`, daftar isi folder | 403 |

### Temuan & perbaikan selama Fase 8
1. **Input berbentuk array** (`?q[]=x`) membuat PHP error dan membocorkan path server → diperbaiki di `includes/init.php` (semua input non-teks diubah jadi teks kosong).
2. **Nama pengguna terlalu panjang** menyebabkan error database → ditambah batas 100 karakter.
3. **Password lebih dari 72 karakter** diam-diam dipotong oleh bcrypt → ditambah batas 8–72 karakter.

### Kelemahan yang tersisa (untuk bagian Kelemahan laporan)
- Tidak ada pembatasan jumlah percobaan login.
- Semua admin setara (tidak ada role), tidak ada audit log.
- Jika petugas yang sedang login membuka link/form jahat dari situs lain, serangannya gagal tetapi petugas ikut ter-logout.
- Di hosting, `display_errors` wajib dimatikan (di Docker sengaja dinyalakan untuk belajar).
