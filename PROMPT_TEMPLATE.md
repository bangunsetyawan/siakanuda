# 📋 Template Prompt Universal — SIAKANUDA v1.21.0
> Copy-paste prompt di bawah ke AI manapun (Gemini, Claude, GPT, Copilot, Cursor, Windsurf, Antigravity, dll).
> Template ini **tidak terikat versi, model, atau komputer** — AI akan membaca versi aktif dari file proyek.
> ⚠️ Tidak perlu tulis path absolut — SSD portabel, drive letter bisa berubah (E:\, F:\, G:\).
> 📅 Terakhir diperbarui: 30 Juni 2026.

---

## ⚡ QUICK REFERENCE — Aturan Kritikal

> **Salin bagian ini ke dalam prompt handoff agar AI tidak mengulangi kesalahan yang sama.**

### 🔴 JANGAN PERNAH (Hard Rules)
1. **JANGAN** sarankan `php spark serve` untuk production — server production pakai **Nginx + PHP 8.4 FPM**
2. **JANGAN** gunakan path absolut (drive letter SSD portabel bisa berubah)
3. **JANGAN** jalankan `npm install` / `composer install` tanpa konfirmasi user
4. **JANGAN** commit file `.env` ke Git
5. **JANGAN** buat folder versi baru (`siakanuda-v2/`, dll) — semua kode di folder yang sama
6. **JANGAN** include folder `dashboard/writable/` dalam arsip tar.gz (ownership `www-data`, akan error permission)
7. **JANGAN** edit file langsung di server — semua perubahan dari lokal → patch → upload

### ✅ WAJIB (Setiap Sesi)
1. **BACA** `AGENTS.md`, `docs/AI_CONTEXT.md`, `docs/STATUS_FITUR.md` sebelum mulai kerja
2. **IDENTIFIKASI** versi aktif dari `docs/STATUS_FITUR.md` (header) — sebutkan ke user
3. **GIT COMMIT** setelah selesai — post-commit hook otomatis update dokumentasi
4. **UPDATE VERSI** di `server.js` (line 3 & `/api/status`), `package.json`, `.env` header jika naik versi

### 🏗️ Arsitektur Production
```
Internet → Cloudflare → Nginx (port 8080) → PHP 8.4 FPM → CI4 Dashboard
                                            ↕
                         Node.js Express (port 7860) → WA Bot (Baileys)
                                            ↕
                              SQLite siakanuda.db (mode WAL)
```
- **Domain**: `https://siakanuda.qzz.io`
- **SSH**: `[SSH_USER]@[IP_SERVER_LAN]` (LAN) atau `[SSH_USER]@[IP_SERVER_TAILSCALE]` (Tailscale)
- **Service aktif**: `nginx`, `php8.4-fpm`, `bot.siswa` (Node.js)
- **Service MATI**: `siakadash` (sudah disabled, digantikan Nginx+FPM)

---

## 📝 Checklist File yang Perlu Diupdate Ketika Commit

### 🤖 Otomatis (Git Post-Commit Hook)

File-file ini **tidak perlu diupdate manual**. Script `execution/update-docs.js` otomatis berjalan setiap `git commit` via Git Hook dan meng-amend hasilnya ke commit.

| # | File | Yang Diupdate Otomatis |
|---|------|------------------------|
| 1 | `docs/STATUS_FITUR.md` | Header versi, tanggal, total commit, statistik commit per tipe, changelog 25 commit terakhir, footer timestamp |
| 2 | `docs/AI_CONTEXT.md` | Header versi & tanggal, jumlah controller, jumlah file execution, referensi versi `package.json` |

### ✋ Manual (Wajib Diupdate Developer/AI Sebelum Commit)

| # | File | Kapan Perlu Diupdate | Apa yang Diupdate |
|---|------|----------------------|-------------------|
| 1 | `docs/STATUS_FITUR.md` | Setiap ada fitur baru / selesai | Centang (`[x]`) checklist fitur di bagian "Modul & Fitur" |
| 2 | `docs/AI_CONTEXT.md` | Setiap ada perubahan arsitektur | Tambah entry di "Log Perubahan Terbaru", update skema DB jika tabel baru |
| 3 | `package.json` | Saat naik versi | Update field `"version"` |
| 4 | `execution/server.js` | Saat naik versi | Update komentar header (line 3) DAN konstanta `version` di `/api/status` |
| 5 | `.env` | Saat ada variabel baru | Update `.env.example` juga (JANGAN commit `.env`!) |

