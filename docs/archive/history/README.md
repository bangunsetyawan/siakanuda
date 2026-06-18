# SIAKANUDA 🏫
**Sistem Informasi Akademik SMK NU Darussalam**

Sistem manajemen akademik sekolah berbasis Web Dashboard (CodeIgniter 4) dengan dukungan Background API (Node.js) untuk notifikasi WhatsApp, pembuatan PDF, dan sinkronisasi data cloud.

---

## 🏗️ Teknologi

| Komponen | Teknologi | Port | Keterangan |
|---|---|---|---|
| **Dashboard Utama** | CodeIgniter 4 (PHP) | **8080** | Pintu masuk utama user & APK Android WebView |
| **Background API** | Node.js + Baileys | **7860** | WhatsApp Bot (notifikasi saja), PDF Kit, Cron Jobs |
| **Database** | SQLite (Lokal) + Supabase (Cloud) | — | Penyimpanan hybrid offline-first |
| **AI Engine** | Google Gemini Flash | — | Parser notifikasi cerdas |

---

## 🚀 Cara Menjalankan Lokal

### 1. Jalankan Background API & Bot WA (Port 7860)
```powershell
cd F:\Antigravity\siakanuda
npm run dev
# API berjalan di http://localhost:7860
```

### 2. Jalankan Dashboard CI4 (Port 8080)
```powershell
cd F:\Antigravity\siakanuda\dashboard
php spark serve
# Dashboard berjalan di http://localhost:8080
```

### 3. Konfigurasi Environment (`.env`)
Salin file template `.env.example` menjadi `.env` di folder root dan folder `dashboard/`, lalu lengkapi nilai-nilai variabelnya.

---

## 📂 Struktur Direktori

```
siakanuda/
├── execution/      ← Backend Node.js (WhatsApp Bot, Cron, PDF, Sync)
├── dashboard/      ← CodeIgniter 4 MVC (User Interface & CRUD)
├── directives/     ← SOP & Panduan Operasional AI Agent
├── database/       ← Schema SQL DDL
└── data/           ← Data Excel Siswa & Guru
```

---

## 👥 Hak Akses Pengguna

| Role | Akses Dashboard |
|---|---|
| `admin` | CRUD Data Master, Laporan Absensi/PKL, WhatsApp Panel, Hapus Data |
| `kepsek` | Read-only Laporan Absensi, PKL, Pelanggaran, Kotak Suara |
| `guru_bk` | Input & Rekap Absensi, Input Pelanggaran BK |
| `guru_mapel` | Input Absensi Kelas, Lihat Jadwal |
| `siswa` | Lihat Jadwal, Absensi, isi Kotak Suara, Laporan PKL Harian |

---

## ⏰ Jadwal Otomatis (Cron Jobs)

| Waktu WIB | Aksi Notifikasi WhatsApp |
|---|---|
| **00:30** | Backup otomatis database SQLite + cleanup backup > 7 hari |
| **08:30** | Pengingat kelas yang belum di-absen oleh Guru |
| **16:00** | Pengingat pengisian jurnal harian PKL untuk Ketua Kelompok |
| **19:00** | Eskalasi laporan absen PKL ke Guru Pembimbing |
| **23:59** | Auto-alpha untuk siswa KBM yang tidak memiliki keterangan absen |

---

## 📍 Riwayat Versi

* **v1.0.0** (30 Mei 2026) - Rilis dasar bot WhatsApp & dashboard SPA Vanilla JS.
* **v1.1.0** (31 Mei 2026) - Implementasi PWA, wrapper APK Android, & sinkronisasi Supabase.
* **v1.2.0** (1 Jun 2026) - Migrasi dashboard ke CodeIgniter 4 dengan SQLite.
* **v1.2.3** (2 Jun 2026) - Overhaul penyatuan dashboard ke port 8080 (CI4) sebagai pintu masuk tunggal, penyederhanaan bot WhatsApp menjadi background service.
* **v1.2.4** (2 Jun 2026) - Implementasi menu pintasan cepat (shortcut) adaptif berbasis peran di dashboard utama, perbaikan ikon Manajemen Guru di sidebar, serta penambahan tombol Kembali dinamis di navbar untuk optimalisasi navigasi pada WebView APK.
* **v1.2.5** (2 Jun 2026) - Widget form absensi PKL inline di dashboard untuk ketua_pkl, sidebar menu terstruktur per role (treeview PKL), dashboard menampilkan status PKL real-time untuk siswa & ketua, shortcut khusus ketua_pkl.
* **v1.2.6** (2 Jun 2026) - Shortcut & sidebar link "Alumni & BKK" untuk admin (external link ke smknudarussalam.sch.id/bkk), dokumentasi folder BKK sebagai standalone workspace dengan deployment terpisah.
* **v1.2.7** (2 Jun 2026) - Penambahan script serve.js untuk BKK portal lokal, perbaikan .gitignore untuk membuang writable/debugbar logs.
* **v1.2.8** (3 Jun 2026) - Revisi dashboard siswa: shortcut card diselaraskan dengan sidebar, pelanggaran hanya tampil milik siswa login, form inline Kotak Suara untuk siswa, welcome message role-aware.
* **v1.2.9** (3 Jun 2026) - Pisahkan siswa biasa vs siswa-anggota-PKL: absensi KBM menampilkan rekap personal siswa (kartu identitas + ringkasan + riwayat), poin pelanggaran tampil individual dengan total poin, menu PKL hanya muncul untuk siswa yang terdaftar di kelompok PKL.
* **v1.2.10** (3 Jun 2026) - Proteksi ketat menu & halaman PKL: deteksi `is_pkl_member` sejak login di session, sembunyikan Laporan PKL pada welcome description siswa biasa, dan alihkan akses langsung `/pkl` siswa non-PKL ke dashboard dengan pesan error.
* **v1.3.0** (3 Jun 2026) - Restrukturisasi hak akses 7 role test: [admin] full, [kepsek] full kecuali hapus, [guru] write absensi KBM, read-only siswa/jadwal/pelanggaran, pembimbing PKL, [guru_bk] guru + write pelanggaran & BK, [siswa] personal read-only + kotak saran, [ketuapkl] siswa + PKL group write + cetak PDF, [anggotapkl] siswa + PKL read-only. Expose menu & shortcut 'Alumni & BKK' ke semua role. Penyederhanaan label login dan perbaikan navbar, sidebar, serta footer untuk tampilan mobile.
* **v1.4.0** (3 Jun 2026) - Implementasi modul Kalender Akademik (CRUD untuk admin, read-only untuk seluruh role), pembaruan tampilan dashboard utama untuk menampilkan Agenda Kesiswaan dan Profil Akademik serta menyematkan Kalender Akademik semester aktif.
* **v1.4.1** (3 Jun 2026) - Pembaruan data tabel Kalender Akademik semester ganjil & genap TP 2025/2026 di SQLite database, dan penambahan widget Jam Real-Time responsif di header dashboard utama.
* **v1.4.2** (3 Jun 2026) - Implementasi modul Prestasi Siswa (tabel database baru, controller, view, dan menu shortcut/sidebar), penyediaan akses Catatan BK (read-only) untuk semua peran siswa, serta penambahan tombol simpan kalender akademik di bawah tabel (admin).
* **v1.4.3** (3 Jun 2026) - Prestasi dibuka untuk semua role (universal), guru dapat menambah prestasi, perbaikan layout select dropdown.
* **v1.4.4** (3 Jun 2026) - Perbaikan layout input numerik yang terjepit, feedback siswa terlihat oleh pengirimnya.
* **v1.4.5** (3 Jun 2026) - Anonimisasi pengirim feedback untuk non-admin, auto-publish feedback baru, filter kata vulgar di Kotak Suara.
* **v1.4.6** (3 Jun 2026) - Penyempurnaan modul PKL: merge shortcut, toggle libur dengan alasan, upload foto per siswa dengan validasi, view read-only anggota, tombol status, pelacakan lokasi, batas jurnal 75 karakter, serta perbaikan camelCase/snake_case dan parsing photo_url. Penambahan opsi `USE_LOCAL_AUTH` untuk testing bot WA lokal.
* **v1.5.0** (3 Jun 2026) - Rombak total panel port 7860: dari SPA penuh (1357 baris HTML + 136KB JS + 46KB CSS) menjadi WA Panel ringan (~160 baris HTML + 6KB JS + 4KB CSS). Menghapus semua modul akademik duplikat, menyisakan hanya manajemen koneksi WA (QR code, kirim pesan, log, cron trigger). Hapus PWA (sw.js, manifest.json).
* **v1.5.1** (3 Jun 2026) - Integrasi PKL ke semua role admin/guru: guru_bk ditambahkan ke menu PKL sidebar, guru_mapel bisa akses Kelompok PKL, guru hanya lihat laporan kelompok bimbingannya (filter pembimbing), tambah 3 stat card di halaman Laporan PKL (total/sudah lapor/belum lapor), shortcut PKL di dashboard guru_bk.
* **v1.5.2** (4 Jun 2026) - Security Hardening: aktifkan CSRF protection global + token randomization, tambah `$validationRules` di 12 model CI4 + `validate()` di 9 controller, hapus hardcoded fallback passwords (`guruhebat`/`guru123`/`adminsmknuda`), session IP binding + regeneration, buat `RoleFilter.php` untuk role-based routing, restrict MessageLog ke admin-only, fix GET-based delete di Kalender Akademik, lock CORS Node.js ke localhost:8080, auth test endpoints, whitelist SQL column names di `updateStudent()`/`updateKelompokPkl()`, validasi file upload PKL (JPG/PNG max 5MB), sanitize error messages, tambah backup database harian (cron 00:30 WIB + CI4 Spark command `db:backup`), tambah `JWT_SECRET` ke `.env.example`.
* **v1.6.0** (4 Jun 2026) - Implementasi konsep **Tahun Pelajaran** (Academic Year) sebagai indikator utama data transaksional di SIAKANUDA. Penambahan tabel `tahun_pelajaran`, migrasi kolom `tahun_pelajaran_id` di 8 tabel transaksional, pembuatan model/controller/view manajemen Tahun Pelajaran aktif (khusus admin), modifikasi `BaseController` untuk global data sharing, integrasi filter `tahun_pelajaran_id` di 7 controller dashboard dan di script Node.js background cron jobs/database adapter.
* **v1.6.1** (4 Jun 2026) - Perbaikan kritis alur Ketua PKL: Proxy cetak PDF via CI4 (port 8080) untuk mengamankan autentikasi dan memecahkan masalah CORS/port, sanitasi nama berkas unggahan foto untuk siswa dengan karakter titik/spasi (mencegah kegagalan upload PHP), serta perbaikan daemon `photo_sync.js` agar melestarikan struktur objek JSON pemetaan nama siswa ke URL foto saat sinkronisasi Supabase.
* **v1.6.2** (4 Jun 2026) - Penyempurnaan Laporan PKL & Penguncian PDF: Menambahkan kartu pratinjau laporan terkirim (lock view) untuk Ketua PKL demi mencegah penimpaan data yang tidak disengaja, menyediakan opsi tombol "Ubah Laporan" untuk membuka form edit secara asinkron (JS), mengunci tombol "Cetak PDF" agar hanya dapat diklik setelah 7 hari dari tanggal laporan (pengunduhan seminggu sekali), dan memperbagus penanganan eror offline PDF generator (port 7860 mati).
* **v1.6.3** (4 Jun 2026) - Implementasi Fitur Hapus Laporan PKL oleh Admin: Menyediakan tombol aksi "Hapus" khusus bagi Admin di daftar laporan PKL harian. Penghapusan ini mencakup pembersihan file foto bukti fisik dari direktori local/dashboard, pembersihan otomatis absensi KBM harian siswa kelompok terkait untuk tanggal laporan tersebut, dan pembersihan record dari database.
* **v1.6.4** (4 Jun 2026) - Penyederhanaan Tabel Laporan PKL & Detail Modal: Mengubah tampilan tabel Laporan PKL harian di dashboard admin/guru agar lebih ringkas, rapi, dan padat (hanya menampilkan rangkuman kehadiran, jurnal, dan satu foto bukti pertama). Menyediakan tombol "Detail" yang menampilkan rincian presensi kelompok secara lengkap, jurnal setiap anggota, serta seluruh berkas foto bukti fisik dalam Bootstrap Modal tanpa *reload* halaman.
* **v1.6.5** (4 Jun 2026) - Penyelarasan & Deployment Services Debian: Deployment pembaruan kode SIAKANUDA ke server Debian (IP 100.110.83.48), penyelarasan nama systemd service dari `siswabot.service` menjadi `bot.siswa.service` untuk menyamakan dengan blueprint produksi, pembersihan service lama, pemuatan skema database SQLite `tahun_pelajaran` secara otomatis lewat daemon startup, serta pengaktifan kembali layanan `bot.siswa` dan `siakadash` dengan status active (running).
* **v1.6.7** (4 Jun 2026) - Kustomisasi WA Broadcast & Form Detail PKL (Sakit, Izin, Alpha): Pembaruan format WhatsApp broadcast agar menampilkan nama pembimbing, jam WIB, dan ringkasan kehadiran ringkas. Desain form detail PKL dinamis berbasis status absen (Hadir/Sakit/Izin/Alpha) dengan validasi wajib foto bukti dan isian alasan di client/server.
* **v1.6.8** (5 Jun 2026) - Notifikasi Dashboard Pintar, Import Excel Interaktif, dan Peningkatan Keamanan Login:
  - **Notifikasi Dashboard Pintar**: Penambahan modul notifikasi dinamis berbasis peran (role-based) untuk mendeteksi data KBM (absensi kelas) dan laporan PKL kelompok yang belum terisi pada hari berjalan.
  - **Penghapusan Auto-Alpha**: Menonaktifkan cron job `runAutoAlphaJob` pukul 23:59 WIB karena default kehadiran siswa adalah Hadir. Guru hanya mengisi data siswa yang sakit, izin, atau alpha.
  - **Penyederhanaan UI KBM**: Mengubah tombol "Hadir Semua (99)" menjadi "Hadir Semua" pada lembar absensi kelas KBM.
  - **Fitur Import Excel (.xlsx)**: Implementasi client-side parser menggunakan library SheetJS di browser pada panel Manajemen Siswa dan Manajemen Guru/Whitelist, lengkap dengan pratinjau data (Live Preview) dan download berkas template Excel (.xlsx) resmi.
  - **Default Password & Reset**: Menetapkan password default siswa = NISN dan guru = No. WhatsApp. Menambahkan tombol "Reset" password ke default di tabel manajemen siswa.
  - **Keamanan Login & Paksa Ganti Password**: Menambahkan verifikasi keamanan di mana Ketua PKL yang masuk menggunakan password default wajib mengganti passwordnya sebelum mengakses dashboard.
  - **Login Tabbed & Dropdown Pilihan Guru**: Mendesain ulang halaman login dengan tab pemilih "Siswa" (input NISN) dan "Guru / Staf" (memilih nama dari dropdown select) untuk kepraktisan akses guru.
  - **Tugas Tambahan Guru**: Menambahkan kolom `tugas_tambahan` ke skema tabel `allowed_numbers`, view manajemen guru, edit modals, dan template import Excel.
