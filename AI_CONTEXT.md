# 🤖 AI_CONTEXT.md — SIAKANUDA
> **Baca file ini PERTAMA sebelum membuka file apapun.**
> Dirancang untuk semua AI model (Gemini, Claude, GPT, dll) di semua PC/agent.
> File ini adalah pengganti scanning seluruh folder — hemat token secara drastis.
> **Terakhir diupdate:** 18 Juni 2026 — v1.11.1 (Sinkronisasi versi & dokumentasi).

> ⚠️ **SSD Portabel** — Proyek ini berada di SSD portabel. Drive letter bisa berubah (E:\, F:\, G:\).
> Gunakan path relatif. Root proyek = folder tempat file ini berada.



---

## ⚡ TL;DR (Baca 30 detik, paham segalanya)

**SIAKANUDA** = Sistem Informasi Akademik SMK NU Darussalam.
Aplikasi sekolah berbasis **WhatsApp Bot + Web Dashboard + PWA**.
Working directory aktif: `siakanuda/` — root proyek (SSD portabel exFAT, drive letter bisa berubah).

Arsitektur **v1.11.1**:
1. **Satu pintu masuk**: Web Dashboard di **Port 8080** (CodeIgniter 4). Semua akses user (Admin, Guru, Siswa) dan PWA langsung menuju ke sini.
2. **Background Service**: WhatsApp Bot + API di **Port 7860** (Node.js). Berjalan diam-diam di belakang layar untuk notifikasi/broadcast, pdf generator, sync Supabase, dan cron jobs. Panel ringan hanya untuk manajemen koneksi WA (QR code, kirim pesan, log, cron trigger).

---

## 📦 Tech Stack

| Komponen | Teknologi | Port | Keterangan |
|---|---|---|---|
| Web Dashboard | CodeIgniter 4 (PHP) | **8080** | **Entry Point Utama** untuk semua user & PWA Mobile. |
| Background Service | Node.js 18+ | **7860** | WhatsApp Bot (notifikasi saja), Cron Jobs, PDF Generator, Sync. |
| Database utama | Supabase Cloud PostgreSQL | cloud | Cloud backend. |
| Database lokal | SQLite (`siakanuda.db`) | lokal | SQLite lokal disinkronisasikan offline-first. |
| WhatsApp Client | Baileys | — | Menghubungkan bot dengan nomor WA sekolah. |
| AI Gateway | 9Router (OpenAI-Compatible) | 20128 | Auto-fallback AI parser (Gemini, Claude, dll) tanpa vendor lock-in. |

---

## 🗺️ Peta Folder

```
./                             ← ROOT PROYEK (SSD portabel, drive letter bisa berubah)
├── AI_CONTEXT.md              ← FILE INI (Single Source of Truth)
├── AGENTS.md                  ← Aturan untuk AI agent
├── .env                       ← Credentials (JANGAN expose!)
├── .env.example               ← Template .env (aman dibaca)
├── .gitignore
├── package.json               ← version: 1.16.0, type: module
├── siakanuda.db               ← SQLite 16 tabel (data lokal)
├── bot.siswa.service          ← Systemd file untuk Debian
│
├── execution/                 ← BACKGROUND NODE.JS SERVICE
│   ├── server.js              ← Entry point Express & Socket.io (Port 7860) + routes
│   ├── bot.js                 ← WhatsApp Bot (Notification & Broadcast only)
│   ├── db.js                  ← DB adapter (SQLite + Supabase)
│   ├── cron_jobs.js           ← Cron 00:30/08:30/16:00/19:00/23:59 WIB (+ backup DB)
│   ├── photo_sync.js          ← Daemon sync foto offline → Supabase
│   └── supabase-auth.js       ← Auth session WA ke Supabase
│
├── public/                    ← ASSET UMUM
│   ├── logo-smk.png           ← Logo sekolah (dipakai PDF generator)
│   └── bkk/                   ← [STANDALONE] Portal BKK Tracer Study Alumni
│                                HTML+Supabase, auth & DB terpisah dari SIAKANUDA.
│                                Folder ini HANYA workspace edit lokal.
│                                Deploy terpisah ke: github.io & smknudarussalam.sch.id/bkk
│                                JANGAN deploy bersama SIAKANUDA.
│
├── dashboard/                 ← CODEIGNITER 4 DASHBOARD (Port 8080)
│   ├── .env                   ← CI4 config (DB path relatif ke siakanuda.db)
│   ├── composer.json
│   ├── spark                  ← CLI CI4
│   └── app/
│       ├── Config/            ← Konfigurasi CI4
│       ├── Controllers/       ← 19 controller (Auth, Dashboard, Student, PKL, WA Panel, dll)
│       ├── Models/            ← Model SQLite 16 tabel
│       └── Views/             ← Tampilan responsive (Bootstrap) untuk browser & PWA Mobile
│
├── directives/                ← SOP OPERASIONAL
│   ├── 00-prompt-templates.md
│   ├── 01-bot-development.md
│   ├── 02-database-development.md
│   ├── 03-deployment-guide.md
│   └── 04-ai-usage-guide.md
│
├── database/                  ← SCHEMA SQL DDL
├── data/                      ← Data referensi sekolah (.xlsx)
├── sessions/                  ← Auth WhatsApp Baileys (gitignored)
├── uploads/pkl/               ← Foto PKL lokal (gitignored)
├── backups/                   ← Backup database harian (gitignored)
│
└── docs/                      ← DOKUMENTASI UTAMA
    └── archive/               ← FOLDER ARSIP (Diabaikan AI agar hemat token)
        ├── README.md      
        ├── ROADMAP.md     
        ├── DESIGN.md      
        ├── STATUS_FITUR.md
        ├── PROMPT_TEMPLATE.md
        ├── debian_setup_guide.md 
        └── siakanuda-v1.x.x.md   ← Blueprint lama
```

---

## 🗄️ Database Schema (16 Tabel SQLite)

| Tabel | Keterangan |
|---|---|
| `students` | Siswa: NIS, nama, kelas, gender, phone, role, password bcrypt |
| `allowed_numbers` | Guru/staf: phone, nama, role, lid (WA LID), active, password bcrypt |
| `attendance` | Absensi KBM harian (H/S/I/A) per siswa |
| `attendance_kelas` | Rekap absensi per kelas per hari |
| `attendance_pkl` | Laporan PKL: absensi + jurnal JSON + foto URL JSON |
| `kelompok_pkl` | Kelompok PKL: tempat, ketua_phone, anggota, pembimbing |
| `violations` | Poin pelanggaran siswa |
| `counseling` | Catatan guru BK |
| `schedules` | Jadwal pelajaran per hari/kelas/mapel |
| `feedbacks` | Kotak suara/aspirasi siswa |
| `logs` | Log chat WhatsApp bot |
| `sessions` | Sesi auth WA (Baileys) |
| `kalender_akademik` | Kalender Akademik: tahun pelajaran, semester, nomor, bulan, detail pekan & agenda |
| `achievements` | Prestasi Siswa: prestasi akademik, non-akademik, peringkat kelas, lomba, dsb |
| `tahun_pelajaran` | Tahun Pelajaran: nama (e.g. '2025/2026'), semester (Ganjil/Genap), tanggal_mulai, tanggal_selesai, is_active |

