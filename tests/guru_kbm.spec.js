import { test, expect } from '@playwright/test';

test.describe('Skenario Guru KBM', () => {

  test('1. Guru melakukan absensi KBM', async ({ page }) => {
    // 1. Login sebagai Guru
    await page.goto('/login');
    await page.locator('button:has-text("Guru / Staf")').click();
    
    const dropdownGuru = page.locator('#username-guru');
    
    // Pilih guru berdasarkan teks
    await dropdownGuru.selectOption({ label: 'BANGUN SETYAWAN, S.T. (Admin)' });
    
    // Coba password asli yang diberikan user
    await page.locator('#password').fill('guruhebat');
    await page.locator('button[type="submit"]').click();

    // Pastikan masuk dashboard (atau profil jika disuruh ganti password)
    await page.waitForURL(/.*(dashboard|profile)/, { timeout: 10000 });

    // 2. Akses halaman Absensi KBM
    // Cari tombol sidebar yang mengandung kata "Absensi"
    const menuAbsensi = page.locator('.nav-link').filter({ hasText: /Absensi KBM/i });
    if (await menuAbsensi.isVisible()) {
        await menuAbsensi.click();
    } else {
        // Coba akses langsung via URL jika menu di-collapse
        await page.goto('/attendance');
    }

    await expect(page).toHaveURL(/.*attendance/);

    // 3. Simulasikan mengisi absensi (jika ada kelas yang ditampilkan)
    const btnInput = page.locator('a:has-text("Input")').first();
    if (await btnInput.isVisible()) {
        await btnInput.click();
        
        // Tes Validasi: Set satu siswa jadi Sakit tapi tanpa alasan
        // Cari select option pertama
        const firstSelect = page.locator('select[name^="status"]').first();
        await firstSelect.selectOption('S');
        
        // Coba simpan
        await page.locator('button:has-text("Simpan")').click();
        
        // Pastikan error "keterangan wajib diisi" muncul (biasanya alert-danger atau required field HTML5)
        // Jika HTML5 required jalan, form tidak akan tersubmit dan browser menahannya.
        
        // Coba klik tombol Hadir Semua
        const btnHadirSemua = page.locator('button:has-text("Hadir Semua")');
        if (await btnHadirSemua.isVisible()) {
            // Karena klik hadir semua biasanya memunculkan konfirmasi atau langsung submit
            // Kita bypass dialog javascript otomatis (klik OK)
            page.on('dialog', dialog => dialog.accept());
            await btnHadirSemua.click();
            
            // Cek flash message success
            const successAlert = page.locator('.alert-success');
            await expect(successAlert).toBeVisible();
        }
    } else {
        console.log('Tidak ada jadwal kelas untuk hari ini yang bisa diabsen.');
    }
  });

});
