/**
 * execution/cron_jobs.js
 * SIAKANUDA v1.0.0 — Tier 3: Cron Jobs for Automation & Warning Escalation
 * Scheduled times: 08:30, 14:00, 15:30, 23:59 (Asia/Jakarta timezone)
 */

import cron from 'node-cron';
import * as db from './db.js';
import { sendMessage, queueMessage } from './bot.js';
import { syncLocalPhotosToSupabase } from './photo_sync.js';
import path from 'path';
import fs from 'fs';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const TZ_JAKARTA = 'Asia/Jakarta';
const SCHOOL_NAME = process.env.SCHOOL_NAME || 'SMK NU Darussalam';
// 1. 08:30 WIB — Early Warning Absensi Kelas (Grup KBM)
export async function runClassAttendanceCheck() {
  try {
    console.log('[CRON] Running Class Attendance Check...');
    const today = new Date().toISOString().split('T')[0];
    const activeTP = await db.getActiveTahunPelajaran();
    const tpId = activeTP ? activeTP.id : 1;

    const groupJid = (process.env.SCHOOL_GROUP_JID || process.env.BROADCAST_GROUP_JID)?.trim() || null;
    if (!groupJid) {
      console.log('[CRON] Skip KBM check: No Group JID configured.');
      return { ok: true, msg: 'Group JID not configured' };
    }

    // Get all students to extract classes
    const allStudents = await db.getAllStudents();
    const allClasses = [...new Set(allStudents.map(s => s.class))].sort();

    if (allClasses.length === 0) return { ok: true, msg: 'Tidak ada kelas terdaftar' };

    // Get classes that reported today
    const reportedRecords = (await db.getAttendanceKelasByDate(today)).filter(r => r.tahun_pelajaran_id === tpId);
    const reportedClasses = reportedRecords.map(r => r.class_name);

    const missingClasses = allClasses.filter(c => !reportedClasses.includes(c));

    if (missingClasses.length > 0) {
      const classListStr = missingClasses.map(c => `• *${c}*`).join('\n');
      const fallbackMsg = `🔔 *INFO ABSENSI HARIAN — SIAKANUDA*\n\nSelamat pagi Bapak/Ibu Guru Hebat, mohon izin menyampaikan informasi. Hingga pukul 08:30 WIB hari ini, data absensi untuk kelas berikut terpantau *BELUM* masuk:\n\n${classListStr}\n\nBagi Bapak/Ibu yang sedang bertugas, mohon berkenan melakukan absensi via WhatsApp (balas menu *1* atau gunakan format \`#absen\`).\n\nJika ada kendala, silakan hubungi petugas input SIAKANUDA. Terima kasih!`;
      
      const msg = await db.renderTemplate('class_attendance_warning', {
        daftar_kelas: classListStr
      }, fallbackMsg);
      
      await sendMessage(groupJid, msg);
      console.log(`[CRON] Sent class attendance warnings to Group JID: ${groupJid}`);
      return { ok: true, msg: 'Peringatan absensi dikirim ke grup', missingClasses };
    } else {
      console.log('[CRON] All classes have reported attendance today.');
      return { ok: true, msg: 'Semua kelas sudah lapor absensi hari ini' };
    }
  } catch (err) {
    console.error('[CRON] Error in 08:30 job:', err);
    throw err;
  }
}