---

## 👥 Role & Akses

| Role | Akses Dashboard (Port 8080) |
|---|---|
| `admin` | Full CRUD + Kelola WhatsApp Panel + Hapus Data (Full Access) + PKL Takeover |
| `kepsek` | Full CRUD kecuali Hapus Pengguna (Guru/Staf & Siswa) + PKL Takeover |
| `guru` | Absensi KBM (Write), Jadwal & Siswa (Read-only), Violations (Read-only/Search), PKL Pembimbing (Edit own guided groups + PKL Takeover) |
| `guru_bk` | Akses Guru + CRUD Violations + CRUD Catatan BK + PKL Takeover |
| `siswa` | Read-only personal recap, schedules, personal violations, Kotak Saran |
| `ketua_pkl` | Akses Siswa + Absensi Kelompok PKL (Write) + Cetak Laporan PKL PDF |
| `anggotapkl` | Akses Siswa + Laporan PKL (Read-only, no submit, no printing PDF) |

---

## ▶️ Cara Menjalankan

```powershell
# === 1. WhatsApp Bot / Background API (Port 7860) ===
# dari root proyek (folder siakanuda)
npm run dev          # node --watch execution/server.js

# === 2. CodeIgniter 4 Dashboard (Port 8080) ===
cd dashboard
php spark serve
```

---

## 🚨 Aturan Wajib untuk AI Agent

1. **JANGAN jalankan `npm install` atau `composer install`** tanpa konfirmasi user.
2. **JANGAN expose atau commit file `.env`** dalam kondisi apapun.
3. **Pintu masuk utama user adalah CI4 (8080)**, PWA di port 7860 sudah tidak dipakai.
4. **WhatsApp bot hanya berjalan di background** untuk notifikasi otomatis dan broadcast API dari CI4. Jangan tambahkan menu chat interaktif state-machine yang rumit.
5. **Update `docs/archive/siakanuda-v1.8.6.md`** jika ada perubahan skema database atau arsitektur dasar.

---

## 🔒 Security Hardening (v1.5.2)

| Komponen | Status |
|---|---|
| CSRF Protection | ✅ Aktif global via `Filters.php`, token randomized |
| Server-Side Validation | ✅ `$validationRules` di semua model + `validate()` di controller |
| Session Security | ✅ IP binding, regenerate on login, destroy old session |
| Auth Hardening | ✅ Hardcoded fallback passwords dihapus |
| Role Filter | ✅ `RoleFilter.php` — role-based access di level routing |
| CORS (Node.js) | ✅ Locked ke `localhost:8080` |
| SQL Injection | ✅ Column whitelist di `updateStudent()` & `updateKelompokPkl()` |
| File Upload | ✅ Validasi tipe (JPG/PNG) & ukuran (max 5MB) |
| Error Sanitization | ✅ Error message generik di production |
| Database Backup | ✅ Cron harian 00:30 WIB + CI4 Spark command `db:backup` |

---

## 🪵 Log Perubahan Terbaru (Recent Changes Log)

