# 🗺️ SIAKANUDA — Peta Status Fitur v1.8.6
> **Terakhir diperbarui:** 7 Juni 2026
> **Untuk siapa:** Developer solo yang perlu re-orient cepat tanpa baca ulang 300 baris.
> **Cara pakai:** Scan checklist → cari bagian yang relevan → langsung kerja.

---

## 🏗️ Arsitektur (2 Service)

| Service | Port | Teknologi | Fungsi |
|---|---|---|---|
| **Dashboard** | 8080 | CodeIgniter 4 (PHP) | UI utama, semua user masuk sini |
| **Background API** | 7860 | Node.js + Express | WA Bot, PDF, Cron, Sync (hidden) |

```
npm run dev              ← Start Node.js (port 7860)
php spark serve          ← Start CI4 (port 8080)
```

---

## ✅ Modul & Fitur — Status Lengkap

### 🔐 Autentikasi & Keamanan
- [x] Login tabbed: Tab "Siswa" (input NISN) + Tab "Guru" (dropdown nama)
- [x] Password default: Siswa = NISN, Guru = No. WA
- [x] Paksa ganti password (Ketua PKL pakai password default)
- [x] CSRF protection global (token randomized)
- [x] Session IP binding + regenerate on login
- [x] Role-based routing (`RoleFilter.php`)
- [x] Hardcoded passwords dihapus (semua via bcrypt DB)
- [x] CORS Node.js locked ke localhost:8080
- [x] SQL injection prevention (column whitelist)
- [x] Error sanitization (generik di production)
- [x] Validasi file upload (JPG/PNG, max 5MB)
- [x] Server-side validation di 12 model + 9 controller

### 👥 Manajemen User
- [x] CRUD Siswa (admin)
- [x] CRUD Guru/Staf — `allowed_numbers` (admin)
- [x] Import Excel (.xlsx) + Live Preview (Siswa & Guru)
- [x] Download template Excel resmi
- [x] Download data siswa aktif (.xlsx) langsung dari UI via SheetJS (v1.8.0)
- [x] Logic import memisahkan insert vs update agar tidak mereset password siswa lama (v1.8.0)
- [x] Opsi bulk nonaktifkan siswa yang tidak terdaftar di file Excel saat import (v1.8.0)
- [x] Reset password siswa ke default
- [x] Kolom `tugas_tambahan` di data guru
- [x] 7 role: admin, kepsek, guru, guru_bk, siswa, ketua_pkl, anggotapkl

### 📋 Absensi KBM
- [x] Input absensi per kelas per hari (guru)
- [x] Status: Hadir (H) / Sakit (S) / Izin (I) / Alpha (A)
- [x] Tombol "Hadir Semua" — auto-submit + redirect dengan parameter tanggal
- [x] Rekap personal siswa (kartu identitas + ringkasan + riwayat)
- [x] Admin bisa hapus data absensi
- [x] Jumlah siswa per kelas ditampilkan
- [x] Broadcast notifikasi KBM masuk otomatis ke grup WA sekolah saat absensi disimpan (v1.7.1)
- [x] Validasi keterangan wajib untuk Sakit/Izin/Alpha + nama siswa di broadcast WA (v1.7.7)
- [x] Rekap konsolidasi otomatis ke grup WA saat semua kelas selesai absen (v1.7.8)
- [x] Broadcast absensi KBM ke wali kelas secara paralel (v1.7.10)
- [x] Respon asinkron broadcast (200 OK instan, kirim WA di background) — anti cURL timeout (v1.7.11)
- [x] Penanda `⚠️ [#Pembaruan Absensi]` saat update absen di hari yang sama (v1.7.13)
- [x] Filter siswa nonaktif di lembar absensi kelas & rekap (v1.8.0)

### 📄 PDF Rekap Absensi Bulanan (v1.6.9–v1.6.17)
- [x] Cetak PDF dari dashboard (modal pilih Bulan & Tahun)
- [x] Layout Landscape A4
- [x] Kop Surat fisik overlay (Portrait + Landscape auto-detect)
- [x] Logo overlay presisi tinggi (koordinat dari template PDF)
- [x] Format hitam putih (tanpa zebra striping)
- [x] Header tabel 2 baris (merged cells: "Periode Bulan" & "Rekap")
- [x] Kolom NISN (bukan NIS), lebar 60pt
- [x] Highlight hari Minggu (merah header + pink background)
- [x] Margin 1.27cm (36pt) semua sisi, tabel lebar 770pt
- [x] Metadata di atas tabel (Tahun Pelajaran, Wali Kelas, Pengunduh)
- [x] 18 baris siswa per halaman pertama
- [x] Tanda tangan Kepala Sekolah dihapus

