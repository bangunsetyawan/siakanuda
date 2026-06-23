# 📋 SIAKANUDA — Deploy Log

> Catatan riwayat deploy ke server Debian (`smknuda@100.110.83.48`).
> File ini ada di **lokal (SSD)** dan **server** sebagai referensi.

---

## Aturan Deploy
- ✅ Git commit di lokal **sebelum** membuat patch
- ✅ Patch hanya berisi file yang berubah (bukan full deploy)
- ✅ Catat setiap deploy di file ini
- ❌ Jangan edit langsung di server
- ❌ Jangan `npm install` kecuali ada dependency baru

## Format Catatan
```
### [Tanggal] — [Versi] — [Nama Patch]
- **Commit:** [hash]
- **File:** [daftar file yang dipatch]
- **Restart:** [service yang di-restart]
- **Catatan:** [ringkasan perubahan]
```

---

## Riwayat Deploy

### 18 Juni 2026 — v1.11.1 — Full Deploy
- **Commit:** `8bc05fc`, `05c5493`, `810c947`
- **Metode:** Full `update.tar.gz` (5.4 MB) via SCP
- **Restart:** Semua service (stop → extract → npm install → start)
- **Catatan:**
  - Deploy pertama v1.11.1 ke server produksi
  - Fix Socket.io auto-detect origin, Supabase auth state, version banner
  - Fix Cloudflare Worker blocking, enable siakadash.service
  - Git tag `v1.11.1` dibuat
  - Website live di `siakanuda.qzz.io`

---

### 19 Juni 2026, 20:18 WIB — v1.11.2 — `patch_v1112.tar.gz`
- **Commit:** `8f528be`
- **File:**
  - `dashboard/app/Config/Routes.php`
  - `dashboard/app/Controllers/Pkl.php`
  - `dashboard/app/Models/AttendancePklModel.php`
  - `dashboard/app/Views/pkl/groups.php`
  - `dashboard/app/Views/pkl/index.php`
  - `execution/bot.js`
  - `execution/db.js`
  - `execution/server.js`
  - `package.json`
- **Restart:** `bot.siswa.service` + `siakadash.service`
- **Catatan:**
  - Fix format nomor HP Indonesia (08→628) di bot.js
  - Hapus TESTING_MODE — broadcast langsung ke guru asli
  - Force SQLite (useSupabase=false), Supabase hanya untuk Storage
  - Tambah fitur: hapus semua laporan PKL, kirim ulang notif WA
  - Tambah search filter & kolom jam lapor di halaman PKL
  - Tampilkan nama ketua (bukan hanya nomor) di UI PKL
  - Aktifkan timestamps (created_at/updated_at) di AttendancePklModel
  - Import PKL sekarang mendukung NISN sebagai identifier ketua

---

<!-- DEPLOY_LOG_END — Tambahkan entry baru di atas baris ini -->

### 20 Juni 2026, 00:06 WIB — v1.14.4 — `patch_v1144.tar.gz`
- **Commit:** `eecebe9`
- **File:**
  - `dashboard/app/Models/KelompokPklModel.php`
  - `dashboard/app/Models/StudentModel.php`
  - `dashboard/app/Views/students/index.php`
  - `dashboard/app/Views/whatsapp_settings/index.php`
  - `execution/db.js`
  - `execution/server.js`
  - `execution/seed_pkl_templates.cjs`
- **Restart:** `bot.siswa.service` + `siakadash.service`
- **Catatan:**
  - Menambahkan kolom `orang_tua_phone`, `pembimbing_phone`, `instruktur_phone`
  - Broadcast otomatis kini bisa dikirim spesifik ke Grup, Orang Tua, Ketua, Instruktur, & Pembimbing
  - Template pesan bot kini dinamis (mendukung Custom WA Templates per target)
  - Memperbaiki bug pengiriman Queue (ReferenceError `runAsync is not defined` di server.js)
  - **Catatan Uji Coba Tertunda:** Malam ini broadcast ke nomor pribadi belum terlihat masuk karena seluruh target (Orang Tua, Pembimbing, dll) diisi dengan **1 nomor yang sama**. WhatsApp memblokir pesan kembar beruntun tersebut sebagai *Spam*. Besok perlu dites ulang dengan **nomor yang berbeda-beda** untuk melihat hasil asli template WA-nya.
  - Error log `new row violates row-level security policy` pada upload foto ditandai murni karena Supabase Anon Insert Permissions yang belum dikonfigurasi (Hanya memengaruhi sync foto ke Cloud Storage, tidak merusak aplikasi lokal).

