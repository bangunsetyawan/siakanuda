# SIAKANUDA — Roadmap Pengembangan
> **Versi:** 1.11.1 | **Sekolah:** SMK NU Darussalam | **Diperbarui:** 18 Juni 2026

Dokumen ini berisi rencana pengembangan dan status rilis proyek SIAKANUDA.

---

## ✅ Sudah Selesai (v1.0.0 — v1.9.0)

### Arsitektur & Infrastruktur
- [x] **Single Dashboard Entry Point (v1.2.3)** — Penyatuan seluruh antarmuka ke CodeIgniter 4 (Port 8080).
- [x] **Penyederhanaan Bot WhatsApp (v1.2.3)** — Bot kini murni sebagai agen notifikasi & broadcast di latar belakang (Port 7860).
- [x] **Rombak Panel Port 7860 (v1.5.0)** — Dari SPA penuh (1357 baris) menjadi WA Panel ringan (~160 baris). Hapus PWA (sw.js, manifest.json).
- [x] **Database Hybrid** — SQLite lokal + Supabase Cloud PostgreSQL (offline-first).
- [x] **APK WebView** — Target port 8080 dengan script `BUILD-APK.bat` & `GANTI-IP.bat`.
- [x] **Manajemen Tahun Pelajaran (v1.6.0 - v1.8.2)** — Integrasi indikator utama data transaksional (KBM, PKL, BK, Prestasi, Masukan) dan global banner di dashboard. Penyederhanaan menjadi struktur 1 Tahun tanpa Semester.
- [x] **Cloudflare Tunnel Integration (v1.7.0)** — Akses dashboard dari internet via domain.
- [x] **9Router AI Gateway Integration (v1.8.6)** — Pembaruan parser Node.js untuk kompatibilitas REST model agnostik.
- [x] **Optimasi Konteks AI (v1.8.7)** — Arsip dokumentasi ke `docs/archive/`, `AI_CONTEXT.md` sebagai Single Source of Truth.
- [x] **Pembersihan Konfigurasi AI (v1.8.8)** — Menghapus variabel AI Gateway dari `.env` untuk mode produksi murni (bot notifikasi saja).

### Fitur Akademik
- [x] **CRUD Lengkap CI4 (v1.2.3 — v1.5.0)** — Semua modul CRUD di dashboard CI4 (siswa, guru, jadwal, absensi, pelanggaran, BK, PKL, kotak suara).
- [x] **Modul PKL Lengkap (v1.2.5 — v1.8.5)** — Kelompok, absensi, jurnal, foto per siswa, toggle libur, pelacakan lokasi, cetak PDF, integrasi ke semua role admin/guru. Auto-grouping Import PKL per Siswa via Excel.
- [x] **Kalender Akademik (v1.4.0)** — CRUD admin + widget ringkasan semester aktif di dashboard.
- [x] **Prestasi Siswa (v1.4.2)** — Modul universal, guru bisa menambah prestasi.
- [x] **Kotak Suara (v1.4.5)** — Anonim untuk non-admin, auto-publish, filter kata vulgar.
- [x] **Laporan PDF Premium** — Generator PDF A4 dengan kop surat & tanda tangan digital.
- [x] **Rekap Absensi Bulanan PDF (v1.6.9)** — Laporan bulanan absensi siswa dengan format standar instansi sekolah.
- [x] **Rekap Mingguan PKL PDF (v1.6.18)** — Laporan padat 6 halaman per minggu, dengan pemisahan preview online & cetak luring.
- [x] **Dashboard Analytics (v1.8.8)** — Visualisasi data eksekutif menggunakan Chart.js untuk grafik tren absensi, statistik pelanggaran, dan progress PKL.
- [x] **Cron Jobs Otomatis** — 5 jadwal pengingat harian + backup database otomatis (00:30 WIB).
- [x] **Optimasi Upload Foto PKL (v1.9.0)** — Kompresi foto di client-side (HP siswa) menggunakan `browser-image-compression` sebelum upload untuk meringankan server Intel Atom N455.
- [x] **Integrasi GAS untuk PDF (v1.9.0)** — Offload pembuatan PDF laporan harian PKL ke Google Apps Script agar backend tidak terbebani.

