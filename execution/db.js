/**
 * execution/db.js
 * Tier 3 — Database operations adapter.
 * Supports both Supabase (Cloud PostgreSQL) and local SQLite.
 * All functions return Promises to ensure async compatibility.
 */

import Database from 'better-sqlite3';
import { createClient } from '@supabase/supabase-js';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const DB_PATH = process.env.DB_PATH
  ? path.resolve(process.env.DB_PATH)
  : path.join(__dirname, '..', 'siakanuda.db');

export const useSupabase = false; // Force SQLite to match CodeIgniter

let db = null;
export let supabase = null;

if (process.env.SUPABASE_URL && process.env.SUPABASE_KEY) {
  console.log('[DB] Supabase client initialized (only for Storage sync)');
  supabase = createClient(process.env.SUPABASE_URL, process.env.SUPABASE_KEY);
}
  console.log('[DB] Mode: Local SQLite Database');
  db = new Database(DB_PATH);
  db.pragma('journal_mode = DELETE');
  db.pragma('foreign_keys = ON');

// Helper to wrap SQLite sync functions in Promise
export const runAsync = (fn) => {
  return new Promise((resolve, reject) => {
    try {
      resolve(fn());
    } catch (err) {
      reject(err);
    }
  });
};

export function getDb() {
  return db;
}

