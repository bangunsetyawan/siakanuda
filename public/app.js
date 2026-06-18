/**
 * SIAKANUDA — WhatsApp Bot Panel (app.js)
 * Lightweight frontend for managing WhatsApp bot connection, 
 * sending messages, viewing logs, and triggering cron jobs.
 * Port 7860 — Background Service Only.
 */

const API = window.location.origin;
const socket = io(API);

// ─── DOM Elements ──────────────────────────────────────────────────────────
const $  = id => document.getElementById(id);
const statusPill   = $('wa-status-pill');
const statusText   = $('wa-status-text');
const connBadge    = $('conn-badge');
const qrContainer  = $('qr-container');
const infoPhone    = $('info-phone');
const infoName     = $('info-name');
const infoVersion  = $('info-version');
const formSend     = $('form-send');
const sendResult   = $('send-result');
const btnSend      = $('btn-send');
const cronResult   = $('cron-result');
const tbodyLogs    = $('tbody-logs');
const btnRefresh   = $('btn-refresh-logs');
const linkDash     = $('link-dashboard');
const footerLink   = $('footer-link');

// ─── Auto-detect dashboard URL ─────────────────────────────────────────────
(function setDashboardLink() {
  const host = window.location.hostname;
  const dashUrl = `http://${host}:8080`;
  linkDash.href = dashUrl;
  footerLink.href = dashUrl;
})();

// ─── Status & Connection ────────────────────────────────────────────────────

function setOnline(phone, name) {
  statusPill.classList.add('online');
  statusPill.classList.remove('offline');
  statusText.textContent = 'Terhubung';
  connBadge.textContent = 'Online';
  connBadge.classList.add('online');
  connBadge.classList.remove('offline');
  if (phone) infoPhone.textContent = phone;
  if (name)  infoName.textContent = name;
  qrContainer.innerHTML = `
    <div class="qr-connected">
      <i class="fa-solid fa-circle-check"></i>
      <p>WhatsApp Terhubung</p>
    </div>`;
}

function setOffline() {
  statusPill.classList.add('offline');
  statusPill.classList.remove('online');
  statusText.textContent = 'Tidak terhubung';
  connBadge.textContent = 'Offline';
  connBadge.classList.add('offline');
  connBadge.classList.remove('online');
  infoPhone.textContent = '—';
  infoName.textContent = '—';
}

async function fetchStatus() {
  try {
    const res = await fetch(`${API}/api/status`);
    const data = await res.json();
    infoVersion.textContent = data.version || '—';
    if (data.connected) {
      setOnline(data.phone, data.name);
    } else {
      setOffline();
    }
  } catch {
    setOffline();
  }
}

// ─── Socket.io Events ──────────────────────────────────────────────────────

socket.on('qr', (qrDataUrl) => {
  qrContainer.innerHTML = `
    <img src="${qrDataUrl}" alt="QR Code WhatsApp" class="qr-img">
    <p class="qr-hint">Scan dengan WhatsApp → Perangkat Tertaut → Tautkan Perangkat</p>`;
  setOffline();
});

socket.on('ready', (data) => {
  setOnline(data?.phone, data?.name);
});

socket.on('disconnected', () => {
  setOffline();
  qrContainer.innerHTML = `
    <div class="qr-placeholder">
      <i class="fa-solid fa-link-slash fa-2x"></i>
      <p>Koneksi terputus. Menunggu reconnect...</p>
    </div>`;
});

socket.on('new-log', () => {
  fetchLogs();
});

// ─── Send Message ──────────────────────────────────────────────────────────

formSend.addEventListener('submit', async (e) => {
  e.preventDefault();
  const phone = $('send-phone').value.trim();
  const message = $('send-message').value.trim();
  if (!phone || !message) return;

  btnSend.disabled = true;
  btnSend.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim...';
  sendResult.textContent = '';

  try {
    const res = await fetch(`${API}/api/send`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ phone: `${phone}@s.whatsapp.net`, message })
    });
    const data = await res.json();
    if (data.ok || data.success) {
      sendResult.className = 'send-result success';
      sendResult.textContent = '✅ Pesan berhasil dikirim!';
      $('send-message').value = '';
    } else {
      throw new Error(data.error || 'Gagal mengirim');
    }
  } catch (err) {
    sendResult.className = 'send-result error';
    sendResult.textContent = `❌ ${err.message}`;
  } finally {
    btnSend.disabled = false;
    btnSend.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Kirim';
    setTimeout(() => { sendResult.textContent = ''; }, 5000);
  }
});

// ─── Cron Jobs ─────────────────────────────────────────────────────────────

document.querySelectorAll('.cron-btn').forEach(btn => {
  btn.addEventListener('click', async () => {
    const jobId = btn.dataset.job;
    btn.disabled = true;
    const origHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menjalankan...';
    cronResult.textContent = '';

    try {
      const res = await fetch(`${API}/api/test/cron/${jobId}`, { method: 'POST' });
      const data = await res.json();
      if (data.success) {
        cronResult.className = 'cron-result success';
        cronResult.textContent = `✅ Cron job ${jobId} berhasil dijalankan.`;
      } else {
        throw new Error(data.error || 'Gagal');
      }
    } catch (err) {
      cronResult.className = 'cron-result error';
      cronResult.textContent = `❌ ${err.message}`;
    } finally {
      btn.disabled = false;
      btn.innerHTML = origHTML;
      setTimeout(() => { cronResult.textContent = ''; }, 5000);
    }
  });
});

// ─── Message Logs ──────────────────────────────────────────────────────────

async function fetchLogs() {
  try {
    const res = await fetch(`${API}/api/logs`);
    if (!res.ok) throw new Error('Unauthorized');
    const logs = await res.json();

    if (!logs.length) {
      tbodyLogs.innerHTML = '<tr><td colspan="4" class="empty">Belum ada riwayat pesan.</td></tr>';
      return;
    }

    tbodyLogs.innerHTML = logs.map(log => {
      const time = log.timestamp
        ? new Date(log.timestamp).toLocaleString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', day: '2-digit', month: 'short' })
        : '—';
      const dirIcon = log.direction === 'outgoing'
        ? '<span class="dir-out" title="Keluar"><i class="fa-solid fa-arrow-up-right-from-square"></i> Keluar</span>'
        : '<span class="dir-in" title="Masuk"><i class="fa-solid fa-arrow-down-to-line"></i> Masuk</span>';
      const phone = log.phone || log.sender || '—';
      const msg = (log.message || log.body || '—').substring(0, 120);
      return `<tr>
        <td class="td-time">${time}</td>
        <td class="td-phone">${phone}</td>
        <td>${dirIcon}</td>
        <td class="td-msg">${msg}</td>
      </tr>`;
    }).join('');
  } catch {
    tbodyLogs.innerHTML = '<tr><td colspan="4" class="empty">Gagal memuat log (mungkin perlu auth).</td></tr>';
  }
}

btnRefresh.addEventListener('click', fetchLogs);

// ─── Init ──────────────────────────────────────────────────────────────────

fetchStatus();
fetchLogs();
// Refresh status setiap 30 detik
setInterval(fetchStatus, 30000);
