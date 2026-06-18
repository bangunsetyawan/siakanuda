# 🗄️ Directive: Database — Panduan Pengembangan (v1.2.3)
> SOP untuk AI agent saat memodifikasi `execution/db.js` atau skema database (SQLite / Supabase).

---

## 🏗️ Aturan Utama `db.js`

### 1. Penanganan Dual-Mode (Supabase + SQLite Fallback)
Setiap fungsi database wajib mendukung sinkronisasi dua mode:
```javascript
if (useSupabase) {
  // Query ke Supabase Cloud (PostgreSQL)
  const { data, error } = await supabase.from('table').select('*')...;
  if (error) throw error;
  return data || [];
} 
// SQLite fallback jika koneksi internet terputus
return runAsync(() => db.prepare('SELECT * FROM table').all());
```
Jangan menulis fungsi database yang hanya mendukung salah satu mode saja.

### 2. Aturan Penamaan (Naming Convention)
* Nama Tabel: `snake_case` (contoh: `kelompok_pkl`, `allowed_numbers`).
* Nama Kolom: `snake_case` (contoh: `student_id`, `created_at`).
* Aksen khusus JavaScript: Hati-hati dengan nama parameter yang bertabrakan dengan reserved word JS (seperti kelas/`class`). Gunakan `studentClass` di JS, tapi simpan sebagai kolom `class` di database.

### 3. Kolom Tipe JSON
Beberapa kolom database menyimpan data terstruktur dalam format JSON string (untuk SQLite) atau JSONB (untuk Supabase):
* `attendance_pkl.attendance_data` → `{"Nama": "hadir/sakit/izin/alpha"}`
* `attendance_pkl.photo_url` → `["url1", "url2", ...]`
* `attendance_pkl.jurnal_kegiatan` → `{"Nama": "kegiatan hari ini"}`

Lakukan `JSON.parse(kolom || '{}')` saat membaca dan `JSON.stringify(objek)` saat menyimpan.

### 4. Prosedur Penambahan Tabel Baru
Jika ada penambahan tabel atau kolom baru di database, lakukan sinkronisasi di:
1. **SQLite (Lokal)** — Tambahkan DDL di fungsi `initSchema()` di file `db.js`.
2. **Supabase (Cloud)** — Jalankan SQL query di SQL Editor dashboard Supabase.
3. **Dokumentasi** — Update bagian skema database pada file **`docs/siakanuda-v1.2.3.md`** dan **`AI_CONTEXT.md`**.

---

## Checklist Setelah Mengubah Database
- [ ] Fungsi baru mendukung dual-mode (Supabase + SQLite).
- [ ] Error handling query Supabase dicek secara manual (`if (error) throw error`).
- [ ] Inisialisasi schema SQLite di `initSchema()` sudah disesuaikan.
- [ ] Dokumentasi `docs/siakanuda-v1.2.3.md` sudah diperbarui.
