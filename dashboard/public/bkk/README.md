# BKK — Website Database Alumni SMK NU Darussalam

Sistem informasi Bursa Kerja Khusus (BKK) dan *Tracer Study* terintegrasi untuk mendata alumni secara dinamis, melacak keterserapan di industri, serta menyelaraskan lulusan SMK NU Darussalam dengan kebutuhan dunia kerja.

Aplikasi ini telah dimigrasikan sepenuhnya dari **Google Sheets (GAS)** ke **Supabase PostgreSQL Cloud Database** demi peningkatan performa, kecepatan, dan keamanan data skala industri.

---

## 🌟 Fitur Utama

- **Pencatatan Tracer Study:** Pendataan alumni per angkatan (2017–2027) dan kompetensi keahlian (TKJ, AKL, TKR).
- **Integrasi Cloud Database Modern:** Sinkronisasi data dua arah secara real-time dengan **Supabase Cloud Database**.
- **Local SDK & Fallback Offline:** Menggunakan SDK Supabase yang dipaketkan secara lokal (`js/supabase.js`) untuk kemandirian penuh tanpa tergantung CDN eksternal, dilengkapi fitur penyimpanan cadangan offline otomatis (`localStorage`) jika koneksi internet terputus.
- **Fitur Impor & Ekspor Excel:** Memungkinkan pengunggahan data massal dari file Excel (`.xlsx`, `.xls`, `.csv`) langsung ke tabel, serta ekspor data tabel kembali ke format Excel.
- **Sistem Login Admin Terproteksi:** Autentikasi asinkron terenkripsi dengan fallback akun lokal jika terjadi gangguan jaringan.
- **Antarmuka (UI) Premium & Responsif:** Desain modern berbasis CSS kustom (*sage green theme*), dilengkapi efek transisi, *sticky header*, dan fitur *freeze columns* pada tabel data yang panjang.

---

## 📊 Perbandingan Performa: Google Sheets (GAS) vs Supabase

Berikut adalah perbandingan mendalam mengapa sistem basis data BKK dimigrasikan ke Supabase:

| Kriteria | Google Sheets + Google Apps Script (GAS) | Supabase Cloud Database (PostgreSQL) |
| :--- | :--- | :--- |
| **Kecepatan Sinkronisasi** | **Lambat (4 - 8 detik)**. Setiap transaksi kirim/baca membutuhkan proses startup script Google. | **Sangat Cepat (< 0.5 detik)**. Koneksi langsung ke engine database PostgreSQL secara real-time. |
| **Kapasitas Penyimpanan** | Terbatas (maksimal 10 juta sel) dan kecepatan menurun seiring pertambahan baris spreadsheet. | Skala industri (ratusan ribu hingga jutaan baris data tanpa penurunan performa). |
| **Keandalan Koneksi** | Sering terkena batasan kuota harian (*Daily Quota Limits*) dan error *API Timeout*. | Stabil & Aktif 24/7 didukung jaringan global CDN Cloudflare & AWS. |
| **Keamanan Data** | Rendah (kredensial API terbatas, spreadsheet rawan terhapus/diacak oleh pengguna tidak sah). | **Tinggi** (Row Level Security / RLS, otorisasi token JWT, dan enkripsi SSL). |
| **Integritas Relasional** | Lemah (data rawan rusak jika struktur baris atau kolom berubah posisi di sheet). | **Sangat Kuat** (Skema relasional PostgreSQL yang ketat menjaga relasi Primary/Foreign Key tetap utuh). |
| **Akses Konkuren (Multi-user)** | Rawan konflik/tabrakan jika ada lebih dari satu admin yang mengedit secara bersamaan. | Mendukung ratusan transaksi baca/tulis konkuren sekaligus secara aman. |

---

## 🛠️ Teknologi yang Digunakan

- **Frontend:** HTML5, Vanilla JavaScript, CSS3 (Custom Styling)
- **Database & Backend:** Supabase Cloud Database (PostgreSQL Engine)
- **Pustaka Pihak Ketiga:**
  - [Supabase JS SDK](js/supabase.js) — Disediakan secara lokal untuk stabilitas koneksi offline.
  - [SheetJS (XLSX)](https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js) — Untuk impor/ekspor berkas Excel.
  - [Font Awesome](https://cdnjs.cloudflare.com/) — Ikonografi.
  - Google Fonts (Plus Jakarta Sans, Cormorant Garamond) — Tipografi premium.

---

## 📁 Struktur Berkas Proyek

```text
bkk/
├── css/
│   └── styles.css             # Tema warna, layouting dashboard, dan tabel responsif
├── js/
│   ├── supabase.js            # Supabase JS SDK (Versi Lokal / Offline-ready)
│   └── main.js                # Inisialisasi DB, sidebar toggle, dan Auth Guard
├── index.html                 # Portal masuk Admin (Autentikasi Supabase)
├── admin_dashboard.html       # Dasbor statistik visual & ringkasan rekapitulasi
├── data_alumni.html           # Manajemen data Tracer Study Alumni
├── mitra_industri.html        # Agregasi instansi mitra sekolah secara otomatis
├── mou_iduka.html             # Manajemen dokumen kerja sama (MOU) dengan IDUKA
├── kunjungan_industri.html    # Rekam jejak log kunjungan industri siswa
├── pengaturan.html            # Konfigurasi sistem, akun admin, & Zona Berbahaya
├── banner.png                 # Gambar spanduk selamat datang
├── logo-bkk.png               # Logo Bursa Kerja Khusus
└── logo-smk.png               # Logo SMK NU Darussalam
```

---

## 🚀 Panduan Pemasangan & Hosting (GitHub Pages)

Karena proyek ini merupakan aplikasi **web statis**, Anda dapat meng-host-nya secara gratis di GitHub Pages dengan langkah berikut:

1. Buat repositori baru di GitHub dengan nama `bkk` dan atur visibilitasnya menjadi **Public**.
2. Hubungkan folder lokal Anda ini ke repositori tersebut dan lakukan *Push* semua file.
3. Di halaman repositori GitHub Anda, masuk ke **Settings** -> **Pages**.
4. Pada bagian *Branch*, pilih **`main`** (atau `master`) dan klik **Save**.
5. Situs web Anda akan aktif di alamat: `https://<username-github-anda>.github.io/bkk/`

---
*© 2026 TIM TKJ SMK NU Darussalam*
