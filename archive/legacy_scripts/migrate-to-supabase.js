import { createClient } from '@supabase/supabase-js';
import Database from 'better-sqlite3';
import dotenv from 'dotenv';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
dotenv.config({ path: path.join(__dirname, '..', '.env') });

const supabaseUrl = process.env.SUPABASE_URL;
const supabaseKey = process.env.SUPABASE_KEY;

if (!supabaseUrl || !supabaseKey) {
  console.error('Error: SUPABASE_URL or SUPABASE_KEY is missing in .env');
  process.exit(1);
}

const supabase = createClient(supabaseUrl, supabaseKey);

// Path ke SQLite DB lokal
const DB_PATH = path.join(__dirname, '..', 'siakanuda.db');
console.log('Connecting to local SQLite database:', DB_PATH);
const db = new Database(DB_PATH, { readonly: true });

async function migrate() {
  try {
    console.log('Starting migration to Supabase...');

    // 1. Migrasi Students (Pilih hanya kolom yang ada di Supabase)
    console.log('Fetching students from SQLite...');
    const students = db.prepare('SELECT id, name, class, nis, gender, phone, role, created_at FROM students').all();
    console.log(`Found ${students.length} students. Migrating...`);
    
    if (students.length > 0) {
      // Chunk inserts to avoid request payload limits
      const chunkSize = 50;
      for (let i = 0; i < students.length; i += chunkSize) {
        const chunk = students.slice(i, i + chunkSize);
        const { error } = await supabase.from('students').insert(chunk);
        if (error) {
          throw new Error(`Failed to migrate students chunk: ${error.message}`);
        }
        console.log(`Migrated students: ${i + chunk.length}/${students.length}`);
      }
    }

    // 2. Migrasi Allowed Numbers
    console.log('Fetching allowed_numbers from SQLite...');
    const allowed = db.prepare('SELECT id, phone, name, role, active, created_at, lid FROM allowed_numbers').all();
    console.log(`Found ${allowed.length} allowed numbers. Migrating...`);

    if (allowed.length > 0) {
      const allowedFormatted = allowed.map(item => ({
        ...item,
        active: item.active === 1 || item.active === true
      }));

      const { error } = await supabase.from('allowed_numbers').insert(allowedFormatted);
      if (error) {
        throw new Error(`Failed to migrate allowed_numbers: ${error.message}`);
      }
      console.log('Migrated all allowed_numbers.');
    }

    // 3. Migrasi Attendance
    console.log('Fetching attendance from SQLite...');
    const attendance = db.prepare('SELECT id, student_id, date, status, note, created_at FROM attendance').all();
    console.log(`Found ${attendance.length} attendance records. Migrating...`);

    if (attendance.length > 0) {
      const chunkSize = 50;
      for (let i = 0; i < attendance.length; i += chunkSize) {
        const chunk = attendance.slice(i, i + chunkSize);
        const { error } = await supabase.from('attendance').insert(chunk);
        if (error) {
          throw new Error(`Failed to migrate attendance chunk: ${error.message}`);
        }
        console.log(`Migrated attendance: ${i + chunk.length}/${attendance.length}`);
      }
    }

    // 4. Migrasi Violations
    console.log('Fetching violations from SQLite...');
    const violations = db.prepare('SELECT id, student_id, date, category, description, points, created_at FROM violations').all();
    console.log(`Found ${violations.length} violations. Migrating...`);

    if (violations.length > 0) {
      const chunkSize = 50;
      for (let i = 0; i < violations.length; i += chunkSize) {
        const chunk = violations.slice(i, i + chunkSize);
        const { error } = await supabase.from('violations').insert(chunk);
        if (error) {
          throw new Error(`Failed to migrate violations chunk: ${error.message}`);
        }
        console.log(`Migrated violations: ${i + chunk.length}/${violations.length}`);
      }
    }

    // 5. Migrasi Counseling
    console.log('Fetching counseling from SQLite...');
    const counseling = db.prepare('SELECT id, student_id, date, type, content, counselor, created_at FROM counseling').all();
    console.log(`Found ${counseling.length} counseling records. Migrating...`);

    if (counseling.length > 0) {
      const chunkSize = 50;
      for (let i = 0; i < counseling.length; i += chunkSize) {
        const chunk = counseling.slice(i, i + chunkSize);
        const { error } = await supabase.from('counseling').insert(chunk);
        if (error) {
          throw new Error(`Failed to migrate counseling chunk: ${error.message}`);
        }
        console.log(`Migrated counseling: ${i + chunk.length}/${counseling.length}`);
      }
    }

    console.log('🎉 Migration completed successfully!');
  } catch (err) {
    console.error('❌ Migration failed:', err.message);
  } finally {
    db.close();
  }
}

migrate();
