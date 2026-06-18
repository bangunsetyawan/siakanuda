/**
 * execution/cron_jobs.js
 * SIAKANUDA v1.0.0 — Tier 3: Cron Jobs for Automation & Warning Escalation
 * Scheduled times: 08:30, 16:00, 19:00, 23:59 (Asia/Jakarta timezone)
 */

import cron from 'node-cron';
import * as db from './db.js';
import { sendMessage } from './bot.js';
import { syncLocalPhotosToSupabase } from './photo_sync.js';
import path from 'path';
import fs from 'fs';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const TZ_JAKARTA = 'Asia/Jakarta';
const SCHOOL_NAME = process.env.SCHOOL_NAME || 'SMK NU Darussalam';
// 1. 08:30 WIB — Early Warning Absensi Kelas
export async function runClassAttendanceCheck() {
  try {
    console.log('[CRON] Running 08:30 WIB Class Attendance Check...');
    const today = new Date().toISOString().split('T')[0];
    const activeTP = await db.getActiveTahunPelajaran();
    const tpId = activeTP ? activeTP.id : 1;

    const groupJid = (process.env.SCHOOL_GROUP_JID || process.env.BROADCAST_GROUP_JID)?.trim() || null;

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
      
      if (groupJid) {
        await sendMessage(groupJid, msg);
        console.log('[CRON] Sent class attendance warnings to Group JID.');
      } else {
        // Fallback: send warning to admins
        const allowed = await db.getAllowedNumbers();
        const admins = allowed.filter(n => n.role === 'admin');
        for (const admin of admins) {
          await sendMessage(admin.phone, msg + '\n\n_(Pesan ini dikirim ke Admin karena SCHOOL_GROUP_JID belum diatur)_');
        }
        console.log('[CRON] Sent class attendance warnings to admins.');
      }
      return { ok: true, msg: 'Peringatan absensi dikirim', missingClasses };
    } else {
      console.log('[CRON] All classes have reported attendance today.');
      return { ok: true, msg: 'Semua kelas sudah lapor absensi hari ini' };
    }
  } catch (err) {
    console.error('[CRON] Error in 08:30 job:', err);
    throw err;
  }
}

// 2. 16:00 WIB — Pengingat Laporan Jurnal PKL (Ketua Kelompok)
export async function runPklReportCheck() {
  try {
    console.log('[CRON] Running 16:00 WIB PKL Report Check...');
    const today = new Date().toISOString().split('T')[0];
    const activeTP = await db.getActiveTahunPelajaran();
    const tpId = activeTP ? activeTP.id : 1;

    // Get all groups
    const allGroups = (await db.getKelompokPkl()).filter(g => g.tahun_pelajaran_id === tpId);
    if (allGroups.length === 0) return { ok: true, msg: 'Tidak ada kelompok PKL terdaftar' };

    // Get reported groups today
    const reportedPkl = (await db.getAttendancePklByDate(today)).filter(r => r.tahun_pelajaran_id === tpId);
    const reportedPhones = reportedPkl.map(r => r.ketua_phone);

    const missingGroups = allGroups.filter(g => !reportedPhones.includes(g.ketua_phone));

    for (const group of missingGroups) {
      const fallbackMsg = `⚠️ *PENGINGAT SIAKANUDA (PKL)*\n\nHalo Ketua Kelompok! Tim Anda di *${group.tempat_pkl}* terpantau belum melaporkan absensi dan jurnal kegiatan hari ini.\n\nSegera laporkan bukti foto dan jurnal kegiatan kelompok Anda via WhatsApp sebelum pukul *19:00 WIB* agar tidak tercatat alpa.\n\n_Ketik menu *4* untuk mulai pelaporan._`;
      const msg = await db.renderTemplate('pkl_report_reminder', {
        tempat_pkl: group.tempat_pkl
      }, fallbackMsg);
      await sendMessage(group.ketua_phone, msg);
      console.log(`[CRON] Sent PKL reminder to Ketua: ${group.ketua_phone} (${group.tempat_pkl})`);
    }
    return { ok: true, msg: `Selesai memeriksa, ${missingGroups.length} pengingat dikirim`, sentCount: missingGroups.length };
  } catch (err) {
    console.error('[CRON] Error in 16:00 job:', err);
    throw err;
  }
}

// 3. 19:00 WIB — Eskalasi Peringatan PKL (Guru Pembimbing)
export async function runPklEscalationCheck() {
  try {
    console.log('[CRON] Running 19:00 WIB PKL Escalation Check...');
    const today = new Date().toISOString().split('T')[0];
    const activeTP = await db.getActiveTahunPelajaran();
    const tpId = activeTP ? activeTP.id : 1;

    const allGroups = (await db.getKelompokPkl()).filter(g => g.tahun_pelajaran_id === tpId);
    if (allGroups.length === 0) return { ok: true, msg: 'Tidak ada kelompok PKL terdaftar' };

    const reportedPkl = (await db.getAttendancePklByDate(today)).filter(r => r.tahun_pelajaran_id === tpId);
    const reportedPhones = reportedPkl.map(r => r.ketua_phone);

    const missingGroups = allGroups.filter(g => !reportedPhones.includes(g.ketua_phone));

    let sentCount = 0;
    for (const group of missingGroups) {
      if (group.pembimbing_phone) {
        const fallbackMsg = `⚠️ *ESKALASI PENGINGAT PKL — SIAKANUDA*\n\nBapak/Ibu Guru Pembimbing, mohon izin menginformasikan bahwa kelompok PKL di *${group.tempat_pkl}* (Ketua: ${group.ketua_phone}) *BELUM* mengirimkan laporan harian hingga pukul 19:00 WIB.\n\nSistem telah mengingatkan ketua kelompok pada pukul 16:00 WIB. Mohon berkenan untuk melakukan konfirmasi/kroscek dengan kelompok siswa bersangkutan.\n\nTerima kasih atas bantuan Bapak/Ibu.`;
        const msg = await db.renderTemplate('pkl_escalation_warning', {
          tempat_pkl: group.tempat_pkl,
          ketua_phone: group.ketua_phone
        }, fallbackMsg);
        await sendMessage(group.pembimbing_phone, msg);
        console.log(`[CRON] Sent PKL escalation to Pembimbing: ${group.pembimbing_phone} for ${group.tempat_pkl}`);
        sentCount++;
      }
    }
    return { ok: true, msg: `Selesai memproses, ${sentCount} eskalasi dikirim`, sentCount };
  } catch (err) {
    console.error('[CRON] Error in 19:00 job:', err);
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

const jobRunners = {
  class_attendance_check: runClassAttendanceCheck,
  pkl_report_check: runPklReportCheck,
  pkl_escalation_check: runPklEscalationCheck,
  auto_alpha_job: runAutoAlphaJob
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
          scheduledJobs[key] = cron.schedule(cron_expression, runner, { timezone: TZ_JAKARTA });
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
