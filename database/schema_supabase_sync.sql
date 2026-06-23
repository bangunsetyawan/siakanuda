-- Tabel ALUMNI
CREATE TABLE IF NOT EXISTS alumni (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  nama_lengkap TEXT,
  nisn TEXT,
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
  keterangan_tambahan TEXT
);

-- Tabel MOU
CREATE TABLE IF NOT EXISTS mou (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  mou_no TEXT,
  instansi TEXT,
  alamat TEXT,
  bidang TEXT,
  konsentrasi TEXT,
  kerjasama TEXT
);

-- Tabel KUNJUNGAN
CREATE TABLE IF NOT EXISTS kunjungan (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  tempat TEXT,
  alamat_cp TEXT,
  waktu TEXT,
  jumlah_peserta INTEGER,
  dokumentasi TEXT
);
