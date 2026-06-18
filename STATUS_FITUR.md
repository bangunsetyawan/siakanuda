# 🗺️ SIAKANUDA — Peta Status Fitur & Pembaruan Git
> **Versi:** v1.11.1 | **Tanggal:** 18 Juni 2026 | **Total Commit:** 191
> **Untuk siapa:** Developer solo yang perlu re-orient cepat tanpa baca ulang ratusan baris.
> **Cara pakai:** Scan checklist → cari bagian yang relevan → langsung kerja.

### Legenda
| Ikon | Arti |
|------|------|
| ✅ `[x]` | Selesai & aktif di production |
| 🔄 | Sedang dikerjakan / dalam proses |
| ⬜ `[ ]` | Belum dimulai (rencana) |
| ❌ | Dibatalkan / tidak dilanjutkan |
| 🔙 | Rollback / diganti pendekatan lain |
| 💡 | Saran pengembangan |

---

## 📊 Ringkasan Git Repository

| Metrik | Nilai |
|--------|-------|
| **Total Commit** | 191 |
| **Branch** | `master` (single branch) |
| **Tags** | `v1.0.0`, `v1.1.0`, `v1.2.0`, `v1.4.6`, `v1.5.0`, `v1.5.1`, `v1.10.0`, `v1.11.0`, `v1.11.1` |
| **Remote** | Tidak ada (lokal only) |
| **Git User** | `SIAKANUDA Agent <siakanuda@smknudarussalam.sch.id>` |
| **Commit Pertama** | v1.0.0 — Fondasi WhatsApp Bot + Web Dashboard |
| **Commit Terakhir** | v1.11.1 — tambah README GitHub profesional + perbaiki .gitignore untuk public repo |
| **Rentang Waktu** | ±15 hari pengembangan aktif (28 Mei – 15 Juni 2026) |

### Statistik Commit per Tipe
| Tipe | Jumlah | Keterangan |
|------|--------|------------|

| `feat` | ~102 | Fitur baru |
| `fix` | ~42 | Perbaikan bug |
| `docs` | ~28 | Dokumentasi |
| `chore` | ~12 | Maintenance |
| `clean` / `refactor` | ~7 | Pembersihan & restrukturisasi |

### Konvensi Commit
```
feat(vX.Y.Z): [ringkasan 1 baris]
fix(vX.Y.Z): [deskripsi perbaikan]
docs(vX.Y.Z): [update dokumentasi]
chore: [maintenance]
refactor: [restrukturisasi]
```

---

## 🏗️ Arsitektur (2 Service)

| Service | Port | Teknologi | Fungsi |
|---------|------|-----------|--------|
| **Dashboard** | 8080 | CodeIgniter 4 (PHP) | UI utama, semua user masuk sini |
| **Background API** | 7860 | Node.js + Express | WA Bot, PDF, Cron, Sync (hidden) |

```
npm run dev              ← Start Node.js (port 7860)
php spark serve          ← Start CI4 (port 8080)
```

---

## 📁 Peta Folder Aktual

```
siakanuda/                              ← Root (Node.js + SQLite)
├── execution/                          ← Backend Node.js (14 file + 1 subdir)
│   ├── server.js                       ← Express entry point (67KB)
│   ├── db.js                           ← SQLite + schema init (64KB)
│   ├── bot.js                          ← WhatsApp Bot logic (11KB)
│   ├── pkl_routes.js                   ← API PKL
│   ├── pdf_generator.js                ← Generator PDF native (33KB)
│   ├── wa_connector.js                 ← Baileys WA (10KB)
│   ├── cron_jobs.js                    ← Cron scheduler (15KB)
│   ├── broadcast_utils.js              ← Utilitas broadcast WA (4KB)
│   ├── photo_sync.js                   ← Sinkronisasi foto Supabase (7KB)
│   ├── ai_processor.js                 ← AI chat [ARCHIVED] (6KB)
│   ├── gas_pdf_client.js               ← Google Apps Script PDF (7KB)
│   ├── supabase_client.js              ← Supabase client
│   ├── generate-templates.js           ← Template generator
│   ├── migrate-auth.js                 ← Migrasi auth (8KB)
│   ├── migrate-to-supabase.js          ← Migrasi Supabase (5KB)
│   ├── reset-to-admin.js               ← Reset admin (7KB)
│   ├── supabase-auth.js                ← Supabase auth (7KB)
│   └── middleware/                     ← Express middleware
├── dashboard/                          ← CodeIgniter 4 (Port 8080)
│   ├── app/Controllers/                ← 18 controller
│   │   ├── Achievement.php             ← Prestasi
│   │   ├── AllowedNumber.php           ← Guru/Staf (7.7KB)
│   │   ├── Analytics.php               ← Dashboard analytics (2.9KB)
│   │   ├── Attendance.php              ← Absensi KBM (18KB)
│   │   ├── Auth.php                    ← Login/logout (7.8KB)
│   │   ├── BaseController.php          ← Base + global data
│   │   ├── Counseling.php              ← BK/Konseling
│   │   ├── Dashboard.php               ← Dashboard utama (16KB)
│   │   ├── Feedback.php                ← Kotak suara
│   │   ├── HariLibur.php               ← Hari libur (2.9KB)
│   │   ├── KalenderAkademik.php        ← Kalender (6.3KB)
│   │   ├── MessageLog.php              ← Log WA
│   │   ├── Pkl.php                     ← PKL lengkap (36KB)
│   │   ├── Schedule.php                ← Jadwal pelajaran
│   │   ├── Student.php                 ← Siswa (12KB)
│   │   ├── TahunPelajaran.php          ← Tahun pelajaran (4.3KB)
│   │   ├── Violation.php               ← Pelanggaran
│   │   └── WhatsappSettings.php        ← WA config (14KB)
│   ├── app/Models/                     ← 12+ model
│   ├── app/Views/                      ← ~33 view (19 subdirektori)
│   └── app/Filters/                    ← AuthFilter, RoleFilter
├── data/                               ← Template statis (Excel, dsb.)
├── database/                           ← Skrip migrasi
├── docs/                               ← Dokumentasi
│   └── archive/                        ← Arsip blueprint lama (10 file)
├── directives/                         ← SOP fitur (Markdown)
├── public/                             ← Aset APK + BKK portal
├── backups/                            ← Backup database
├── uploads/                            ← Foto PKL (gitignored)
├── sessions/                           ← Sesi WA (gitignored)
└── siakanuda-apk/                      ← Android APK (gitignored)
```

