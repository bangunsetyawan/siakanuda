# 🧪 SIAKANUDA — Checklist Testing Lengkap (v1.10.2)

> **Tanggal:** 14 Juni 2026
> **Tujuan:** Testing menyeluruh sebelum rilis final — semua role, fitur, dan operasi.
> **Cara pakai:** Login sebagai setiap role → ikuti checklist → centang ✅ jika lolos, ❌ jika gagal.

> [!IMPORTANT]
> Test dilakukan di **2 environment**: Browser desktop (Chrome) dan APK Android (WebView).
> Pastikan kedua server aktif: CI4 (`:8080`) dan Node.js (`:7860`).

---

## 📋 Daftar Isi

1. [Pra-Testing (Persiapan)](#-1-pra-testing-persiapan)
2. [Role: Admin](#-2-role-admin)
3. [Role: Kepala Sekolah](#-3-role-kepala-sekolah-kepsek)
4. [Role: Guru](#-4-role-guru)
5. [Role: Guru BK](#-5-role-guru-bk)
6. [Role: Siswa](#-6-role-siswa)
7. [Role: Ketua PKL](#-7-role-ketua-pkl)
8. [Role: Anggota PKL](#-8-role-anggota-pkl)
9. [Cross-Cutting: Keamanan](#-9-cross-cutting-keamanan)
10. [Cross-Cutting: WhatsApp Bot](#-10-cross-cutting-whatsapp-bot)
11. [Cross-Cutting: Responsivitas & UI/UX](#-11-cross-cutting-responsivitas--uiux)
12. [Cross-Cutting: Database & Backup](#-12-cross-cutting-database--backup)
13. [Cross-Cutting: Deployment & Infrastruktur](#-13-cross-cutting-deployment--infrastruktur)

---

## 🔧 1. Pra-Testing (Persiapan)

| # | Item | Status | Catatan |
|---|------|:------:|---------|
| 1.1 | Server CI4 berjalan di port 8080 | [ ] | `php spark serve` |
| 1.2 | Server Node.js berjalan di port 7860 | [ ] | `npm run dev` |
| 1.3 | Database `siakanuda.db` ada dan bisa dibaca | [ ] | |
| 1.4 | File `.env` (root + dashboard) terkonfigurasi benar | [ ] | |
| 1.5 | Tahun Pelajaran aktif sudah di-set | [ ] | Cek di menu Tahun Pelajaran |
| 1.6 | Minimal ada data: 1 admin, 1 guru, 1 guru_bk, 1 kepsek, beberapa siswa kelas XII | [ ] | |
| 1.7 | Minimal ada 1 kelompok PKL (dengan ketua + anggota) | [ ] | |
| 1.8 | Bot WhatsApp terkoneksi (QR sudah di-scan) | [ ] | Cek di Pengaturan WA |
| 1.9 | Grup WA target sudah di-set (KBM & PKL) | [ ] | |
| 1.10 | Kalender Akademik ada data untuk TP aktif | [ ] | |

---

## 👑 2. Role: ADMIN

### 2A. Login & Autentikasi

| # | Test Case | Langkah | Expected Result | Status | Catatan |
|---|-----------|---------|-----------------|:------:|---------|
| 2A.1 | Login Admin | Buka `/login` → Tab Guru → pilih nama admin → masukkan password | Masuk ke dashboard admin, sidebar lengkap | [ ] | |
| 2A.2 | Login password salah | Input password salah | Error message, tidak masuk | [ ] | |
| 2A.3 | Ganti password | Profil → Ganti Password → input lama & baru | Password berhasil diganti, bisa login ulang | [ ] | |
| 2A.4 | Logout | Klik Logout | Kembali ke halaman login, session dihapus | [ ] | |
| 2A.5 | Akses halaman tanpa login | Buka `/dashboard` tanpa session | Redirect ke `/login` | [ ] | |

### 2B. Dashboard Utama

| # | Test Case | Expected Result | Status | Catatan |
|---|-----------|-----------------|:------:|---------|
| 2B.1 | Tampilan dashboard admin | Card statistik: Total Siswa, KBM Hadir, PKL Hadir, Pelanggaran | [ ] | |
| 2B.2 | Widget jam real-time | Jam, hari, tanggal tampil dan berjalan | [ ] | |
| 2B.3 | Banner Dapodik di navbar | `TP [Tahun] ([Semester])` tampil di navbar | [ ] | |
| 2B.4 | Notifikasi pintar | Tampil notifikasi kelas belum absen / PKL belum lapor | [ ] | |
| 2B.5 | Widget Kalender Akademik | Tampil kalender semester aktif | [ ] | |
| 2B.6 | Tracker rekapitulasi KBM 30 hari | Tampil tabel status absensi 30 hari terakhir | [ ] | |
| 2B.7 | Total siswa = hanya siswa aktif | Angka total tidak menghitung siswa nonaktif | [ ] | |
| 2B.8 | Card PKL Hadir terpisah dari KBM | Dua card berbeda untuk KBM dan PKL | [ ] | |

### 2C. Manajemen Siswa

| # | Test Case | Langkah | Expected Result | Status | Catatan |
|---|-----------|---------|-----------------|:------:|---------|
| 2C.1 | Lihat daftar siswa | Menu Siswa | Tabel semua siswa aktif tampil | [ ] | |
| 2C.2 | Toggle siswa nonaktif | Klik toggle "Tampilkan Siswa Nonaktif" | Siswa nonaktif muncul/hilang | [ ] | |
| 2C.3 | Tambah siswa baru | Modal Tambah → isi data → Simpan | Siswa baru tersimpan di database | [ ] | |
| 2C.4 | Edit siswa | Klik Edit → ubah data → Simpan | Data terupdate | [ ] | |
| 2C.5 | Hapus siswa | Klik Hapus → Konfirmasi | Siswa terhapus | [ ] | |
| 2C.6 | Set role siswa | Modal Tambah/Edit → pilih role (Siswa/Ketua PKL/Anggota PKL) | Role tersimpan | [ ] | |
| 2C.7 | Reset password siswa | Klik Reset Password | Password kembali ke NISN | [ ] | |
| 2C.8 | Import siswa Excel | Upload file `.xlsx` → Preview → Konfirmasi | Siswa baru ditambah, lama diupdate (password tidak direset) | [ ] | |
| 2C.9 | Import + nonaktifkan missing | Centang "Nonaktifkan siswa tidak ada di Excel" | Siswa tidak ada di file → status nonaktif | [ ] | |
| 2C.10 | Download template Excel | Klik Download Template | File `.xlsx` terunduh dengan format benar | [ ] | |
| 2C.11 | Download data siswa aktif | Klik Export Excel | File `.xlsx` berisi semua siswa aktif | [ ] | |
| 2C.12 | Detail siswa | Klik nama siswa | Halaman detail: profil + rekap absensi + pelanggaran | [ ] | |

### 2D. Manajemen Guru/Staf

| # | Test Case | Expected Result | Status | Catatan |
|---|-----------|-----------------|:------:|---------|
| 2D.1 | Lihat daftar guru | Tabel semua guru/staf tampil di Pengaturan WA → tab Whitelist | [ ] | |
| 2D.2 | Tambah guru baru | Guru baru tersimpan dengan role + nomor WA | [ ] | |
| 2D.3 | Edit guru | Data guru terupdate | [ ] | |
| 2D.4 | Hapus guru | Guru terhapus | [ ] | |
| 2D.5 | Import guru Excel | Preview + import berhasil | [ ] | |
| 2D.6 | Kolom tugas_tambahan | Tampil dan bisa diisi di form tambah/edit | [ ] | |

### 2E. Absensi KBM

| # | Test Case | Langkah | Expected Result | Status | Catatan |
|---|-----------|---------|-----------------|:------:|---------|
| 2E.1 | Lihat daftar kelas | Menu Absensi KBM | Tabel semua kelas dengan status absensi hari ini | [ ] | |
| 2E.2 | Input absensi kelas | Pilih kelas → set status per siswa → Simpan | Data tersimpan, broadcast WA terkirim | [ ] | |
| 2E.3 | Tombol "Hadir Semua" | Klik tombol → otomatis submit | Semua siswa status H, redirect dengan parameter tanggal | [ ] | |
| 2E.4 | Validasi keterangan S/I/A | Set siswa ke Sakit tanpa keterangan | Error: keterangan wajib diisi | [ ] | |
| 2E.5 | Update absensi hari sama | Ubah absensi yang sudah diisi | Tersimpan + label `⚠️ [#Pembaruan Absensi]` di WA | [ ] | |
| 2E.6 | Hapus data absensi | Klik Hapus pada record absensi | Data terhapus | [ ] | |
| 2E.7 | Filter tanggal | Ganti tanggal di date picker | Data absensi sesuai tanggal tampil | [ ] | |
| 2E.8 | Cetak PDF rekap bulanan | Modal → pilih Bulan & Tahun → Cetak | PDF Landscape A4 terunduh, kop surat ada, format benar | [ ] | |
| 2E.9 | Siswa nonaktif tidak tampil | Buka form absensi kelas | Siswa nonaktif tidak ada di daftar | [ ] | |
| 2E.10 | Lewati Minggu & hari libur | Set tanggal ke hari Minggu/libur | Sistem skip atau beri notifikasi | [ ] | |

### 2F. PDF Rekap Absensi Bulanan

| # | Test Case | Expected Result | Status | Catatan |
|---|-----------|-----------------|:------:|---------|
| 2F.1 | Layout Landscape A4 | Orientasi benar | [ ] | |
| 2F.2 | Kop surat overlay | Kop surat fisik tampil di halaman pertama | [ ] | |
| 2F.3 | Logo overlay presisi | Logo SMK tajam, tidak pecah/meleset | [ ] | |
| 2F.4 | Header tabel 2 baris | "Periode Bulan" & "Rekap" merged, tanggal di baris 2 | [ ] | |
| 2F.5 | Kolom NISN (bukan NIS) | Label NISN, lebar cukup untuk 10 digit | [ ] | |
| 2F.6 | Highlight hari Minggu | Header merah, background pink | [ ] | |
| 2F.7 | Format hitam putih | Tanpa zebra striping, hemat tinta | [ ] | |
| 2F.8 | Metadata di atas tabel | TP, Wali Kelas, Pengunduh, Tanggal tampil | [ ] | |
| 2F.9 | Margin 1.27cm (36pt) | Konten tidak terpotong | [ ] | |
| 2F.10 | Multi-halaman | Lebih dari 18 siswa → halaman baru dengan header ulang | [ ] | |

### 2G. Modul PKL

| # | Test Case | Langkah | Expected Result | Status | Catatan |
|---|-----------|---------|-----------------|:------:|---------|
| 2G.1 | Lihat semua kelompok PKL | Menu PKL → Kelompok | Semua kelompok tampil | [ ] | |
| 2G.2 | Tambah kelompok PKL | Modal → isi tempat, pilih anggota | Kelompok tersimpan, role siswa ter-sync | [ ] | |
| 2G.3 | Edit kelompok PKL | Ubah anggota/ketua/pembimbing | Data terupdate, role ter-sync | [ ] | |
| 2G.4 | Hapus kelompok PKL | Konfirmasi hapus | Kelompok terhapus, role siswa reset | [ ] | |
| 2G.5 | Ketua PKL opsional | Buat kelompok tanpa ketua | Tersimpan tanpa error | [ ] | |
| 2G.6 | Pilihan siswa hanya kelas XII | Buka student picker | Hanya siswa XII aktif yang muncul | [ ] | |
| 2G.7 | Import kelompok via Excel | Upload file → Preview → Import | Kelompok terbuat, role ter-sync | [ ] | |
| 2G.8 | Import per siswa (auto-group) | Upload format per siswa | Siswa dengan tempat PKL sama → 1 kelompok | [ ] | |
| 2G.9 | Dashboard laporan PKL | Menu PKL → Laporan | 3 stat card: total/sudah/belum lapor | [ ] | |
| 2G.10 | Lihat detail laporan | Klik detail kelompok → modal | Kehadiran + jurnal + foto semua anggota | [ ] | |
| 2G.11 | Hapus laporan PKL | Klik Hapus pada laporan | Laporan + foto + absensi terhapus | [ ] | |
| 2G.12 | Tombol "Cetak Harian" admin | Pilih tanggal → cetak | Fungsi print/cetak berjalan | [ ] | |
| 2G.13 | Riwayat Laporan PKL | Buka halaman riwayat | HTML preview per kelompok, filter bulan, CSS print rapi | [ ] | |
| 2G.14 | Foto per kelompok (bukan per siswa) | Lihat form laporan | Input 1 foto kelompok + bukti per anggota S/I/A | [ ] | |

### 2H. Kalender Akademik & Hari Libur

| # | Test Case | Expected Result | Status | Catatan |
|---|-----------|-----------------|:------:|---------|
| 2H.1 | Lihat kalender | Data semester Ganjil & Genap tampil | [ ] | |
| 2H.2 | Tambah/edit kalender | Form simpan berfungsi (admin only) | [ ] | |
| 2H.3 | Tab Hari Libur | CRUD hari libur di tabbed UI | [ ] | |
| 2H.4 | Tambah hari libur | Tanggal + keterangan tersimpan | [ ] | |
| 2H.5 | Hapus hari libur | Data terhapus | [ ] | |
| 2H.6 | Koneksi ke Tahun Pelajaran | Dropdown TP terisi dari tabel master | [ ] | |

### 2I. Tahun Pelajaran

| # | Test Case | Expected Result | Status | Catatan |
|---|-----------|-----------------|:------:|---------|
| 2I.1 | Lihat daftar TP | Semua tahun pelajaran tampil | [ ] | |
| 2I.2 | Tambah TP baru | TP tersimpan (semester di-hardcode 'Ganjil') | [ ] | |
| 2I.3 | Set TP aktif | Hanya 1 TP aktif, lainnya nonaktif | [ ] | |
| 2I.4 | Edit TP | Data terupdate | [ ] | |
| 2I.5 | Hapus TP | TP terhapus (pastikan tidak hapus data terkait) | [ ] | |

### 2J. Pelanggaran & BK

| # | Test Case | Expected Result | Status | Catatan |
|---|-----------|-----------------|:------:|---------|
| 2J.1 | Lihat pelanggaran | Semua data pelanggaran tampil | [ ] | |
| 2J.2 | Lihat catatan BK | Semua catatan BK tampil | [ ] | |

### 2K. Lainnya (Admin)

| # | Test Case | Expected Result | Status | Catatan |
|---|-----------|-----------------|:------:|---------|
| 2K.1 | Prestasi Siswa — CRUD | Tambah, edit, hapus prestasi berfungsi | [ ] | |
| 2K.2 | Jadwal Pelajaran — CRUD | Tambah, edit, hapus jadwal berfungsi | [ ] | |
| 2K.3 | Kotak Suara — Lihat semua | Feedback tampil dengan identitas pengirim | [ ] | |
| 2K.4 | Message Log — Real-time | Log WA real-time via Socket.io | [ ] | |
| 2K.5 | Analytics Dashboard | Grafik tren absensi, pelanggaran, PKL tampil (Chart.js) | [ ] | |
| 2K.6 | Analytics — Data KBM & PKL terpisah | Grafik KBM dan PKL tidak tercampur | [ ] | |

### 2L. Pengaturan WhatsApp (Admin Only)

| # | Test Case | Langkah | Expected Result | Status | Catatan |
|---|-----------|---------|-----------------|:------:|---------|
| 2L.1 | Status koneksi bot | Buka Pengaturan WA | Status connected/disconnected tampil real-time | [ ] | |
| 2L.2 | Scan QR code | Jika disconnected → scan QR | Bot terkoneksi | [ ] | |
| 2L.3 | Logout sesi WA | Klik Logout | Bot disconnected | [ ] | |
| 2L.4 | Tab Template — Lihat | Buka tab Template | Semua template WA tampil | [ ] | |
| 2L.5 | Tab Template — Edit | Ubah template → klik tag variabel → Simpan | Template terupdate, redirect ke tab Template | [ ] | |
| 2L.6 | Tab Cron — Lihat & kelola | Buka tab Cron | Daftar cron job tampil, bisa tambah/hapus | [ ] | |
| 2L.7 | Tab Whitelist — CRUD guru | Buka tab Whitelist | CRUD guru/staf berfungsi | [ ] | |
| 2L.8 | Tab Groups — Deteksi grup live | Buka tab Groups | Daftar grup WA yang terhubung tampil | [ ] | |
| 2L.9 | Tab Groups — Set target JID | Pilih grup untuk KBM & PKL | JID tersimpan ke `.env` | [ ] | |
| 2L.10 | Tab Groups — Set Keduanya | Klik "Set Keduanya" | Satu grup untuk KBM & PKL | [ ] | |
| 2L.11 | Join grup via link | Input link undangan → Submit | Bot bergabung ke grup baru | [ ] | |

---

## 🏫 3. Role: KEPALA SEKOLAH (kepsek)

| # | Test Case | Expected Result | Status | Catatan |
|---|-----------|-----------------|:------:|---------|
| 3.1 | Login sebagai Kepsek | Masuk ke dashboard, sidebar sesuai role | [ ] | |
| 3.2 | Dashboard — statistik lengkap | Card KBM, PKL, pelanggaran tampil | [ ] | |
| 3.3 | Dashboard — notifikasi pintar | Notifikasi kelas belum absen tampil | [ ] | |
| 3.4 | Analytics Dashboard | Grafik Chart.js tampil dan interaktif | [ ] | |
| 3.5 | Absensi KBM — Read only | Bisa lihat data, TIDAK bisa input/edit/hapus | [ ] | |
| 3.6 | Cetak PDF Rekap Bulanan | Bisa cetak PDF absensi bulanan | [ ] | |
| 3.7 | PKL — Lihat semua kelompok | Bisa lihat semua kelompok & laporan | [ ] | |
| 3.8 | PKL — Riwayat Laporan | Bisa buka halaman riwayat + cetak | [ ] | |
| 3.9 | Pelanggaran — Read only | Bisa lihat, TIDAK bisa tambah/edit | [ ] | |
| 3.10 | Catatan BK — Read only | Bisa lihat catatan | [ ] | |
| 3.11 | Jadwal — Read only | Bisa lihat jadwal | [ ] | |
| 3.12 | Prestasi — CRUD | Bisa tambah/edit/hapus prestasi | [ ] | |
| 3.13 | Kalender — Read only | Bisa lihat kalender | [ ] | |
| 3.14 | Kotak Suara — Read only | Bisa lihat (anonim) | [ ] | |
| 3.15 | **TIDAK bisa** akses Pengaturan WA | URL `/whatsapp-settings` → redirect/block | [ ] | |
| 3.16 | **TIDAK bisa** CRUD User | Menu Siswa/Guru tidak ada atau read-only | [ ] | |
| 3.17 | **TIDAK bisa** hapus data | Tidak ada tombol hapus user | [ ] | |
| 3.18 | **TIDAK bisa** akses Tahun Pelajaran | Menu tidak tampil / akses diblokir | [ ] | |
| 3.19 | **TIDAK bisa** akses Message Log | Menu tidak tampil | [ ] | |

---

## 👨‍🏫 4. Role: GURU

| # | Test Case | Expected Result | Status | Catatan |
|---|-----------|-----------------|:------:|---------|
| 4.1 | Login sebagai Guru | Tab Guru → pilih nama → password (default: No. WA) | [ ] | |
| 4.2 | Dashboard guru | Statistik + notifikasi sesuai role | [ ] | |
| 4.3 | Absensi KBM — **Input absensi** | Bisa pilih kelas → input status → Simpan | [ ] | |
| 4.4 | Absensi KBM — Validasi S/I/A | Keterangan wajib saat set Sakit/Izin/Alpha | [ ] | |
| 4.5 | Absensi KBM — Broadcast WA otomatis | Setelah simpan, pesan WA terkirim ke grup | [ ] | |
| 4.6 | Cetak PDF Rekap Bulanan | Bisa cetak PDF | [ ] | |
| 4.7 | Jadwal — Read only | Bisa lihat, tidak bisa edit | [ ] | |
| 4.8 | Siswa — Read only | Bisa lihat daftar siswa | [ ] | |
| 4.9 | Pelanggaran — Search only | Bisa cari pelanggaran siswa, TIDAK bisa CRUD | [ ] | |
| 4.10 | Prestasi — CRUD | Bisa tambah prestasi siswa | [ ] | |
| 4.11 | PKL — Hanya kelompok bimbingan | Hanya lihat kelompok yang dia bimbing | [ ] | |
| 4.12 | PKL — Riwayat Laporan bimbingan | Bisa buka riwayat kelompok bimbingannya | [ ] | |
| 4.13 | Kalender — Read only | Bisa lihat | [ ] | |
| 4.14 | Kotak Suara — Read only anonim | Bisa lihat feedback (tanpa identitas pengirim) | [ ] | |
| 4.15 | **TIDAK bisa** akses user CRUD | Menu tidak tampil | [ ] | |
| 4.16 | **TIDAK bisa** akses Pengaturan WA | Diblokir | [ ] | |
| 4.17 | **TIDAK bisa** akses Analytics | Menu tidak tampil | [ ] | |

---

## 🧑‍⚕️ 5. Role: GURU BK

| # | Test Case | Expected Result | Status | Catatan |
|---|-----------|-----------------|:------:|---------|
| 5.1 | Login sebagai Guru BK | Masuk dengan sidebar role guru_bk | [ ] | |
| 5.2 | Semua akses Guru (4.2–4.14) | Sama seperti guru biasa | [ ] | |
| 5.3 | Pelanggaran — **CRUD lengkap** | Bisa tambah, edit, hapus pelanggaran | [ ] | |
| 5.4 | Tambah pelanggaran | Pilih siswa → jenis + poin → Simpan | [ ] | |
| 5.5 | Edit pelanggaran | Ubah data → Simpan | [ ] | |
| 5.6 | Hapus pelanggaran | Konfirmasi → data terhapus | [ ] | |
| 5.7 | Catatan BK — **CRUD lengkap** | Bisa tambah, edit, hapus catatan | [ ] | |
| 5.8 | Tambah catatan BK | Pilih siswa → isi catatan → Simpan | [ ] | |
| 5.9 | Absensi KBM — **Input** | Bisa input absensi (sama seperti guru) | [ ] | |

---

## 🎓 6. Role: SISWA

| # | Test Case | Langkah | Expected Result | Status | Catatan |
|---|-----------|---------|-----------------|:------:|---------|
| 6.1 | Login siswa | Tab Siswa → input NISN → password (default: NISN) | Masuk ke dashboard siswa | [ ] | |
| 6.2 | First login — paksa ganti password | Login pertama kali | Redirect ke form ganti password | [ ] | |
| 6.3 | Dashboard siswa | Tampil welcome + ringkasan personal | [ ] | |
| 6.4 | Rekap absensi personal | Menu Absensi → lihat | Kartu identitas + ringkasan H/S/I/A + riwayat | [ ] | |
| 6.5 | Jadwal pelajaran | Menu Jadwal | Jadwal kelas siswa tampil (read-only) | [ ] | |
| 6.6 | Pelanggaran personal | Menu Pelanggaran | Hanya lihat pelanggaran diri sendiri | [ ] | |
| 6.7 | Catatan BK personal | Menu BK | Hanya lihat catatan BK sendiri (read-only) | [ ] | |
| 6.8 | Prestasi — Read only | Menu Prestasi | Bisa lihat semua prestasi | [ ] | |
| 6.9 | Kalender — Read only | Menu Kalender | Bisa lihat kalender | [ ] | |
| 6.10 | Kotak Suara — **Kirim** | Isi feedback → Kirim | Feedback terkirim | [ ] | |
| 6.11 | Kotak Suara — Lihat milik sendiri | Feedback yang dikirim tampil | [ ] | |
| 6.12 | Profil & ganti password | Menu Profil | Bisa lihat profil + ganti password | [ ] | |
| 6.13 | **TIDAK bisa** lihat data siswa lain | Coba akses URL `/students` | Redirect/block | [ ] | |
| 6.14 | **TIDAK bisa** akses absensi kelas | Coba akses URL `/attendance/class` | Redirect/block | [ ] | |
| 6.15 | **TIDAK bisa** akses PKL (bukan anggota) | Coba akses URL `/pkl` | Redirect/block atau menu tidak tampil | [ ] | |

---

## 🎖️ 7. Role: KETUA PKL

| # | Test Case | Langkah | Expected Result | Status | Catatan |
|---|-----------|---------|-----------------|:------:|---------|
| 7.1 | Login Ketua PKL | Tab Siswa → NISN → password | Masuk, sidebar ada menu PKL | [ ] | |
| 7.2 | Semua akses Siswa (6.3–6.12) | Berfungsi sama | [ ] | |
| 7.3 | Dashboard — Widget PKL | Status card PKL clickable → ke `/pkl` | [ ] | |
| 7.4 | Buka halaman PKL | Menu PKL | Form laporan harian tampil | [ ] | |
| 7.5 | Isi lokasi presensi (wajib) | Input lokasi | Form absensi terbuka setelah lokasi diisi | [ ] | |
| 7.6 | Tanpa lokasi → form terkunci | Coba isi tanpa lokasi | Jurnal/absen diblokir | [ ] | |
| 7.7 | Absensi anggota — Hadir | Set Hadir → isi jurnal (≥75 char) + upload foto | Validasi lolos | [ ] | |
| 7.8 | Counter karakter | Ketik di jurnal | Counter `X/75` interaktif, merah→hijau | [ ] | |
| 7.9 | Jurnal < 75 karakter | Submit dengan jurnal 50 char | Error: minimal 75 karakter | [ ] | |
| 7.10 | Absensi — Sakit | Set Sakit → isi alasan + foto bukti (surat dokter) | Tersimpan | [ ] | |
| 7.11 | Absensi — Izin | Set Izin → isi alasan + foto bukti | Tersimpan | [ ] | |
| 7.12 | Absensi — Alpha | Set Alpha → pilih status hubungi + keterangan | Tersimpan | [ ] | |
| 7.13 | Upload foto kelompok | Upload 1 foto kelompok | Foto terupload + terkompresi client-side | [ ] | |
| 7.14 | Kompresi client-side | Upload foto besar (>2MB) | Foto dikompresi (≤500KB) sebelum upload | [ ] | |
| 7.15 | Toggle Libur | Set hari libur + alasan | Form anggota hilang, alasan tersimpan | [ ] | |
| 7.16 | Tombol simpan cepat libur | Klik tombol di bawah alasan | Submit tanpa scroll | [ ] | |
| 7.17 | Submit laporan | Klik Simpan | Data tersimpan + broadcast WA terkirim + notifikasi sukses WIB | [ ] | |
| 7.18 | Lihat laporan terkirim (locked) | Setelah submit | Form terkunci, tombol "Ubah Laporan" tampil | [ ] | |
| 7.19 | Ubah laporan | Klik "Ubah Laporan" | Form terbuka, label `⚠️ [#Perubahan Laporan]` di WA | [ ] | |
| 7.20 | Laporan susulan (H-7) | Pilih tanggal kemarin (max 7 hari) | Bisa isi laporan untuk tanggal tersebut | [ ] | |
| 7.21 | Laporan > H-7 | Pilih tanggal > 7 hari lalu | Ditolak / form terkunci | [ ] | |
| 7.22 | Cetak laporan | Klik cetak | Fungsi print browser berjalan (CSS `@media print`) | [ ] | |
| 7.23 | Riwayat Laporan PKL | Buka halaman riwayat | Preview HTML per hari, filter bulan | [ ] | |

---

## 👤 8. Role: ANGGOTA PKL

| # | Test Case | Expected Result | Status | Catatan |
|---|-----------|-----------------|:------:|---------|
| 8.1 | Login Anggota PKL | Masuk dengan sidebar role anggotapkl | [ ] | |
| 8.2 | Semua akses Siswa (6.3–6.12) | Berfungsi sama | [ ] | |
| 8.3 | PKL Laporan — **Read only** | Bisa lihat laporan kelompoknya, TIDAK bisa submit | [ ] | |
| 8.4 | **TIDAK bisa** submit laporan | Form tidak tampil / tombol submit disabled | [ ] | |
| 8.5 | **TIDAK bisa** cetak PDF PKL | Tombol cetak tidak ada | [ ] | |
| 8.6 | Riwayat Laporan — Read only | Bisa lihat riwayat | [ ] | |
| 8.7 | Akses tanpa ketua → diblokir | Jika kelompok belum ada ketua | Pesan peringatan, tidak bisa akses | [ ] | |

---

## 🔒 9. Cross-Cutting: KEAMANAN

| # | Test Case | Langkah | Expected Result | Status | Catatan |
|---|-----------|---------|-----------------|:------:|---------|
| 9.1 | CSRF Protection | Submit form tanpa CSRF token (manual cURL/Postman) | Rejected (403/419) | [ ] | |
| 9.2 | CSRF di AJAX import | Import Excel via AJAX | Token dikirim dari meta tag PHP | [ ] | |
| 9.3 | Session hijack prevention | Login → catat session → ganti IP | Session invalidated | [ ] | |
| 9.4 | Session regenerate on login | Login baru | Session ID baru dibuat | [ ] | |
| 9.5 | Role escalation — URL direct | Login siswa → akses `/students` atau `/whatsapp-settings` | Diblokir oleh RoleFilter | [ ] | |
| 9.6 | Role escalation — semua route sensitif | Coba akses setiap route admin dengan role non-admin | Semua diblokir | [ ] | |
| 9.7 | SQL Injection | Input `'; DROP TABLE students;--` di form | Query aman, tidak crash | [ ] | |
| 9.8 | XSS Prevention | Input `<script>alert(1)</script>` di kotak suara | Script tidak dieksekusi | [ ] | |
| 9.9 | File upload — tipe salah | Upload file `.exe` atau `.php` di foto PKL | Ditolak: hanya JPG/PNG | [ ] | |
| 9.10 | File upload — ukuran besar | Upload file > 5MB | Ditolak | [ ] | |
| 9.11 | CORS Node.js | Request dari origin selain localhost:8080 | Ditolak | [ ] | |
| 9.12 | Password bcrypt | Cek database → password hash bcrypt | Tidak ada plaintext | [ ] | |
| 9.13 | `.env` tidak di-expose | Akses `/.env` via browser | 404 / tidak bisa diakses | [ ] | |
| 9.14 | Error message generik | Trigger error di production | Pesan generik, tidak expose stack trace | [ ] | |

---

## 📱 10. Cross-Cutting: WHATSAPP BOT

| # | Test Case | Expected Result | Status | Catatan |
|---|-----------|-----------------|:------:|---------|
| 10.1 | Bot status — connected | Indikator hijau di Pengaturan WA | [ ] | |
| 10.2 | Broadcast KBM ke grup sekolah | Pesan KBM masuk ke grup WA dengan format benar | [ ] | |
| 10.3 | Broadcast KBM ke wali kelas | Wali kelas menerima pesan WA paralel | [ ] | |
| 10.4 | Rekap konsolidasi — semua kelas selesai | Setelah semua kelas absen → rekap sekolah terkirim otomatis | [ ] | |
| 10.5 | Broadcast PKL ke grup | Laporan PKL terkirim dengan format benar | [ ] | |
| 10.6 | Broadcast PKL libur | Nama anggota + kelas ketua tampil | [ ] | |
| 10.7 | Label update KBM | `⚠️ [#Pembaruan Absensi]` saat update | [ ] | |
| 10.8 | Label update PKL | `⚠️ [#Perubahan Laporan]` saat update | [ ] | |
| 10.9 | Nama + detail S/I/A di WA KBM | Nama siswa absen + keterangan tampil | [ ] | |
| 10.10 | Info pembimbing di WA PKL | `👨‍🏫 Pembimbing: [Nama]` tampil | [ ] | |
| 10.11 | Jam WIB di broadcast | `(Pukul HH:MM WIB)` tampil | [ ] | |
| 10.12 | Async response — 200 OK instan | Dashboard tidak timeout saat broadcast | [ ] | |
| 10.13 | Fallback jika grup JID kosong | Broadcast tetap jalan ke admin | [ ] | |
| 10.14 | Bot disconnected → fail fast | `sendMessage()` langsung gagal, tidak hang | [ ] | |
| 10.15 | Template sesuai format baru | Tanpa garis pembatas, footer standar | [ ] | |
| 10.16 | Skip broadcast hari Minggu/libur | Tidak ada broadcast/cron saat hari libur | [ ] | |
| 10.17 | Log audit real-time | Chat masuk tampil di Message Log via Socket.io | [ ] | |

### Cron Jobs

| # | Test Case | Expected Result | Status | Catatan |
|---|-----------|-----------------|:------:|---------|
| 10.18 | 00:30 — Backup DB | File backup `.db` dibuat di `backups/`, cleanup > 7 hari | [ ] | |
| 10.19 | 08:30 — Pengingat KBM | Pesan ke guru yang belum absen | [ ] | |
| 10.20 | 16:00 — Pengingat jurnal PKL | Pesan ke ketua yang belum lapor | [ ] | |
| 10.21 | 19:00 — Eskalasi ke pembimbing | Pesan ke pembimbing jika kelompok belum lapor | [ ] | |
| 10.22 | Cron kustom via UI | Buat cron baru → dijalankan sesuai jadwal | [ ] | |

---

## 📐 11. Cross-Cutting: RESPONSIVITAS & UI/UX

### Desktop (Chrome)

| # | Test Case | Expected Result | Status | Catatan |
|---|-----------|-----------------|:------:|---------|
| 11.1 | Sidebar navigasi | Expand/collapse, highlight halaman aktif | [ ] | |
| 11.2 | Tabel responsif | Tabel tidak overflow, scrollable jika perlu | [ ] | |
| 11.3 | Modal form | Buka/tutup lancar, form tidak terpotong | [ ] | |
| 11.4 | Flash message | Notifikasi success/error/info tampil dengan warna benar | [ ] | |
| 11.5 | Date picker | Berfungsi di semua form tanggal | [ ] | |
| 11.6 | Search/filter tabel | Pencarian di setiap tabel berfungsi | [ ] | |

### Mobile / APK WebView

| # | Test Case | Expected Result | Status | Catatan |
|---|-----------|-----------------|:------:|---------|
| 11.7 | Sidebar mobile | Toggle buka/tutup + close button | [ ] | |
| 11.8 | Login responsive | Form login pas di layar HP | [ ] | |
| 11.9 | Tabel mobile | Scroll horizontal, kolom tidak terpotong | [ ] | |
| 11.10 | Form PKL di HP | Input jurnal, foto, absensi semua berfungsi | [ ] | |
| 11.11 | Upload foto dari kamera HP | Capture foto → upload | [ ] | |
| 11.12 | Footer compact | Footer tidak mengganggu konten | [ ] | |
| 11.13 | Navbar nama + role | Tampil dengan benar | [ ] | |
| 11.14 | Tombol Kembali APK | Tombol back di navbar berfungsi | [ ] | |
| 11.15 | Logo & tagline login | Tampil rapi di mobile (rem units) | [ ] | |

---

## 🗄️ 12. Cross-Cutting: DATABASE & BACKUP

| # | Test Case | Expected Result | Status | Catatan |
|---|-----------|-----------------|:------:|---------|
| 12.1 | SQLite journal mode = DELETE | Cek PRAGMA journal_mode | `delete` (bukan WAL, untuk exFAT) | [ ] | |
| 12.2 | Schema 16 tabel lengkap | Semua tabel ada di `siakanuda.db` | [ ] | |
| 12.3 | `tahun_pelajaran_id` di 8 tabel | Filter TP berfungsi di semua tabel transaksional | [ ] | |
| 12.4 | Backup manual via Spark | `php spark db:backup` | File backup dibuat di `backups/` | [ ] | |
| 12.5 | Cron backup otomatis | Cek `backups/` setelah 00:30 | File baru ada, file > 7 hari terhapus | [ ] | |
| 12.6 | Supabase sync — foto PKL | Upload foto → cek Supabase Storage | Foto terkompresi ada di cloud | [ ] | |
| 12.7 | `is_active` filter | Siswa nonaktif tidak muncul di absensi, dashboard stat | [ ] | |

---

## 🚀 13. Cross-Cutting: DEPLOYMENT & INFRASTRUKTUR

| # | Test Case | Expected Result | Status | Catatan |
|---|-----------|-----------------|:------:|---------|
| 13.1 | Akses via LAN (WiFi Sekolah) | `http://10.10.11.37:8080` → dashboard berfungsi | [ ] | |
| 13.2 | Akses via Tailscale VPN | `http://100.110.83.48:8080` → berfungsi | [ ] | |
| 13.3 | Akses via Cloudflare Tunnel | `https://domain.tld` → berfungsi | [ ] | |
| 13.4 | Dynamic baseURL | `HTTP_HOST` otomatis mendeteksi domain/IP | [ ] | |
| 13.5 | Systemd service — Node.js | `systemctl status bot.siswa` → active | [ ] | |
| 13.6 | Systemd service — CI4 | `systemctl status siakadash` → active | [ ] | |
| 13.7 | APK build | Jalankan `BUILD-APK.bat` | APK terbangun | [ ] | |
| 13.8 | APK ganti IP | Jalankan `GANTI-IP.bat` | URL berubah ke target | [ ] | |
| 13.9 | APK WebView — navigasi | Semua halaman berfungsi di dalam APK | [ ] | |

---

## 📊 Ringkasan Total Test Case

| Bagian | Jumlah |
|--------|:------:|
| Pra-Testing | 10 |
| Admin (login, dashboard, siswa, guru, KBM, PDF, PKL, kalender, TP, lainnya, WA) | ~75 |
| Kepala Sekolah | 19 |
| Guru | 17 |
| Guru BK | 9 |
| Siswa | 15 |
| Ketua PKL | 23 |
| Anggota PKL | 7 |
| Keamanan | 14 |
| WhatsApp Bot + Cron | 22 |
| Responsivitas & UI/UX | 15 |
| Database & Backup | 7 |
| Deployment & Infrastruktur | 9 |
| **TOTAL** | **~242** |

---

> [!TIP]
> **Cara efisien testing:**
> 1. Mulai dari **Admin** (paling banyak fitur) → verifikasi semua CRUD
> 2. Lanjut **Guru** & **Guru BK** → fokus absensi KBM + pelanggaran
> 3. **Ketua PKL** → fitur PKL yang paling kritis
> 4. **Siswa** & **Anggota PKL** → pastikan read-only benar
> 5. **Kepala Sekolah** → verifikasi hak akses terbatas
> 6. Cross-cutting (keamanan, WA, responsivitas) bisa dilakukan paralel

> [!IMPORTANT]
> Tandai setiap test case yang **GAGAL** dengan ❌ dan catatan detailnya, agar bisa diperbaiki sebelum rilis final.