### 🏭 PKL (Praktik Kerja Lapangan)
- [x] CRUD Kelompok PKL (tempat, ketua, anggota, pembimbing)
- [x] Laporan harian oleh Ketua PKL
- [x] Form dinamis per status: Hadir (jurnal+foto), Sakit (alasan+foto bukti), Izin (alasan+foto), Alpha (status hubungi)
- [x] Jurnal minimal 75 karakter
- [x] Upload foto per siswa + validasi
- [x] Pelacakan lokasi presensi
- [x] Toggle libur dengan alasan
- [x] Lock view laporan terkirim + tombol "Ubah Laporan"
- [x] Cetak PDF PKL (dikunci 7 hari dari tanggal laporan)
- [x] Admin bisa hapus laporan PKL (+ cleanup foto + cleanup absensi KBM)
- [x] Detail modal (kehadiran lengkap + jurnal + foto semua anggota)
- [x] 3 stat card: total / sudah lapor / belum lapor
- [x] Guru hanya lihat kelompok bimbingannya
- [x] Sinkronisasi foto ke Supabase Storage (`photo_sync.js`)
- [x] Preview laporan mingguan PKL (read-only untuk anggota, tanpa cetak) (v1.6.18)
- [x] Cetak PDF rekap mingguan PKL (ketua_pkl + guru + admin). Format: 1 hari = 1 halaman, total ~6 halaman/minggu (Senin–Sabtu) (v1.6.18)
- [x] Manajemen Peran Siswa di dashboard admin (dropdown list & edit manual) (v1.6.19)
- [x] Pemilih Ketua Kelompok PKL via searchable Single Student Picker (v1.6.19)
- [x] Sinkronisasi otomatis peran siswa di DB (`siswa`/`ketua_pkl`/`anggotapkl`) saat kelompok PKL dibuat, diedit, atau dihapus, serta auto-default seluruh kelas XII menjadi `anggotapkl` (v1.8.3)
- [x] Widget Absensi PKL di dashboard utama ketua_pkl disederhanakan menjadi status card premium yang dapat diklik ke halaman laporan utama (v1.6.20)
- [x] Tombol Simpan Cepat libur tepat di bawah input alasan libur untuk efisiensi pengisian (v1.6.21)
- [x] Format broadcast WhatsApp saat libur menampilkan nama semua anggota kelompok satu baris & kelas Ketua Kelompok (v1.6.21)
- [x] Input jurnal, sakit, izin, dan alpha menggunakan textarea yang lebih besar dan auto-resize (v1.6.22)
- [x] Pengisian kembali kolom "Tempat Melakukan Presensi" (`location_data`) yang wajib saat PKL masuk dan opsional saat libur (v1.6.22)
- [x] Integrasi kolom lokasi presensi ke broadcast WhatsApp dan modal detail laporan admin (v1.6.22)
- [x] Perbaikan selector JS validator untuk textarea, penambahan counter karakter (X/75) interaktif, dan placeholder contoh isian (v1.6.23)
- [x] Peringatan 📍 Lokasi Melakukan Absensi PKL (Wajib) dan penguncian form absensi/jurnal sebelum lokasi terisi (v1.6.24)
- [x] Notifikasi berhasil dengan format waktu lokal lengkap (WIB) setelah submit absensi (v1.6.24)
- [x] Format notifikasi WA menyertakan kelas Ketua, lokasi absensi, dan penanda `⚠️ *[#Perubahan Laporan]*` ketika ada pembaruan laporan (v1.6.24)
- [x] Layout PDF Harian PKL satu halaman penuh (Compact Card Layout) dengan resolusi foto maksimal dan bersih dari emoji tidak dikenal (v1.6.24)
- [x] Tombol pintasan "Cetak Harian" di dashboard admin untuk mencetak/menguji cetak PDF untuk tanggal berapa pun (v1.6.24)
- [x] Resolusi `disk I/O error` pada SQLite dengan memigrasi journal_mode dari WAL ke DELETE demi kompatibilitas drive exFAT (v1.6.24)
- [x] Ketua Kelompok PKL bersifat opsional (tidak wajib diisi saat tambah/edit kelompok) (v1.7.0)
- [x] Tombol aksi laporan PKL sejajar horizontal dalam flexbox (v1.7.15)
- [x] Pembatasan daftar pilihan siswa (Ketua & Anggota) di Kelompok PKL agar hanya memuat siswa aktif kelas XII (v1.8.3)
- [x] Import data kelompok PKL secara massal via Excel (.xlsx) beserta template download dinamis via SheetJS (v1.8.4)
- [x] Keamanan database dengan memaksa `ketua_phone` default ke string kosong `""` (bukan `null`) saat create/edit/import kelompok PKL tanpa ketua (v1.8.4)
- [x] Import data kelompok PKL secara massal via Excel (.xlsx) dengan auto-grouping per siswa berdasarkan kesamaan `Tempat Pkl` (v1.8.5)


