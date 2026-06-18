# 📋 Template Prompt Universal — SIAKANUDA
> Copy-paste prompt di bawah ini ke AI manapun (Gemini, Claude, GPT, Copilot, dll) untuk melanjutkan pengembangan SIAKANUDA.
> Template ini **tidak terikat versi** — AI akan membaca versi aktif sendiri dari file proyek.

---

## 🟢 PROMPT STANDAR (Copy mulai dari sini)

```
Kita melanjutkan pengembangan proyek SIAKANUDA (Sistem Informasi Akademik SMK NU Darussalam).

Sebelum melakukan modifikasi kode atau analisis apa pun, baca terlebih dahulu file penting berikut secara BERURUTAN untuk memahami arsitektur saat ini:
1. AI_CONTEXT.md             ← Peta folder, tech stack, versi aktif
2. AGENTS.md                 ← Aturan wajib untuk AI agent
3. README.md                 ← Changelog & riwayat versi

Aturan penting yang wajib kamu ikuti di sesi ini:
- Working Directory Utama: F:\Antigravity\siakanuda
- Port Utama/Dashboard: CodeIgniter 4 (Port 8080, bound ke `0.0.0.0` untuk akses Wi-Fi/LAN smartphone).
- Background API: Node.js (Port 7860) untuk notifikasi WA (Baileys), Cron, & PDF Generator.
- JANGAN membuat folder versi baru.
- Setelah selesai mengubah kode, WAJIB commit git dengan format: feat(vX.Y.Z): [deskripsi] atau fix(vX.Y.Z): [deskripsi].
- Update AI_CONTEXT.md (Log Perubahan Terbaru) jika ada perubahan fitur/desain/skema.
- Format Laporan WA & Form Detail PKL (Sakit, Izin, Alpha) versi v1.6.7 harus tetap terjaga konsistensinya.

Tugas kita sekarang adalah:
[TULIS TUGAS DI SINI]
```

---

## 🟡 PROMPT SINGKAT (Untuk tugas kecil/cepat)

```
Lanjutkan proyek SIAKANUDA. Baca AI_CONTEXT.md dan AGENTS.md dulu.
Working dir: F:\Antigravity\siakanuda
Wajib commit git setelah selesai.

Tugas: [TULIS TUGAS DI SINI]
```

---

## 🔵 PROMPT REVIEW / LAPORAN (Tanpa edit kode)

```
Lanjutkan proyek SIAKANUDA. Baca AI_CONTEXT.md, AGENTS.md, dan README.md.
Working dir: F:\Antigravity\siakanuda

Jangan edit kode apapun. Saya hanya butuh:
- Rangkuman status proyek saat ini (versi, fitur, git log)
- Saran langkah pengembangan selanjutnya
```

---

## 🟠 PROMPT SETELAH GANTI MODEL AI

```
Saya baru ganti model AI. Lanjutkan proyek SIAKANUDA.
Baca AI_CONTEXT.md, AGENTS.md, dan README.md.
Working dir: F:\Antigravity\siakanuda

Tugas:
1. Rangkum status proyek ini sampai di mana
2. Commit perubahan yang belum ter-commit (jika ada)
3. Beri saya laporan lengkap
```

---

## 🔴 PROMPT DEPLOYMENT / PRODUCTION

```
Lanjutkan proyek SIAKANUDA. Baca AI_CONTEXT.md dan AGENTS.md.
Working dir: F:\Antigravity\siakanuda

Tugas: Siapkan deployment ke server Debian production.
- Pastikan semua perubahan sudah di-commit
- Update bot.siswa.service dan siakadash.service jika perlu
- Buat checklist deployment
```

---

## 💡 Tips Penggunaan

1. **Selalu sertakan "Baca AI_CONTEXT.md"** — ini memaksa AI membaca peta proyek dulu sebelum bertindak.
2. **Selalu sertakan "Wajib commit git"** — ini memastikan semua perubahan terlacak.
3. **Tidak perlu sebutkan versi** — AI akan mendeteksi sendiri dari file.
4. **Tugas boleh bahasa Indonesia atau Inggris** — AI akan menyesuaikan.
5. **Satu sesi = satu tugas fokus** — lebih baik beri 1-3 tugas spesifik daripada 10 tugas sekaligus.
