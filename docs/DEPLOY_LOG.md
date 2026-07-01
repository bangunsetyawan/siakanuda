# 📋 SIAKANUDA — Deploy Log

> Catatan riwayat deploy ke server Debian (`[SSH_USER]@[IP_SERVER_TAILSCALE]`).
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

### 29 Juni 2026 — v1.19.0 — `update_siakanuda_pwa.tar.gz`
- **Commit:** `4c792d3`, `8ec6fae`
- **File:**
  - `dashboard/app/Controllers/Auth.php`
  - `dashboard/app/Views/auth/login.php`
  - `dashboard/app/Views/pkl/monitoring_add.php`
  - `dashboard/app/Views/pkl/monitoring_edit.php`
- **Restart:** (Tidak wajib)
- **Catatan:**
  - PWA: Fix bug login memantul (Safari/Mobile) dengan field statis dan tab-state memori.
  - PKL Monitoring: Lock form sampai GPS ada, timeout fallback 20s, dan kompresi foto klien.

---

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

### 23 Juni 2026 — v1.18.0 — `patch_v1180_docs.tar.gz`
- **Commit:** `045c1cd`
- **File:**
  - `dashboard/app/Controllers/Bkk.php`
  - `dashboard/app/Views/bkk/data_alumni.php`
  - `dashboard/app/Views/bkk/dashboard.php`
  - `dashboard/app/Views/auth/login.php`
  - `dashboard/app/Views/dashboard/index.php`
  - `dashboard/app/Views/layouts/template.php`
  - `package.json`
  - `DEPLOY_LOG.md`
- **Restart:** `siakadash.service` (opsional)
- **Catatan:**
  - Integrasi penuh modul BKK & Tracer Study ke dalam ekosistem CI4 murni
  - Fitur Import Excel (Hybrid dengan SheetJS + endpoint API CI4)
  - Fitur Export Excel terintegrasi (DataTables Buttons)
  - Form CRUD 23 Kolom interaktif via Pop-up Modal untuk keamanan data
  - Pembersihan bug Mojibake (UTF-8) di seluruh Dashboard
  - Update dependensi struktur database dari Supabase menjadi full SQLite lokal

---

### 26 Juni 2026 — v1.16.1 — `patch_wa_ui_20260626.tar.gz`
- **Commit:** `285044d`
- **File:**
  - `dashboard/app/Views/whatsapp_settings/index.php`
  - `DEPLOY_LOG.md`
- **Restart:** (Tidak wajib, UI otomatis berubah saat refresh browser)
- **Catatan:**
  - Redesign antarmuka Pengaturan WhatsApp menjadi struktur *Dashboard* (Kotak Menu).
  - Memisahkan form setiap template WA agar masing-masing memiliki tombol Simpan independen, mempermudah pengeditan dan mencegah kesalahan simpan massal.

---

### 26 Juni 2026 — v1.16.2 — `patch_test_bot_20260626.tar.gz`
- **Commit:** `caa0e70`
- **File:**
  - `dashboard/app/Views/whatsapp_settings/index.php`
  - `dashboard/app/Controllers/WhatsappSettings.php`
  - `execution/server.js`
  - `dashboard/app/Config/Routes.php`
- **Restart:** `bot.siswa.service` (Wajib direstart karena penambahan endpoint API Node.js)
- **Catatan:**
  - Penambahan fitur "Test Kirim Pesan" Bot WhatsApp langsung dari Dashboard.
  - Perbaikan UI WebSocket Javascript yang sebelumnya *crash* akibat ID elemen lama terhapus saat redesign.
  - Sistem validasi ketat pengecekan eksistensi nomor WA (`sock.onWhatsApp()`) di backend untuk mencegah *silent failure* (contoh: nomor kurang angka 8).
  - Controller PHP kini mem-parsing JSON *error message* dari Node.js alih-alih menampilkan pesan *error* generik.
  - Dukungan penuh pengujian kirim pesan ke **Grup WhatsApp** menggunakan Group JID (`@g.us`).

---

