# 📋 Notulen Rapat — Perombakan Arsitektur PDF PKL
> **Tanggal:** 12 Juni 2026, 16:40 — 17:12 WIB  
> **Peserta:** Developer (NOMENA) + Antigravity Agent (Claude Opus 4.6)  
> **Topik:** Strategi PDF untuk kondisi server real & penyederhanaan alur PKL  
> **Status:** ✅ Disetujui — menunggu eksekusi

---

## 🎯 Latar Belakang

Server produksi SIAKANUDA (Intel Atom N455, 2GB RAM, Debian Headless) tidak cukup kuat untuk menjalankan PDF generation secara realtime, khususnya untuk laporan PKL yang mengandung foto. Rapat ini membahas strategi yang realistis dan berkelanjutan.

---

## 📌 Keputusan Rapat

### 1. Satu Service PDF: PDFKit Saja
- **PDFKit / pdf-lib** (native Node.js) menjadi satu-satunya engine PDF
- **Google Apps Script (GAS)** dihapus sebagai dependensi
- Alasan: satu service lebih mudah di-maintain, tidak bergantung internet untuk PDF

### 2. Foto PKL: 1 Foto Per Kelompok Per Hari
- **Sebelum:** Setiap anggota kelompok wajib upload foto sendiri (~81 foto/hari)
- **Sesudah:** 1 foto kelompok per hari (~34 foto/hari)
- Anggota yang tidak hadir (Sakit/Izin/Alpha) → ketua **wajib sertakan bukti** (SS WA, surat dokter, foto halangan)
- Kompresi tetap aktif: client-side (`browser-image-compression`) + server-side (`sharp`)
- **Estimasi storage 4 bulan PKL:** ~0,54 GB (muat di Supabase free tier 1 GB)

### 3. Hapus PDF Realtime PKL
- **PDF harian PKL** → ❌ Dihapus
- **PDF mingguan PKL** → ❌ Dihapus
- **GAS offload** → ❌ Dihapus
- Alasan: terlalu berat untuk server, tidak perlu realtime

### 4. Ganti Dengan HTML Preview + Print Browser
- Buat halaman **"Riwayat Laporan"** per kelompok
- Filter per bulan, tombol detail per hari
- Isi detail: kehadiran + jurnal + foto kelompok + bukti absen (gabungan, tidak dipisah)
- Tambahkan **CSS `@media print`** agar tampilan rapi saat dicetak
- Untuk sidang PKL: user buka preview → pilih bulan → **Ctrl+P → Save as PDF / Print**
- **Beban server = nol** — browser yang render, foto diambil dari Supabase CDN

### 5. Rekap Absensi PKL (PDF) Tetap Dipertahankan
- Template PDF rekap absensi yang sudah ada dan berfungsi **tidak diubah**
- Tetap menggunakan GAS atau PDFKit sesuai yang sudah jalan

---

## 🔧 Rencana Perubahan Teknis

### 🟢 BUAT BARU

| # | Item | Keterangan |
|---|------|-----------|
| 1 | **Halaman Riwayat Laporan PKL** | Halaman HTML baru (atau perbaikan view existing) yang menampilkan seluruh riwayat laporan per kelompok dengan filter bulan |
| 2 | **Komponen Detail Harian** | Card/expandable per hari: status kehadiran semua anggota + jurnal + foto kelompok + bukti absensi |
| 3 | **CSS `@media print`** | Stylesheet khusus cetak: header sekolah, page break per hari, sembunyikan navigasi/sidebar, layout rapi A4 |
| 4 | **Tombol "Cetak Bulan Ini"** | Trigger `window.print()` dengan filter bulan aktif |

### 🔴 HAPUS

