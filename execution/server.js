/**
 * execution/server.js
 * SIAKANUDA v1.11.1 - Sistem Informasi Akademik SMK NU Darussalam
 * Tier 3 — Express web server + Socket.io + WhatsApp bot entry point.
 *
 * Port 7860 default untuk background API (WA Bot, PDF, Cron, Sync).
 * Endpoint /ping = keep-alive health check.
 */

import 'dotenv/config';
import fs from 'fs';
import bcrypt from 'bcrypt';
import { generateToken, requireAuth } from './middleware/auth.js';
import { onlyAdmin, onlySuperAdmin, onlyStaff, onlyBK } from './middleware/roles.js';
import express from 'express';
import { createServer } from 'http';
import { Server } from 'socket.io';
import { fileURLToPath } from 'url';
import path from 'path';
import cors from 'cors';
import { 
  initSchema, getDashboardStats, getAllStudents, getRecentLogs,
  getAttendanceToday, getRecentCounseling, getTopViolators,
  getAllowedNumbers, addAllowedNumber, removeAllowedNumber,
  getAttendanceByDate, getStudentsByClass,
  addStudent, deleteStudent, updateStudent,
  updateAllowedNumber, getRecentViolations,
  deleteAttendance, deleteCounseling, deleteViolation,
  getSchedules, addSchedule, deleteSchedule, updateSchedule,
  getFeedbacks, addFeedback, deleteFeedback, hideFeedback,
  getKelompokPkl, addKelompokPkl, deleteKelompokPkl, updateKelompokPkl,
  getAttendancePklByDate, getAttendancePklToday, getKelompokPklByKetua, getAttendancePklByKetuaAndDate,
  renderTemplate, getBotTemplates, updateBotTemplate, getCronConfigs, updateCronConfig, addCronConfig, deleteCronConfig
} from './db.js';
import { initAI } from './ai_processor.js';
import { startBot, getBotStatus, sendMessage, setSocketIO, logoutSession } from './bot.js';
import { startCronJobs, runClassAttendanceCheck, runPklReportCheck, runPklEscalationCheck, runAutoAlphaJob, loadAndScheduleCronJobs } from './cron_jobs.js';
import { syncLocalPhotosToSupabase } from './photo_sync.js';


const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT      = path.join(__dirname, '..');

const app    = express();
const server = createServer(app);
const io     = new Server(server, { cors: { origin: ['http://localhost:8080', 'http://127.0.0.1:8080'], credentials: true } });

const PORT        = parseInt(process.env.PORT || '7860', 10);
const SCHOOL_NAME = process.env.SCHOOL_NAME || 'SMK NU Darussalam';
const DASH_PASS   = process.env.DASHBOARD_PASSWORD || 'adminsmknuda';

// ─── Middleware ───────────────────────────────────────────────────────────────
app.use(cors({ origin: ['http://localhost:8080', 'http://127.0.0.1:8080'], credentials: true }));
app.use(express.json({ limit: '1mb' }));
app.use(express.static(path.join(ROOT, 'public')));
app.use('/uploads', express.static(path.join(ROOT, 'dashboard', 'public', 'uploads')));
app.get('/siakanuda', (_, res) => res.redirect('/'));

// ─── Keep-Alive & Status ──────────────────────────────────────────────────────

/** UptimeRobot akan ping endpoint ini setiap 5 menit agar server gratis tidak tidur */
app.get('/ping', (_, res) => res.status(200).send('PONG — SIAKANUDA aktif'));

app.get('/api/status', (_, res) => {
  res.json({ 
    ...getBotStatus(), 
    school: SCHOOL_NAME, 
    version: '1.11.1',
    broadcast_group_jid: (process.env.BROADCAST_GROUP_JID || '').trim(),
    school_group_jid: (process.env.SCHOOL_GROUP_JID || '').trim()
  });
});

// ─── Dashboard Stats API ──────────────────────────────────────────────────────