### 26 Juni 2026 — v1.18.0 — (Massive Deploy: WA Templates, UI PKL, OpenRouter)
- **File & Patch Terkait:**
  - `patch_template_db_20260626.tar.gz`
  - `patch_template_cleanup_20260626.tar.gz`
  - `patch_wa_templates_revised_20260626.tar.gz`
  - `patch_teacher_group_final_20260626.tar.gz`
  - `patch_photo_url_hotfix_20260626.tar.gz`
  - `patch_student_report_20260626.tar.gz`
- **Restart:** `bot.siswa.service` (Wajib untuk backend Node.js)
- **Catatan & Pembaruan:**
  - **Arsitektur Bot:** `bot.siswa.service` kini merangkap sebagai server AI dan Broadcast, menggantikan arsitektur lama yang terpisah. 9Router/OpenRouter API AI resmi terhubung (OpenAI-compatible).
  - **Pemisahan JID Grup:** Menambahkan input khusus JID Grup Guru Pembimbing di Dashboard agar notifikasi *real-time* laporan PKL hanya terpusat ke grup panitia, bukan grup Yayasan (yang difokuskan untuk rekap harian).
  - **Pembaruan Template WA:** Membersihkan template-template usang dari database (misal `pkl_masuk_broadcast`) dan menggantikannya dengan template granular (`pkl_masuk_siswa`, `pkl_masuk_pembimbing`, dsb) di UI Pengaturan WhatsApp.
  - **Perbaikan UI Laporan PKL Siswa:** Anak-anak kini bisa menghapus salah *upload* foto langsung dengan mencentang kotak 'Hapus Foto Ini' lalu menekan Simpan.
  - **Penyesuaian Aturan Absensi PKL:** Jika **seluruh** anggota berstatus Sakit, Izin, atau Alpha, form sistem tidak lagi mewajibkan unggah "Foto Dokumentasi Kelompok", melainkan menjadi opsional (Hanya foto bukti surat sakit/izin dari anggota yang bersangkutan yang diwajibkan).
  - **Bugfix Hotfix:** Memperbaiki insiden URL ganda (`https://siakanuda.qzz.iohttps://...`) karena masalah logika *fallback* lokal/Supabase Storage.

---

### 28 Juni 2026 — v1.18.1 — `patch_wa_anti_spam_20260628.tar.gz`
- **Commit:** `1abdae7`
- **File:**
  - `package.json`
  - `execution/server.js`
  - `execution/cron_jobs.js`
  - `execution/db.js`
  - `dashboard/app/Views/whatsapp_settings/index.php`
  - `docs/DEPLOY_LOG.md`
- **Restart:** `bot.siswa.service` (Wajib direstart karena perubahan berkas di execution/)
- **Catatan:**
  - Rombak total logika cron job: Pengecekan absensi KBM dan Pengingat Jurnal PKL **hanya** dikirim ke Grup WhatsApp sekolah (Grup KBM), meniadakan pengiriman DMs ke nomor pribadi siswa/Ketua PKL.
  - Mempertahankan logika pengiriman eskalasi peringatan PKL ke nomor pribadi Guru Pembimbing (menggunakan rekap konsolidasi agar terhindar dari spam beruntun), namun status aktifnya dinonaktifkan sementara di panel settings agar admin bebas mengaktifkannya sewaktu-waktu.
  - Mempertahankan logika pengiriman laporan PKL masuk ke nomor pribadi Instruktur industri, dikontrol langsung via toggle settings di dashboard.
  - Memperbarui skema inisialisasi SQLite (`db.js`) agar secara otomatis mengupdate nama, deskripsi, dan jadwal default (cron expression) di database agar UI sinkron dengan logika baru.
  - Memperbarui petunjuk teks ekspresi cron di View dashboard (`whatsapp_settings/index.php`).
  - Memperbaiki bug form bertumpuk (nested HTML forms) di halaman Pengaturan WA yang menyebabkan tombol "Hapus" tugas cron kustom tidak bekerja (malah memicu submit update form utama). Kini tombol Hapus memicu JavaScript POST via single hidden form.
  - Memperbarui template bawaan `pkl_masuk_grup`, `pkl_masuk_grup_guru`, dan `pkl_masuk_instruktur` di `db.js` untuk menambahkan informasi Ketua dan Guru Pembimbing sebelum bagian lokasi.
  - Mengimplementasikan helper pencocokan nomor telepon toleran (`cleanPhone`) untuk lookup nama Ketua PKL dan Guru Pembimbing agar yang tampil selalu nama lengkap mereka, bukan nomor HP/NISN.
  - Menyempurnakan UI Editor Template Pesan (`index.php`) dengan menambahkan panel informasi Target Kirim, Pemicu (Trigger), dan Deskripsi Fungsional pada tiap kartu template agar admin tidak bingung saat menyunting.
  - Menonaktifkan inisialisasi AI command parser (9Router) saat booting server karena chatbot pintar sudah tidak aktif di produksi.
  - Status: v1.18.1 siap dideploy.