---

### 22 Juni 2026 — v1.15.0 — `patch_v1150.tar.gz`
- **File:**
  - `execution/bot.js`
  - `execution/server.js`
- **Restart:** `bot.siswa.service`
- **Catatan:**
  - Meningkatkan durasi antrean pesan WA (delay) menjadi 12-20 detik acak di `bot.js`.
  - Menyisipkan string `[Ref: XXXXXX]` secara dinamis di akhir tiap pesan broadcast (`server.js`) untuk mem-bypass filter pendeteksi Spam WhatsApp.
  - Memberikan panduan penggantian Supabase API Key menjadi Service Role Key.

---

### 22 Juni 2026 — v1.15.1 — `patch_v1151.tar.gz`
- **File:**
  - `dashboard/app/Views/layouts/template.php`
- **Restart:** (Tidak wajib, UI otomatis berubah saat refresh browser)
- **Catatan:**
  - Optimalisasi UI: Menghilangkan efek *glassmorphism* (blur) pada navbar yang berat.
  - Memperbaiki bug tata letak tombol (`.btn`) dan ikon yang keluar jalur dengan menghapus *override* CSS agresif (`inline-flex`) dan mengembalikannya ke kerangka responsif Bootstrap 4 / AdminLTE bawaan.
  - Meringankan efek bayangan (*box-shadow*) pada panel kartu.

---

### 22 Juni 2026 — v1.15.2 — `patch_v1152.tar.gz`
- **File:**
  - `dashboard/app/Views/layouts/template.php`
- **Restart:** (Tidak wajib, UI otomatis berubah saat refresh browser)
- **Catatan:**
  - Perbaikan Performa Ekstrem (HP): Menghapus `transition: all 0.3s ease` dari `.nav-link` dan tombol. Properti tersebut sebelumnya menyebabkan *Layout Thrashing* (lag/patah-patah) di CPU HP karena browser dipaksa menganimasi semua elemen setiap kali menu ditekan atau digeser.

---

### 22 Juni 2026 — v1.16.0 — `patch_v1160.tar.gz`
- **Tipe:** Pembaruan Mayor (Fitur Baru)
- **File:**
  - `dashboard/app/Views/pkl/student_report.php`
  - `dashboard/app/Views/pkl/index.php`
  - `dashboard/app/Views/pkl/riwayat.php`
- **Fitur Baru (Location Check-In GPS & Form Dinamis):**
  - Mengimplementasikan sistem pelacakan koordinat satelit GPS berpresisi tinggi (`enableHighAccuracy`) di formulir laporan PKL siswa.
  - Form Absensi kini bersifat dinamis dan sangat ketat: Saat tombol status ("Masuk PKL" atau "Libur") diklik, pengguna **wajib** mengirimkan lokasi GPS terlebih dahulu sebelum isian jurnal atau alasan libur bisa diakses/diketik (mencegah absen dari rumah).
  - Integrasi cerdas: Teks titik koordinat disematkan ke dalam *field* teks lokasi, otomatis diubah menjadi tombol "Buka Maps" di *dashboard* admin dan riwayat.
- **Pembaruan Riwayat Laporan & Hak Akses:**
  - Merombak halaman Riwayat Laporan (`riwayat.php`) untuk menampilkan *Galeri Mini* yang memuat seluruh foto lampiran (foto kelompok + surat dokter/izin anggota), tidak lagi hanya 1 foto utama.
  - Menghapus fitur redundan "Preview Data Mingguan" (sisa dari versi *pdf server* lama) agar *dashboard* sisi kanan lebih lega dan *to the point*.
  - Membuka akses penuh halaman **Riwayat Laporan Lengkap** beserta fitur **Cetak Rekap Absensi Individu** untuk **Anggota PKL biasa** (sebelumnya hanya ketua kelompok yang bisa mencetak dan melihat galeri utuh). Peran anggota kini naik level menjadi pemantau penuh, hanya dibatasi pada pengisian/pengiriman absen.
