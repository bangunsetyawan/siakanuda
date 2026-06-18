-- SIAKANUDA V2 — Supabase PostgreSQL DDL Migrations
-- Copy and run this script in Supabase -> SQL Editor -> New Query -> Run

-- 1. Table: schedules (Jadwal Pelajaran)
CREATE TABLE IF NOT EXISTS schedules (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    day VARCHAR(20) NOT NULL,            -- 'Senin', 'Selasa', dll.
    class_name VARCHAR(50) NOT NULL,     -- '10 TKJ 1', dll.
    subject VARCHAR(100) NOT NULL,       -- Nama Mapel
    teacher_phone VARCHAR(50) NOT NULL,  -- Nomor WA Guru Pengajar (628xxx)
    start_time TIME,
    end_time TIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Table: feedbacks (Kotak Suara Aspirasi)
CREATE TABLE IF NOT EXISTS feedbacks (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sender_name VARCHAR(100) NOT NULL,
    class_name VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    is_public BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Table: kelompok_pkl (Pemetaan DU/DI & Anggota)
CREATE TABLE IF NOT EXISTS kelompok_pkl (
    id SERIAL PRIMARY KEY,
    tempat_pkl VARCHAR(200) NOT NULL,     -- Nama Tempat DU/DI
    ketua_phone VARCHAR(50) NOT NULL,     -- Nomor WA Ketua Kelompok (628xxx)
    anggota TEXT NOT NULL,                -- Comma-separated name/ID (e.g. "Budi Santoso, Andi Wijaya")
    pembimbing_phone VARCHAR(50),         -- Nomor WA Guru Pembimbing PKL
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Table: attendance_pkl (Laporan Presensi PKL Harian)
CREATE TABLE IF NOT EXISTS attendance_pkl (
    id SERIAL PRIMARY KEY,
    date DATE NOT NULL DEFAULT CURRENT_DATE,
    tempat_pkl VARCHAR(200) NOT NULL,
    ketua_phone VARCHAR(50) NOT NULL,
    status_libur BOOLEAN DEFAULT FALSE,
    attendance_data JSONB,                 -- JSON data kehadiran anggota
    photo_url TEXT,                        -- Tautan bukti foto di Supabase Storage
    jurnal_kegiatan TEXT,                  -- Isi Jurnal Kegiatan Harian
    is_takeover BOOLEAN DEFAULT FALSE,     -- Di-takeover guru pembimbing / admin
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT attendance_pkl_unique UNIQUE (date, ketua_phone)
);

-- 5. Table: attendance_kelas (Laporan Presensi Kelas Harian)
CREATE TABLE IF NOT EXISTS attendance_kelas (
    id SERIAL PRIMARY KEY,
    date DATE NOT NULL DEFAULT CURRENT_DATE,
    class_name VARCHAR(50) NOT NULL,
    teacher_phone VARCHAR(50) NOT NULL,
    attendance_data JSONB,                 -- JSON data ketidakhadiran
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT attendance_kelas_unique UNIQUE (date, class_name)
);

-- 6. Update Violations Table (Tambah Kolom Bukti & Tindak Lanjut jika belum ada)
ALTER TABLE violations ADD COLUMN IF NOT EXISTS proof_url TEXT;
ALTER TABLE violations ADD COLUMN IF NOT EXISTS follow_up TEXT;

-- 7. Update User Sessions Table (Context data & last_seen timestamp)
ALTER TABLE user_sessions ADD COLUMN IF NOT EXISTS context TEXT;
ALTER TABLE user_sessions ADD COLUMN IF NOT EXISTS last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- 8. Indeks Kunci untuk Optimasi Kueri
CREATE INDEX IF NOT EXISTS idx_schedules_class_day ON schedules(class_name, day);
CREATE INDEX IF NOT EXISTS idx_schedules_teacher_day ON schedules(teacher_phone, day);
CREATE INDEX IF NOT EXISTS idx_attendance_pkl_date ON attendance_pkl(date);
CREATE INDEX IF NOT EXISTS idx_attendance_kelas_date ON attendance_kelas(date);
CREATE INDEX IF NOT EXISTS idx_kelompok_pkl_ketua ON kelompok_pkl(ketua_phone);
