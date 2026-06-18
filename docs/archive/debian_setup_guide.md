# 🚀 Panduan Setup & Deployment Debian Server — SIAKANUDA v1.2.3
> **Status:** Terverifikasi & Berjalan Aktif | **Tanggal:** 2 Juni 2026

Dokumen ini adalah panduan teknis mengenai konfigurasi server Debian di sekolah untuk sistem **SIAKANUDA v1.2.3** (CodeIgniter 4 Web Dashboard + WhatsApp Bot).

---

## 📡 1. Arsitektur & Topologi Jaringan

Server Debian dihubungkan ke infrastruktur jaringan sekolah menggunakan Switch Managed dan Router Mikrotik agar memiliki performa lokal yang cepat serta IP yang statis.

```
                  [ Internet / ISP ]
                          │
                   ( WAN Ethernet )
                          │
               [ Mikrotik RB750Gr3 ]
                          │
                    ( Trunk / LAN )
                          │
            [ Switch TP-Link TL-SG3210 ]
             ├── Server Debian (Lokal: 10.10.11.37 / Tailscale: 100.110.83.48) -> Port 8080 (CI4) & 7860 (Node.js API)
             ├── Access Point Wi-Fi Ujian
             └── Komputer Lab Sekolah
```

* **IP Server Lokal:** `10.10.11.37` (atau `100.110.83.48` jika diakses via Tailscale)
* **Subnet Mask:** `255.255.254.0` (`/23`)
* **Gateway/DNS:** Dikelola oleh Mikrotik. IP server diset permanen/statis melalui DHCP Lease Reservation pada Winbox Mikrotik.

---

## 📦 2. Pemasangan Runtime & Dependency (Debian)

Semua package berikut diinstal di server Debian 13 (Trixie) Headless:

```bash
# 1. Update Package List
sudo apt update

# 2. Instalasi Node.js (via NodeSource v22)
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt-get install -y nodejs

# 3. Instalasi PHP 8.4 & Ekstensi untuk CodeIgniter 4
sudo apt install -y php-cli php-sqlite3 php-mbstring php-xml php-curl php-intl php-zip unzip

# 4. Instalasi Composer Secara Global
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

---

## 📂 3. Direktori Proyek & Instalasi Package

Proyek diletakkan pada folder `/home/smknuda/siakanuda/`.

1. **Instalasi Package Node.js (Background API & Bot WA):**
   ```bash
   cd ~/siakanuda
   npm install --production
   ```
2. **Instalasi Package PHP (Dashboard CI4):**
   ```bash
   cd ~/siakanuda/dashboard
   composer install --no-dev
   ```

---

## ⚙️ 4. Konfigurasi Environment (`.env`)

### Root `.env` (Node.js Bot WA) — `~/siakanuda/.env`
Tambahkan alamat IP lokal server Debian di bagian bawah:
```env
BASE_URL=http://10.10.11.37:7860   # ATAU http://100.110.83.48:7860 jika via Tailscale
```

### Dashboard `.env` (CodeIgniter 4) — `~/siakanuda/dashboard/.env`
Sesuaikan URL akses dashboard agar mengarah ke port 8080 (CI4):
```env
app.baseURL = 'http://10.10.11.37:8080/'   # ATAU 'http://100.110.83.48:8080/' jika via Tailscale
database.default.DBDriver = SQLite3
database.default.database = ../../siakanuda.db
WA_BOT_URL = 'http://127.0.0.1:7860'
```

---

## 🛠️ 5. Konfigurasi Autostart (Systemd Services)

Layanan berjalan di latar belakang sebagai background service yang akan menyala otomatis saat server booting.

### A. Service WhatsApp Bot & API (`/etc/systemd/system/bot.siswa.service`)
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

### B. Service Dashboard CI4 (`/etc/systemd/system/siakadash.service`)
```ini
[Unit]
Description=SIAKANUDA Dashboard — CodeIgniter 4
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
SyslogIdentifier=siakadash

[Install]
WantedBy=multi-user.target
```

### Perintah Pengelolaan Service:
```bash
# Reload Konfigurasi Systemd
sudo systemctl daemon-reload

# Menyalakan & Mengaktifkan Auto-start Saat Booting
sudo systemctl enable --now bot.siswa
sudo systemctl enable --now siakadash

# Memeriksa Status Layanan
sudo systemctl status bot.siswa
sudo systemctl status siakadash

# Memantau Log Aktivitas Real-time
journalctl -u bot.siswa -f
journalctl -u siakadash -f
```

---

## 🔒 6. Manajemen Remote & Pengiriman Update (Tailscale)

Untuk mengelola server secara remote via SSH atau transfer update dari rumah:

1. **Instalasi Tailscale di Debian:**
   ```bash
   curl -fsSL https://tailscale.com/install.sh | sh
   sudo tailscale up
   ```
2. **Cara Deploy Update dari Laptop Windows:**
   ```powershell
   # 1. Kompres perubahan di Windows
   tar --exclude="node_modules" --exclude="dashboard/vendor" --exclude=".git" -czf update.tar.gz -C F:\Antigravity siakanuda
   
   # 2. Kirim via SCP menggunakan IP Tailscale
   scp update.tar.gz smknuda@100.110.83.48:~/
   
   # 3. SSH & Ekstrak di Debian
   ssh smknuda@100.110.83.48
   tar -xzf update.tar.gz -C ~/
   sudo systemctl restart bot.siswa
   sudo systemctl restart siakadash
   ```
