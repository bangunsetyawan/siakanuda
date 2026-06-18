#!/bin/bash
# =============================================================
# SIAKANUDA — Post-Boot Health Check & Recovery
# Dijalankan otomatis 60 detik setelah server boot via cron @reboot
# Lokasi: ~/siakanuda/scripts/health-check.sh
# =============================================================

LOG="/home/smknuda/siakanuda/backups/boot-$(date +%Y%m%d-%H%M%S).log"
SERVICES=("bot.siswa.service" "siakadash.service" "nginx" "php8.4-fpm" "cloudflared")

echo "========================================" | tee "$LOG"
echo "SIAKANUDA Boot Health Check" | tee -a "$LOG"
echo "Waktu: $(date '+%Y-%m-%d %H:%M:%S WIB')" | tee -a "$LOG"
echo "Uptime: $(uptime -p)" | tee -a "$LOG"
echo "========================================" | tee -a "$LOG"

ALL_OK=true

for svc in "${SERVICES[@]}"; do
    if systemctl is-active --quiet "$svc"; then
        echo "✅ $svc: RUNNING" | tee -a "$LOG"
    else
        echo "❌ $svc: DOWN — Restarting..." | tee -a "$LOG"
        sudo systemctl start "$svc" 2>&1 | tee -a "$LOG"
        sleep 3
        if systemctl is-active --quiet "$svc"; then
            echo "   ↳ ✅ Berhasil di-restart!" | tee -a "$LOG"
        else
            echo "   ↳ ❌ GAGAL restart! Perlu pengecekan manual." | tee -a "$LOG"
            ALL_OK=false
        fi
    fi
done

# Cek HTTP response dari Nginx (dashboard)
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:80 2>/dev/null)
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
    echo "✅ Dashboard HTTP: $HTTP_CODE (OK)" | tee -a "$LOG"
else
    echo "⚠️  Dashboard HTTP: $HTTP_CODE (unexpected)" | tee -a "$LOG"
fi

# Cek Node.js API
API_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:7860/ping 2>/dev/null)
if [ "$API_CODE" = "200" ]; then
    echo "✅ Node.js API: PONG (OK)" | tee -a "$LOG"
else
    echo "⚠️  Node.js API: $API_CODE (unexpected)" | tee -a "$LOG"
fi

echo "========================================" | tee -a "$LOG"
if [ "$ALL_OK" = true ]; then
    echo "🎉 Semua service berjalan normal!" | tee -a "$LOG"
else
    echo "⚠️  Ada service yang gagal! Cek log: $LOG" | tee -a "$LOG"
fi
echo "========================================" | tee -a "$LOG"
