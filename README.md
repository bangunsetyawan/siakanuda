# 🏫 SIAKANUDA — Sistem Informasi Akademik SMK NU Darussalam

> **v1.21.0** · Production-Ready · Self-Hosted · WhatsApp Bot Integrated

<div align="center">

**SIAKANUDA** adalah sistem informasi akademik sekolah berbasis **Web + WhatsApp Bot + PWA (Progressive Web App)**.
Dibangun khusus untuk SMK NU Darussalam dengan arsitektur **offline-first** dan **self-hosted** di server lokal sekolah.

[📱 Live Demo](https://siakanuda.qzz.io) · [📋 Roadmap](ROADMAP.md) · [📖 Dokumentasi](docs/) · [📊 Status Fitur](STATUS_FITUR.md)

</div>

---

## ✨ Fitur Utama

### 📚 Absensi KBM (Kegiatan Belajar Mengajar)
- Input absensi per kelas oleh ketua kelas
- Broadcast otomatis ke WhatsApp guru & wali kelas
- Rekap bulanan dalam format PDF
- Analitik kehadiran dengan grafik tren 30 hari

### 🏭 Modul PKL (Praktik Kerja Lapangan)
- Manajemen kelompok PKL dengan guru pembimbing
- Jurnal harian siswa (teks + foto dokumentasi)
- Absensi PKL mandiri oleh ketua kelompok
- Laporan harian & mingguan (PDF otomatis)
- Rekap 5 bulan per siswa & per kelompok
- Riwayat lengkap dengan foto dokumentasi
- **PKL Takeover (Laporan Susulan)**: Hak akses khusus Admin/Guru Pembimbing untuk mengisikan absensi mundur (bypass batas 7 hari) bagi siswa yang tertinggal.

### 🤖 WhatsApp Bot
> [!CAUTION]
> **ATURAN KETAT UJI COBA BOT WA (ANTI-BANNED):**
> Jangan pernah menggunakan **1 nomor WA yang sama** untuk semua peran (Orang Tua, Pembimbing, Instruktur, dll) saat melakukan uji coba pengiriman broadcast masif. WhatsApp akan mendeteksi pengiriman pesan template bertubi-tubi ke nomor yang sama sebagai **SPAM**, yang berakibat pada pembatasan akun sementara hingga **pemblokiran permanen**. Selalu gunakan nomor tujuan yang berbeda-beda saat testing.

- Notifikasi otomatis absensi ke guru & wali kelas
- Broadcast ke grup sekolah & grup PKL
- Dashboard pengaturan WA (scan QR, kelola grup, template pesan)
- Log audit semua pesan terkirim
- Cron job terjadwal dari dashboard

### 📊 Dashboard Analytics
- Statistik kehadiran real-time
- Grafik tren Chart.js (30 hari)
- Overview per kelas & per siswa

### 👥 Multi-Role (7 Role)
| Role | Akses |
|------|-------|
| `admin` | Full access + pengaturan WA |
| `kepsek` | Dashboard + laporan |
| `guru` | KBM + PKL (kelompok bimbingan) |
| `guru_bk` | Konseling + pelanggaran + analytics |
| `siswa` | Profil + riwayat absensi |
| `ketua_pkl` | Absensi & jurnal PKL kelompok |
| `anggotapkl` | Jurnal & riwayat pribadi |

### 📱 Akses Multi-Platform
| Platform | URL | Keterangan |
|----------|-----|------------|
| Web Browser | `https://siakanuda.qzz.io` | Semua fitur |
| LAN Sekolah | `http://server-ip:8080` | Akses lokal tanpa internet |
| PWA (Mobile) | Install via Chrome / Browser | Add to Home Screen di HP siswa |

---

## 🏗️ Arsitektur

```
┌─────────────────────────────────────────────────┐
│              AKSES USER                          │
│  Browser / PWA → Cloudflare Tunnel → Nginx:80  │
│  LAN Sekolah  → langsung ke PHP Spark:8080     │
└────────────────────┬────────────────────────────┘
                     │
         ┌───────────┴───────────┐
         ▼                       ▼
┌─────────────────┐    ┌─────────────────┐
│  Dashboard CI4  │    │  Node.js API    │
│  Port 8080      │    │  Port 7860      │
│  ─────────────  │    │  ─────────────  │
│  PHP 8.4        │◄──►│  Express.js     │
│  CodeIgniter 4  │    │  Socket.io      │
│  18 Controller  │    │  Baileys WA     │
│  33 View        │    │  Puppeteer PDF  │
│  12+ Model      │    │  Cron Jobs      │
└────────┬────────┘    └────────┬────────┘
         │                      │
         └──────────┬───────────┘
                    ▼
         ┌─────────────────┐
         │  SQLite (lokal)  │
         │  siakanuda.db    │
         └────────┬────────┘
                  │ sync foto
                  ▼
         ┌─────────────────┐
         │  Supabase Cloud  │
         │  (foto PKL only) │
         └─────────────────┘
```

### Tech Stack
| Layer | Teknologi |
|-------|-----------|
| **Frontend** | Bootstrap 5 + Chart.js + DataTables + Socket.io Client |
| **Backend Web** | CodeIgniter 4 (PHP 8.4) |
| **Backend API** | Express.js (Node.js 22) |
| **Database** | SQLite 3 (better-sqlite3) |
| **WA Bot** | Baileys (WhatsApp Web API) |
| **PDF** | Puppeteer (Chromium headless) |
| **Cloud Storage** | Supabase Storage (foto PKL) |
| **Deployment** | Nginx + Cloudflare Tunnel + systemd |
| **Testing** | Playwright (E2E) |

---

## 🚀 Quick Start

### Prerequisites
- **Node.js** ≥ 18 (recommended: v22)
- **PHP** ≥ 8.1 dengan ekstensi `sqlite3`, `intl`, `mbstring`
- **Composer** (untuk dependensi PHP)

### Instalasi

```bash
# 1. Clone repository
git clone https://github.com/bangunsetyawan/siakanuda.git
cd siakanuda

# 2. Install dependensi Node.js
npm install

# 3. Install dependensi PHP (CodeIgniter 4)
cd dashboard && composer install && cd ..

# 4. Setup environment
cp .env.example .env
# Edit .env sesuai kebutuhan (lihat komentar di .env.example)

# 5. Jalankan (2 terminal)
# Terminal 1: Node.js background service
npm run dev

# Terminal 2: PHP dashboard
cd dashboard && php spark serve --host 0.0.0.0 --port 8080
```

Buka `http://localhost:8080` di browser.

### Login Default
| Username | Password | Role |
|----------|----------|------|
| `admin` | *(set di .env)* | Admin |

---

## 📁 Struktur Proyek

```
siakanuda/
├── execution/          # Backend Node.js (WA Bot, API, PDF, Cron)
│   ├── server.js       # Express entry point
│   ├── bot.js          # WhatsApp Bot (Baileys)
│   ├── db.js           # SQLite schema & queries
│   ├── cron_jobs.js    # Scheduled tasks
│   └── middleware/     # Auth & role middleware
├── dashboard/          # Frontend CodeIgniter 4
│   ├── app/
│   │   ├── Controllers/  # 18 controller
│   │   ├── Models/       # 12+ model
│   │   ├── Views/        # 33 view files
│   │   └── Filters/      # Auth & role filters
│   └── public/           # Static assets
├── scripts/            # Utility scripts (health-check, etc.)
├── docs/               # Dokumentasi teknis
├── data/               # Template statis (Excel, jadwal)
├── database/           # Skrip migrasi
├── tests/              # Playwright E2E tests
└── public/             # PWA assets
```

---

## 🖥️ Deployment (Production)

SIAKANUDA dirancang untuk berjalan di **server lokal sekolah** (self-hosted).
Panduan lengkap: [docs/DEPLOY_NOTES.md](docs/DEPLOY_NOTES.md)

### 🌐 Akses Production

| Jalur | URL | Keterangan |
|-------|-----|------------|
| **Internet (Publik)** | [https://siakanuda.qzz.io](https://siakanuda.qzz.io) | Via Cloudflare Tunnel |
| **Shortlink** | [https://s.id/siakanuda](https://s.id/siakanuda) | Redirect ke URL di atas |
| **LAN Sekolah** | `http://[IP_SERVER_LAN]:8080` | Akses langsung tanpa internet |
| **SSH (Tailscale VPN)** | `ssh [SSH_USER]@[IP_SERVER_TAILSCALE]` | Administrasi server |

### 🔒 Cloudflare Tunnel

Server sekolah tidak memiliki IP publik. Akses internet menggunakan **Cloudflare Tunnel** yang membuat koneksi aman dari server ke Cloudflare edge tanpa perlu port forwarding.

```
Browser (Internet)
    ↓ HTTPS
Cloudflare Edge (siakanuda.qzz.io)
    ↓ Encrypted Tunnel
cloudflared daemon (server sekolah)
    ↓ localhost:80
Nginx (reverse proxy)
    ├── / → PHP-FPM (CodeIgniter 4 Dashboard)
    ├── /socket.io/ → Node.js:7860 (WebSocket)
    └── /api/ → Node.js:7860 (REST API)
```

**Domain**: `siakanuda.qzz.io` (free subdomain via [DigitalPlat](https://digitalplat.org))

### 🛡️ Tailscale VPN

Untuk administrasi remote (SSH, monitoring), server terhubung ke jaringan **Tailscale** mesh VPN. Ini memungkinkan akses SSH dari mana saja tanpa expose port 22 ke internet.

### Systemd Services (Auto-Start on Boot)

| Service | Fungsi | Port |
|---------|--------|------|
| `bot.siswa.service` | Node.js WA Bot + Express API | 7860 |
| `siakadash.service` | PHP spark serve (akses LAN) | 8080 |
| `nginx` | Reverse proxy (akses internet) | 80 |
| `php8.4-fpm` | PHP processor untuk Nginx | socket |
| `cloudflared` | Cloudflare Tunnel daemon | — |

Semua service `enabled` (auto-start) + health-check script via cron `@reboot`.

### Server Specs (Production)

| Komponen | Detail |
|----------|--------|
| **Hardware** | Laptop repurposed (CPU Atom N455, 2GB RAM) |
| **OS** | Debian 13 (Trixie) |
| **Storage** | 115 GB SSD |
| **Node.js** | v22.x |
| **PHP** | 8.4.x |
| **Koneksi** | WiFi sekolah → Internet |

---

## 🧪 Testing

```bash
# Jalankan E2E tests (Playwright)
npx playwright test

# Jalankan test spesifik
npx playwright test tests/login.spec.js
npx playwright test tests/kbm.spec.js
npx playwright test tests/pkl.spec.js
```

---

## 📋 Roadmap

Lihat [ROADMAP.md](ROADMAP.md) untuk rencana pengembangan lengkap.

### Rencana v2.0.0+
- [ ] Laporan bulanan 1 sekolah (PDF otomatis)
- [ ] Rate limiting & Fail2ban
- [ ] Backup database otomatis ke cloud
- [ ] Notifikasi email

---

## 📄 Dokumentasi

| Dokumen | Deskripsi |
|---------|-----------|
| [AI_CONTEXT.md](AI_CONTEXT.md) | Konteks proyek untuk AI agent |
| [STATUS_FITUR.md](STATUS_FITUR.md) | Status fitur & changelog |
| [ROADMAP.md](ROADMAP.md) | Rencana pengembangan |
| [docs/DEPLOY_NOTES.md](docs/DEPLOY_NOTES.md) | SOP deploy & troubleshooting |
| [.env.example](.env.example) | Template konfigurasi |

---

## 🤝 Kontribusi

Proyek ini dikembangkan secara internal untuk SMK NU Darussalam.
Untuk pertanyaan atau kontribusi, hubungi pengembang.

---

## 📜 Lisensi

Hak cipta © 2026 SMK NU Darussalam. All rights reserved.

---

<div align="center">

**Dibuat dengan ❤️ untuk SMK NU Darussalam**

*Dikembangkan oleh Bangun Setyawan dengan bantuan AI Agent*

</div>
## ?? PERINGATAN KRITIKAL: DEPLOYMENT SERVER PRODUCTION

> [!CAUTION]
> **DILARANG KERAS** menggunakan php spark serve (atau php -S) di environment production (server asli)!
> Server bawaan PHP ini bersifat Single-Threaded. Jika diakses lebih dari 1 orang bersamaan, server akan mati, CPU akan mencapai 100%, dan database SQLite akan terkunci (Crash database is locked).

> [!IMPORTANT]
> **SYARAT WAJIB SERVER PRODUCTION:**
> 1. **Gunakan Nginx + PHP-FPM.** Nginx memiliki kemampuan Multi-Threading.
> 2. **Tuning PHP-FPM:** Sesuaikan `pm.max_children` dengan kapasitas RAM server. Untuk server dengan spesifikasi 2GB RAM (seperti spesifikasi production saat ini), gunakan maksimal **12 hingga 15**. **DILARANG KERAS** menggunakan angka 50 karena akan memicu Out of Memory (OOM) dan server hang/stuck. Sedikit antrean di Nginx jauh lebih baik daripada server crash.
> 3. **Mode WAL SQLite:** Wajib aktifkan Write-Ahead Logging (WAL) pada siakanuda.db agar puluhan siswa bisa menyimpan data tanpa saling *lock*. 
> 4. **Jangan Hardcode Pragma:** Bot Node.js (execution/db.js) sudah dimodifikasi agar membungkus db.pragma dengan try-catch dan diberi _timeout_ 15 detik. Jangan kembalikan kodenya seperti semula, atau bot akan gagal _booting_ dengan error SQLITE_BUSY.
