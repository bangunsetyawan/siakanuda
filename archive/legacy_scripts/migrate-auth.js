/**
 * execution/migrate-auth.js
 * Script migrasi satu kali: tambah kolom auth ke Supabase + seed akun superadmin.
 *
 * CARA JALANKAN (hanya sekali):
 *   node --env-file=.env execution/migrate-auth.js
 *
 * YANG DILAKUKAN:
 *   1. Tambah kolom password_hash, is_super_admin, first_login ke tabel allowed_numbers
 *   2. Tambah kolom password_hash, first_login ke tabel students
 *   3. Buat akun superadmin (username: superadmin, password: smk12399)
 *   4. Set password_hash = hash('guruhebat') untuk semua guru yang belum punya password
 *   5. Set password_hash = hash(nis) untuk semua siswa yang belum punya password (default = NISN)
 */

import 'dotenv/config';
import { createClient } from '@supabase/supabase-js';
import bcrypt from 'bcrypt';

const SALT_ROUNDS = 10;

// ─── Koneksi Supabase ─────────────────────────────────────────────────────────
if (!process.env.SUPABASE_URL || !process.env.SUPABASE_KEY) {
  console.error('❌ SUPABASE_URL dan SUPABASE_KEY harus ada di .env');
  process.exit(1);
}

const supabase = createClient(process.env.SUPABASE_URL, process.env.SUPABASE_KEY);

// ─── Konfigurasi akun default ─────────────────────────────────────────────────
const SUPERADMIN_USERNAME  = 'superadmin';
const SUPERADMIN_PASSWORD  = 'smk12399';
const DEFAULT_GURU_PASSWORD = 'guruhebat';

// ─── Fungsi migrasi kolom via SQL ─────────────────────────────────────────────
async function runSQL(sql, label) {
  const { error } = await supabase.rpc('exec_sql', { sql });
  if (error) {
    // Kolom sudah ada → abaikan error "duplicate column"
    if (error.message?.includes('already exists') || error.message?.includes('duplicate')) {
      console.log(`  ⚠️  ${label}: kolom sudah ada, dilewati.`);
    } else {
      console.log(`  ℹ️  ${label}: ${error.message} (mungkin perlu jalankan SQL manual — lihat instruksi di bawah)`);
    }
  } else {
    console.log(`  ✅ ${label}`);
  }
}