// 2. 14:00 WIB — Pengingat Laporan Jurnal PKL (Grup KBM/Umum)
export async function runPklReportCheck() {
  try {
    console.log('[CRON] Running PKL Report Check...');
    const today = new Date().toISOString().split('T')[0];
    const activeTP = await db.getActiveTahunPelajaran();
    const tpId = activeTP ? activeTP.id : 1;

    const groupJid = process.env.BROADCAST_GROUP_JID?.trim() || null;
    if (!groupJid) {
      console.log('[CRON] Skip PKL report check: No Broadcast Group JID configured.');
      return { ok: true, msg: 'Broadcast Group JID not configured' };
    }

    // Get all groups
    const allGroups = (await db.getKelompokPkl()).filter(g => g.tahun_pelajaran_id === tpId);
    if (allGroups.length === 0) return { ok: true, msg: 'Tidak ada kelompok PKL terdaftar' };

    // Get reported groups today
    const reportedPkl = (await db.getAttendancePklByDate(today)).filter(r => r.tahun_pelajaran_id === tpId);
    const reportedPhones = reportedPkl.map(r => r.ketua_phone);

    const missingGroups = allGroups.filter(g => !reportedPhones.includes(g.ketua_phone));

    if (missingGroups.length > 0) {
      // Format a consolidated list
      const listStr = missingGroups.map((g, idx) => `${idx + 1}. *${g.tempat_pkl}*`).join('\n');
      const fallbackMsg = `⚠️ *PENGINGAT LAPORAN PKL — SIAKANUDA*\n\nHalo teman-teman siswa PKL! Kelompok PKL di lokasi berikut terpantau *BELUM* melaporkan absensi & jurnal hari ini:\n\n${listStr}\n\nMohon ketua kelompok segera melaporkan bukti foto dan jurnal kelompok via WhatsApp sebelum batas akhir. Terima kasih!`;
      
      const msg = await db.renderTemplate('pkl_report_reminder', {
        tempat_pkl: 'beberapa lokasi (daftar terlampir)'
      }, fallbackMsg);

      let finalMsg = msg;
      if (msg === fallbackMsg) {
        finalMsg = fallbackMsg;
      } else {
        finalMsg = `${msg}\n\n📋 *Daftar Kelompok Belum Melapor:*\n${listStr}`;
      }

      await sendMessage(groupJid, finalMsg);
      console.log(`[CRON] Sent consolidated PKL reminder to Group JID: ${groupJid}`);
      return { ok: true, msg: `Selesai memeriksa, pengingat grup dikirim`, missingCount: missingGroups.length };
    } else {
      console.log('[CRON] All PKL groups have reported today.');
      return { ok: true, msg: 'Semua kelompok PKL sudah lapor hari ini' };
    }
  } catch (err) {
    console.error('[CRON] Error in PKL report check job:', err);
    throw err;
  }
}

