-- ============================================================================
-- SIAKANUDA v1.6.0 — Tahun Pelajaran (Academic Year)
-- ============================================================================
-- Tabel master tahun pelajaran. Hanya 1 baris yang boleh is_active = 1.
-- Semua data transaksional (attendance, violations, counseling, feedbacks,
-- kelompok_pkl, attendance_pkl, attendance_kelas, achievements) merujuk ke
-- kolom tahun_pelajaran_id (FK ke tabel ini).
-- ============================================================================

CREATE TABLE IF NOT EXISTS tahun_pelajaran (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    nama            TEXT NOT NULL UNIQUE,        -- e.g. '2025/2026'
    semester        TEXT NOT NULL DEFAULT 'Ganjil' CHECK(semester IN ('Ganjil', 'Genap')),
    tanggal_mulai   DATE,
    tanggal_selesai DATE,
    is_active       INTEGER NOT NULL DEFAULT 0,  -- hanya 1 baris yang boleh = 1
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Seed data default
INSERT OR IGNORE INTO tahun_pelajaran (nama, semester, tanggal_mulai, tanggal_selesai, is_active)
VALUES ('2025/2026', 'Genap', '2026-01-06', '2026-06-21', 1);

-- ============================================================================
-- MIGRASI: Tambah kolom tahun_pelajaran_id ke 8 tabel transaksional
-- Jalankan satu per satu (ALTER TABLE di SQLite tidak mendukung IF NOT EXISTS)
-- ============================================================================

-- ALTER TABLE attendance ADD COLUMN tahun_pelajaran_id INTEGER DEFAULT 1;
-- ALTER TABLE attendance_kelas ADD COLUMN tahun_pelajaran_id INTEGER DEFAULT 1;
-- ALTER TABLE attendance_pkl ADD COLUMN tahun_pelajaran_id INTEGER DEFAULT 1;
-- ALTER TABLE kelompok_pkl ADD COLUMN tahun_pelajaran_id INTEGER DEFAULT 1;
-- ALTER TABLE violations ADD COLUMN tahun_pelajaran_id INTEGER DEFAULT 1;
-- ALTER TABLE counseling ADD COLUMN tahun_pelajaran_id INTEGER DEFAULT 1;
-- ALTER TABLE achievements ADD COLUMN tahun_pelajaran_id INTEGER DEFAULT 1;
-- ALTER TABLE feedbacks ADD COLUMN tahun_pelajaran_id INTEGER DEFAULT 1;
