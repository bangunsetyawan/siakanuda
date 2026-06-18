import { test, expect } from '@playwright/test';

test.describe('Skenario Ketua PKL', () => {

  test('1. Ketua PKL melakukan pelaporan jurnal harian', async ({ page }) => {
    // 1. Login sebagai Siswa
    await page.goto('/login');
    const btnSiswa = page.locator('button:has-text("Siswa")');
    await btnSiswa.click();
    
    // Menggunakan NISN asli Ketua PKL: 0099496822
    await page.locator('#username-siswa').fill('0099496822');
    await page.locator('#password').fill('009949682'); // Password yang sudah dikurangi 1 angka
    await page.locator('button[type="submit"]').click();

    // Pastikan masuk dashboard (atau profil jika disuruh ganti password)
    await page.waitForURL(/.*(dashboard|profile)/, { timeout: 10000 });
    
    // Jika ternyata masuk ke profil (paksa ganti password), kita harus kembali ke dashboard atau lewati
    if (page.url().includes('profile')) {
        await page.goto('/dashboard');
    }

    // 2. Akses halaman Laporan PKL langsung ke URL
    await page.goto('/pkl');
    await page.waitForURL(/.*pkl/);

    await expect(page).toHaveURL(/.*pkl/);

    // 3. Uji Coba Laporan
    const inputLokasi = page.locator('input[name="lokasi_presensi"]');
    if (await inputLokasi.isVisible()) {
        
        // Coba isi jurnal tanpa lokasi, cek apakah UI mengunci (misal disabled attribute)
        const inputJurnal = page.locator('textarea[name="jurnal"]');
        
        // Isi Lokasi
        await inputLokasi.fill('Kantor Cabang Utama - Divisi IT');
        
        // Isi Jurnal kurang dari 75 karakter
        await inputJurnal.fill('Hari ini saya belajar jaringan.');
        
        // Coba klik simpan
        const btnSimpan = page.locator('button:has-text("Simpan")').first();
        await btnSimpan.click();
        
        // Sistem seharusnya menahan form submit (HTML5 minlength atau JS)
        // Kita isi yang benar (>75 karakter)
        const jurnalValid = 'Hari ini saya belajar merakit jaringan komputer lokal (LAN), memotong kabel UTP, memasang konektor RJ45, dan melakukan pengujian menggunakan LAN Tester hingga semua lampu menyala.';
        await inputJurnal.fill(jurnalValid);
        
        // Bypass confirm dialog js
        page.on('dialog', dialog => dialog.accept());
        
        // Klik simpan lagi
        await btnSimpan.click();
        
        // Jika berhasil, akan ada alert success dan form dikunci
        // Tunggu sedikit agar proses backend (termasuk bot WA) selesai (max 10 detik)
        await page.waitForTimeout(2000);
        
        // Cek jika muncul tombol "Ubah Laporan" yang menandakan laporan berhasil disubmit
        const btnUbah = page.locator('button:has-text("Ubah Laporan")');
        if (await btnUbah.isVisible()) {
            console.log('Skenario berhasil: Laporan PKL disubmit dan form terkunci.');
        }

    } else {
        console.log('Sudah melaporkan hari ini atau bukan halaman input laporan.');
    }
  });

});
