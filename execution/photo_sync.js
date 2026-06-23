import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { createClient } from '@supabase/supabase-js';

// sharp dihapus: menyebabkan SIGILL crash pada CPU Atom N455.
// Foto sudah dikompresi di client-side (HP siswa) via browser-image-compression.

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.join(__dirname, '..');

export async function syncLocalPhotosToSupabase() {
  const SUPABASE_URL = process.env.SUPABASE_URL;
  const SUPABASE_KEY = process.env.SUPABASE_KEY;
  if (!SUPABASE_URL || !SUPABASE_KEY) {
    console.log('[SYNC] Supabase URL/Key tidak terkonfigurasi, skip auto-sync foto.');
    return;
  }

  // Impor koneksi DB secara dinamis
  const db = await import('./db.js');
  const { supabase, useSupabase, getDb } = db;

  console.log('[SYNC] Menjalankan pengecekan sinkronisasi foto lokal ke Supabase Storage...');

  try {
    // Ambil 100 laporan PKL terakhir untuk mendeteksi file lokal yang belum di-backup
    let reports = [];
    if (useSupabase) {
      const { data } = await supabase.from('attendance_pkl')
        .select('*')
        .order('date', { ascending: false })
        .limit(100);
      reports = data || [];
    } else {
      reports = getDb().prepare('SELECT * FROM attendance_pkl ORDER BY date DESC LIMIT 100').all();
    }

    for (const report of reports) {
      let photoObj = {};
      let isObject = false;

      try {
        photoObj = typeof report.photo_url === 'string' ? JSON.parse(report.photo_url || '{}') : (report.photo_url || {});
      } catch (_) {
        photoObj = {};
      }

      if (photoObj && typeof photoObj === 'object' && !Array.isArray(photoObj)) {
        isObject = true;
      } else if (Array.isArray(photoObj)) {
        isObject = false;
      } else if (typeof report.photo_url === 'string') {
        photoObj = [report.photo_url];
        isObject = false;
      } else {
        photoObj = [];
        isObject = false;
      }

      let hasLocalPath = false;

      if (isObject) {
        const updatedPhotoObj = { ...photoObj };
        for (const [name, url] of Object.entries(photoObj)) {
          if (typeof url === 'string' && url.startsWith('/uploads/pkl/')) {
            const filename = url.replace('/uploads/pkl/', '');
            const localFilePath = path.join(ROOT, 'dashboard', 'public', 'uploads', 'pkl', filename);

            if (fs.existsSync(localFilePath)) {
              console.log(`[SYNC] Mengunggah cadangan foto ${filename} ke Supabase...`);
              const fileBuffer = fs.readFileSync(localFilePath);
              
              const uploadBuffer = fileBuffer;

              // Upload ke Supabase
              const { error } = await supabase.storage
                .from('siakanuda-uploads')
                .upload(`pkl/${filename}`, uploadBuffer, { contentType: 'image/jpeg', upsert: true });

              if (!error) {
                const { data: { publicUrl } } = supabase.storage
                  .from('siakanuda-uploads')
                  .getPublicUrl(`pkl/${filename}`);
                
                updatedPhotoObj[name] = publicUrl;
                hasLocalPath = true;
                console.log(`[SYNC] Berhasil upload. URL Baru: ${publicUrl}`);
              } else {
                console.error(`[SYNC] Gagal upload ${filename}:`, error.message);
              }
            }
          }
        }

        if (hasLocalPath) {
          const photoUrlJson = JSON.stringify(updatedPhotoObj);
          if (useSupabase) {
            const { error } = await supabase.from('attendance_pkl')
              .update({ photo_url: photoUrlJson })
              .eq('id', report.id);
            if (error) throw error;
          } else {
            getDb().prepare('UPDATE attendance_pkl SET photo_url = ? WHERE id = ?').run(photoUrlJson, report.id);
          }
          console.log(`[SYNC] Laporan ID ${report.id} diperbarui dengan URL Supabase (Preserved Object).`);
        }
      } else {
        const updatedUrls = [];
        const urlsArray = Array.isArray(photoObj) ? photoObj : [photoObj].filter(Boolean);
        for (const url of urlsArray) {
          if (typeof url === 'string' && url.startsWith('/uploads/pkl/')) {
            const filename = url.replace('/uploads/pkl/', '');
            const localFilePath = path.join(ROOT, 'dashboard', 'public', 'uploads', 'pkl', filename);

            if (fs.existsSync(localFilePath)) {
              console.log(`[SYNC] Mengunggah cadangan foto ${filename} ke Supabase...`);
              const fileBuffer = fs.readFileSync(localFilePath);
              
              const uploadBuffer = fileBuffer;

              // Upload ke Supabase
              const { error } = await supabase.storage
                .from('siakanuda-uploads')
                .upload(`pkl/${filename}`, uploadBuffer, { contentType: 'image/jpeg', upsert: true });

              if (!error) {
                const { data: { publicUrl } } = supabase.storage
                  .from('siakanuda-uploads')
                  .getPublicUrl(`pkl/${filename}`);
                
                updatedUrls.push(publicUrl);
                hasLocalPath = true;
                console.log(`[SYNC] Berhasil upload. URL Baru: ${publicUrl}`);
              } else {
                console.error(`[SYNC] Gagal upload ${filename}:`, error.message);
                updatedUrls.push(url);
              }
            } else {
              updatedUrls.push(url);
            }
          } else {
            updatedUrls.push(url);
          }
        }

        if (hasLocalPath) {
          const photoUrlJson = JSON.stringify(updatedUrls);
          if (useSupabase) {
            const { error } = await supabase.from('attendance_pkl')
              .update({ photo_url: photoUrlJson })
              .eq('id', report.id);
            if (error) throw error;
          } else {
            getDb().prepare('UPDATE attendance_pkl SET photo_url = ? WHERE id = ?').run(photoUrlJson, report.id);
          }
          console.log(`[SYNC] Laporan ID ${report.id} diperbarui dengan URL Supabase (Array).`);
        }
      }
    }
  } catch (e) {
    console.error('[SYNC] Error saat menjalankan auto-sync foto:', e.message);
  }
}
