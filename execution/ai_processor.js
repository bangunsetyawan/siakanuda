/**
 * execution/ai_processor.js
 * SIAKANUDA v1.0.0 — Tier 3: 9Router / OpenAI-compatible command parser.
 *
 * STRATEGI HEMAT TOKEN:
 * - AI HANYA dipanggil jika pesan tidak cocok dengan menu angka atau perintah #
 * - Daftar siswa TIDAK dikirim jika pesan tidak mengandung nama/kata kunci siswa
 * - Menggunakan 9Router lokal untuk auto-fallback dan manajemen kuota pintar
 * - System prompt dipadatkan seminimal mungkin
 */

import * as db from './db.js';

let ninerouterUrl = 'http://localhost:20128';
let ninerouterKey = '';

export function initAI() {
  ninerouterUrl = process.env.NINEROUTER_URL || 'http://localhost:20128';
  ninerouterKey = process.env.NINEROUTER_KEY || '';
  console.log(`[AI] 9Router parser siap terhubung ke ${ninerouterUrl} (OpenAI-compatible)`);
}

// ─── Deteksi: apakah pesan mungkin berisi nama siswa? ─────────────────────────
// Jika iya, kirim daftar siswa ke AI. Jika tidak, hemat token — kirim prompt ringkas.
function likelySiswaQuery(text) {
  const triggers = ['siswa', 'murid', 'nama', 'kelas', 'absen', 'hadir', 'alpha',
                    'pelanggaran', 'poin', 'konseling', 'bimbingan', 'rekap',
                    'siapa', 'daftar', 'list', 'laporan', 'data'];
  const lower = text.toLowerCase();
  return triggers.some(t => lower.includes(t));
}

/**
 * Parse pesan WA menjadi array JSON action untuk dieksekusi.
 * Mengembalikan minimal 1 action, atau action 'unknown' jika tidak dimengerti.
 */
export async function parseCommand(text, students, senderName) {
  // ── Bangun konteks DB (ringkas) ──────────────────────────────────────────────
  let dbStats = '';
  let studentList = '';

  try {
    const stats = db.getDashboardStats();
    dbStats = `DB: ${stats.totalStudents} siswa, ${stats.totalTeachers} guru, absen tidak hadir hari ini: ${stats.absenToday}`;
  } catch (_) {}

  // Hemat token: hanya sertakan daftar siswa jika relevan
  if (likelySiswaQuery(text) && students && students.length > 0) {
    // Kirim format ringkas: "Nama (Kelas)" bukan objek lengkap
    studentList = students
      .slice(0, 200) // maksimal 200 siswa untuk hemat token
      .map(s => `${s.name}(${s.class})`)
      .join(',');
  }

  // ── System Prompt (dipadatkan) ───────────────────────────────────────────────
  const systemPrompt = `Kamu asisten ${senderName} di ${process.env.SCHOOL_NAME || 'SMKN U DARUSSALAM'}.
Tanggal: ${new Date().toLocaleDateString('id-ID', { weekday:'long', year:'numeric', month:'long', day:'numeric' })}.
${dbStats}
${studentList ? `Daftar siswa: ${studentList}` : ''}

Ubah pesan menjadi JSON array. Setiap elemen: {"action":"...","params":{...},"confirmation":"...","error":null}

Actions: add_attendance(name,class?,status[alpha/sakit/izin/hadir],date?,note?), add_violation(name,class?,category,points,description?), add_counseling(name,class?,type,content,counselor?), query_student(name,class?), query_attendance(status?,class?,date?), query_violations(name?,class?), query_stats, add_student(name,class,nis?,gender?), delete_record(table,id), unknown.

BALAS HANYA JSON VALID. Tanpa markdown/penjelasan.`;

  try {
    const response = await fetch(`${ninerouterUrl}/v1/chat/completions`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        ...(ninerouterKey && { 'Authorization': `Bearer ${ninerouterKey}` })
      },
      body: JSON.stringify({
        model: process.env.NINEROUTER_MODEL || 'ag/gemini-3.1-pro-low', // Diambil dari .env agar dinamis di 9Router
        messages: [{ role: 'user', content: `${systemPrompt}\n\nPesan: ${text}` }],
        temperature: 0.1,
        max_tokens: 512,
        response_format: { type: 'json_object' } // Opsional: memastikan format valid jika provider mendukung
      })
    });

    if (!response.ok) {
      throw new Error(`9Router API error: ${response.status} ${response.statusText}`);
    }

    const result = await response.json();
    const raw = result.choices[0].message.content.trim();

    // Bersihkan markdown code block jika ada
    const clean = raw.replace(/^```json\s*/i, '').replace(/^```\s*/i, '').replace(/```$/g, '').trim();

    const parsed = JSON.parse(clean);
    return Array.isArray(parsed) ? parsed : [parsed];

  } catch (err) {
    console.error('[AI] Parse error (9Router):', err.message);
    return [{
      action: 'unknown',
      params: {},
      confirmation: '❓ Saya kurang memahami perintah tersebut. Coba gunakan format:\n• `#absen [Nama] [H/S/I/A]`\n• `#poin [Nama] [Poin] [Kasus]`\n• `#cek [Nama]`\n• Atau ketik *menu* untuk panduan lengkap.',
      error: err.message
    }];
  }
}

/**
 * Generate laporan/narasi ringkas dari data.
 * Dipanggil hanya saat admin minta rekap naratif — bukan untuk setiap pesan.
 */
export async function generateReport(prompt, data) {
  try {
    const response = await fetch(`${ninerouterUrl}/v1/chat/completions`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        ...(ninerouterKey && { 'Authorization': `Bearer ${ninerouterKey}` })
      },
      body: JSON.stringify({
        model: process.env.NINEROUTER_MODEL || 'ag/gemini-3.1-pro-low',
        messages: [{ role: 'user', content: `${prompt}\n\nData: ${JSON.stringify(data)}` }],
        temperature: 0.3,
        max_tokens: 800
      })
    });

    if (!response.ok) {
      throw new Error(`9Router API error: ${response.statusText}`);
    }

    const result = await response.json();
    return result.choices[0].message.content.trim();
  } catch (err) {
    console.error('[AI] Report error (9Router):', err.message);
    return `Gagal generate laporan: ${err.message}`;
  }
}