### 📅 Kalender Akademik
- [x] CRUD kalender (admin)
- [x] Read-only semua role
- [x] Data semester Ganjil & Genap TP 2025/2026
- [x] Widget di dashboard utama
- [x] Tombol simpan di bawah tabel (admin)
- [x] Menghubungkan daftar pilihan tahun & default selected ke master tabel `tahun_pelajaran` (memaksa pengisian ulang jika berganti tahun ajaran baru) (v1.8.2)

### 🏆 Prestasi Siswa
- [x] CRUD prestasi (admin + guru)
- [x] Read-only semua role (universal)
- [x] Kategori: akademik, non-akademik, lomba, dll

### ⚠️ Pelanggaran & BK
- [x] Input pelanggaran + poin (guru_bk)
- [x] Poin pelanggaran tampil individual + total
- [x] Catatan BK — CRUD (guru_bk), Read-only (semua siswa)
- [x] Search pelanggaran (guru)

### 💬 Kotak Suara / Feedback
- [x] Kirim feedback (siswa)
- [x] Anonim untuk non-admin
- [x] Auto-publish feedback baru
- [x] Filter kata vulgar
- [x] Feedback terlihat oleh pengirimnya

### 📱 WhatsApp Bot & Broadcast
- [x] Koneksi via Baileys (QR code)
- [x] Notifikasi & broadcast saja (tanpa menu chat interaktif / balas otomatis / auto-reply) (v1.6.20)
- [x] WA Panel ringan (~160 baris HTML) di port 7860
- [x] Format broadcast: nama pembimbing, jam WIB, ringkasan kehadiran
- [ ] ❓ Notifikasi ke orang tua/wali siswa? (saran)
- [x] Menu "Pengaturan WA" interaktif terpusat di dashboard admin port 8080 (v1.7.0)
- [x] Pemantauan status koneksi bot real-time & scan QR Code di dashboard (v1.7.0)
- [x] Fitur logout sesi WA bot dan pairing nomor baru langsung dari dashboard (v1.7.0)
- [x] Editor template pesan otomatis dengan klik tag variabel & character counter (v1.7.0)
- [x] Manajemen Whitelist Nomor WA (CRUD nomor terdaftar + Excel import SheetJS) dipindahkan sepenuhnya ke Pengaturan WA (v1.7.0)
- [x] Deteksi grup WA yang diikuti bot secara live & penetapan target JID via UI (v1.7.0)
- [x] Log audit chat WA real-time via Socket.io (v1.7.5)
- [x] Hubungkan grup WA baru via link undangan dari dashboard (v1.7.5)
- [x] Tombol "Set Keduanya" untuk satu grup terpusat KBM & PKL (v1.7.7)
- [x] Redirect tab Template Pesan setelah simpan agar perubahan langsung terlihat (v1.7.9)
- [x] 7 template pesan diperbarui: format ringkas, tanpa garis pembatas, footer seragam SIAKANUDA (v1.7.12)
- [x] Refactor AI parser ke 9Router OpenAI-compatible REST untuk auto-fallback & hemat token (v1.8.6)

### ⏰ Cron Jobs
- [x] 00:30 — Backup DB SQLite + cleanup > 7 hari
- [x] 08:30 — Pengingat kelas belum di-absen
- [x] 16:00 — Pengingat jurnal PKL ke Ketua
- [x] 19:00 — Eskalasi laporan PKL ke Pembimbing
- [x] ~~23:59 — Auto-alpha~~ (DINONAKTIFKAN, default = Hadir)
- [x] Manajemen cron job dinamis tersimpan di SQLite & reload scheduler tanpa restart (v1.7.0)
- [x] Pembuatan tugas cron kustom baru & penghapusan via UI yang dipetakan ke runner sistem (v1.7.0)