### View Files (33 file, 19 subdirektori)
| Subdirektori | File | Ukuran Terbesar |
|-------------|------|-----------------|
| `whatsapp_settings/` | `index.php` | 76KB |
| `pkl/` | `student_report.php` | 60KB |
| `pkl/` | `groups.php` | 45KB |
| `dashboard/` | `index.php` | 49KB |
| `students/` | `index.php` | 30KB |
| `allowed_numbers/` | `index.php` | 25KB |
| `kalender_akademik/` | `index.php` | 23KB |
| `layouts/` | `template.php` | 22KB |
| `auth/` | `login.php` | 19KB |
| `schedules/` | `index.php` | 15KB |
| `attendance/` | `index.php`, `class.php`, `personal.php` | 14KB |
| `violations/` | `index.php` | 11KB |
| `hari_libur/` | `index.php` | 10KB |
| `students/` | `details.php` | 10KB |
| `achievements/` | `index.php` | 8KB |
| `auth/` | `profile.php` | 9KB |
| `counseling/` | `index.php` | 7KB |
| `feedbacks/` | `index.php` | 7KB |
| `logs/` | `index.php` | 7KB |
| `tahun_pelajaran/` | `index.php` | 7KB |
| `analytics/` | `index.php` | 4KB |

---

## ✅ Modul & Fitur — Status Lengkap

### 🔐 A. Autentikasi & Keamanan
- [x] Login tabbed: Tab "Siswa" (input NISN) + Tab "Guru" (dropdown nama)
- [x] Password default: Siswa = NISN, Guru = No. WA
- [x] Paksa ganti password (Ketua PKL pakai password default)
- [x] Halaman profil & ganti password (v1.6.19)
- [x] CSRF protection global (token randomized, termasuk AJAX import)
- [x] Session IP binding + regenerate on login
- [x] Role-based routing (`RoleFilter.php`)
- [x] Hardcoded passwords dihapus (semua via bcrypt DB)
- [x] CORS Node.js locked ke localhost:8080
- [x] SQL injection prevention (column whitelist)
- [x] Error sanitization (generik di production)
- [x] Validasi file upload (JPG/PNG, max 5MB)
- [x] Server-side validation di 12 model + 9 controller
- [x] 7 role: admin, kepsek, guru, guru_bk, siswa, ketua_pkl, anggotapkl
- [ ] Rate limiting & Fail2ban — proteksi brute-force (v2.0.0)
- [ ] ~~2FA / OTP~~ ❌ Tidak direncanakan

### 👥 B. Manajemen User
- [x] CRUD Siswa (admin)
- [x] CRUD Guru/Staf — `allowed_numbers` (admin)
- [x] Import Excel (.xlsx) + Live Preview (Siswa & Guru)
- [x] Download template Excel resmi
- [x] Download data siswa aktif (.xlsx) langsung dari UI via SheetJS (v1.8.0)
- [x] Logic import memisahkan insert vs update agar tidak mereset password siswa lama (v1.8.0)
- [x] Opsi bulk nonaktifkan siswa yang tidak terdaftar di file Excel saat import (v1.8.0)
- [x] Reset password siswa ke default
- [x] Kolom `tugas_tambahan` di data guru (v1.6.8)
- [x] CSRF token di import AJAX (PHP-rendered meta tag) (v1.6.8)

