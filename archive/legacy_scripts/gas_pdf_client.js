import fetch from 'node-fetch';
import { getAttendancePklByKetuaAndDate, getKelompokPklByKetua, getAllStudents } from './db.js';

export async function generateDailyPklHtml(ketuaPhone, date) {
  const record = await getAttendancePklByKetuaAndDate(ketuaPhone, date);
  const kelompok = await getKelompokPklByKetua(ketuaPhone);
  if (!record || !kelompok) throw new Error("Data laporan tidak ditemukan.");
  
  const allS = await getAllStudents();
  const ketuaStudent = allS.find(s => s.phone === ketuaPhone || String(s.nis) === String(ketuaPhone));
  const ketuaName = ketuaStudent ? ketuaStudent.name : 'Ketua Kelompok';
  
  let attendanceData = {}, jurnalKegiatan = {}, photoUrls = [];
  try { attendanceData = typeof record.attendance_data === 'string' ? JSON.parse(record.attendance_data) : (record.attendance_data || {}); } catch (_) {}
  try { jurnalKegiatan = typeof record.jurnal_kegiatan === 'string' ? JSON.parse(record.jurnal_kegiatan) : (record.jurnal_kegiatan || {}); } catch (_) {}
  try { photoUrls = typeof record.photo_url === 'string' ? JSON.parse(record.photo_url) : (record.photo_url || []); } catch (_) {}

  let members = kelompok.anggota.split(',').map(m => m.trim());
  const listAnggota = members.filter(m => m.toLowerCase() !== ketuaName.toLowerCase());
  const finalMembersList = [`${ketuaName} (Ketua)`, ...listAnggota];

  const dateStr = new Date(record.created_at || date).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

  let html = `
  <html>
  <head>
    <style>
      body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 30px; color: #222; }
      .header { text-align: center; border-bottom: 3px solid #0b6623; padding-bottom: 15px; margin-bottom: 25px; }
      .title { font-size: 20px; font-weight: bold; color: #0b6623; margin-bottom: 5px; }
      .subtitle { font-size: 14px; color: #555; }
      .info-box { margin-bottom: 20px; font-size: 13px; line-height: 1.6; }
      table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
      th, td { border: 1px solid #ccc; padding: 10px; text-align: left; vertical-align: top; }
      th { background-color: #f8f9fa; font-weight: bold; color: #333; }
      .status-hadir { color: #28a745; font-weight: bold; }
      .status-sakit { color: #ffc107; font-weight: bold; }
      .status-izin { color: #17a2b8; font-weight: bold; }
      .status-alpha { color: #dc3545; font-weight: bold; }
      .photo { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
      .jurnal-text { white-space: pre-wrap; font-style: italic; color: #444; }
    </style>
  </head>
  <body>
    <div class="header">
      <div class="title">LAPORAN HARIAN PRAKTIK KERJA LAPANGAN (PKL)</div>
      <div class="subtitle">SIAKANUDA — SMK NU DARUSSALAM</div>
    </div>
    
    <div class="info-box">
      <table style="border:none; margin-top:0;">
        <tr><td style="border:none; padding:2px; width:120px;"><strong>Tanggal</strong></td><td style="border:none; padding:2px;">: ${dateStr}</td></tr>
        <tr><td style="border:none; padding:2px;"><strong>Tempat PKL</strong></td><td style="border:none; padding:2px;">: ${record.tempat_pkl}</td></tr>
        <tr><td style="border:none; padding:2px;"><strong>Pembimbing</strong></td><td style="border:none; padding:2px;">: ${kelompok.pembimbing_nama || kelompok.pembimbing_phone || 'Belum ditentukan'}</td></tr>
      </table>
    </div>
  `;

  if (record.status_libur == 1 || String(record.status_libur) === 'true') {
    html += `<div style="padding:25px; background:#fce4e4; color:#cc0000; font-weight:bold; text-align:center; border: 2px dashed #cc0000; margin-top:30px; font-size:16px;">
        INSTANSI / TEMPAT PKL LIBUR ATAU TUTUP<br>
        <span style="font-size:13px; font-weight:normal; margin-top:10px; display:block;">Alasan: ${record.libur_reason || record.alasan_libur || '-'}</span>
    </div>`;
  } else {
    html += `
    <table>
      <thead>
        <tr>
          <th width="5%">No</th>
          <th width="20%">Nama Siswa</th>
          <th width="10%">Status</th>
          <th width="45%">Jurnal Kegiatan</th>
          <th width="20%">Foto Bukti</th>
        </tr>
      </thead>
      <tbody>
    `;
    
    finalMembersList.forEach((mInfo, idx) => {
      const mName = idx === 0 ? ketuaName : listAnggota[idx-1];
      const statusRaw = attendanceData[mName] || 'alpha';
      const statusLower = statusRaw.toLowerCase();
      let statusText = statusRaw.toUpperCase();
      if (statusText === 'H') statusText = 'HADIR';
      if (statusText === 'S') statusText = 'SAKIT';
      if (statusText === 'I') statusText = 'IZIN';
      if (statusText === 'A') statusText = 'ALPHA';
      
      const statusClass = ['hadir','sakit','izin','alpha'].includes(statusLower) ? statusLower : (statusText==='HADIR'?'hadir':(statusText==='SAKIT'?'sakit':(statusText==='IZIN'?'izin':'alpha')));

      const journalText = jurnalKegiatan[mName] || '-';
      
      let photoHtml = '<span style="color:#999; font-size:10px;">Tidak ada</span>';
      if (photoUrls) {
        // Handle array or object
        const url = Array.isArray(photoUrls) ? photoUrls[idx] : photoUrls[mName];
        if (url && typeof url === 'string' && url.startsWith('http')) {
           photoHtml = `<img src="${url}" class="photo" />`; 
        }
      }

      html += `
        <tr>
          <td align="center">${idx + 1}</td>
          <td><strong>${mName}</strong></td>
          <td class="status-${statusClass}">${statusText}</td>
          <td><div class="jurnal-text">"${journalText}"</div></td>
          <td align="center">${photoHtml}</td>
        </tr>
      `;
    });
    
    html += `</tbody></table>`;
  }

  html += `
    <div style="margin-top: 50px; font-size: 10px; color: #888; text-align: center; border-top: 1px solid #eee; padding-top: 10px;">
      Dokumen ini dihasilkan secara otomatis oleh sistem SIAKANUDA menggunakan Google Apps Script.<br>
      Waktu Generate: ${new Date().toLocaleString('id-ID')}
    </div>
  </body></html>`;
  
  return html;
}

export async function requestPdfFromGas(htmlContent, filename) {
  const url = process.env.GAS_PDF_URL;
  if (!url) throw new Error("GAS_PDF_URL belum diatur di .env");

  const response = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ html: htmlContent, filename })
  });

  const text = await response.text();
  try {
    const data = JSON.parse(text);
    if (data.status !== 'success') {
      throw new Error(data.message || "Gagal membuat PDF di GAS.");
    }
    return data; // { download_url, view_url, file_id }
  } catch (e) {
    throw new Error("Respons tidak valid dari GAS: " + text.substring(0, 100));
  }
}
