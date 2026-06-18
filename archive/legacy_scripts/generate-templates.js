import XLSX from 'xlsx';
import path from 'path';
import { fileURLToPath } from 'url';
import fs from 'fs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const targetDir = path.join(__dirname, '..', 'dashboard', 'public', 'templates');

if (!fs.existsSync(targetDir)) {
  fs.mkdirSync(targetDir, { recursive: true });
}

// Generate Student Template
const studentData = [
  { nisn: '1234567890', nama: 'Andi Saputra', kelas: 'XII TKJ', gender: 'L', whatsapp: '6281234567890', role: 'siswa' },
  { nisn: '0987654321', nama: 'Siti Aminah', kelas: 'XII TKJ', gender: 'P', whatsapp: '', role: 'ketua_pkl' }
];
const studentSheet = XLSX.utils.json_to_sheet(studentData);
const studentWorkbook = XLSX.utils.book_new();
XLSX.utils.book_append_sheet(studentWorkbook, studentSheet, 'Siswa');
XLSX.writeFile(studentWorkbook, path.join(targetDir, 'template_siswa.xlsx'));

// Generate Teacher Template
const teacherData = [
  { nama: 'Budi Utomo', whatsapp: '6281111111111', 'tugas tambahan': 'Wali Kelas', role: 'guru' },
  { nama: 'Rina Wijaya', whatsapp: '6282222222222', 'tugas tambahan': 'Pembina Osis', role: 'guru_bk' }
];
const teacherSheet = XLSX.utils.json_to_sheet(teacherData);
const teacherWorkbook = XLSX.utils.book_new();
XLSX.utils.book_append_sheet(teacherWorkbook, teacherSheet, 'Guru');
XLSX.writeFile(teacherWorkbook, path.join(targetDir, 'template_guru.xlsx'));

console.log('Template XLSX berhasil dibuat!');