### 📋 C. Absensi KBM
- [x] Input absensi per kelas per hari (guru)
- [x] Status: Hadir (H) / Sakit (S) / Izin (I) / Alpha (A)
- [x] Tombol "Hadir Semua" — auto-submit + redirect dengan parameter tanggal
- [x] Rekap personal siswa (kartu identitas + ringkasan + riwayat)
- [x] Admin bisa hapus data absensi
- [x] Jumlah siswa per kelas ditampilkan
- [x] Broadcast notifikasi KBM masuk otomatis ke grup WA sekolah (v1.7.1)
- [x] Validasi keterangan wajib untuk S/I/A + nama siswa di broadcast WA (v1.7.7)
- [x] Rekap konsolidasi otomatis ke grup WA saat semua kelas selesai absen (v1.7.8)
- [x] Broadcast absensi KBM ke wali kelas secara paralel (v1.7.10)
- [x] Respon asinkron broadcast (200 OK instan, WA di background) — anti cURL timeout (v1.7.11)
- [x] Penanda `⚠️ [#Pembaruan Absensi]` saat update absen di hari yang sama (v1.7.13)
- [x] Filter siswa nonaktif di lembar absensi kelas & rekap (v1.8.0)
- [x] Lewati Minggu & Hari Libur otomatis (v1.8.8)

### 📄 D. PDF Rekap Absensi Bulanan (v1.6.9–v1.6.17)
- [x] Cetak PDF dari dashboard (modal pilih Bulan & Tahun)
- [x] Layout Landscape A4
- [x] Kop Surat fisik overlay (Portrait + Landscape auto-detect)
- [x] Logo overlay presisi tinggi (koordinat dari template PDF)
- [x] Format hitam putih (tanpa zebra striping, hemat tinta)
- [x] Header tabel 2 baris (merged cells: "Periode Bulan" & "Rekap")
- [x] Kolom NISN (bukan NIS), lebar 60pt
- [x] Highlight hari Minggu (merah header + pink background)
- [x] Margin 1.27cm (36pt) semua sisi, tabel lebar 770pt
- [x] Metadata di atas tabel (Tahun Pelajaran, Wali Kelas, Pengunduh)
- [x] 18 baris siswa per halaman pertama
- [x] Tanda tangan Kepala Sekolah dihapus

### 🏭 E. Modul PKL (Praktik Kerja Lapangan)
- [x] CRUD Kelompok PKL (tempat, ketua, anggota, pembimbing)
- [x] Laporan harian oleh Ketua PKL
- [x] Form dinamis per status: Hadir (jurnal+foto), Sakit (alasan+foto bukti), Izin (alasan+foto), Alpha (status hubungi)
- [x] Jurnal minimal 75 karakter
- [x] Upload foto per siswa + validasi
- [x] Pelacakan lokasi presensi (input manual teks, bukan GPS)
- [x] Toggle libur dengan alasan + tombol simpan cepat (v1.6.21)
- [x] Lock view laporan terkirim + tombol "Ubah Laporan"
- [x] Cetak PDF PKL (dikunci 7 hari dari tanggal laporan)
- [x] Admin bisa hapus laporan PKL (+ cleanup foto + cleanup absensi)
- [x] Detail modal (kehadiran + jurnal + foto semua anggota)
- [x] 3 stat card: total / sudah lapor / belum lapor
- [x] Guru hanya lihat kelompok bimbingannya
- [x] Sinkronisasi foto ke Supabase Storage (`photo_sync.js`)
- [x] Preview laporan mingguan PKL (read-only anggota, tanpa cetak) (v1.6.18)
- [x] Cetak PDF rekap mingguan PKL — 1 hari = 1 halaman, ~6 halaman/minggu (v1.6.18)
- [x] Manajemen Peran Siswa di dashboard admin (dropdown & edit manual) (v1.6.19)
- [x] Pemilih Ketua via searchable Single Student Picker (v1.6.19)
- [x] Auto-sync role siswa di DB saat kelompok dibuat/diedit/dihapus (v1.8.3)
- [x] Auto-default kelas XII → `anggotapkl` (v1.8.3)
- [x] Widget Absensi PKL ketua → status card premium clickable (v1.6.20)
- [x] Tombol simpan cepat libur di bawah input alasan (v1.6.21)
- [x] Format broadcast WA libur: nama anggota + kelas ketua (v1.6.21)
- [x] Input jurnal/sakit/izin/alpha textarea auto-resize (v1.6.22)
- [x] Kolom lokasi presensi wajib saat PKL masuk, opsional saat libur (v1.6.22)
- [x] Integrasi lokasi ke broadcast WA + modal detail admin (v1.6.22)
- [x] Counter karakter (X/75) interaktif + placeholder contoh isian (v1.6.23)
- [x] Penguncian form sebelum lokasi terisi (v1.6.24)
- [x] Notifikasi berhasil dengan waktu lokal WIB (v1.6.24)
- [x] Penanda `⚠️ [#Perubahan Laporan]` di WA saat update (v1.6.24)
- [x] Layout PDF Harian satu halaman penuh (Compact Card) (v1.6.24)
- [x] Tombol pintasan "Cetak Harian" di admin dashboard (v1.6.24)
- [x] Fix SQLite disk I/O error — WAL → DELETE mode untuk exFAT (v1.6.24)
- [x] Ketua PKL bersifat opsional (v1.7.0)
- [x] Tombol aksi laporan sejajar horizontal flexbox (v1.7.15)
- [x] Pembatasan pilihan siswa hanya kelas XII aktif (v1.8.3)
- [x] Import kelompok PKL via Excel + template SheetJS (v1.8.4)
- [x] Keamanan: `ketua_phone` default `""` bukan `null` (v1.8.4)
- [x] Import PKL per siswa dengan auto-grouping per `Tempat Pkl` (v1.8.5)
- [x] Kompresi client-side foto di HP siswa (`browser-image-compression`) (v1.9.0)
- [x] Offload PDF harian PKL ke Google Apps Script (v1.9.0)
- [x] Laporan susulan maksimal H-7 (siswa) & bypass takeover admin/guru (v1.9.2)
- [x] ~~Puppeteer HTML → PDF~~ 🔙 Rollback ke PDFKit (v1.8.9→v1.8.10, performa Intel Atom)
- [x] ~~Offload PDF harian PKL ke Google Apps Script~~ 🔙 Akan dihapus (v1.10.0, hasil rapat 12 Jun)
- [x] 🔄 **v1.10.0:** Ubah foto per siswa → 1 foto per kelompok + bukti absensi
- [x] 🔄 **v1.10.0:** Hapus PDF realtime PKL (harian & mingguan jurnal/dokumentasi)
- [x] 🔄 **v1.10.0:** Halaman Riwayat Laporan PKL (HTML preview + CSS print + print browser)
- [x] 🔄 **v1.10.0:** Cleanup dead code PDF generator (3 fungsi) + GAS dependency