### ❌ Tidak Perlu Diupdate (Kecuali Kasus Khusus)

| # | File | Catatan |
|---|------|---------| 
| 1 | `AGENTS.md` | Aturan universal — hanya diupdate jika SOP pengembangan berubah |
| 2 | `PROMPT_TEMPLATE.md` | Template prompt ini — hanya diupdate jika alur kerja berubah |
| 3 | `docs/archive/*` | Arsip blueprint lama — hanya untuk referensi historis |

### 🔀 Diagram Alur Commit

```
Developer/AI selesai ubah kode
        │
        ▼
┌─────────────────────────────────┐
│ 1. Apakah ada fitur baru?       │──Yes──► Update checklist docs/STATUS_FITUR.md
│ 2. Apakah arsitektur berubah?   │──Yes──► Update docs/AI_CONTEXT.md (Log Perubahan)
│ 3. Apakah versi naik?           │──Yes──► Update package.json + server.js (2 tempat!)
│ 4. Apakah ada env baru?         │──Yes──► Update .env.example
└─────────────────────────────────┘
        │
        ▼
  git add [file yang diubah]
  git commit -m "feat(vX.Y.Z): ..."
        │
        ▼
┌─────────────────────────────────┐
│ 🤖 Git Hook Otomatis:           │
│ • update-docs.js berjalan       │
│ • STATUS_FITUR.md stats updated │
│ • AI_CONTEXT.md header updated  │
│ • Auto-amend ke commit          │
└─────────────────────────────────┘
        │
        ▼
  ✅ Commit selesai & terdokumentasi
```

---

## 🟢 PROMPT HANDOFF — Sesi Lanjutan (Pindah AI / Sesi Baru)

> **Ini prompt paling penting.** Gunakan setiap kali memulai sesi baru di AI manapun.

```
# [PROMPT HANDOFF] Sesi Lanjutan SIAKANUDA

Halo! Lanjutkan pekerjaan sebagai Asisten AI untuk sistem SIAKANUDA
(Sistem Informasi Akademik SMK NU Darussalam) milik saya.

## Aturan Wajib Sebelum Mulai
Baca file berikut secara BERURUTAN:
1. `AGENTS.md`            ← Aturan wajib AI agent
2. `docs/AI_CONTEXT.md`   ← Single Source of Truth (arsitektur, folder map, changelog)
3. `docs/STATUS_FITUR.md` ← Checklist fitur & status terkini

Setelah membaca, sebutkan versi aktif dari header STATUS_FITUR.md.

## Arsitektur Production (WAJIB DIINGAT)
- Web Server: **Nginx (port 8080) + PHP 8.4 FPM** (pm.max_children=50)
  ⛔ JANGAN sarankan `php spark serve` untuk production!
- Database: SQLite `siakanuda.db` mode **WAL** (Write-Ahead Logging)
- Bot WA: Node.js Express (port 7860) + Baileys
- Domain: `https://siakanuda.qzz.io` via Cloudflare Tunnel
- Akses Server (Debian):
  - SSH: `[SSH_USER]@[IP_SERVER_LAN]` (LAN) / `[SSH_USER]@[IP_SERVER_TAILSCALE]` (Tailscale)
  - Lokasi Direktori Kerja: `~/siakanuda/` (Atau `/home/smknuda/siakanuda/`)
    ⛔ *PERHATIAN: BUKAN di `/var/www/siakanuda/`! JANGAN gunakan path `/var/www/`.*
  - Service Utama Aktif:
    1. `bot.siswa` (Menjalankan Node.js WA Bot di port 7860)
    2. `php8.4-fpm` (Menjalankan eksekusi PHP CodeIgniter)
    3. `nginx` (Reverse proxy)

## Aturan Deployment
- Semua perubahan di lokal → git commit → buat patch .tar.gz → SCP → extract di server
- Exclude dari tar.gz: `node_modules`, `sessions`, `.env`, `*.db`, `dashboard/writable`, `*.tar.gz`, `.git`
- Ubah file PHP → restart `php8.4-fpm` (atau cukup clear opcache)
- Ubah file JS (execution/) → restart `bot.siswa`
- JANGAN restart `siakadash` — service ini sudah DISABLED (digantikan Nginx)

