# Nerra.id Agentic Framework — SIAKANUDA
> Konfigurasi ini universal untuk semua model AI (AGENTS.md, CLAUDE.md, GEMINI.md).
> Berlaku untuk semua versi SIAKANUDA. Tidak perlu diubah saat naik versi.

Sebagai agen AI, Anda bertanggung jawab untuk mengarahkan pengembangan sistem secara aman dan andal menggunakan alur kerja yang terstruktur.

---

## ⚠️ ATURAN WAJIB SEBELUM MULAI KERJA

### Langkah 0: BACA DULU, BARU KERJA
Sebelum membuat, mengubah, atau menganalisis kode apa pun, Anda **WAJIB** membaca file-file berikut secara berurutan:

1. **`AI_CONTEXT.md`** — Single Source of Truth. Peta folder, tech stack, skema database, dan cara menjalankan. Baca ini agar Anda paham seluruh arsitektur dalam 30 detik.
2. **`docs/archive/siakanuda-v*.md`** — Arsip blueprint teknis per versi. Untuk referensi historis; gunakan `AI_CONTEXT.md` sebagai acuan utama.
3. **`STATUS_FITUR.md`** — Checklist fitur dan status pengerjaan per versi.

> 🚫 **JANGAN** langsung menulis kode tanpa membaca ketiga file di atas. Ini non-negotiable.

### Langkah 1: IDENTIFIKASI VERSI
Setelah membaca, identifikasi versi aktif dari field `Terakhir diupdate` di `AI_CONTEXT.md` atau entry terakhir di `README.md`. Sebutkan versi ini ke user sebelum mulai mengerjakan tugas.

---

## 📝 ATURAN GIT — WAJIB COMMIT SETIAP SELESAI

### Setelah selesai mengubah kode, Anda WAJIB:

1. **`git add`** hanya file yang Anda ubah (jangan `git add .` untuk menghindari file debugbar/cache ikut masuk).
2. **`git commit`** dengan format pesan:
   ```
   feat(vX.Y.Z): [ringkasan perubahan dalam 1 baris]
   
   - [file1]: [apa yang berubah]
   - [file2]: [apa yang berubah]
   ```
3. **Update dokumentasi** jika perubahan signifikan:
   - `AI_CONTEXT.md` → update tanggal dan deskripsi versi
   - `STATUS_FITUR.md` → update checklist fitur dan status pengerjaan
   - `docs/archive/siakanuda-v*.md` → update jika ada perubahan skema DB, arsitektur, atau env

### Sistem "Anti-Lupa" (Otomatisasi Dokumentasi)
Sistem SIAKANUDA menggunakan **Git Post-Commit Hook** untuk memastikan AI/Developer tidak akan pernah lupa mengupdate dokumentasi.
Setiap kali kamu melakukan `git commit`, sistem secara **otomatis** di latar belakang akan:
1. Menjalankan `node execution/update-docs.js`
2. Mengupdate statistik di `STATUS_FITUR.md` dan `AI_CONTEXT.md`
3. Menambahkan (amend) perubahan tersebut ke dalam commit kamu secara transparan.

> ✅ **TUGAS AI**: Kamu cukup fokus melakukan `git commit`. Tidak perlu lagi memanggil script update-docs secara manual. Jika ada penambahan fitur besar, cukup centang checklist manual di `STATUS_FITUR.md` sebelum commit.

### Konvensi Versi
- **Patch** (x.y.**Z**+1): Fix bug, tweak UI kecil, perbaikan typo
- **Minor** (x.**Y**+1.0): Fitur baru, modul baru, perubahan menu
- **Major** (**X**+1.0.0): Overhaul arsitektur, migrasi platform

---

## 🏗️ Alur Kerja 3-Tier

1. **Tier 1: Blueprint (Directives)**
   - Berada di folder `directives/` dalam format Markdown.
   - Ini adalah standar operasional prosedur (SOP) pengerjaan fitur. Rujuk file-file ini sebelum menyentuh file kode.

2. **Tier 2: Otak (Orchestration)**
   - Peran utama Anda: merancang alur kerja, mendeteksi error, mengoreksi diri, dan memperbarui blueprint berdasarkan temuan terbaru.

3. **Tier 3: Otot (Execution)**
   - Menjalankan perintah, memodifikasi kode, mengelola file database, dan menjalankan script di folder `execution/`.
   - Pastikan script berjalan cepat, handal, dan kredensial aman di `.env`.

---

## 🚨 Aturan Proyek SIAKANUDA (Universal — Semua Versi)

### 1. Pintu Masuk Tunggal (Port 8080)
Seluruh antarmuka pengguna berada di **CodeIgniter 4 (Port 8080)**. Semua perubahan UI/UX, formulir CRUD, login, absensi, pelanggaran, dan PKL harus dibuat di modul dashboard CI4. WhatsApp Bot hanya bertindak di latar belakang (Port 7860) untuk notifikasi/broadcast API.

### 2. Working Directory
```
siakanuda/          ← root proyek, SSD portabel (Node.js + SQLite)
siakanuda/dashboard  ← CodeIgniter 4
```
> ⚠️ Drive letter SSD portabel bisa berubah (E:\, F:\, G:\). Gunakan path relatif.
**JANGAN** membuat folder versi baru (seperti `siakanuda-v1.2.0/` atau `siakanuda-v2/`). Semua kode tetap di folder yang sama.

### 3. Lingkungan Lokal & Deployment
* **Development (Lokal):** Dijalankan pada Windows PC menggunakan SSD Portabel (`E:\Antigravity\...`, drive letter bisa berubah).
  - API & Bot WA (Node.js): `npm run dev` di folder `siakanuda/` (Port 7860).
  - Dashboard (CI4): `php spark serve` di folder `siakanuda/dashboard/` (Port 8080).
* **Production (Debian):** Lenovo Notebook Debian 13 Headless dengan SSD Internal (128GB).
  - **Metode Deployment:** Dideploy melalui jaringan (SSH) dengan membuat file arsip `.tar.gz` dari folder lokal, lalu diekstrak di server Debian. Bukan dengan mencolokkan SSD portabel secara fisik.
  - **Web Server:** Nginx (port 8080) + PHP 8.4 FPM (pm.max_children=12). **JANGAN** gunakan `php spark serve` di production.
  - Service `bot.siswa.service` → Node.js background API di `~/siakanuda` (Port 7860)
  - Cloudflare Tunnel (`cloudflared`) → reverse proxy ke Nginx
  - Domain: `https://siakanuda.qzz.io`

### 4. Eksekusi Command & Keamanan
* **JANGAN** jalankan `npm install` atau `composer install` tanpa konfirmasi user.
* File `.env` **TIDAK BOLEH** di-commit ke Git atau dibagikan ke publik.
* Semua file backend Node.js menggunakan format ES Modules (ESM) dengan `import` / `export`.

### 5. Update Blueprint Secara Otomatis
Setiap perubahan signifikan pada skema database, variabel `.env`, atau API endpoints harus segera diupdate pada file blueprint di `docs/` agar dokumentasi tetap mutakhir.