### 📊 Dashboard
- [x] Shortcut card adaptif per role
- [x] Widget Jam Real-Time
- [x] Banner Dapodik di navbar (`TP [Tahun] ([Semester])`)
- [x] Welcome message role-aware
- [x] Notifikasi pintar (kelas belum absen, PKL belum lapor)
- [x] Widget Kalender Akademik semester aktif
- [x] Status card absensi PKL ketua_pkl interaktif (v1.6.20)
- [x] Card "Siswa PKL Hadir" terpisah dari statistik KBM (v1.7.14)
- [x] Total siswa murni menghitung siswa aktif saja (v1.8.0)
- [x] **Hari Libur Management (v1.8.6)** — CRUD admin, API realtime, notifikasi KBM kondisional, label hari realtime
- [ ] ❓ Grafik/chart analytics (tren kehadiran, distribusi pelanggaran)? (saran)

### 📆 Tahun Pelajaran (v1.6.0)
- [x] Tabel `tahun_pelajaran` (nama, semester, tanggal, is_active)
- [x] Manajemen TP aktif (admin only)
- [x] Filter `tahun_pelajaran_id` di 8 tabel transaksional
- [x] Global data sharing via BaseController
- [x] Terintegrasi di 7 controller + Node.js cron/db
- [x] Penyederhanaan TP menjadi 1 tahun pelajaran saja tanpa opsi semester di UI (auto-hardcode 'Ganjil' di backend untuk SQLite CHECK constraint) (v1.8.1)

### 📱 APK Android
- [x] WebView wrapper → port 8080
- [x] `BUILD-APK.bat` + `GANTI-IP.bat`
- [x] Tombol Kembali dinamis di navbar

### 🌐 BKK Portal (Standalone)
- [x] Di folder `public/bkk/` — workspace edit lokal saja
- [x] Deploy terpisah ke github.io & smknudarussalam.sch.id/bkk
- [x] JANGAN deploy bersama SIAKANUDA

---

## 📦 Database: 16 Tabel SQLite

| # | Tabel | Fungsi |
|---|---|---|
| 1 | `students` | Data siswa (NISN, nama, kelas, password bcrypt) |
| 2 | `allowed_numbers` | Data guru/staf (phone, role, tugas_tambahan) |
| 3 | `attendance` | Absensi KBM harian per siswa |
| 4 | `attendance_kelas` | Rekap absensi per kelas per hari |
| 5 | `attendance_pkl` | Laporan PKL harian (jurnal + foto JSON) |
| 6 | `kelompok_pkl` | Kelompok PKL (tempat, anggota, pembimbing) |
| 7 | `violations` | Poin pelanggaran siswa |
| 8 | `counseling` | Catatan guru BK |
| 9 | `schedules` | Jadwal pelajaran |
| 10 | `feedbacks` | Kotak suara / saran |
| 11 | `logs` | Log chat WA bot |
| 12 | `sessions` | Sesi auth WA Baileys |
| 13 | `kalender_akademik` | Kalender per semester/bulan/pekan |
| 14 | `achievements` | Prestasi siswa |
| 15 | `tahun_pelajaran` | Master tahun pelajaran aktif |
| 16 | `hari_libur` | Manajemen hari libur & penonotifasian KBM |

> 8 tabel transaksional memiliki kolom `tahun_pelajaran_id`.

---

## 👥 Akses Per Role (Quick Reference)

| Fitur | admin | kepsek | guru | guru_bk | siswa | ketua_pkl | anggota_pkl |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| CRUD User | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Hapus Data | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| WA Panel | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Message Log | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
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
| Tahun Pelajaran | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |

> 👤 = personal only, 👁️ = read-only, 🔍 = search only, 🏫 = kelompok bimbingan only, ✏️ = kirim saja

---

## 💡 Saran Pengembangan (Belum Dikerjakan)

