# Laporan Rilis & Deployment Terkini

**Nama Sistem:** SIAKANUDA — Sistem Informasi Akademik SMK NU Darussalam
**Versi Deployment:** `v1.10.4`
**Lingkungan:** Produksi (Debian Server)
**Tanggal Deployment:** 14 Juni 2026

---

## 🎯 Pencapaian Utama (Milestones)

Deployment kali ini berfokus pada transisi ke *Local SQLite Database* yang stabil dan optimalisasi kinerja *WhatsApp Bot Background Service* pada lingkungan virtualisasi (Proxmox/Debian).

> [!TIP]
> Performa server terbukti sangat efisien dengan tingkat penggunaan RAM hanya ~361MB untuk seluruh ekosistem (Web Dashboard + Bot API + Database).

## 🛠️ Ringkasan Perubahan & Perbaikan (*Changelog*)

### 1. Sinkronisasi Data Produksi
- **Pemulihan Database Asli:** Mengunggah dan mengekstrak `siakanuda.db` dari Windows lokal (berisi 150 data siswa riil beserta kelompok PKL) ke dalam server Debian.
- **Isolasi Database:** Mengeluarkan database `.db` dari sistem pelacakan Git (`git rm --cached`) untuk menghindari kebocoran data dan pembengkakan repositori.

### 2. Penambalan *Crash* Kritis (*Hotfixes*)
- **Penghapusan Modul `sharp`:** Menyelesaikan *error* `Illegal instruction` yang diakibatkan oleh inkompatibilitas arsitektur CPU virtual dengan instruksi AVX modern dari dependensi *sharp*.
- **Pembersihan Modul Zombie:** Membersihkan sisa *import* pemanggilan `sharp` secara manual di dalam file `execution/photo_sync.js` agar bot dapat melakukan *booting* dengan sempurna.
- **Penyesuaian Variabel Bot:** Memperbaiki *error* `ReferenceError: VERSION is not defined` yang mengotori *log* sistem setiap kali dashboard meminta *health check*.

### 3. Optimalisasi Fitur WhatsApp Bot (Baileys)
- **CORS WebSocket:** Mengubah batasan `cors: { origin: '*' }` pada server Socket.io untuk melancarkan jembatan komunikasi antara Bot Server (port 7860) dengan Web Dashboard CI4 (port 8080) yang diakses via Tailscale.
- **Sistem QR Code Terminal:** Mengembalikan fungsionalitas pemindaian QR Code di layar hitam terminal menggunakan *library* `qrcode` secara manual, karena kebijakan depresiasi (*deprecation*) `printQRInTerminal` dari engine Baileys versi terbaru.

### 4. Reorganisasi Repositori (Git Clean-up)
- **Arsip Script Usang:** Mengarsipkan *script* lama (*legacy*) ke dalam folder `/archive/legacy_scripts/` menggunakan fitur `git mv` agar riwayat *commit* tidak terputus.
- **Sistem Anti-Lupa (Docs Sync):** Menjalankan *script* pembaruan dokumentasi otomatis (`update-docs.js`) sehingga file `STATUS_FITUR.md` tersinkronisasi 100% dengan kondisi kode terbaru.
- **Pembersihan *Temp Files*:** Menambahkan aturan pengabaian (*ignore*) untuk file sementara MS Office (`~$*` dan `~*`) di dalam `.gitignore`.

---

## 🚦 Status Sistem Saat Ini

| Komponen | Status | Port / Keterangan |
| :--- | :---: | :--- |
| **Web Dashboard (CodeIgniter 4)** | 🟢 Aktif | `8080` (Tailscale IP) |
| **WA Bot & Background API (Node.js)** | 🟢 Aktif | `7860` (Background Service) |
| **Database System** | 🟢 Aktif | *Local SQLite Database* |
| **Konektivitas WhatsApp** | 🟢 Tertaut | Nomor Utama Sistem |
| **Git Working Tree** | ✨ Bersih | *Nothing to commit* |

> [!IMPORTANT]
> Sistem sudah berstatus *Production-Ready*. Fitur Absensi KBM dan Pelaporan PKL harian dapat segera digunakan oleh seluruh staf dan guru di lapangan.

---
*Dihasilkan secara otomatis oleh Tim AI Antigravity.*
