# 🤖 Directive: WhatsApp Bot — Panduan Pengembangan (v1.2.3)
> SOP untuk AI agent saat memodifikasi `execution/bot.js`

Di versi 1.2.3, WhatsApp bot **disederhanakan penuh** untuk menjaga stabilitas dan efisiensi memori pada server produksi Lenovo Debian.

---

## 🏗️ Aturan Utama `bot.js`

### 1. Tanpa State Machine Interaktif
* **Logika state-machine interaktif dinonaktifkan/dihapus**.
* Bot tidak lagi memproses alur input multi-langkah (seperti absen PKL 4-langkah via chat, tanya-jawab, dll).
* Semua input absensi, pelanggaran, dan PKL **wajib dialihkan ke Web Dashboard CI4 (Port 8080)**.
* Jika ada siswa atau guru mengirim chat biasa ke bot, bot cukup merespon dengan pesan template otomatis:
  ```
  Halo! Untuk absensi, pelanggaran, dan jurnal PKL, silakan akses aplikasi resmi SIAKANUDA melalui tautan berikut:
  http://[IP-SEKOLAH]:8080
  ```

### 2. Fungsi Utama Bot (Notifikasi & Broadcast)
Bot hanya melayani tugas-tugas berikut di latar belakang:
* **Broadcast API** — Dipanggil dari dashboard CI4 via HTTP POST ke `http://localhost:7860/api/broadcast` untuk mengirim pesan/pengumuman penting.
* **Cron Jobs Notifikasi** — Otomatis mengirim laporan absensi kelas atau reminder PKL harian pada jam-jam yang ditentukan (`execution/cron_jobs.js`).
* **Shortcut Command Admin** — Hanya melayani perintah teks instan dari nomor Superadmin (`#adminlogin`, `#getgroupjid`, dll).

### 3. Testing Mode (Testing Guard)
Fungsi `sendWhatsAppMessage` wajib mematuhi aturan testing guard:
* Jika `TESTING_MODE=true` di `.env`, pesan keluar hanya boleh dikirim ke nomor Superadmin (`6285334354102`).
* Ini mencegah pengiriman pesan spam ke grup/siswa/guru saat masa pengembangan (development).

### 4. Penanganan LID (WhatsApp Baru)
* Beberapa akun WhatsApp menggunakan format `@lid` (bukan `@s.whatsapp.net`).
* Pastikan resolusi LID tetap diaktifkan saat membaca nomor pengirim agar pencocokan dengan tabel `allowed_numbers` atau `students` di SQLite tetap akurat.

---

## Checklist Setelah Edit `bot.js`
- [ ] Pengiriman pesan menghormati aturan `TESTING_MODE`.
- [ ] Log pesan masuk/keluar dicatat dengan ringkas di database `logs`.
- [ ] Tidak ada memori bocor (memory leak) karena proses loop chat.
- [ ] Fungsi broadcast API berjalan asinkron dan tidak memblokir koneksi Baileys.
