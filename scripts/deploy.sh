#!/bin/bash
# Script Automasi Deployment SIAKANUDA

echo "🚀 Memulai deployment SIAKANUDA..."

# 1. Tarik pembaruan terbaru dari Git
echo "📦 Pulling latest changes from Git..."
git pull origin main

# 2. Update dependensi PHP (jika ada)
echo "🧩 Updating Composer dependencies..."
if command -v composer &> /dev/null
then
    cd dashboard && composer install --no-dev --optimize-autoloader && cd ..
else
    echo "⚠️ Composer tidak ditemukan. Lewati update composer."
fi

# 3. Membersihkan Cache CodeIgniter (Otomatis)
echo "🧹 Membersihkan cache sistem..."
if [ -d "dashboard/writable/cache" ]; then
    rm -rf dashboard/writable/cache/*
    echo "✅ Cache framework berhasil dibersihkan."
fi

echo "✅ Deployment selesai! Sistem siap digunakan."