// ─── Schema Setup ────────────────────────────────────────────────────────────
export async function initSchema() {
  if (useSupabase) {
    // Di Supabase, schema dibuat manual lewat SQL Editor sesuai petunjuk.
    // Kita hanya cek koneksi di sini.
    console.log('[DB] Supabase connection active.');
    return;
  }

  return runAsync(() => {
    db.exec(`
      CREATE TABLE IF NOT EXISTS students (
        id            INTEGER PRIMARY KEY AUTOINCREMENT,
        name          TEXT NOT NULL,
        class         TEXT NOT NULL,
        nis           TEXT UNIQUE,
        gender        TEXT,
        phone         TEXT,
        role          TEXT DEFAULT 'siswa',
        password_hash TEXT,
        first_login   INTEGER DEFAULT 1,
        is_active     INTEGER DEFAULT 1,
        orang_tua_phone TEXT,
        created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
      );

      CREATE TABLE IF NOT EXISTS attendance (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id INTEGER NOT NULL,
        date       DATE NOT NULL DEFAULT (date('now', 'localtime')),
        status     TEXT NOT NULL CHECK(status IN ('alpha','sakit','izin','hadir')),
        note       TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
      );

      CREATE TABLE IF NOT EXISTS violations (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id  INTEGER NOT NULL,
        date        DATE NOT NULL DEFAULT (date('now', 'localtime')),
        category    TEXT NOT NULL,
        description TEXT,
        points      INTEGER NOT NULL DEFAULT 5,
        created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
      );

      CREATE TABLE IF NOT EXISTS counseling (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id INTEGER NOT NULL,
        date       DATE NOT NULL DEFAULT (date('now', 'localtime')),
        type       TEXT NOT NULL DEFAULT 'catatan',
        content    TEXT NOT NULL,
        counselor  TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
      );

      CREATE TABLE IF NOT EXISTS allowed_numbers (
        id            INTEGER PRIMARY KEY AUTOINCREMENT,
        phone         TEXT NOT NULL UNIQUE,
        name          TEXT,
        role          TEXT DEFAULT 'guru',
        lid           TEXT,
        active        INTEGER DEFAULT 1,
        password_hash TEXT,
        first_login   INTEGER DEFAULT 1,
        tugas_tambahan TEXT,
        created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
      );

      CREATE TABLE IF NOT EXISTS message_logs (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        phone      TEXT NOT NULL,
        direction  TEXT NOT NULL,
        message    TEXT NOT NULL,
        ai_action  TEXT,
        status     TEXT DEFAULT 'ok',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
      );

      CREATE TABLE IF NOT EXISTS user_sessions (
        phone      TEXT PRIMARY KEY,
        role       TEXT,
        state      TEXT DEFAULT 'onboarding',
        name       TEXT,
        context    TEXT,
        last_seen  DATETIME DEFAULT CURRENT_TIMESTAMP
      );

      CREATE TABLE IF NOT EXISTS schedules (
        id            INTEGER PRIMARY KEY AUTOINCREMENT,
        day           TEXT NOT NULL,
        class_name    TEXT NOT NULL,
        subject       TEXT NOT NULL,
        teacher_phone TEXT NOT NULL,
        start_time    TEXT,
        end_time      TEXT
      );

      CREATE TABLE IF NOT EXISTS feedbacks (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        date        DATETIME DEFAULT CURRENT_TIMESTAMP,
        sender_name TEXT NOT NULL,
        class_name  TEXT NOT NULL,
        message     TEXT NOT NULL,
        is_public   INTEGER DEFAULT 1
      );

      CREATE TABLE IF NOT EXISTS kelompok_pkl (
        id               INTEGER PRIMARY KEY AUTOINCREMENT,
        tempat_pkl       TEXT NOT NULL,
        ketua_phone      TEXT NOT NULL,
        anggota          TEXT NOT NULL,
        pembimbing_phone TEXT,
        instruktur_phone TEXT
      );

      CREATE TABLE IF NOT EXISTS attendance_pkl (
        id              INTEGER PRIMARY KEY AUTOINCREMENT,
        date            DATE NOT NULL DEFAULT (date('now', 'localtime')),
        tempat_pkl      TEXT NOT NULL,
        ketua_phone     TEXT NOT NULL,
        status_libur    INTEGER DEFAULT 0,
        attendance_data TEXT, -- JSON string
        photo_url       TEXT,
        jurnal_kegiatan TEXT,
        is_takeover     INTEGER DEFAULT 0,
        created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(date, ketua_phone)
      );

      CREATE TABLE IF NOT EXISTS attendance_kelas (
        id              INTEGER PRIMARY KEY AUTOINCREMENT,
        date            DATE NOT NULL DEFAULT (date('now', 'localtime')),
        class_name      TEXT NOT NULL,
        teacher_phone   TEXT NOT NULL,
        attendance_data TEXT, -- JSON string
        created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(date, class_name)
      );

      CREATE TABLE IF NOT EXISTS tahun_pelajaran (
        id              INTEGER PRIMARY KEY AUTOINCREMENT,
        nama            TEXT NOT NULL UNIQUE,
        semester        TEXT NOT NULL DEFAULT 'Ganjil' CHECK(semester IN ('Ganjil', 'Genap')),
        tanggal_mulai   DATE,
        tanggal_selesai  DATE,
        is_active       INTEGER NOT NULL DEFAULT 0,
        created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
      );

      CREATE TABLE IF NOT EXISTS bot_templates (
        key         TEXT PRIMARY KEY,
        name        TEXT DEFAULT '',
        body        TEXT NOT NULL,
        variables   TEXT NOT NULL,
        description TEXT NOT NULL,
        updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP
      );

      CREATE TABLE IF NOT EXISTS cron_configs (
        key             TEXT PRIMARY KEY,
        name            TEXT NOT NULL,
        cron_expression TEXT NOT NULL,
        is_active       INTEGER NOT NULL DEFAULT 1,
        description     TEXT NOT NULL,
        action          TEXT,
        payload         TEXT DEFAULT '{}',
        updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP
      );

      CREATE INDEX IF NOT EXISTS idx_attendance_date ON attendance(date);
      CREATE INDEX IF NOT EXISTS idx_attendance_student ON attendance(student_id);
      CREATE INDEX IF NOT EXISTS idx_violations_student ON violations(student_id);
      CREATE INDEX IF NOT EXISTS idx_students_class ON students(class);
      CREATE INDEX IF NOT EXISTS idx_students_name ON students(name);
      CREATE UNIQUE INDEX IF NOT EXISTS idx_attendance_unique ON attendance(student_id, date);
      CREATE INDEX IF NOT EXISTS idx_schedules_class_day ON schedules(class_name, day);
      CREATE INDEX IF NOT EXISTS idx_schedules_teacher_day ON schedules(teacher_phone, day);
      CREATE INDEX IF NOT EXISTS idx_attendance_pkl_date ON attendance_pkl(date);
      CREATE INDEX IF NOT EXISTS idx_attendance_kelas_date ON attendance_kelas(date);
      CREATE INDEX IF NOT EXISTS idx_kelompok_pkl_ketua ON kelompok_pkl(ketua_phone);

      CREATE TABLE IF NOT EXISTS system_settings (
        key         TEXT PRIMARY KEY,
        value       TEXT NOT NULL,
        updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP
      );
    `);

    // Migrasi SQLite — kolom lama
    try { db.exec(`ALTER TABLE allowed_numbers ADD COLUMN lid TEXT`); } catch (_) {}
    try { db.exec(`ALTER TABLE allowed_numbers ADD COLUMN password_hash TEXT`); } catch (_) {}
    try { db.exec(`ALTER TABLE allowed_numbers ADD COLUMN first_login INTEGER DEFAULT 1`); } catch (_) {}
    try { db.exec(`ALTER TABLE allowed_numbers ADD COLUMN tugas_tambahan TEXT`); } catch (_) {}
    try { db.exec(`ALTER TABLE user_sessions ADD COLUMN context TEXT`); } catch (_) {}
    try { db.exec(`ALTER TABLE user_sessions ADD COLUMN last_seen DATETIME DEFAULT CURRENT_TIMESTAMP`); } catch (_) {}
    try { db.exec(`ALTER TABLE students ADD COLUMN role TEXT DEFAULT 'siswa'`); } catch (_) {}
    try { db.exec(`ALTER TABLE students ADD COLUMN password_hash TEXT`); } catch (_) {}
    try { db.exec(`ALTER TABLE students ADD COLUMN first_login INTEGER DEFAULT 1`); } catch (_) {}
    try { db.exec(`ALTER TABLE students ADD COLUMN is_active INTEGER DEFAULT 1`); } catch (_) {}
    try { db.exec(`ALTER TABLE students ADD COLUMN orang_tua_phone TEXT`); } catch (_) {}
    try { db.exec(`ALTER TABLE violations ADD COLUMN proof_url TEXT`); } catch (_) {}
    try { db.exec(`ALTER TABLE violations ADD COLUMN follow_up TEXT`); } catch (_) {}
    try { db.exec("ALTER TABLE kelompok_pkl ADD COLUMN instruktur_phone TEXT"); } catch (_) {}
    try { db.exec("ALTER TABLE cron_configs ADD COLUMN payload TEXT DEFAULT '{}'"); } catch (_) {}

    // Migrasi v1.6.0 — tambah kolom tahun_pelajaran_id ke 8 tabel transaksional
    try { db.exec(`ALTER TABLE attendance ADD COLUMN tahun_pelajaran_id INTEGER DEFAULT 1`); } catch (_) {}
    try { db.exec(`ALTER TABLE attendance_kelas ADD COLUMN tahun_pelajaran_id INTEGER DEFAULT 1`); } catch (_) {}
    try { db.exec(`ALTER TABLE attendance_pkl ADD COLUMN tahun_pelajaran_id INTEGER DEFAULT 1`); } catch (_) {}
    try { db.exec(`ALTER TABLE kelompok_pkl ADD COLUMN tahun_pelajaran_id INTEGER DEFAULT 1`); } catch (_) {}
    try { db.exec(`ALTER TABLE violations ADD COLUMN tahun_pelajaran_id INTEGER DEFAULT 1`); } catch (_) {}
    try { db.exec(`ALTER TABLE counseling ADD COLUMN tahun_pelajaran_id INTEGER DEFAULT 1`); } catch (_) {}
    try { db.exec(`ALTER TABLE achievements ADD COLUMN tahun_pelajaran_id INTEGER DEFAULT 1`); } catch (_) {}
    try { db.exec(`ALTER TABLE feedbacks ADD COLUMN tahun_pelajaran_id INTEGER DEFAULT 1`); } catch (_) {}

    // Seed default Tahun Pelajaran jika tabel kosong
    const tpCount = db.prepare('SELECT COUNT(*) as c FROM tahun_pelajaran').get().c;
    if (tpCount === 0) {
      const stmt = db.prepare(`INSERT INTO tahun_pelajaran (nama, semester, tanggal_mulai, tanggal_selesai, is_active) VALUES (?, ?, ?, ?, ?)`);
      stmt.run('2024/2025', 'Ganjil', '2024-07-15', '2024-12-20', 1);
      console.log('[DB] Seeded default Tahun Pelajaran: 2024/2025 Ganjil (active)');
    }

    // Seed default system_settings for PKL broadcast targets
    const defaultSettings = [
      { key: 'broadcast_pkl_group', value: '1' },
      { key: 'broadcast_pkl_pembimbing', value: '1' },
      { key: 'broadcast_pkl_orangtua', value: '0' },
      { key: 'broadcast_pkl_instruktur', value: '0' },
      { key: 'broadcast_pkl_anggota', value: '0' }
    ];
    for (const s of defaultSettings) {
      db.prepare(`INSERT OR IGNORE INTO system_settings (key, value) VALUES (?, ?)`).run(s.key, s.value);
    }

    // Seed default bot templates jika empty/missing
    try {
      const templateCount = db.prepare("SELECT COUNT(*) as c FROM bot_templates WHERE key = 'kbm_attendance_broadcast'").get().c;
      if (templateCount === 0) {
        db.prepare(`
          INSERT INTO bot_templates (key, body, variables, description)
          VALUES (?, ?, ?, ?)
        `).run(
          'kbm_attendance_broadcast',
          '📢 *[SIAKANUDA] Laporan Absensi KBM Masuk*\n━━━━━━━━━━━━━━━━━━━━\n📅 *Tanggal:* {tanggal} (Pukul {waktu} WIB)\n🏫 *Kelas:* {kelas}\n👨‍🏫 *Guru Pengabsen:* {guru}\n━━━━━━━━━━━━━━━━━━━━\n📊 *Ringkasan Kehadiran:*\n• Hadir : {hadir} Siswa\n• Sakit : {sakit} Siswa\n• Izin  : {izin} Siswa\n• Alpha : {alpha} Siswa\n━━━━━━━━━━━━━━━━━━━━\n🔗 Detail: {url}',
          'tanggal, waktu, kelas, guru, hadir, sakit, izin, alpha, detail_absen, url',
          'Broadcast notifikasi ketika guru menyimpan absensi kelas KBM.'
        );
        console.log('[DB] Seeded KBM attendance broadcast template.');
      } else {
        // Migration check: update existing template variables to include detail_absen if missing
        const template = db.prepare("SELECT variables FROM bot_templates WHERE key = 'kbm_attendance_broadcast'").get();
        if (template && !template.variables.includes('detail_absen')) {
          db.prepare("UPDATE bot_templates SET variables = 'tanggal, waktu, kelas, guru, hadir, sakit, izin, alpha, detail_absen, url' WHERE key = 'kbm_attendance_broadcast'").run();
          console.log('[DB] Updated existing KBM template variables to include detail_absen.');
        }
      }
    } catch (e) {
      console.error('[DB] Gagal seeding/update template KBM:', e.message);
    }

    // Seed default bot template untuk rekapitulasi konsolidasi KBM jika empty/missing
    try {
      const consolidatedCount = db.prepare("SELECT COUNT(*) as c FROM bot_templates WHERE key = 'kbm_consolidated_recap'").get().c;
      if (consolidatedCount === 0) {
        db.prepare(`
          INSERT INTO bot_templates (key, body, variables, description)
          VALUES (?, ?, ?, ?)
        `).run(
          'kbm_consolidated_recap',
          '📊 *[SIAKANUDA] REKAPITULASI ABSENSI HARIAN KBM SEKOLAH*\n━━━━━━━━━━━━━━━━━━━━\n📅 *Hari/Tanggal:* {tanggal} (Pukul {waktu} WIB)\n🏫 *Sekolah:* {nama_sekolah}\n━━━━━━━━━━━━━━━━━━━━\n*Status Pengisian:* ✅ 100% Terisi (Semua Kelas Sudah Lapor)\n\n*Rekapitulasi per Kelas:*\n{rekap_kelas}\n\n*Akumulasi Total Sekolah:*\n• Total Siswa : {total_siswa} Siswa\n• Total Hadir : {total_hadir} Siswa\n• Total Sakit : {total_sakit} Siswa\n• Total Izin  : {total_izin} Siswa\n• Total Alpha : {total_alpha} Siswa\n━━━━━━━━━━━━━━━━━━━━\n🔗 Detail Laporan Lengkap: {url}',
          'tanggal, waktu, nama_sekolah, rekap_kelas, total_siswa, total_hadir, total_sakit, total_izin, total_alpha, url',
          'Laporan rekapitulasi absensi harian sekolah setelah semua kelas mengisi absen.'
        );
        console.log('[DB] Seeded KBM consolidated recap template.');
      }
    } catch (e) {
      console.error('[DB] Gagal seeding template KBM consolidated:', e.message);
    }

      // Migration: Add name column to bot_templates
      try {
        db.prepare("ALTER TABLE bot_templates ADD COLUMN name TEXT DEFAULT ''").run();
        console.log('[DB] Added name column to bot_templates');
      } catch (err) {
        // Ignore if column already exists
      }

      // Backfill missing names for old templates
      try {
        db.prepare("UPDATE bot_templates SET name = 'Peringatan Absensi Kelas KBM' WHERE key = 'class_attendance_warning' AND name = ''").run();
        db.prepare("UPDATE bot_templates SET name = 'Broadcast Laporan Absensi KBM' WHERE key = 'kbm_attendance_broadcast' AND name = ''").run();
        db.prepare("UPDATE bot_templates SET name = 'Rekapitulasi Absensi KBM' WHERE key = 'kbm_consolidated_recap' AND name = ''").run();
        db.prepare("UPDATE bot_templates SET name = 'Pengingat Laporan PKL' WHERE key = 'pkl_report_reminder' AND name = ''").run();
        db.prepare("UPDATE bot_templates SET name = 'Peringatan Eskalasi PKL' WHERE key = 'pkl_escalation_warning' AND name = ''").run();
        db.prepare("UPDATE bot_templates SET name = 'Pengingat Libur PKL (Lama)' WHERE key = 'pkl_libur_broadcast' AND name = ''").run();
        db.prepare("UPDATE bot_templates SET name = 'Laporan Masuk PKL (Lama)' WHERE key = 'pkl_masuk_broadcast' AND name = ''").run();
      } catch (err) {
        console.error('[DB] Gagal backfill nama template lama:', err.message);
      }

      // Seed 10 PKL templates
      const pklTemplates = [
        // PKL Masuk
        { key: 'pkl_masuk_grup', name: 'PKL Masuk (Grup Sekolah)', body: 'Halo, laporan PKL masuk untuk grup sekolah.\nTanggal: {tanggal}\nTempat: {tempat_pkl}\nDetail: {url}' },
        { key: 'pkl_masuk_pembimbing', name: 'PKL Masuk (Guru Pembimbing)', body: 'Yth. Bapak/Ibu {pembimbing}, laporan PKL baru saja masuk.\nTanggal: {tanggal}\nTempat: {tempat_pkl}\nDetail: {url}' },
        { key: 'pkl_masuk_ortu', name: 'PKL Masuk (Orang Tua)', body: 'Bapak/Ibu Orang Tua, anak Anda telah melaporkan kegiatan PKL hari ini.\nTanggal: {tanggal}\nTempat: {tempat_pkl}\nDetail: {url}' },
        { key: 'pkl_masuk_instruktur', name: 'PKL Masuk (Instruktur)', body: 'Yth. Instruktur DU/DI, laporan kegiatan harian siswa di tempat {tempat_pkl} telah disubmit.\nTanggal: {tanggal}\nDetail: {url}' },
        { key: 'pkl_masuk_siswa', name: 'PKL Masuk (Siswa/Ketua)', body: 'Halo {ketua}, laporan PKL kelompokmu di {tempat_pkl} sudah masuk ke sistem.\nTanggal: {tanggal}\nDetail: {url}' },
        // PKL Libur
        { key: 'pkl_libur_grup', name: 'PKL Libur (Grup Sekolah)', body: 'Info: Kelompok PKL di {tempat_pkl} melaporkan libur pada hari ini ({tanggal}).\nAlasan: {jurnal_lines}' },
        { key: 'pkl_libur_pembimbing', name: 'PKL Libur (Guru Pembimbing)', body: 'Yth. {pembimbing}, kelompok bimbingan Anda di {tempat_pkl} melaporkan libur hari ini.\nAlasan: {jurnal_lines}' },
        { key: 'pkl_libur_ortu', name: 'PKL Libur (Orang Tua)', body: 'Bapak/Ibu Orang Tua, anak Anda melaporkan bahwa tempat PKL sedang libur hari ini.\nAlasan: {jurnal_lines}' },
        { key: 'pkl_libur_instruktur', name: 'PKL Libur (Instruktur)', body: 'Yth. Instruktur DU/DI, kelompok di {tempat_pkl} menginput laporan libur hari ini.\nAlasan: {jurnal_lines}' },
        { key: 'pkl_libur_siswa', name: 'PKL Libur (Siswa/Ketua)', body: 'Halo {ketua}, laporan bahwa tempat PKL kalian ({tempat_pkl}) libur hari ini telah tercatat.' }
      ];

      for (const t of pklTemplates) {
        try {
          const c = db.prepare("SELECT COUNT(*) as c FROM bot_templates WHERE key = ?").get(t.key).c;
          if (c === 0) {
            db.prepare(`
              INSERT INTO bot_templates (key, name, body, variables, description)
              VALUES (?, ?, ?, ?, ?)
            `).run(t.key, t.name, t.body, 'tanggal, waktu, tempat_pkl, pembimbing, ketua, kehadiran, absen_list, jurnal_lines, url', 'Template spesifik per target');
            console.log(`[DB] Seeded PKL template: ${t.key}`);
          } else {
            // Update the name if it was empty
            db.prepare("UPDATE bot_templates SET name = ? WHERE key = ? AND name = ''").run(t.name, t.key);
          }
        } catch (e) {
          console.error(`[DB] Gagal seeding PKL template ${t.key}:`, e.message);
        }
      }

    console.log('[DB] Local SQLite schema initialized');
  });
}

