# 📋 Template Prompt — SIAKANUDA
> Copy-paste prompt di bawah ke AI manapun (Gemini, Claude, GPT, Copilot, Cursor, Windsurf, dll).
> Template ini **tidak terikat versi, model, atau komputer** — AI akan membaca versi aktif dari file proyek.
> ⚠️ Tidak perlu tulis path absolut — SSD portabel, drive letter bisa berubah.

---

## 🟢 PROMPT STANDAR (Fitur baru / revisi besar)

```
Lanjutkan proyek SIAKANUDA (Sistem Informasi Akademik SMK NU Darussalam).

Baca file berikut secara BERURUTAN sebelum mulai:
1. AGENTS.md        ← Aturan wajib AI agent
2. AI_CONTEXT.md    ← Arsitektur, tech stack, folder map
3. STATUS_FITUR.md  ← Status fitur & checklist
4. DEPLOY_LOG.md    ← Riwayat deploy ke server

Konteks penting:
- Proyek ini di SSD portabel (drive letter bisa berubah, gunakan path relatif).
- Server produksi: Debian di smknuda@100.110.83.48 (Tailscale) / 10.10.11.37 (LAN).
- Deploy menggunakan metode PATCH (tar.gz file yang berubah saja), BUKAN full deploy.
- Jangan edit langsung di server. Semua perubahan dari lokal → patch → upload.
- Git commit dulu sebelum membuat patch.

Tugas:
[TULIS TUGAS DI SINI]
```

---

## 🟡 PROMPT SINGKAT (Fix kecil / tweak cepat)

```
Lanjutkan SIAKANUDA. Baca AGENTS.md dan AI_CONTEXT.md dulu.

Aturan:
- Edit di lokal saja, jangan edit di server.
- Git commit setelah selesai.
- Siapkan patch .tar.gz jika perlu deploy.

Tugas: [TULIS TUGAS DI SINI]
```

---

## 🔵 PROMPT REVIEW (Tanpa edit kode)

```
Lanjutkan SIAKANUDA. Baca AI_CONTEXT.md, STATUS_FITUR.md, dan DEPLOY_LOG.md.
Jangan edit kode. Saya butuh:
- Rangkuman status proyek (versi, fitur, git log terakhir)
- Status server produksi (versi terdeploy vs versi lokal)
- Saran pengembangan selanjutnya
```

---

## 🟠 PROMPT PINDAH AI / PINDAH KOMPUTER

```
Saya baru pindah AI agent / komputer. Lanjutkan proyek SIAKANUDA.
Baca AGENTS.md, AI_CONTEXT.md, STATUS_FITUR.md, dan DEPLOY_LOG.md.

Tugas:
1. Identifikasi versi lokal (dari package.json + git log)
2. Bandingkan dengan versi terdeploy (dari DEPLOY_LOG.md)
3. Cek apakah ada perubahan belum di-commit (git status)
4. Beri saya laporan lengkap dan rekomendasi
```

---

## 🔴 PROMPT DEPLOYMENT (Patch ke Server)

```
Lanjutkan SIAKANUDA. Baca AGENTS.md, AI_CONTEXT.md, dan DEPLOY_LOG.md.

Tugas: Deploy perubahan terbaru ke server produksi Debian.
Gunakan metode PATCH:
1. Pastikan semua perubahan sudah di-commit (git status clean)
2. Buat patch .tar.gz HANYA berisi file yang berubah sejak deploy terakhir
3. Upload via SCP ke smknuda@100.110.83.48:~/
4. Beri saya command SSH untuk extract & restart service yang relevan
5. Update DEPLOY_LOG.md (lokal & server)

Aturan restart:
- Ubah file PHP (dashboard/) → restart siakadash.service
- Ubah file JS (execution/) → restart bot.siswa.service
- Ubah file statis (public/) → tidak perlu restart
- JANGAN npm install kecuali ada dependency baru di package.json
```

---

## 🟣 PROMPT CEK LOG SERVER

```
Lanjutkan SIAKANUDA. Baca AI_CONTEXT.md.
Saya sedang SSH ke server Debian (smknuda@100.110.83.48).

Bantu saya mengecek kondisi server:
1. Status 5 service (bot.siswa, siakadash, nginx, php-fpm, cloudflared)
2. Log error hari ini (journalctl + CI4 writable/logs)
3. Versi yang terdeploy (package.json)
4. Masalah yang perlu diperbaiki

Berikan command yang bisa saya copy-paste langsung ke SSH.
Saya tidak hafal perintah CLI — beri penjelasan sederhana untuk setiap output.
```

---

## 💡 Tips Universal

1. **Tidak perlu tulis path absolut** — AI akan menemukan root proyek sendiri dari lokasi file.
2. **Tidak perlu sebut versi** — AI mendeteksi dari `package.json` & git log.
3. **Satu sesi = satu tugas fokus** — beri 1–3 tugas spesifik, bukan 10 sekaligus.
4. **Bahasa bebas** — Indonesia atau Inggris, AI menyesuaikan.
5. **Sistem Anti-Lupa** — Git hook otomatis update STATUS_FITUR.md saat commit.
6. **Selalu commit sebelum cabut SSD** — hindari perubahan hilang saat pindah komputer.
7. **Deploy = Patch** — Jangan full deploy ulang, cukup kirim file yang berubah.
8. **Jangan edit di server** — Semua perubahan dari lokal, deploy via patch.

---

## 📂 File Referensi yang Harus Dibaca AI

| File | Fungsi | Kapan Baca |
|------|--------|------------|
| `AGENTS.md` | Aturan wajib AI agent | Selalu |
| `AI_CONTEXT.md` | Arsitektur, tech stack, folder map | Selalu |
| `STATUS_FITUR.md` | Checklist fitur & changelog | Saat develop fitur |
| `DEPLOY_LOG.md` | Riwayat deploy ke server | Saat deploy / review |
| `ROADMAP.md` | Rencana pengembangan | Saat butuh inspirasi fitur |
| `.env.example` | Template environment variables | Saat setup / debug |
| `docs/DEPLOY_NOTES.md` | Catatan teknis deploy | Saat troubleshoot server |
