# 📁 STRUKTUR_PROYEK.md — Panduan Folder SIAKANUDA

> **Tujuan file ini:** Agar semua AI agent (Gemini, Claude, GPT, dll) dan developer
> langsung memahami struktur folder proyek tanpa perlu scan ulang.
> **Terakhir diupdate:** 23 Juni 2026 — Pasca perapian besar.

---

## ⚡ TL;DR

Proyek SIAKANUDA ada di **2 lokasi** yang harus dijaga konsisten:

| Lokasi | Path | Fungsi |
|---|---|---|
| **Lokal** (SSD Portabel) | `F:\Antigravity\siakanuda\` | Development & source of truth |
| **Server** (Debian) | `~/siakanuda/` di `[SSH_USER]@[IP_SERVER_TAILSCALE]` | Production (aplikasi berjalan) |

> ⚠️ **SSD Portabel** — Drive letter bisa berubah (E:\, F:\, G:\). Selalu gunakan path relatif.

---

## 🗂️ Workspace Lokal — `F:\Antigravity\`

```
F:\Antigravity\
├── siakanuda/           ← WORKING COPY AKTIF (development di sini)
├── siakanuda-github/    ← Mirror untuk push ke GitHub (repo terpisah, versi lebih lama)
└── _arsip_lama/         ← File lama yang sudah dirapikan (aman dihapus kapan saja)
    ├── patches/         ← 30 file .tar.gz patch lama
    ├── scripts/         ← 5 build script one-time (Python/PHP)
    ├── html_lama/       ← 2 HTML lama (admin_dashboard_old, data_alumni_old)
    └── misc/            ← siakanuda.db (copy), supabase.js, exclude.txt
