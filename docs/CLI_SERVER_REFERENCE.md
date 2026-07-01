# 🖥️ CLI Server Reference — SIAKANUDA

> **Referensi perintah SSH untuk server production.**
> Copy-paste langsung ke terminal SSH `[SSH_USER]@[IP_SERVER_LAN]` (LAN) atau `[SSH_USER]@[IP_SERVER_TAILSCALE]` (Tailscale).
> Semua perintah sudah diverifikasi pada server aktual (1 Juli 2026).

---

## 📋 Daftar Isi

1. [Akses Server](#-1-akses-server)
2. [Cek Kondisi Cepat](#-2-cek-kondisi-cepat-1-menit)
3. [Status Service](#-3-status-service)
4. [Restart Service](#-4-restart-service)
5. [Bot WA Bermasalah](#-5-bot-wa-bermasalah)
6. [Database](#-6-database)
7. [Log & Troubleshooting](#-7-log--troubleshooting)
8. [Memory & Disk](#-8-memory--disk)
9. [Deployment (Patch)](#-9-deployment-patch)
10. [Nginx & PHP-FPM Config](#-10-nginx--php-fpm-config)
11. [Maintenance Opsional](#-11-maintenance-opsional)

---

## 🔑 1. Akses Server

```bash
# Dari jaringan LAN sekolah
ssh [SSH_USER]@[IP_SERVER_LAN]

# Dari luar sekolah (via Tailscale VPN)
ssh [SSH_USER]@[IP_SERVER_TAILSCALE]
```

> Monitoring web: https://cockpit.siakanuda.qzz.io (port 9090)

---

## ⚡ 2. Cek Kondisi Cepat (1 menit)

Paste ini untuk overview lengkap dalam sekali jalan:

```bash
echo "── SERVICE ──"; for svc in nginx php8.4-fpm bot.siswa cloudflared; do echo "  $svc: $(sudo systemctl is-active $svc)"; done; echo "── API ──"; curl -s http://localhost:7860/api/status; echo ""; echo "── MEMORY ──"; free -h | head -2; echo "── DISK ──"; df -h / | tail -1; echo "── UPTIME ──"; uptime
```

---

## 🔍 3. Status Service

### Lihat status semua service

```bash
# Ringkasan (active/inactive)
for svc in nginx php8.4-fpm bot.siswa cloudflared; do
  echo "$svc: $(sudo systemctl is-active $svc) ($(sudo systemctl is-enabled $svc))"
done
```

### Status detail per service

```bash
# Bot WA (Node.js)
sudo systemctl status bot.siswa --no-pager -l

# PHP-FPM
sudo systemctl status php8.4-fpm --no-pager -l

# Nginx
sudo systemctl status nginx --no-pager -l

# Cloudflare Tunnel
sudo systemctl status cloudflared --no-pager -l
```

### Cek API Bot WA

```bash
# Cek koneksi WA + versi
curl -s http://localhost:7860/api/status
```

Output normal:
```json
{"connected":true,"name":"siakanuda","version":"1.20.9",...}
```

### Cek port yang listening

```bash
sudo ss -tlnp | grep -E ':(80|8080|7860|443) '
```

Port yang harus aktif:
| Port | Service |
|------|---------|
| 80   | nginx (Cloudflare Tunnel) |
| 8080 | nginx (akses LAN) |
| 7860 | node (Bot WA + API) |
| 443  | tailscaled (VPN) |

---

## 🔄 4. Restart Service

> ⚠️ **JANGAN restart `siakadash`** — service itu sudah DISABLED.

### Kapan restart apa?

| Perubahan | Service yang direstart |
|-----------|----------------------|
| File PHP (`dashboard/`) | `php8.4-fpm` |
| File JS (`execution/`) | `bot.siswa` |
| File statis (CSS/JS/gambar) | Tidak perlu restart |
| Config Nginx | `nginx` |

### Perintah restart

```bash
# Restart PHP-FPM (setelah ubah file .php)
sudo systemctl restart php8.4-fpm

# Restart Bot WA (setelah ubah file .js)
sudo systemctl restart bot.siswa

# Restart Nginx (setelah ubah config nginx)
sudo systemctl restart nginx

# Reload Nginx tanpa downtime (lebih aman)
sudo systemctl reload nginx
```

### Restart semua (hati-hati, bot WA perlu reconnect)

```bash
sudo systemctl restart nginx php8.4-fpm bot.siswa
```

---

## 📱 5. Bot WA Bermasalah

### Gejala: Bot offline / disconnect terus-menerus

**Langkah 1 — Cek log dulu**
```bash
sudo journalctl -u bot.siswa -n 50 --no-pager
```

**Langkah 2 — Cek koneksi internet**
```bash
curl -sI https://web.whatsapp.com | head -3
```

**Langkah 3 — Identifikasi masalah dari log**

| Log yang muncul | Artinya | Tindakan |
|-----------------|---------|----------|
| `Disconnected. Reason: 500` | Timeout biasa | Tunggu, auto-reconnect 5 detik |
| `Disconnected. Reason: 408` (looping) | Session corrupt | Reset session (lihat bawah) |
| `MessageCounterError` | Pesan lama gagal decrypt | Normal, abaikan |
| `SQLITE_BUSY` | Database terkunci | Cek proses lain yang akses DB |
| `✅ WhatsApp Connected!` | Bot berhasil connect | Tidak perlu tindakan |

### Reset Session WA (untuk disconnect 408 yang looping)

```bash
# 1. Stop bot
sudo systemctl stop bot.siswa

# 2. Hapus session lama
rm -rf ~/siakanuda/sessions

# 3. Start ulang bot
sudo systemctl start bot.siswa

# 4. Cek log — tunggu muncul QR code
sudo journalctl -u bot.siswa -f
```

**Setelah itu:**
1. Buka https://siakanuda.qzz.io/whatsapp-settings
2. Scan QR Code dengan HP yang nomornya terdaftar
3. Tunggu sampai status **Connected**

> Tekan `Ctrl+C` untuk keluar dari `journalctl -f`

### Cek status WA dari browser

```bash
curl -s http://localhost:7860/api/status | python3 -m json.tool 2>/dev/null || curl -s http://localhost:7860/api/status
```

---

## 🗄️ 6. Database

> ⚠️ `sqlite3` CLI tidak terinstall di server. Gunakan Node.js untuk akses DB.

### Cek ukuran database

```bash
ls -lh ~/siakanuda/siakanuda.db*
```

Output normal:
```
siakanuda.db       ~864K   (file utama)
siakanuda.db-wal   ~1-5M   (Write-Ahead Log — normal)
siakanuda.db-shm   ~32K    (Shared Memory)
```

### Cek ownership & permission

```bash
ls -la ~/siakanuda/siakanuda.db*
```

Harus `rwxrwxrwx` (777) agar `smknuda` (Node.js) dan `www-data` (PHP-FPM) bisa akses.

### Install sqlite3 CLI (opsional, untuk debugging)

```bash
sudo apt install sqlite3
```

Setelah install, bisa cek:
```bash
# Cek WAL mode
sqlite3 ~/siakanuda/siakanuda.db "PRAGMA journal_mode;"
# Harus output: wal

# Cek integritas
sqlite3 ~/siakanuda/siakanuda.db "PRAGMA integrity_check;"
# Harus output: ok

# Paksa WAL checkpoint (merge WAL ke DB utama)
sqlite3 ~/siakanuda/siakanuda.db "PRAGMA wal_checkpoint(TRUNCATE);"
```

### WAL checkpoint via Node.js (tanpa install sqlite3)

```bash
cd ~/siakanuda && node -e "
  import Database from 'better-sqlite3';
  const db = new Database('./siakanuda.db');
  const r = db.pragma('wal_checkpoint(TRUNCATE)');
  console.log('Checkpoint result:', r);
  db.close();
"
```

---

## 📜 7. Log & Troubleshooting

### Log Bot WA

```bash
# 30 baris terakhir
sudo journalctl -u bot.siswa -n 30 --no-pager

# Follow real-time (Ctrl+C untuk keluar)
sudo journalctl -u bot.siswa -f

# Log hari ini saja
sudo journalctl -u bot.siswa --since today --no-pager

# Log 1 jam terakhir
sudo journalctl -u bot.siswa --since "1 hour ago" --no-pager
```

### Log Nginx

```bash
# Error log
sudo tail -30 /var/log/nginx/error.log

# Access log (request masuk)
sudo tail -30 /var/log/nginx/access.log

# Follow real-time
sudo tail -f /var/log/nginx/error.log
```

### Log PHP-FPM

```bash
sudo journalctl -u php8.4-fpm -n 20 --no-pager
```

### Cek siapa yang akses server sekarang

```bash
who
```

### Cek proses yang paling banyak makan RAM

```bash
ps -eo pid,rss,args --sort=-rss | head -15
```

---

## 💾 8. Memory & Disk

### Memory

```bash
free -h
```

Kondisi sehat:
- `available` > 500 MB
- `Swap used` < 100 MB

### Disk

```bash
df -h /
```

Kondisi sehat: `Use%` < 80%

### Ukuran folder siakanuda

```bash
du -sh ~/siakanuda/
du -sh ~/siakanuda/node_modules/
du -sh ~/siakanuda/sessions/
du -sh ~/siakanuda/uploads/
```

### Ukuran log journald

```bash
sudo journalctl --disk-usage
```

### Cek load CPU

```bash
uptime
# atau
htop
```

---

## 📦 9. Deployment (Patch)

> Semua perubahan dari PC lokal → patch `.tar.gz` → upload → extract di server.
> **JANGAN edit file langsung di server.**

### Upload patch dari PC lokal (jalankan di PC, bukan server)

```powershell
# Dari PowerShell di PC lokal
scp F:\Antigravity\siakanuda-patch.tar.gz [SSH_USER]@[IP_SERVER_LAN]:~/
```

### Extract patch di server

```bash
cd ~
tar -xzf ~/siakanuda-patch.tar.gz -C ~/
```

### Restart service setelah deploy

```bash
# Jika ada perubahan file PHP
sudo systemctl restart php8.4-fpm

# Jika ada perubahan file JS (execution/)
sudo systemctl restart bot.siswa

# Jika keduanya berubah
sudo systemctl restart php8.4-fpm bot.siswa
```

### Hapus patch setelah extract

```bash
rm ~/siakanuda-patch.tar.gz
```

### File yang HARUS di-exclude dari tar.gz

```
node_modules/
sessions/
.env
*.db
*.db-wal
*.db-shm
dashboard/writable/
*.tar.gz
.git/
uploads/
backups/
```

---

## ⚙️ 10. Nginx & PHP-FPM Config

### Lihat config PHP-FPM

```bash
sudo cat /etc/php/8.4/fpm/pool.d/www.conf | grep -E "^pm\.|^user|^group|^listen"
```

Nilai saat ini:
```
pm.max_children = 12
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
pm.max_requests = 500
```

### Lihat config Nginx

```bash
sudo cat /etc/nginx/nginx.conf
```

### Lihat site config Nginx

```bash
ls /etc/nginx/sites-enabled/
sudo cat /etc/nginx/sites-enabled/*
```

### Test config Nginx (sebelum reload)

```bash
sudo /usr/sbin/nginx -t
```

### Versi software

```bash
node -v
php -v | head -1
/usr/sbin/nginx -v
```

---

## 🧹 11. Maintenance Opsional

### Batasi ukuran log journald

```bash
# Batasi log maksimal 100MB
sudo journalctl --vacuum-size=100M

# Atau batasi log maksimal 7 hari
sudo journalctl --vacuum-time=7d
```

### Fix ownership database (jika ada permission error)

```bash
sudo chown smknuda:smknuda ~/siakanuda/siakanuda.db*
chmod 777 ~/siakanuda/siakanuda.db*
```

### Cek disk space terpakai per folder

```bash
du -sh ~/siakanuda/*/ 2>/dev/null | sort -rh | head -10
```

### Lihat service yang enabled (auto-start on boot)

```bash
sudo systemctl list-unit-files --state=enabled | grep -E "nginx|php|bot|cloud|cockpit"
```

### Reboot server (darurat saja)

```bash
sudo reboot
```

> Setelah reboot, semua service auto-start. Health-check script jalan otomatis setelah 60 detik.

---

## 📞 Info Koneksi

| Jalur | Alamat |
|-------|--------|
| SSH LAN | `[SSH_USER]@[IP_SERVER_LAN]` |
| SSH Tailscale | `[SSH_USER]@[IP_SERVER_TAILSCALE]` |
| Web Dashboard | https://siakanuda.qzz.io |
| Cockpit | https://cockpit.siakanuda.qzz.io |
| WA Settings | https://siakanuda.qzz.io/whatsapp-settings |
| API Status | http://localhost:7860/api/status (dari SSH) |

---

> 📅 Terakhir diperbarui: 1 Juli 2026
> 📝 Semua perintah diverifikasi pada Debian 13 (Trixie), server SIAKANUDA production.
