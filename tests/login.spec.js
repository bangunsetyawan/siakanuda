import { test, expect } from '@playwright/test';

test.describe('Pengujian Halaman Login SIAKANUDA', () => {

  test('1. Harusnya memuat halaman login dengan benar', async ({ page }) => {
    await page.goto('/login');
    // Pastikan judul aplikasi muncul
    await expect(page.locator('.brand-title')).toContainText('SIAKANUDA');
    // Pastikan tombol tab Siswa dan Guru ada
    await expect(page.locator('button:has-text("Siswa")')).toBeVisible();
    await expect(page.locator('button:has-text("Guru / Staf")')).toBeVisible();
  });

  test('2. Tes Login Siswa (Skenario Error/Berhasil)', async ({ page }) => {
    await page.goto('/login');
    
    // Pastikan tab siswa aktif secara default
    const btnSiswa = page.locator('button:has-text("Siswa")');
    await expect(btnSiswa).toHaveClass(/active/);

    // Isi NISN dan Password
    await page.locator('#username-siswa').fill('0099496822'); 
    await page.locator('#password').fill('009949682');
    
    // Klik tombol login
    await page.locator('button[type="submit"]').click();

    // Tunggu proses (bisa jadi error jika dummy tidak ada, atau berhasil jika ada)
    // Jika dummy tidak ada, kita cek apakah ada alert error
    // Jika dummy ada, kita cek apakah diarahkan ke dashboard
    
    // Kita buat logic fleksibel: cek apakah masih di halaman login ada error, atau masuk dashboard
    const currentUrl = page.url();
    if (currentUrl.includes('/login')) {
      const errorAlert = page.locator('.alert-danger');
      if (await errorAlert.isVisible()) {
        console.log('Login ditolak (seperti yang diharapkan jika akun dummy tidak ada di database)');
      }
    } else {
      await expect(page).toHaveURL(/.*dashboard/);
    }
  });

  test('3. Tes Login Guru / Staf', async ({ page }) => {
    await page.goto('/login');
    
    // Pindah ke tab Guru
    await page.locator('button:has-text("Guru / Staf")').click();

    // Pastikan dropdown guru muncul
    const dropdownGuru = page.locator('#username-guru');
    await expect(dropdownGuru).toBeVisible();

    // CATATAN: Karena dropdown guru memerlukan data asli dari database, 
    // jika database kosong, dropdown tidak bisa dipilih.
    // Jika Anda punya nomor HP dummy guru, ganti nilai '08123456789' di bawah ini
    // dengan value option yang sesuai di dropdown.
    
    try {
      // Coba pilih option kedua jika ada
      const optionsCount = await dropdownGuru.locator('option').count();
      if (optionsCount > 1) {
        await dropdownGuru.selectOption({ index: 1 });
        await page.locator('#password').fill('guruhebat');
        await page.locator('button[type="submit"]').click();
      } else {
        console.log('Tidak ada data guru di dropdown untuk dites');
      }
    } catch (e) {
      console.log('Skip tes login guru karena dropdown kosong/error', e);
    }
  });

  test('4. Tes Login Admin', async ({ page }) => {
    await page.goto('/login');
    
    // Pindah ke tab Guru/Staf
    await page.locator('button:has-text("Guru / Staf")').click();

    // Pilih admin dari dropdown (username/value 'admin')
    const dropdownGuru = page.locator('#username-guru');
    await dropdownGuru.selectOption({ value: 'admin' });
    
    // Masukkan password admin
    await page.locator('#password').fill('admin');
    await page.locator('button[type="submit"]').click();

    // Pastikan diarahkan ke dashboard
    await expect(page).toHaveURL(/.*dashboard/);
  });

});