| # | Saran | Prioritas | Estimasi | Status |
|---|---|---|---|---|
| 1 | ~~Tanda tangan Wali Kelas di PDF rekap absensi~~ | — | — | ❌ Ditolak |
| 2 | PDF rekap **mingguan** PKL per kelompok (1 hari = 1 halaman) + Preview read-only anggota | Sedang | 4-5 jam | ✅ Done (v1.6.18) |
| 3 | Grafik/chart analytics di dashboard (tren kehadiran, pelanggaran) | Sedang | 4-6 jam | 💡 Saran |
| 4 | Notifikasi WA ke orang tua/wali siswa | Tinggi | 4-6 jam | 💡 Saran |
| 5 | Dark mode dashboard | Rendah | 3-4 jam | 💡 Saran |

### 📝 Spesifikasi #2: PDF Rekap Mingguan PKL
- **Format:** 1 hari = 1 halaman (Senin–Sabtu, ~6 halaman per PDF)
- **Isi per halaman:** Tanggal, daftar anggota, status kehadiran, jurnal/alasan, foto bukti
- **Cetak PDF:** Ketua PKL + Guru Pembimbing + Admin — **hanya dari WiFi sekolah** (pakai foto asli)
- **Preview di app:** Semua anggota kelompok bisa lihat (read-only, tanpa tombol cetak), **dari mana saja** (pakai foto thumbnail)
- **Kop Surat:** Overlay otomatis (Portrait) seperti PDF absensi KBM

---

## 🌐 Skema Deployment & Akses (Final)

### Topologi Jaringan

```
                    [ Internet / ISP ]
                           │
                    [ Mikrotik RB750Gr3 ]
                           │
              [ Switch TP-Link TL-SG3210 ]
                           │
         Server Debian ── 10.10.11.37 (LAN)
         ├── Port 8080 (CI4 Dashboard)
         ├── Port 7860 (Node.js API, internal)
         └── cloudflared (tunnel ke Cloudflare)
```

### 2 Jalur Akses

```
JALUR 1 — WiFi Sekolah (langsung):
  User → 10.10.11.37:8080 → Server Debian
  Kecepatan: ⚡ 1-5ms
  Foto: asli (dari disk Debian)
  Cetak PDF: ✅ aktif

JALUR 2 — Internet (lewat domain):
  User → siakanuda.domain.com → Cloudflare → tunnel → Server Debian
  Kecepatan: 🌐 50-200ms
  Foto: thumbnail (dari Supabase)
  Cetak PDF: ❌ disabled
```

### Cara Server Membedakan Lokal vs Internet

```
Request masuk
    │
    ├── Ada header "CF-Connecting-IP"?
    │   └── YA → dari INTERNET (via Cloudflare)
    │
    └── TIDAK ada header → cek IP source
        ├── 10.10.10.0/23 → WiFi SEKOLAH ✅ lokal
        ├── 100.64.0.0/10 → TAILSCALE ✅ lokal
        ├── 127.0.0.1     → LOCALHOST ✅ lokal
        └── lainnya       → anggap INTERNET
```

### Perbedaan Fitur Lokal vs Internet

| Fitur | WiFi Sekolah | Internet (domain) |
|---|---|---|
| Login, CRUD, absensi, jadwal, PKL | ✅ Sama | ✅ Sama |
| Dashboard, notifikasi, semua teks | ✅ Sama | ✅ Sama |
| Foto PKL (preview) | 🖼️ Asli (full quality) | 🖼️ Thumbnail (compressed) |
| Cetak PDF (dengan foto) | ✅ Aktif (foto HD) | ❌ Disabled + pesan info |
| Upload foto PKL | ⚡ Cepat (LAN) | 🌐 Lebih lambat |

> **Prinsip:** Semua fitur 100% sama kecuali kualitas foto dan cetak PDF.
> Kalau mau cetak → wajib dari WiFi sekolah.

---

## 📸 Skema Dual Storage Foto PKL (Final)

### Alur Upload & Penyimpanan

```
Siswa upload foto (max 5MB dari HP)
        │
        ▼
   Server Debian
        │
        ├──► uploads/pkl/ ── FOTO ASLI (full quality)
        │    Storage: ~12GB per 4 bulan PKL
        │    Kapasitas Debian: 90GB+ (aman 7+ angkatan)
        │
        └──► photo_sync.js ── COMPRESS via sharp
             Resize 640px, quality 50% (~50-100KB)
                    │
                    ▼
             Supabase Storage ── THUMBNAIL
             Storage: ~600MB per 4 bulan PKL
             Kapasitas free tier: 1GB (cukup + buffer)
```

### Lifecycle Foto