* **v1.6.9** (5 Jun 2026) - Cetak Rekap Absensi Bulanan & Kop Surat PDF:
  - **Cetak Rekap Absensi Bulanan (PDF)**: Implementasi rute dan pengontrol `printPdf()` di dashboard CI4 dan API endpoint di Node.js background service untuk mencetak laporan kehadiran bulanan per kelas.
  - **Dukungan Kop Surat PDF Fisik**: Mengintegrasikan pustaka `pdf-lib` untuk menempelkan (overlay) file `Kop surat.pdf` fisik secara otomatis pada halaman pertama laporan PDF, dengan fallback kop surat manual jika file fisik tidak ada.
* **v1.6.10** (5 Jun 2026) - Matriks Absensi Bulanan & Margin Kop Surat PDF:
  - **Penyelarasan Margin Kop Surat**: Menyesuaikan margin kiri dokumen PDF ke 78pt dan margin kanan ke 44pt di `pdf_generator.js` agar sejajar dengan Kop Surat cetak. Menggeser teks, table, dan tandatangan ke x=78pt.
  - **Format Matriks Absensi Bulanan**: Mengubah rekap bulanan kelas menjadi tabel matriks horizontal ber-grid dengan 28-31 kolom hari, kolom No, Nama, NIS, dan total (H/S/I/A).
  - **Penyederhanaan & Reposisi Banner Dapodik**: Memindahkan banner akademik besar di bawah flash message dan memindahkannya ke tengah navbar atas di `template.php` dengan format teks ringkas `Dapodik: TP [Tahun] ([Semester])`.
* **v1.6.11** (5 Jun 2026) - PDF Rekap KBM Landscape & Deteksi Hari Minggu:
  - **PDF Rekap KBM Landscape**: Mengubah layout dokumen rekap absensi bulanan menjadi Landscape (A4) di `pdf_generator.js` agar memuat 28-31 kolom secara lapang, dengan lebar tabel 670pt, margin kiri 110pt (lurus Kop Surat stretched), dan margin kanan 44pt.
  - **Highlight Hari Minggu**: Mendeteksi hari Minggu secara dinamis (`getDay() === 0`). Memberi warna merah pada angka tanggal Minggu di header, dan mewarnai latar belakang sel kolom hari Minggu dengan warna merah muda lembut (`#fdf2f2`) pada tabel absensi.
* **v1.6.12** (5 Jun 2026) - Integrasi Kop Surat Landscape & Pembersihan Git status:
  - **Integrasi Kop Surat Landscape**: Mendefinisikan konstanta `KOP_LANDSCAPE_PATH` untuk file `data/Kop surat lanscape.pdf`. Mengubah logic `applyKopSurat` agar mendeteksi orientasi halaman PDF (Landscape vs Portrait) lewat ukuran halaman dan menempelkan Kop Surat yang sesuai secara otomatis. Menyelaraskan rujukan di `generateClassReportPDF` agar menggunakan `KOP_LANDSCAPE_PATH` untuk mengukur layout tabel, margin, dan posisi signature.
  - **Pembersihan Git status**: Mengabaikan folder `siakanuda-apk/` di `.gitignore` agar tidak mengotori repositori utama. Menambahkan file `data/Kop surat lanscape.pdf` ke Git untuk dipantau secara resmi.
* **v1.6.13** (5 Jun 2026) - Auto-submit Hadir Semua & Perbaikan PDF Rekap:
  - **Auto-submit Hadir Semua**: Tombol "Hadir Semua" pada absensi kelas KBM kini langsung mengirimkan formulir (saving) secara asinkron/otomatis dan mengarahkan kembali ke daftar absensi kelas dengan melestarikan parameter tanggal terpilih.
  - **Perbaikan PDF Rekap KBM**: Menggeser start Y ke `145` agar isi PDF tidak bertabrakan dengan Kop Surat Landscape, memperlebar tabel menjadi `680` (nama siswa diperluas ke `160`, NIS disesuaikan ke `35`, dan kolom rekap H/S/I/A disesuaikan ke `14`), serta menghapus kolom tanda tangan kepala sekolah ("Mengetahui, Kepala Sekolah...").
* **v1.6.14** (5 Jun 2026) - Perubahan Identitas NIS ke NISN & Sinkronisasi Margin PDF:
  - **Perubahan Identitas NIS ke NISN**: Mengubah nama kolom identitas di tabel PDF rekap bulanan menjadi `NISN` dan memperlebar lebarnya (`nisWidth`) dari `35` menjadi `60` agar nomor NISN (10 digit) dapat ditampilkan dalam satu baris rata tanpa mengalami pemotongan/wrapping.
  - **Sinkronisasi Margin**: Menyelaraskan margin kiri halaman landscape menjadi `78` (sama seperti portrait) dan lebar tabel menjadi `720` agar sejajar dengan garis horizontal dari `Kop surat lanscape.pdf` resmi. Lebar nama siswa (`namaWidth`) ditingkatkan menjadi `180`.
