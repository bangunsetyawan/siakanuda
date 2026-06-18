/**
 * execution/reset-to-admin.js
 * Reset database bersih — hapus semua data, sisakan 1 akun admin saja.
 * Otomatis migrasi skema jika ada kolom/tabel yang belum ada.
 *
 * Admin: phone=6285334354102, password=admin, role=admin
 *
 * Jalankan: node execution/reset-to-admin.js
 */

import Database from 'better-sqlite3';
import bcrypt from 'bcrypt';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const DB_PATH = path.join(__dirname, '..', 'siakanuda.db');

console.log('');
console.log('╔══════════════════════════════════════════╗');
console.log('║   SIAKANUDA — Reset Database ke Admin    ║');
console.log('╚══════════════════════════════════════════╝');
console.log('');
console.log('[RESET] Database:', DB_PATH);

const db = new Database(DB_PATH);

// ─── HAPUS SEMUA DATA ────────────────────────────────────────────────────────
console.log('\n[RESET] Mengosongkan semua tabel...');

const tabelHapus = [
  'attendance_pkl',
  'attendance_kelas',
  'attendance',
  'kelompok_pkl',
  'violations',
  'counseling',
  'schedules',
  'feedbacks',
  'logs',
  'message_logs',
  'user_sessions',
  'students',
  'allowed_numbers',
];

for (const tabel of tabelHapus) {
  try {
    const result = db.prepare(`DELETE FROM ${tabel}`).run();
    console.log(`  ✅ ${tabel.padEnd(20)} → ${result.changes} baris dihapus`);
  } catch (err) {
    console.log(`  ⏭️  ${tabel.padEnd(20)} → (belum ada, akan dibuat)`);
  }
}

// ─── MIGRASI SKEMA ───────────────────────────────────────────────────────────
console.log('\n[RESET] Migrasi skema database...');

// Tambah kolom password_hash & first_login ke allowed_numbers jika belum ada
const cols = db.prepare("PRAGMA table_info(allowed_numbers)").all().map(c => c.name);
if (!cols.includes('password_hash')) {
  db.exec("ALTER TABLE allowed_numbers ADD COLUMN password_hash TEXT");
  console.log('  ✅ Kolom password_hash ditambahkan ke allowed_numbers');
}
if (!cols.includes('first_login')) {
  db.exec("ALTER TABLE allowed_numbers ADD COLUMN first_login INTEGER DEFAULT 1");
  console.log('  ✅ Kolom first_login ditambahkan ke allowed_numbers');
}

// Tambah kolom password_hash & first_login ke students jika belum ada
const studentCols = db.prepare("PRAGMA table_info(students)").all().map(c => c.name);
if (!studentCols.includes('password_hash')) {
  db.exec("ALTER TABLE students ADD COLUMN password_hash TEXT");
  console.log('  ✅ Kolom password_hash ditambahkan ke students');
}
if (!studentCols.includes('first_login')) {
  db.exec("ALTER TABLE students ADD COLUMN first_login INTEGER DEFAULT 1");
  console.log('  ✅ Kolom first_login ditambahkan ke students');
}

// Buat tabel baru yang belum ada
const newTables = {
  attendance_kelas: `CREATE TABLE IF NOT EXISTS attendance_kelas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    date DATE NOT NULL, class_name TEXT NOT NULL,
    teacher_phone TEXT, attendance_data TEXT,
    UNIQUE(date, class_name))`,
  kelompok_pkl: `CREATE TABLE IF NOT EXISTS kelompok_pkl (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tempat_pkl TEXT NOT NULL, ketua_phone TEXT,
    anggota TEXT, pembimbing_phone TEXT)`,
  attendance_pkl: `CREATE TABLE IF NOT EXISTS attendance_pkl (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    date DATE NOT NULL, tempat_pkl TEXT, ketua_phone TEXT,
    status_libur INTEGER DEFAULT 0, attendance_data TEXT,
    photo_url TEXT, jurnal_kegiatan TEXT, is_takeover INTEGER DEFAULT 0,
    UNIQUE(date, ketua_phone))`,
  schedules: `CREATE TABLE IF NOT EXISTS schedules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    day TEXT NOT NULL, class_name TEXT NOT NULL,
    subject TEXT, teacher_phone TEXT, start_time TEXT, end_time TEXT)`,
  feedbacks: `CREATE TABLE IF NOT EXISTS feedbacks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    sender_name TEXT, class_name TEXT, message TEXT, is_public INTEGER DEFAULT 1)`,
  logs: `CREATE TABLE IF NOT EXISTS logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    phone TEXT, direction TEXT, message TEXT,
    ai_action TEXT, status TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP)`,
};

for (const [nama, sql] of Object.entries(newTables)) {
  try {
    db.exec(sql);
    console.log(`  ✅ Tabel ${nama} siap`);
  } catch (err) {
    console.warn(`  ⚠️  ${nama}: ${err.message}`);
  }
}

// ─── INSERT ADMIN ────────────────────────────────────────────────────────────
console.log('\n[RESET] Membuat akun admin...');

const passwordHash = await bcrypt.hash('admin', 10);

try {
  db.prepare(`
    INSERT INTO allowed_numbers (phone, name, role, active, password_hash, first_login)
    VALUES (?, ?, ?, ?, ?, ?)
  `).run('admin', 'Admin', 'admin', 1, passwordHash, 0);

  console.log('  ✅ Akun admin berhasil dibuat:');
  console.log('     Phone    : admin');
  console.log('     Nama     : Admin');
  console.log('     Role     : admin');
  console.log('     Password : admin');
} catch (err) {
  console.error('  ❌ Gagal buat admin:', err.message);
  process.exit(1);
}

// ─── VERIFIKASI ──────────────────────────────────────────────────────────────
console.log('\n[RESET] Verifikasi...');
const admin = db.prepare('SELECT phone, name, role, active FROM allowed_numbers').get();
console.log('  Data tersimpan:', JSON.stringify(admin));

const totalSiswa = db.prepare('SELECT COUNT(*) as n FROM students').get();
console.log('  Total siswa  :', totalSiswa.n, '(seharusnya 0)');

db.close();

console.log('');
console.log('╔══════════════════════════════════════════╗');
console.log('║   ✅ Reset selesai! Database bersih.     ║');
console.log('║                                          ║');
console.log('║   Login dashboard CI4:                   ║');
console.log('║   Username: admin                        ║');
console.log('║   Password: admin                        ║');
console.log('╚══════════════════════════════════════════╝');
console.log('');
