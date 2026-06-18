# 📘 SIAKANUDA — Master Blueprint Teknis (Single Source of Truth)
> **Versi:** 1.6.23 | **Sekolah:** SMK NU Darussalam | **Diperbarui:** 6 Juni 2026

Dokumen ini adalah referensi utama sistem **SIAKANUDA v1.6.23**. Semua model AI dan pengembang wajib merujuk ke dokumen ini sebelum melakukan perubahan logika atau skema.

---

## 1. RINGKASAN ARSITEKTUR (v1.6.0)

SIAKANUDA (Sistem Informasi Akademik SMK NU Darussalam) v1.6.17 menggunakan arsitektur **Single Dashboard Entry Point**:

1. **Dashboard Utama (CodeIgniter 4)** — Berjalan di **Port 8080**. Ini adalah satu-satunya antarmuka yang diakses pengguna (Admin, Guru, Guru BK, Kepala Sekolah, Siswa, Ketua PKL, Anggota PKL) baik via browser PC/HP maupun dari **APK WebView**.
2. **Background Services (Node.js)** — Berjalan di **Port 7860 (Internal/Hidden)**. Tidak diakses oleh pengguna secara langsung. Bertugas untuk:
   - 🤖 WhatsApp Bot (notifikasi & broadcast saja, tanpa menu chat interaktif).
   - ⏰ Cron Jobs otomatis (00:30 / 08:30 / 16:00 / 19:00 / 23:59 WIB).
   - 📄 Laporan PDF A4 Premium (kop surat & tanda tangan digital).
   - 📸 Sinkronisasi foto PKL dari lokal ke Supabase Storage.
   - 💾 Backup database SQLite harian otomatis (00:30 WIB).
3. **Database Hybrid** — Data disimpan secara real-time di **Supabase Cloud (PostgreSQL)** dan disinkronisasikan ke **SQLite3 lokal (`siakanuda.db`)** sebagai fallback offline-first.

---

## 2. STRUKTUR DIREKTORI UTAMA

```
siakanuda/
├── .env                      ← Konfigurasi utama Node.js (Bot & API)
├── .env.example              ← Template .env kosong
├── .gitignore
├── package.json              ← Node.js v18+, ESM ("type": "module"), v1.6.17
├── bot.siswa.service         ← Systemd service file (WorkingDir: ~/siakanuda)
├── siakanuda.db              ← Database SQLite aktif (15 tabel)
├── execution/                ← BACKGROUND SERVICES (Node.js)
│   ├── server.js             ← Entry point Express & Socket.io (Port 7860)
│   ├── bot.js                ← WhatsApp Bot (Notification & Broadcast only)
│   ├── db.js                 ← DB adapter (SQLite lokal + Supabase cloud)
│   ├── cron_jobs.js          ← Cron 00:30/08:30/16:00/19:00/23:59 WIB (+ backup DB)
│   ├── pdf_generator.js      ← Generator PDF laporan A4 Premium
│   ├── photo_sync.js         ← Daemon sinkronisasi foto offline-first
│   └── supabase-auth.js      ← Adapter auth session WA ke Supabase
├── dashboard/                ← WEB DASHBOARD & USER INTERFACE (CodeIgniter 4)
│   ├── .env                  ← CI4 env (DB path: ../../siakanuda.db, Port 8080)
│   ├── composer.json
│   ├── spark                 ← Spark CLI CI4
│   └── app/
│       ├── Config/           ← Konfigurasi CI4 (Routes, Filters, Security)
│       ├── Controllers/      ← 15 Controller (Auth, Dashboard, CRUD, PKL, WA Panel, TahunPelajaran)
│       ├── Filters/          ← AuthFilter.php, RoleFilter.php
│       ├── Models/           ← 13 Model SQLite (14 tabel)
│       └── Views/            ← Tampilan responsif (Bootstrap) untuk browser & APK WebView
├── public/                   ← ASSET UMUM
│   ├── logo-smk.png          ← Logo sekolah (dipakai PDF generator)
│   └── bkk/                  ← [STANDALONE] Portal BKK — JANGAN deploy bersama SIAKANUDA
├── data/                     ← Data referensi sekolah (.xlsx)
├── database/                 ← Schema SQL DDL (kalender_akademik.sql, prestasi.sql)
├── directives/               ← Panduan operasional (5 file)
├── backups/                  ← Backup database harian (gitignored)
└── uploads/pkl/              ← Foto PKL tersimpan lokal (gitignored)
```