* **v1.6.15** (5 Jun 2026) - Format PDF Rekap Hitam Putih & Integrasi Meta Info Baru:
  - **Hitam Putih Polos & Hari Minggu**: Menghapus background zebra striping (`#fafafa`) dan mewarnai seluruh huruf status absen (S, I, A) menjadi hitam `#000000` agar tabel berformat hitam putih murni. Namun, shading/highlight hari Minggu (`#fdf2f2` & `#fcedeb`) dan shading header tabel tetap dipertahankan.
  - **Margin 1.27 cm (36 pt) & Lebar Tabel 770**: Menyesuaikan seluruh margin dokumen menjadi `36` (setara 1.27 cm). Lebar tabel ditingkatkan menjadi `770` dan titik mulai x disesuaikan ke `36`. Table wrap check disesuaikan ke `530` dan posisi awal table halaman baru disesuaikan ke `70`.
  - **Metadata Informasi Absensi**: Menambahkan judul, tahun pelajaran, periode bulan, wali kelas, serta catatan kecil cetak miring berisi tanggal unduh dan nama pengunduh di atas tabel absensi.
* **v1.6.16** (5 Jun 2026) - Header Dua Baris PDF & Logo Overlay Resolusi Tinggi:
  - **Header Dua Baris (Two-Row Header)**: Mendesain ulang header tabel absensi menjadi 2 baris (No, Nama, NISN setinggi 36pt, dengan cell merged "Periode Bulan..." di atas tanggal, dan "Rekap" di atas kolom H/S/I/A). Mengubah `drawVerticalGridLines` agar garis pembatas tanggal tidak memotong cell merged baris pertama.
  - **Overlay Logo Resolusi Tinggi**: Memperbarui `applyKopSurat` agar menggambar persegi putih solid di area logo Kop Surat Lanskap untuk menutupi placeholder, lalu menempelkan logo PNG asli dengan resolusi tajam.
  - **Penyelarasan Teks & Info**: Judul rekap absensi diposisikan di tengah (*center-aligned*) dan informasi Wali Kelas serta Tanggal Unduh terformat rapi rata kiri.
* **v1.6.17** (5 Jun 2026) - Koreksi Jarak Judul & Overlay Logo Presisi PDF:
  - **Overlay Logo Resolusi Tinggi Presisi**: Mengganti programmatic logo overlay lama yang meleset koordinatnya dengan overlay logo presisi tinggi (menggunakan berkas logo-smk.png resolusi 540x540px) di dalam helper `applyKopSurat` pada pdf_generator.js.
  - **Presisi Jarak Judul & Table**: Menetapkan koordinat tetap bagi elemen-elemen Page 1 (Title y=118, Subtitle y=131, Wali Kelas y=159, Tanggal Unduh y=172, Table y=193) menyamai layout `format-revisi.pdf` secara persis dan memperkecil jarak kosong yang berlebih di bawah Kop Surat.
  - **Optimasi Baris Halaman 1**: Mengubah batas wrap tabel ke `540` agar muat persis 18 baris siswa di halaman pertama.
* **v1.6.18** (5 Jun 2026) - Rekap Mingguan PKL & Kompresi Foto Otomatis:
  - **Kompresi Foto Otomatis**: Integrasi `sharp` di `photo_sync.js` untuk me-resize gambar hingga lebar max 640px dan kualitas JPEG 50% sebelum diunggah ke Supabase Storage, sangat menghemat kuota siswa & storage server.
  - **Deteksi Akses Lokal (WiFi)**: Helper `network_helper.php` untuk memisahkan akses lokal (WiFi Sekolah/Tailscale/localhost) dan internet. Membatasi pengunduhan PDF laporan harian & mingguan hanya untuk akses lokal demi performa server.
  - **PDF Rekap Mingguan PKL**: Membuat PDF A4 Portrait 6 halaman (1 hari = 1 halaman, Senin-Sabtu) yang memuat rekap absensi harian kelompok, jurnal kegiatan, foto bukti asli, dan Kop Surat overlay otomatis.
  - **UI/UX Rekap Mingguan**: Penambahan menu Rekap Mingguan, modal preview asinkron yang memuat thumbnail Supabase, serta tombol cetak mingguan di dashboard siswa (`student_report.php`) dan dashboard admin/pembimbing (`index.php`).
