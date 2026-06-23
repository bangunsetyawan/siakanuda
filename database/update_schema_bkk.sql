-- Tabel BKK ALUMNI (Tracer Study)
CREATE TABLE IF NOT EXISTS bkk_alumni (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nama_lengkap TEXT NOT NULL,
    nisn TEXT UNIQUE,
    nik TEXT,
    jenis_kelamin TEXT,
    tempat_lahir TEXT,
    tanggal_lahir TEXT,
    agama TEXT,
    kompetensi_keahlian TEXT,
    tahun_lulus TEXT,
    alamat TEXT,
    kota_kab TEXT,
    no_hp_wa TEXT,
    email_aktif TEXT,
    status_utama TEXT,
    nama_tempat_kerja TEXT,
    jabatan_pekerjaan TEXT,
    sektor_usaha_bidang TEXT,
    lokasi_kerja_kota TEXT,
    keselarasan_jurusan TEXT,
    rentang_pendapatan TEXT,
    keterangan_tambahan TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabel BKK MOU
CREATE TABLE IF NOT EXISTS bkk_mou (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    no_mou TEXT,
    iduka_instansi TEXT NOT NULL,
    alamat TEXT,
    bidang TEXT,
    konsentrasi_jurusan TEXT,
    bentuk_kerjasama TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabel BKK KUNJUNGAN INDUSTRI
CREATE TABLE IF NOT EXISTS bkk_kunjungan (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tempat_kunjungan TEXT NOT NULL,
    alamat_contact_person TEXT,
    waktu TEXT,
    jumlah_peserta INTEGER,
    dokumentasi_link TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
