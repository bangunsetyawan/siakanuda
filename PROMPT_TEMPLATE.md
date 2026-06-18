# 📋 Template Prompt — SIAKANUDA
> Copy-paste prompt di bawah ke AI manapun (Gemini, Claude, GPT, Copilot, dll).
> Template ini **tidak terikat versi** — AI akan membaca versi aktif dari file proyek.
> ⚠️ Tidak perlu tulis path absolut — SSD portabel, drive letter bisa berubah.

---

## 🟢 PROMPT STANDAR (Fitur baru / revisi besar)

```
Lanjutkan proyek SIAKANUDA (Sistem Informasi Akademik SMK NU Darussalam).

Baca file berikut secara BERURUTAN sebelum mulai:
1. AGENTS.md        ← Aturan wajib AI agent
2. AI_CONTEXT.md    ← Arsitektur, tech stack, folder map
3. STATUS_FITUR.md  ← Status fitur & checklist

Tugas:
[TULIS TUGAS DI SINI]
```

---

## 🟡 PROMPT SINGKAT (Fix kecil / tweak cepat)

```
Lanjutkan SIAKANUDA. Baca AGENTS.md dan AI_CONTEXT.md dulu.
Commit setelah selesai. Dokumentasi akan di-sync otomatis oleh sistem.

Tugas: [TULIS TUGAS DI SINI]
```

---

## 🔵 PROMPT REVIEW (Tanpa edit kode)

```
Lanjutkan SIAKANUDA. Baca AI_CONTEXT.md dan STATUS_FITUR.md.
Jangan edit kode. Saya butuh:
- Rangkuman status proyek (versi, fitur, git log)
- Saran pengembangan selanjutnya
```

---

## 🟠 PROMPT GANTI MODEL AI

```
Saya baru ganti model AI. Lanjutkan proyek SIAKANUDA.
Baca AGENTS.md, AI_CONTEXT.md, dan STATUS_FITUR.md.

Tugas:
1. Rangkum status proyek sampai di mana
2. Cek apakah ada perubahan belum di-commit
3. Beri saya laporan lengkap
```

---

## 🔴 PROMPT DEPLOYMENT

```
Lanjutkan SIAKANUDA. Baca AGENTS.md dan AI_CONTEXT.md.
Tugas: Siapkan deployment ke server Debian production.
- Pastikan semua perubahan sudah di-commit (dokumen akan auto-sync)
- Update bot.siswa.service dan siakadash.service jika perlu
- Buat checklist deployment
```

---

Commit + reorganisasi arsip + sinkronisasi dokumentasi
Git deteksi  file arsip sebagai rename (bukan delete+create), jadi riwayat git tetap terjaga
Sistem Anti-Lupa otomatis jalan dan meng-update STATUS_FITUR.md
File temp ~$ di-gitignore
Working tree wajib 100% bersih — siap untuk pengembangan selanjutnya

## 💡 Tips

1. **Tidak perlu tulis path** — AI akan menemukan root proyek sendiri dari lokasi file.
2. **Tidak perlu sebut versi** — AI mendeteksi dari `package.json` & git log.
3. **Satu sesi = satu tugas fokus** — beri 1–3 tugas spesifik, bukan 10 sekaligus.
4. **Bahasa bebas** — Indonesia atau Inggris, AI menyesuaikan.
5. **Sistem Anti-Lupa** — Tidak perlu menyuruh AI menjalankan script sinkronisasi, Git hook sudah mengurusnya otomatis saat commit.