// ─── Students ─────────────────────────────────────────────────────────────────
export async function findStudent({ name, studentClass, nis }) {
  if (useSupabase) {
    if (nis) {
      const { data } = await supabase.from('students').select('*').eq('nis', nis).maybeSingle();
      return data;
    }
    if (name && studentClass) {
      const { data } = await supabase.from('students').select('*').ilike('name', `%${name}%`).eq('class', studentClass).limit(1).maybeSingle();
      return data;
    }
    if (name) {
      const { data } = await supabase.from('students').select('*').ilike('name', `%${name}%`).limit(5);
      if (data && data.length === 1) return data[0];
      return data || [];
    }
    return null;
  }

  return runAsync(() => {
    if (nis) return db.prepare('SELECT * FROM students WHERE nis = ?').get(nis);
    if (name && studentClass) {
      return db.prepare('SELECT * FROM students WHERE name LIKE ? AND class = ? LIMIT 1').get(`%${name}%`, studentClass);
    }
    if (name) {
      const rows = db.prepare('SELECT * FROM students WHERE name LIKE ? LIMIT 5').all(`%${name}%`);
      return rows.length === 1 ? rows[0] : rows;
    }
    return null;
  });
}

export async function getAllStudents() {
  if (useSupabase) {
    const { data } = await supabase.from('students').select('*').order('class').order('name');
    return data || [];
  }
  return runAsync(() => db.prepare('SELECT * FROM students ORDER BY class, name').all());
}

export async function getStudentsByClass(cls) {
  if (useSupabase) {
    const { data } = await supabase.from('students').select('*').eq('class', cls).order('name');
    return data || [];
  }
  return runAsync(() => db.prepare('SELECT * FROM students WHERE class = ? ORDER BY name').all(cls));
}

export async function addStudent({ name, studentClass, nis, gender, phone, orangTuaPhone }) {
  if (useSupabase) {
    const { data, error } = await supabase.from('students').insert({ name, class: studentClass, nis: nis || null, gender: gender || null, phone: phone || null, orang_tua_phone: orangTuaPhone || null }).select().single();
    if (error) throw error;
    return data;
  }
  return runAsync(() => {
    const result = db.prepare('INSERT INTO students (name, class, nis, gender, phone, orang_tua_phone) VALUES (?,?,?,?,?,?)').run(name, studentClass, nis || null, gender || null, phone || null, orangTuaPhone || null);
    return db.prepare('SELECT * FROM students WHERE id = ?').get(result.lastInsertRowid);
  });
}

export async function deleteStudent(id) {
  if (useSupabase) {
    const { error } = await supabase.from('students').delete().eq('id', id);
    if (error) throw error;
    return true;
  }
  return runAsync(() => db.prepare('DELETE FROM students WHERE id = ?').run(id));
}

export async function updateStudent(id, fields) {
  if (useSupabase) {
    const { error } = await supabase.from('students').update(fields).eq('id', id);
    if (error) throw error;
    return true;
  }
  return runAsync(() => {
    const allowedColumns = ['name', 'class', 'nis', 'gender', 'phone', 'orang_tua_phone', 'role', 'password_hash', 'first_login'];
    const safeFields = {};
    for (const [k, v] of Object.entries(fields)) {
      if (allowedColumns.includes(k)) safeFields[k] = v;
    }
    if (Object.keys(safeFields).length === 0) return null;
    const sets = Object.keys(safeFields).map(k => `${k} = ?`).join(', ');
    return db.prepare(`UPDATE students SET ${sets} WHERE id = ?`).run(...Object.values(safeFields), id);
  });
}

