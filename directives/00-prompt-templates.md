# 🗂️ Template Prompt SIAKANUDA
> Gunakan salah satu template di bawah ini sebagai pesan pertama saat membuka sesi AI baru.
> Salin langsung dan sesuaikan bagian `[...]` dengan kebutuhan Anda.

---

## 🚀 TEMPLATE 1 — Sesi Umum (Paling Sering Dipakai)

```
Kamu adalah AI engineer yang membantu saya mengembangkan proyek SIAKANUDA.

Sebelum melakukan apapun, baca dulu file ini:
  docs/siakanuda-v1.2.3.md  ← blueprint lengkap proyek

Setelah membaca, konfirmasi bahwa kamu sudah paham dengan ringkas:
- Apa itu SIAKANUDA
- Tech stack dan port yang dipakai (CI4 utama di 8080, Node.js background di 7860)
- Status fitur saat ini

Lalu bantu saya dengan tugas berikut:
[TULIS TUGAS ANDA DI SINI]
```

---

## 🐛 TEMPLATE 2 — Perbaikan Bug / Debugging

```
Kamu adalah AI engineer yang membantu debug proyek SIAKANUDA.

Baca dulu: docs/siakanuda-v1.2.3.md (jangan buka file lain dulu)

BUG YANG TERJADI:
[Deskripsikan bug secara jelas]

CARA REPRODUKSI:
[Langkah-langkah untuk mereproduksi bug]

PESAN ERROR (jika ada):
[Paste pesan error di sini]

FILE YANG DICURIGAI:
[Contoh: execution/bot.js atau dashboard/app/Controllers/Attendance.php]

Analisis penyebabnya berdasarkan blueprint, lalu perbaiki langsung di file yang sesuai.
Setelah selesai, update bagian "Bug & Catatan Teknis" di docs/siakanuda-v1.2.3.md jika perlu.
```

---

## ✨ TEMPLATE 3 — Tambah Fitur Baru

```
Kamu adalah AI engineer yang membantu mengembangkan proyek SIAKANUDA.

Baca dulu: docs/siakanuda-v1.2.3.md

FITUR YANG INGIN DITAMBAHKAN:
[Deskripsikan fitur baru]

KONTEKS TAMBAHAN:
- Siapa yang akan menggunakan fitur ini: [Guru / Siswa / Admin]
- Diakses dari halaman mana di CI4 dashboard (Port 8080)
- Data apa yang perlu disimpan di SQLite / Supabase

Buatkan implementation plan dulu sebelum menulis kode.
Setelah selesai, update docs/siakanuda-v1.2.3.md di bagian yang relevan.
```

---

## 🎨 TEMPLATE 4 — Update Web Dashboard (CodeIgniter 4)

```
Kamu adalah AI engineer untuk proyek SIAKANUDA.

Baca dulu: docs/siakanuda-v1.2.3.md

File dashboard berada di folder:
- dashboard/app/Controllers/ (Controller logic PHP)
- dashboard/app/Views/ (Bootstrap HTML/PHP views)
- dashboard/app/Models/ (SQLite database queries)

PERUBAHAN YANG DIINGINKAN:
[Deskripsikan perubahan UI/fitur dashboard]

Teknologi: PHP CodeIgniter 4 + Bootstrap 5.
Pastikan tampilan responsif di browser mobile (skala HP) karena akan diakses melalui APK WebView.
```

---

## 🚢 TEMPLATE 5 — Persiapan Deploy ke Debian

```
Kamu adalah AI engineer untuk proyek SIAKANUDA.

Baca dulu: docs/debian_setup_guide.md

Saya siap mendeploy ke Lenovo Debian. Bantu saya:
1. Cek file bot.siswa.service dan siakadash.service apakah sudah terkonfigurasi dengan benar.
2. Berikan panduan langkah-demi-langkah transfer file via SCP dan SSH untuk sesi deploy ini.
3. Jalankan script deploy / status jika diperlukan.

IP Lenovo Debian: [ISI IP ADDRESS]
Username SSH: smknuda
```
