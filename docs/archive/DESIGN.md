---
name: SIAKANUDA Premium Design System
version: 1.2.3
author: Google Antigravity & SMK NU Darussalam
colors:
  primary: "#2563eb"
  primary-hover: "#1d4ed8"
  primary-light: "#eff6ff"
  success: "#16a34a"
  warning: "#d97706"
  danger: "#dc2626"
  purple: "#7c3aed"
  background: "#f4f6fb"
  surface: "#ffffff"
  border: "#e2e8f0"
  border-hover: "#cbd5e1"
  text-primary: "#1e293b"
  text-muted: "#64748b"
  text-light: "#94a3b8"
  sidebar-bg: "#1e293b"
  sidebar-active: "#2563eb"
  sidebar-text: "#94a3b8"
  sidebar-hover-text: "#f1f5f9"
typography:
  fontFamily: "Inter, system-ui, sans-serif"
  sizes:
    xs: "0.72rem"
    sm: "0.82rem"
    base: "0.875rem"
    md: "0.95rem"
    lg: "1.1rem"
    xl: "1.4rem"
    xxl: "1.7rem"
  weights:
    light: 300
    normal: 400
    medium: 500
    semibold: 600
    bold: 700
spacing:
  xs: "4px"
  sm: "8px"
  md: "12px"
  lg: "16px"
  xl: "20px"
  xxl: "28px"
  layout-padding: "40px"
shapes:
  border-radius-sm: "6px"
  border-radius-md: "8px"
  border-radius-lg: "12px"
  border-radius-full: "9999px"
shadows:
  sm: "0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04)"
  md: "0 4px 12px rgba(0,0,0,.08)"
  lg: "0 8px 24px rgba(0,0,0,.10)"
transitions:
  default: "0.18s cubic-bezier(0.4, 0, 0.2, 1)"
---

# 🎨 SIAKANUDA — Premium Design System Specification

Spesifikasi sistem desain untuk **SIAKANUDA (Sistem Informasi Akademik SMK NU Darussalam) v1.2.3** untuk memastikan konsistensi visual, keindahan antarmuka, dan kenyamanan pengguna baik melalui browser PC maupun aplikasi WebView di handphone Android.

---

## 1. RINGKASAN DESAIN

SIAKANUDA v1.2.3 memadukan aspek **Educational Credibility** (kepercayaan akademik) dengan **Modern Materiality** (fungsionalitas modern):
* **Profesional & Kredibel**: Didominasi oleh warna biru kobalt, abu-abu slate, dan putih bersih untuk memberikan kesan rapi dan teratur.
* **Interaktif & Hidup**: Menggunakan efek bayangan halus (shadows), transisi lembut, serta micro-animations pada komponen interaktif seperti tombol, kartu, dan menu navigasi.
* **Mobile-First Responsiveness**: Dirancang menggunakan framework Bootstrap yang dikustomisasi secara presisi agar adaptif di layar smartphone (lebar 320px - 480px) untuk mendukung integrasi APK Android WebView.

---

## 2. PALET WARNA SEMANTIK

| Peran | Kode Hex | Deskripsi / Penggunaan |
|---|---|---|
| **Primary** | `#2563eb` | Biru Kobalt. Tombol utama, tautan aktif, aksen brand. |
| **Primary Hover**| `#1d4ed8` | Biru Kobalt Tua. State hover/fokus untuk tombol utama. |
| **Primary Light**| `#eff6ff` | Biru Es. Latar belakang baris terpilih, alert info. |
| **Success** | `#16a34a` | Hijau Emerald. Absensi "Hadir", status bot WA terkoneksi. |
| **Warning** | `#d97706` | Jingga Amber. Absensi "Izin/Sakit", status tertunda (pending). |
| **Danger** | `#dc2626` | Merah Crimson. Absensi "Alpha", tombol hapus, bot WA terputus. |
| **Purple** | `#7c3aed` | Ungu Violet. Aksen catatan Guru BK / Konseling. |
| **Background** | `#f4f6fb` | Abu-abu Lembut. Latar belakang sistem untuk mengurangi kelelahan mata. |
| **Surface** | `#ffffff` | Putih Bersih. Kotak data (cards), form, modal, dan tabel. |
| **Text Primary** | `#1e293b` | Arang Gelap. Warna tulisan utama, judul halaman. |
| **Text Muted** | `#64748b` | Abu-abu Baja. Label form, header tabel, deskripsi sekunder. |

---

## 3. TIPOGRAFI & SKALA

* **Font Utama**: `Inter`, fallback `system-ui, sans-serif` (dimuat via Google Fonts).
* **Ukuran & Berat Tulisan**:
  - `Title (h1)`: `1.4rem` (Bold 700) — Judul utama halaman.
  - `Subtitle (h2)`: `0.95rem` (Semibold 600) — Header kartu data/modal.
  - `Body Base`: `0.875rem` (Normal 400) — Tulisan konten tabel, form input.
  - `Helper (small)`: `0.82rem` (Normal 400) — Keterangan tambahan di bawah input.
  - `Badges`: `0.72rem` (Bold 700) — Status tag ("Hadir", "Izin", "Alpha").

---

## 4. PEDOMAN KOMPONEN RESPONSIVE (MOBILE VIEW)

Karena sistem diakses oleh siswa dan guru langsung dari handphone (melalui WebView APK):

### A. Sidebar Navigasi (Mobile Overlay)
* Pada layar desktop (`>768px`), sidebar tampil permanen di sisi kiri (`width: 240px`).
* Pada layar handphone (`<=768px`), sidebar tersembunyi secara default dan muncul sebagai slide-in drawer dari kiri saat tombol hamburger di-tap.

### B. Kartu Statistik (`.card`)
* Memiliki border-radius `12px` dengan bayangan halus (`Shadow-SM`).
* Pada layar kecil, statistik disusun secara vertikal atau grid 2 kolom (`grid-template-columns: 1fr 1fr`) agar teks tidak terpotong.

### C. Tabel Data (`.table-responsive`)
* Semua tabel wajib dibungkus dalam container yang memiliki properti `overflow-x: auto`.
* Tidak diperbolehkan menyajikan tabel lebar tanpa scroll horizontal pada tampilan mobile.

### D. Form Input
* Ukuran area tap (touch target) untuk tombol dan input form minimal `44px` agar mudah ditekan dengan jari.
* Latar belakang input aktif menggunakan tint `#eff6ff` dengan border biru kobalt.