// ─── Attendance ───────────────────────────────────────────────────────────────
export async function addAttendance({ studentId, date, status, note, tahun_pelajaran_id }) {
  const d = date || new Date().toISOString().split('T')[0];
  let tpId = tahun_pelajaran_id;
  if (!tpId) {
    const activeTP = await getActiveTahunPelajaran();
    tpId = activeTP ? activeTP.id : 1;
  }
  if (useSupabase) {
    const { error } = await supabase.from('attendance').upsert({ student_id: studentId, date: d, status, note: note || null, tahun_pelajaran_id: tpId }, { onConflict: 'student_id,date' });
    if (error) throw error;
    return true;
  }
  return runAsync(() => {
    return db.prepare(`
      INSERT INTO attendance (student_id, date, status, note, tahun_pelajaran_id)
      VALUES (?, ?, ?, ?, ?)
      ON CONFLICT(student_id, date) DO UPDATE SET
        status = excluded.status,
        note = excluded.note
    `).run(studentId, d, status, note || null, tpId);
  });
}

export async function getAttendanceToday() {
  const today = new Date().toISOString().split('T')[0];
  if (useSupabase) {
    const { data } = await supabase.from('attendance').select('*, students(name, class)').eq('date', today);
    return (data || []).map(item => ({
      ...item,
      name: item.students?.name,
      class: item.students?.class
    }));
  }
  return runAsync(() => db.prepare(`
    SELECT a.*, s.name, s.class FROM attendance a
    JOIN students s ON s.id = a.student_id
    WHERE a.date = ?
    ORDER BY s.class, s.name
  `).all(today));
}

export async function getAttendanceByDate(date) {
  if (useSupabase) {
    const { data } = await supabase.from('attendance').select('*, students(name, class)').eq('date', date);
    return (data || []).map(item => ({
      ...item,
      name: item.students?.name,
      class: item.students?.class
    }));
  }
  return runAsync(() => db.prepare(`
    SELECT a.*, s.name, s.class FROM attendance a
    JOIN students s ON s.id = a.student_id
    WHERE a.date = ?
    ORDER BY s.class, s.name
  `).all(date));
}

export async function getAttendanceByStudent(studentId) {
  if (useSupabase) {
    const { data } = await supabase.from('attendance').select('*').eq('student_id', studentId).order('date', { ascending: false }).limit(30);
    return data || [];
  }
  return runAsync(() => db.prepare('SELECT * FROM attendance WHERE student_id = ? ORDER BY date DESC LIMIT 30').all(studentId));
}

export async function getAttendanceSummary(period = 'month') {
  if (useSupabase) {
    let dateFilter = null;
    let limitDate = null;
    if (period === 'today') {
      dateFilter = new Date().toISOString().split('T')[0];
    } else if (period === 'week') {
      const d = new Date();
      d.setDate(d.getDate() - 7);
      limitDate = d.toISOString().split('T')[0];
    } else if (period === 'month') {
      const d = new Date();
      d.setDate(d.getDate() - 30);
      limitDate = d.toISOString().split('T')[0];
    }

    const { data: students } = await supabase.from('students').select('id, name, class');
    let query = supabase.from('attendance').select('student_id, status');
    if (period === 'today') {
      query = query.eq('date', dateFilter);
    } else if (limitDate) {
      query = query.gte('date', limitDate);
    }
    const { data: att } = await query;

    const attMap = {};
    (att || []).forEach(a => {
      if (!attMap[a.student_id]) attMap[a.student_id] = { alpha: 0, sakit: 0, izin: 0 };
      if (a.status === 'alpha') attMap[a.student_id].alpha++;
      if (a.status === 'sakit') attMap[a.student_id].sakit++;
      if (a.status === 'izin') attMap[a.student_id].izin++;
    });

    const summary = (students || []).map(s => {
      const counts = attMap[s.id] || { alpha: 0, sakit: 0, izin: 0 };
      return {
        name: s.name,
        class: s.class,
        alpha: counts.alpha,
        sakit: counts.sakit,
        izin: counts.izin
      };
    });

    summary.sort((a, b) => b.alpha - a.alpha);
    return summary;
  }

  return runAsync(() => {
    let dateFilter = '';
    if (period === 'today') dateFilter = "AND a.date = date('now', 'localtime')";
    if (period === 'week')  dateFilter = "AND a.date >= date('now', 'localtime', '-7 days')";
    if (period === 'month') dateFilter = "AND a.date >= date('now', 'localtime', '-30 days')";

    return db.prepare(`
      SELECT s.name, s.class,
        SUM(CASE WHEN a.status='alpha' THEN 1 ELSE 0 END) as alpha,
        SUM(CASE WHEN a.status='sakit' THEN 1 ELSE 0 END) as sakit,
        SUM(CASE WHEN a.status='izin'  THEN 1 ELSE 0 END) as izin
      FROM students s
      LEFT JOIN attendance a ON a.student_id = s.id ${dateFilter}
      GROUP BY s.id ORDER BY alpha DESC
    `).all();
  });
}

// ─── Violations ───────────────────────────────────────────────────────────────
export async function addViolation({ studentId, date, category, description, points, proof_url, follow_up, tahun_pelajaran_id }) {
  const d = date || new Date().toISOString().split('T')[0];
  let tpId = tahun_pelajaran_id;
  if (!tpId) {
    const activeTP = await getActiveTahunPelajaran();
    tpId = activeTP ? activeTP.id : 1;
  }
  if (useSupabase) {
    const { error } = await supabase.from('violations').insert({
      student_id: studentId, date: d, category, description: description || null,
      points: points || 5, proof_url: proof_url || null, follow_up: follow_up || null,
      tahun_pelajaran_id: tpId
    });
    if (error) throw error;
    return true;
  }
  return runAsync(() => {
    return db.prepare(
      `INSERT INTO violations (student_id, date, category, description, points, proof_url, follow_up, tahun_pelajaran_id)
       VALUES (?,?,?,?,?,?,?,?)`
    ).run(studentId, d, category, description || null, points || 5, proof_url || null, follow_up || null, tpId);
  });
}

export async function getViolationsByStudent(studentId) {
  if (useSupabase) {
    const { data } = await supabase.from('violations').select('*').eq('student_id', studentId).order('date', { ascending: false });
    return data || [];
  }
  return runAsync(() => db.prepare('SELECT * FROM violations WHERE student_id = ? ORDER BY date DESC').all(studentId));
}

export async function getTotalPoints(studentId) {
  if (useSupabase) {
    const { data } = await supabase.from('violations').select('points').eq('student_id', studentId);
    return (data || []).reduce((sum, v) => sum + (v.points || 0), 0);
  }
  return runAsync(() => {
    const row = db.prepare('SELECT SUM(points) as total FROM violations WHERE student_id = ?').get(studentId);
    return row?.total || 0;
  });
}

export async function getTopViolators(limit = 10) {
  if (useSupabase) {
    const { data: viol } = await supabase.from('violations').select('student_id, points');
    const { data: stud } = await supabase.from('students').select('id, name, class');
    
    const studMap = {};
    (stud || []).forEach(s => { studMap[s.id] = s; });

    const counts = {};
    (viol || []).forEach(v => {
      if (!counts[v.student_id]) counts[v.student_id] = { total_points: 0, count: 0 };
      counts[v.student_id].total_points += (v.points || 0);
      counts[v.student_id].count++;
    });

    return Object.keys(counts).map(sid => {
      const student = studMap[sid] || { name: 'Unknown', class: 'Unknown' };
      return {
        name: student.name,
        class: student.class,
        total_points: counts[sid].total_points,
        count: counts[sid].count
      };
    }).sort((a, b) => b.total_points - a.total_points).slice(0, limit);
  }

  return runAsync(() => db.prepare(`
    SELECT s.name, s.class, SUM(v.points) as total_points, COUNT(*) as count
    FROM violations v
    JOIN students s ON s.id = v.student_id
    GROUP BY v.student_id ORDER BY total_points DESC LIMIT ?
  `).all(limit));
}

// ─── Counseling ───────────────────────────────────────────────────────────────
export async function addCounseling({ studentId, date, type, content, counselor, tahun_pelajaran_id }) {
  const d = date || new Date().toISOString().split('T')[0];
  let tpId = tahun_pelajaran_id;
  if (!tpId) {
    const activeTP = await getActiveTahunPelajaran();
    tpId = activeTP ? activeTP.id : 1;
  }
  if (useSupabase) {
    const { error } = await supabase.from('counseling').insert({
      student_id: studentId, date: d, type: type || 'catatan', content,
      counselor: counselor || null, tahun_pelajaran_id: tpId
    });
    if (error) throw error;
    return true;
  }
  return runAsync(() => {
    return db.prepare(
      `INSERT INTO counseling (student_id, date, type, content, counselor, tahun_pelajaran_id)
       VALUES (?,?,?,?,?,?)`
    ).run(studentId, d, type || 'catatan', content, counselor || null, tpId);
  });
}

export async function getCounselingByStudent(studentId) {
  if (useSupabase) {
    const { data } = await supabase.from('counseling').select('*').eq('student_id', studentId).order('date', { ascending: false });
    return data || [];
  }
  return runAsync(() => db.prepare('SELECT * FROM counseling WHERE student_id = ? ORDER BY date DESC').all(studentId));
}