---

### 29 Juni 2026 — v1.19.0 — `patch_pkl_monitoring_20260629.tar.gz`
- **File:**
  - `dashboard/app/Config/Routes.php`
  - `dashboard/app/Controllers/Pkl.php`
  - `dashboard/app/Models/MonitoringPklModel.php`
  - `dashboard/app/Views/layouts/template.php`
  - `dashboard/app/Views/pkl/monitoring.php`
  - `execution/db.js`
  - `docs/AI_CONTEXT.md`
  - `docs/STATUS_FITUR.md`
  - `docs/DEPLOY_LOG.md`
- **Restart:** `bot.siswa.service` + `siakadash.service`
- **Catatan:**
  - Menambahkan fitur dan menu baru "Monitoring PKL" bagi guru pembimbing untuk mendata kunjungan monitoring secara langsung di DU/DI.
  - CRUD monitoring_pkl lengkap dengan validasi form minimal 10 karakter catatan kunjungan.
  - Upload dokumentasi foto kunjungan maksimal 3 foto, terintegrasi dengan upload path lokal.
  - Tampilan dinamis per-role (guru pembimbing menginput dan melihat catatan monitoring miliknya, admin/kepsek dapat melihat keseluruhan, siswa melihat monitoring kelompoknya sendiri).

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

---

### 23 Juni 2026 - v1.17.0 - patch_bkk_native.tar.gz & Sync Patches
- **Tipe:** Pembaruan Mayor (Perombakan Arsitektur BKK)
- **File:**
  - dashboard/app/Config/Routes.php
  - dashboard/app/Controllers/Bkk.php
  - dashboard/app/Views/bkk/dashboard.php
  - dashboard/app/Views/bkk/data_alumni.php
  - dashboard/app/Views/bkk/mou_iduka.php
  - dashboard/app/Views/bkk/kunjungan_industri.php
  - dashboard/app/Views/bkk/mitra_industri.php
  - dashboard/app/Models/BkkAlumniModel.php
  - dashboard/app/Models/BkkMouModel.php
  - dashboard/app/Models/BkkKunjunganModel.php
  - database/schema_supabase_sync.sql
  - migrate_supabase.php
- **Fitur Baru & Perombakan:**
  - **Penghapusan Ekosistem Supabase & JS Standalone:** Modul BKK yang sebelumnya mengandalkan Supabase API dan Javascript murni di-frontend telah dirombak total menjadi aplikasi Native CodeIgniter 4 yang terintegrasi 100% dengan ekosistem SIAKANUDA (menggunakan layouts/template).
  - **Sinkronisasi Skema Database:** Nama tabel dan kolom BKK (Alumni, MOU, Kunjungan) telah disesuaikan 100% agar identik dengan struktur Supabase lama, memastikan migrasi data mulus tanpa kehilangan kolom.
  - **Script Migrasi Otomatis:** Menyertakan migrate_supabase.php untuk memfasilitasi penarikan massal (download) data dari REST API Supabase langsung ke dalam database SQLite lokal secara otomatis.
- **Perbaikan Bug (Hotfixes):**
  - **PHP 7/8 Compatibility:** Menghapus sintaks *Arrow Functions* (n() =>) di controller Bkk.php yang menyebabkan *Parse Error (Error 500)* pada server Debian dengan PHP versi 7.3/7.4.
  - **UTF-8 BOM Removal:** Membersihkan Byte Order Mark (BOM) tidak kasat mata dari file Models dan Views yang memicu *ErrorException: Namespace declaration statement has to be the very first statement*.