// ─── Main ──────────────────────────────────────────────────────────────────────
async function migrate() {
  console.log('\n🔧 SIAKANUDA — Migrasi Auth Database');
  console.log('=====================================\n');

  // ─── LANGKAH 1: Tambah kolom ke tabel allowed_numbers ───────────────────────
  console.log('📋 Langkah 1: Tambah kolom ke tabel allowed_numbers...');
  console.log('  ℹ️  Jika gagal, jalankan SQL ini manual di Supabase SQL Editor:');
  console.log(`
  ALTER TABLE allowed_numbers ADD COLUMN IF NOT EXISTS password_hash TEXT;
  ALTER TABLE allowed_numbers ADD COLUMN IF NOT EXISTS is_super_admin INTEGER DEFAULT 0;
  ALTER TABLE allowed_numbers ADD COLUMN IF NOT EXISTS first_login INTEGER DEFAULT 1;
  `);

  // ─── LANGKAH 2: Tambah kolom ke tabel students ──────────────────────────────
  console.log('\n📋 Langkah 2: Tambah kolom ke tabel students...');
  console.log('  ℹ️  Jika gagal, jalankan SQL ini manual di Supabase SQL Editor:');
  console.log(`
  ALTER TABLE students ADD COLUMN IF NOT EXISTS password_hash TEXT;
  ALTER TABLE students ADD COLUMN IF NOT EXISTS first_login INTEGER DEFAULT 1;
  `);

  // ─── LANGKAH 3: Buat/update akun superadmin ──────────────────────────────────
  console.log('\n👑 Langkah 3: Membuat akun superadmin...');
  const superAdminHash = await bcrypt.hash(SUPERADMIN_PASSWORD, SALT_ROUNDS);

  const { data: existing } = await supabase
    .from('allowed_numbers')
    .select('id, phone')
    .eq('phone', SUPERADMIN_USERNAME)
    .maybeSingle();

  if (existing) {
    const { error } = await supabase
      .from('allowed_numbers')
      .update({
        name: 'Super Admin',
        role: 'admin',
        is_super_admin: 1,
        password_hash: superAdminHash,
        first_login: 0,
        active: true,
      })
      .eq('phone', SUPERADMIN_USERNAME);

    if (error) {
      console.log(`  ⚠️  Update superadmin: ${error.message}`);
      console.log('  ℹ️  Mungkin kolom belum ada. Jalankan SQL manual dulu, lalu ulangi script ini.');
    } else {
      console.log('  ✅ Akun superadmin diperbarui');
    }
  } else {
    const { error } = await supabase
      .from('allowed_numbers')
      .insert({
        phone: SUPERADMIN_USERNAME,
        name: 'Super Admin',
        role: 'admin',
        is_super_admin: 1,
        password_hash: superAdminHash,
        first_login: 0,
        active: true,
      });

    if (error) {
      console.log(`  ⚠️  Insert superadmin: ${error.message}`);
      console.log('  ℹ️  Mungkin kolom belum ada. Jalankan SQL manual dulu, lalu ulangi script ini.');
    } else {
      console.log('  ✅ Akun superadmin dibuat (username: superadmin)');
    }
  }

  // ─── LANGKAH 4: Set password default untuk semua guru yang belum punya ────────
  console.log('\n👨‍🏫 Langkah 4: Set password default untuk semua guru...');
  const guruHash = await bcrypt.hash(DEFAULT_GURU_PASSWORD, SALT_ROUNDS);

  const { data: gurus, error: guruErr } = await supabase
    .from('allowed_numbers')
    .select('id, phone, name, password_hash')
    .neq('phone', SUPERADMIN_USERNAME);

  if (guruErr) {
    console.log(`  ⚠️  Tidak bisa ambil data guru: ${guruErr.message}`);
  } else {
    let updated = 0;
    let skipped = 0;
    for (const guru of (gurus || [])) {
      if (!guru.password_hash) {
        const { error } = await supabase
          .from('allowed_numbers')
          .update({ password_hash: guruHash, first_login: 1 })
          .eq('id', guru.id);
        if (!error) updated++;
      } else {
        skipped++;
      }
    }
    console.log(`  ✅ ${updated} guru diberi password default 'guruhebat' | ${skipped} sudah punya password (dilewati)`);
  }

  // ─── LANGKAH 5: Set password default untuk semua siswa (NISN) ─────────────────
  console.log('\n🎒 Langkah 5: Set password default untuk semua siswa (NISN sebagai password)...');

  const { data: students, error: studErr } = await supabase
    .from('students')
    .select('id, name, nis, password_hash');

  if (studErr) {
    console.log(`  ⚠️  Tidak bisa ambil data siswa: ${studErr.message}`);
  } else {
    let updated = 0;
    let skipped = 0;
    let noNisn = 0;

    for (const student of (students || [])) {
      if (!student.password_hash) {
        if (!student.nis) {
          noNisn++;
          continue; // Tidak bisa set password jika NISN kosong
        }
        const hash = await bcrypt.hash(student.nis, SALT_ROUNDS);
        const { error } = await supabase
          .from('students')
          .update({ password_hash: hash, first_login: 1 })
          .eq('id', student.id);
        if (!error) updated++;
      } else {
        skipped++;
      }
    }
    console.log(`  ✅ ${updated} siswa diberi password default (NISN mereka) | ${skipped} sudah ada password | ${noNisn} tidak punya NISN (dilewati)`);
    if (noNisn > 0) {
      console.log(`  ⚠️  ${noNisn} siswa tidak punya NISN — perlu diisi manual di dashboard`);
    }
  }

  // ─── SELESAI ─────────────────────────────────────────────────────────────────
  console.log('\n✅ Migrasi selesai!');
  console.log('=====================================');
  console.log('📌 Yang perlu dilakukan selanjutnya:');
  console.log('   1. Buka Supabase SQL Editor dan jalankan SQL manual (lihat di atas) jika ada yang gagal');
  console.log('   2. Login ke web dashboard: username=superadmin, password=smk12399');
  console.log('   3. Jalankan: npm run dev');
  console.log('');
}

migrate().catch(err => {
  console.error('❌ Fatal error:', err.message);
  process.exit(1);
});