* **v1.7.1** (6 Jun 2026) - Broadcast Otomatis Absensi KBM ke Grup WA:
  - **Pemicu Broadcast**: Integrasi broadcast ke WhatsApp group `SCHOOL_GROUP_JID` pada `saveClass()` di [Attendance.php](file:///F:/Antigravity/siakanuda/dashboard/app/Controllers/Attendance.php) setelah absensi KBM disimpan.
  - **API Endpoint KBM**: Menambahkan `/api/attendance/broadcast` di [server.js](file:///F:/Antigravity/siakanuda/execution/server.js) untuk memproses formatting pesan KBM secara asinkron.
  - **Dukungan database**: Tabel `bot_templates` dan `cron_configs` ditambahkan ke `initSchema()` di [db.js](file:///F:/Antigravity/siakanuda/execution/db.js).
* **v1.7.0** (6 Jun 2026) - Konsolidasi Fitur & Peningkatan Fleksibilitas WhatsApp Bot:
  - **Kelompok PKL**: Ketua kelompok PKL opsional, melonggarkan validasi `ketua_phone` di [KelompokPklModel.php](file:///F:/Antigravity/siakanuda/dashboard/app/Models/KelompokPklModel.php) dan template form.
  - **Pengaturan WA Terpusat**: Menu sidebar baru "Pengaturan WA" yang menyatukan log qr code, kirim pesan manual, whitelist allowed numbers (Excel import), target broadcast group JID, dan dynamic cron configurations.
* **v1.6.24** (6 Jun 2026) - Overhaul Laporan PDF Harian & Resolusi Kritis SQLite I/O exFAT:
  - **Penyelesaian Disk I/O Error**: Migrasi `journal_mode` SQLite dari `WAL` ke `DELETE` di `execution/db.js` demi mendukung drive exFAT portabel (menghilangkan file `.db-shm` dan `.db-wal` secara otomatis).
  - **Redesain PDF Harian PKL**: Mengubah format halaman laporan harian PDF menjadi 1 halaman penuh murni (Compact Card Layout) dengan margin minimal, area foto diperbesar tanpa blur, dan pembersihan karakter emoji/tidak dikenal untuk mencegah error rendering.
  - **Pembaruan Form & Validasi Absensi**: Mengubah label lokasi menjadi alert peringatan wajib di dashboard Ketua PKL, mengunci input kehadiran/jurnal sebelum lokasi diisi, dan menampilkan pesan sukses interaktif berformat waktu lokal lengkap (WIB).
  - **Kustomisasi Broadcast WA**: Menambahkan cetak kelas Ketua `(Kelas)`, mengubah label lokasi absensi, dan menyertakan label `⚠️ *[#Perubahan Laporan]*` saat terjadi pembaruan laporan.
  - **Shortcut Cetak Admin**: Menambahkan tombol pintasan "Cetak Harian" pada panel admin untuk mempermudah cetak & uji coba laporan PDF harian untuk tanggal berapa pun.
* **v1.6.23** (6 Jun 2026) - Fix Bug Validasi Textarea PKL, Contoh Isian, & Karakter Counter:
  - **Fix Bug Validasi**: Memperbaiki selector javascript validator agar membaca nilai `<textarea>` alih-alih `<input>`, menyelesaikan bug validasi minimal 75 karakter.
  - **Karakter Counter**: Menambahkan counter interaktif di bawah isian jurnal (berwarna merah jika kurang dari 75, hijau jika terpenuhi).
  - **Lega & Contoh Isian**: Memperbesar rows textarea menjadi 4 (min-height 90px) dan melengkapinya dengan placeholder contoh isian realistis di setiap status (Hadir, Sakit, Izin, Alpha).
* **v1.6.22** (6 Jun 2026) - Pembaruan Input Textarea PKL & Pengembalian Lokasi Presensi:
  - **Textarea Fleksibel**: Mengubah input Jurnal, Sakit, Izin, dan Alpha menjadi `<textarea>` dengan default rows 2-3 dan auto-growing berdasarkan tinggi isian.
  - **Lokasi Presensi**: Menampilkan kembali kolom input "Tempat Melakukan Presensi" (`location_data`) yang wajib diisi jika PKL Masuk dan disembunyikan/opsional saat PKL Libur.
  - **Integrasi Notifikasi & Modal Admin**: Menampilkan `location_data` di WhatsApp broadcast notification saat PKL Masuk dan di modal detail laporan halaman admin PKL.
* **v1.6.21** (6 Jun 2026) - Tombol Simpan Cepat Libur & Optimasi Format WA PKL Libur:
  - **Tombol Simpan Cepat Libur**: Menambahkan tombol simpan/submit di dalam form alasan libur pada `student_report.php` agar ketua PKL tidak perlu men-scroll ke bawah saat tempat PKL libur.
  - **Format WA Broadcast Libur**: Memperbarui notifikasi broadcast WA saat libur di `server.js` untuk menampilkan seluruh nama anggota kelompok (satu baris dipisahkan koma) dan menyematkan kelas pada nama Ketua Kelompok.
* **v1.6.20** (6 Jun 2026) - Penyederhanaan Widget Absensi PKL Ketua PKL:
  - **Penyederhanaan Widget Dashboard**: Menyederhanakan tampilan widget absensi PKL pada dashboard utama `ketua_pkl` menjadi satu status card premium yang dapat diklik (mengarahkan langsung ke halaman pengisian `/pkl`).
  - **Pembersihan JS**: Menghapus form absensi inline dan membersihkan JavaScript validasi/manipulasi form terkait pada halaman dashboard utama.
* **v1.6.19** (5 Jun 2026) - UI Manajemen Peran Siswa & Otomatisasi Peran PKL:
  - Dropdown Peran/Role di manajemen siswa dan searchable single student picker ketua kelompok PKL.
  - Sinkronisasi otomatis peran siswa dan default kelas XI menjadi anggotapkl.
* **v1.7.2** (6 Jun 2026) - Perbaikan Fallback KBM Broadcast:
  - Logika fallback ke BROADCAST_GROUP_JID dan isolasi try-catch per admin untuk menjamin fallback WA berjalan aman.
* **v1.7.3** (6 Jun 2026) - Dual Notifikasi Hasil Absensi KBM:
  - Flashdata ganda (success & info) dan respon detil JSON dari API Node.js.
* **v1.7.4** (6 Jun 2026) - Reposisi Card Koneksi Perangkat Bot:
  - Memindahkan card koneksi ke bagian bawah pengaturan WA agar tab navigasi lebih responsif dan lebar.
* **v1.7.5** (6 Jun 2026) - Real-time Audit Logs & Hubungkan Grup WA via Link:
  - Menampilkan log audit WA secara real-time via Socket.io dan tombol hubungkan grup WA baru via link undangan.
* **v1.7.6** (6 Jun 2026) - Perbaikan Timeout WhatsApp Broadcast & Optimasi Paralel:
  - Fail fast saat WhatsApp bot terputus (isConnected = false) untuk menghindari timeout 8 detik di CI4.
  - Mengirim notifikasi KBM/PKL secara paralel menggunakan Promise.allSettled() di background Express.
  - Trimming leading/trailing space pada JID agar tidak error saat parsing.
* **v1.7.7** (6 Jun 2026) - Grup Terpusat & Keterangan KBM Absent WA:
  - Tombol "Set Keduanya" di WhatsApp Settings -> Groups tab untuk memudahkan pengaturan satu grup WA terpusat.
  - Validasi catatan kehadiran siswa (sakit, izin, alpha wajib diisi keterangan) baik di sisi client (JS) maupun server (PHP).
  - Menampilkan nama dan catatan/keterangan detail siswa yang tidak hadir pada broadcast WA absensi KBM.
* **v1.7.8** (6 Jun 2026) - Otomatis Kirim Rekap Konsolidasi KBM Harian:
  - Pengecekan status pengisian absensi seluruh kelas di Node.js API.
  - Jika 100% kelas sudah mengisi absen hari ini, rekapitulasi sekolah dikirim secara otomatis ke grup WA.
  - Template `kbm_consolidated_recap` yang dapat dikustomisasi via dashboard.
* **v1.7.9** (6 Jun 2026) - Redirect Template Tab Setelah Simpan:
  - Redirect `updateTemplates()` ke `/whatsapp-settings?tab=templates` agar perubahan langsung terlihat.
* **v1.7.10** (6 Jun 2026) - Kirim Laporan KBM ke Wali Kelas:
  - Broadcast absensi KBM ke nomor WA wali kelas secara paralel bersamaan dengan pengiriman ke grup WA dan fallback admin.
* **v1.7.11** (6 Jun 2026) - Respon Asinkron KBM Broadcast:
  - Endpoint `/api/attendance/broadcast` langsung mengembalikan 200 OK, pengiriman pesan WA dilakukan di background thread. Menghilangkan cURL timeout 28 di dashboard.
* **v1.7.12** (6 Jun 2026) - Pembaruan Format Template Bot WhatsApp:
  - 7 template pesan diperbarui: hapus garis pembatas tebal, hapus link localhost, footer seragam `_Pesan ini dikirim otomatis oleh SIAKANUDA ~ SMK NU Darussalam_`.
* **v1.7.13** (6 Jun 2026) - Penanda Pembaruan Absensi KBM:
  - Deteksi update absensi di tanggal yang sama, tambahkan label `⚠️ [#Pembaruan Absensi]` pada broadcast WA KBM.
* **v1.7.14** (6 Jun 2026) - Pisahkan Statistik KBM & PKL di Dashboard:
  - Card terpisah "Siswa PKL Hadir" di dashboard admin/kepsek/guru/guru_bk. Query KBM difilter agar tidak tercampur data PKL.
* **v1.7.15** (6 Jun 2026) - Sinkronisasi Dokumentasi & Perbaikan UI PKL:
  - Tombol aksi laporan PKL sejajar horizontal (flexbox). Sinkronisasi versi di `package.json`, `AI_CONTEXT.md`, `README.md`, `STATUS_FITUR.md`.
* **v1.8.0** (7 Jun 2026) - Penyempurnaan Alur Transisi Tahun Ajaran Baru:
  - Penambahan kolom `is_active` di database students dan konfigurasi model.
  - Implementasi download Excel siswa aktif dengan SheetJS.
  - Perubahan logic import agar upsert tidak meng-overwrite password siswa lama.
  - Penambahan checkbox untuk menonaktifkan siswa yang tidak terdaftar di Excel.
  - Filter siswa nonaktif di pencarian, absensi KBM harian, dan dashboard.
* **v1.8.1** (7 Jun 2026) - Penyederhanaan Tahun Pelajaran Tanpa Semester:
  - Menyembunyikan input dropdown semester dan kolom semester di form/tabel UI Tahun Pelajaran.
  - Mengatur default semester ke 'Ganjil' di backend controller (`TahunPelajaran.php`) untuk memenuhi SQLite check constraint secara transparan.
  - Menampilkan kalender akademik setahun penuh (Ganjil & Genap) pada dashboard untuk TP aktif.
  - Menghilangkan label semester pada tampilan navbar header dan PDF metadata.
* **v1.8.2** (7 Jun 2026) - Koneksi Kalender Akademik ke Tahun Pelajaran Master:
  - Menghubungkan daftar tahun pilihan dan default selected year pada Kalender Akademik dengan master tabel `tahun_pelajaran`.
  - Memastikan jika tahun ajaran baru berganti, admin dipaksa menginput atau mengunggah ulang data kalender akademik yang aktif karena masih kosong.
* **v1.8.3** (7 Jun 2026) - Pembatasan Kelompok PKL Khusus Kelas XII:
  - Membatasi daftar pilihan siswa (Ketua & Anggota) di menu Kelompok PKL agar hanya memuat siswa aktif kelas XII.
  - Memindahkan logika default role PKL (`anggotapkl`) dari kelas XI ke kelas XII pada model sinkronisasi dan pengontrol user.
* **v1.8.4** (7 Jun 2026) - Import Kelompok PKL via Excel:
  - **Bulk Import Kelompok PKL**: Fitur unggah file Excel/CSV untuk memetakan kelompok PKL secara massal.
  - **Template Unduh Dinamis**: Tombol download template format Excel kelompok PKL yang digenerate client-side menggunakan SheetJS.
  - **Ketua Kelompok Opsional**: Mendukung pengosongan data ketua kelompok saat import maupun input manual (mengatasi kendala ketua belum ditetapkan).
  - **Otomatisasi & Sinkronisasi Peran**: Menjalankan sinkronisasi peran (`role`) siswa secara otomatis setelah import.
  - **Keamanan Validasi Database**: Menyempurnakan penyimpanan agar `ketua_phone` default ke string kosong `""` (bukan `null`) guna mencegah kegagalan NOT NULL constraint di SQLite.
* **v1.8.5** (7 Jun 2026) - Auto-grouping Import PKL per Siswa:
  - **Format Import Per Siswa**: Mendukung data input baris per siswa. Sistem otomatis melakukan grouping/pengelompokan siswa berdasarkan kesamaan `Tempat Pkl`.
  - **Template Baru**: Tombol download template menghasilkan format template "Per Siswa" secara dinamis.
  - **Akses Pengaman Laporan**: Siswa diblokir masuk menu laporan PKL jika kelompok PKL belum ditentukan Ketua Kelompok oleh Admin.
  - **Resolusi Bug**: Menghilangkan typo JavaScript dan merapikan duplicate closing curly brackets pada file import view.
* **v1.8.6** (7 Jun 2026) - Integrasi 9Router AI Gateway:
  - Refactor parser menggunakan standar REST OpenAI untuk dukungan multi-model dan failover otomatis.
* **v1.8.7** (7 Jun 2026) - Template PDF Kustom & Pembatasan Hak Akses Cetak PKL:
  - **Penerapan Template PDF Kustom**: Menggunakan `template_laporan_harian_pkl.pdf` sebagai background overlay untuk PDF laporan harian PKL (pdf-lib embed page + logo high-res overlay).
  - **Pembatasan Hak Akses Cetak PDF Ketat Berbasis Role**:
    - **Anggota PKL**: Hanya bisa melihat riwayat laporan, **tidak bisa cetak PDF sama sekali**.
    - **Ketua PKL**: Hanya bisa cetak **Rekap Mingguan (7 hari gabungan)** via card "Rekap Mingguan" di student_report.php. Tombol cetak harian per-report dihapus.
| `admin` | CRUD Data Master, Laporan Absensi/PKL, WhatsApp Panel, Hapus Data |
| `kepsek` | Read-only Laporan Absensi, PKL, Pelanggaran, Kotak Suara |
| `guru_bk` | Input & Rekap Absensi, Input Pelanggaran BK |
| `guru_mapel` | Input Absensi Kelas, Lihat Jadwal |
| `siswa` | Lihat Jadwal, Absensi, isi Kotak Suara, Laporan PKL Harian |

---

## ⏰ Jadwal Otomatis (Cron Jobs)

| Waktu WIB | Aksi Notifikasi WhatsApp |
|---|---|
| **00:30** | Backup otomatis database SQLite + cleanup backup > 7 hari |
| **08:30** | Pengingat kelas yang belum di-absen oleh Guru |
| **16:00** | Pengingat pengisian jurnal harian PKL untuk Ketua Kelompok |
| **19:00** | Eskalasi laporan absen PKL ke Guru Pembimbing |
| **23:59** | Auto-alpha untuk siswa KBM yang tidak memiliki keterangan absen |

---

## 📍 Riwayat Versi

* **v1.0.0** (30 Mei 2026) - Rilis dasar bot WhatsApp & dashboard SPA Vanilla JS.
* **v1.1.0** (31 Mei 2026) - Implementasi PWA, wrapper APK Android, & sinkronisasi Supabase.
* **v1.2.0** (1 Jun 2026) - Migrasi dashboard ke CodeIgniter 4 dengan SQLite.
* **v1.2.3** (2 Jun 2026) - Overhaul penyatuan dashboard ke port 8080 (CI4) sebagai pintu masuk tunggal, penyederhanaan bot WhatsApp menjadi background service.
* **v1.2.4** (2 Jun 2026) - Implementasi menu pintasan cepat (shortcut) adaptif berbasis peran di dashboard utama, perbaikan ikon Manajemen Guru di sidebar, serta penambahan tombol Kembali dinamis di navbar untuk optimalisasi navigasi pada WebView APK.
* **v1.2.5** (2 Jun 2026) - Widget form absensi PKL inline di dashboard untuk ketua_pkl, sidebar menu terstruktur per role (treeview PKL), dashboard menampilkan status PKL real-time untuk siswa & ketua, shortcut khusus ketua_pkl.
* **v1.2.6** (2 Jun 2026) - Shortcut & sidebar link "Alumni & BKK" untuk admin (external link ke smknudarussalam.sch.id/bkk), dokumentasi folder BKK sebagai standalone workspace dengan deployment terpisah.
* **v1.2.7** (2 Jun 2026) - Penambahan script serve.js untuk BKK portal lokal, perbaikan .gitignore untuk membuang writable/debugbar logs.
* **v1.2.8** (3 Jun 2026) - Revisi dashboard siswa: shortcut card diselaraskan dengan sidebar, pelanggaran hanya tampil milik siswa login, form inline Kotak Suara untuk siswa, welcome message role-aware.
* **v1.2.9** (3 Jun 2026) - Pisahkan siswa biasa vs siswa-anggota-PKL: absensi KBM menampilkan rekap personal siswa (kartu identitas + ringkasan + riwayat), poin pelanggaran tampil individual dengan total poin, menu PKL hanya muncul untuk siswa yang terdaftar di kelompok PKL.
* **v1.2.10** (3 Jun 2026) - Proteksi ketat menu & halaman PKL: deteksi `is_pkl_member` sejak login di session, sembunyikan Laporan PKL pada welcome description siswa biasa, dan alihkan akses langsung `/pkl` siswa non-PKL ke dashboard dengan pesan error.
* **v1.3.0** (3 Jun 2026) - Restrukturisasi hak akses 7 role test: [admin] full, [kepsek] full kecuali hapus, [guru] write absensi KBM, read-only siswa/jadwal/pelanggaran, pembimbing PKL, [guru_bk] guru + write pelanggaran & BK, [siswa] personal read-only + kotak saran, [ketuapkl] siswa + PKL group write + cetak PDF, [anggotapkl] siswa + PKL read-only. Expose menu & shortcut 'Alumni & BKK' ke semua role. Penyederhanaan label login dan perbaikan navbar, sidebar, serta footer untuk tampilan mobile.
* **v1.4.0** (3 Jun 2026) - Implementasi modul Kalender Akademik (CRUD untuk admin, read-only untuk seluruh role), pembaruan tampilan dashboard utama untuk menampilkan Agenda Kesiswaan dan Profil Akademik serta menyematkan Kalender Akademik semester aktif.
* **v1.4.1** (3 Jun 2026) - Pembaruan data tabel Kalender Akademik semester ganjil & genap TP 2025/2026 di SQLite database, dan penambahan widget Jam Real-Time responsif di header dashboard utama.
* **v1.4.2** (3 Jun 2026) - Implementasi modul Prestasi Siswa (tabel database baru, controller, view, dan menu shortcut/sidebar), penyediaan akses Catatan BK (read-only) untuk semua peran siswa, serta penambahan tombol simpan kalender akademik di bawah tabel (admin).
* **v1.4.3** (3 Jun 2026) - Prestasi dibuka untuk semua role (universal), guru dapat menambah prestasi, perbaikan layout select dropdown.
* **v1.4.4** (3 Jun 2026) - Perbaikan layout input numerik yang terjepit, feedback siswa terlihat oleh pengirimnya.
* **v1.4.5** (3 Jun 2026) - Anonimisasi pengirim feedback untuk non-admin, auto-publish feedback baru, filter kata vulgar di Kotak Suara.
* **v1.4.6** (3 Jun 2026) - Penyempurnaan modul PKL: merge shortcut, toggle libur dengan alasan, upload foto per siswa dengan validasi, view read-only anggota, tombol status, pelacakan lokasi, batas jurnal 75 karakter, serta perbaikan camelCase/snake_case dan parsing photo_url. Penambahan opsi `USE_LOCAL_AUTH` untuk testing bot WA lokal.
* **v1.5.0** (3 Jun 2026) - Rombak total panel port 7860: dari SPA penuh (1357 baris HTML + 136KB JS + 46KB CSS) menjadi WA Panel ringan (~160 baris HTML + 6KB JS + 4KB CSS). Menghapus semua modul akademik duplikat, menyisakan hanya manajemen koneksi WA (QR code, kirim pesan, log, cron trigger). Hapus PWA (sw.js, manifest.json).
* **v1.5.1** (3 Jun 2026) - Integrasi PKL ke semua role admin/guru: guru_bk ditambahkan ke menu PKL sidebar, guru_mapel bisa akses Kelompok PKL, guru hanya lihat laporan kelompok bimbingannya (filter pembimbing), tambah 3 stat card di halaman Laporan PKL (total/sudah lapor/belum lapor), shortcut PKL di dashboard guru_bk.
* **v1.5.2** (4 Jun 2026) - Security Hardening: aktifkan CSRF protection global + token randomization, tambah `$validationRules` di 12 model CI4 + `validate()` di 9 controller, hapus hardcoded fallback passwords (`guruhebat`/`guru123`/`adminsmknuda`), session IP binding + regeneration, buat `RoleFilter.php` untuk role-based routing, restrict MessageLog ke admin-only, fix GET-based delete di Kalender Akademik, lock CORS Node.js ke localhost:8080, auth test endpoints, whitelist SQL column names di `updateStudent()`/`updateKelompokPkl()`, validasi file upload PKL (JPG/PNG max 5MB), sanitize error messages, tambah backup database harian (cron 00:30 WIB + CI4 Spark command `db:backup`), tambah `JWT_SECRET` ke `.env.example`.
* **v1.6.0** (4 Jun 2026) - Implementasi konsep **Tahun Pelajaran** (Academic Year) sebagai indikator utama data transaksional di SIAKANUDA. Penambahan tabel `tahun_pelajaran`, migrasi kolom `tahun_pelajaran_id` di 8 tabel transaksional, pembuatan model/controller/view manajemen Tahun Pelajaran aktif (khusus admin), modifikasi `BaseController` untuk global data sharing, integrasi filter `tahun_pelajaran_id` di 7 controller dashboard dan di script Node.js background cron jobs/database adapter.
* **v1.6.1** (4 Jun 2026) - Perbaikan kritis alur Ketua PKL: Proxy cetak PDF via CI4 (port 8080) untuk mengamankan autentikasi dan memecahkan masalah CORS/port, sanitasi nama berkas unggahan foto untuk siswa dengan karakter titik/spasi (mencegah kegagalan upload PHP), serta perbaikan daemon `photo_sync.js` agar melestarikan struktur objek JSON pemetaan nama siswa ke URL foto saat sinkronisasi Supabase.
* **v1.6.2** (4 Jun 2026) - Penyempurnaan Laporan PKL & Penguncian PDF: Menambahkan kartu pratinjau laporan terkirim (lock view) untuk Ketua PKL demi mencegah penimpaan data yang tidak disengaja, menyediakan opsi tombol "Ubah Laporan" untuk membuka form edit secara asinkron (JS), mengunci tombol "Cetak PDF" agar hanya dapat diklik setelah 7 hari dari tanggal laporan (pengunduhan seminggu sekali), dan memperbagus penanganan eror offline PDF generator (port 7860 mati).
* **v1.6.3** (4 Jun 2026) - Implementasi Fitur Hapus Laporan PKL oleh Admin: Menyediakan tombol aksi "Hapus" khusus bagi Admin di daftar laporan PKL harian. Penghapusan ini mencakup pembersihan file foto bukti fisik dari direktori local/dashboard, pembersihan otomatis absensi KBM harian siswa kelompok terkait untuk tanggal laporan tersebut, dan pembersihan record dari database.
* **v1.6.4** (4 Jun 2026) - Penyederhanaan Tabel Laporan PKL & Detail Modal: Mengubah tampilan tabel Laporan PKL harian di dashboard admin/guru agar lebih ringkas, rapi, dan padat (hanya menampilkan rangkuman kehadiran, jurnal, dan satu foto bukti pertama). Menyediakan tombol "Detail" yang menampilkan rincian presensi kelompok secara lengkap, jurnal setiap anggota, serta seluruh berkas foto bukti fisik dalam Bootstrap Modal tanpa *reload* halaman.
* **v1.6.5** (4 Jun 2026) - Penyelarasan & Deployment Services Debian: Deployment pembaruan kode SIAKANUDA ke server Debian (IP 100.110.83.48), penyelarasan nama systemd service dari `siswabot.service` menjadi `bot.siswa.service` untuk menyamakan dengan blueprint produksi, pembersihan service lama, pemuatan skema database SQLite `tahun_pelajaran` secara otomatis lewat daemon startup, serta pengaktifan kembali layanan `bot.siswa` dan `siakadash` dengan status active (running).
* **v1.6.7** (4 Jun 2026) - Kustomisasi WA Broadcast & Form Detail PKL (Sakit, Izin, Alpha): Pembaruan format WhatsApp broadcast agar menampilkan nama pembimbing, jam WIB, dan ringkasan kehadiran ringkas. Desain form detail PKL dinamis berbasis status absen (Hadir/Sakit/Izin/Alpha) dengan validasi wajib foto bukti dan isian alasan di client/server.
* **v1.6.8** (5 Jun 2026) - Notifikasi Dashboard Pintar, Import Excel Interaktif, dan Peningkatan Keamanan Login:
  - **Notifikasi Dashboard Pintar**: Penambahan modul notifikasi dinamis berbasis peran (role-based) untuk mendeteksi data KBM (absensi kelas) dan laporan PKL kelompok yang belum terisi pada hari berjalan.
  - **Penghapusan Auto-Alpha**: Menonaktifkan cron job `runAutoAlphaJob` pukul 23:59 WIB karena default kehadiran siswa adalah Hadir. Guru hanya mengisi data siswa yang sakit, izin, atau alpha.
  - **Penyederhanaan UI KBM**: Mengubah tombol "Hadir Semua (99)" menjadi "Hadir Semua" pada lembar absensi kelas KBM.
  - **Fitur Import Excel (.xlsx)**: Implementasi client-side parser menggunakan library SheetJS di browser pada panel Manajemen Siswa dan Manajemen Guru/Whitelist, lengkap dengan pratinjau data (Live Preview) dan download berkas template Excel (.xlsx) resmi.
  - **Default Password & Reset**: Menetapkan password default siswa = NISN dan guru = No. WhatsApp. Menambahkan tombol "Reset" password ke default di tabel manajemen siswa.
  - **Keamanan Login & Paksa Ganti Password**: Menambahkan verifikasi keamanan di mana Ketua PKL yang masuk menggunakan password default wajib mengganti passwordnya sebelum mengakses dashboard.
  - **Login Tabbed & Dropdown Pilihan Guru**: Mendesain ulang halaman login dengan tab pemilih "Siswa" (input NISN) dan "Guru / Staf" (memilih nama dari dropdown select) untuk kepraktisan akses guru.
  - **Tugas Tambahan Guru**: Menambahkan kolom `tugas_tambahan` ke skema tabel `allowed_numbers`, view manajemen guru, edit modals, dan template import Excel.
* **v1.6.9** (5 Jun 2026) - Cetak Rekap Absensi Bulanan & Kop Surat PDF:
  - **Cetak Rekap Absensi Bulanan (PDF)**: Implementasi rute dan pengontrol `printPdf()` di dashboard CI4 dan API endpoint di Node.js background service untuk mencetak laporan kehadiran bulanan per kelas.
  - **Dukungan Kop Surat PDF Fisik**: Mengintegrasikan pustaka `pdf-lib` untuk menempelkan (overlay) file `Kop surat.pdf` fisik secara otomatis pada halaman pertama laporan PDF, dengan fallback kop surat manual jika file fisik tidak ada.
* **v1.6.10** (5 Jun 2026) - Matriks Absensi Bulanan & Margin Kop Surat PDF:
  - **Penyelarasan Margin Kop Surat**: Menyesuaikan margin kiri dokumen PDF ke 78pt dan margin kanan ke 44pt di `pdf_generator.js` agar sejajar dengan Kop Surat cetak. Menggeser teks, table, dan tandatangan ke x=78pt.
  - **Format Matriks Absensi Bulanan**: Mengubah rekap bulanan kelas menjadi tabel matriks horizontal ber-grid dengan 28-31 kolom hari, kolom No, Nama, NIS, dan total (H/S/I/A).
  - **Penyederhanaan & Reposisi Banner Dapodik**: Memindahkan banner akademik besar di bawah flash message dan memindahkannya ke tengah navbar atas di `template.php` dengan format teks ringkas `Dapodik: TP [Tahun] ([Semester])`.
* **v1.6.11** (5 Jun 2026) - PDF Rekap KBM Landscape & Deteksi Hari Minggu:
  - **PDF Rekap KBM Landscape**: Mengubah layout dokumen rekap absensi bulanan menjadi Landscape (A4) di `pdf_generator.js` agar memuat 28-31 kolom secara lapang, dengan lebar tabel 670pt, margin kiri 110pt (lurus Kop Surat stretched), dan margin kanan 44pt.
  - **Highlight Hari Minggu**: Mendeteksi hari Minggu secara dinamis (`getDay() === 0`). Memberi warna merah pada angka tanggal Minggu di header, dan mewarnai latar belakang sel kolom hari Minggu dengan warna merah muda lembut (`#fdf2f2`) pada tabel absensi.
* **v1.6.12** (5 Jun 2026) - Integrasi Kop Surat Landscape & Pembersihan Git status:
  - **Integrasi Kop Surat Landscape**: Mendefinisikan konstanta `KOP_LANDSCAPE_PATH` untuk file `data/Kop surat lanscape.pdf`. Mengubah logic `applyKopSurat` agar mendeteksi orientasi halaman PDF (Landscape vs Portrait) lewat ukuran halaman dan menempelkan Kop Surat yang sesuai secara otomatis. Menyelaraskan rujukan di `generateClassReportPDF` agar menggunakan `KOP_LANDSCAPE_PATH` untuk mengukur layout tabel, margin, dan posisi signature.
  - **Pembersihan Git status**: Mengabaikan folder `siakanuda-apk/` di `.gitignore` agar tidak mengotori repositori utama. Menambahkan file `data/Kop surat lanscape.pdf` ke Git untuk dipantau secara resmi.
* **v1.6.13** (5 Jun 2026) - Auto-submit Hadir Semua & Perbaikan PDF Rekap:
  - **Auto-submit Hadir Semua**: Tombol "Hadir Semua" pada absensi kelas KBM kini langsung mengirimkan formulir (saving) secara asinkron/otomatis dan mengarahkan kembali ke daftar absensi kelas dengan melestarikan parameter tanggal terpilih.
  - **Perbaikan PDF Rekap KBM**: Menggeser start Y ke `145` agar isi PDF tidak bertabrakan dengan Kop Surat Landscape, memperlebar tabel menjadi `680` (nama siswa diperluas ke `160`, NIS disesuaikan ke `35`, dan kolom rekap H/S/I/A disesuaikan ke `14`), serta menghapus kolom tanda tangan kepala sekolah ("Mengetahui, Kepala Sekolah...").
* **v1.6.14** (5 Jun 2026) - Perubahan Identitas NIS ke NISN & Sinkronisasi Margin PDF:
  - **Perubahan Identitas NIS ke NISN**: Mengubah nama kolom identitas di tabel PDF rekap bulanan menjadi `NISN` dan memperlebar lebarnya (`nisWidth`) dari `35` menjadi `60` agar nomor NISN (10 digit) dapat ditampilkan dalam satu baris rata tanpa mengalami pemotongan/wrapping.
  - **Sinkronisasi Margin**: Menyelaraskan margin kiri halaman landscape menjadi `78` (sama seperti portrait) dan lebar tabel menjadi `720` agar sejajar dengan garis horizontal dari `Kop surat lanscape.pdf` resmi. Lebar nama siswa (`namaWidth`) ditingkatkan menjadi `180`.
* **v1.6.15** (5 Jun 2026) - Format PDF Rekap Hitam Putih & Integrasi Meta Info Baru:
  - **Hitam Putih Polos & Hari Minggu**: Menghapus background zebra striping (`#fafafa`) dan mewarnai seluruh huruf status absen (S, I, A) menjadi hitam `#000000` agar tabel berformat hitam putih murni. Namun, shading/highlight hari Minggu (`#fdf2f2` & `#fcedeb`) dan shading header tabel tetap dipertahaman.
  - **Margin 1.27 cm (36 pt) & Lebar Tabel 770**: Menyesuaikan seluruh margin dokumen menjadi `36` (setara 1.27 cm). Lebar tabel ditingkatkan menjadi `770` dan titik mulai x disesuaikan ke `36`. Table wrap check disesuaikan ke `530` dan posisi awal table halaman baru disesuaikan ke `70`.
  - **Metadata Informasi Absensi**: Menambahkan judul, tahun pelajaran, periode bulan, wali kelas, serta catatan kecil cetak miring berisi tanggal unduh dan nama pengunduh di atas tabel absensi.
* **v1.6.16** (5 Jun 2026) - Header Dua Baris PDF & Logo Overlay Resolusi Tinggi:
  - **Header Dua Baris (Two-Row Header)**: Mendesain ulang header tabel absensi menjadi 2 baris (No, Nama, NISN setinggi 36pt, dengan cell merged "Periode Bulan..." di atas tanggal, dan "Rekap" di atas kolom H/S/I/A). Mengubah `drawVerticalGridLines` agar garis pembatas tanggal tidak memotong cell merged baris pertama.
  - **Overlay Logo Resolusi Tinggi**: Memperbarui `applyKopSurat` agar menggambar persegi putih solid di area logo Kop Surat Lanskap untuk menutupi placeholder, lalu menempelkan logo PNG asli dengan resolusi tajam.
  - **Penyelarasan Teks & Info**: Judul rekap absensi diposisikan di tengah (*center-aligned*) dan informasi Wali Kelas serta Tanggal Unduh terformat rapi rata kiri.
* **v1.6.17** (5 Jun 2026) - Koreksi Jarak Judul & Overlay Logo Presisi PDF:
  - **Overlay Logo Resolusi Tinggi Presisi**: Mengganti programmatic logo overlay lama yang meleset koordinatnya dengan overlay logo presisi tinggi (menggunakan berkas logo-smk.png resolusi 540x540px) di dalam helper `applyKopSurat` pada pdf_generator.js.
  - **Presisi Jarak Judul & Table**: Menetapkan koordinat tetap bagi elemen-elemen Page 1 (Title y=118, Subtitle y=131, Wali Kelas y=159, Tanggal Unduh y=172, Table y=193) menyamai layout `format-revisi.pdf` secara persis dan memperkecil jarak kosong yang berlebih di bawah Kop Surat.
  - **Optimasi Baris Halaman 1**: Mengubah batas wrap tabel ke `540` agar muat persis 18 baris siswa di halaman pertama.
* **v1.6.18** (5 Jun 2026) - Rekap Mingguan PKL & Kompresi Foto Otomatis:
  - **Kompresi Foto Otomatis**: Integrasi `sharp` di `photo_sync.js` untuk me-resize gambar hingga lebar max 640px dan kualitas JPEG 50% sebelum diunggah ke Supabase Storage, sangat menghemat kuota siswa & storage server.
  - **Deteksi Akses Lokal (WiFi)**: Helper `network_helper.php` untuk memisahkan akses lokal (WiFi Sekolah/Tailscale/localhost) dan internet. Membatasi pengunduhan PDF laporan harian & mingguan hanya untuk akses lokal demi performa server.
  - **PDF Rekap Mingguan PKL**: Membuat PDF A4 Portrait 6 halaman (1 hari = 1 halaman, Senin-Sabtu) yang memuat rekap absensi harian kelompok, jurnal kegiatan, foto bukti asli, dan Kop Surat overlay otomatis.
  - **UI/UX Rekap Mingguan**: Penambahan menu Rekap Mingguan, modal preview asinkron yang memuat thumbnail Supabase, serta tombol cetak mingguan di dashboard siswa (`student_report.php`) dan dashboard admin/pembimbing (`index.php`).
* **v1.7.1** (6 Jun 2026) - Broadcast Otomatis Absensi KBM ke Grup WA:
  - **Pemicu Broadcast**: Integrasi broadcast ke WhatsApp group `SCHOOL_GROUP_JID` pada `saveClass()` di [Attendance.php](file:///F:/Antigravity/siakanuda/dashboard/app/Controllers/Attendance.php) setelah absensi KBM disimpan.
  - **API Endpoint KBM**: Menambahkan `/api/attendance/broadcast` di [server.js](file:///F:/Antigravity/siakanuda/execution/server.js) untuk memproses formatting pesan KBM secara asinkron.
  - **Dukungan database**: Tabel `bot_templates` dan `cron_configs` ditambahkan ke `initSchema()` di [db.js](file:///F:/Antigravity/siakanuda/execution/db.js).
* **v1.7.0** (6 Jun 2026) - Konsolidasi Fitur & Peningkatan Fleksibilitas WhatsApp Bot:
  - **Kelompok PKL**: Ketua kelompok PKL opsional, melonggarkan validasi `ketua_phone` di [KelompokPklModel.php](file:///F:/Antigravity/siakanuda/dashboard/app/Models/KelompokPklModel.php) dan template form.
  - **Pengaturan WA Terpusat**: Menu sidebar baru "Pengaturan WA" yang menyatukan log qr code, kirim pesan manual, whitelist allowed numbers (Excel import), target broadcast group JID, dan dynamic cron configurations.
* **v1.6.24** (6 Jun 2026) - Overhaul Laporan PDF Harian & Resolusi Kritis SQLite I/O exFAT:
  - **Penyelesaian Disk I/O Error**: Migrasi `journal_mode` SQLite dari `WAL` ke `DELETE` di `execution/db.js` demi mendukung drive exFAT portabel (menghilangkan file `.db-shm` dan `.db-wal` secara otomatis).
  - **Redesain PDF Harian PKL**: Mengubah format halaman laporan harian PDF menjadi 1 halaman penuh murni (Compact Card Layout) dengan margin minimal, area foto diperbesar tanpa blur, dan pembersihan karakter emoji/tidak dikenal untuk mencegah error rendering.
  - **Pembaruan Form & Validasi Absensi**: Mengubah label lokasi menjadi alert peringatan wajib di dashboard Ketua PKL, mengunci input kehadiran/jurnal sebelum lokasi diisi, dan menampilkan pesan sukses interaktif berformat waktu lokal lengkap (WIB).
  - **Kustomisasi Broadcast WA**: Menambahkan cetak kelas Ketua `(Kelas)`, mengubah label lokasi absensi, dan menyertakan label `⚠️ *[#Perubahan Laporan]*` saat terjadi pembaruan laporan.
  - **Shortcut Cetak Admin**: Menambahkan tombol pintasan "Cetak Harian" pada panel admin untuk mempermudah cetak & uji coba laporan PDF harian untuk tanggal berapa pun.
* **v1.6.23** (6 Jun 2026) - Fix Bug Validasi Textarea PKL, Contoh Isian, & Karakter Counter:
  - **Fix Bug Validasi**: Memperbaiki selector javascript validator agar membaca nilai `<textarea>` alih-alih `<input>`, menyelesaikan bug validasi minimal 75 karakter.
  - **Karakter Counter**: Menambahkan counter interaktif di bawah isian jurnal (berwarna merah jika kurang dari 75, hijau jika terpenuhi).
  - **Lega & Contoh Isian**: Memperbesar rows textarea menjadi 4 (min-height 90px) dan melengkapinya dengan placeholder contoh isian realistis di setiap status (Hadir, Sakit, Izin, Alpha).
* **v1.6.22** (6 Jun 2026) - Pembaruan Input Textarea PKL & Pengembalian Lokasi Presensi:
  - **Textarea Fleksibel**: Mengubah input Jurnal, Sakit, Izin, dan Alpha menjadi `<textarea>` dengan default rows 2-3 dan auto-growing berdasarkan tinggi isian.
  - **Lokasi Presensi**: Menampilkan kembali kolom input "Tempat Melakukan Presensi" (`location_data`) yang wajib diisi jika PKL Masuk dan disembunyikan/opsional saat PKL Libur.
  - **Integrasi Notifikasi & Modal Admin**: Menampilkan `location_data` di WhatsApp broadcast notification saat PKL Masuk dan di modal detail laporan halaman admin PKL.
* **v1.6.21** (6 Jun 2026) - Tombol Simpan Cepat Libur & Optimasi Format WA PKL Libur:
  - **Tombol Simpan Cepat Libur**: Menambahkan tombol simpan/submit di dalam form alasan libur pada `student_report.php` agar ketua PKL tidak perlu men-scroll ke bawah saat tempat PKL libur.
  - **Format WA Broadcast Libur**: Memperbarui notifikasi broadcast WA saat libur di `server.js` untuk menampilkan seluruh nama anggota kelompok (satu baris dipisahkan koma) dan menyematkan kelas pada nama Ketua Kelompok.
* **v1.6.20** (6 Jun 2026) - Penyederhanaan Widget Absensi PKL Ketua PKL:
  - **Penyederhanaan Widget Dashboard**: Menyederhanakan tampilan widget absensi PKL pada dashboard utama `ketua_pkl` menjadi satu status card premium yang dapat diklik (mengarahkan langsung ke halaman pengisian `/pkl`).
  - **Pembersihan JS**: Menghapus form absensi inline dan membersihkan JavaScript validasi/manipulasi form terkait pada halaman dashboard utama.
* **v1.6.19** (5 Jun 2026) - UI Manajemen Peran Siswa & Otomatisasi Peran PKL:
  - Dropdown Peran/Role di manajemen siswa dan searchable single student picker ketua kelompok PKL.
  - Sinkronisasi otomatis peran siswa dan default kelas XI menjadi anggotapkl.
* **v1.7.2** (6 Jun 2026) - Perbaikan Fallback KBM Broadcast:
  - Logika fallback ke BROADCAST_GROUP_JID dan isolasi try-catch per admin untuk menjamin fallback WA berjalan aman.
* **v1.7.3** (6 Jun 2026) - Dual Notifikasi Hasil Absensi KBM:
  - Flashdata ganda (success & info) dan respon detil JSON dari API Node.js.
* **v1.7.4** (6 Jun 2026) - Reposisi Card Koneksi Perangkat Bot:
  - Memindahkan card koneksi ke bagian bawah pengaturan WA agar tab navigasi lebih responsif dan lebar.
* **v1.7.5** (6 Jun 2026) - Real-time Audit Logs & Hubungkan Grup WA via Link:
  - Menampilkan log audit WA secara real-time via Socket.io dan tombol hubungkan grup WA baru via link undangan.
* **v1.7.6** (6 Jun 2026) - Perbaikan Timeout WhatsApp Broadcast & Optimasi Paralel:
  - Fail fast saat WhatsApp bot terputus (isConnected = false) untuk menghindari timeout 8 detik di CI4.
  - Mengirim notifikasi KBM/PKL secara paralel menggunakan Promise.allSettled() di background Express.
  - Trimming leading/trailing space pada JID agar tidak error saat parsing.
* **v1.7.7** (6 Jun 2026) - Grup Terpusat & Keterangan KBM Absent WA:
  - Tombol "Set Keduanya" di WhatsApp Settings -> Groups tab untuk memudahkan pengaturan satu grup WA terpusat.
  - Validasi catatan kehadiran siswa (sakit, izin, alpha wajib diisi keterangan) baik di sisi client (JS) maupun server (PHP).
  - Menampilkan nama dan catatan/keterangan detail siswa yang tidak hadir pada broadcast WA absensi KBM.
* **v1.7.8** (6 Jun 2026) - Otomatis Kirim Rekap Konsolidasi KBM Harian:
  - Pengecekan status pengisian absensi seluruh kelas di Node.js API.
  - Jika 100% kelas sudah mengisi absen hari ini, rekapitulasi sekolah dikirim secara otomatis ke grup WA.
  - Template `kbm_consolidated_recap` yang dapat dikustomisasi via dashboard.
* **v1.7.9** (6 Jun 2026) - Redirect Template Tab Setelah Simpan:
  - Redirect `updateTemplates()` ke `/whatsapp-settings?tab=templates` agar perubahan langsung terlihat.
* **v1.7.10** (6 Jun 2026) - Kirim Laporan KBM ke Wali Kelas:
  - Broadcast absensi KBM ke nomor WA wali kelas secara paralel bersamaan dengan pengiriman ke grup WA dan fallback admin.
* **v1.7.11** (6 Jun 2026) - Respon Asinkron KBM Broadcast:
  - Endpoint `/api/attendance/broadcast` langsung mengembalikan 200 OK, pengiriman pesan WA dilakukan di background thread. Menghilangkan cURL timeout 28 di dashboard.
* **v1.7.12** (6 Jun 2026) - Pembaruan Format Template Bot WhatsApp:
  - 7 template pesan diperbarui: hapus garis pembatas tebal, hapus link localhost, footer seragam `_Pesan ini dikirim otomatis oleh SIAKANUDA ~ SMK NU Darussalam_`.
* **v1.7.13** (6 Jun 2026) - Penanda Pembaruan Absensi KBM:
  - Deteksi update absensi di tanggal yang sama, tambahkan label `⚠️ [#Pembaruan Absensi]` pada broadcast WA KBM.
* **v1.7.14** (6 Jun 2026) - Pisahkan Statistik KBM & PKL di Dashboard:
  - Card terpisah "Siswa PKL Hadir" di dashboard admin/kepsek/guru/guru_bk. Query KBM difilter agar tidak tercampur data PKL.
* **v1.7.15** (6 Jun 2026) - Sinkronisasi Dokumentasi & Perbaikan UI PKL:
  - Tombol aksi laporan PKL sejajar horizontal (flexbox). Sinkronisasi versi di `package.json`, `AI_CONTEXT.md`, `README.md`, `STATUS_FITUR.md`.
* **v1.8.0** (7 Jun 2026) - Penyempurnaan Alur Transisi Tahun Ajaran Baru:
  - Penambahan kolom `is_active` di database students dan konfigurasi model.
  - Implementasi download Excel siswa aktif dengan SheetJS.
  - Perubahan logic import agar upsert tidak meng-overwrite password siswa lama.
  - Penambahan checkbox untuk menonaktifkan siswa yang tidak terdaftar di Excel.
  - Filter siswa nonaktif di pencarian, absensi KBM harian, dan dashboard.
* **v1.8.1** (7 Jun 2026) - Penyederhanaan Tahun Pelajaran Tanpa Semester:
  - Menyembunyikan input dropdown semester dan kolom semester di form/tabel UI Tahun Pelajaran.
  - Mengatur default semester ke 'Ganjil' di backend controller (`TahunPelajaran.php`) untuk memenuhi SQLite check constraint secara transparan.
  - Menampilkan kalender akademik setahun penuh (Ganjil & Genap) pada dashboard untuk TP aktif.
  - Menghilangkan label semester pada tampilan navbar header dan PDF metadata.
* **v1.8.2** (7 Jun 2026) - Koneksi Kalender Akademik ke Tahun Pelajaran Master:
  - Menghubungkan daftar tahun pilihan dan default selected year pada Kalender Akademik dengan master tabel `tahun_pelajaran`.
  - Memastikan jika tahun ajaran baru berganti, admin dipaksa menginput atau mengunggah ulang data kalender akademik yang aktif karena masih kosong.
* **v1.8.3** (7 Jun 2026) - Pembatasan Kelompok PKL Khusus Kelas XII:
  - Membatasi daftar pilihan siswa (Ketua & Anggota) di menu Kelompok PKL agar hanya memuat siswa aktif kelas XII.
  - Memindahkan logika default role PKL (`anggotapkl`) dari kelas XI ke kelas XII pada model sinkronisasi dan pengontrol user.
* **v1.8.4** (7 Jun 2026) - Import Kelompok PKL via Excel:
  - **Bulk Import Kelompok PKL**: Fitur unggah file Excel/CSV untuk memetakan kelompok PKL secara massal.
  - **Template Unduh Dinamis**: Tombol download template format Excel kelompok PKL yang digenerate client-side menggunakan SheetJS.
  - **Ketua Kelompok Opsional**: Mendukung pengosongan data ketua kelompok saat import maupun input manual (mengatasi kendala ketua belum ditetapkan).
  - **Otomatisasi & Sinkronisasi Peran**: Menjalankan sinkronisasi peran (`role`) siswa secara otomatis setelah import.
  - **Keamanan Validasi Database**: Menyempurnakan penyimpanan agar `ketua_phone` default ke string kosong `""` (bukan `null`) guna mencegah kegagalan NOT NULL constraint di SQLite.
* **v1.8.5** (7 Jun 2026) - Auto-grouping Import PKL per Siswa:
  - **Format Import Per Siswa**: Mendukung data input baris per siswa. Sistem otomatis melakukan grouping/pengelompokan siswa berdasarkan kesamaan `Tempat Pkl`.
  - **Template Baru**: Tombol download template menghasilkan format template "Per Siswa" secara dinamis.
  - **Akses Pengaman Laporan**: Siswa diblokir masuk menu laporan PKL jika kelompok PKL belum ditentukan Ketua Kelompok oleh Admin.
  - **Resolusi Bug**: Menghilangkan typo JavaScript dan merapikan duplicate closing curly brackets pada file import view.
* **v1.8.6** (7 Jun 2026) - Integrasi 9Router AI Gateway:
  - Refactor parser menggunakan standar REST OpenAI untuk dukungan multi-model dan failover otomatis.
* **v1.8.7** (7 Jun 2026) - Template PDF Kustom & Pembatasan Hak Akses Cetak PKL:
  - **Penerapan Template PDF Kustom**: Menggunakan `template_laporan_harian_pkl.pdf` sebagai background overlay untuk PDF laporan harian PKL (pdf-lib embed page + logo high-res overlay).
  - **Pembatasan Hak Akses Cetak PDF Ketat Berbasis Role**:
    - **Anggota PKL**: Hanya bisa melihat riwayat laporan, **tidak bisa cetak PDF sama sekali**.
    - **Ketua PKL**: Hanya bisa cetak **Rekap Mingguan (7 hari gabungan)** via card "Rekap Mingguan" di student_report.php. Tombol cetak harian per-report dihapus.
    - **Admin/Kepsek**: Hanya bisa cetak **PDF Harian** per laporan via index.php. Tombol "Rekap Mingguan" dihapus dari view Admin.
    - **Guru/Guru Mapel/BK**: Sama seperti Admin — hanya cetak harian.
  - **Backend (pdf_generator.js)**: Fungsi baru `applyTemplateHarianPkl()` untuk overlay template, menggantikan `applyKopSurat` pada laporan harian.
  - **Controller (Pkl.php)**: Endpoint `printPdf` dibatasi hanya non-student (Admin/Guru); `printWeeklyPdf` dibatasi hanya Ketua kelompok + Admin/Kepsek.
  - **View (student_report.php)**: Card Rekap Mingguan hanya tampil untuk `$isKetua`; tombol cetak harian di riwayat dihapus.
  - **View (index.php)**: Tombol "Cetak Harian" hanya untuk admin/kepsek; "Rekap Mingguan" dihapus.
* **v1.8.8** (7 Jun 2026) - Perbaikan Layering Template PDF Harian PKL:
  - **Fix: Template sebagai Background, Data sebagai Foreground**: Rewrite `applyTemplateHarianPkl()` menggunakan `PDFLibDoc.create()` untuk merakit layer PDF baru secara bersih. Template laporan harian digambar terlebih dahulu (layer bawah), diikuti oleh konten data pdfkit (layer atas). Hal ini memastikan teks data laporan anggota dan foto jurnal tidak lagi tertutupi oleh background putih dari template PDF.
  - **Penghapusan Header Programatik Lama**: Menghapus script yang menggambar kop surat, judul, dan info box secara manual via pdfkit pada laporan harian, karena template PDF baru sudah mencakup layout dan desain tersebut secara penuh (startY dimulai dari margin atas 145).
  - **Hapus File Tidak Terpakai**: `data/Kop surat.pdf` dan file lama dihapus. (KBM tidak terdampak dan masih menggunakan format tersendiri).
* **v1.8.9** (7 Jun 2026) - Migrasi Format PDF PKL ke HTML & Puppeteer:
  - **Migrasi PDF Generator**: Mengubah engine PDF dari `pdfkit` (hardcoded layout) menjadi `puppeteer` (HTML based) untuk Laporan Harian dan Rekap Mingguan PKL agar menghasilkan layout presisi, tabel rapi, foto jelas, dan mudah disesuaikan.
  - **Tata Letak Khusus & 1 Lembar Murni**: Memadatkan Kop Surat menjadi lebih proporsional (rata kiri dengan logo, baris tunggal) dan memaksakan layout Laporan Harian muat tepat dalam 1 halaman (`max-height: 265mm`, margin presisi 2-1.5-1.5-1.5 cm) tanpa meluber ke halaman baru.
  - **Efisiensi Tabel**: Menggabungkan daftar identitas `Ketua Kelompok` dan `Anggota Kelompok` menjadi satu list `Kelompok` terpadu dengan penanda `(Ketua)` otomatis di nomor 1.
* **v1.8.10** (7 Jun 2026) - Rollback Performa Kritis ke PDFKit (Super Ringan):
  - **Penghapusan Puppeteer**: Meng-uninstall *dependency* Chromium Headless (`puppeteer`) karena membebani hardware *server* lawas (Intel Atom N455, 2GB RAM).
  - **PDFKit Asli (Native Layout)**: Menulis ulang logika koordinat X dan Y secara murni di JavaScript untuk menggambar *Kop Surat* dan *Daftar Anggota Kelompok* yang baru secara manual tanpa memuat berkas HTML, meningkatkan kecepatan *render* PDF dari ~2000ms menjadi ~121ms (beban nyaris 0%).