### Hak Akses & Keamanan
- [x] **7 Role System (v1.3.0)** — admin, kepsek, guru, guru_bk, siswa, ketua_pkl, anggotapkl.
- [x] **Security Hardening (v1.5.2)** — CSRF protection global, server-side validation (12 model + 9 controller), session IP binding & regeneration, hapus hardcoded passwords, RoleFilter routing, CORS lock, SQL injection prevention, file upload validation, error sanitization.
- [x] **Database Backup (v1.5.2)** — Cron harian + CI4 Spark command `db:backup`.
- [x] **Integrasi API CI4 ↔ Node.js (v1.5.1)** — Dashboard CI4 terhubung mulus dengan background API untuk trigger broadcast & status bot.

### UI/UX & WhatsApp Bot
- [x] **Dashboard Adaptif per Role (v1.2.4)** — Shortcut, sidebar, welcome message disesuaikan per role.
- [x] **Optimasi Mobile (v1.3.0)** — Navbar, sidebar, footer compact untuk WebView APK.
- [x] **Jam Real-time (v1.4.1)** — Widget jam, hari, tanggal di header dashboard.
- [x] **WhatsApp Bot Config Dashboard (v1.7.0)** — Manajemen setting bot, template WA, cron jobs, dan target grup langsung dari dashboard CI4 tanpa menyentuh source code.

---

## 🔥 Prioritas Berikutnya (v1.10.0) — Perombakan Arsitektur PDF PKL

> Hasil rapat teknis 12 Juni 2026. Lihat notulen lengkap di Antigravity Agent.

### Keputusan Arsitektur
- **Service PDF:** PDFKit saja (hapus ketergantungan Google Apps Script)
- **Foto PKL:** 1 foto per kelompok per hari (sebelumnya per siswa) + bukti untuk absensi
- **PDF realtime PKL:** Dihapus semua (harian & mingguan)
- **Pengganti:** Halaman "Riwayat Laporan" (HTML preview + CSS `@media print` + print browser)
- **Rekap Absensi PKL (PDF):** Tetap dipertahankan (sudah jadi & berfungsi)

### Checklist
- [x] **Halaman Riwayat Laporan PKL** — HTML preview per kelompok, filter bulan, detail per hari (absensi + jurnal + foto + bukti gabungan), CSS `@media print` untuk cetak rapi
- [x] **Ubah foto: per siswa → per kelompok** — 1 foto kelompok + bukti per anggota tidak hadir
- [x] **Hapus PDF realtime PKL** — Endpoint, route, controller, tombol view
- [x] **Hapus GAS dependency** — `gas_pdf_client.js`, `absensi_pdf_builder.js`, `GAS_PDF_URL` di `.env`
- [x] **Cleanup dead code** — `generatePklReportPDF()`, `generateTodayPklReportPDF()`, `generateWeeklyPklReportPDF()` di `pdf_generator.js`

---

## 📋 Rencana Selanjutnya (v2.0.0)

- ~~**Modul Nilai / Rapor**~~ ❌ Tidak diperlukan di SIAKANUDA (14 Jun 2026).

---

## 🔄 Rencana Menengah (v2.0.0)

- [ ] **Laporan Bulanan PDF Otomatis** — Rekap absen satu sekolah dalam satu file PDF bulanan.
- [ ] **Rate Limiting & Fail2ban** — Perlindungan brute-force pada login.

---

## 🔮 Rencana Jangka Panjang (v2.x)

- [ ] **Perpustakaan Digital** — Modul pinjam-meminjam buku digital untuk siswa.
- [ ] **Notifikasi Push (Firebase/OneSignal)** — Push notification ke APK sebagai alternatif/pelengkap WA bot.
- [ ] **Multi-Sekolah (SaaS)** — Arsitektur multi-tenant untuk adopsi oleh sekolah lain.

---

## 🖥️ Target Deployment

### Spesifikasi Server Produksi (Lenovo Debian)
- **Perangkat:** Lenovo Notebook (~2010), Intel Atom N455, 2GB RAM, 128GB SSD.
- **Sistem Operasi:** Debian 13 (Trixie) Minimal CLI Headless.
- **Service Manager:** Systemd (`bot.siswa.service` & `siakadash.service`).
- **Jaringan:** IP Statis LAN Sekolah (`10.10.11.37`) + Tailscale VPN (`100.110.83.48`).