export async function getRecentCounseling(limit = 20) {
  if (useSupabase) {
    const { data } = await supabase.from('counseling').select('*, students(name, class)').order('created_at', { ascending: false }).limit(limit);
    return (data || []).map(item => ({
      ...item,
      name: item.students?.name,
      class: item.students?.class
    }));
  }
  return runAsync(() => db.prepare(`
    SELECT c.*, s.name, s.class FROM counseling c
    JOIN students s ON s.id = c.student_id
    ORDER BY c.created_at DESC LIMIT ?
  `).all(limit));
}

// ─── Allowed Numbers ─────────────────────────────────────────────────────────
export async function getAllowedNumbers() {
  if (useSupabase) {
    const { data } = await supabase.from('allowed_numbers').select('*').eq('active', true);
    return data || [];
  }
  return runAsync(() => db.prepare('SELECT * FROM allowed_numbers WHERE active = 1').all());
}

export async function getAllowedByRole(role) {
  if (useSupabase) {
    const { data } = await supabase.from('allowed_numbers').select('*').eq('active', true).eq('role', role);
    return data || [];
  }
  return runAsync(() => db.prepare('SELECT * FROM allowed_numbers WHERE active = 1 AND role = ?').all(role));
}

export async function addAllowedNumber({ phone, name, role }) {
  if (useSupabase) {
    const { error } = await supabase.from('allowed_numbers').upsert({ phone, name: name || null, role: role || 'guru', active: true }, { onConflict: 'phone' });
    if (error) throw error;
    return true;
  }
  return runAsync(() => {
    return db.prepare(
      `INSERT INTO allowed_numbers (phone, name, role) VALUES (?,?,?)
       ON CONFLICT(phone) DO UPDATE SET active=1, name=excluded.name, role=excluded.role`
    ).run(phone, name || null, role || 'guru');
  });
}

export async function removeAllowedNumber(phone) {
  if (useSupabase) {
    const { error } = await supabase.from('allowed_numbers').update({ active: false }).eq('phone', phone);
    if (error) throw error;
    return true;
  }
  return runAsync(() => db.prepare('UPDATE allowed_numbers SET active=0 WHERE phone=?').run(phone));
}

export async function isNumberAllowed(phone) {
  if (useSupabase) {
    const { data } = await supabase.from('allowed_numbers').select('id').eq('phone', phone).eq('active', true).maybeSingle();
    return !!data;
  }
  return runAsync(() => {
    const row = db.prepare('SELECT id FROM allowed_numbers WHERE phone=? AND active=1').get(phone);
    return !!row;
  });
}

export async function updateLidForPhone(phone, lid) {
  if (useSupabase) {
    const { error } = await supabase.from('allowed_numbers').update({ lid }).eq('phone', phone);
    if (error) throw error;
    return true;
  }
  return runAsync(() => db.prepare('UPDATE allowed_numbers SET lid=? WHERE phone=?').run(lid, phone));
}

export async function findByLid(lid) {
  if (useSupabase) {
    const { data } = await supabase.from('allowed_numbers').select('*').eq('lid', lid).eq('active', true).maybeSingle();
    return data;
  }
  return runAsync(() => db.prepare('SELECT * FROM allowed_numbers WHERE lid=? AND active=1').get(lid));
}

// ─── Message Logs ─────────────────────────────────────────────────────────────
export async function logMessage({ phone, direction, message, aiAction, status }) {
  if (useSupabase) {
    const { error } = await supabase.from('message_logs').insert({ phone, direction, message, ai_action: aiAction || null, status: status || 'ok' });
    if (error) throw error;
    return true;
  }
  return runAsync(() => {
    return db.prepare(
      `INSERT INTO message_logs (phone, direction, message, ai_action, status)
       VALUES (?,?,?,?,?)`
    ).run(phone, direction, message, aiAction ? JSON.stringify(aiAction) : null, status || 'ok');
  });
}

export async function getRecentLogs(limit = 50) {
  if (useSupabase) {
    const { data } = await supabase.from('message_logs').select('*').order('created_at', { ascending: false }).limit(limit);
    return data || [];
  }
  return runAsync(() => db.prepare('SELECT * FROM message_logs ORDER BY created_at DESC LIMIT ?').all(limit));
}

// Local cache untuk performa WhatsApp bot instan (0ms database delay)
const sessionCache = new Map();

export async function getSession(phone) {
  if (sessionCache.has(phone)) {
    return sessionCache.get(phone);
  }
  let session = null;
  if (useSupabase) {
    const { data } = await supabase.from('user_sessions').select('*').eq('phone', phone).maybeSingle();
    session = data;
  } else {
    session = await runAsync(() => {
      const row = db.prepare('SELECT * FROM user_sessions WHERE phone = ?').get(phone);
      if (row && row.context) {
        try { row.context = JSON.parse(row.context); } catch (_) { row.context = null; }
      }
      return row;
    });
  }
  if (session) {
    if (useSupabase && typeof session.context === 'string') {
      try { session.context = JSON.parse(session.context || '{}'); } catch (_) { session.context = {}; }
    }
    sessionCache.set(phone, session);
  }
  return session;
}

export async function setSession(phone, { role, state, name, context }) {
  const updatedSession = {
    phone,
    role: role || null,
    state: state || 'onboarding',
    name: name || null,
    context: context || null,
    last_seen: new Date().toISOString()
  };

  // 1. Simpan di RAM secara instan
  sessionCache.set(phone, updatedSession);

  // 2. Sinkronkan ke database di latar belakang (tanpa await)
  if (useSupabase) {
    supabase.from('user_sessions')
      .upsert(updatedSession, { onConflict: 'phone' })
      .then(({ error }) => {
        if (error) console.error(`[DB Cache Sync] Gagal sinkronisasi sesi ke Supabase untuk ${phone}:`, error.message);
      });
  } else {
    const contextStr = context ? JSON.stringify(context) : null;
    runAsync(() => {
      try {
        db.prepare(`
          INSERT INTO user_sessions (phone, role, state, name, context, last_seen)
          VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
          ON CONFLICT(phone) DO UPDATE SET
            role = excluded.role,
            state = excluded.state,
            name = COALESCE(excluded.name, user_sessions.name),
            context = excluded.context,
            last_seen = CURRENT_TIMESTAMP
        `).run(phone, role || null, state || 'onboarding', name || null, contextStr);
      } catch (e) {
        console.error(`[DB Cache Sync] Gagal sinkronisasi sesi ke SQLite untuk ${phone}:`, e.message);
      }
    });
  }
  return true;
}

export async function clearSession(phone) {
  // Hapus dari RAM cache
  sessionCache.delete(phone);

  if (useSupabase) {
    supabase.from('user_sessions')
      .delete()
      .eq('phone', phone)
      .then(({ error }) => {
        if (error) console.error(`[DB Cache Sync] Gagal hapus sesi di Supabase untuk ${phone}:`, error.message);
      });
  } else {
    runAsync(() => db.prepare('DELETE FROM user_sessions WHERE phone = ?').run(phone));
  }
  return true;
}

// ─── Stats ────────────────────────────────────────────────────────────────────
export async function getDashboardStats() {
  if (useSupabase) {
    const today = new Date().toISOString().split('T')[0];
    const [
      studentsRes,
      siswaPKLRes,
      teachersRes,
      adminsRes,
      absenRes,
      violationsRes,
      counselingRes
    ] = await Promise.all([
      supabase.from('students').select('*', { count: 'exact', head: true }),
      supabase.from('students').select('*', { count: 'exact', head: true }).eq('role', 'siswa-pkl'),
      supabase.from('allowed_numbers').select('*', { count: 'exact', head: true }).eq('active', true).in('role', ['admin','kepsek','guru_mapel','guru_bk']),
      supabase.from('allowed_numbers').select('*', { count: 'exact', head: true }).eq('active', true).eq('role', 'admin'),
      supabase.from('attendance').select('*', { count: 'exact', head: true }).eq('date', today).neq('status', 'hadir'),
      supabase.from('violations').select('*', { count: 'exact', head: true }),
      supabase.from('counseling').select('*', { count: 'exact', head: true })
    ]);
    
    return {
      totalStudents: studentsRes.count || 0,
      totalSiswaPKL: siswaPKLRes.count || 0,
      totalTeachers: teachersRes.count || 0,
      totalAdmins: adminsRes.count || 0,
      absenToday: absenRes.count || 0,
      totalViolations: violationsRes.count || 0,
      totalCounseling: counselingRes.count || 0,
    };
  }

  return runAsync(() => {
    const today = new Date().toISOString().split('T')[0];
    return {
      totalStudents:   db.prepare('SELECT COUNT(*) as c FROM students').get().c,
      totalSiswaPKL:   db.prepare("SELECT COUNT(*) as c FROM students WHERE role='siswa-pkl'").get().c,
      totalTeachers:   db.prepare("SELECT COUNT(*) as c FROM allowed_numbers WHERE active=1 AND role IN ('admin','kepsek','guru_mapel','guru_bk')").get().c,
      totalAdmins:     db.prepare("SELECT COUNT(*) as c FROM allowed_numbers WHERE active=1 AND role='admin'").get().c,
      absenToday:      db.prepare(`SELECT COUNT(*) as c FROM attendance WHERE date=? AND status!='hadir'`).get(today).c,
      totalViolations: db.prepare('SELECT COUNT(*) as c FROM violations').get().c,
      totalCounseling: db.prepare('SELECT COUNT(*) as c FROM counseling').get().c,
    };
  });
}