### 📅 F. Kalender Akademik & Hari Libur
- [x] CRUD kalender (admin)
- [x] Read-only semua role
- [x] Data semester Ganjil & Genap
- [x] Widget di dashboard utama
- [x] Tombol simpan di bawah tabel (admin)
- [x] Koneksi ke master tabel `tahun_pelajaran` (v1.8.2)
- [x] Hari Libur Management — CRUD admin, API realtime (v1.8.6)
- [x] Merge Hari Libur ke Kalender Akademik — tabbed UI, dedup sidebar (v1.8.7)
- [x] Notifikasi KBM kondisional (skip saat libur) (v1.8.8)

### 🏆 G. Prestasi Siswa
- [x] CRUD prestasi (admin + guru)
- [x] Read-only semua role (universal)
- [x] Kategori: akademik, non-akademik, lomba, dll

### ⚠️ H. Pelanggaran & BK
- [x] Input pelanggaran + poin (guru_bk)
- [x] Poin pelanggaran tampil individual + total
- [x] Catatan BK — CRUD (guru_bk), Read-only (semua siswa)
- [x] Search pelanggaran (guru)

### 💬 I. Kotak Suara / Feedback
- [x] Kirim feedback (siswa)
- [x] Anonim untuk non-admin
- [x] Auto-publish feedback baru
- [x] Filter kata vulgar
- [x] Feedback terlihat oleh pengirimnya