// 3. 16:00 WIB — Eskalasi Peringatan PKL (Guru Pembimbing)
export async function runPklEscalationCheck() {
  try {
    console.log('[CRON] Running PKL Escalation Check...');
    const today = new Date().toISOString().split('T')[0];
    const activeTP = await db.getActiveTahunPelajaran();
    const tpId = activeTP ? activeTP.id : 1;

    const allGroups = (await db.getKelompokPkl()).filter(g => g.tahun_pelajaran_id === tpId);
    if (allGroups.length === 0) return { ok: true, msg: 'Tidak ada kelompok PKL terdaftar' };

    const reportedPkl = (await db.getAttendancePklByDate(today)).filter(r => r.tahun_pelajaran_id === tpId);
    const reportedPhones = reportedPkl.map(r => r.ketua_phone);

    const missingGroups = allGroups.filter(g => !reportedPhones.includes(g.ketua_phone));

    if (missingGroups.length > 0) {
      const teacherGroupJid = process.env.TEACHER_GROUP_JID?.trim() || process.env.SCHOOL_GROUP_JID?.trim() || null;
      if (!teacherGroupJid) {
        console.log('[CRON] Skip PKL escalation: No Teacher Group JID configured.');
        return { ok: true, msg: 'Teacher Group JID not configured' };
      }

      // Map allowed numbers to get teacher names
      const allowed = await db.getAllowedNumbers();
      const teachersMap = {};
      const cleanPhone = (p) => p ? String(p).replace(/\D/g, '').replace(/^(0|62)/, '') : '';
      for (const t of allowed) {
        teachersMap[cleanPhone(t.phone)] = t.name;
      }

      // Consolidate all missing groups into one list
      const fallbackMsg = `⚠️ *ESKALASI PENGINGAT PKL — SIAKANUDA*\n\nYth. Bapak/Ibu Dewan Guru & Pembimbing, mohon izin menginformasikan bahwa hingga pukul 16:00 WIB hari ini, kelompok PKL berikut *BELUM* mengirimkan laporan harian.\n\nSistem telah mengirimkan teguran otomatis ke Grup Siswa pada pukul 14:00 WIB sebelumnya. Mohon berkenan bagi Bapak/Ibu Pembimbing yang bersangkutan untuk melakukan konfirmasi/kroscek.\n\nTerima kasih.`;
      const baseMsg = await db.renderTemplate('pkl_escalation_warning', {
        tempat_pkl: 'beberapa lokasi (daftar terlampir)',
        ketua_phone: 'daftar terlampir'
      }, fallbackMsg);
      
      const listStr = missingGroups.map((g, idx) => {
        let pembimbingName = '-';
        if (g.pembimbing_phone) {
          pembimbingName = teachersMap[cleanPhone(g.pembimbing_phone)] || g.pembimbing_phone;
        }
        return `${idx + 1}. *${g.tempat_pkl}*\n   👨‍🏫 Pembimbing: ${pembimbingName}\n   👨‍🎓 Ketua: ${g.ketua_phone || '-'}`;
      }).join('\n');
      
      const msg = `${baseMsg}\n\n📋 *Daftar Kelompok Belum Melapor:*\n${listStr}`;

      // Broadcast single message to Teacher Group
      await sendMessage(teacherGroupJid, msg);
      console.log(`[CRON] Sent PKL escalation to Teacher Group: ${teacherGroupJid} for ${missingGroups.length} groups`);
      
      return { ok: true, msg: `Eskalasi massal dikirim ke grup guru`, sentCount: 1 };
    } else {
      console.log('[CRON] All PKL groups have reported today.');
      return { ok: true, msg: 'Semua kelompok PKL sudah lapor hari ini' };
    }
  } catch (err) {
    console.error('[CRON] Error in escalation job:', err);
    throw err;
  }
}

// 4. 23:59 WIB — Kunci Tutup Buku & Auto-Alpha Harian
export async function runAutoAlphaJob() {
  try {
    console.log('[CRON] Running 23:59 WIB Auto-Alpha Lockout Job...');
    const today = new Date().toISOString().split('T')[0];
    const activeTP = await db.getActiveTahunPelajaran();
    const tpId = activeTP ? activeTP.id : 1;

    // ── A. Kelas KBM Kroscek ──
    const allStudents = await db.getAllStudents();
    const allClasses = [...new Set(allStudents.map(s => s.class))].sort();
    const reportedKelas = (await db.getAttendanceKelasByDate(today)).filter(r => r.tahun_pelajaran_id === tpId);
    const reportedKelasNames = reportedKelas.map(r => r.class_name);
    const missingKelas = allClasses.filter(c => !reportedKelasNames.includes(c));

    for (const className of missingKelas) {
      const classStudents = allStudents.filter(s => s.class === className);
      const autoAbsenData = {};
      
      for (const student of classStudents) {
        const records = (await db.getAttendanceToday()).filter(r => r.tahun_pelajaran_id === tpId);
        const exist = records.find(r => r.student_id === student.id && r.date === today);
        if (!exist) {
          await db.addAttendance({ studentId: student.id, date: today, status: 'alpha', note: 'Auto-Alpha Sistem', tahun_pelajaran_id: tpId });
          autoAbsenData[student.name] = 'alpha';
        } else {
          autoAbsenData[student.name] = exist.status;
        }
      }

      await db.addAttendanceKelas({
        date: today,
        className,
        teacherPhone: 'system',
        attendanceData: autoAbsenData,
        tahun_pelajaran_id: tpId
      });
      console.log(`[CRON] Auto-Alpha applied to class: ${className}`);
    }

    // ── B. Kelompok PKL Kroscek ──
    const allGroups = (await db.getKelompokPkl()).filter(g => g.tahun_pelajaran_id === tpId);
    const reportedPkl = (await db.getAttendancePklByDate(today)).filter(r => r.tahun_pelajaran_id === tpId);
    const reportedPklPhones = reportedPkl.map(r => r.ketua_phone);
    const missingGroups = allGroups.filter(g => !reportedPklPhones.includes(g.ketua_phone));

    for (const group of missingGroups) {
      const autoAbsenData = {};
      const members = group.anggota.split(',').map(m => m.trim()).filter(m => m.length > 0);

      for (const name of members) {
        const student = allStudents.find(s => s.name.toLowerCase().includes(name.toLowerCase()));
        if (student) {
          await db.addAttendance({ studentId: student.id, date: today, status: 'alpha', note: 'Auto-Alpha PKL Sistem', tahun_pelajaran_id: tpId });
          autoAbsenData[student.name] = 'alpha';
        } else {
          autoAbsenData[name] = 'alpha';
        }
      }

      await db.addAttendancePkl({
        date: today,
        tempatPkl: group.tempat_pkl,
        ketuaPhone: group.ketua_phone,
        statusLibur: false,
        attendanceData: autoAbsenData,
        photoUrl: null,
        jurnalKegiatan: 'Sistem Auto-Alpha: Tidak melapor hingga batas akhir pukul 23:59 WIB.',
        isTakeover: false,
        tahun_pelajaran_id: tpId
      });
      console.log(`[CRON] Auto-Alpha applied to PKL: ${group.tempat_pkl}`);
    }

    return { ok: true, msg: 'Auto-Alpha harian berhasil diaplikasikan' };
  } catch (err) {
    console.error('[CRON] Error in 23:59 job:', err);
    throw err;
  }
}

