-- =========================================================
--  Struktur database Sistem Otomasi Perpustakaan
--  Di hosting: buka phpMyAdmin > pilih database > Import file ini,
--  lalu import 02-seed.sql.
-- =========================================================

SET NAMES utf8mb4;

-- Petugas / admin yang bisa login
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,          -- password terenkripsi, bukan password asli
    nama          VARCHAR(100) NOT NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data bibliografi (koleksi buku)
CREATE TABLE buku (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    kode_buku     VARCHAR(20)  NOT NULL UNIQUE,   -- contoh: BK0001, juga isi barcode
    isbn          VARCHAR(20)  NULL,
    judul         VARCHAR(255) NOT NULL,
    pengarang     VARCHAR(150) NOT NULL,
    penerbit      VARCHAR(150) NULL,
    tahun_terbit  VARCHAR(20)  NULL,              -- teks, karena ada "Abad ke-8 SM"
    kategori      VARCHAR(50)  NOT NULL,
    lokasi_rak    VARCHAR(20)  NULL,
    stok_total    INT UNSIGNED NOT NULL DEFAULT 1, -- jumlah eksemplar yang dimiliki
    stok_tersedia INT UNSIGNED NOT NULL DEFAULT 1, -- jumlah yang sedang ada di rak
    link_ebook    VARCHAR(255) NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data anggota perpustakaan
CREATE TABLE anggota (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    no_anggota     VARCHAR(20)  NOT NULL UNIQUE,  -- contoh: AG0001, juga isi barcode kartu
    nama           VARCHAR(100) NOT NULL,
    alamat         TEXT         NULL,
    no_hp          VARCHAR(20)  NULL,
    email          VARCHAR(100) NULL,
    tanggal_daftar DATE         NOT NULL,
    status         ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Transaksi sirkulasi (peminjaman & pengembalian)
CREATE TABLE peminjaman (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    anggota_id      INT  NOT NULL,
    buku_id         INT  NOT NULL,
    petugas_id      INT  NULL,
    tanggal_pinjam  DATE NOT NULL,
    jatuh_tempo     DATE NOT NULL,
    tanggal_kembali DATE NULL,                     -- kosong selama buku belum kembali
    hari_terlambat  INT UNSIGNED NOT NULL DEFAULT 0,
    denda           INT UNSIGNED NOT NULL DEFAULT 0, -- dalam rupiah
    status          ENUM('dipinjam','dikembalikan') NOT NULL DEFAULT 'dipinjam',
    -- Jika buku/anggota dihapus, riwayatnya ikut terhapus
    FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE CASCADE,
    FOREIGN KEY (buku_id)    REFERENCES buku(id)    ON DELETE CASCADE,
    FOREIGN KEY (petugas_id) REFERENCES users(id)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
