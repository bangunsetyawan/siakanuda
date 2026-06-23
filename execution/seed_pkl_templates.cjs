const db = require('better-sqlite3')('../siakanuda.db');

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
    }
  } catch (e) {
    console.error(`[DB] Gagal seeding PKL template ${t.key}:`, e.message);
  }
}
console.log("Seeding complete.");