## Instruksi Sesi Ini
Tugas saya hari ini adalah:
[TULIS TUGAS DI SINI]
```

---

## 🟡 PROMPT SINGKAT — Fix Kecil / Tweak Cepat

```
Lanjutkan SIAKANUDA. Baca AGENTS.md dan docs/AI_CONTEXT.md dulu.

Aturan:
- Edit di lokal, jangan edit di server.
- Production pakai Nginx+FPM, BUKAN php spark serve.
- Git commit setelah selesai.
- Siapkan patch .tar.gz jika perlu deploy (exclude: node_modules, sessions, .env, *.db, dashboard/writable).

Tugas: [TULIS TUGAS DI SINI]
```

---

## 🔵 PROMPT REVIEW — Tanpa Edit Kode

```
Lanjutkan SIAKANUDA. Baca docs/AI_CONTEXT.md dan docs/STATUS_FITUR.md.
Jangan edit kode. Saya butuh:
1. Rangkuman status proyek (versi aktif, fitur terbaru, git log terakhir)
2. Cek konsistensi versi di: package.json, server.js, docs/STATUS_FITUR.md
3. Saran pengembangan atau perbaikan selanjutnya
```

---

## 🔴 PROMPT DEPLOYMENT — Patch ke Server

```
Lanjutkan SIAKANUDA. Baca AGENTS.md dan docs/AI_CONTEXT.md.

Tugas: Deploy perubahan terbaru ke server produksi Debian.

Langkah wajib:
1. Pastikan semua perubahan sudah di-commit (git status clean)
2. Buat patch .tar.gz HANYA berisi file yang berubah
   EXCLUDE: node_modules, sessions, .env, *.db, *.db-wal, *.db-shm,
            dashboard/writable, *.tar.gz, .git, .tmp, uploads, backups
3. Beri saya command SCP + SSH untuk:
   - Upload: scp [file] [SSH_USER]@[IP_SERVER_LAN]:~/
   - Extract: tar -xzf ~/[file] -C ~/
   - Restart service yang relevan (BUKAN siakadash — itu sudah DISABLED)

Aturan restart:
- Ubah file PHP (dashboard/) → sudo systemctl restart php8.4-fpm
- Ubah file JS (execution/) → sudo systemctl restart bot.siswa
- Ubah file statis (public/) → tidak perlu restart
- JANGAN npm install kecuali ada dependency baru di package.json
```

---

## 🟣 PROMPT CEK SERVER — Copy-Paste ke SSH

```
Lanjutkan SIAKANUDA. Baca docs/AI_CONTEXT.md.
Saya sedang SSH ke server Debian ([SSH_USER]@[IP_SERVER_LAN] atau [SSH_USER]@[IP_SERVER_TAILSCALE]).

