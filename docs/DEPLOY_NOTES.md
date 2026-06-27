# 📝 Catatan Deploy & Troubleshooting — v1.11.1

> **Tanggal**: 18 Juni 2026
> **Tujuan**: Referensi cepat untuk deploy dan troubleshooting agar error tidak terulang.

---

## 🚀 SOP Deploy ke Production (Debian)

### Dari PC Lokal (Windows PowerShell):
```powershell
# 1. Buat arsip BERSIH (tanpa file sensitif)
tar -czf F:\Antigravity\update.tar.gz --exclude=".git" --exclude="node_modules" --exclude="sessions" --exclude="sessions_corrupt" --exclude="backups" --exclude=".env" --exclude="siakanuda.db" --exclude="uploads/pkl" --exclude=".tmp" --exclude="scratch" --exclude="siakanuda-apk" --exclude="test-results" --exclude="playwright-report" -C "F:\Antigravity" siakanuda

# 2. Upload ke server
scp F:\Antigravity\update.tar.gz [SSH_USER]@[IP_SERVER_TAILSCALE]:~/
```

### Di SSH (Debian):
```bash
# 3. Stop, extract, install, restart
sudo systemctl stop bot.siswa.service siakadash.service
tar -xzf ~/update.tar.gz -C ~/ --overwrite
cd ~/siakanuda && npm install --omit=dev
sudo systemctl start bot.siswa.service siakadash.service

# 4. Verifikasi
sudo journalctl -u bot.siswa.service --no-pager -n 10 --since "10 sec ago"
```

---

## 🩹 SOP Deploy Minor (Patch Update)

Gunakan metode ini jika Anda hanya mengubah **sebagian kecil file** (misal: hanya desain View atau Controller PHP) tanpa ada penambahan library baru (`npm install` / `composer`). Metode ini **sangat aman** karena tidak menghapus folder secara keseluruhan dan meminimalkan potensi *crash*.

### Dari PC Lokal (Windows PowerShell):
```powershell
# 1. Masuk ke folder proyek
cd F:\Antigravity\siakanuda

# 2. Buat arsip patch HANYA untuk file yang berubah (spesifik sebutkan lokasinya)
# Contoh:
tar -czf F:\Antigravity\patch.tar.gz dashboard/app/Config/Routes.php dashboard/app/Views/auth/login.php dashboard/app/Controllers/Bantuan.php dashboard/app/Views/bantuan/index.php

# 3. Upload file patch ke server
scp F:\Antigravity\patch.tar.gz [SSH_USER]@[IP_SERVER_TAILSCALE]:~/
```

### Di SSH (Debian):
```bash
# 4. Ekstrak langsung menimpa file di dalam folder siakanuda
tar -xzf ~/patch.tar.gz -C ~/siakanuda/ --overwrite

# 5. Restart service web agar CodeIgniter membaca file baru di memori
# (Catatan: Bot WA tidak perlu distop/direstart sehingga siswa tetap terlayani)
sudo systemctl restart siakadash.service
```

---

### ⚠️ JANGAN sertakan dalam arsip:
| File/Folder | Alasan |
|-------------|--------|
| `.env` | Beda konfigurasi lokal vs production. AKAN MENIMPA! |
| `siakanuda.db` | Database production. AKAN MENIMPA! |
| `node_modules/` | Install ulang di server. Membengkakkan arsip. |
| `sessions/` | Session WA bot. Harus generate ulang (scan QR). |

---

## ✅ Post-Deploy Checklist

- [ ] Banner startup menampilkan versi yang benar (cek log `journalctl`)
- [ ] `curl localhost:80` → response 302 (dashboard aktif)
- [ ] `curl localhost:7860/ping` → response PONG (Node.js aktif)
- [ ] Dashboard bisa diakses via `https://siakanuda.qzz.io/`
- [ ] QR code muncul di halaman Pengaturan WA (jika bot belum connect)
- [ ] Bot WA terhubung setelah scan QR

---

## 🔌 Setelah Pemadaman Listrik (Server Reboot)

Saat server Debian reboot setelah mati lampu, semua 5 service akan otomatis start:

| Service | Fungsi | Auto-Start |
|---------|--------|------------|
| `bot.siswa.service` | Node.js WA Bot + API (port 7860) | ✅ enabled |
| `siakadash.service` | PHP spark serve (port 8080, LAN) | ✅ enabled |
| `nginx` | Reverse proxy (port 80, internet) | ✅ enabled |
| `php8.4-fpm` | PHP processor untuk Nginx | ✅ enabled |
| `cloudflared` | Cloudflare Tunnel (internet access) | ✅ enabled |

