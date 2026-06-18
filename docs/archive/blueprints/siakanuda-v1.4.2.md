# 📘 SIAKANUDA — Master Blueprint Teknis (Single Source of Truth)
> **Versi:** 1.4.2 | **Sekolah:** SMK NU Darussalam | **Diperbarui:** 3 Juni 2026

Dokumen ini adalah referensi utama sistem **SIAKANUDA v1.4.2**. Semua model AI dan pengembang wajib merujuk ke dokumen ini sebelum melakukan perubahan logika atau skema.

---

## 1. RINGKASAN ARSITEKTUR (v1.4.2)

SIAKANUDA (Sistem Informasi Akademik SMK NU Darussalam) v1.4.2 menggunakan arsitektur **Single Dashboard Entry Point**:

1. **Dashboard Utama (CodeIgniter 4)** — Berjalan di **Port 8080**. Ini adalah satu-satunya antarmuka yang diakses pengguna (Admin, Guru, Guru BK, Kepala Sekolah, Siswa, Ketua PKL, Anggota PKL) baik via browser PC/HP maupun dari **APK WebView**.
2. **Background Services (Node.js)** — Berjalan di **Port 7860 (Internal/Hidden)**. Tidak diakses oleh pengguna secara langsung. Hanya bertugas untuk:
   - 🤖 WhatsApp Bot (disederhanakan penuh untuk notifikasi & broadcast, tanpa menu chat interaktif kompleks).
   - ⏰ Cron Jobs otomatis (08:30 / 16:00 / 19:00 / 23:59 WIB).
   - 📄 Laporan PDF (kop & tanda tangan premium).
   - 📸 Sinkronisasi foto PKL dari lokal ke Supabase Storage.
3. **Database Hybrid** — Data disimpan secara real-time di **Supabase Cloud (PostgreSQL)** dan disinkronisasikan ke **SQLite3 lokal (`siakanuda.db`)** sebagai fallback offline-first.

---

## 2. STRUKTUR DIREKTORI UTAMA

```
siakanuda/
├── .env                      ← Konfigurasi utama Node.js (Bot & API)
├── .env.example              ← Template .env kosong
├── .gitignore
├── package.json              ← Node.js v18+, ESM ("type": "module"), v1.2.3
├── siswabot.service          ← Systemd service file (WorkingDir: ~/siakanuda)
├── siakanuda.db              ← Database SQLite aktif
├── execution/                ← BACKGROUND SERVICES (Node.js)
│   ├── server.js             ← Entry point Express & Socket.io (Port 7860)
│   ├── bot.js                ← WhatsApp Bot (Notification & Broadcast only)
│   ├── db.js                 ← DB adapter (SQLite lokal + Supabase cloud)
│   ├── cron_jobs.js          ← Penjadwalan pengingat otomatis
│   ├── pdf_generator.js      ← Generator PDF laporan A4 Premium
│   ├── photo_sync.js         ← Daemon sinkronisasi foto offline-first
│   └── supabase-auth.js      ← Adapter auth session WA ke Supabase
├── dashboard/                ← WEB DASHBOARD & USER INTERFACE (CodeIgniter 4)
│   ├── .env                  ← CI4 env (DB path: ../../siakanuda.db, Port 8080)
│   ├── composer.json
│   ├── spark                 ← Spark CLI CI4
│   └── app/
│       ├── Config/           ← Konfigurasi CI4
│       ├── Controllers/      ← Controller login, CRUD guru/siswa/jadwal/absensi/PKL/BK, WhatsApp panel, Kalender Akademik, Prestasi
│       ├── Models/           ← Model SQLite 14 tabel
│       └── Views/            ← Tampilan responsif (Bootstrap) untuk browser & APK WebView
├── data/                     ← Data referensi sekolah (.xlsx)
├── database/                 ← Schema SQL DDL (kalender_akademik.sql, prestasi.sql)
├── directives/               ← Panduan operasional (5 file)
└── uploads/pkl/              ← Foto PKL tersimpan lokal (gitignored)
```

---

## 3. ENVIRONMENT VARIABLES

### Root `.env` (Node.js Background Service) — `siakanuda/.env`
```env
PORT=7860
SCHOOL_NAME=SMK NU Darussalam
TEACHER_PASSWORD=guru123
DASHBOARD_PASSWORD=adminsmknuda
SUPABASE_URL=https://hiajrbcynqpbkxgwcjvj.supabase.co
SUPABASE_KEY=eyJhbGci...  # Service role key
BROADCAST_GROUP_JID=120363425526639587@g.us
GEMINI_API_KEY=AIzaSy...
TESTING_MODE=false
BASE_URL=http://localhost:7860
```