Bantu saya mengecek kondisi server:
1. Status service: nginx, php8.4-fpm, bot.siswa (siakadash sudah DISABLED)
2. Log error terakhir bot WA (journalctl -u bot.siswa -n 30)
3. Status koneksi WA (Connected / QR Code / Offline)
4. Database integrity (WAL mode aktif, integrity_check)
5. Disk usage
6. Domain accessible (curl https://siakanuda.qzz.io)

Berikan command yang bisa saya copy-paste langsung ke SSH.
Saya tidak hafal perintah CLI — beri penjelasan sederhana untuk setiap output.

⚠️ Jika bot WA offline (looping "Disconnected Reason: 408"):
   sudo systemctl stop bot.siswa
   rm -rf ~/siakanuda/sessions
   sudo systemctl start bot.siswa
   → Lalu scan QR ulang di https://siakanuda.qzz.io/whatsapp-settings
```

---

## 🟤 PROMPT DARURAT — Bot WA Offline

```
Lanjutkan SIAKANUDA. Bot WhatsApp saya OFFLINE.

Info server:
- SSH: [SSH_USER]@[IP_SERVER_LAN] (LAN) / [SSH_USER]@[IP_SERVER_TAILSCALE] (Tailscale)
- Service: bot.siswa
- WA Settings: https://siakanuda.qzz.io/whatsapp-settings

Berikan langkah troubleshooting berurutan:
1. Cek log: sudo journalctl -u bot.siswa -n 50 --no-pager
2. Cek internet: curl -I https://web.whatsapp.com
3. Jika looping 408 → reset session (rm -rf ~/siakanuda/sessions)
4. Jika error SQLITE_BUSY → cek WAL mode masih aktif
5. Beri saya command copy-paste untuk setiap langkah
```

---

## 🔶 PROMPT AUDIT — Cek Kesehatan Proyek

```
Lanjutkan SIAKANUDA. Baca AGENTS.md, docs/AI_CONTEXT.md, docs/STATUS_FITUR.md.

Lakukan audit kesehatan proyek:
1. Cek konsistensi versi di semua file (server.js, package.json, AI_CONTEXT.md, STATUS_FITUR.md, .env, README.md, bot.js)
2. Cek apakah ada file sisa (.tar.gz lama, .tmp, stash tertinggal)
3. Cek dokumentasi masih akurat vs kode aktual
4. Cek .cursorrules/.clinerules menunjuk path yang benar (docs/AI_CONTEXT.md, docs/STATUS_FITUR.md)
5. Berikan laporan temuan dan rekomendasi perbaikan
```

---

## 💡 Tips Universal untuk Semua AI

1. **Dokumentasi berada di `docs/`** — `docs/AI_CONTEXT.md` dan `docs/STATUS_FITUR.md`, BUKAN di root
2. **Satu sesi = satu tugas fokus** — beri 1–3 tugas spesifik, bukan 10 sekaligus
3. **Sistem Anti-Lupa** — Git hook otomatis update docs saat commit, tapi HANYA header/stats. Changelog dan checklist fitur tetap harus manual
4. **Selalu commit sebelum cabut SSD** — hindari perubahan hilang saat pindah komputer
5. **Deploy = Patch** — Jangan full deploy, cukup kirim file yang berubah via tar.gz
6. **Bahasa bebas** — Indonesia atau Inggris, AI menyesuaikan
7. **Versi ada di 4 tempat** — Saat naik versi, update: `package.json`, `server.js` (2 lokasi), `.env` header
8. **Bot WA disconnect 408** — Hampir selalu karena folder `sessions/` corrupt. Hapus → restart → scan QR ulang

---

## 📂 File Referensi yang Harus Dibaca AI

| File | Fungsi | Lokasi | Kapan Baca |
|------|--------|--------|------------|
| `AGENTS.md` | Aturan wajib AI agent | Root proyek | Selalu |
| `docs/AI_CONTEXT.md` | Arsitektur, tech stack, folder map, changelog | `docs/` | Selalu |
| `docs/STATUS_FITUR.md` | Checklist fitur & statistik | `docs/` | Saat develop fitur |
| `PROMPT_TEMPLATE.md` | Template prompt ini | Root proyek | Saat handoff ke AI baru |
| `.cursorrules` / `.clinerules` | Aturan ringkas untuk Cursor/Cline | Root proyek | Otomatis dibaca IDE |
| `.env.example` | Template environment variables | Root proyek | Saat setup / debug |

---

## 🚨 Kesalahan Umum yang Sering Terjadi (Belajar dari Pengalaman)

| # | Kesalahan | Penyebab | Pencegahan |
|---|-----------|----------|------------|
| 1 | AI sarankan `php spark serve` di production | `AGENTS.md` belum diupdate | Selalu sertakan warning Nginx+FPM di prompt |
| 2 | Versi berbeda-beda di tiap file | AI lupa update semua lokasi | Update 4 tempat: `package.json`, `server.js` (×2), `.env` |
| 3 | Error tar `Cannot utime` saat extract | Folder `writable/` owned by `www-data` | Exclude `dashboard/writable` dari tar.gz |
| 4 | Bot WA looping disconnect 408 | Folder `sessions/` corrupt | `rm -rf sessions/` → restart → scan QR |
| 5 | AI tidak menemukan AI_CONTEXT.md | File di `docs/`, bukan root | Selalu tulis `docs/AI_CONTEXT.md` di prompt |
| 6 | Port 8080 conflict saat deploy | `siakadash` service masih aktif | `sudo systemctl disable siakadash` |
| 7 | GPS ditambah lalu dihapus (waste) | Fitur ditambah tanpa validasi user | Buat implementation plan dulu, minta approval |
| 8 | `.tar.gz` menumpuk 36+ file | Tidak ada cleanup policy | Pindahkan ke `_arsip_lama/patches/` setelah deploy |