```
1. Siswa upload     → foto asli masuk Debian (permanen)
2. photo_sync.js    → auto-compress → upload thumbnail ke Supabase
3. Selama PKL       → Supabase thumbnail untuk preview dari mana saja
4. PKL selesai      → cleanup Supabase (kosongkan untuk angkatan baru)
5. Arsip di Debian  → tetap tersimpan permanen (backup sekolah)
```

### Kalkulasi Storage

| Lokasi | Per hari | 4 bulan PKL | Kapasitas | Status |
|---|---|---|---|---|
| **Debian** (asli) | 60 × ~2MB = 120MB | ~12GB | 90GB+ | ✅ Aman 7+ angkatan |
| **Supabase** (thumbnail) | 60 × ~80KB = 4.8MB | ~480MB | 1GB free | ✅ Cukup + buffer |

### Dependency Baru

| Package | Fungsi | Ukuran | Beban server |
|---|---|---|---|
| `sharp` (npm) | Compress/resize foto | ~5MB install | ~30MB RAM sesaat, ~50ms/foto |

---

## 🚀 Setup Domain & Cloudflare Tunnel (1 kali)

### Yang perlu dilakukan:

| # | Langkah | Waktu | Dimana |
|---|---|---|---|
| 1 | Beli domain (atau pakai subdomain gratis) | 10 menit | Registrar domain |
| 2 | Pindahkan DNS ke Cloudflare (gratis) | 10 menit | dashboard.cloudflare.com |
| 3 | Install `cloudflared` di Debian | 5 menit | Server Debian (3 command) |
| 4 | Buat tunnel & arahkan ke localhost:8080 | 5 menit | Server Debian |
| 5 | (Opsional) DNS static di Mikrotik | 2 menit | Winbox Mikrotik |

> **Langkah 5 opsional:** Supaya user di WiFi sekolah yang ketik `siakanuda.domain.com` langsung resolve ke `10.10.11.37` tanpa keluar ke internet. Ini 1 baris di Mikrotik: `/ip dns static add name=siakanuda.domain.com address=10.10.11.37`

### Setelah setup selesai:
- **Tidak perlu maintenance** — tunnel jalan otomatis saat server boot
- **SSL/HTTPS gratis** — Cloudflare handle sertifikat
- **Tidak perlu buka port** di Mikrotik — tunnel dari dalam keluar
- **Beban server** — tambahan ~15MB RAM untuk `cloudflared`

---

## 🔗 File Penting (Quick Access)

| File | Fungsi |
|---|---|
| `AI_CONTEXT.md` | Konteks lengkap untuk AI agent |
| `README.md` | Riwayat versi (changelog) |
| `AGENTS.md` | Aturan kerja AI agent |
| `docs/siakanuda-v1.8.6.md` | Blueprint teknis aktif |
| `docs/STATUS_FITUR.md` | **File ini** — peta status fitur |
| `docs/debian_setup_guide.md` | Panduan setup server Debian |
| `execution/server.js` | Entry point Node.js (45KB) |
| `execution/bot.js` | WhatsApp Bot (Notification Only) (6KB) |
| `execution/pdf_generator.js` | PDF generator (32KB) |
| `execution/db.js` | Database adapter (55KB) |
| `execution/photo_sync.js` | Sync & compress foto → Supabase |
| `execution/cron_jobs.js` | 5 jadwal cron |
| `dashboard/app/Controllers/` | 15 controller CI4 |
| `.env` + `dashboard/.env` | Credentials (JANGAN commit!) |

---

## 📝 Changelog v1.8.6 (7 Juni 2026)

### ✨ Baru: Modul Hari Libur & Penonotifasian KBM Kondisional

| Fitur | Deskripsi |
|---|---|
| **Tabel `hari_libur`** | Tabel baru untuk manajemen hari libur (nasional, sekolah, cuti bersama) |
| **Admin CRUD UI** | Halaman `/hari-libur` lengkap: form tambah, tabel daftar, status (Hari Ini/Akan Datang/Lewat), hapus dengan konfirmasi |
| **Sidebar Menu** | Menu "Hari Libur" (ikon 📅 merah) hanya untuk Admin, di antara Log Audit WA dan Alumni & BKK |
| **API Endpoint** | `GET /api/holiday-status` → JSON `{ is_holiday, reason, type, date }` untuk JS realtime |
| **Dashboard Label Realtime** | Label hari di dashboard otomatis update setiap 60 detik via fetch API + fallback data PHP |
| **Kondisi Notifikasi KBM** | Jika hari libur aktif: notifikasi KBM ("X Kelas belum absen") **disembunyikan**, diganti info biru "Hari Ini Libur: [alasan] — Notifikasi KBM dinonaktifkan" |
| **PKL Tetap Aktif** | Notifikasi PKL (grup belum lapor) **tidak terpengaruh** hari libur (PKL bisa jalan di lapangan) |
| **Fallback Minggu** | Hari Minggu tetap otomatis dianggap libur (logika existing) + tambahan alasan custom dari DB |
| **Realtime 2 Tab** | Admin tambah libur di tab 1 → tab 2 label hari berubah otomatis < 60 detik tanpa reload |

