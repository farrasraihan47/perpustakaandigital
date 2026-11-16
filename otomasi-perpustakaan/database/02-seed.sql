-- =========================================================
--  Data contoh untuk demo
--  Login awal:  username = admin   password = admin123
--  (segera ganti lewat menu Pengguna > Ganti Password)
-- =========================================================

SET NAMES utf8mb4;

INSERT INTO users (username, password_hash, nama) VALUES
('admin', '$2y$12$/dGX.1ut4o1752epfXKKEOLaX/3W5wgsDi8WTmhakFkANNYbjBXSO', 'Petugas Sirkulasi');

-- Koleksi diambil dari folder ebook/ di repositori perpustakaan digital
INSERT INTO buku (kode_buku, isbn, judul, pengarang, penerbit, tahun_terbit, kategori, lokasi_rak, stok_total, stok_tersedia, link_ebook) VALUES
('BK0001', NULL,            'Cog',                                        'Farras',               'Mandiri',               '2026',       'Karya Mahasiswa', 'A-01', 2, 2, 'https://farrasraihan47.github.io/perpustakaandigital/ebook/cog.pdf'),
('BK0002', NULL,            'Panduan Memahami Lisensi Creative Commons',  'Nurul Asiah',          'Creative Commons ID',   '2025',       'Referensi',       'B-02', 3, 3, 'https://farrasraihan47.github.io/perpustakaandigital/ebook/Panduan%20Memahami%20Lisensi%20Creative%20Commons.pdf'),
('BK0003', '9780553213690', 'The Metamorphosis',                          'Franz Kafka',          'Bantam Classics',       '1915',       'Fiksi Klasik',    'C-03', 2, 2, 'https://farrasraihan47.github.io/perpustakaandigital/ebook/the%20metamorphosis.pdf'),
('BK0004', '9780140268867', 'The Odyssey',                                'Homer',                'Penguin Classics',      'Abad ke-8 SM','Fiksi Klasik',   'C-01', 2, 2, 'https://farrasraihan47.github.io/perpustakaandigital/ebook/the%20odyssey.pdf'),
('BK0005', '9780147514011', 'Little Women',                               'Louisa May Alcott',    'Puffin Books',          '1868',       'Fiksi Klasik',    'C-05', 2, 1, 'https://farrasraihan47.github.io/perpustakaandigital/ebook/little%20women.pdf'),
('BK0006', '9780141439563', 'Great Expectations',                         'Charles Dickens',      'Penguin Classics',      '1861',       'Fiksi Klasik',    'C-02', 2, 2, 'https://farrasraihan47.github.io/perpustakaandigital/ebook/great%20expectations.pdf'),
('BK0007', '9780141439846', 'Dracula',                                    'Bram Stoker',          'Penguin Classics',      '1897',       'Fiksi Klasik',    'C-06', 3, 2, 'https://farrasraihan47.github.io/perpustakaandigital/ebook/dracula.pdf'),
('BK0008', '9780143035008', 'Anna Karenina',                              'Leo Tolstoy',          'Penguin Classics',      '1878',       'Fiksi Klasik',    'C-04', 1, 1, 'https://farrasraihan47.github.io/perpustakaandigital/ebook/anna%20karenina.pdf'),
('BK0009', '9780451524935', '1984',                                       'George Orwell',        'Signet Classics',       '1949',       'Fiksi Klasik',    'C-07', 3, 2, 'https://farrasraihan47.github.io/perpustakaandigital/ebook/1984.pdf'),
('BK0010', '9780141441146', 'Jane Eyre',                                  'Charlotte Brontë',     'Penguin Classics',      '1847',       'Fiksi Klasik',    'C-08', 1, 0, 'https://farrasraihan47.github.io/perpustakaandigital/ebook/jane%20eyre.pdf'),
('BK0011', NULL,            'Ringkasan UGC',                              'Farras Raihan ZA',     'Mandiri',               '2026',       'Karya Mahasiswa', 'A-02', 1, 1, 'https://farrasraihan47.github.io/perpustakaandigital/ebook/Ringkasan%20UGC.pdf');

INSERT INTO anggota (no_anggota, nama, alamat, no_hp, email, tanggal_daftar, status) VALUES
('AG0001', 'Rani Puspitasari',  'Jl. Prof. Soedarto No. 13, Tembalang', '081234560001', 'rani@contoh.id',   DATE_SUB(CURDATE(), INTERVAL 120 DAY), 'aktif'),
('AG0002', 'Dimas Aryo Wibowo', 'Jl. Banjarsari Raya No. 7, Semarang',  '081234560002', 'dimas@contoh.id',  DATE_SUB(CURDATE(), INTERVAL 90 DAY),  'aktif'),
('AG0003', 'Siti Nurhaliza',    'Jl. Ngesrep Timur V No. 21, Semarang', '081234560003', 'siti@contoh.id',   DATE_SUB(CURDATE(), INTERVAL 60 DAY),  'aktif'),
('AG0004', 'Bagas Pratama',     'Jl. Mulawarman Selatan No. 4',         '081234560004', 'bagas@contoh.id',  DATE_SUB(CURDATE(), INTERVAL 30 DAY),  'aktif'),
('AG0005', 'Laras Kinanti',     'Jl. Sirojudin No. 9, Tembalang',       '081234560005', 'laras@contoh.id',  DATE_SUB(CURDATE(), INTERVAL 200 DAY), 'nonaktif');

-- Peminjaman contoh. stok_tersedia di atas sudah dikurangi untuk yang masih dipinjam:
-- Little Women (BK0005), Dracula (BK0007), 1984 (BK0009), Jane Eyre (BK0010)
INSERT INTO peminjaman (anggota_id, buku_id, petugas_id, tanggal_pinjam, jatuh_tempo, tanggal_kembali, hari_terlambat, denda, status) VALUES
-- masih dipinjam, belum jatuh tempo
(1, 5,  1, DATE_SUB(CURDATE(), INTERVAL 3 DAY),  DATE_ADD(CURDATE(), INTERVAL 4 DAY),  NULL, 0, 0, 'dipinjam'),
(2, 9,  1, DATE_SUB(CURDATE(), INTERVAL 5 DAY),  DATE_ADD(CURDATE(), INTERVAL 2 DAY),  NULL, 0, 0, 'dipinjam'),
-- masih dipinjam, SUDAH TERLAMBAT (untuk demo denda)
(3, 7,  1, DATE_SUB(CURDATE(), INTERVAL 12 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY),  NULL, 0, 0, 'dipinjam'),
(4, 10, 1, DATE_SUB(CURDATE(), INTERVAL 9 DAY),  DATE_SUB(CURDATE(), INTERVAL 2 DAY),  NULL, 0, 0, 'dipinjam'),
-- sudah dikembalikan
(1, 3,  1, DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_SUB(CURDATE(), INTERVAL 23 DAY), DATE_SUB(CURDATE(), INTERVAL 24 DAY), 0, 0, 'dikembalikan'),
(2, 6,  1, DATE_SUB(CURDATE(), INTERVAL 25 DAY), DATE_SUB(CURDATE(), INTERVAL 18 DAY), DATE_SUB(CURDATE(), INTERVAL 15 DAY), 3, 3000, 'dikembalikan');