| # | Item | File | Keterangan |
|---|------|------|-----------|
| 1 | PDF harian PKL (realtime) | [server.js](file:///f:/Antigravity/siakanuda/execution/server.js) L456-483 | Endpoint `GET /api/pkl-report/pdf/:ketuaPhone/:date` |
| 2 | PDF mingguan jurnal/dokumentasi | [server.js](file:///f:/Antigravity/siakanuda/execution/server.js) L491-521 | Endpoint `GET /api/pkl-report/pdf-weekly` (tipe `jurnal` dan `dokumentasi`) |
| 3 | GAS PDF client | [gas_pdf_client.js](file:///f:/Antigravity/siakanuda/execution/gas_pdf_client.js) | Seluruh file — hapus atau arsipkan |
| 4 | GAS absensi builder | [absensi_pdf_builder.js](file:///f:/Antigravity/siakanuda/execution/absensi_pdf_builder.js) | Seluruh file — hapus atau arsipkan |
| 5 | Dead code `generatePklReportPDF()` | [pdf_generator.js](file:///f:/Antigravity/siakanuda/execution/pdf_generator.js) L598-689 | Fungsi tidak terpakai |
| 6 | Dead code `generateTodayPklReportPDF()` | [pdf_generator.js](file:///f:/Antigravity/siakanuda/execution/pdf_generator.js) L691-836 | Diganti preview HTML |
| 7 | Dead code `generateWeeklyPklReportPDF()` | [pdf_generator.js](file:///f:/Antigravity/siakanuda/execution/pdf_generator.js) L838+ | Diganti preview HTML |
| 8 | Route CI4 print-pdf PKL harian | [Routes.php](file:///f:/Antigravity/siakanuda/dashboard/app/Config/Routes.php) L96 | `pkl/print-pdf/(:any)/(:any)` |
| 9 | Route CI4 print-weekly-pdf jurnal/dokumentasi | [Routes.php](file:///f:/Antigravity/siakanuda/dashboard/app/Config/Routes.php) L97 | `pkl/print-weekly-pdf/(:any)/(:any)` — pertahankan hanya untuk rekap absensi jika masih dipakai |
| 10 | Method `printPdf()` di Pkl.php | [Pkl.php](file:///f:/Antigravity/siakanuda/dashboard/app/Controllers/Pkl.php) L789-843 | Dihapus |
| 11 | Tombol cetak PDF di view admin PKL | [pkl/index.php](file:///f:/Antigravity/siakanuda/dashboard/app/Views/pkl/index.php) L172-188 | Ganti dengan link ke riwayat laporan |
| 12 | Tombol cetak PDF di view ketua PKL | [pkl/student_report.php](file:///f:/Antigravity/siakanuda/dashboard/app/Views/pkl/student_report.php) | Ganti dengan link ke riwayat + tombol print browser |
| 13 | Variabel `GAS_PDF_URL` | [.env.example](file:///f:/Antigravity/siakanuda/.env.example) | Hapus dari template |

### 🟡 PERBAIKI / UBAH

| # | Item | Keterangan |
|---|------|-----------|
| 1 | **Input foto PKL** | Ubah dari upload per siswa → 1 foto kelompok + foto bukti per anggota tidak hadir |
| 2 | **Preview detail di admin PKL** | Perbaiki modal detail existing → jadikan halaman riwayat penuh |
| 3 | **`printWeeklyPdf()` di Pkl.php** | Pertahankan HANYA untuk rekap absensi (type=absensi). Hapus type jurnal & dokumentasi |
| 4 | **Pembatasan `is_local_access()`** | Evaluasi ulang — jika preview HTML, tidak perlu batasan WiFi |

---

## 📊 Dampak Perubahan

### Sebelum vs Sesudah

| Aspek | Sebelum | Sesudah |
|-------|---------|--------|
| Service PDF | PDFKit + GAS (2 service) | PDFKit saja (1 service) |
| Foto per hari | ~81 (per siswa) | ~34 (per kelompok) |
| Storage 4 bulan | ~1,28 GB | ~0,54 GB |
| Beban server cetak | 🔴 Berat (realtime) | 🟢 Nol (browser render) |
| Akses cetak | ❌ WiFi only | ✅ Online dari mana saja |
| File yang dihapus | — | ~5 file / fungsi |
| Kompleksitas kode | Tinggi | Berkurang signifikan |

### Yang Tidak Berubah
- ✅ Input harian PKL (absensi + jurnal + foto) — alur tetap sama
- ✅ WA broadcast notifikasi — tetap jalan
- ✅ Rekap Absensi PKL (PDF) — template sudah jadi, tetap dipakai
- ✅ Rekap Absensi KBM Bulanan (PDF) — tidak terpengaruh
- ✅ Kompresi foto (client + server) — tetap aktif

---

## 🗓️ Rencana Eksekusi

| Fase | Tugas | Prioritas |
|------|-------|-----------|
| **Fase 1** | Buat halaman Riwayat Laporan PKL (HTML preview + CSS print) | 🔴 Tinggi |
| **Fase 2** | Ubah input foto: per siswa → per kelompok + bukti absen | 🔴 Tinggi |
| **Fase 3** | Hapus PDF realtime (endpoint, route, controller, view) | 🟡 Sedang |
| **Fase 4** | Hapus GAS dependency (file, env, import) | 🟡 Sedang |
| **Fase 5** | Cleanup dead code di pdf_generator.js | 🟢 Rendah |
| **Fase 6** | Update dokumentasi & commit | 🟢 Rendah |

---

> 📌 **Catatan:** Eksekusi dimulai setelah makan malam. Notulen ini menjadi acuan implementasi.