### `dashboard/.env` (CodeIgniter 4 Web) — `siakanuda/dashboard/.env`
```env
# Hubungkan ke SQLite lokal dan arahkan WA API ke port 7860
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'
database.default.DBDriver = SQLite3
database.default.database = ../../siakanuda.db
WA_BOT_URL = 'http://127.0.0.1:7860'
SCHOOL_NAME = 'SMK NU Darussalam'
```

---

## 4. DATABASE SCHEMA (14 Tabel SQLite)

| Tabel | Keterangan |
|---|---|
| `students` | Data siswa (NIS, nama, kelas, password bcrypt) |
| `allowed_numbers` | Data guru/staf (nomor WA, role, password bcrypt) |
| `attendance` | Absensi harian KBM (H/S/I/A) |
| `attendance_kelas` | Rekap absensi per kelas |
| `attendance_pkl` | Laporan PKL harian (jurnal + tautan foto) |
| `violations` | Poin pelanggaran siswa |
| `kelompok_pkl` | Relasi tempat PKL, ketua, anggota, pembimbing |
| `counseling` | Catatan guru BK |
| `schedules` | Jadwal pelajaran per kelas & hari |
| `feedbacks` | Kotak suara/saran siswa |
| `logs` | Log chat WhatsApp bot |
| `sessions` | Sesi auth WA (Baileys) |
| `kalender_akademik` | Detail baris kalender per semester/bulan/pekan |
| `achievements` | Riwayat prestasi akademik/non-akademik siswa |

### Skema Tabel Prestasi: `achievements`
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

---

## 5. Hak Akses 7 Role (v1.4.2)

1. **`admin`**: Akses penuh CRUD semua modul, WhatsApp Panel, hapus data.
2. **`kepsek`**: Akses penuh kecuali menghapus pengguna (Guru/Staf & Siswa) dan input absensi.
3. **`guru` / `guru_mapel`**: Absensi KBM (Write), siswa & jadwal (Read-only), Kelompok PKL bimbingannya, dan CRUD Prestasi Siswa.
4. **`guru_bk`**: Hak akses Guru + CRUD Pelanggaran & Catatan BK, serta CRUD Prestasi Siswa.
5. **`siswa`**: Read-only data absensi personal, pelanggaran personal, jadwal, Catatan BK personal (hanya-lihat), Prestasi personal (hanya-lihat), dan mengirim Kotak Saran.
6. **`ketua_pkl`**: Hak akses Siswa + Absensi & Jurnal kelompok PKL (Write), serta cetak laporan PKL ke PDF.
7. **`anggotapkl`**: Hak akses Siswa + read-only menu/halaman PKL kelompoknya (tanpa submit absensi/jurnal).

---

## 6. CARA MENJALANKAN LOKAL

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
Akses di HP (jaringan Wi-Fi sama): `http://[IP_PC_LOKAL]:8080` (contoh: `http://192.168.1.15:8080`)

### C. Kredensial Login Default
- **Username (No. WA):** `6285334354102` (BANGUN SETYAWAN — Superadmin)
- **Password:** `adminsmknuda` (diambil dari `.env` root)

---

## 7. STATUS OPERASIONAL FITUR

| Fitur | Status | Keterangan |
|---|:---:|---|
| **Pintu Masuk WebView APK** | ✅ CI4 (8080) | APK WebView langsung memuat dashboard CI4. |
| **WhatsApp Bot** | ✅ Notifikasi | Hanya mengirimkan notifikasi absensi, PKL, & broadcast. Tidak ada menu chat interaktif. |
| **Cron Jobs** | ✅ Aktif | Otomatis mengirimkan report harian via bot WhatsApp. |
| **Laporan PDF** | ✅ Aktif | Dibuat oleh Node.js dan diunduh langsung lewat link dashboard CI4. |
| **Kalender Akademik** | ✅ Aktif | Modul CRUD untuk admin dengan tombol simpan di atas & di bawah tabel. Widget ringkasan semester aktif di halaman utama dashboard. |
| **Prestasi Siswa** | ✅ Aktif | Modul baru untuk mencatat peringkat, juara lomba, dsb. Tampil secara personal pada dashboard siswa, dan secara umum untuk admin, guru, dan guru BK. |
| **Jam Real-time** | ✅ Aktif | Menampilkan jam, hari, dan tanggal real-time di bagian atas dashboard utama. |
| **PWA Frontend Lama** | ❌ Deprecated | Folder `public/` diabaikan, semua fungsi dipindah ke CI4. |

---

## 8. APK WEBVIEW (siakanuda-apk)
- APK dibangun menggunakan Java Android WebView standar.
- Konfigurasi URL diarahkan ke **Port 8080** (CodeIgniter 4).
- Ganti IP server menggunakan `GANTI-IP.bat` di folder `siakanuda-apk`, lalu build via `BUILD-APK.bat`.