// -----------------------------------------------------------------------------
// 6. Custom Scheduled Message Job
// -----------------------------------------------------------------------------
export async function runCustomMessageJob(config) {
  console.log(`[CRON] Menjalankan custom message job: ${config.name}`);
  try {
    if (!config.payload) {
      console.error(`[CRON] Gagal mengirim pesan kustom: Payload kosong pada job ${config.key}`);
      return;
    }
    const payload = typeof config.payload === 'string' ? JSON.parse(config.payload) : config.payload;
    if (!payload.target || !payload.message) {
      console.error(`[CRON] Gagal mengirim pesan kustom: Target atau Message tidak lengkap pada job ${config.key}`);
      return;
    }

    let target = payload.target.trim();
    const message = payload.message.trim();

    // Pastikan ID target formatnya benar (tambah @s.whatsapp.net jika nomor pribadi)
    if (!target.includes('@g.us') && !target.includes('@s.whatsapp.net')) {
      target = `${target}@s.whatsapp.net`;
    }

    queueMessage(target, message);
    console.log(`[CRON] Pesan kustom dijadwalkan untuk dikirim ke ${target}`);
  } catch (err) {
    console.error(`[CRON] Kesalahan pada runCustomMessageJob (${config.key}):`, err.message);
  }
}

const jobRunners = {
  class_attendance_check: runClassAttendanceCheck,
  pkl_report_check: runPklReportCheck,
  pkl_escalation_check: runPklEscalationCheck,
  auto_alpha_job: runAutoAlphaJob,
  custom_message: runCustomMessageJob
};

let scheduledJobs = {};