### Checklist Tambahan Setelah Pemadaman:
1. SSH ke server → jalankan `bash ~/siakanuda/scripts/health-check.sh`
2. Cek Cloudflare Dashboard:
   - **Workers & Pages** → pastikan TIDAK ADA route `siakanuda.qzz.io/*` di Worker darurat
   - **Caching** → Purge Everything (jika halaman lama ter-cache)
3. Buka `https://siakanuda.qzz.io/` → login → **Pengaturan WA** → scan QR bot

> **PENTING**: Bot WA akan SELALU perlu scan QR ulang setelah mati lampu lama,
> karena session WhatsApp expired jika bot offline > 14 hari.
> Untuk pemadaman singkat (< 1 jam), bot biasanya auto-reconnect.

---

## 🐛 Bug yang Pernah Terjadi (Jangan Ulangi!)

### 1. QR Code Tidak Muncul di Dashboard (via Internet)
**Gejala**: Halaman Pengaturan WA loading terus, console Chrome error `ERR_CONNECTION_REFUSED`.
**Penyebab**: Socket.io di frontend hardcoded ke `http://127.0.0.1:7860` — tidak bisa dijangkau browser via internet.
**Fix**: Frontend auto-detect origin. Jika akses via internet → gunakan `window.location.origin` (lewat Nginx proxy). Sudah di-fix di commit `8bc05fc`.

### 2. `supabase.from is not a function`
**Gejala**: Bot log error saat startup, WA tidak connect.
**Penyebab**: `bot.js` mengirim URL string ke fungsi yang mengharapkan Supabase client object.
**Fix**: Buat `createClient()` dulu sebelum memanggil `useSupabaseAuthState()`. Sudah di-fix di commit `05c5493`.

### 3. Halaman "Sistem Sedang Offline" Muncul Padahal Server Hidup
**Gejala**: Website menampilkan halaman darurat meskipun server running.
**Penyebab**: Cloudflare Worker `pengaman-siakanuda` dengan route `siakanuda.qzz.io/*` masih aktif.
**Fix**: Hapus route di Cloudflare Dashboard → Workers & Pages → pengaman-siakanuda → Routes → Delete.

### 4. Bot WA Error 401 (Unauthorized) Terus-Menerus
**Gejala**: Log bot menunjukkan `Disconnected. Reason: 401. Reconnect: false`.
**Penyebab**: Session WA lama expired (di Supabase atau di folder sessions/).
**Fix**: Hapus sessions → restart:
```bash
rm -rf ~/siakanuda/sessions/* && sudo systemctl restart bot.siswa.service
```
Lalu scan QR ulang dari dashboard.

### 5. `.env` Production Tertimpa Saat Deploy
**Gejala**: Konfigurasi server berubah setelah deploy (misal `USE_LOCAL_AUTH` muncul).
**Penyebab**: Arsip tar.gz menyertakan file `.env`.
**Fix**: SELALU exclude `.env` dari arsip deploy (sudah termasuk di SOP di atas).

### 6. Versi Tidak Konsisten di Banyak File
**Gejala**: Banner menampilkan versi lama, `/api/status` beda versi, dokumentasi beda versi.
**Lokasi versi yang harus di-update saat naik versi**:
- `package.json` → field `version`
- `execution/server.js` → header comment + `/api/status` response (sekarang otomatis dari package.json)
- `AI_CONTEXT.md` → header "Terakhir diupdate"
- `ROADMAP.md` → header "Versi"
- Git tag → `git tag vX.Y.Z`
- Banner startup → sekarang otomatis dari package.json ✅

---

## 🏗️ Arsitektur Auth WA (Keputusan 18 Juni 2026)

**Supabase Auth State untuk WA session TIDAK DIGUNAKAN.**

| Environment | Auth WA | Konfigurasi |
|-------------|---------|-------------|
| Localhost (Windows) | Local File (`sessions/`) | `USE_LOCAL_AUTH=true` di `.env` |
| Production (Debian) | Local File (`sessions/`) | `USE_LOCAL_AUTH=true` di `.env` |

Supabase tetap dipakai HANYA untuk **backup foto PKL** ke cloud storage (via `photo_sync.js`).

**Alasan**: Supabase Auth State menyebabkan:
- Conflict session antara localhost dan production
- Error `supabase.from is not a function` (bug di bot.js)
- Session expired 401 yang sulit di-debug
- Kompleksitas tanpa manfaat untuk server tunggal