app.get('/api/stats', requireAuth, async (_, res) => {
  try { res.json(await getDashboardStats()); }
  catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

// ─── Students (Siswa) CRUD API ────────────────────────────────────────────────

app.get('/api/students', requireAuth, onlyStaff, async (req, res) => {
  try {
    const students = await getAllStudents();
    const q = req.query.search?.toLowerCase();
    const filtered = q
      ? students.filter(s => s.name.toLowerCase().includes(q) || s.class.toLowerCase().includes(q) || (s.nis && s.nis.includes(q)))
      : students;
    res.json(filtered);
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

app.post('/api/students', requireAuth, onlyAdmin, async (req, res) => {
  try {
    const { name, class: cls, nis, gender, phone } = req.body;
    if (!name || !cls) return res.status(400).json({ error: 'name dan class wajib diisi' });
    const student = await addStudent({ name, studentClass: cls, nis, gender, phone });
    res.json(student);
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

app.put('/api/students/:id', requireAuth, onlyAdmin, async (req, res) => {
  try {
    const { id } = req.params;
    const { name, class: cls, nis, gender, phone } = req.body;
    await updateStudent(id, { name, class: cls, nis, gender, phone });
    res.json({ ok: true });
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

app.delete('/api/students/:id', requireAuth, onlyAdmin, async (req, res) => {
  try {
    await deleteStudent(req.params.id);
    res.json({ ok: true });
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

app.get('/api/students/class/:cls', requireAuth, onlyStaff, async (req, res) => {
  try { res.json(await getStudentsByClass(decodeURIComponent(req.params.cls))); }
  catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

app.get('/api/students/:id/details', requireAuth, async (req, res) => {
  try {
    const studentId = req.params.id;
    const dbFuncs = await import('./db.js');
    const attendance = await dbFuncs.getAttendanceByStudent(studentId);
    const violations = await dbFuncs.getViolationsByStudent(studentId);
    const totalPoints = await dbFuncs.getTotalPoints(studentId);
    res.json({ attendance, violations, totalPoints });
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

// ─── Attendance (Absensi) CRUD API ────────────────────────────────────────────

app.get('/api/attendance/today', requireAuth, onlyStaff, async (_, res) => {
  try { res.json(await getAttendanceToday()); }
  catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

app.get('/api/attendance/date/:date', requireAuth, onlyStaff, async (req, res) => {
  try { res.json(await getAttendanceByDate(req.params.date)); }
  catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

app.delete('/api/attendance/:id', requireAuth, onlyAdmin, async (req, res) => {
  try {
    await deleteAttendance(req.params.id);
    res.json({ ok: true });
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

// ─── Counseling (BK) CRUD API ────────────────────────────────────────────────

app.get('/api/counseling', requireAuth, onlyBK, async (_, res) => {
  try { res.json(await getRecentCounseling(50)); }
  catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

app.delete('/api/counseling/:id', requireAuth, onlyAdmin, async (req, res) => {
  try {
    await deleteCounseling(req.params.id);
    res.json({ ok: true });
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

// ─── Violations (Pelanggaran) CRUD API ────────────────────────────────────────

app.get('/api/violations', requireAuth, onlyStaff, async (_, res) => {
  try { res.json(await getRecentViolations(50)); }
  catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

app.get('/api/violations/top', requireAuth, onlyStaff, async (req, res) => {
  try { res.json(await getTopViolators(parseInt(req.query.limit || '10', 10))); }
  catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

app.delete('/api/violations/:id', requireAuth, onlyAdmin, async (req, res) => {
  try {
    await deleteViolation(req.params.id);
    res.json({ ok: true });
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

// ─── Message Logs API ─────────────────────────────────────────────────────────

app.get('/api/logs', requireAuth, onlyAdmin, async (_, res) => {
  try { res.json(await getRecentLogs(50)); }
  catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

// ─── Allowed Numbers (Data Guru) CRUD API ─────────────────────────────────────

app.get('/api/allowed-numbers', requireAuth, onlyStaff, async (_, res) => {
  try { res.json(await getAllowedNumbers()); }
  catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

app.post('/api/allowed-numbers', requireAuth, onlyAdmin, async (req, res) => {
  try {
    const { phone, name, role } = req.body;
    if (!phone || !name) return res.status(400).json({ error: 'phone dan name wajib diisi' });
    await addAllowedNumber({ phone, name, role: role || 'guru_bk' });
    res.json({ ok: true });
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

app.put('/api/allowed-numbers/:oldPhone', requireAuth, onlyAdmin, async (req, res) => {
  try {
    const { oldPhone } = req.params;
    const { phone, name, role } = req.body;
    await updateAllowedNumber(oldPhone, { phone, name, role });
    res.json({ ok: true });
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

app.delete('/api/allowed-numbers/:phone', requireAuth, onlyAdmin, async (req, res) => {
  try {
    await removeAllowedNumber(req.params.phone);
    res.json({ ok: true });
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

// ─── Schedules API ────────────────────────────────────────────────────────────

app.get('/api/schedules', requireAuth, async (_, res) => {
  try { res.json(await getSchedules()); }
  catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

app.post('/api/schedules', requireAuth, onlyAdmin, async (req, res) => {
  try {
    const { day, className, subject, teacherPhone, startTime, endTime } = req.body;
    if (!day || !className || !subject || !teacherPhone) {
      return res.status(400).json({ error: 'day, className, subject, teacherPhone wajib diisi' });
    }
    const schedule = await addSchedule({ day, className, subject, teacherPhone, startTime, endTime });
    res.json(schedule);
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

app.delete('/api/schedules/:id', requireAuth, onlyAdmin, async (req, res) => {
  try {
    await deleteSchedule(req.params.id);
    res.json({ ok: true });
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

app.put('/api/schedules/:id', requireAuth, onlyAdmin, async (req, res) => {
  try {
    const { day, className, subject, teacherPhone, startTime, endTime } = req.body;
    await updateSchedule(req.params.id, { day, className, subject, teacherPhone, startTime, endTime });
    res.json({ ok: true });
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

// ─── Feedbacks (Kotak Suara) API ──────────────────────────────────────────────

app.get('/api/feedbacks', requireAuth, onlyStaff, async (_, res) => {
  try { res.json(await getFeedbacks()); }
  catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

app.post('/api/feedbacks', requireAuth, async (req, res) => {
  try {
    const { senderName, className, message, isPublic } = req.body;
    if (!senderName || !className || !message) {
      return res.status(400).json({ error: 'senderName, className, message wajib diisi' });
    }
    const feedback = await addFeedback({ senderName, className, message, isPublic });
    res.json(feedback);
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

app.put('/api/feedbacks/:id/hide', requireAuth, onlyAdmin, async (req, res) => {
  try {
    const { isPublic } = req.body;
    await hideFeedback(req.params.id, isPublic);
    res.json({ ok: true });
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

app.delete('/api/feedbacks/:id', requireAuth, onlyAdmin, async (req, res) => {
  try {
    await deleteFeedback(req.params.id);
    res.json({ ok: true });
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

// ─── Kelompok PKL API ─────────────────────────────────────────────────────────

app.get('/api/kelompok-pkl', requireAuth, async (_, res) => {
  try { res.json(await getKelompokPkl()); }
  catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

app.post('/api/kelompok-pkl', requireAuth, onlyAdmin, async (req, res) => {
  try {
    const { tempatPkl, ketuaPhone, anggota, pembimbingPhone } = req.body;
    if (!tempatPkl || !ketuaPhone || !anggota) {
      return res.status(400).json({ error: 'tempatPkl, ketuaPhone, anggota wajib diisi' });
    }
    const group = await addKelompokPkl({ tempatPkl, ketuaPhone, anggota, pembimbingPhone });
    res.json(group);
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

app.put('/api/kelompok-pkl/:id', requireAuth, onlyAdmin, async (req, res) => {
  try {
    const { tempatPkl, ketuaPhone, anggota, pembimbingPhone } = req.body;
    await updateKelompokPkl(req.params.id, { tempatPkl, ketuaPhone, anggota, pembimbingPhone });
    res.json({ ok: true });
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

app.delete('/api/kelompok-pkl/:id', requireAuth, onlyAdmin, async (req, res) => {
  try {
    await deleteKelompokPkl(req.params.id);
    res.json({ ok: true });
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

// ─── Attendance PKL (Jurnal) API ──────────────────────────────────────────────

/** GET /api/attendance-pkl?date=YYYY-MM-DD  (default: hari ini) */
app.get('/api/attendance-pkl', requireAuth, async (req, res) => {
  try {
    const date = req.query.date;
    const rows = date ? await getAttendancePklByDate(date) : await getAttendancePklToday();
    // Parse JSON fields sebelum kirim ke client
    const parsed = rows.map(r => ({
      ...r,
      photo_urls: (() => { try { return JSON.parse(r.photo_url || '[]'); } catch { return r.photo_url ? [r.photo_url] : []; } })(),
      jurnal: (() => { try { return JSON.parse(r.jurnal_kegiatan || '{}'); } catch { return { catatan: r.jurnal_kegiatan }; } })()
    }));
    res.json(parsed);
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

/** GET /api/attendance-pkl/history?limit=30 — riwayat terbaru */
app.get('/api/attendance-pkl/history', requireAuth, async (req, res) => {
  try {
    const limit = parseInt(req.query.limit || '30', 10);
    // Ambil semua tanggal unik 30 hari terakhir
    const { supabase, useSupabase, runAsync, getDb } = await import('./db.js');
    let rows;
    if (useSupabase) {
      const { data } = await supabase.from('attendance_pkl')
        .select('*')
        .order('date', { ascending: false })
        .limit(limit);
      rows = data || [];
    } else {
      rows = getDb().prepare('SELECT * FROM attendance_pkl ORDER BY date DESC LIMIT ?').all(limit);
    }
    const parsed = rows.map(r => ({
      ...r,
      photo_urls: (() => { try { return JSON.parse(r.photo_url || '[]'); } catch { return r.photo_url ? [r.photo_url] : []; } })(),
      jurnal: (() => { try { return JSON.parse(r.jurnal_kegiatan || '{}'); } catch { return { catatan: r.jurnal_kegiatan }; } })()
    }));
    res.json(parsed);
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});


/** GET /api/attendance-pkl/group-history/:ketuaPhone */
app.get('/api/attendance-pkl/group-history/:ketuaPhone', requireAuth, async (req, res) => {
  try {
    const { ketuaPhone } = req.params;
    const dbFuncs = await import('./db.js');
    const rows = await dbFuncs.getAttendancePklByKetua(ketuaPhone);
    const parsed = rows.map(r => ({
      ...r,
      photo_urls: (() => { try { return JSON.parse(r.photo_url || '[]'); } catch { return r.photo_url ? [r.photo_url] : []; } })(),
      jurnal: (() => { try { return JSON.parse(r.jurnal_kegiatan || '{}'); } catch { return { catatan: r.jurnal_kegiatan }; } })()
    }));
    res.json(parsed);
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});


// ─── Attendance PKL grouped by kelompok (for dashboard Laporan PKL) ──────────

/**
 * GET /api/attendance-pkl/by-group?date=YYYY-MM-DD
 * Mengembalikan laporan PKL dikelompokkan per tempat_pkl,
 * di-join dengan data kelompok_pkl (anggota, pembimbing).
 */
app.get('/api/attendance-pkl/by-group', requireAuth, async (req, res) => {
  try {
    const date = req.query.date || new Date().toISOString().split('T')[0];

    // Ambil semua kelompok PKL, laporan PKL, dan daftar guru secara paralel!
    const { supabase, useSupabase, getDb } = await import('./db.js');
    const [kelompok, pklRows, teachers] = await Promise.all([
      getKelompokPkl(),
      (async () => {
        if (useSupabase) {
          const { data } = await supabase.from('attendance_pkl').select('*').eq('date', date);
          return data || [];
        } else {
          return getDb().prepare('SELECT * FROM attendance_pkl WHERE date = ?').all(date);
        }
      })(),
      getAllowedNumbers()
    ]);

    // Map laporan ke kelompok
    const result = kelompok.map(k => {
      const laporanRow = pklRows.find(r => r.ketua_phone === k.ketua_phone) || null;

      let attendanceData = {};
      let jurnalKegiatan = {};
      let photoUrls = [];
      if (laporanRow) {
        try { attendanceData = typeof laporanRow.attendance_data === 'string' ? JSON.parse(laporanRow.attendance_data) : (laporanRow.attendance_data || {}); } catch (_) {}
        try { jurnalKegiatan = typeof laporanRow.jurnal_kegiatan === 'string' ? JSON.parse(laporanRow.jurnal_kegiatan) : (laporanRow.jurnal_kegiatan || {}); } catch (_) {}
        try { photoUrls = typeof laporanRow.photo_url === 'string' ? JSON.parse(laporanRow.photo_url) : (laporanRow.photo_url || []); } catch (_) {}
      }

      const pembimbing = teachers.find(t => t.phone === k.pembimbing_phone);
      const anggotaList = k.anggota ? k.anggota.split(',').map(a => a.trim()).filter(Boolean) : [];

      const hadir = anggotaList.filter(a => (attendanceData[a] || 'hadir') === 'hadir').length;

      return {
        id: k.id,
        tempat_pkl: k.tempat_pkl,
        ketua_phone: k.ketua_phone,
        anggota: anggotaList,
        pembimbing_phone: k.pembimbing_phone || null,
        pembimbing_nama: pembimbing?.name || null,
        tanggal: date,
        sudah_lapor: !!laporanRow,
        status_libur: laporanRow?.status_libur ? true : false,
        jumlah_hadir: hadir,
        jumlah_total: anggotaList.length,
        attendance_data: attendanceData,
        jurnal_kegiatan: jurnalKegiatan,
        photo_urls: photoUrls,
        created_at: laporanRow?.created_at || null,
      };
    });

    res.json(result);
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});



app.post('/api/pkl/broadcast', async (req, res) => {
  // Allow localhost only
  const ip = req.ip || req.connection.remoteAddress;
  if (!ip.includes('127.0.0.1') && !ip.includes('::1') && ip !== 'localhost') {
    return res.status(403).json({ error: 'Forbidden' });
  }

  try {
    const { date } = req.body;
    const ketuaPhone = req.body.ketuaPhone || req.body.ketua_phone;
    const isUpdate = req.body.is_update === true || req.body.is_update === 1 || req.body.is_update === 'true';
    if (!date || !ketuaPhone) {
      return res.status(400).json({ error: 'date dan ketuaPhone/ketua_phone wajib diisi' });
    }

    // 1. Fetch Kelompok PKL
    const kelompok = await getKelompokPklByKetua(ketuaPhone);
    if (!kelompok) {
      return res.status(404).json({ error: 'Kelompok PKL tidak ditemukan' });
    }

    // 2. Fetch Report
    const report = await getAttendancePklByKetuaAndDate(ketuaPhone, date);
    if (!report) {
      return res.status(404).json({ error: 'Laporan PKL tidak ditemukan untuk tanggal ' + date });
    }

    // Parse JSON fields
    let attendanceData = {};
    let jurnalKegiatan = {};
    let photoUrls = [];
    try { attendanceData = typeof report.attendance_data === 'string' ? JSON.parse(report.attendance_data) : (report.attendance_data || {}); } catch (_) {}
    try { jurnalKegiatan = typeof report.jurnal_kegiatan === 'string' ? JSON.parse(report.jurnal_kegiatan) : (report.jurnal_kegiatan || {}); } catch (_) {}
    try { photoUrls = typeof report.photo_url === 'string' ? JSON.parse(report.photo_url) : (report.photo_url || []); } catch (_) {}
    const urlsArray = Array.isArray(photoUrls) 
      ? photoUrls 
      : (typeof photoUrls === 'object' && photoUrls !== null 
          ? Object.values(photoUrls) 
          : [photoUrls].filter(Boolean));

    const members = kelompok.anggota ? kelompok.anggota.split(',').map(a => a.trim()).filter(Boolean) : [];
    
    // Fetch Ketua Name
    const students = await getAllStudents();
    const ketuaObj = students.find(s => s.phone === ketuaPhone || String(s.nis) === String(ketuaPhone)) || { name: 'Ketua Kelompok' };
    const senderName = ketuaObj.name;
    const ketuaClass = ketuaObj.class ? ` (${ketuaObj.class})` : '';
    const senderNameWithClass = `${senderName}${ketuaClass}`;

    // Fetch Pembimbing Name
    const allowed = await getAllowedNumbers();
    const pembimbingObj = allowed.find(g => g.phone === kelompok.pembimbing_phone);
    const pembimbingName = pembimbingObj ? pembimbingObj.name : 'Belum ditentukan';

    const dateOptions = { timeZone: 'Asia/Jakarta', weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const timeOptions = { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', hour12: false };
    const todayFormatted = new Date().toLocaleDateString('id-ID', dateOptions);
    const timeString = new Date().toLocaleTimeString('id-ID', timeOptions);
    const baseUrl = process.env.BASE_URL || 'http://localhost:8080';

    let broadcastMsg = '';

    if (report.status_libur) {
      // Holiday message
      const anggotaStr = members.join(', ');
      const fallbackMsg =
        `📢 *[SIAKANUDA] Laporan PKL Libur/Tutup*\n` +
        `\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\n` +
        `📅 *Tanggal:* ${todayFormatted} (Pukul ${timeString} WIB)\n` +
        `🏭 *Tempat PKL:* ${kelompok.tempat_pkl}\n` +
        `👨‍🏫 *Pembimbing:* ${pembimbingName}\n` +
        `👨\u200d🎓 *Ketua:* ${senderNameWithClass}\n` +
        `👥 *Anggota:* ${anggotaStr}\n` +
        `\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\n` +
        `ℹ️ *Status:* 🏢 LIBUR / TUTUP\n` +
        `📝 *Alasan:* "${report.libur_reason || '-'}"\n` +
        `\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\n` +
        `🔗 Detail: ${baseUrl}/pkl`;

      broadcastMsg = await renderTemplate('pkl_libur_broadcast', {
        tanggal: todayFormatted,
        waktu: timeString,
        tempat_pkl: kelompok.tempat_pkl,
        pembimbing: pembimbingName,
        ketua: senderNameWithClass,
        anggota: anggotaStr,
        alasan: report.libur_reason || '-',
        url: `${baseUrl}/pkl`
      }, fallbackMsg);
    } else {
      // Active attendance message
      const hadir  = members.filter(m => (attendanceData[m] || 'hadir') === 'hadir');
      const sakit  = members.filter(m => attendanceData[m] === 'sakit');
      const izin   = members.filter(m => attendanceData[m] === 'izin');
      const alpha  = members.filter(m => attendanceData[m] === 'alpha');

      let absenList = '';
      if (sakit.length) {
        const sakitText = sakit.map(m => m + (jurnalKegiatan[m] ? ` (${jurnalKegiatan[m].replace(/^Sakit:\s*/i, '')})` : '')).join(', ');
        absenList += `🏥 Sakit : ${sakitText}\n`;
      }
      if (izin.length) {
        const izinText = izin.map(m => m + (jurnalKegiatan[m] ? ` (${jurnalKegiatan[m].replace(/^Izin:\s*/i, '')})` : '')).join(', ');
        absenList += `📋 Izin  : ${izinText}\n`;
      }
      if (alpha.length) {
        const alphaText = alpha.map(m => m + (jurnalKegiatan[m] ? ` (${jurnalKegiatan[m]})` : '')).join(', ');
        absenList += `🔴 Alpha : ${alphaText}\n`;
      }

      let jurnalLines = members.map((m, i) => {
        const status = (attendanceData[m] || 'hadir');
        if (status === 'hadir') {
          const jurnal = jurnalKegiatan[m] ? `_${jurnalKegiatan[m]}_` : '_(tidak diisi)_';
          return `${i + 1}. *${m}*\n   📌 ${jurnal}`;
        } else {
          const statUpper = status.toUpperCase();
          let note = jurnalKegiatan[m] || '';
          if (note.toLowerCase().startsWith(status)) {
              note = note.substring(status.length).replace(/^:\s*/, '');
          }
          return `${i + 1}. *${m}* [${statUpper}]\n   📌 _${note || '(tanpa keterangan)'}_`;
        }
      }).join('\n');
      if (!jurnalLines) {
        jurnalLines = '_(tidak ada kegiatan / semua anggota absen)_';
      }

      const fallbackMsg =
        `📋 *[SIAKANUDA] Laporan PKL Masuk*\n` +
        `\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\n` +
        `📅 *Tanggal:* ${todayFormatted} (Pukul ${timeString} WIB)\n` +
        `🏭 *Tempat PKL:* ${kelompok.tempat_pkl}\n` +
        `👨‍🏫 *Pembimbing:* ${pembimbingName}\n` +
        `👨\u200d🎓 *Ketua:* ${senderNameWithClass}\n` +
        `📊 *Kehadiran:* ${hadir.length} Hadir / ${members.length} Total | 📸 *Foto:* ${urlsArray.length}${report.location_data ? ` | 📍 *lokasi absensi:* ${report.location_data}` : ''}\n` +
        absenList +
        `\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\n` +
        `📝 *Jurnal Kegiatan:*\n${jurnalLines}\n` +
        `\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\n` +
        `🔗 Detail: ${baseUrl}/pkl`;

      broadcastMsg = await renderTemplate('pkl_masuk_broadcast', {
        tanggal: todayFormatted,
        waktu: timeString,
        tempat_pkl: kelompok.tempat_pkl,
        pembimbing: pembimbingName,
        ketua: senderNameWithClass,
        kehadiran: `${hadir.length} Hadir / ${members.length} Total | 📸 *Foto:* ${urlsArray.length}${report.location_data ? ` | 📍 *lokasi absensi:* ${report.location_data}` : ''}`,
        absen_list: absenList,
        jurnal_lines: jurnalLines,
        url: `${baseUrl}/pkl`
      }, fallbackMsg);
    }

    // Prepend update prefix if it is an update/re-send
    if (isUpdate) {
      broadcastMsg = `⚠️ *[#Perubahan Laporan]*\n\n` + broadcastMsg;
    }

    const TESTING_MODE = process.env.TESTING_MODE !== 'false';
    const ADMIN_TEST_PHONE = '6285334354102';

    const sendPromises = [];

    // 1. Broadcast to Group (or fallback to SCHOOL_GROUP_JID)
    const broadcastGroupJid = (process.env.BROADCAST_GROUP_JID || process.env.SCHOOL_GROUP_JID)?.trim();
    if (broadcastGroupJid && sendMessage) {
      sendPromises.push(
        sendMessage(broadcastGroupJid, broadcastMsg)
          .then(() => console.log(`[BOT] [WEB-API] 📤 Broadcast grup sukses: ${broadcastGroupJid}`))
          .catch((err) => console.error('[BOT] [WEB-API] Gagal broadcast grup:', err.message))
      );
    }

    // 2. Kirim ke Guru Pembimbing
    const pembimbingPhone = kelompok.pembimbing_phone;
    if (TESTING_MODE) {
      if (ADMIN_TEST_PHONE && sendMessage) {
        const previewMsg =
          `🧪 *[TESTING MODE — Preview Notif Pembimbing (Web)]*\n` +
          `_(Pesan ini hanya dikirim ke admin selama masa testing)_\n` +
          `_Pembimbing asli: ${pembimbingPhone || 'belum diset'}_\n\n` +
          broadcastMsg;
        sendPromises.push(
          sendMessage(`${ADMIN_TEST_PHONE}@s.whatsapp.net`, previewMsg)
            .then(() => console.log(`[BOT] [WEB-API] 🧪 Testing mode preview dikirim ke admin: ${ADMIN_TEST_PHONE}`))
            .catch((err) => console.error('[BOT] [WEB-API] Gagal kirim preview admin:', err.message))
        );
      }
    } else if (pembimbingPhone && sendMessage) {
      sendPromises.push(
        sendMessage(`${pembimbingPhone}@s.whatsapp.net`, broadcastMsg)
          .then(() => console.log(`[BOT] [WEB-API] 📤 Notifikasi dikirim ke pembimbing: ${pembimbingPhone}`))
          .catch((err) => console.error('[BOT] [WEB-API] Gagal kirim notif pembimbing:', err.message))
      );
    }

    await Promise.allSettled(sendPromises);

    res.json({ ok: true });
  } catch (e) {
    console.error('[WEB-API] Broadcast error:', e);
    res.status(500).json({ error: 'Terjadi kesalahan internal server.' });
  }
});
app.post('/api/attendance/broadcast', async (req, res) => {
  // Allow localhost only
  const ip = req.ip || req.connection.remoteAddress;
  if (!ip.includes('127.0.0.1') && !ip.includes('::1') && ip !== 'localhost') {
    return res.status(403).json({ error: 'Forbidden' });
  }

  try {
    const { class_name, date, teacher, summary } = req.body;
    if (!class_name || !date || !teacher || !summary) {
      return res.status(400).json({ error: 'class_name, date, teacher, dan summary wajib diisi' });
    }

    const dateOptions = { timeZone: 'Asia/Jakarta', weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const timeOptions = { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', hour12: false };
    const todayFormatted = new Date().toLocaleDateString('id-ID', dateOptions);
    const timeString = new Date().toLocaleTimeString('id-ID', timeOptions);
    const baseUrl = process.env.BASE_URL || 'http://localhost:8080';

    let detailsBlock = '';
    const details = req.body.details || [];
    if (details.length > 0) {
      detailsBlock = '\n📝 *Detail Ketidakhadiran:*\n';
      const sakitList = details.filter(d => d.status === 'sakit');
      const izinList = details.filter(d => d.status === 'izin');
      const alphaList = details.filter(d => d.status === 'alpha');

      if (sakitList.length > 0) {
        detailsBlock += `• *Sakit (${sakitList.length}):*\n` + sakitList.map(d => `  - ${d.name} (${d.note || 'tanpa keterangan'})`).join('\n') + '\n';
      }
      if (izinList.length > 0) {
        detailsBlock += `• *Izin (${izinList.length}):*\n` + izinList.map(d => `  - ${d.name} (${d.note || 'tanpa keterangan'})`).join('\n') + '\n';
      }
      if (alphaList.length > 0) {
        detailsBlock += `• *Alpha (${alphaList.length}):*\n` + alphaList.map(d => `  - ${d.name} (${d.note || 'tanpa keterangan'})`).join('\n') + '\n';
      }
      detailsBlock += `━━━━━━━━━━━━━━━━━━━━\n`;
    }

    let fallbackMsg =
      `📢 *[SIAKANUDA] Laporan Absensi KBM Masuk*\n` +
      `━━━━━━━━━━━━━━━━━━━━\n` +
      `📅 *Tanggal:* ${todayFormatted} (Pukul ${timeString} WIB)\n` +
      `🏫 *Kelas:* ${class_name}\n` +
      `👨‍🏫 *Guru Pengabsen:* ${teacher}\n` +
      `━━━━━━━━━━━━━━━━━━━━\n` +
      `📊 *Ringkasan Kehadiran:*\n` +
      `• Hadir : ${summary.hadir} Siswa\n` +
      `• Sakit : ${summary.sakit} Siswa\n` +
      `• Izin  : ${summary.izin} Siswa\n` +
      `• Alpha : ${summary.alpha} Siswa\n` +
      `━━━━━━━━━━━━━━━━━━━━\n`;

    if (detailsBlock) {
      fallbackMsg += detailsBlock;
    }

    fallbackMsg += `🔗 Detail: ${baseUrl}/attendance?date=${date}`;

    let broadcastMsg = await renderTemplate('kbm_attendance_broadcast', {
      tanggal: todayFormatted,
      waktu: timeString,
      kelas: class_name,
      guru: teacher,
      hadir: summary.hadir,
      sakit: summary.sakit,
      izin: summary.izin,
      alpha: summary.alpha,
      detail_absen: detailsBlock.trim(),
      url: `${baseUrl}/attendance?date=${date}`
    }, fallbackMsg);

    // If template didn't use {detail_absen} (old template), append it manually
    try {
      const { getBotTemplate } = await import('./db.js');
      const templateObj = await getBotTemplate('kbm_attendance_broadcast');
      const templateHasDetail = templateObj && templateObj.body && templateObj.body.includes('{detail_absen}');
      if (!templateHasDetail && detailsBlock) {
        if (broadcastMsg.includes('🔗 Detail:')) {
          broadcastMsg = broadcastMsg.replace('🔗 Detail:', `${detailsBlock}🔗 Detail:`);
        } else {
          broadcastMsg += '\n' + detailsBlock;
        }
      }
    } catch (err) {
      console.error('[BOT] Gagal mengecek template detail_absen:', err.message);
    }

    // Prepend update prefix if KBM is updated
    const isUpdate = req.body.is_update === true || req.body.is_update === 1 || req.body.is_update === 'true';
    if (isUpdate) {
      broadcastMsg = `⚠️ *[#Pembaruan Absensi]*\n\n` + broadcastMsg;
    }

    const TESTING_MODE = process.env.TESTING_MODE !== 'false';
    const ADMIN_TEST_PHONE = '6285334354102';

    let sentToGroup = false;
    let targetGroupJid = null;

    const kbmPromises = [];

    // 1. Broadcast to School KBM Group JID (SCHOOL_GROUP_JID) or fallback to BROADCAST_GROUP_JID
    const schoolGroupJid = process.env.SCHOOL_GROUP_JID?.trim();
    const broadcastGroupJid = process.env.BROADCAST_GROUP_JID?.trim();
    targetGroupJid = schoolGroupJid || broadcastGroupJid;

    if (targetGroupJid && sendMessage) {
      kbmPromises.push(
        sendMessage(targetGroupJid, broadcastMsg)
          .then(() => {
            console.log(`[BOT] [WEB-API] 📤 KBM Broadcast grup sukses: ${targetGroupJid}`);
            sentToGroup = true;
          })
          .catch((err) => {
            console.error(`[BOT] [WEB-API] Gagal KBM broadcast ke grup ${targetGroupJid}:`, err.message);
          })
      );
    }

    // 2. Kirim ke Wali Kelas (wali_phone)
    const { wali_phone } = req.body;
    if (wali_phone && sendMessage) {
      if (TESTING_MODE) {
        const waliPreviewMsg = 
          `🧪 *[TESTING MODE — Preview Notif Wali Kelas (KBM)]*\n` +
          `_(Pesan ini hanya dikirim ke admin selama masa testing)_\n` +
          `_Wali kelas asli: ${wali_phone}_\n\n` +
          broadcastMsg;
        kbmPromises.push(
          sendMessage(`${ADMIN_TEST_PHONE}@s.whatsapp.net`, waliPreviewMsg)
            .then(() => console.log(`[BOT] [WEB-API] 🧪 Testing mode preview KBM wali kelas dikirim ke admin: ${ADMIN_TEST_PHONE}`))
            .catch((err) => console.error('[BOT] [WEB-API] Gagal kirim preview wali kelas ke admin:', err.message))
        );
      } else {
        kbmPromises.push(
          sendMessage(`${wali_phone}@s.whatsapp.net`, broadcastMsg)
            .then(() => console.log(`[BOT] [WEB-API] 📤 Laporan KBM berhasil dikirim ke Wali Kelas: ${wali_phone}`))
            .catch((err) => console.error(`[BOT] [WEB-API] Gagal kirim laporan KBM ke Wali Kelas ${wali_phone}:`, err.message))
        );
      }
    }

    // Kirim response 200 OK langsung ke dashboard PHP agar tidak timeout
    res.json({ ok: true, sent_to_group: true, target_group: targetGroupJid });

    // Jalankan seluruh proses pengiriman pesan WA di latar belakang secara asinkron
    (async () => {
      try {
        await Promise.allSettled(kbmPromises);

        // Jika tidak terkirim ke grup (baik grup kosong atau kirim grup gagal), kirim fallback ke admins
        if (!sentToGroup && sendMessage) {
          const allowed = await getAllowedNumbers();
          const admins = allowed.filter(n => n.role === 'admin' && /^\d+$/.test(n.phone));
          const adminPromises = admins.map(async (admin) => {
            try {
              const adminMsg = broadcastMsg + `\n\n_(Pesan ini dikirim ke Admin karena target grup WA belum diatur atau gagal dijangkau)_`;
              await sendMessage(admin.phone, adminMsg);
            } catch (adminErr) {
              console.error(`[BOT] [WEB-API] Gagal kirim fallback KBM ke admin ${admin.phone}:`, adminErr.message);
            }
          });
          await Promise.allSettled(adminPromises);
          console.log('[BOT] [WEB-API] Sent KBM broadcast to admins fallback.');
        }
      } catch (err) {
        console.error('[BOT] [WEB-API] Gagal memproses antrean pengiriman WA KBM:', err.message);
      }

      // 3. Cek dan kirim rekap konsolidasi jika seluruh kelas sudah diabsen
      try {
        const { getAttendanceKelasByDate } = await import('./db.js');
        const studentsList = await getAllStudents();
        const allClasses = [...new Set(studentsList.map(s => s.class?.trim()))].filter(Boolean).sort();
        
        if (allClasses.length > 0) {
          const reportedToday = await getAttendanceKelasByDate(date);
          const reportedClasses = reportedToday.map(r => r.class_name?.trim()).filter(Boolean);
          
          const missingClasses = allClasses.filter(c => !reportedClasses.includes(c));
          
          if (missingClasses.length === 0) {
            console.log('[BOT] [WEB-API] All classes filled attendance today! Preparing consolidated recap...');
            
            let classSummaries = [];
            let grandTotal = { hadir: 0, sakit: 0, izin: 0, alpha: 0 };
            
            for (const record of reportedToday) {
              let hadir = 0, sakit = 0, izin = 0, alpha = 0;
              let attData = {};
              try {
                attData = typeof record.attendance_data === 'string' ? JSON.parse(record.attendance_data) : (record.attendance_data || {});
              } catch (e) {}
              
              for (const status of Object.values(attData)) {
                const s = String(status).toLowerCase();
                if (s === 'hadir') hadir++;
                else if (s === 'sakit') sakit++;
                else if (s === 'izin') izin++;
                else if (s === 'alpha') alpha++;
              }
              
              classSummaries.push({
                className: record.class_name,
                hadir, sakit, izin, alpha
              });
              
              grandTotal.hadir += hadir;
              grandTotal.sakit += sakit;
              grandTotal.izin += izin;
              grandTotal.alpha += alpha;
            }
            
            classSummaries.sort((a, b) => a.className.localeCompare(b.className));
            
            const classListStr = classSummaries.map((c, idx) => {
              return `${idx + 1}. *${c.className}* (H: ${c.hadir} | S: ${c.sakit} | I: ${c.izin} | A: ${c.alpha})`;
            }).join('\n');
            
            const totalSiswa = grandTotal.hadir + grandTotal.sakit + grandTotal.izin + grandTotal.alpha;
            
            const consolidatedFallbackMsg =
              `📊 *[SIAKANUDA] REKAPITULASI ABSENSI HARIAN KBM SEKOLAH*\n` +
              `━━━━━━━━━━━━━━━━━━━━\n` +
              `📅 *Hari/Tanggal:* ${todayFormatted} (Pukul ${timeString} WIB)\n` +
              `🏫 *Sekolah:* ${SCHOOL_NAME}\n` +
              `━━━━━━━━━━━━━━━━━━━━\n` +
              `*Status Pengisian:* ✅ 100% Terisi (Semua Kelas Sudah Lapor)\n\n` +
              `*Rekapitulasi per Kelas:*\n${classListStr}\n\n` +
              `*Akumulasi Total Sekolah:*\n` +
              `• Total Siswa : ${totalSiswa} Siswa\n` +
              `• Total Hadir : ${grandTotal.hadir} Siswa\n` +
              `• Total Sakit : ${grandTotal.sakit} Siswa\n` +
              `• Total Izin  : ${grandTotal.izin} Siswa\n` +
              `• Total Alpha : ${grandTotal.alpha} Siswa\n` +
              `━━━━━━━━━━━━━━━━━━━━\n` +
              `🔗 Detail Laporan Lengkap: ${baseUrl}/attendance?date=${date}`;
              
            const consolidatedBroadcastMsg = await renderTemplate('kbm_consolidated_recap', {
              tanggal: todayFormatted,
              waktu: timeString,
              nama_sekolah: SCHOOL_NAME,
              rekap_kelas: classListStr,
              total_siswa: totalSiswa,
              total_hadir: grandTotal.hadir,
              total_sakit: grandTotal.sakit,
              total_izin: grandTotal.izin,
              total_alpha: grandTotal.alpha,
              url: `${baseUrl}/attendance?date=${date}`
            }, consolidatedFallbackMsg);
            
            // Send to KBM Group JID
            if (targetGroupJid && sendMessage) {
              try {
                await sendMessage(targetGroupJid, consolidatedBroadcastMsg);
                console.log(`[BOT] [WEB-API] 📤 Consolidated recap sent to group: ${targetGroupJid}`);
              } catch (sendErr) {
                console.error(`[BOT] [WEB-API] Gagal mengirim consolidated recap ke grup ${targetGroupJid}:`, sendErr.message);
              }
            }
          } else {
            console.log(`[BOT] [WEB-API] Consolidated check: ${missingClasses.length} classes still missing. Skipping consolidated recap.`);
          }
        }
      } catch (err) {
        console.error('[BOT] [WEB-API] Error checking/sending consolidated recap:', err.message);
      }
    })();
  } catch (e) {
    console.error('[WEB-API] KBM Broadcast error:', e);
    // Jika error terjadi sebelum pengiriman response awal
    if (!res.headersSent) {
      res.status(500).json({ error: 'Terjadi kesalahan internal server.' });
    }
  }
});



app.post('/api/send', requireAuth, onlyAdmin, async (req, res) => {
  try {
    const { phone, message } = req.body;
    if (!phone || !message) return res.status(400).json({ error: 'phone dan message wajib diisi' });
    await sendMessage(phone, message);
    res.json({ ok: true });
  } catch (e) { res.status(500).json({ error: 'Terjadi kesalahan internal server.' }); }
});

// ─── Bot & Cron Settings API (v1.6.25) ───────────────────────────────────────

/** POST /api/bot/logout — Disconnect WA Session */
app.post('/api/bot/logout', requireAuth, onlyAdmin, async (_, res) => {
  try {
    await logoutSession();
    res.json({ ok: true });
  } catch (e) {
    console.error('[API] Logout error:', e);
    res.status(500).json({ error: 'Gagal memutus sesi WhatsApp.' });
  }
});

/** GET /api/settings/templates — Get all message templates */
app.get('/api/settings/templates', requireAuth, onlyAdmin, async (_, res) => {
  try {
    const templates = await getBotTemplates();
    res.json(templates);
  } catch (e) {
    res.status(500).json({ error: 'Gagal mengambil data template.' });
  }
});

/** POST /api/settings/templates — Update a template */
app.post('/api/settings/templates', requireAuth, onlyAdmin, async (req, res) => {
  try {
    const { key, body } = req.body;
    if (!key || body === undefined) {
      return res.status(400).json({ error: 'Key dan body wajib diisi.' });
    }
    await updateBotTemplate(key, body);
    res.json({ ok: true });
  } catch (e) {
    res.status(500).json({ error: 'Gagal memperbarui template.' });
  }
});

/** GET /api/settings/cron — Get all cron configs */
app.get('/api/settings/cron', requireAuth, onlyAdmin, async (_, res) => {
  try {
    const configs = await getCronConfigs();
    res.json(configs);
  } catch (e) {
    res.status(500).json({ error: 'Gagal mengambil konfigurasi cron.' });
  }
});

/** POST /api/settings/cron — Update cron configs */
app.post('/api/settings/cron', requireAuth, onlyAdmin, async (req, res) => {
  try {
    const { configs } = req.body; // Expecting array of { key, cronExpression, isActive }
    if (!Array.isArray(configs)) {
      return res.status(400).json({ error: 'Format data salah, configs harus berupa array.' });
    }
    for (const conf of configs) {
      await updateCronConfig(conf.key, conf.cronExpression || conf.cron_expression, conf.isActive !== undefined ? conf.isActive : conf.is_active);
    }
    // Reload/reschedule immediately in background!
    await loadAndScheduleCronJobs();
    res.json({ ok: true });
  } catch (e) {
    console.error('[API] Cron update error:', e);
    res.status(500).json({ error: 'Gagal memperbarui konfigurasi cron.' });
  }
});

/** POST /api/settings/cron/add — Add new cron config */
app.post('/api/settings/cron/add', requireAuth, onlyAdmin, async (req, res) => {
  try {
    const { key, name, cronExpression, isActive, description, action } = req.body;
    if (!key || !name || !cronExpression || !action) {
      return res.status(400).json({ error: 'key, name, cronExpression, dan action wajib diisi.' });
    }
    await addCronConfig({ key, name, cronExpression, isActive, description, action });
    // Reload/reschedule immediately in background!
    await loadAndScheduleCronJobs();
    res.json({ ok: true });
  } catch (e) {
    console.error('[API] Cron add error:', e);
    res.status(500).json({ error: 'Gagal menambahkan konfigurasi cron.' });
  }
});

/** POST /api/settings/cron/delete/:key — Delete a custom cron config */
app.post('/api/settings/cron/delete/:key', requireAuth, onlyAdmin, async (req, res) => {
  try {
    const { key } = req.params;
    if (!key) {
      return res.status(400).json({ error: 'Key wajib diisi.' });
    }
    // Don't allow deleting default keys
    const defaults = ['class_attendance_check', 'pkl_report_check', 'pkl_escalation_check', 'auto_alpha_job'];
    if (defaults.includes(key)) {
      return res.status(400).json({ error: 'Tugas cron default sistem tidak boleh dihapus.' });
    }
    await deleteCronConfig(key);
    // Reload/reschedule immediately in background!
    await loadAndScheduleCronJobs();
    res.json({ ok: true });
  } catch (e) {
    console.error('[API] Cron delete error:', e);
    res.status(500).json({ error: 'Gagal menghapus konfigurasi cron.' });
  }
});

/** GET /api/bot/groups — Get all participating groups */
app.get('/api/bot/groups', requireAuth, onlyAdmin, async (req, res) => {
  try {
    const { getBotStatus, getParticipatingGroups } = await import('./bot.js');
    const status = getBotStatus();
    if (!status.connected) {
      return res.json({ connected: false, groups: [] });
    }
    const groups = await getParticipatingGroups();
    res.json({ connected: true, groups });
  } catch (err) {
    console.error('[API] Error fetching groups:', err.message);
    res.status(500).json({ error: 'Gagal mengambil daftar grup: ' + err.message });
  }
});

/** POST /api/bot/join-group — Join a group via invite link */
app.post('/api/bot/join-group', requireAuth, onlyAdmin, async (req, res) => {
  try {
    const { inviteLink } = req.body;
    if (!inviteLink) {
      return res.status(400).json({ error: 'Link undangan (inviteLink) wajib diisi.' });
    }
    const { joinGroupByInvite } = await import('./bot.js');
    const result = await joinGroupByInvite(inviteLink);
    res.json({ ok: true, group: result });
  } catch (err) {
    console.error('[API] Error joining group:', err.message);
    res.status(500).json({ error: 'Gagal bergabung ke grup: ' + err.message });
  }
});

/** POST /api/settings/groups — Update BROADCAST_GROUP_JID and SCHOOL_GROUP_JID in .env */
app.post('/api/settings/groups', requireAuth, onlyAdmin, async (req, res) => {
  try {
    const { broadcast_group_jid, school_group_jid } = req.body;
    const { fileURLToPath } = await import('url');
    const path = await import('path');
    const fs = await import('fs');
    const __dirname = path.dirname(fileURLToPath(import.meta.url));
    const envPath = path.join(__dirname, '..', '.env');
    
    if (fs.existsSync(envPath)) {
      let envContent = fs.readFileSync(envPath, 'utf8');
      
      if (broadcast_group_jid !== undefined) {
        if (envContent.includes('BROADCAST_GROUP_JID=')) {
          envContent = envContent.replace(/BROADCAST_GROUP_JID=.*/, `BROADCAST_GROUP_JID=${broadcast_group_jid}`);
        } else {
          envContent += `\nBROADCAST_GROUP_JID=${broadcast_group_jid}`;
        }
        process.env.BROADCAST_GROUP_JID = broadcast_group_jid;
      }
      
      if (school_group_jid !== undefined) {
        if (envContent.includes('SCHOOL_GROUP_JID=')) {
          envContent = envContent.replace(/SCHOOL_GROUP_JID=.*/, `SCHOOL_GROUP_JID=${school_group_jid}`);
        } else {
          envContent += `\nSCHOOL_GROUP_JID=${school_group_jid}`;
        }
        process.env.SCHOOL_GROUP_JID = school_group_jid;
      }
      
      fs.writeFileSync(envPath, envContent, 'utf8');
      return res.json({ ok: true, message: 'Group JIDs updated successfully' });
    }
    res.status(404).json({ error: 'File .env tidak ditemukan' });
  } catch (err) {
    console.error('[API] Error updating group settings:', err.message);
    res.status(500).json({ error: err.message });
  }
});

// ─── Authentication API (v2 — bcrypt + JWT) ──────────────────────────────────

/**
 * POST /api/login
 * Body: { username, password }
 *   - Guru/Admin/Superadmin: username = nomor WA (atau 'superadmin'), password = password
 *   - Siswa: username = NISN (10 digit), password = NISN (default) atau password baru
 * Response: { token, role, name, isSuperAdmin, firstLogin }
 */
app.post('/api/login', async (req, res) => {
  try {
    const { username, password } = req.body;
    if (!username || !password) {
      return res.status(400).json({ error: 'Username dan password wajib diisi.' });
    }

    const { supabase, useSupabase } = await import('./db.js');
    if (!useSupabase) {
      return res.status(500).json({ error: 'Sistem auth hanya support Supabase mode.' });
    }

    // ── Cek di tabel allowed_numbers (Guru / Admin / Superadmin) ──────────────
    const { data: staffUser } = await supabase
      .from('allowed_numbers')
      .select('*')
      .eq('phone', username)
      .eq('active', true)
      .maybeSingle();

    if (staffUser) {
      // Jika password_hash belum ada (akun lama), fallback ke password default
      let passwordOk = false;
      if (staffUser.password_hash) {
        passwordOk = await bcrypt.compare(password, staffUser.password_hash);
      } else {
        // Fallback ke password lama sebelum migrasi
        const oldPasswords = ['guruhebat', 'guru123', process.env.DASHBOARD_PASSWORD || 'adminsmknuda'];
        passwordOk = oldPasswords.includes(password);
      }

      if (!passwordOk) {
        return res.status(401).json({ error: 'Username atau password salah.' });
      }

      const tokenPayload = {
        phone: staffUser.phone,
        role: staffUser.role,
        name: staffUser.name,
        isSuperAdmin: staffUser.is_super_admin === 1 || staffUser.is_super_admin === true,
      };
      const token = generateToken(tokenPayload);

      return res.json({
        success: true,
        token,
        role: staffUser.role,
        name: staffUser.name,
        phone: staffUser.phone,
        isSuperAdmin: tokenPayload.isSuperAdmin,
        firstLogin: false, // Guru/Staf tidak perlu ganti password pertama kali
      });
    }

    // ── Cek di tabel students (Siswa — username = NISN) ───────────────────────
    const { data: studentUser } = await supabase
      .from('students')
      .select('*')
      .eq('nis', username)  // kolom nis = NISN 10 digit
      .maybeSingle();

    if (studentUser) {
      let passwordOk = false;
      if (studentUser.password_hash) {
        passwordOk = await bcrypt.compare(password, studentUser.password_hash);
      } else {
        // Fallback: password default = NISN itu sendiri
        passwordOk = (password === studentUser.nis);
      }

      if (!passwordOk) {
        return res.status(401).json({ error: 'NISN atau password salah.' });
      }

      const tokenPayload = {
        phone: studentUser.phone || studentUser.nis,
        role: studentUser.role || 'siswa',
        name: studentUser.name,
        studentId: studentUser.id,
        nisn: studentUser.nis,
        class: studentUser.class,
        isSuperAdmin: false,
      };
      const token = generateToken(tokenPayload);

      return res.json({
        success: true,
        token,
        role: studentUser.role || 'siswa',
        name: studentUser.name,
        class: studentUser.class,
        studentId: studentUser.id,
        nisn: studentUser.nis,
        isSuperAdmin: false,
        firstLogin: studentUser.first_login === 1 || studentUser.first_login === true,
      });
    }

    // Tidak ditemukan di kedua tabel
    return res.status(401).json({ error: 'Username atau password salah.' });

  } catch (e) {
    console.error('[AUTH] Login error:', e.message);
    res.status(500).json({ error: 'Terjadi kesalahan server. Coba lagi.' });
  }
});

/**
 * GET /api/auth/me
 * Cek siapa user yang sedang login (berdasarkan token).
 * Response: { phone, role, name, isSuperAdmin }
 */
app.get('/api/auth/me', requireAuth, (req, res) => {
  res.json({ success: true, user: req.user });
});

/**
 * POST /api/auth/change-password
 * Ganti password sendiri.
 * Body: { oldPassword, newPassword }
 * - Berlaku untuk semua role (guru maupun siswa)
 * - Jika firstLogin=true, oldPassword dilewati (cukup kirim newPassword)
 */
app.post('/api/auth/change-password', requireAuth, async (req, res) => {
  try {
    const { oldPassword, newPassword } = req.body;
    if (!newPassword || newPassword.length < 8) {
      return res.status(400).json({ error: 'Password baru minimal 8 karakter.' });
    }

    const { supabase } = await import('./db.js');
    const { phone, role, nisn, studentId } = req.user;
    const isSiswa = ['siswa', 'ketua_pkl'].includes(role);

    if (isSiswa) {
      // Ambil data siswa
      const { data: student } = await supabase
        .from('students')
        .select('password_hash, first_login, nis')
        .eq('id', studentId)
        .maybeSingle();

      if (!student) return res.status(404).json({ error: 'Data siswa tidak ditemukan.' });

      // Verifikasi password lama (kecuali first login)
      if (!student.first_login) {
        if (!oldPassword) return res.status(400).json({ error: 'Password lama wajib diisi.' });
        const oldOk = student.password_hash
          ? await bcrypt.compare(oldPassword, student.password_hash)
          : (oldPassword === student.nis);
        if (!oldOk) return res.status(401).json({ error: 'Password lama salah.' });
      }

      const newHash = await bcrypt.hash(newPassword, 10);
      await supabase.from('students').update({ password_hash: newHash, first_login: 0 }).eq('id', studentId);

    } else {
      // Guru / Admin
      const { data: staff } = await supabase
        .from('allowed_numbers')
        .select('password_hash, first_login')
        .eq('phone', phone)
        .maybeSingle();

      if (!staff) return res.status(404).json({ error: 'Data pengguna tidak ditemukan.' });

      if (!staff.first_login) {
        if (!oldPassword) return res.status(400).json({ error: 'Password lama wajib diisi.' });
        const oldOk = staff.password_hash
          ? await bcrypt.compare(oldPassword, staff.password_hash)
          : ['guruhebat', 'guru123'].includes(oldPassword);
        if (!oldOk) return res.status(401).json({ error: 'Password lama salah.' });
      }

      const newHash = await bcrypt.hash(newPassword, 10);
      await supabase.from('allowed_numbers').update({ password_hash: newHash, first_login: 0 }).eq('phone', phone);
    }

    res.json({ success: true, message: 'Password berhasil diubah.' });

  } catch (e) {
    console.error('[AUTH] Change password error:', e.message);
    res.status(500).json({ error: 'Terjadi kesalahan server.' });
  }
});

/**
 * POST /api/auth/reset-student-password
 * Reset password siswa ke default (NISN) — hanya Admin ke atas.
 * Body: { studentId }
 */
app.post('/api/auth/reset-student-password', requireAuth, onlyAdmin, async (req, res) => {
  try {
    const { studentId } = req.body;
    if (!studentId) return res.status(400).json({ error: 'studentId wajib diisi.' });

    const { supabase } = await import('./db.js');
    const { data: student } = await supabase
      .from('students')
      .select('id, name, nis')
      .eq('id', studentId)
      .maybeSingle();

    if (!student) return res.status(404).json({ error: 'Siswa tidak ditemukan.' });
    if (!student.nis) return res.status(400).json({ error: 'Siswa ini tidak memiliki NISN. Isi NISN dulu.' });

    const defaultHash = await bcrypt.hash(student.nis, 10);
    await supabase
      .from('students')
      .update({ password_hash: defaultHash, first_login: 1 })
      .eq('id', studentId);

    res.json({
      success: true,
      message: `Password ${student.name} berhasil direset ke NISN (${student.nis}). Siswa wajib ganti password saat login berikutnya.`,
    });

  } catch (e) {
    console.error('[AUTH] Reset password error:', e.message);
    res.status(500).json({ error: 'Terjadi kesalahan server.' });
  }
});

/**
 * POST /api/auth/reset-staff-password
 * Reset password guru/staf ke default (guruhebat) — hanya Admin ke atas.
 * Body: { phone }
 */
app.post('/api/auth/reset-staff-password', requireAuth, onlyAdmin, async (req, res) => {
  try {
    const { phone } = req.body;
    if (!phone) return res.status(400).json({ error: 'phone wajib diisi.' });

    const { supabase } = await import('./db.js');
    const { data: staff } = await supabase
      .from('allowed_numbers')
      .select('id, name')
      .eq('phone', phone)
      .maybeSingle();

    if (!staff) return res.status(404).json({ error: 'Data guru/staf tidak ditemukan.' });

    const defaultHash = await bcrypt.hash('guruhebat', 10);
    await supabase
      .from('allowed_numbers')
      .update({ password_hash: defaultHash, first_login: 1 })
      .eq('phone', phone);

    res.json({
      success: true,
      message: `Password ${staff.name} berhasil direset ke 'guruhebat'. Guru wajib ganti password saat login berikutnya.`,
    });

  } catch (e) {
    console.error('[AUTH] Reset staff password error:', e.message);
    res.status(500).json({ error: 'Terjadi kesalahan server.' });
  }
});

/**
 * POST /api/auth/toggle-superadmin
 * Toggle super admin status for a staff member — hanya Super Admin.
 * Body: { phone, isSuperAdmin }
 */
app.post('/api/auth/toggle-superadmin', requireAuth, onlyAdmin, async (req, res) => {
  try {
    const { phone, isSuperAdmin } = req.body;
    if (!phone) return res.status(400).json({ error: 'phone wajib diisi.' });

    // Cek apakah user yang request adalah superadmin asli
    if (!req.user.isSuperAdmin) {
      return res.status(403).json({ error: 'Akses ditolak. Hanya Super Admin yang bisa mengubah status Super Admin.' });
    }

    const { supabase } = await import('./db.js');
    const { data: staff } = await supabase
      .from('allowed_numbers')
      .select('id, name')
      .eq('phone', phone)
      .maybeSingle();

    if (!staff) return res.status(404).json({ error: 'Data guru/staf tidak ditemukan.' });

    await supabase
      .from('allowed_numbers')
      .update({ is_super_admin: isSuperAdmin ? 1 : 0 })
      .eq('phone', phone);

    res.json({
      success: true,
      message: `Status Super Admin untuk ${staff.name} berhasil diubah.`,
    });

  } catch (e) {
    console.error('[AUTH] Toggle superadmin error:', e.message);
    res.status(500).json({ error: 'Terjadi kesalahan server.' });
  }
});

// ─── Socket.io ───────────────────────────────────────────────────────────────

io.on('connection', (socket) => {
  console.log(`[WS] Client terhubung: ${socket.id}`);
  socket.on('disconnect', () => console.log(`[WS] Client terputus: ${socket.id}`));
});

// ─── Boot ─────────────────────────────────────────────────────────────────────

async function boot() {
  await initSchema();
  initAI();
  setSocketIO(io);
  startCronJobs();
  
  // Pemicu awal sinkronisasi foto tertunda saat server booting
  syncLocalPhotosToSupabase().catch(err => console.error('[BOOT] Gagal sinkronisasi foto awal:', err.message));

  server.listen(PORT, () => {
    const pkg = JSON.parse(fs.readFileSync(path.join(ROOT, 'package.json'), 'utf8'));
    console.log(`\n🏫 SIAKANUDA v${pkg.version} — ${SCHOOL_NAME}`);
    console.log(`🚀 Server berjalan di http://localhost:${PORT}`);
    console.log(`🤖 WA Panel  : http://localhost:${PORT}`);
    console.log(`🔌 API       : http://localhost:${PORT}/api`);
    console.log(`🏓 Keep-Alive: http://localhost:${PORT}/ping\n`);
    startBot().catch(err => console.error('[BOOT] Gagal start bot:', err));
  });
}

boot().catch(err => console.error('[BOOT] Fatal boot error:', err));