export async function getAttendanceStatsForClass(className, date) {
  if (useSupabase) {
    const [totalStudentsRes, recordsRes] = await Promise.all([
      supabase.from('students').select('*', { count: 'exact', head: true }).eq('class', className),
      supabase.from('attendance').select('status, students!inner(class)').eq('date', date).eq('students.class', className)
    ]);
    const totalStudents = totalStudentsRes.count || 0;
    const records = recordsRes.data || [];
    
    const counts = { sakit: 0, izin: 0, alpha: 0, hadir: 0 };
    (records || []).forEach(r => {
      if (r.status === 'sakit') counts.sakit++;
      if (r.status === 'izin') counts.izin++;
      if (r.status === 'alpha') counts.alpha++;
    });
    counts.hadir = (totalStudents || 0) - (counts.sakit + counts.izin + counts.alpha);
    return { total: totalStudents || 0, hadir: counts.hadir, sakit: counts.sakit, izin: counts.izin, alpha: counts.alpha };
  }

  return runAsync(() => {
    const totalStudents = db.prepare('SELECT COUNT(*) as c FROM students WHERE class = ?').get(className).c;
    const records = db.prepare(`
      SELECT a.status, COUNT(*) as c
      FROM attendance a
      JOIN students s ON s.id = a.student_id
      WHERE s.class = ? AND a.date = ?
      GROUP BY a.status
    `).all(className, date);
    
    let sakit = 0, izin = 0, alpha = 0;
    records.forEach(r => {
      if (r.status === 'sakit') sakit = r.c;
      if (r.status === 'izin') izin = r.c;
      if (r.status === 'alpha') alpha = r.c;
    });
    
    const hadir = totalStudents - (sakit + izin + alpha);
    return { total: totalStudents, hadir, sakit, izin, alpha };
  });
}

export async function getAttendanceStatsOverall(date) {
  if (useSupabase) {
    const { count: totalStudents } = await supabase.from('students').select('*', { count: 'exact', head: true });
    const { data: records } = await supabase.from('attendance').select('status').eq('date', date);
    
    const counts = { sakit: 0, izin: 0, alpha: 0 };
    (records || []).forEach(r => {
      if (r.status === 'sakit') counts.sakit++;
      if (r.status === 'izin') counts.izin++;
      if (r.status === 'alpha') counts.alpha++;
    });
    const totalAbsent = counts.sakit + counts.izin + counts.alpha;
    const hadir = (totalStudents || 0) - totalAbsent;
    return { total: totalStudents || 0, hadir, absent: totalAbsent, sakit: counts.sakit, izin: counts.izin, alpha: counts.alpha };
  }

  return runAsync(() => {
    const totalStudents = db.prepare('SELECT COUNT(*) as c FROM students').get().c;
    const records = db.prepare(`
      SELECT status, COUNT(*) as c
      FROM attendance
      WHERE date = ?
      GROUP BY status
    `).all(date);
    
    let sakit = 0, izin = 0, alpha = 0;
    records.forEach(r => {
      if (r.status === 'sakit') sakit = r.c;
      if (r.status === 'izin') izin = r.c;
      if (r.status === 'alpha') alpha = r.c;
    });
    
    const totalAbsent = sakit + izin + alpha;
    const hadir = totalStudents - totalAbsent;
    return { total: totalStudents, hadir, absent: totalAbsent, sakit, izin, alpha };
  });
}

export async function getViolation(id) {
  if (useSupabase) {
    const { data } = await supabase.from('violations').select('*, students(name, class)').eq('id', id).maybeSingle();
    if (!data) return null;
    return {
      ...data,
      name: data.students?.name,
      class: data.students?.class
    };
  }

  return runAsync(() => {
    return db.prepare(`
      SELECT v.*, s.name, s.class
      FROM violations v
      JOIN students s ON s.id = v.student_id
      WHERE v.id = ?
    `).get(id);
  });
}

export async function deleteViolation(id) {
  if (useSupabase) {
    const { error } = await supabase.from('violations').delete().eq('id', id);
    if (error) throw error;
    return true;
  }
  return runAsync(() => db.prepare('DELETE FROM violations WHERE id = ?').run(id));
}

export async function updateAllowedNumber(oldPhone, { phone, name, role }) {
  if (useSupabase) {
    const { error } = await supabase.from('allowed_numbers').update({ phone, name, role }).eq('phone', oldPhone);
    if (error) throw error;
    return true;
  }
  return runAsync(() => {
    return db.prepare('UPDATE allowed_numbers SET phone = ?, name = ?, role = ? WHERE phone = ?').run(phone, name, role, oldPhone);
  });
}

export async function getRecentViolations(limit = 50) {
  if (useSupabase) {
    const { data } = await supabase.from('violations').select('*, students(name, class)').order('created_at', { ascending: false }).limit(limit);
    return (data || []).map(item => ({
      ...item,
      name: item.students?.name,
      class: item.students?.class
    }));
  }
  return runAsync(() => {
    return db.prepare(`
      SELECT v.*, s.name, s.class
      FROM violations v
      JOIN students s ON s.id = v.student_id
      ORDER BY v.created_at DESC LIMIT ?
    `).all(limit);
  });
}

export async function deleteAttendance(id) {
  if (useSupabase) {
    const { error } = await supabase.from('attendance').delete().eq('id', id);
    if (error) throw error;
    return true;
  }
  return runAsync(() => db.prepare('DELETE FROM attendance WHERE id = ?').run(id));
}

export async function deleteCounseling(id) {
  if (useSupabase) {
    const { error } = await supabase.from('counseling').delete().eq('id', id);
    if (error) throw error;
    return true;
  }
  return runAsync(() => db.prepare('DELETE FROM counseling WHERE id = ?').run(id));
}

// ─── schedules ────────────────────────────────────────────────────────────────
export async function getSchedules() {
  if (useSupabase) {
    const { data } = await supabase.from('schedules').select('*').order('day').order('start_time');
    return data || [];
  }
  return runAsync(() => db.prepare('SELECT * FROM schedules ORDER BY day, start_time').all());
}

export async function getScheduleByClassAndDay(className, day) {
  if (useSupabase) {
    const { data } = await supabase.from('schedules').select('*').eq('class_name', className).eq('day', day).order('start_time');
    return data || [];
  }
  return runAsync(() => db.prepare('SELECT * FROM schedules WHERE class_name = ? AND day = ? ORDER BY start_time').all(className, day));
}

export async function getScheduleByTeacherAndDay(teacherPhone, day) {
  if (useSupabase) {
    const { data } = await supabase.from('schedules').select('*').eq('teacher_phone', teacherPhone).eq('day', day).order('start_time');
    return data || [];
  }
  return runAsync(() => db.prepare('SELECT * FROM schedules WHERE teacher_phone = ? AND day = ? ORDER BY start_time').all(teacherPhone, day));
}

export async function getScheduleByClass(className) {
  if (useSupabase) {
    const { data } = await supabase.from('schedules').select('*').eq('class_name', className).order('day').order('start_time');
    return data || [];
  }
  return runAsync(() => db.prepare('SELECT * FROM schedules WHERE class_name = ? ORDER BY day, start_time').all(className));
}

export async function getScheduleByTeacher(teacherPhone) {
  if (useSupabase) {
    const { data } = await supabase.from('schedules').select('*').eq('teacher_phone', teacherPhone).order('day').order('start_time');
    return data || [];
  }
  return runAsync(() => db.prepare('SELECT * FROM schedules WHERE teacher_phone = ? ORDER BY day, start_time').all(teacherPhone));
}

export async function addSchedule({ day, className, subject, teacherPhone, startTime, endTime }) {
  if (useSupabase) {
    const { data, error } = await supabase.from('schedules').insert({
      day, class_name: className, subject, teacher_phone: teacherPhone, start_time: startTime || null, end_time: endTime || null
    }).select().single();
    if (error) throw error;
    return data;
  }
  return runAsync(() => {
    const result = db.prepare('INSERT INTO schedules (day, class_name, subject, teacher_phone, start_time, end_time) VALUES (?,?,?,?,?,?)')
      .run(day, className, subject, teacherPhone, startTime || null, endTime || null);
    return db.prepare('SELECT * FROM schedules WHERE id = ?').get(result.lastInsertRowid);
  });
}

