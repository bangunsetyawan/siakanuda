# 🚀 Directive: Deployment — Panduan Deploy ke Debian Lenovo (v1.2.3)
> SOP untuk AI agent saat mendampingi proses deployment ke server produksi sekolah.

---

## 📡 Konteks & Topologi Target
* **Sumber (Lokal):** Windows PC `F:\Antigravity\siakanuda`
* **Target (Produksi):** Lenovo Notebook Debian 13 Headless
* **Lokasi Folder Target:** `/home/smknuda/siakanuda`
* **IP Server Debian:** Lokal `10.10.11.37` | Tailscale `100.110.83.48`
* **Port Layanan:**
  - **8080** (CodeIgniter 4 Dashboard) — **Exposed / Diakses oleh Pengguna & APK**
  - **7860** (Node.js Background API) — **Internal / Hidden** (Hanya dipanggil secara lokal oleh CI4)

---

## 📋 Langkah-Langkah Deployment

### Step 1: Transfer File Ke Server Debian
Pastikan folder `node_modules/`, `dashboard/vendor/`, `.git/`, dan `sessions/` lokal diabaikan untuk mempercepat transfer.

Dari PowerShell Windows (menggunakan IP Tailscale/Lokal Debian):
```powershell
# 1. Kompres seluruh folder kerja
tar --exclude="node_modules" --exclude="dashboard/vendor" --exclude=".git" --exclude="sessions" -czf update.tar.gz -C F:\Antigravity siakanuda

# 2. Kirim file arsip ke Debian
scp update.tar.gz smknuda@100.110.83.48:~/

# 3. Masuk ke SSH server Debian dan ekstrak
ssh smknuda@100.110.83.48
tar -xzf update.tar.gz -C ~/
```

### Step 2: Install Dependency (Jika Diperlukan saja)
Jika ada library baru yang dipasang selama development:
```bash
# Node.js Background API
cd ~/siakanuda
npm install --production

# PHP CodeIgniter 4 Dashboard
cd ~/siakanuda/dashboard
composer install --no-dev
```

### Step 3: Setup Konfigurasi Systemd Services

SIAKANUDA v1.2.3 membutuhkan **dua service** yang berjalan di background Debian.

#### A. WhatsApp Bot / Background API Service (`/etc/systemd/system/bot.siswa.service`)
```ini
[Unit]
Description=SIAKANUDA — WhatsApp Bot & Background API
After=network-online.target
Wants=network-online.target

[Service]
Type=simple
User=smknuda
WorkingDirectory=/home/smknuda/siakanuda
ExecStart=/usr/bin/node --env-file=.env execution/server.js
Restart=always
RestartSec=5
Environment=NODE_ENV=production
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

#### B. CodeIgniter 4 Dashboard Service (`/etc/systemd/system/siakadash.service`)
```ini
[Unit]
Description=SIAKANUDA — Dashboard CodeIgniter 4
After=network-online.target
Wants=network-online.target

[Service]
Type=simple
User=smknuda
WorkingDirectory=/home/smknuda/siakanuda/dashboard
ExecStart=/usr/bin/php spark serve --host 0.0.0.0 --port 8080
Restart=always
RestartSec=5
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

Jalankan perintah berikut untuk memuat ulang dan mengaktifkan service:
```bash
sudo systemctl daemon-reload
sudo systemctl enable --now bot.siswa
sudo systemctl enable --now siakadash
```

---

## 🔍 Cara Verifikasi Hasil Deployment

1. **Akses Dashboard Sekolah:**
   Buka `http://10.10.11.37:8080` dari laptop/HP yang terhubung ke Wi-Fi sekolah.
2. **Cek Koneksi WhatsApp Bot:**
   Buka menu panel WhatsApp di dashboard CI4 (Admin Area). Pastikan status bot terlaporkan sebagai `connected`.
3. **Pantau Log Layanan:**
   ```bash
   journalctl -u bot.siswa -f       # Pantau log bot WA
   journalctl -u siakadash -f      # Pantau log dashboard CI4
   ```