---

## 3. ENVIRONMENT VARIABLES

### Root `.env` (Node.js Background Service) — `siakanuda/.env`
```env
PORT=7860
SCHOOL_NAME=SMK NU Darussalam
SUPABASE_URL=https://xxx.supabase.co
SUPABASE_KEY=eyJhbGci...           # Service role key
BROADCAST_GROUP_JID=12036...@g.us
GEMINI_API_KEY=AIzaSy...
TESTING_MODE=false
BASE_URL=http://localhost:8080
JWT_SECRET=                        # ← Ditambahkan v1.5.2
ALLOWED_NUMBERS=628xxx,628xxx
# Catatan: TEACHER_PASSWORD & DASHBOARD_PASSWORD dihapus di v1.5.2
#          Semua auth sekarang menggunakan bcrypt dari database.
```

### `dashboard/.env` (CodeIgniter 4 Web) — `siakanuda/dashboard/.env`
```env
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'
database.default.DBDriver = SQLite3
database.default.database = ../../siakanuda.db
WA_BOT_URL = 'http://127.0.0.1:7860'
SCHOOL_NAME = 'SMK NU Darussalam'
```

---

## 4. DATABASE SCHEMA (15 Tabel SQLite)

| Tabel | Keterangan |
|---|---|
| `students` | Data siswa (NIS, nama, kelas, gender, phone, role, password bcrypt) |
| `allowed_numbers` | Data guru/staf (nomor WA, nama, role, lid, active, password bcrypt) |
| `attendance` | Absensi harian KBM (H/S/I/A) per siswa [Mengacu pada tahun_pelajaran_id] |
| `attendance_kelas` | Rekap absensi per kelas per hari [Mengacu pada tahun_pelajaran_id] |
| `attendance_pkl` | Laporan PKL harian (jurnal + tautan foto JSON) [Mengacu pada tahun_pelajaran_id] |
| `kelompok_pkl` | Kelompok PKL: tempat, ketua_phone, anggota, pembimbing [Mengacu pada tahun_pelajaran_id] |
| `violations` | Poin pelanggaran siswa [Mengacu pada tahun_pelajaran_id] |
| `counseling` | Catatan guru BK [Mengacu pada tahun_pelajaran_id] |
| `schedules` | Jadwal pelajaran per kelas & hari |
| `feedbacks` | Kotak suara/saran siswa (anonim untuk non-admin, filter vulgar) [Mengacu pada tahun_pelajaran_id] |
| `logs` | Log chat WhatsApp bot |
| `sessions` | Sesi auth WA (Baileys) |
| `kalender_akademik` | Detail kalender per semester/bulan/pekan/agenda |
| `achievements` | Riwayat prestasi akademik/non-akademik siswa [Mengacu pada tahun_pelajaran_id] |
| `tahun_pelajaran` | Master Tahun Pelajaran (nama, semester, tanggal_mulai/selesai, is_active) |

### Skema Tabel Baru (sejak v1.4.0)

#### `kalender_akademik`
```sql
CREATE TABLE IF NOT EXISTS kalender_akademik (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tahun_pelajaran TEXT NOT NULL,
    semester TEXT NOT NULL,
    nomor INTEGER NOT NULL,
    bulan TEXT NOT NULL,
    pekan TEXT,
    agenda TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

#### `achievements`
```sql
CREATE TABLE IF NOT EXISTS achievements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    date DATE NOT NULL DEFAULT (date('now', 'localtime')),
    category TEXT NOT NULL,
    title TEXT NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);