```

> **Aturan:** Root `F:\Antigravity\` harus tetap bersih — hanya 3 folder di atas.
> Jangan taruh file loose di root.

---

## 🗂️ Server Debian — `~/` (Home smknuda)

```
~/
├── siakanuda/           ← APLIKASI PRODUCTION (service bot.siswa berjalan di sini)
└── arsip_lama/          ← File lama yang sudah dirapikan (aman dihapus kapan saja)
```

> **Aturan:** Home directory `~/` harus tetap bersih — hanya 2 folder di atas.
> Jangan taruh file patch, script, atau temporary di `~/` langsung.

---

## 📂 Peta Folder `siakanuda/` (Berlaku untuk Lokal & Server)

```
siakanuda/
│
├── ───────── SOURCE CODE ─────────
├── execution/                 ← Node.js Backend (Port 7860)
│   ├── server.js              ← Entry point Express & Socket.io + routes
│   ├── bot.js                 ← WhatsApp Bot (Baileys)
│   ├── db.js                  ← Database adapter (SQLite)
│   ├── cron_jobs.js           ← Cron scheduler (backup, notifikasi)
│   ├── ai_processor.js        ← AI text parser
│   ├── photo_sync.js          ← Sync foto
│   ├── update-docs.js         ← Auto-update dokumentasi
│   └── middleware/             ← auth.js, roles.js
│
├── dashboard/                 ← CodeIgniter 4 Web Dashboard (Port 8080)
│   ├── app/
│   │   ├── Controllers/       ← 20 controller
│   │   ├── Models/            ← 20 model
│   │   ├── Views/             ← 21 folder view (Bootstrap responsive)
│   │   ├── Filters/           ← AuthFilter, RoleFilter
│   │   └── Config/            ← Konfigurasi CI4
│   ├── public/                ← Assets web (CSS, JS, gambar, uploads)
│   ├── writable/              ← Cache, logs, session CI4 (auto-generated)
│   ├── vendor/                ← Composer dependencies
│   ├── .env                   ← Config CI4 (JANGAN expose)
│   └── composer.json
│
├── public/                    ← Static landing page & assets umum
│   ├── index.html, app.js, style.css
│   └── logo-smk.png
│
├── web-darurat/               ← Halaman darurat offline (Cloudflare Pages)
│   └── index.html
│
├── ───────── DATA & DATABASE ─────────
├── siakanuda.db               ← SQLite database UTAMA (16 tabel)
├── database/                  ← SQL schema & migration files
├── data/                      ← Template Excel (guru, siswa)
├── backups/                   ← Backup database harian otomatis
├── uploads/                   ← Upload file (foto PKL, dll)
│
├── ───────── KONFIGURASI ─────────
├── .env                       ← Environment variables (JANGAN expose!)
├── .env.example               ← Template .env (aman dibaca)
├── package.json               ← Node.js config (versi, dependencies)
├── package-lock.json          ← Lock file NPM
├── bot.siswa.service          ← Systemd service file (untuk Debian)
├── deploy.sh                  ← Script deployment
├── .gitignore                 ← Git ignore rules
├── .clinerules                ← AI assistant rules (Cline)
├── .cursorrules               ← AI assistant rules (Cursor) — isi identik dengan .clinerules
│
├── ───────── DOKUMENTASI ─────────
├── AI_CONTEXT.md              ← 🔑 Single Source of Truth — BACA PERTAMA
├── AGENTS.md                  ← Aturan wajib untuk AI agent
├── STRUKTUR_PROYEK.md         ← 📁 FILE INI — peta folder & panduan
├── README.md                  ← Readme proyek
├── STATUS_FITUR.md            ← Checklist fitur per versi
├── ROADMAP.md                 ← Rencana pengembangan
├── DEPLOY_LOG.md              ← Log deployment ke server
├── PROMPT_TEMPLATE.md         ← Template prompt untuk AI
│
├── directives/                ← SOP operasional (5 file .md)
├── docs/                      ← Dokumentasi tambahan & arsip
│   ├── DEPLOY_NOTES.md
│   ├── checklist_testing.md
│   └── archive/               ← Blueprint lama, history, patch lama
│
├── ───────── ARSIP & RUNTIME ─────────
├── archive/                   ← Legacy scripts & file lama
│   ├── legacy_scripts/        ← Script JS lama (backup)
│   └── patches/               ← Patch .tar.gz lama (sudah diapply)
│
├── sessions/                  ← WhatsApp session (Baileys) — JANGAN HAPUS
├── node_modules/              ← NPM dependencies — JANGAN COMMIT
│
├── ───────── KHUSUS LOKAL ─────────
├── .git/                      ← Git repository (hanya di lokal)
├── tests/                     ← Playwright E2E test specs
├── scripts/                   ← Utility scripts (health-check.sh)
└── .tmp/                      ← Temporary files
```

---

## 🔄 Alur Deployment: Lokal → Server

```
┌─────────────────┐    tar.gz     ┌──────────────────┐
│  LOKAL (Windows) │ ──────────►  │  SERVER (Debian)  │
│  F:\...\siakanuda│   via SCP    │  ~/siakanuda/     │
│  Development     │              │  Production       │
└─────────────────┘              └──────────────────┘
```

1. **Develop** di lokal (`F:\Antigravity\siakanuda\`)
2. **Buat patch** — `tar czf patch_vXXXX.tar.gz file1 file2 ...`
3. **Upload** ke server — `scp patch.tar.gz [SSH_USER]@[IP_SERVER_TAILSCALE]:~/siakanuda/`
4. **Extract** di server — `cd ~/siakanuda && tar xzf patch.tar.gz`
5. **Restart service** — `sudo systemctl restart bot.siswa`
6. **Pindahkan patch** ke arsip — `mv patch.tar.gz archive/patches/`

> ⚠️ **JANGAN** tinggalkan file `.tar.gz` di root `~/siakanuda/` atau `~/`.
> Selalu pindahkan ke `archive/patches/` setelah extract.

---

## 🚫 File yang TIDAK BOLEH Disentuh

| File/Folder | Alasan |
|---|---|
| `siakanuda.db` | Database produksi — data siswa, guru, dll |
| `.env` (root & dashboard) | Credentials & secrets |
| `sessions/` | WhatsApp session aktif — hapus = harus scan QR ulang |
| `backups/` | Backup database harian |
| `dashboard/public/uploads/` | Upload foto siswa (33 MB di server) |
| `node_modules/` | Dependencies — jangan commit, jangan hapus di server |
| `dashboard/vendor/` | Composer dependencies |

---

## 🧹 Aturan Kebersihan untuk AI Agent

### ✅ LAKUKAN
- Setelah membuat patch `.tar.gz`, **pindahkan ke `archive/patches/`** setelah selesai
- Gunakan `git add <file>` spesifik — **JANGAN `git add .`**
- Bersihkan `dashboard/writable/session/` secara berkala (session CI4 lama menumpuk)
- Bersihkan `dashboard/writable/debugbar/` setelah debugging selesai
- Update `AI_CONTEXT.md` jika ada perubahan arsitektur/versi
- Update `STATUS_FITUR.md` jika ada fitur baru/selesai

### ❌ JANGAN
- Jangan taruh file temporary/patch di root `~/` atau root `siakanuda/`
- Jangan buat folder bersarang `siakanuda/siakanuda/` (pernah terjadi karena path extract salah)
- Jangan commit `*.tar.gz`, `*.db`, `*.log`, `node_modules/`, `sessions/`
- Jangan hapus `tests/` di lokal (masih berguna untuk testing)
- Jangan install Playwright/testing tools di server production

---

## 📋 Riwayat Perapian

### 23 Juni 2026 — Perapian Besar

**Lokal (`F:\Antigravity\`):**
- ✅ 40 file loose di root dipindah ke `_arsip_lama/` (patches, scripts, HTML lama)
- ✅ 280 session files + 20 debugbar + test artifacts dihapus di `siakanuda/`
- ✅ 15 patch archives dipindah ke `siakanuda/archive/patches/`
- ✅ Folder dev/test dihapus: `playwright-report/`, `test-results/`, `scratch/`, `temp_ssh/`
- ✅ File kosong `database.sqlite` dan `playwright.config.js` dihapus

**Server (`[SSH_USER]@[IP_SERVER_TAILSCALE]`):**
- ✅ 46 file di `~/` dipindah ke `~/arsip_lama/`
- ✅ `siakanuda-v1.2.0/` (111 MB, versi lama) dihapus
- ✅ 805 session files + 20 debugbar + log lama dihapus
- ✅ 11 patch archives dipindah ke `archive/patches/`
- ✅ Folder dev dihapus: `playwright-report/`, `test-results/`, `scratch/`, `siakanuda-apk/`
- ⬜ Folder bersarang `siakanuda/siakanuda/` (3 file sisa patch) — menunggu dihapus

**Hasil:** Aplikasi tetap berjalan normal. Tidak ada source code yang diubah.
