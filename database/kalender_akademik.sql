CREATE TABLE IF NOT EXISTS kalender_akademik (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tahun_pelajaran TEXT NOT NULL,
    semester TEXT NOT NULL CHECK(semester IN ('Ganjil', 'Genap')),
    nomor INTEGER NOT NULL,
    bulan TEXT NOT NULL,
    total_pekan INTEGER NOT NULL DEFAULT 0,
    pekan_efektif INTEGER NOT NULL DEFAULT 0,
    pekan_tidak_efektif INTEGER NOT NULL DEFAULT 0,
    keterangan TEXT DEFAULT ''
);

-- Seed TP 2025/2026
DELETE FROM kalender_akademik WHERE tahun_pelajaran = '2025/2026';

INSERT INTO kalender_akademik (tahun_pelajaran, semester, nomor, bulan, total_pekan, pekan_efektif, pekan_tidak_efektif, keterangan) VALUES
('2025/2026', 'Ganjil', 1, 'JULI 2025', 3, 2, 1, 'Pekan ke-2: Caracter Building & Sosialisasi Tempat Magang, Simulasi ANBK'),
('2025/2026', 'Ganjil', 2, 'AGUSTUS 2025', 4, 3, 1, 'Pekan ke-3: Kegiatan Agustus'),
('2025/2026', 'Ganjil', 3, 'SEPTEMBER 2025', 4, 3, 1, 'Pekan ke-2: Maulid Nabi Muhammad SAW & STS'),
('2025/2026', 'Ganjil', 4, 'OKTOBER 2025', 5, 5, 0, '-'),
('2025/2026', 'Ganjil', 5, 'NOPEMBER 2025', 4, 4, 0, '-'),
('2025/2026', 'Ganjil', 6, 'DESEMBER 2025', 3, 1, 2, 'Pekan ke-2: PASPekan ke-3: Remidi'),
('2025/2026', 'Genap', 1, 'JANUARI 2026', 4, 4, 0, '-'),
('2025/2026', 'Genap', 2, 'PEBRUARI 2026', 4, 3, 1, 'Pekan ke-3: KPP & Pondok Romadlon '),
('2025/2026', 'Genap', 3, 'MARET 2026', 3, 2, 1, 'Pekan ke-2: STS'),
('2025/2026', 'Genap', 4, 'APRIL 2026', 3, 3, 0, '-'),
('2025/2026', 'Genap', 5, 'MEI 2026', 4, 4, 0, '-'),
('2025/2026', 'Genap', 6, 'JUNI 2026', 3, 2, 1, 'Pekan ke-3: ASAS & REMIDI');