export async function deleteSchedule(id) {
  if (useSupabase) {
    const { error } = await supabase.from('schedules').delete().eq('id', id);
    if (error) throw error;
    return true;
  }
  return runAsync(() => db.prepare('DELETE FROM schedules WHERE id = ?').run(id));
}

export async function updateSchedule(id, { day, className, subject, teacherPhone, startTime, endTime }) {
  if (useSupabase) {
    const { error } = await supabase.from('schedules').update({
      day, class_name: className, subject, teacher_phone: teacherPhone, start_time: startTime || null, end_time: endTime || null
    }).eq('id', id);
    if (error) throw error;
    return true;
  }
  return runAsync(() => {
    return db.prepare('UPDATE schedules SET day = ?, class_name = ?, subject = ?, teacher_phone = ?, start_time = ?, end_time = ? WHERE id = ?')
      .run(day, className, subject, teacherPhone, startTime || null, endTime || null, id);
  });
}

// ─── feedbacks (Kotak Suara) ──────────────────────────────────────────────────
export async function getFeedbacks(limit = 50) {
  if (useSupabase) {
    const { data } = await supabase.from('feedbacks').select('*').order('date', { ascending: false }).limit(limit);
    return data || [];
  }
  return runAsync(() => db.prepare('SELECT * FROM feedbacks ORDER BY date DESC LIMIT ?').all(limit));
}

export async function addFeedback({ senderName, className, message, isPublic = true, tahun_pelajaran_id }) {
  const isPub = typeof isPublic === 'boolean' ? isPublic : isPublic === 1 || isPublic === 'true';
  let tpId = tahun_pelajaran_id;
  if (!tpId) {
    const activeTP = await getActiveTahunPelajaran();
    tpId = activeTP ? activeTP.id : 1;
  }
  if (useSupabase) {
    const { data, error } = await supabase.from('feedbacks').insert({
      sender_name: senderName, class_name: className, message, is_public: isPub, tahun_pelajaran_id: tpId
    }).select().single();
    if (error) throw error;
    return data;
  }
  return runAsync(() => {
    const result = db.prepare('INSERT INTO feedbacks (sender_name, class_name, message, is_public, tahun_pelajaran_id) VALUES (?,?,?,?,?)')
      .run(senderName, className, message, isPub ? 1 : 0, tpId);
    return db.prepare('SELECT * FROM feedbacks WHERE id = ?').get(result.lastInsertRowid);
  });
}

export async function deleteFeedback(id) {
  if (useSupabase) {
    const { error } = await supabase.from('feedbacks').delete().eq('id', id);
    if (error) throw error;
    return true;
  }
  return runAsync(() => db.prepare('DELETE FROM feedbacks WHERE id = ?').run(id));
}

export async function hideFeedback(id, isPublic) {
  const isPub = typeof isPublic === 'boolean' ? isPublic : isPublic === 1 || isPublic === 'true';
  if (useSupabase) {
    const { error } = await supabase.from('feedbacks').update({ is_public: isPub }).eq('id', id);
    if (error) throw error;
    return true;
  }
  return runAsync(() => db.prepare('UPDATE feedbacks SET is_public = ? WHERE id = ?').run(isPub ? 1 : 0, id));
}

// ─── kelompok_pkl ─────────────────────────────────────────────────────────────
export async function getKelompokPkl() {
  if (useSupabase) {
    const { data } = await supabase.from('kelompok_pkl').select('*').order('tempat_pkl');
    return data || [];
  }
  return runAsync(() => db.prepare('SELECT * FROM kelompok_pkl ORDER BY tempat_pkl').all());
}

export async function getKelompokPklByKetua(ketuaPhone) {
  if (useSupabase) {
    const { data } = await supabase.from('kelompok_pkl').select('*').eq('ketua_phone', ketuaPhone).maybeSingle();
    return data;
  }
  return runAsync(() => db.prepare('SELECT * FROM kelompok_pkl WHERE ketua_phone = ?').get(ketuaPhone));
}

export async function addKelompokPkl({ tempatPkl, ketuaPhone, anggota, pembimbingPhone, instrukturPhone, tahun_pelajaran_id }) {
  let tpId = tahun_pelajaran_id;
  if (!tpId) {
    const activeTP = await getActiveTahunPelajaran();
    tpId = activeTP ? activeTP.id : 1;
  }
  if (useSupabase) {
    const { data, error } = await supabase.from('kelompok_pkl').insert({
      tempat_pkl: tempatPkl, ketua_phone: ketuaPhone, anggota, pembimbing_phone: pembimbingPhone || null, instruktur_phone: instrukturPhone || null, tahun_pelajaran_id: tpId
    }).select().single();
    if (error) throw error;
    return data;
  }
  return runAsync(() => {
    const result = db.prepare('INSERT INTO kelompok_pkl (tempat_pkl, ketua_phone, anggota, pembimbing_phone, instruktur_phone, tahun_pelajaran_id) VALUES (?,?,?,?,?,?)')
      .run(tempatPkl, ketuaPhone, anggota, pembimbingPhone || null, instrukturPhone || null, tpId);
    return db.prepare('SELECT * FROM kelompok_pkl WHERE id = ?').get(result.lastInsertRowid);
  });
}

export async function deleteKelompokPkl(id) {
  if (useSupabase) {
    const { error } = await supabase.from('kelompok_pkl').delete().eq('id', id);
    if (error) throw error;
    return true;
  }
  return runAsync(() => db.prepare('DELETE FROM kelompok_pkl WHERE id = ?').run(id));
}

export async function updateKelompokPkl(id, fields) {
  const supabaseFields = {};
  if (fields.tempatPkl !== undefined) supabaseFields.tempat_pkl = fields.tempatPkl;
  if (fields.ketuaPhone !== undefined) supabaseFields.ketua_phone = fields.ketuaPhone;
  if (fields.anggota !== undefined) supabaseFields.anggota = fields.anggota;
  if (fields.pembimbingPhone !== undefined) supabaseFields.pembimbing_phone = fields.pembimbingPhone;
  if (fields.instrukturPhone !== undefined) supabaseFields.instruktur_phone = fields.instrukturPhone;

  if (useSupabase) {
    const { error } = await supabase.from('kelompok_pkl').update(supabaseFields).eq('id', id);
    if (error) throw error;
    return true;
  }
  return runAsync(() => {
    const allowedColumns = ['tempat_pkl', 'ketua_phone', 'anggota', 'pembimbing_phone', 'instruktur_phone'];
    const safeFields = {};
    for (const [k, v] of Object.entries(supabaseFields)) {
      if (allowedColumns.includes(k)) safeFields[k] = v;
    }
    if (Object.keys(safeFields).length === 0) return null;
    const sets = Object.keys(safeFields).map(k => `${k} = ?`).join(', ');
    return db.prepare(`UPDATE kelompok_pkl SET ${sets} WHERE id = ?`).run(...Object.values(safeFields), id);
  });
}

// ─── attendance_pkl ───────────────────────────────────────────────────────────
export async function addAttendancePkl({ date, tempatPkl, ketuaPhone, statusLibur, attendanceData, photoUrl, jurnalKegiatan, isTakeover, tahun_pelajaran_id }) {
  const d = date || new Date().toISOString().split('T')[0];
  const isLib = typeof statusLibur === 'boolean' ? statusLibur : statusLibur === 1 || statusLibur === 'true';
  const isTake = typeof isTakeover === 'boolean' ? isTakeover : isTakeover === 1 || isTakeover === 'true';
  const attData = typeof attendanceData === 'string' ? attendanceData : JSON.stringify(attendanceData);
  let tpId = tahun_pelajaran_id;
  if (!tpId) {
    const activeTP = await getActiveTahunPelajaran();
    tpId = activeTP ? activeTP.id : 1;
  }

  if (useSupabase) {
    const { error } = await supabase.from('attendance_pkl').upsert({
      date: d, tempat_pkl: tempatPkl, ketua_phone: ketuaPhone, status_libur: isLib,
      attendance_data: typeof attendanceData === 'string' ? JSON.parse(attendanceData) : attendanceData,
      photo_url: photoUrl || null, jurnal_kegiatan: jurnalKegiatan || null, is_takeover: isTake,
      tahun_pelajaran_id: tpId
    }, { onConflict: 'date,ketua_phone' });
    if (error) throw error;
    return true;
  }
  return runAsync(() => {
    return db.prepare(`
      INSERT INTO attendance_pkl (date, tempat_pkl, ketua_phone, status_libur, attendance_data, photo_url, jurnal_kegiatan, is_takeover, tahun_pelajaran_id)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
      ON CONFLICT(date, ketua_phone) DO UPDATE SET
        tempat_pkl = excluded.tempat_pkl,
        status_libur = excluded.status_libur,
        attendance_data = excluded.attendance_data,
        photo_url = excluded.photo_url,
        jurnal_kegiatan = excluded.jurnal_kegiatan,
        is_takeover = excluded.is_takeover
    `).run(d, tempatPkl, ketuaPhone, isLib ? 1 : 0, attData, photoUrl || null, jurnalKegiatan || null, isTake ? 1 : 0, tpId);
  });
}

