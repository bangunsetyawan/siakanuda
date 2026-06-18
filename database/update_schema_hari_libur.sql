-- Tabel hari_libur: daftar hari libur (nasional, sekolah, cuti bersama)
CREATE TABLE IF NOT EXISTS hari_libur (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    date DATE NOT NULL UNIQUE,
    reason VARCHAR(200) NOT NULL,
    type VARCHAR(50) DEFAULT 'sekolah',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Supabase/PostgreSQL version (optional, run separately)
-- CREATE TABLE IF NOT EXISTS hari_libur (
--     id SERIAL PRIMARY KEY,
--     date DATE NOT NULL UNIQUE,
--     reason VARCHAR(200) NOT NULL,
--     type VARCHAR(50) DEFAULT 'sekolah',
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );

-- Index untuk query berdasarkan tanggal
CREATE INDEX IF NOT EXISTS idx_hari_libur_date ON hari_libur(date);