### **v1.9.0** (9 Jun 2026) - Optimasi Client-Side & Cloud Offloading
1. **Optimasi Upload Foto PKL (Client-Side Compression)**:
   - Menambahkan library `browser-image-compression` via CDN di [student_report.php](file:///./dashboard/app/Views/pkl/student_report.php).
   - Mencegat (*intercept*) *submit* form untuk mengompres foto secara asinkron di *client-side* (maks 500KB, max-width/height 1280px) sebelum diunggah.
   - Menambahkan status *loading spinner* pada tombol untuk UX yang lebih baik dan mencegah *double submit*.
2. **Offload Pembuatan PDF ke Google Apps Script (GAS)**:
   - Membuat *script* GAS kustom untuk mengonversi HTML menjadi PDF dan menyimpannya di Google Drive.
   - Memodifikasi [server.js](file:///./execution/server.js) untuk mengirim payload JSON/HTML ke `GAS_PDF_URL` (didefinisikan di `.env`).
   - Membuat file klien baru [gas_pdf_client.js](file:///./execution/gas_pdf_client.js) untuk memproses data dari database SQLite lokal menjadi *string* HTML laporan PKL harian yang terstruktur dengan CSS *inline*.
   - Mengalihkan endpoint unduhan PDF PKL harian agar otomatis me-*redirect* browser pengguna ke tautan Google Drive yang dihasilkan GAS.

### **v1.8.8** (7 Jun 2026) - Pembersihan Konfigurasi AI di .env
1. **Pembersihan File `.env`**:
   - Menghapus variabel lingkungan `NINEROUTER_URL`, `NINEROUTER_KEY`, dan `NINEROUTER_MODEL` dari file `.env` dan `.env.example`.
   - Hal ini dilakukan karena WhatsApp Bot SIAKANUDA pada mode produksi berjalan secara murni tanpa AI (fitur chatbot pintar dinonaktifkan/hanya menggunakan log dan broadcast notifikasi). Konfigurasi AI Gateway (9Router) kini hanya digunakan secara eksklusif pada sisi development (IDE/Antigravity Agent) melalui MITM Proxy.

### **v1.8.7** (7 Jun 2026) - Pembersihan Workspace & Optimasi AI Context
1. **Pembersihan Root Directory**:
   - Memindahkan seluruh file `.md` usang (`README.md`, `STATUS_FITUR.md`, cetak biru `siakanuda-v1.x.x.md`, dsb.) ke dalam folder `docs/archive/`.
   - Menjadikan `AI_CONTEXT.md` sebagai satu-satunya *Single Source of Truth* untuk menghemat konsumsi token secara masif saat agent AI memindai proyek (Vibe Coding Optimization).
   - Menyesuaikan peta direktori di dokumentasi.

### **v1.8.6** (7 Jun 2026) - Integrasi 9Router AI Gateway
1. **Refactor AI Parser Node.js**:
   - Mengganti pemanggilan API `@google/generative-ai` langsung dengan `fetch` ke endpoint REST OpenAI-compatible bawaan 9Router (`http://localhost:20128/v1/chat/completions`).
   - Menyediakan fitur *auto-fallback* jika kuota model utama habis, *load balancing*, dan menghilangkan ikatan vendor (vendor lock-in) pada satu spesifik AI.
   - Mengubah environment variabel dari `GEMINI_API_KEY` menjadi `NINEROUTER_URL` dan `NINEROUTER_KEY`.

### **v1.8.5** (7 Jun 2026) - Auto-grouping Import PKL per Siswa
1. **Peningkatan Import & Parser**:
   - Mendukung format import kelompok PKL per siswa (satu baris per siswa dengan kolom `Nama` dan `Tempat Pkl`).
   - Sistem otomatis melakukan grouping siswa dengan `Tempat Pkl` yang sama menjadi satu kelompok PKL.
   - Tetap mendukung format per kelompok (satu baris per kelompok dengan kolom `tempat_pkl` dan `anggota`).
   - Memperbarui tombol unduh template agar mengunduh format template "Per Siswa" secara dinamis.
2. **Pencegahan Error Database**:
   - Memastikan `ketua_phone` di-set ke string kosong `""` alih-alih `null` saat ketua dikosongkan guna menghindari SQLite database constraint error.
   - Menambahkan pengaman `index()` PKL agar siswa tidak dapat mengakses menu laporan PKL jika ketua kelompoknya belum ditentukan oleh Admin (menghindari cross-group leakage data).

### **v1.8.4** (7 Jun 2026) - Import Kelompok PKL via Excel
1. **Bulk Import & Template**:
   - Menambahkan fitur import data kelompok PKL secara massal melalui file Excel (`.xlsx`) di menu Kelompok PKL [groups.php](file:///./dashboard/app/Views/pkl/groups.php).
   - Menyediakan tombol download template Excel yang dibuat dinamis via client-side SheetJS.
   - Sesuai permintaan, ketua kelompok PKL bersifat opsional (boleh dikosongkan saat import maupun input manual).
2. **Backend Route & Processing**:
   - Menambahkan POST route `/pkl/groups/import` di [Routes.php](file:///./dashboard/app/Config/Routes.php).
   - Mengimplementasikan method `importGroups()` di [Pkl.php](file:///./dashboard/app/Controllers/Pkl.php) yang melakukan pembersihan nomor, validasi kualifikasi kelompok, insert/update DU/DI, dan sinkronisasi otomatis peran (`role`) siswa.

### **v1.8.3** (7 Jun 2026) - Pembatasan Kelompok PKL Khusus Kelas XII
1. **Penyaringan Student Picker**:
   - Membatasi kueri `$students` di `groups()` pada [Pkl.php](file:///./dashboard/app/Controllers/Pkl.php) agar hanya menyaring siswa aktif yang duduk di kelas XII (menggunakan query `like('class', 'XII', 'after')`).
2. **Otomatisasi Peran PKL & Defaulting**:
   - Memperbarui [StudentModel.php](file:///./dashboard/app/Models/StudentModel.php) method `syncPklRoles()` agar menetapkan default role `anggotapkl` kepada siswa kelas XII (sebelumnya XI).
   - Memperbarui [Student.php](file:///./dashboard/app/Controllers/Student.php) pada method `create()`, `update()`, dan `import()` untuk mengalihkan pemetaan default role PKL dari kelas XI ke kelas XII.

### **v1.8.2** (7 Jun 2026) - Koneksi Kalender Akademik ke Tahun Pelajaran Master
1. **Daftar Tahun & Default Selected**:
   - Memodifikasi [KalenderAkademik.php](file:///./dashboard/app/Controllers/KalenderAkademik.php) agar mengambil daftar tahun pelajaran dan tahun pelajaran aktif/default langsung dari tabel master `tahun_pelajaran` alih-alih `kalender_akademik`.
   - Hal ini memastikan bahwa pada tahun ajaran baru, admin dipaksa menginput/mengunggah ulang kalender pendidikan aktif karena datanya kosong untuk tahun ajaran baru tersebut.

### **v1.8.1** (7 Jun 2026) - Penyederhanaan Tahun Pelajaran Tanpa Semester
1. **Penyederhanaan Form & Tabel TP**:
   - Menghapus input dropdown `<select name="semester">` dari Form Tambah/Edit dan kolom Semester dari tabel daftar Tahun Pelajaran di [index.php (tahun_pelajaran)](file:///./dashboard/app/Views/tahun_pelajaran/index.php).
   - Menyetel default `'semester' => 'Ganjil'` pada `create()` dan `update()` di [TahunPelajaran.php](file:///./dashboard/app/Controllers/TahunPelajaran.php) untuk memenuhi SQLite constraint secara aman.
2. **Konsolidasi Kalender Akademik setahun penuh**:
   - Mengambil seluruh kalender (Ganjil & Genap) dari database di [Dashboard.php](file:///./dashboard/app/Controllers/Dashboard.php) untuk TP yang aktif.
   - Memperbarui [index.php (dashboard)](file:///./dashboard/app/Views/dashboard/index.php) untuk menampilkan kalender setahun penuh dengan label semester yang jelas.
3. **Penyederhanaan Tampilan Navbar & PDF**:
   - Menghapus string semester dari navbar atas [template.php (layouts)](file:///./dashboard/app/Views/layouts/template.php).
   - Menghapus display semester dari query parameter `tp` di [Attendance.php](file:///./dashboard/app/Controllers/Attendance.php).

### **v1.8.0** (7 Jun 2026) - Penyempurnaan Alur Transisi Tahun Ajaran Baru
1. **Database Schema (`is_active` Column)**:
   - Menambahkan kolom `is_active` ke tabel `students` di [db.js](file:///./execution/db.js) (CREATE TABLE & ALTER TABLE) dan [StudentModel.php](file:///./dashboard/app/Models/StudentModel.php).
2. **Fitur Download Data Siswa Aktif**:
   - Menambahkan tombol ekspor di [index.php](file:///./dashboard/app/Views/students/index.php) dan rute `exportExcel()` di [Student.php](file:///./dashboard/app/Controllers/Student.php) & [Routes.php](file:///./dashboard/app/Config/Routes.php) untuk mengunduh Excel siswa aktif menggunakan SheetJS.
3. **Upsert Tanpa Reset Password**:
   - Memodifikasi logic `import()` di [Student.php](file:///./dashboard/app/Controllers/Student.php) agar tidak meng-overwrite `password_hash` atau `first_login` saat memperbarui profil siswa lama.
4. **Soft Deactivation via Excel**:
   - Menambahkan checkbox "Nonaktifkan siswa yang tidak ada di Excel" dan logic pemrosesan parameter `deactivate_missing` di [Student.php](file:///./dashboard/app/Controllers/Student.php) untuk transisi ajaran baru secara bulk.
5. **Filter Siswa Nonaktif di Seluruh Sistem**:
   - Menyediakan toggle "Tampilkan Siswa Nonaktif" di Manajemen Siswa [index.php](file:///./dashboard/app/Views/students/index.php).
   - Memfilter siswa aktif di absensi kelas KBM pada [Attendance.php](file:///./dashboard/app/Controllers/Attendance.php) dan statistik total siswa di dashboard utama [Dashboard.php](file:///./dashboard/app/Controllers/Dashboard.php).

### **v1.7.15** (6 Jun 2026) - Sinkronisasi Dokumentasi & Perbaikan UI PKL
1. **Perbaikan Tata Letak Tombol Aksi PKL**:
   - Menyatukan tombol aksi laporan PKL ke dalam kontainer flexbox horizontal di [index.php](file:///./dashboard/app/Views/pkl/index.php) agar sejajar dan rapi.

### **v1.7.14** (6 Jun 2026) - Pisahkan Statistik KBM & PKL di Dashboard
1. **Card Siswa PKL Hadir**:
   - Menambahkan card statistik terpisah "Siswa PKL Hadir" di [index.php](file:///./dashboard/app/Views/dashboard/index.php) untuk semua role (admin, kepsek, guru, guru_bk).
   - Memfilter query KBM di [Dashboard.php](file:///./dashboard/app/Controllers/Dashboard.php) agar mengecualikan data PKL (catatan diawali 'PKL:').

### **v1.7.13** (6 Jun 2026) - Penanda Pembaruan Absensi KBM
1. **Label Update KBM**:
   - Mendeteksi apakah absensi kelas sudah pernah dibuat di tanggal yang sama di [Attendance.php](file:///./dashboard/app/Controllers/Attendance.php).
   - Menambahkan penanda `⚠️ *[#Pembaruan Absensi]*` pada broadcast WA KBM di [server.js](file:///./execution/server.js) jika ini adalah update (bukan laporan pertama).

### **v1.7.12** (6 Jun 2026) - Pembaruan Format Template Bot WhatsApp
1. **7 Template Pesan Diperbarui**:
   - Menghapus pembatas garis tebal `━━━━━━━━━━━━━━━━━━━━` dan link `http://localhost:8080` dari seluruh template pesan di database `siakanuda.db`.
   - Menyeragamkan footer penutup: `_Pesan ini dikirim otomatis oleh SIAKANUDA ~ SMK NU Darussalam_`.

### **v1.7.11** (6 Jun 2026) - Respon Asinkron KBM Broadcast (Anti Timeout)
1. **Non-blocking Response**:
   - Mengubah endpoint `/api/attendance/broadcast` di [server.js](file:///./execution/server.js) agar langsung mengembalikan 200 OK setelah template dirender, lalu mengirim pesan WA di background thread.
   - Menghilangkan cURL timeout 28 di dashboard PHP.

### **v1.7.10** (6 Jun 2026) - Kirim Laporan KBM ke Wali Kelas
1. **Broadcast ke Wali Kelas**:
   - Mengambil nomor WA wali kelas dari `WaliKelasModel` berdasarkan kelas di [Attendance.php](file:///./dashboard/app/Controllers/Attendance.php).
   - Mengirim laporan absensi KBM ke wali kelas secara paralel di [server.js](file:///./execution/server.js) bersamaan dengan pengiriman ke grup WA.

### **v1.7.9** (6 Jun 2026) - Redirect Template Tab Setelah Simpan
1. **Perbaikan Redirect**:
   - Mengubah `updateTemplates()` di [WhatsappSettings.php](file:///./dashboard/app/Controllers/WhatsappSettings.php) agar redirect ke `/whatsapp-settings?tab=templates` setelah simpan.

### **v1.7.8** (6 Jun 2026) - Otomatis Kirim Rekap Konsolidasi KBM Harian
1. **Rekap Konsolidasi Otomatis**:
   - Menambahkan pengecekan status pengisian absensi seluruh kelas di [server.js](file:///./execution/server.js). Jika semua kelas sudah mengisi absen hari ini, rekapitulasi sekolah dikirim secara otomatis ke grup WA.
   - Menambahkan template `kbm_consolidated_recap` di [db.js](file:///./execution/db.js) yang dapat dikustomisasi oleh admin melalui dashboard.

### **v1.7.7** (6 Jun 2026) - Grup Terpusat & Keterangan KBM Absent WA
1. **Grup Terpusat KBM & PKL**:
   - Menambahkan tombol "Set Keduanya" pada tab Grup WhatsApp di [index.php](file:///./dashboard/app/Views/whatsapp_settings/index.php) untuk memudahkan pengaturan satu grup WA.
   - Melakukan JID fallback otomatis di [server.js](file:///./execution/server.js) dan [cron_jobs.js](file:///./execution/cron_jobs.js) jika salah satu target group JID dikosongkan.
2. **Keterangan & Nama Siswa Absen KBM**:
   - Mewajibkan pengisian keterangan untuk status Sakit, Izin, dan Alpha via validasi JS di [class.php](file:///./dashboard/app/Views/attendance/class.php) dan validasi PHP di [Attendance.php](file:///./dashboard/app/Controllers/Attendance.php).
   - Memetakan nama dan catatan detail absensi siswa tidak hadir dan menyisipkannya via variabel `detail_absen` ke notifikasi WhatsApp KBM di [server.js](file:///./execution/server.js).

### **v1.7.6** (6 Jun 2026) - Perbaikan Timeout WhatsApp Broadcast & Optimasi Paralel
1. **Perbaikan Timeout Absensi KBM**:
   - Menambahkan validasi `isConnected` pada `sendMessage()` di [bot.js](file:///./execution/bot.js) agar langsung gagal (fail fast) saat bot terputus, menghindari status hang/queue Baileys.
   - Meningkatkan timeout curl request di [Attendance.php](file:///./dashboard/app/Controllers/Attendance.php) dari 8 detik menjadi 12 detik.
2. **Optimasi Pengiriman Paralel & Trimming JID**:
   - Menggunakan `Promise.allSettled` di [server.js](file:///./execution/server.js) untuk mengirim notifikasi ke banyak admin/pembimbing secara paralel.
   - Melakukan `.trim()` pada pembacaan `SCHOOL_GROUP_JID` dan `BROADCAST_GROUP_JID` di [server.js](file:///./execution/server.js) dan [cron_jobs.js](file:///./execution/cron_jobs.js) untuk membersihkan leading/trailing spaces dari file `.env`.

### **v1.7.5** (6 Jun 2026) - Real-time Audit Logs & Hubungkan Grup WA via Link
1. **Real-time Log Audit Chat**:
   - Menambahkan event listener `messages.upsert` di [bot.js](file:///./execution/bot.js) untuk menyimpan chat masuk dan memancarkan log via Socket.io (`bot:log`).
   - Memperbarui [logs/index.php](file:///./dashboard/app/Views/logs/index.php) untuk terhubung ke Socket.io di port 7860 secara real-time dan secara otomatis memetakan nama pengirim dari allowed_numbers/students.
2. **Hubungkan Grup WhatsApp via Link Undangan**:
   - Menambahkan method `joinGroupByInvite` di [bot.js](file:///./execution/bot.js) dan endpoint `POST /api/bot/join-group` di [server.js](file:///./execution/server.js) untuk memproses bergabung ke grup WA dengan input link/code.
   - Menambahkan form hubungkan grup via link undangan pada tab Grup WhatsApp di [index.php](file:///./dashboard/app/Views/whatsapp_settings/index.php) dan route proxy di controller [WhatsappSettings.php](file:///./dashboard/app/Controllers/WhatsappSettings.php).

### **v1.7.4** (6 Jun 2026) - Penyelarasan Tampilan Pengaturan WA
1. **Reposisi Card Koneksi Perangkat Bot**:
   - Memindahkan card "Koneksi Perangkat Bot" ke bagian bawah di [index.php](file:///./dashboard/app/Views/whatsapp_settings/index.php).
   - Memperluas tab navigasi pengaturan (Template, Cron, Whitelist, Groups) menjadi full-width (`col-lg-12`) agar tidak sempit.
   - Menghapus kelas `h-100` pada kedua card untuk responsivitas layout yang lebih baik.

### **v1.7.3** (6 Jun 2026) - Dual Notifikasi Hasil Absensi KBM
1. **Dua Notifikasi Umpan Balik (Double Alert)**:
   - Memodifikasi `layouts/template.php` untuk merender flashdata `info` sebagai info-box berwarna biru di samping `success` dan `error`.
   - Mengubah `saveClass()` di [Attendance.php](file:///./dashboard/app/Controllers/Attendance.php) agar mengambil data JSON detail dari API Node.js dan menyetel dua flashdata sekaligus: notifikasi penyimpanan sukses (`success`) dan notifikasi status pengiriman WhatsApp (`info`/`error`).
2. **Detail Status API Broadcast KBM**:
   - Memperbarui endpoint `/api/attendance/broadcast` di [server.js](file:///./execution/server.js) agar mengembalikan status pengiriman (`sent_to_group`) dan target JID grup agar controller PHP dapat memberikan notifikasi real-time yang akurat.

### **v1.7.2** (6 Jun 2026) - Perbaikan Fallback Broadcast Absensi KBM
1. **Fallback ke Broadcast Grup Umum & Admin**:
   - Menambahkan fallback otomatis ke `BROADCAST_GROUP_JID` jika `SCHOOL_GROUP_JID` tidak diatur.
   - Memperbaiki logika penanganan error agar kegagalan kirim ke grup WA tidak menghentikan pengiriman fallback ke para Administrator.
   - Mengisolasi penanganan kesalahan (try-catch) pada pengiriman ke masing-masing admin secara individual.

### **v1.7.1** (6 Jun 2026) - Broadcast Otomatis Absensi KBM ke Grup WA
1. **Pemicu Broadcast Absensi KBM**:
   - Menambahkan pemanggilan API WhatsApp broadcast di `saveClass()` pada [Attendance.php](file:///./dashboard/app/Controllers/Attendance.php) setiap kali guru menyimpan absensi kelas KBM.
2. **Endpoint API Baru KBM Broadcast**:
   - Menambahkan rute `/api/attendance/broadcast` di [server.js](file:///./execution/server.js) yang memformat pesan KBM dan mengirimkannya ke `SCHOOL_GROUP_JID`.
3. **Template & Skema Database SQLite**:
   - Menambahkan tabel `bot_templates` dan `cron_configs` di `initSchema()` pada [db.js](file:///./execution/db.js) untuk menjamin portabilitas, serta melakukan seeding template baru `kbm_attendance_broadcast` secara otomatis.

### **v1.7.0** (6 Jun 2026) - Konsolidasi Fitur & Peningkatan Fleksibilitas WhatsApp Bot
1. **Kelompok PKL Tanpa Batasan Ketua**:
   - Melonggarkan validasi `ketua_phone` di [KelompokPklModel.php](file:///./dashboard/app/Models/KelompokPklModel.php) (`permit_empty`) dan menghapus atribut `required` pada form [groups.php](file:///./dashboard/app/Views/pkl/groups.php).
2. **Tab Pengaturan WA Baru & Hapus Menu Manajemen Guru**:
   - Memindahkan whitelist nomor guru/staf dari menu terpisah (/allowed-numbers) menjadi salah satu tab di bawah menu baru **"Pengaturan WA"** (/whatsapp-settings?tab=whitelist). Kontroler [AllowedNumber.php](file:///./dashboard/app/Controllers/AllowedNumber.php) mengarahkan secara otomatis ke tab whitelist.
3. **Konfigurasi Target Group JID Live**:
   - Menambahkan visualisasi daftar group WA terhubung melalui Sock Baileys API di [bot.js](file:///./execution/bot.js) dan form target broadcast JID KBM & PKL via UI Admin langsung menyimpan ke berkas `.env`.
4. **Manajemen Cron Job Dinamis & Template Pesan**:
   - Mendukung penambahan tugas cron kustom melalui database SQLite yang dipetakan ke scheduler runtime di [cron_jobs.js](file:///./execution/cron_jobs.js).
   - Menambahkan UI text editor template pesan WhatsApp dengan klik tag variabel di dashboard.

### **v1.6.24** (6 Jun 2026) - Overhaul Laporan PDF Harian & Resolusi Kritis SQLite I/O exFAT
1. **Resolusi Disk I/O Error exFAT**:
   - Mengubah `journal_mode` SQLite dari `WAL` menjadi `DELETE` pada [db.js](file:///./execution/db.js) demi mendukung drive exFAT portabel (tidak mendukung memory mapping/shared memory).
2. **Compact Daily PDF PKL**:
   - Mengubah struktur PDF harian menjadi compact card layout 1 halaman bersih dari emoji tidak dikenal dan resolusi foto diperbesar tanpa distorsi.
3. **Pemberitahuan Berhasil & Validasi Lokasi**:
   - Menampilkan alert wajib lokasi di Ketua PKL, melarang isi jurnal/absen sebelum lokasi terisi, dan menyertakan notifikasi sukses berformat waktu WIB.
4. **WhatsApp Broadcast Update**:
   - Menyertakan info kelas Ketua PKL, lokasi absensi, dan label `⚠️ *[#Perubahan Laporan]*` ketika terjadi update laporan.
5. **Tombol Pintasan Uji Cetak**:
   - Tombol "Cetak Harian" kustom ditambahkan untuk admin menguji cetakan laporan pada tanggal apa pun.

### **v1.6.23** (6 Jun 2026) - Fix Bug Validasi Textarea PKL, Contoh Isian, & Karakter Counter
1. **Fix Bug Validasi Form PKL**:
   - Memperbaiki selector Javascript di [student_report.php](file:///./dashboard/app/Views/pkl/student_report.php) untuk menggunakan target `textarea` alih-alih `input` agar validasi karakter minimal 75 karakter berfungsi kembali setelah perubahan ke textarea.
2. **Karakter Counter Interaktif & Lega**:
   - Menambahkan counter karakter interaktif di bawah isian jurnal (misal `60/75`) yang otomatis berubah warna menjadi merah (jika kurang dari 75) atau hijau (jika >= 75) saat diketik.
   - Memperbesar ukuran default textarea isian jurnal, sakit, izin, dan alpha menjadi 4 atau 5 baris (`rows="4"`, `min-height: 90px`).
   - Menambahkan placeholder berisi contoh isian realistis di setiap textarea untuk mempermudah siswa.

### **v1.6.22** (6 Jun 2026) - Pembaruan Input Textarea PKL & Pengembalian Lokasi Presensi
1. **Pengembangan Textarea Fleksibel**:
   - Mengubah input Jurnal, Sakit, Izin, dan Alpha menjadi `<textarea>` di [student_report.php](file:///./dashboard/app/Views/pkl/student_report.php) dengan baris default 2-3 baris dan auto-grow berdasarkan tinggi inputan.
2. **Pengembalian Lokasi Presensi**:
   - Menampilkan kembali kolom input "Tempat Melakukan Presensi" (`location_data`) yang bersifat wajib diisi saat PKL Masuk dan disembunyikan/opsional saat PKL Libur.
   - Mengintegrasikan data lokasi presensi ke dalam WhatsApp broadcast notification saat PKL Masuk di [server.js](file:///./execution/server.js).
   - Menampilkan detail lokasi presensi pada modal laporan halaman admin PKL di [index.php](file:///./dashboard/app/Views/pkl/index.php).

### **v1.6.21** (6 Jun 2026) - Tombol Simpan Cepat Libur & Optimasi Format WA PKL Libur
1. **Tombol Simpan Cepat Libur**:
   - Menambahkan tombol simpan/submit di dalam form alasan libur pada [student_report.php](file:///./dashboard/app/Views/pkl/student_report.php) agar ketua PKL tidak perlu men-scroll ke bawah saat tempat PKL libur/tutup.
2. **Format WA Broadcast Libur**:
   - Memperbarui broadcast notifikasi WA di [server.js](file:///./execution/server.js) saat libur untuk menampilkan seluruh nama anggota kelompok dalam satu baris (dipisahkan koma) dan menyertakan kelas pada nama Ketua Kelompok.

### **v1.6.20** (6 Jun 2026) - Penyederhanaan Widget Absensi PKL Ketua PKL
1. **Penyederhanaan Widget Dashboard**:
   - Menyederhanakan tampilan widget absensi PKL pada dashboard utama `ketua_pkl` menjadi satu status card premium yang dapat diklik (mengarahkan langsung ke halaman pengisian `/pkl`).
   - Menghapus form absensi inline dan membersihkan JavaScript validasi/manipulasi form terkait pada halaman dashboard utama.

### **v1.6.19** (5 Jun 2026) - UI Manajemen Peran Siswa & Otomatisasi Peran PKL
1. **Dropdown Peran Siswa**:
   - Menambahkan pilihan Peran/Role (`Siswa`, `Ketua PKL`, `Anggota PKL`) di Modal Tambah & Edit Siswa serta menampilkan kolom Peran pada halaman Manajemen Siswa di [index.php](file:///./dashboard/app/Views/students/index.php).
2. **Pemilih Ketua Kelompok PKL**:
   - Mengganti input manual WA Ketua Kelompok dengan searchable dropdown pemilih siswa (Single Student Picker) di halaman Kelompok PKL [groups.php](file:///./dashboard/app/Views/pkl/groups.php).
3. **Otomatisasi Sinkronisasi Peran**:
   - Menambahkan method `syncStudentPklRoles()` di [Pkl.php](file:///./dashboard/app/Controllers/Pkl.php) yang mensinkronisasi otomatis peran siswa di database (`siswa`, `ketua_pkl`, `anggotapkl`) saat kelompok PKL dibuat, diedit, atau dihapus.

### **v1.6.18** (5 Jun 2026) - Rekap Mingguan PKL & Kompresi Foto Otomatis
1. **Kompresi Foto Otomatis**:
   - Menambahkan library `sharp` di [package.json](file:///./package.json).
   - Mengubah [photo_sync.js](file:///./execution/photo_sync.js) untuk me-resize gambar hingga lebar max 640px dan kualitas JPEG 50% sebelum diunggah ke Supabase Storage, sangat menghemat kuota siswa & storage server.
2. **Deteksi Akses Lokal (WiFi)**:
   - Membuat helper [network_helper.php](file:///./dashboard/app/Helpers/network_helper.php) untuk memisahkan akses lokal (WiFi Sekolah/Tailscale/localhost) dan internet.
   - Membatasi pengunduhan PDF laporan harian & mingguan hanya untuk akses lokal demi performa server.
3. **PDF Rekap Mingguan PKL**:
   - Membuat PDF A4 Portrait 6 halaman (1 hari = 1 halaman, Senin-Sabtu) yang memuat rekap absensi harian kelompok, jurnal kegiatan, foto bukti asli, dan Kop Surat overlay otomatis.
4. **UI/UX Rekap Mingguan**:
   - Penambahan menu Rekap Mingguan, modal preview asinkron yang memuat thumbnail Supabase, serta tombol cetak mingguan di dashboard siswa [student_report.php](file:///./dashboard/app/Views/pkl/student_report.php) dan dashboard admin/pembimbing [index.php](file:///./dashboard/app/Views/pkl/index.php).

### **v1.6.17** (5 Jun 2026) - Koreksi Spasi Judul & Overlay Logo Presisi PDF
1. **Overlay Logo Resolusi Tinggi Presisi**:
   - Mengganti programmatic logo overlay lama yang meleset koordinatnya dengan overlay logo presisi tinggi (menggunakan berkas [logo-smk.png](file:///./public/logo-smk.png) resolusi 540x540px) di dalam helper `applyKopSurat` pada [pdf_generator.js](file:///./execution/pdf_generator.js).
   - Menggunakan koordinat persis dari template hasil analisis PDF (Landscape: x=40.5, y=505.42, w=66.6, h=66.75; Portrait: x=73.45, y=731.27, w=66.6, h=66.75) dan menutupi logo bawaan yang pecah (88x88px) menggunakan background putih solid tepat di bawahnya agar logo tampil bersih dan tajam 100% tanpa distorsi/meleset.
2. **Koreksi Jarak Vertikal Judul (Jarak Judul Ke Kop)**:
   - Mengubah penetapan koordinat vertikal (Y) pada halaman pertama jika menggunakan template kop surat agar mengikuti format `format-revisi.pdf` secara presisi.
   - Posisi Title (`REKAP ABSENSI BULANAN — KELAS ...`) diatur ke `118` (baseline `129`), Subtitle ke `131` (baseline `142`), Wali Kelas ke `159` (baseline `168`), Tanggal Unduh ke `172` (baseline `181`), dan titik mulai tabel (`tableY`) ke `193` dari batas atas halaman.
3. **Penyelarasan Batas Wrap Table**:
   - Menyesuaikan batas pembungkus baris tabel (`wrap check`) dari `530` menjadi `540` agar halaman pertama dapat menampung tepat 18 baris siswa sebelum memicu perpindahan halaman baru, persis seperti layout pada `format-revisi.pdf`.

### **v1.6.16** (5 Jun 2026) - Header Dua Baris PDF & Logo Overlay Resolusi Tinggi
1. **Header Dua Baris (Two-Row Header) & Grid Sepotong**:
   - Mendesain ulang struktur header tabel absensi bulanan di [pdf_generator.js](file:///./execution/pdf_generator.js) menjadi dua baris:
     - Row 1: Kolom `No`, `Nama Siswa`, `NISN` (spans 36pt vertikal) serta cell merged `Periode Bulan [Nama Bulan]` (di atas kolom tanggal) dan `Rekap` (di atas kolom H/S/I/A).
     - Row 2: Angka tanggal (`1 2 3 ... 30`) dan huruf `H S I A`.
   - Menyelaraskan fungsi `drawVerticalGridLines` agar garis pembatas kolom tanggal dan rekap hanya ditarik mulai baris kedua header (`startY + 18`) agar tidak memotong isi cell merged di baris pertama.
2. **Penyelarasan Teks Info Absensi**:
   - Memposisikan teks judul laporan `REKAP ABSENSI BULANAN` dan `Tahun Pelajaran` di tengah halaman (*center-aligned*, lebar `770`, mulai `x = 36`).
   - Menyusun info `Wali Kelas` dan `Tanggal Unduh` secara terformat rata kiri di atas tabel.
3. **Overlay Logo Resolusi Tinggi & Hapus Placeholder**:
   - Memperbarui helper `applyKopSurat` agar pada orientasi Landscape, sistem menggambar persegi putih solid di posisi `x = 47, y = height - 74` untuk menutupi placeholder tulisan "Logo-smk.png" pada template PDF fisik, lalu menempelkan berkas logo asli [logo-smk.png](file:///./public/logo-smk.png) di atasnya agar tidak buram/pecah saat dicetak.

### **v1.6.15** (5 Jun 2026) - Format PDF Rekap Hitam Putih & Integrasi Meta Info Baru
1. **Format Absensi Hitam Putih (B&W)**:
   - Menghapus background zebra striping (`#fafafa`) pada tabel absensi bulanan di [pdf_generator.js](file:///./execution/pdf_generator.js) agar tampilan tabel bersih polos (B&W) sesuai permintaan.
   - Mengubah warna huruf status absen (Sakit, Izin, Alpha) menjadi hitam (`#000000`) agar senada dengan teks lainnya, kecuali arsiran hari Minggu dan header tabel yang tetap dipertahankan.
2. **Sinkronisasi Margin 1.27 cm (36 pt)**:
   - Mengubah seluruh margin dokumen landscape (top, bottom, left, right) menjadi `36` (setara 1.27 cm).
   - Memperlebar lebar tabel (`tableWidth`) menjadi `770` (lebar halaman `842` - `36` - `36`) dan menyelaraskan seluruh koordinat awal X konten ke `36`.
   - Mengoptimalkan pembatas baris halaman table (wrap check) ke `530` dan posisi awal table halaman baru ke `70`.
3. **Penyematan Blok Keterangan Metadata Baru**:
   - Memperbarui parameter API `generateClassReportPDF` agar menerima objek `metadata` (Tahun Pelajaran, Tanggal Unduh, nama Pengunduh, dan Wali Kelas).
   - Menuliskan metadata tersebut di bagian atas tabel dengan susunan:
     - `REKAP ABSENSI BULANAN — KELAS [Nama Kelas] Tahun Pelajaran [TP]`
     - `( Tanggal Unduh: [Tanggal], Pengunduh: [Nama Guru] )` (format huruf miring dan kecil)
     - `Periode = Bulan [Nama Bulan]`
     - `Wali Kelas = [Nama Wali Kelas]`
   - Mengintegrasikan pengambilan data wali kelas dan parameter session pengunduh di `printPdf` pada [Attendance.php](file:///./dashboard/app/Controllers/Attendance.php) dan meneruskannya via query string ke Express API di [server.js](file:///./execution/server.js).

### **v1.6.14** (5 Jun 2026) - Perubahan Identitas NIS ke NISN & Sinkronisasi Margin PDF
1. **Perubahan Kolom Identitas NIS ke NISN**:
   - Mengubah judul kolom tabel di rekap absensi bulanan dari `NIS` menjadi `NISN` pada [pdf_generator.js](file:///./execution/pdf_generator.js) di dua lokasi (header halaman pertama dan header halaman baru).
   - Memperlebar lebar kolom `nisWidth` dari `35` menjadi `60` agar nomor NISN (10 digit) dapat muat dalam satu baris tanpa terpotong (*no wrap*).
2. **Sinkronisasi Margin PDF dengan Kop Surat**:
   - Menyelaraskan margin kiri halaman PDF rekap bulanan menjadi `78` (sama seperti format portrait) untuk meratakan alignment kiri-kanan tabel dengan garis pembatas Kop Surat Landscape.
   - Menyesuaikan `tableWidth` menjadi `720` (lebar halaman `842` - margin kiri `78` - margin kanan `44`), memperlebar kolom nama siswa (`namaWidth`) menjadi `180`, serta memindahkan koordinat X teks info dan judul ke `78`.

### **v1.6.13** (5 Jun 2026) - Auto-submit Hadir Semua & Perbaikan PDF Rekap
1. **Auto-submit Hadir Semua (Absensi KBM)**:
   - Mengubah fungsi `markAllHadir()` di [class.php](file:///./dashboard/app/Views/attendance/class.php) agar otomatis men-submit form setelah mengubah status semua siswa menjadi Hadir.
   - Menyelaraskan rute pengalihan di `saveClass` pada [Attendance.php](file:///./dashboard/app/Controllers/Attendance.php) agar mengarahkan kembali ke `/attendance?date=[date]` guna melestarikan parameter tanggal yang sedang aktif.
2. **Penyempurnaan Layout & Lebar Kolom PDF Rekap**:
   - Di [pdf_generator.js](file:///./execution/pdf_generator.js):
     - Menurunkan `startY` konten ke `145` (sehingga tabel dimulai pada `185`) saat `hasKop` aktif agar tabel tidak menabrak Kop Surat Landscape.
     - Memperlebar lebar tabel menjadi `680` (lebar kolom nama siswa diperbesar menjadi `160`, kolom NIS diperkecil menjadi `35`, dan total kolom rekap H/S/I/A disesuaikan ke `14`).
     - Menghapus blok tanda tangan Kepala Sekolah ("Mengetahui, Kepala Sekolah...") di bagian kaki dokumen sesuai permintaan.

### **v1.6.12** (5 Jun 2026) - Integrasi Kop Surat Landscape & Pembersihan Git status
1. **Integrasi Kop Surat Landscape**:
   - Mendefinisikan konstanta `KOP_LANDSCAPE_PATH` untuk mengacu pada berkas `data/Kop surat lanscape.pdf`.
   - Mengubah helper `applyKopSurat` agar secara dinamis mendeteksi orientasi halaman PDF (Landscape vs Portrait) menggunakan dimensi lebar halaman, dan memuat berkas Kop Surat yang sesuai.
   - Menyelaraskan logika cek file Kop Surat di `generateClassReportPDF` agar menggunakan `KOP_LANDSCAPE_PATH` saat menentukan pergeseran konten dan penempatan tanda tangan.
2. **Pembersihan Git Status & Gitignore**:
   - Menambahkan folder `siakanuda-apk/` ke `.gitignore` agar tidak mengotori repositori utama (karena folder tersebut merupakan repositori terpisah).
   - Menambahkan dan memantau berkas template `data/Kop surat lanscape.pdf` di Git.

### **v1.6.11** (5 Jun 2026) - PDF Rekap KBM Landscape & Deteksi Hari Minggu
1. **PDF Rekap KBM Landscape**:
   - Mengubah orientasi PDF rekap absensi bulanan dari Portrait menjadi Landscape (`layout: 'landscape'`) di [pdf_generator.js](file:///./execution/pdf_generator.js) agar muat 31 kolom tanggal.
   - Menyesuaikan batas pembungkus halaman (overflow check) dari 720pt menjadi 490pt.
   - Menyelaraskan titik mulai tabel dan header ke x=110pt dan batas tanda tangan di x=560pt.
2. **Highlight & Deteksi Hari Minggu**:
   - Mendeteksi hari minggu (`getDay() === 0`) pada target bulan/tahun secara dinamis.
   - Mewarnai header tanggal Minggu dengan warna merah dan memberi arsiran warna latar merah muda lembut (`#fdf2f2`) pada sel/kolom hari Minggu di tabel absensi.

### **v1.6.10** (5 Jun 2026) - Matriks Absensi Bulanan & Margin Kop Surat PDF
1. **Penyelarasan Margin Kop Surat**:
   - Menyesuaikan margin kiri dokumen PDF ke 78pt dan margin kanan ke 44pt di [pdf_generator.js](file:///./execution/pdf_generator.js) agar lurus dengan Kop Surat cetak.
   - Menggeser posisi semua teks judul, metadata, tabel, and tandatangan ke x=78pt.
2. **Format Matriks Absensi Bulanan**:
   - Mengubah rekap bulanan menjadi tabel matriks horizontal ber-grid dengan 28/29/30/31 kolom hari, kolom No, Nama, NIS, dan total (H/S/I/A).
3. **Penyederhanaan & Reposisi Banner Dapodik**:
   - Menghapus banner akademik besar di bawah flash message dan memindahkannya ke tengah navbar atas di [template.php](file:///./dashboard/app/Views/layouts/template.php) dalam bentuk teks ringkas `Dapodik: TP [Tahun] ([Semester])`.

### **v1.6.9** (5 Jun 2026) - Rekap Absensi Bulanan & Kop Surat PDF
1. **Cetak Rekap Absensi Bulanan (PDF)**:
   - Menambahkan rute dan metode `printPdf()` di [Attendance.php](file:///./dashboard/app/Controllers/Attendance.php) untuk mengunduh rekap absensi kelas KBM per bulan.
   - Menyediakan tombol cetak PDF rekap bulanan dengan modal penentu Bulan dan Tahun pada halaman utama Absensi KBM di [index.php](file:///./dashboard/app/Views/attendance/index.php).
   - Menambahkan API endpoint `GET /api/attendance-report/pdf/:className` di [server.js](file:///./execution/server.js) yang terhubung ke modul generator PDF.
2. **Integrasi Kop Surat PDF Fisik**:
   - Menambahkan dukungan pustaka `pdf-lib` di Node.js.
   - Mengimplementasikan helper `applyKopSurat()` di [pdf_generator.js](file:///./execution/pdf_generator.js) untuk menempelkan (overlay) file [Kop surat.pdf](file:///./data/Kop%20surat.pdf) secara otomatis di halaman pertama dokumen PDF hasil generator (Rekap Absen KBM, Rekap Jurnal PKL, Bukti PKL Hari Ini).
   - Menyembunyikan kop surat bawaan jika berkas kop surat fisik ditemukan di folder `data/`.

### **v1.6.8** (5 Jun 2026) - Notifikasi Dashboard Pintar (Actionable Notifications)
1. **Pendeteksi Ketidakterisian Data Real-Time**:
   - Di [Dashboard.php](file:///./dashboard/app/Controllers/Dashboard.php):
     - Logika pendeteksi KBM kelas hari berjalan yang belum di-absen.
     - Logika pendeteksi kelompok PKL hari berjalan yang belum mengirim laporan harian.
     - Logika menyesuaikan data notifikasi secara dinamis berdasarkan role (Admin, Kepsek, Guru BK/Mapel, Ketua PKL, Anggota PKL).
2. **Widget Notifikasi Dashboard Utama**:
   - Di [index.php](file:///./dashboard/app/Views/dashboard/index.php):
     - Widget modern premium untuk menampilkan notifikasi tindakan penting lengkap dengan ikon representatif, tingkat urgensi (warna *soft warning/danger*), dan tautan langsung untuk mempercepat pengisian data.

### **v1.6.7** (4 Jun 2026) - Kustomisasi WA Broadcast & Form Detail PKL (Sakit, Izin, Alpha)
1. **Pembaruan Notifikasi WA PKL**:
   - Di [server.js](file:///./execution/server.js) dan [bot.js](file:///./execution/bot.js):
     - Ditambahkan nama pembimbing (`👨‍🏫 Pembimbing: [Nama Guru]`) di bawah Tempat PKL.
     - Ditambahkan detail jam pelaporan dalam WIB (`(Pukul HH:MM WIB)`).
     - Digabungkan baris Kehadiran, Foto bukti, dan Lokasi Presensi menjadi satu baris hemat tempat (`📊 Kehadiran: X Hadir / Y Total | 📸 Foto: Z | 📍 Lokasi: [Lokasi]`).
2. **Form Pengisian PKL Khusus (Sakit, Izin, Alpha)**:
   - Di [student_report.php](file:///./dashboard/app/Views/pkl/student_report.php) dan [Pkl.php](file:///./dashboard/app/Controllers/Pkl.php):
     - Tampilan form diubah dinamis berdasarkan status absensi siswa.
     - **Hadir**: Menampilkan isian jurnal (minimal 75 karakter) + wajib foto selfie.
     - **Sakit**: Menampilkan input isian sakit apa + wajib foto bukti (surat dokter / screenshot izin ortu).
     - **Izin**: Menampilkan input isian alasan izin + wajib foto bukti (surat izin / screenshot persetujuan).
     - **Alpha**: Menampilkan radio pilihan status hubungi (Ya / Tidak) + keterangan tambahan opsional. Foto bukti bersifat opsional.
     - Logika validasi data wajib pada form sisi klien (JS) dan sisi server (PHP) telah diperbarui sepenuhnya.
   - Detail alasan sakit/izin/alpha dikompilasi menjadi string terformat (misal: `Sakit: Demam tinggi`) dan disimpan ke dalam kolom database `jurnal_kegiatan` pada tabel `attendance_pkl` (tanpa mengubah skema database).