export async function getAttendancePklByDate(date) {
  if (useSupabase) {
    const { data } = await supabase.from('attendance_pkl').select('*').eq('date', date);
    return data || [];
  }
  return runAsync(() => db.prepare('SELECT * FROM attendance_pkl WHERE date = ?').all(date));
}

export async function getAttendancePklToday() {
  const today = new Date().toISOString().split('T')[0];
  return getAttendancePklByDate(today);
}

export async function getAttendancePklByKetuaAndDate(ketuaPhone, date) {
  if (useSupabase) {
    const { data } = await supabase.from('attendance_pkl').select('*').eq('ketua_phone', ketuaPhone).eq('date', date).maybeSingle();
    return data;
  }
  return runAsync(() => db.prepare('SELECT * FROM attendance_pkl WHERE ketua_phone = ? AND date = ?').get(ketuaPhone, date));
}

export async function getAttendancePklByKetua(ketuaPhone) {
  if (useSupabase) {
    const { data } = await supabase.from('attendance_pkl').select('*').eq('ketua_phone', ketuaPhone).order('date', { ascending: false });
    return data || [];
  }
  return runAsync(() => db.prepare('SELECT * FROM attendance_pkl WHERE ketua_phone = ? ORDER BY date DESC').all(ketuaPhone));
}


// ─── attendance_kelas ─────────────────────────────────────────────────────────
export async function addAttendanceKelas({ date, className, teacherPhone, attendanceData, tahun_pelajaran_id }) {
  const d = date || new Date().toISOString().split('T')[0];
  const attData = typeof attendanceData === 'string' ? attendanceData : JSON.stringify(attendanceData);
  let tpId = tahun_pelajaran_id;
  if (!tpId) {
    const activeTP = await getActiveTahunPelajaran();
    tpId = activeTP ? activeTP.id : 1;
  }

  if (useSupabase) {
    const { error } = await supabase.from('attendance_kelas').upsert({
      date: d, class_name: className, teacher_phone: teacherPhone,
      attendance_data: typeof attendanceData === 'string' ? JSON.parse(attendanceData) : attendanceData,
      tahun_pelajaran_id: tpId
    }, { onConflict: 'date,class_name' });
    if (error) throw error;
    return true;
  }
  return runAsync(() => {
    return db.prepare(`
      INSERT INTO attendance_kelas (date, class_name, teacher_phone, attendance_data, tahun_pelajaran_id)
      VALUES (?, ?, ?, ?, ?)
      ON CONFLICT(date, class_name) DO UPDATE SET
        teacher_phone = excluded.teacher_phone,
        attendance_data = excluded.attendance_data
    `).run(d, className, teacherPhone, attData, tpId);
  });
}

export async function getAttendanceKelasByDate(date) {
  if (useSupabase) {
    const { data } = await supabase.from('attendance_kelas').select('*').eq('date', date);
    return data || [];
  }
  return runAsync(() => db.prepare('SELECT * FROM attendance_kelas WHERE date = ?').all(date));
}

export async function getAttendanceKelasToday() {
  const today = new Date().toISOString().split('T')[0];
  return getAttendanceKelasByDate(today);
}

export async function getAttendanceKelasByClassAndDate(className, date) {
  if (useSupabase) {
    const { data } = await supabase.from('attendance_kelas').select('*').eq('class_name', className).eq('date', date).maybeSingle();
    return data;
  }
  return runAsync(() => db.prepare('SELECT * FROM attendance_kelas WHERE class_name = ? AND date = ?').get(className, date));
}

// ─── Tahun Pelajaran ──────────────────────────────────────────────────────────
export async function getActiveTahunPelajaran() {
  if (useSupabase) {
    const { data } = await supabase.from('tahun_pelajaran').select('*').eq('is_active', true).maybeSingle();
    return data;
  }
  return runAsync(() => db.prepare('SELECT * FROM tahun_pelajaran WHERE is_active = 1').get());
}

export async function getAllTahunPelajaran() {
  if (useSupabase) {
    const { data } = await supabase.from('tahun_pelajaran').select('*').order('created_at', { ascending: false });
    return data || [];
  }
  return runAsync(() => db.prepare('SELECT * FROM tahun_pelajaran ORDER BY created_at DESC').all());
}

// ─── Bot Settings & Templates (v1.6.25) ──────────────────────────────────────────
export async function getBotTemplates() {
  if (useSupabase) {
    const { data } = await supabase.from('bot_templates').select('*');
    return data || [];
  }
  return runAsync(() => db.prepare('SELECT * FROM bot_templates').all());
}

export async function getBotTemplate(key) {
  if (useSupabase) {
    const { data } = await supabase.from('bot_templates').select('*').eq('key', key).maybeSingle();
    return data;
  }
  return runAsync(() => db.prepare('SELECT * FROM bot_templates WHERE key = ?').get(key));
}

export async function updateBotTemplate(key, body) {
  if (useSupabase) {
    const { error } = await supabase.from('bot_templates').update({ body, updated_at: new Date().toISOString() }).eq('key', key);
    if (error) throw error;
    return true;
  }
  return runAsync(() => db.prepare('UPDATE bot_templates SET body = ?, updated_at = CURRENT_TIMESTAMP WHERE key = ?').run(body, key));
}

// ─── Cron Configs (v1.6.25) ───────────────────────────────────────────────
export async function getCronConfigs() {
  if (useSupabase) {
    const { data } = await supabase.from('cron_configs').select('*');
    return data || [];
  }
  return runAsync(() => db.prepare('SELECT * FROM cron_configs').all());
}

export async function getCronConfig(key) {
  if (useSupabase) {
    const { data } = await supabase.from('cron_configs').select('*').eq('key', key).maybeSingle();
    return data;
  }
  return runAsync(() => db.prepare('SELECT * FROM cron_configs WHERE key = ?').get(key));
}

export async function updateCronConfig(key, cronExpression, isActive, action = null, payload = null) {
  if (useSupabase) {
    const updateData = { 
      cron_expression: cronExpression, 
      is_active: isActive === true || isActive === 1 ? 1 : 0, 
      updated_at: new Date().toISOString() 
    };
    if (action !== null) updateData.action = action;
    if (payload !== null) updateData.payload = payload;

    const { error } = await supabase.from('cron_configs').update(updateData).eq('key', key);
    if (error) throw error;
    return true;
  }

  let query = 'UPDATE cron_configs SET cron_expression = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP';
  const params = [cronExpression, isActive === true || isActive === 1 ? 1 : 0];
  
  if (action !== null) { query += ', action = ?'; params.push(action); }
  if (payload !== null) { query += ', payload = ?'; params.push(payload); }
  
  query += ' WHERE key = ?';
  params.push(key);
  
  return runAsync(() => db.prepare(query).run(...params));
}

export async function addCronConfig({ key, name, cronExpression, isActive, description, action, payload = '{}' }) {
  if (useSupabase) {
    const { error } = await supabase.from('cron_configs').upsert({
      key,
      name,
      cron_expression: cronExpression,
      is_active: isActive === true || isActive === 1 ? 1 : 0,
      description: description || '',
      action: action || key,
      payload: payload,
      updated_at: new Date().toISOString()
    });
    if (error) throw error;
    return true;
  }
  return runAsync(() => db.prepare(
    'INSERT OR REPLACE INTO cron_configs (key, name, cron_expression, is_active, description, action, payload, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)'
  ).run(key, name, cronExpression, isActive === true || isActive === 1 ? 1 : 0, description || '', action || key, payload));
}

export async function deleteCronConfig(key) {
  if (useSupabase) {
    const { error } = await supabase.from('cron_configs').delete().eq('key', key);
    if (error) throw error;
    return true;
  }
  return runAsync(() => db.prepare('DELETE FROM cron_configs WHERE key = ?').run(key));
}

// Helper untuk merender template dengan variabel kustom
export async function renderTemplate(key, variables, fallback) {
  try {
    const template = await getBotTemplate(key);
    if (template && template.body) {
      let body = template.body;
      for (const [k, v] of Object.entries(variables)) {
        body = body.replaceAll(`{${k}}`, v === null || v === undefined ? '' : String(v));
      }
      return body;
    }
  } catch (err) {
    console.error(`[TEMPLATE] Gagal me-render template ${key}:`, err.message);
  }
  return fallback;
}