```

#### `tahun_pelajaran`
```sql
CREATE TABLE IF NOT EXISTS tahun_pelajaran (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    nama            TEXT NOT NULL UNIQUE,
    semester        TEXT NOT NULL DEFAULT 'Ganjil' CHECK(semester IN ('Ganjil', 'Genap')),
    tanggal_mulai   DATE,
    tanggal_selesai DATE,
    is_active       INTEGER NOT NULL DEFAULT 0,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

---

## 5. Hak Akses 7 Role (v1.5.2)

| Role | Akses Dashboard (Port 8080) |
|---|---|
| `admin` | Full CRUD semua modul + WhatsApp Panel + Hapus Data + Message Log |
| `kepsek` | Full CRUD kecuali hapus pengguna (Guru/Staf & Siswa) dan input absensi |
| `guru` | Absensi KBM (Write), Siswa & Jadwal (Read-only), Violations (Read-only/Search), PKL Pembimbing (Edit kelompok bimbingan), CRUD Prestasi |
| `guru_bk` | Hak akses Guru + CRUD Violations + CRUD Catatan BK + CRUD Prestasi + Menu PKL sidebar |
| `siswa` | Read-only personal: absensi, pelanggaran, jadwal, Catatan BK, Prestasi + Kirim Kotak Saran |
| `ketua_pkl` | Hak akses Siswa + Absensi & Jurnal kelompok PKL (Write) + Cetak Laporan PKL (PDF) |
| `anggotapkl` | Hak akses Siswa + Read-only menu/halaman PKL kelompoknya |

---

## 6. ROUTE INVENTORY (CI4)

### Public
| Method | URI | Controller |
|---|---|---|
| GET | `/login` | Auth::login |
| POST | `/login` | Auth::attemptLogin |
| GET | `/logout` | Auth::logout |

### Protected (AuthFilter)
| Method | URI | Controller |
|---|---|---|
| GET | `/`, `/dashboard` | Dashboard::index |
| GET | `/profile` | Auth::profile |
| POST | `/profile/change-password` | Auth::changePassword |
| GET | `/students` | Student::index |
| POST | `/students/create\|update\|delete` | Student CRUD |
| GET | `/students/details/(:num)` | Student::details |
| GET | `/allowed-numbers` | AllowedNumber::index |
| POST | `/allowed-numbers/create\|update\|delete` | AllowedNumber CRUD |
| GET | `/schedules` | Schedule::index |
| POST | `/schedules/create\|update\|delete` | Schedule CRUD |
| GET | `/attendance` | Attendance::index |
| GET | `/attendance/class/(:any)` | Attendance::class |
| POST | `/attendance/save-class` | Attendance::saveClass |
| GET | `/pkl` | Pkl::index |
| POST | `/pkl/submit` | Pkl::submitReport |
| GET | `/pkl/groups` | Pkl::groups |
| POST | `/pkl/groups/create\|update\|delete` | Pkl group CRUD |
| GET | `/pkl/reports` | Pkl::reports |
| GET | `/violations` | Violation::index |
| POST | `/violations/create\|delete` | Violation CRUD |
| GET | `/counseling` | Counseling::index |
| POST | `/counseling/create\|delete` | Counseling CRUD |
| GET | `/prestasi` | Achievement::index |
| POST | `/prestasi/create\|delete` | Achievement CRUD |
| GET | `/feedbacks` | Feedback::index |
| POST | `/feedbacks/create\|toggle\|delete` | Feedback CRUD |
| GET | `/kalender-akademik` | KalenderAkademik::index |
| POST | `/kalender-akademik/save\|delete` | KalenderAkademik CRUD |
| GET | `/logs` | MessageLog::index (admin-only) |

---

## 7. SECURITY HARDENING (v1.5.2)

| Aspek | Implementasi |
|---|---|
| **CSRF Protection** | Global via `Filters.php` (`csrf` + `invalidchars` di `before`), POST-only method filter, token randomized |
| **Server-Side Validation** | `$validationRules` di semua 12 model CI4 + `validate()` di 9 controller |
| **Session Security** | IP binding, `session_regenerate_id()` on login, destroy old session |
| **Auth Hardening** | Hardcoded fallback passwords (`guruhebat`/`guru123`/`adminsmknuda`) dihapus — semua auth via bcrypt DB |
| **Role-based Routing** | `RoleFilter.php` — filter akses di level routing berdasarkan role session |
| **CORS (Node.js)** | Origin locked ke `http://localhost:8080` |
| **SQL Injection** | Column whitelist di `updateStudent()` & `updateKelompokPkl()` |
| **File Upload** | Validasi tipe (JPG/PNG) & ukuran (max 5MB) pada foto PKL |
| **Error Sanitization** | Pesan error generik di production, detail hanya di development |
| **Database Backup** | Cron harian 00:30 WIB (Node.js) + CI4 Spark command `php spark db:backup` |

---

## 8. JADWAL CRON JOBS

| Waktu WIB | Aksi |
|---|---|
| **00:30** | Backup otomatis database SQLite + cleanup backup > 7 hari |
| **08:30** | Pengingat kelas yang belum di-absen oleh Guru |
| **16:00** | Pengingat pengisian jurnal harian PKL untuk Ketua Kelompok |
| **19:00** | Eskalasi laporan absen PKL ke Guru Pembimbing |
| **23:59** | Auto-alpha untuk siswa KBM yang tidak memiliki keterangan absen |

---

## 9. CARA MENJALANKAN LOKAL

### A. WhatsApp Bot / Background API (Port 7860)
```powershell
cd F:\Antigravity\siakanuda
npm run dev        # node --watch execution/server.js
```

### B. CI4 Dashboard (Port 8080)
Jika ingin diakses dari HP di jaringan Wi-Fi yang sama, bind server ke `0.0.0.0`:
```powershell
cd F:\Antigravity\siakanuda\dashboard
C:\xampp\php\php.exe spark serve --host 0.0.0.0 --port 8080
```
Akses di browser PC: `http://localhost:8080`
Akses di HP (jaringan Wi-Fi sama): `http://[IP_PC_LOKAL]:8080`

---

## 10. STATUS OPERASIONAL FITUR

| Fitur | Status | Keterangan |
|---|:---:|---|
| **Dashboard Entry Point** | ✅ Aktif | APK WebView & browser → CI4 (8080) |
| **WhatsApp Bot** | ✅ Aktif | Notifikasi & broadcast saja |
| **Cron Jobs** | ✅ Aktif | 5 jadwal + backup harian |
| **Laporan PDF** | ✅ Aktif | A4 premium via Node.js |
| **Kalender Akademik** | ✅ Aktif | CRUD admin + widget dashboard |
| **Prestasi Siswa** | ✅ Aktif | Universal semua role |
| **Kotak Suara** | ✅ Aktif | Anonim + filter vulgar |
| **Profil & Ganti Password** | ✅ Aktif | Route `/profile` |
| **Jam Real-time** | ✅ Aktif | Widget header dashboard |
| **Security Hardening** | ✅ Aktif | CSRF, validation, session, role filter, backup |
| **WA Panel (Port 7860)** | ✅ Ringan | ~160 baris HTML, QR code + kirim pesan + log + cron trigger |
| **PWA Frontend Lama** | ❌ Deprecated | Dihapus di v1.5.0 (sw.js, manifest.json) |

---

## 11. APK WEBVIEW (siakanuda-apk)

- APK dibangun menggunakan Java Android WebView standar.
- Konfigurasi URL diarahkan ke **Port 8080** (CodeIgniter 4).
- Ganti IP server menggunakan `GANTI-IP.bat` di folder `siakanuda-apk`, lalu build via `BUILD-APK.bat`.

---

## 12. RINGKASAN PERUBAHAN v1.6.8 — v1.6.17

| Versi | Ringkasan |
|---|---|
| **v1.6.8** | Notifikasi Dashboard Pintar (role-based), Import Excel (.xlsx) dengan Live Preview, Login tabbed (Siswa/Guru dropdown), Default password = NISN/No.WA + paksa ganti password, Kolom `tugas_tambahan` di `allowed_numbers`, Hapus auto-alpha cron |
| **v1.6.9** | Cetak Rekap Absensi Bulanan PDF (`printPdf()` di CI4 + API Node.js), Integrasi Kop Surat PDF fisik via `pdf-lib` (`applyKopSurat` helper) |
| **v1.6.10** | Format matriks absensi bulanan (28–31 kolom hari), Margin PDF diselaraskan dengan Kop Surat (78pt kiri, 44pt kanan), Banner Dapodik dipindahkan ke navbar |
| **v1.6.11** | PDF Rekap KBM diubah ke Landscape A4, Highlight & deteksi hari Minggu (merah di header, `#fdf2f2` di sel) |
| **v1.6.12** | Kop Surat Landscape (`KOP_LANDSCAPE_PATH`), `applyKopSurat` auto-deteksi orientasi, `.gitignore` + `siakanuda-apk/` |
| **v1.6.13** | Auto-submit "Hadir Semua" + redirect dengan parameter tanggal, Perlebar tabel PDF 680pt, Hapus tanda tangan Kepala Sekolah di PDF |
| **v1.6.14** | Kolom NIS → NISN (lebar 60pt), Sinkronisasi margin landscape = portrait (78pt), `tableWidth` = 720, `namaWidth` = 180 |
| **v1.6.15** | PDF Hitam Putih (hapus zebra striping), Margin 1.27cm (36pt) semua sisi, `tableWidth` = 770, Metadata info (TP, Wali Kelas, Pengunduh) di atas tabel |
| **v1.6.16** | Header tabel 2 baris (merged cells "Periode Bulan" & "Rekap"), Overlay logo resolusi tinggi (persegi putih + PNG overlay), Center-align judul |
| **v1.6.17** | Presisi koordinat overlay logo (dari template PDF), Presisi jarak judul ke Kop (Title y=118, Table y=193), Wrap limit 540 = 18 baris/halaman |
| **v1.6.18** | Rekap Mingguan PKL & Kompresi Foto Otomatis (sharp) |
| **v1.6.19** | UI Manajemen Peran Siswa & Otomatisasi Peran PKL |
| **v1.6.20** | Widget Absensi PKL di dashboard utama ketua_pkl disederhanakan menjadi status card premium |
| **v1.6.21** | Tombol Simpan Cepat libur PKL (student_report.php) & Optimasi broadcast WA libur (server.js) |

### File Utama yang Berubah (v1.6.8–v1.6.21)

| File | Perubahan |
|---|---|
| `execution/pdf_generator.js` | Seluruh evolusi PDF generator (layout, margin, header, logo overlay, metadata) |
| `execution/server.js` | API endpoint `GET /api/attendance-report/pdf/:className` + metadata query string |
| `dashboard/app/Controllers/Attendance.php` | Method `printPdf()`, integrasi metadata (wali kelas, pengunduh) |
| `dashboard/app/Views/attendance/index.php` | Tombol cetak PDF + modal pilih Bulan/Tahun |
| `dashboard/app/Views/attendance/class.php` | Auto-submit `markAllHadir()` |
| `dashboard/app/Controllers/Dashboard.php` | Notifikasi pintar role-based |
| `dashboard/app/Views/dashboard/index.php` | Widget notifikasi tindakan |
| `dashboard/app/Controllers/Auth.php` | Login tabbed, paksa ganti password |
| `dashboard/app/Views/pkl/student_report.php` | Form dinamis Hadir/Sakit/Izin/Alpha |
| `execution/bot.js` | Format WA broadcast (pembimbing, jam WIB, ringkasan) |