export async function loadAndScheduleCronJobs() {
  console.log('[CRON] Loading and scheduling dynamic Cron Jobs from DB...');
  
  // 1. Stop all currently active dynamic jobs
  for (const key of Object.keys(scheduledJobs)) {
    try {
      scheduledJobs[key].stop();
      console.log(`[CRON] Stopped job: ${key}`);
    } catch (err) {
      console.error(`[CRON] Gagal menghentikan job ${key}:`, err.message);
    }
    delete scheduledJobs[key];
  }

  // 2. Fetch from DB and schedule
  try {
    const configs = await db.getCronConfigs();
    for (const config of configs) {
      const { key, cron_expression, is_active, name, action } = config;
      const runnerKey = action || key;
      const runner = jobRunners[runnerKey];
      
      if (!runner) {
        console.warn(`[CRON] Warning: Runner tidak ditemukan untuk job key "${key}" (action: "${runnerKey}")`);
        continue;
      }
      
      if (is_active === 1 || is_active === true) {
        console.log(`[CRON] Scheduling job: "${name}" (${key}) -> [${cron_expression}]`);
        try {
          scheduledJobs[key] = cron.schedule(cron_expression, () => runner(config), { timezone: TZ_JAKARTA });
        } catch (err) {
          console.error(`[CRON] Gagal menjadwalkan job "${name}" dengan ekspresi [${cron_expression}]:`, err.message);
        }
      } else {
        console.log(`[CRON] Job: "${name}" (${key}) is inactive, skipping.`);
      }
    }
  } catch (err) {
    console.error('[CRON] Error loading cron configs from database:', err.message);
  }
}

export function startCronJobs() {
  console.log('[CRON] Initializing Cron Jobs (WIB Timezone)...');

  // Load dynamic jobs from database
  loadAndScheduleCronJobs();
  
  // Pengecekan & sinkronisasi foto lokal ke Supabase setiap 15 menit
  cron.schedule('*/15 * * * *', async () => {
    try {
      await syncLocalPhotosToSupabase();
    } catch (err) {
      console.error('[CRON] Gagal menjalankan auto-sync foto:', err.message);
    }
  });

  // Backup database harian jam 00:30 WIB
  cron.schedule('30 0 * * *', async () => {
    try {
      await runDatabaseBackup();
    } catch (err) {
      console.error('[CRON] Gagal menjalankan backup database:', err.message);
    }
  }, { timezone: TZ_JAKARTA });
}

// 5. 00:30 WIB — Backup Database Harian
export async function runDatabaseBackup() {
  try {
    console.log('[CRON] Running 00:30 WIB Database Backup...');
    const dbPath = process.env.DB_PATH
      ? path.resolve(process.env.DB_PATH)
      : path.join(__dirname, '..', 'siakanuda.db');
    
    if (!fs.existsSync(dbPath)) {
      console.log('[CRON] Database file not found, skipping backup.');
      return { ok: false, msg: 'Database file not found' };
    }

    const backupDir = path.join(__dirname, '..', 'backups');
    if (!fs.existsSync(backupDir)) {
      fs.mkdirSync(backupDir, { recursive: true });
    }

    const timestamp = new Date().toISOString().replace(/[-:T]/g, '').split('.')[0];
    const backupFile = path.join(backupDir, `siakanuda_${timestamp}.db`);

    // Use WAL checkpoint then copy
    const localDb = db.getDb();
    if (localDb) {
      localDb.pragma('wal_checkpoint(TRUNCATE)');
    }

    fs.copyFileSync(dbPath, backupFile);
    const sizeKB = (fs.statSync(backupFile).size / 1024).toFixed(1);
    console.log(`[CRON] ✓ Backup berhasil: siakanuda_${timestamp}.db (${sizeKB} KB)`);

    // Cleanup old backups (> 7 days)
    const cutoff = Date.now() - 7 * 24 * 60 * 60 * 1000;
    const files = fs.readdirSync(backupDir).filter(f => f.startsWith('siakanuda_') && f.endsWith('.db'));
    let deleted = 0;
    for (const file of files) {
      const filePath = path.join(backupDir, file);
      if (fs.statSync(filePath).mtimeMs < cutoff) {
        fs.unlinkSync(filePath);
        deleted++;
      }
    }
    if (deleted > 0) console.log(`[CRON] ✓ ${deleted} backup lama dihapus (> 7 hari).`);

    return { ok: true, msg: `Backup berhasil: siakanuda_${timestamp}.db`, sizeKB };
  } catch (err) {
    console.error('[CRON] Error in database backup:', err);
    throw err;
  }
}