### 🔧 Perubahan Teknis
- **4 File Baru**: `database/update_schema_hari_libur.sql`, `app/Models/HariLiburModel.php`, `app/Controllers/HariLibur.php`, `app/Views/hari_libur/index.php`
- **4 File Diubah**: `Dashboard.php` (skip notif KBM), `dashboard/index.php` (JS label + hidden data), `Routes.php` (rute Baru), `template.php` (sidebar menu)
- **SQLite Migration**: Tabel `hari_libur` + index `idx_hari_libur_date` sudah dieksekusi via PHP script

### 🎯 Verifikasi Cepat
```bash
# Tambah hari libur hari ini via CLI test:
sqlite3 siakanuda.db "INSERT INTO hari_libur (date, reason, type) VALUES ('2026-06-07', 'Tes Libur Sistem', 'sekolah');"
# Refresh dashboard → Label: 🛌 Hari Libur: Tes Libur Sistem | Notif KBM: HILANG | Notif PKL: TETAP
```

---

## 📝 Changelog v1.8.6 (7 Juni 2026)

### ✨ Baru: Modul Hari Libur & Penonotifasian KBM Kondisional

| Fitur | Deskripsi |
|---|---|
| **Tabel `hari_libur`** | Tabel baru untuk manajemen hari libur (nasional, sekolah, cuti bersama) |
| **Admin CRUD UI** | Halaman `/hari-libur` lengkap: form tambah, tabel daftar, status (Hari Ini/Akan Datang/Lewat), hapus dengan konfirmasi |
| **Sidebar Menu** | Menu "Hari Libur" (ikon 📅 merah) hanya untuk Admin, di antara Log Audit WA dan Alumni & BKK |
| **API Endpoint** | `GET /api/holiday-status` → JSON `{ is_holiday, reason, type, date }` untuk JS realtime |
| **Dashboard Label Realtime** | Label hari di dashboard otomatis update setiap 60 detik via fetch API + fallback data PHP |
| **Kondisi Notifikasi KBM** | Jika hari libur aktif: notifikasi KBM ("X Kelas belum absen") **disembunyikan**, diganti info biru "Hari Ini Libur: [alasan] — Notifikasi KBM dinonaktifkan" |
| **PKL Tetap Aktif** | Notifikasi PKL (grup belum lapor) **tidak terpengaruh** hari libur (PKL bisa jalan di lapangan) |
| **Fallback Minggu** | Hari Minggu tetap otomatis dianggap libur (logika existing) + tambahan alasan custom dari DB |
| **Realtime 2 Tab** | Admin tambah libur di tab 1 → tab 2 label hari berubah otomatis < 60 detik tanpa reload |

### 🔧 Perubahan Teknis
- **4 File Baru**: `database/update_schema_hari_libur.sql`, `app/Models/HariLiburModel.php`, `app/Controllers/HariLibur.php`, `app/Views/hari_libur/index.php`
- **4 File Diubah**: `Dashboard.php` (skip notif KBM), `dashboard/index.php` (JS label + hidden data), `Routes.php` (rute Baru), `template.php` (sidebar menu)
- **SQLite Migration**: Tabel `hari_libur` + index `idx_hari_libur_date` sudah dieksekusi via PHP script

### 🎯 Verifikasi Cepat
```bash
# Tambah hari libur hari ini via CLI test:
sqlite3 siakanuda.db "INSERT INTO hari_libur (date, reason, type) VALUES ('2026-06-07', 'Tes Libur Sistem', 'sekolah');"
# Refresh dashboard → Label: 🛌 Hari Libur: Tes Libur Sistem | Notif KBM: HILANG | Notif PKL: TETAP
```

