# 🚀 Walkthrough Deploy v1.11.1 — 18 Juni 2026

> **Tujuan**: Sinkronisasi versi, deploy ke production Debian, dan fix semua bug yang ditemukan saat deploy.
> **Durasi**: ~1 jam (20:08 – 21:06 WIB)
> **Hasil**: ✅ Website live, bot WA terhubung, semua bug fixed.

---

## 📋 Daftar Perubahan (3 Commit Baru)

| # | Commit | Deskripsi |
|---|--------|-----------|
| 1 | `810c947` | **Sinkronisasi versi & dokumentasi** + perbaikan akses role guru |
| 2 | `05c5493` | **Fix Supabase auth state** (createClient) & banner versi dinamis |
| 3 | `8bc05fc` | **Fix Socket.io frontend** auto-detect origin untuk production Nginx proxy |

---

## 🐛 Bug & Error yang Ditemukan + Solusi

### Bug 1: Inkonsistensi Versi di Seluruh Codebase

> [!CAUTION]
> Versi tersebar di banyak file dan sering tertinggal saat naik versi.

| File | Versi Lama | Versi Fix |
|------|-----------|-----------|
| [package.json](file:///F:/Antigravity/siakanuda/package.json) | `1.10.4` + duplikat `description` | `1.11.1` + field bersih |
| [server.js](file:///F:/Antigravity/siakanuda/execution/server.js) header | `v1.10.0` | `v1.11.1` |
| [server.js](file:///F:/Antigravity/siakanuda/execution/server.js) `/api/status` | `1.11.0` | `1.11.1` |
| [AI_CONTEXT.md](file:///F:/Antigravity/siakanuda/AI_CONTEXT.md) | v1.9.0 (11 Jun) | v1.11.1 (18 Jun) |
| [ROADMAP.md](file:///F:/Antigravity/siakanuda/ROADMAP.md) | v1.9.0 (9 Jun) | v1.11.1 (18 Jun) |
| `.env` komentar | v1.5.2 | v1.11.1 |
| **Git tag** | ❌ tidak ada v1.11.1 | ✅ `v1.11.1` dibuat |

**Pencegahan**: Setelah naik versi, selalu cek semua file di atas. Atau buat script `update-version.js` yang update semua sekaligus.

---

### Bug 2: `supabase.from is not a function`

> [!WARNING]
> Error ini menyebabkan bot WA gagal connect di production saat menggunakan Supabase Auth State.

**File**: [bot.js](file:///F:/Antigravity/siakanuda/execution/bot.js#L68-L80)

**Penyebab**: `useSupabaseAuthState()` di [supabase-auth.js](file:///F:/Antigravity/siakanuda/execution/supabase-auth.js#L36) mengharapkan **Supabase client object**, tapi `bot.js` mengirim **raw URL string**.

**Kode Lama** (SALAH):
```javascript
authState = await useSupabaseAuthState(process.env.SUPABASE_URL, process.env.SUPABASE_KEY);
```

**Kode Fix** (BENAR):
```javascript
const { createClient } = await import('@supabase/supabase-js');
const supabase = createClient(process.env.SUPABASE_URL, process.env.SUPABASE_KEY);
authState = await useSupabaseAuthState(supabase);
```

**Catatan**: Bug ini kemungkinan ada sejak lama tapi tidak pernah terdeteksi karena production selalu fallback ke `initAuthCreds()` (membuat credentials baru) saat error, lalu menampilkan QR code. Masalah baru muncul saat session expired (401) dan bot tidak bisa reconnect.

---

### Bug 3: Banner Startup Hardcoded `v1.4.6`

**File**: [server.js](file:///F:/Antigravity/siakanuda/execution/server.js#L1453-L1454)

**Penyebab**: String versi di banner startup hardcoded sejak versi awal, tidak pernah diperbarui.

**Kode Lama**:
```javascript
console.log(`\n🏫 SIAKANUDA v1.4.6 — ${SCHOOL_NAME}`);
```

**Kode Fix** (dinamis dari package.json):
```javascript
const pkg = JSON.parse(fs.readFileSync(path.join(ROOT, 'package.json'), 'utf8'));
console.log(`\n🏫 SIAKANUDA v${pkg.version} — ${SCHOOL_NAME}`);
```

> [!NOTE]
> Juga perlu menambahkan `import fs from 'fs'` di bagian atas server.js karena belum ada.

---

### Bug 4: Socket.io & API Frontend Hardcoded ke `127.0.0.1:7860`

> [!CAUTION]
> Bug KRITIS — menyebabkan QR code WA tidak muncul saat dashboard diakses via internet (Cloudflare Tunnel).

**File**: [whatsapp_settings/index.php](file:///F:/Antigravity/siakanuda/dashboard/app/Views/whatsapp_settings/index.php#L679-L700)

**Penyebab**: Halaman Pengaturan WA memuat `socket.io.js` dan menghubungkan Socket.io langsung ke `http://127.0.0.1:7860`. Ini hanya bisa diakses dari localhost, TIDAK bisa dari browser via internet.

**Error di Console Chrome**:
```
Failed to load resource: net::ERR_CONNECTION_REFUSED    socket.io.js:1
[SOCKET] Connecting to WA Bot background service at: http://127.0.0.1:7860
Uncaught ReferenceError: io is not defined
```

**Kode Lama** (SALAH):
```html
<script src="<?= $waBotUrl ?>/socket.io/socket.io.js"></script>
<!-- waBotUrl = http://127.0.0.1:7860 — tidak bisa dijangkau browser via internet -->
```

**Kode Fix** (auto-detect):
```html
<script src="/socket.io/socket.io.js"></script>
<script>
    const isLocal = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    const waBotUrl = isLocal ? "<?= $waBotUrl ?>" : window.location.origin;
</script>
```

**Cara kerjanya**:
- **Localhost** (`localhost:8080`): connect langsung ke `http://127.0.0.1:7860` ✅
- **Production** (`siakanuda.qzz.io`): connect ke origin sendiri → Nginx proxy `/socket.io/` dan `/api/` ke Node.js port 7860 ✅

---

## 🔧 Masalah Deployment yang Ditemukan

### Masalah 5: `USE_LOCAL_AUTH=true` di Server Production

**Penyebab**: Deploy lama (arsip tar.gz versi sebelumnya) menyertakan file `.env` yang sudah mengandung `USE_LOCAL_AUTH=true`. Seharusnya `.env` TIDAK ikut di-deploy.

**Dampak**: Bot WA di server mencoba pakai Supabase auth state → session expired (401) → QR tidak muncul.

**Keputusan Arsitektur (18 Juni 2026)**:
> Supabase Auth State untuk WA session **TIDAK DIGUNAKAN** lagi. Kedua environment (localhost & production) menggunakan **Local File Auth State** (`sessions/` folder). Tidak ada conflict karena mesin berbeda, folder berbeda.

> Supabase tetap dipakai **HANYA** untuk sync foto PKL ke cloud storage.

**Status `.env` yang benar**:

| Environment | `USE_LOCAL_AUTH` | Alasan |
|-------------|-----------------|--------|
| **Localhost** (Windows) | `true` | Pakai sessions/ lokal |
| **Production** (Debian) | `true` | Pakai sessions/ lokal |

---

### Masalah 6: Cloudflare Worker `pengaman-siakanuda` Memblokir Akses

**Penyebab**: Saat pemadaman PLN (15 Juni), sebuah Cloudflare Worker dengan route `siakanuda.qzz.io/*` di-deploy untuk menampilkan halaman "Sistem Sedang Offline". Setelah server kembali online, route Worker ini **tidak dihapus**.

**Dampak**: Semua request ke `siakanuda.qzz.io` diinterupsi oleh Worker dan selalu menampilkan halaman offline, meskipun server sudah hidup.

**Solusi**: Hapus **route** `siakanuda.qzz.io/*` dari Worker di Cloudflare Dashboard → Workers & Pages → `pengaman-siakanuda` → Routes → Delete.

> [!IMPORTANT]
> **Checklist Setelah Server Kembali Online dari Pemadaman:**
> 1. ✅ Nyalakan server & pastikan service running
> 2. ✅ Cek Cloudflare Workers — **hapus route darurat** jika aktif
> 3. ✅ Cek Cloudflare DNS — pastikan CNAME mengarah ke tunnel
> 4. ✅ Purge Cloudflare cache
> 5. ✅ Scan QR WhatsApp dari dashboard

---

### Masalah 7: `siakadash.service` Disabled (Tidak Auto-Start)

**Ditemukan**: `systemctl status` menunjukkan `siakadash.service` berstatus `disabled`.

**Dampak**: Jika server reboot, dashboard PHP tidak akan otomatis jalan — harus manual start.

**Fix**: 
```bash
sudo systemctl enable siakadash.service
```

---

### Masalah 8: Arsip Deploy Lama Menyertakan File Sensitif

**Ditemukan**: File `update.tar.gz` versi lama (53.5 MB) mengandung `node_modules/`, `.env`, `siakanuda.db`, dan `sessions_corrupt/`.

**Risiko**: 
- `.env` production bisa tertimpa oleh `.env` localhost
- Database production bisa tertimpa
- Ukuran arsip membengkak

**Perintah arsip yang benar** (exclude file sensitif):
```bash
tar -czf update.tar.gz \
  --exclude=".git" \
  --exclude="node_modules" \
  --exclude="sessions" \
  --exclude="sessions_corrupt" \
  --exclude="backups" \
  --exclude=".env" \
  --exclude="siakanuda.db" \
  --exclude="uploads/pkl" \
  --exclude=".tmp" \
  --exclude="scratch" \
  --exclude="siakanuda-apk" \
  --exclude="test-results" \
  --exclude="playwright-report" \
  -C "F:\Antigravity" siakanuda
```

> [!TIP]
> Arsip bersih = **~5.4 MB** vs arsip kotor = **~53 MB**

---

## 📝 Prosedur Deploy Standar (SOP)

Untuk menghindari masalah di atas terulang, berikut SOP deploy ke production:

### Dari PC Lokal (Windows):
```powershell
# 1. Buat arsip (TANPA file sensitif)
tar -czf F:\Antigravity\update.tar.gz --exclude=".git" --exclude="node_modules" --exclude="sessions" --exclude="sessions_corrupt" --exclude="backups" --exclude=".env" --exclude="siakanuda.db" --exclude="uploads/pkl" --exclude=".tmp" --exclude="scratch" --exclude="siakanuda-apk" --exclude="test-results" --exclude="playwright-report" -C "F:\Antigravity" siakanuda

# 2. Upload ke server
scp F:\Antigravity\update.tar.gz [SSH_USER]@[IP_SERVER_TAILSCALE]:~/
```

### Di SSH (Debian):
```bash
# 3. Stop, extract, restart
sudo systemctl stop bot.siswa.service siakadash.service
tar -xzf ~/update.tar.gz -C ~/ --overwrite
cd ~/siakanuda && npm install --omit=dev
sudo systemctl start bot.siswa.service siakadash.service

# 4. Verifikasi
sudo journalctl -u bot.siswa.service --no-pager -n 10 --since "10 sec ago"
```

### Post-Deploy Checklist:
- [ ] Banner menampilkan versi yang benar
- [ ] `/api/status` menunjukkan versi yang benar
- [ ] Dashboard bisa diakses via `siakanuda.qzz.io`
- [ ] QR code muncul di Pengaturan WA (jika bot belum connect)
- [ ] Bot WA terhubung setelah scan QR

---

## 📊 Status Akhir

| Komponen | Status |
|----------|--------|
| **Versi** | v1.11.1 ✅ |
| **Git tag** | v1.11.1 ✅ |
| **package.json** | 1.11.1 ✅ |
| **Working tree** | Clean ✅ |
| **Website** | Live di siakanuda.qzz.io ✅ |
| **Bot WA** | Terhubung (Local Auth State) ✅ |
| **Dashboard service** | Running + enabled ✅ |
| **Total commit** | 189 (186 + 3 baru) |
