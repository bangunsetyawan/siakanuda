# 🧠 Directive: Panduan Penggunaan AI yang Efisien (v1.2.3)
> SOP untuk AI agent saat mendampingi user dalam sesi pengembangan atau pemecahan masalah.

---

## 🏗️ Strategi Model AI (Hybrid Model)

| Model AI | Rekomendasi Penggunaan | Mengapa? |
|---|---|---|
| **Antigravity (Gemini)** | Pembuatan fitur, debugging cepat, editing file langsung, eksekusi perintah shell lokal. | Memiliki akses langsung ke filesystem proyek dan interpreter. |
| **Claude Sonnet** | Refactoring arsitektur besar, desain database baru, logika rumit. | Memiliki tingkat penalaran (reasoning) yang kuat untuk struktur besar. |
| **Gemini Flash (Runtime)** | Asisten bot WhatsApp internal. | Ringan, cepat, dan memiliki kuota free tier yang melimpah. |

---

## ⚡ Protokol Efisiensi Token Sesi Baru
Setiap kali memulai sesi baru bersama AI, ikuti protokol berikut untuk menghemat kuota token:

1. **JANGAN melakukan scan folder secara menyeluruh** (`grep` tak terarah atau list folder berkali-kali).
2. **Rujuk langsung file blueprint utama**:
   ```
   docs/siakanuda-v1.2.3.md
   ```
   File ini sudah merangkum seluruh arsitektur, port, skema database, dan status fitur sehingga AI tidak perlu membaca puluhan file source code hanya untuk memahami konteks aplikasi.
3. **Sebutkan modul / file secara spesifik** (contoh: "Saya ingin mengubah validasi di `dashboard/app/Controllers/Attendance.php`").

---

## Checklist Sebelum Menutup Sesi Kerja
Pastikan AI menulis rangkuman sesi kerja di akhir pengerjaan dengan format:

```markdown
## Sesi [Tanggal]
### Yang Dikerjakan:
- [Fitur / bug yang diselesaikan]

### File yang Diubah:
- [Tulis path file lengkap dengan perubahan singkat]

### Status Fitur:
- [Nama fitur] (❌ → ✅)
```
Hal ini membantu AI pada sesi berikutnya untuk langsung mengetahui kemajuan terakhir tanpa perlu membaca histori chat panjang.