### 📱 J. WhatsApp Bot & Broadcast
- [x] Koneksi via Baileys (QR code)
- [x] Notifikasi & broadcast saja — tanpa auto-reply (v1.6.20)
- [x] WA Panel ringan (~160 baris HTML) di port 7860
- [x] Format broadcast: pembimbing, jam WIB, ringkasan kehadiran
- [x] "Pengaturan WA" terpusat di dashboard admin port 8080 (v1.7.0)
- [x] Status koneksi bot real-time & scan QR di dashboard (v1.7.0)
- [x] Logout sesi & pairing nomor baru dari dashboard (v1.7.0)
- [x] Editor template pesan — klik tag variabel & character counter (v1.7.0)
- [x] Whitelist nomor WA (CRUD + Excel import) di Pengaturan WA (v1.7.0)
- [x] Deteksi grup WA live & penetapan target JID via UI (v1.7.0)
- [x] Log audit chat WA real-time via Socket.io (v1.7.5)
- [x] Join grup WA baru via link undangan dari dashboard (v1.7.5)
- [x] Tombol "Set Keduanya" untuk grup terpusat KBM & PKL (v1.7.7)
- [x] Redirect tab Template setelah simpan (v1.7.9)
- [x] 7 template pesan diperbarui: ringkas, tanpa garis pembatas (v1.7.12)
- [x] Broadcast KBM otomatis ke grup WA setelah absensi (v1.7.1)
- [x] Rekap konsolidasi sekolah setelah semua kelas lapor (v1.7.8)
- [x] Broadcast wali kelas paralel (v1.7.10)
- [x] Async response anti timeout (v1.7.11)
- [x] Penanda [#Pembaruan Absensi] untuk update (v1.7.13)
- [x] Lewati Minggu & Hari Libur otomatis (v1.8.8)
- [x] Kombo-produksi force config (anti ban) (v1.8.8)
- [x] ~~AI parser 9Router~~ Refactor ke REST agnostik (v1.8.6)
- [x] ~~Auto-reply AI chatbot~~ ❌ Diarsip (produksi = notifikasi only)
- [ ] 💡 Notifikasi ke orang tua/wali siswa? (saran)

### ⏰ K. Cron Jobs
- [x] 00:30 — Backup DB SQLite + cleanup > 7 hari
- [x] 08:30 — Pengingat kelas belum di-absen
- [x] 16:00 — Pengingat jurnal PKL ke Ketua
- [x] 19:00 — Eskalasi laporan PKL ke Pembimbing
- [x] ~~23:59 — Auto-alpha~~ (DINONAKTIFKAN, default = Hadir)
- [x] Manajemen cron dinamis tersimpan di SQLite & reload tanpa restart (v1.7.0)
- [x] Pembuatan/penghapusan cron kustom via UI (v1.7.0)

### 📊 L. Dashboard & UI/UX
- [x] Shortcut card adaptif per role
- [x] Widget Jam Real-Time
- [x] Banner Dapodik di navbar (`TP [Tahun] ([Semester])`)
- [x] Welcome message role-aware
- [x] Notifikasi pintar (kelas belum absen, PKL belum lapor)
- [x] Widget Kalender Akademik semester aktif
- [x] Footer compact mobile
- [x] Sticky header + sidebar mobile close button
- [x] Logo sekolah & tagline di login (mobile-first rem units)
- [x] Navbar nama+role
- [x] Card "Siswa PKL Hadir" terpisah dari KBM (v1.7.14)
- [x] Total siswa hanya menghitung siswa aktif (v1.8.0)
- [x] Dashboard Analytics (Chart.js) — tren absensi, pelanggaran, progress PKL (v1.8.8)
- [ ] 💡 Dark mode dashboard? (saran)

### 📆 M. Tahun Pelajaran (v1.6.0+)
- [x] Tabel `tahun_pelajaran` (nama, semester, tanggal, is_active)
- [x] Manajemen TP aktif (admin only)
- [x] Filter `tahun_pelajaran_id` di 8 tabel transaksional
- [x] Global data sharing via BaseController
- [x] Terintegrasi di 7 controller + Node.js cron/db
- [x] Penyederhanaan: 1 tahun pelajaran tanpa semester di UI (v1.8.1)
- [x] Auto-hardcode 'Ganjil' di backend (SQLite CHECK constraint) (v1.8.1)
- [x] Transisi tahun ajaran baru (v1.8.0)
- [x] Koneksi Kalender ↔ Tahun Pelajaran (v1.8.2)
- [x] Update nama kelas (TKR→TKRO, XITKJ→XI TKJ, AK→AKL) (v1.8.1)

### 🗄️ N. Database & Backup
- [x] SQLite lokal (`siakanuda.db`) — better-sqlite3
- [x] Supabase cloud sync (PostgreSQL)
- [x] Foto PKL dual storage (lokal + Supabase)
- [x] Cron backup harian (00:30 WIB)
- [x] CI4 Spark command `db:backup`
- [x] Migrasi `tahun_pelajaran_id` ke 8 tabel (v1.6.0)
- [x] Schema auto-init (`getActiveTahunPelajaran()`) (v1.6.0)
- [x] Tabel `wali_kelas` (relasi guru ↔ kelas) (v1.6.8)
- [x] Tabel `hari_libur` (v1.8.6)
- [x] SQLite disk I/O fix — WAL → DELETE mode untuk exFAT (v1.6.24)

### ⚡ O. Optimasi Performa
- [x] Sharp auto-compress foto di server (v1.6.18)
- [x] Kompresi client-side (`browser-image-compression`) di HP siswa (v1.9.0)
- [x] GAS offload PDF harian PKL (v1.9.0)
- [x] Async HTTP response WA — 200 OK instan (v1.7.11)
- [x] Parallel broadcast wali kelas (v1.7.10)
- [x] WA Panel lightweight (~160 baris, dari SPA 1357 baris) (v1.5.0)
- [x] ~~Puppeteer PDF~~ 🔙 Rollback ke PDFKit (performa Intel Atom N455) (v1.8.10)

### 🚀 P. Deployment & Infrastruktur
- [x] Systemd services — `bot.siswa.service` + `siakadash.service`
- [x] Nginx Reverse Proxy — local HTTP to 8080 (v1.11.0)
- [x] Tailscale VPN (`100.110.83.48`)
- [x] ~~Cloudflare Tunnel — akses internet via domain (v1.7.0)~~ (Digantikan v1.11.0)
- [x] Cloudflare Tunnel (cloudflared) terhubung ke Nginx port 80 (v1.11.0)
- [x] Domain kustom gratis DigitalPlat `https://siakanuda.qzz.io/` (v1.11.0)
- [x] Shortlink custom `https://s.id/siakanuda` (v1.11.0)
- [x] APK WebView → HTTPS Cloudflare (`BUILD-APK.bat` di-update)
- [x] Dynamic baseURL — deteksi `HTTP_HOST` otomatis (v1.6.5)
- [x] Tombol Kembali dinamis di navbar APK
- [ ] ~~CI/CD pipeline~~ ❌ Tidak direncanakan


### 📥 Q. Import & Export Data
- [x] Import siswa via Excel (default login NISN) (v1.6.8)
- [x] Import guru via Excel (v1.6.8)
- [x] Download template XLSX resmi (v1.6.8)
- [x] Download data siswa aktif (.xlsx) via SheetJS (v1.8.0)
- [x] Import kelompok PKL via Excel + template (v1.8.4)
- [x] Import PKL per siswa (auto-grouping) (v1.8.5)
- [x] Logic insert vs update (tidak reset password lama) (v1.8.0)
- [x] Opsi bulk nonaktifkan siswa tidak terdaftar (v1.8.0)

### 📚 R. Dokumentasi & Tooling
- [x] `AI_CONTEXT.md` — onboarding AI (Single Source of Truth)
- [x] `AGENTS.md` — aturan agen AI (universal semua model)
- [x] `ROADMAP.md` — rencana pengembangan (v1.9.0)
- [x] `STATUS_FITUR.md` — **dokumen ini**
- [x] `docs/archive/` — arsip blueprint lama (10 file)
- [x] `PROMPT_TEMPLATE.md` — template instruksi AI
- [x] `tests/` — E2E Testing dengan Playwright (Login, KBM, PKL) (v1.10.4)

### 📱 S. APK Android & BKK Portal
- [x] WebView wrapper → port 8080
- [x] `BUILD-APK.bat` + `GANTI-IP.bat`
- [x] BKK Portal di `public/bkk/` — workspace lokal, deploy terpisah ke github.io

---

## 📦 Database: 16 Tabel SQLite

| # | Tabel | Fungsi | TP Filter |
|---|-------|--------|-----------|
| 1 | `students` | Data siswa (NISN, nama, kelas, password bcrypt) | — |
| 2 | `allowed_numbers` | Data guru/staf (phone, role, tugas_tambahan) | — |
| 3 | `attendance` | Absensi KBM harian per siswa | ✅ |
| 4 | `attendance_kelas` | Rekap absensi per kelas per hari | ✅ |
| 5 | `attendance_pkl` | Laporan PKL harian (jurnal + foto JSON) | ✅ |
| 6 | `kelompok_pkl` | Kelompok PKL (tempat, anggota, pembimbing) | ✅ |
| 7 | `violations` | Poin pelanggaran siswa | ✅ |
| 8 | `counseling` | Catatan guru BK | ✅ |
| 9 | `schedules` | Jadwal pelajaran | ✅ |
| 10 | `feedbacks` | Kotak suara / saran | ✅ |
| 11 | `logs` | Log chat WA bot | — |
| 12 | `sessions` | Sesi auth WA Baileys | — |
| 13 | `kalender_akademik` | Kalender per semester/bulan/pekan | — |
| 14 | `achievements` | Prestasi siswa | — |
| 15 | `tahun_pelajaran` | Master tahun pelajaran aktif | — |
| 16 | `hari_libur` | Hari libur & penonotifasian KBM | — |

> 8 tabel transaksional memiliki kolom `tahun_pelajaran_id` (ditandai ✅).

---

## 👥 Matriks Akses Per Role

| Fitur | admin | kepsek | guru | guru_bk | siswa | ketua_pkl | anggota_pkl |
|-------|:-----:|:------:|:----:|:-------:|:-----:|:---------:|:-----------:|
| CRUD User | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Hapus Data | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| WA Panel & Config | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Message Log | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Tahun Pelajaran | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Analytics | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Absensi KBM (write) | ✅ | ❌ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Absensi KBM (read) | ✅ | ✅ | ✅ | ✅ | 👤 | 👤 | 👤 |
| Pelanggaran (write) | ✅ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |
| Pelanggaran (read) | ✅ | ✅ | 🔍 | ✅ | 👤 | 👤 | 👤 |
| Catatan BK | ✅ | ✅ | ❌ | ✅ | 👁️ | 👁️ | 👁️ |
| Jadwal | ✅ | ✅ | 👁️ | 👁️ | 👁️ | 👁️ | 👁️ |
| Prestasi | ✅ | ✅ | ✅ | ✅ | 👁️ | 👁️ | 👁️ |
| Kalender | ✅ | 👁️ | 👁️ | 👁️ | 👁️ | 👁️ | 👁️ |
| Kotak Suara | ✅ | 👁️ | 👁️ | 👁️ | ✏️ | ✏️ | ✏️ |
| PKL Kelompok | ✅ | ✅ | 🏫 | ✅ | ❌ | ✅ | 👁️ |
| PKL Laporan | ✅ | ✅ | 🏫 | ✅ | ❌ | ✅ | 👁️ |
| Cetak PDF PKL | ✅ | ✅ | 🏫 | ✅ | ❌ | ✅ | ❌ |

> 👤 = personal only · 👁️ = read-only · 🔍 = search only · 🏫 = kelompok bimbingan only · ✏️ = kirim saja

---

## 🗓️ Riwayat Rilis (Ringkasan dari Git)

| Versi | Tanggal* | Commit | Highlight |
|-------|----------|--------|-----------|
| **v1.0.0** | 28 Mei | 1 | Fondasi WA Bot + Dashboard Vanilla JS |
| **v1.1.0** | 29 Mei | 1 | PWA + APK Android + Mobile-First |
| **v1.2.0–v1.2.10** | 30 Mei | ~12 | CI4 Dashboard, CRUD, PKL inline, role sidebar |
| **v1.3.0** | 30 Mei | ~4 | 7 Role system, login redesign, mobile UI |
| **v1.4.0–v1.4.5** | 30 Mei | ~7 | Kalender, prestasi, kotak suara, PKL foto |
| **v1.5.0–v1.5.2** | 31 Mei | ~5 | WA Panel rombak, security hardening |
| **v1.6.0–v1.6.24** | 1–3 Jun | ~55 | Tahun pelajaran, PDF premium, PKL lengkap |
| **v1.7.0–v1.7.15** | 4–5 Jun | ~30 | WA config dashboard, KBM broadcast, template |
| **v1.8.0–v1.8.8** | 5–7 Jun | ~25 | Tahun ajaran, import Excel, analytics, hari libur |
| **v1.8.9–v1.8.10** | 7 Jun | 2 | Puppeteer percobaan → rollback ke PDFKit |
| **v1.9.0** | 9 Jun | ~5 | Kompresi client-side + GAS PDF offload |

> *Tanggal berdasarkan timestamp commit (2026). Total: **162 commit**.

---

## 📝 Changelog — 25 Commit Terakhir (Auto-Generated)

> ⚙️ Bagian ini di-generate otomatis oleh `npm run docs`. Jangan edit manual.

<!-- AUTO_CHANGELOG_START -->

| # | Hash | Tanggal | Pesan Commit |
|---|------|---------|-------------|
| 1 | `a13f97e` | 18 Jun | docs(v1.11.1): tambah README GitHub profesional + perbaiki .gitignore untuk public repo |
| 2 | `88f18d8` | 18 Jun | docs(v1.11.1): tambah SOP deploy, troubleshooting guide, dan health-check script |
| 3 | `8bc05fc` | 18 Jun | fix(v1.11.1): Socket.io & API frontend auto-detect origin untuk production Nginx proxy |
| 4 | `05c5493` | 18 Jun | fix(v1.11.1): perbaiki Supabase auth state (createClient) & banner versi dinamis |
| 5 | `810c947` | 18 Jun | chore(v1.11.1): sinkronisasi versi & dokumentasi + perbaikan akses role guru |
| 6 | `d5bda89` | 15 Jun | feat(v1.11.1): revisi cetak rekap absensi individu (PDF 5 Bulan gabung), perbaiki bug startYear, dan sinkronisasi nama ketua dengan nomor HP |
| 7 | `bdd17e6` | 15 Jun | feat(v1.9.1): Penambahan halaman web darurat offline Cloudflare Pages dan revisi panduan deployment SSH di AGENTS.md |
| 8 | `4f65507` | 15 Jun | chore(v1.11.0): finalisasi Cloudflare Tunnel, domain DigitalPlat, shortlink s.id, reorganize archives, clean git tree |
| 9 | `94b40f2` | 14 Jun | chore: deploy success, reorganize archives, update docs, clean git tree |
| 10 | `2dcf46b` | 14 Jun | docs(v1.10.4): integrasi skenario E2E Playwright dan update STATUS_FITUR |
| 11 | `3521466` | 14 Jun | feat(v1.10.3): tambah link Profil & Ganti Password di navbar dan sidebar untuk semua role |
| 12 | `9037de7` | 13 Jun | chore: reorganisasi arsip - simpan legacy PDF script (rename detection) |
| 13 | `afcaac7` | 13 Jun | feat(v1.10.2): pisahkan data KBM dan PKL pada dashboard analytics & ubah grafik tren harian PKL |
| 14 | `a370c0b` | 13 Jun | feat(v1.10.1): perluas tracker rekapitulasi KBM menjadi 30 hari |
| 15 | `64bf44d` | 13 Jun | feat(v1.10.1): tambah tracker rekapitulasi 7 hari absensi kelas KBM |
| 16 | `8ed2cef` | 13 Jun | docs(v1.10.0): centang tugas ubah foto PKL menjadi per kelompok |
| 17 | `c56100a` | 13 Jun | chore(v1.10.0): remove legacy pdf-lib and pdfkit dependencies |
| 18 | `6ff5ed0` | 13 Jun | feat(v1.10.0): hapus PDF realtime PKL & migrasi cetak absensi KBM ke Web View HTML |
| 19 | `b810b04` | 12 Jun | feat(pkl): migrasi arsitektur PDF ke HTML & update UI foto kelompok (v1.10.0) |
| 20 | `53adfa4` | 12 Jun | chore(v1.9.2): restrukturisasi arsip & sinkronisasi dokumentasi |
| 21 | `5702619` | 12 Jun | feat(pkl): replace daily PDF with weekly recap buttons in admin view and grant Guru access |
| 22 | `79c981d` | 11 Jun | fix(pkl): hardcode absensi table to always start from July |
| 23 | `53ae318` | 11 Jun | style(pkl): fix PDF absensi alignment, widen TTD column, show real class and teacher name |
| 24 | `521cc03` | 11 Jun | feat(pkl): implement dynamic PDF generation for Rekap Absensi using GAS client |
| 25 | `461aff5` | 11 Jun | feat(v1.9.2): implementasi fitur laporan susulan PKL (H-7) dan mode takeover Admin/Guru beserta perbaikan UI Date Picker |

<!-- AUTO_CHANGELOG_END -->

---
## 🔮 Rencana Selanjutnya

### v1.10.0 — Perombakan PDF PKL (Hasil Rapat 12 Jun 2026)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 1 | Halaman Riwayat Laporan PKL | ✅ | HTML preview + filter bulan + detail + CSS `@media print` |
| 2 | Ubah foto per siswa → per kelompok | ✅ | 1 foto kelompok + bukti absensi (sakit/izin/alpha) |
| 3 | Hapus PDF realtime PKL | ✅ | Endpoint, route, controller, tombol view |
| 4 | Hapus GAS dependency | ✅ | `gas_pdf_client.js`, `absensi_pdf_builder.js`, `GAS_PDF_URL` |
| 5 | Cleanup dead code PDF | ✅ | 3 fungsi di `pdf_generator.js` |

### v2.0.0 — Prioritas Utama
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| ~~1~~ | ~~Modul Nilai / Rapor~~ | ❌ | Tidak diperlukan di SIAKANUDA (14 Jun 2026) |
| 2 | Laporan Bulanan PDF Otomatis | ⬜ | Rekap absen satu sekolah 1 file |
| 3 | Rate Limiting & Fail2ban | ⬜ | Proteksi brute-force login |

### v2.x — Jangka Panjang
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 4 | Perpustakaan Digital | ⬜ | Modul pinjam-meminjam buku |
| 5 | Push Notification (Firebase/OneSignal) | ⬜ | Pelengkap WA bot |
| 6 | Multi-Sekolah (SaaS) | ⬜ | Arsitektur multi-tenant |

### 💡 Saran Pengembangan
| # | Saran | Prioritas | Status |
|---|-------|-----------|--------|
| 1 | ~~Tanda tangan Wali Kelas di PDF~~ | — | ❌ Ditolak |
| 2 | Grafik/chart analytics (tren kehadiran) | Sedang | ✅ Done (v1.8.8) |
| 3 | Notifikasi WA ke orang tua/wali | Tinggi | 💡 Saran |
| 4 | Dark mode dashboard | Rendah | 💡 Saran |

---

## 📋 File yang Di-ignore (.gitignore)

| Kategori | Path | Alasan |
|----------|------|--------|
| Dependensi | `node_modules/`, `dashboard/vendor/` | Install ulang |
| Kredensial | `.env`, `dashboard/.env` | JANGAN commit! |
| Sesi WA | `sessions/`, `auth_info_multi/` | Scan QR ulang |
| Foto PKL | `uploads/` | Ada di Supabase |
| SQLite temp | `*.db-shm`, `*.db-wal` | Otomatis |
| Sementara | `.tmp/`, `scratch/` | File kerja |
| Backup DB | `backups/*.db` | Besar |
| Binary | `*.exe` | Download ulang |
| Log | `*.log`, `dashboard/writable/` | Otomatis |
| APK | `siakanuda-apk/` | Repo terpisah |
| IDE | `.vscode/`, `.idea/` | Personal |

---

## 🔗 File Penting (Quick Access)

| File | Fungsi |
|------|--------|
| `AI_CONTEXT.md` | Konteks lengkap untuk AI agent |
| `AGENTS.md` | Aturan kerja AI agent |
| `ROADMAP.md` | Rencana pengembangan |
| `STATUS_FITUR.md` | **File ini** — peta status fitur |
| `execution/server.js` | Entry point Node.js (67KB) |
| `execution/bot.js` | WhatsApp Bot (11KB) |
| `execution/pdf_generator.js` | PDF generator (33KB) |
| `execution/db.js` | Database adapter (64KB) |
| `execution/photo_sync.js` | Sync foto → Supabase (7KB) |
| `execution/cron_jobs.js` | Cron scheduler (15KB) |
| `dashboard/app/Controllers/Pkl.php` | Controller PKL terbesar (36KB) |
| `dashboard/app/Views/whatsapp_settings/index.php` | View terbesar (76KB) |
| `.env` + `dashboard/.env` | Credentials (JANGAN commit!) |

---

## 🖥️ Target Deployment

### Spesifikasi Server Produksi (Lenovo Debian)
- **Perangkat:** Lenovo Notebook (~2010), Intel Atom N455, 2GB RAM, 128GB SSD
- **Sistem Operasi:** Debian 13 (Trixie) Minimal CLI Headless
- **Service Manager:** Systemd (`bot.siswa.service` & `siakadash.service`)
- **Jaringan:** IP Statis LAN Sekolah (`10.10.11.37`) + Tailscale VPN (`100.110.83.48`)

### 2 Jalur Akses
| Jalur | Via | URL | Fitur |
|-------|-----|-----|-------|
| **Lokal (WiFi)** | LAN Sekolah | `http://10.10.11.37:8080` | Semua fitur + cetak PDF (foto asli) |
| **Internet** | Cloudflare Tunnel | `https://domain.tld` | Semua fitur — cetak PDF (foto thumbnail) |

---

> 📌 **Dokumen ini di-update setiap ada rilis versi baru.**
> Terakhir diperbarui: **18 Juni 2026** oleh Antigravity Agent.
