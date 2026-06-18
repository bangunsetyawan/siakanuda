# 🚀 SIAKANUDA — Catatan Rilis v1.7.0
> **Tanggal Rilis:** 6 Juni 2026  
> **Versi:** v1.7.0  
> **Tipe Pembaruan:** Konsolidasi Fitur & Peningkatan Fleksibilitas WhatsApp Bot

---

## 🌟 Ringkasan Rilis

Rilis **v1.7.0** memfokuskan pada sentralisasi kontrol penuh fitur WhatsApp bot langsung di dalam menu **"Pengaturan WA"** pada port 8080 (dashboard admin) untuk membebaskan administrator dalam mengelola template pesan, jadwal cron kustom, whitelisting nomor, dan target grup penerima notifikasi tanpa perlu mengakses terminal atau file konfigurasi secara manual. Selain itu, rilis ini juga menghapus batasan validasi kaku pada kelengkapan Ketua Kelompok PKL.

---

## 🛠️ Rincian Perubahan & Peningkatan

### 1. Kelompok PKL Tanpa Batasan Ketua Kelompok
* **Backend**: Validasi `ketua_phone` dilonggarkan di [KelompokPklModel.php](file:///e:/Antigravity/siakanuda/dashboard/app/Models/KelompokPklModel.php) dengan rule `permit_empty` menggantikan `required`.
* **Frontend**: Atribut form `required` pada pemilih ketua kelompok di modal Tambah/Edit pada [groups.php](file:///e:/Antigravity/siakanuda/dashboard/app/Views/pkl/groups.php) telah dihapus.
* **Tujuan**: Memungkinkan kelompok PKL didaftarkan terlebih dahulu meskipun belum ditentukan siapa ketua kelompoknya atau jika ketua kelompok tidak memiliki nomor WhatsApp aktif.

### 2. Konsolidasi Whitelist Nomor WA ke Pengaturan WA
* **Penyederhanaan Navigasi**: Menu sidebar **"Manajemen Guru"** (`/allowed-numbers`) disembunyikan/dihapus. Seluruh fitur pengelola whitelist dipindahkan sebagai tab **"Whitelist Nomor WA"** di bawah **"Pengaturan WA"**.
* **Fungsionalitas**: Mendukung pendaftaran nomor tunggal baru, pencarian instan nama/nomor, pembatalan akses, serta import massal data Excel (.xlsx/.xls/.csv) via library client-side *SheetJS*.
* **Pengalihan Otomatis**: Semua aksi tambah, update, cabut akses, dan import Excel akan otomatis mengalihkan admin kembali ke `/whatsapp-settings?tab=whitelist`.

### 3. Panel Grup WhatsApp & Pengaturan Target JID Live
* **Integrasi Baileys API**: Menambahkan helper `getParticipatingGroups` di [bot.js](file:///e:/Antigravity/siakanuda/execution/bot.js) untuk memanggil API Baileys `sock.groupFetchAllParticipating()` saat tab dibuka.
* **Antarmuka Interaktif**: Menampilkan daftar seluruh grup yang diikuti bot secara langsung, lengkap dengan tombol pintas untuk langsung menyalin JID grup tersebut sebagai **Target Broadcast PKL** (`BROADCAST_GROUP_JID`) atau **Target Peringatan Absensi KBM** (`SCHOOL_GROUP_JID`).
* **Penyimpanan Instan**: Menyimpan konfigurasi JID grup ke berkas `.env` dan memperbarui `process.env` memori Node.js secara instan tanpa memerlukan restart server bot.

### 4. Tambah/Hapus Jadwal Cron Dinamis Baru
* **Database Schema**: Menambahkan kolom `action` pada tabel `cron_configs` untuk memetakan nama tugas cron dengan trigger aksi sistem.
* **Peta Penjadwalan Dinamis**: [cron_jobs.js](file:///e:/Antigravity/siakanuda/execution/cron_jobs.js) memetakan eksekusi dynamic job berdasarkan nilai `action || key`.
* **Tugas Cron Kustom**:
  * Pengguna dapat menambahkan jadwal cron baru melalui tombol "Tambah Jadwal Baru" di dashboard.
  * Pilihan aksi terintegrasi dengan 4 runner sistem (early warning absensi, pengingat jurnal PKL, eskalasi pembimbing, dan auto-alpha).
  * Cron kustom dapat dinonaktifkan via toggle switch atau dihapus sepenuhnya dari tabel via tombol Hapus.

---

## 📂 Berkas yang Terpengaruh

* **Dashboard (CI4)**:
  * [KelompokPklModel.php](file:///e:/Antigravity/siakanuda/dashboard/app/Models/KelompokPklModel.php)
  * [groups.php](file:///e:/Antigravity/siakanuda/dashboard/app/Views/pkl/groups.php)
  * [template.php](file:///e:/Antigravity/siakanuda/dashboard/app/Views/layouts/template.php)
  * [AllowedNumber.php](file:///e:/Antigravity/siakanuda/dashboard/app/Controllers/AllowedNumber.php)
  * [Routes.php](file:///e:/Antigravity/siakanuda/dashboard/app/Config/Routes.php)
  * [WhatsappSettings.php](file:///e:/Antigravity/siakanuda/dashboard/app/Controllers/WhatsappSettings.php)
  * [index.php](file:///e:/Antigravity/siakanuda/dashboard/app/Views/whatsapp_settings/index.php)
* **Background Service (Node.js)**:
  * [bot.js](file:///e:/Antigravity/siakanuda/execution/bot.js)
  * [cron_jobs.js](file:///e:/Antigravity/siakanuda/execution/cron_jobs.js)
  * [db.js](file:///e:/Antigravity/siakanuda/execution/db.js)
  * [server.js](file:///e:/Antigravity/siakanuda/execution/server.js)
  * [package.json](file:///e:/Antigravity/siakanuda/package.json)
  * [.env](file:///e:/Antigravity/siakanuda/.env)

---

## 🚀 Panduan Verifikasi & Cara Test
Silakan lihat panduan verifikasi terperinci di berkas [walkthrough.md](file:///C:/Users/SMKNUDA/.gemini/antigravity/brain/cf150da3-155a-4e32-8480-a2f928afd118/walkthrough.md).
