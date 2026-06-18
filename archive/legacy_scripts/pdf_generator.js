/**
 * execution/pdf_generator.js
 * SIAKANUDA v1.0.0 — Tier 3: PDF Document Generator (pdfkit)
 * Generates PDF reports in intranet memory/temp folder to send via WhatsApp.
 */

import PDFDocument from 'pdfkit';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { PDFDocument as PDFLibDoc, rgb } from 'pdf-lib';
import * as db from './db.js';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const TMP_DIR   = path.join(__dirname, '..', '.tmp');
const LOGO_PATH = path.join(__dirname, '..', 'public', 'logo-smk.png');
const KOP_PATH  = path.join(__dirname, '..', 'data', 'Kop surat.pdf');
const KOP_LANDSCAPE_PATH = path.join(__dirname, '..', 'data', 'Kop surat lanscape.pdf');
const PKL_TEMPLATE_PATH = path.join(__dirname, '..', 'data', 'template_laporan_harian_pkl.pdf');

if (!fs.existsSync(TMP_DIR)) {
  fs.mkdirSync(TMP_DIR, { recursive: true });
}

async function applyKopSurat(pdfPath) {
  try {
    const pdfBytes = fs.readFileSync(pdfPath);
    const doc = await PDFLibDoc.load(pdfBytes);
    const pages = doc.getPages();

    if (pages.length > 0) {
      const firstPage = pages[0];
      const { width, height } = firstPage.getSize();
      
      const isLandscape = width > height;
      const targetKopPath = isLandscape ? KOP_LANDSCAPE_PATH : KOP_PATH;

      if (!fs.existsSync(targetKopPath)) return;

      const kopBytes = fs.readFileSync(targetKopPath);
      const kopDoc = await PDFLibDoc.load(kopBytes);

      const [kopPage] = await doc.embedPdf(kopDoc);

      firstPage.drawPage(kopPage, {
        x: 0,
        y: 0,
        width: width,
        height: height,
      });

      // Overlay high-res logo over the pixelated logo in the Kop Surat
      if (fs.existsSync(LOGO_PATH)) {
        const logoBytes = fs.readFileSync(LOGO_PATH);
        const logoImage = await doc.embedPng(logoBytes);
        
        const logoCoords = isLandscape 
          ? { x: 40.50, y: 505.42, width: 66.60, height: 66.75 }
          : { x: 73.45, y: 731.27, width: 66.60, height: 66.75 };
          
        // Draw white background to completely hide the low-res logo underneath
        firstPage.drawRectangle({
          x: logoCoords.x,
          y: logoCoords.y,
          width: logoCoords.width,
          height: logoCoords.height,
          color: rgb(1, 1, 1),
        });
        
        // Draw the high-res logo PNG image
        firstPage.drawImage(logoImage, {
          x: logoCoords.x,
          y: logoCoords.y,
          width: logoCoords.width,
          height: logoCoords.height,
        });
      }
    }

  } catch (err) {
    console.error('[PDF-GEN] Gagal menempelkan Kop Surat:', err.message);
  }
}

/**
 * Apply template laporan harian PKL (template_laporan_harian_pkl.pdf) as BACKGROUND overlay,
 * then place pdfkit-generated DATA content on TOP of the template.
 * Strategy: create a brand-new pdf-lib document, embed template first (bottom layer),
 * then embed pdfkit content on top (foreground).
 * @param {string} pdfPath Path to generated PDF file
 */
async function applyTemplateHarianPkl(pdfPath) {
  try {
    if (!fs.existsSync(PKL_TEMPLATE_PATH)) {
      console.warn('[PDF-GEN] Template harian PKL tidak ditemukan, tidak ada overlay template.');
      return;
    }

    const pdfBytes = fs.readFileSync(pdfPath);
    const pdfkitDoc = await PDFLibDoc.load(pdfBytes);
    const pdfkitPages = pdfkitDoc.getPages();
    if (pdfkitPages.length === 0) return;

    const { width, height } = pdfkitPages[0].getSize();

    // Load template PDF
    const templBytes = fs.readFileSync(PKL_TEMPLATE_PATH);
    const templDoc = await PDFLibDoc.load(templBytes);

    // Create a fresh document to assemble layers
    const finalDoc = await PDFLibDoc.create();
    const page = finalDoc.addPage([width, height]);

    // Layer 1 (bottom): Embed template page as background
    const [embeddedTempl] = await finalDoc.embedPdf(templDoc);
    page.drawPage(embeddedTempl, { x: 0, y: 0, width, height });

    // Layer 2 (middle): Embed pdfkit content as foreground on top of template
    const [embeddedPdfkit] = await finalDoc.embedPdf(pdfkitDoc);
    page.drawPage(embeddedPdfkit, { x: 0, y: 0, width, height });

    // Layer 3 (topmost): Overlay high-res logo on the template's logo area
    if (fs.existsSync(LOGO_PATH)) {
      const logoBytes = fs.readFileSync(LOGO_PATH);
      const logoImage = await finalDoc.embedPng(logoBytes);

      // Template is always portrait A4
      const logoCoords = {
        x: 73.45, y: 731.27, width: 66.60, height: 66.75,
      };

      // White box to hide low-res logo in template
      page.drawRectangle({
        x: logoCoords.x, y: logoCoords.y,
        width: logoCoords.width, height: logoCoords.height,
        color: rgb(1, 1, 1),
      });

      // High-res logo image
      page.drawImage(logoImage, logoCoords);
    }

    const savedBytes = await finalDoc.save();
    fs.writeFileSync(pdfPath, savedBytes);
    console.log('[PDF-GEN] Template harian PKL overlay berhasil (template=bg, data=fg).');
  } catch (err) {
    console.error('[PDF-GEN] Gagal menempelkan template harian PKL:', err.message);
  }
}

/**
 * Helper to draw tables in pdfkit
 */
function drawTable(doc, startX, startY, headers, rows, colWidths) {
  let y = startY;
  const totalWidth = colWidths.reduce((a, b) => a + b, 0);

  // Draw headers
  doc.font('Helvetica-Bold').fontSize(10);
  let x = startX;
  headers.forEach((h, i) => {
    doc.text(h, x, y, { width: colWidths[i], align: 'left' });
    x += colWidths[i];
  });
  y += 18;

  // Draw header bottom line
  doc.moveTo(startX, y).lineTo(startX + totalWidth, y).strokeColor('#333333').lineWidth(1).stroke();
  y += 6;

  // Draw rows
  doc.font('Helvetica').fontSize(9);
  rows.forEach((row, rowIdx) => {
    let x = startX;
    
    // Check height constraints, add page if near bottom
    if (y > 720) {
      doc.addPage();
      y = 50;
      
      // Re-draw headers on new page
      doc.font('Helvetica-Bold').fontSize(10);
      x = startX;
      headers.forEach((h, i) => {
        doc.text(h, x, y, { width: colWidths[i], align: 'left' });
        x += colWidths[i];
      });
      y += 18;
      doc.moveTo(startX, y).lineTo(startX + totalWidth, y).strokeColor('#333333').stroke();
      y += 6;
      doc.font('Helvetica').fontSize(9);
    }

    // Zebra striping background
    if (rowIdx % 2 === 1) {
      doc.fillColor('#f9f9f9').rect(startX, y - 4, totalWidth, 18).fill();
    }

    doc.fillColor('#000000');
    x = startX;
    row.forEach((cell, i) => {
      // Handle multiline strings in Jurnal
      const cellText = String(cell || '');
      doc.text(cellText, x, y, { width: colWidths[i] - 5, align: 'left', height: 14, ellipsis: true });
      x += colWidths[i];
    });
    y += 18;
  });
}

/**
 * Generate PDF Rekap Absensi Kelas
 * @param {string} className 
 * @returns {Promise<string>} Path to temporary PDF file
 */
function drawVerticalGridLines(doc, startX, startY, endY, noWidth, namaWidth, nisWidth, dayColWidth, numDays, sumWidth) {
  doc.strokeColor('#aaaaaa').lineWidth(0.5);
  let x = startX;
  
  // Left border
  doc.moveTo(x, startY).lineTo(x, endY).stroke();
  x += noWidth;
  
  // After No
  doc.moveTo(x, startY).lineTo(x, endY).stroke();
  x += namaWidth;
  
  // After Name
  doc.moveTo(x, startY).lineTo(x, endY).stroke();
  x += nisWidth;
  
  // After NISN
  doc.moveTo(x, startY).lineTo(x, endY).stroke();

  // Between Days (separators start from startY + 18, except the last one which is the separator before Rekap)
  for (let d = 1; d <= numDays; d++) {
    x += dayColWidth;
    const isLastDay = (d === numDays);
    const lineStartY = isLastDay ? startY : (startY + 18);
    doc.moveTo(x, lineStartY).lineTo(x, endY).stroke();
  }

  // Between Summaries (separators start from startY + 18, except the last one which is the right border)
  for (let s = 1; s <= 4; s++) {
    x += sumWidth;
    const isRightBorder = (s === 4);
    const lineStartY = isRightBorder ? startY : (startY + 18);
    doc.moveTo(x, lineStartY).lineTo(x, endY).stroke();
  }
}

function drawMonthlyAttendanceTable(doc, startX, startY, numDays, studentRecsMap, targetMonth, targetYear) {
  const tableWidth = 770;
  let startYActive = startY;
  
  // Column Widths
  const noWidth = 20;
  const namaWidth = 180;
  const nisWidth = 60;
  const sumWidth = 14; // H, S, I, A
  const totalSumWidth = sumWidth * 4; // 56
  const dayColWidth = (tableWidth - noWidth - namaWidth - nisWidth - totalSumWidth) / numDays;

  let y = startY;
  
  // Draw header background (36pt height for two-row header)
  doc.fillColor('#f5f5f5').rect(startX, y, tableWidth, 36).fill();

  // Highlighting Sundays in header background (row 2 only)
  for (let d = 1; d <= numDays; d++) {
    const isSunday = new Date(targetYear, targetMonth, d).getDay() === 0;
    if (isSunday) {
      const colX = startX + noWidth + namaWidth + nisWidth + (d - 1) * dayColWidth;
      doc.fillColor('#fcedeb').rect(colX, y + 18, dayColWidth, 18).fill();
    }
  }

  doc.fillColor('#000000');
  doc.strokeColor('#aaaaaa').lineWidth(0.5);

  // Draw header texts - Row 1
  let x = startX;
  doc.font('Helvetica-Bold').fontSize(8.5);
  doc.text('No', x, y + 14, { width: noWidth, align: 'center' });
  x += noWidth;

  doc.text('Nama Siswa', x + 3, y + 14, { width: namaWidth - 3, align: 'left' });
  x += namaWidth;

  doc.text('NISN', x, y + 14, { width: nisWidth, align: 'center' });
  x += nisWidth;

  // Spanning header for Periode
  const tempDate = new Date(targetYear, targetMonth, 1);
  const monthLabel = tempDate.toLocaleDateString('id-ID', { year: 'numeric', month: 'long' });
  const dayHeaderWidth = dayColWidth * numDays;
  doc.text(`Periode Bulan ${monthLabel}`, x, y + 5, { width: dayHeaderWidth, align: 'center' });
  
  // Spanning header for Rekap
  const rekapWidth = sumWidth * 4;
  doc.text('Rekap', x + dayHeaderWidth, y + 5, { width: rekapWidth, align: 'center' });

  // Row 2 - Days and Summaries
  doc.fontSize(7);
  let xRow2 = x;
  for (let d = 1; d <= numDays; d++) {
    const isSunday = new Date(targetYear, targetMonth, d).getDay() === 0;
    if (isSunday) {
      doc.fillColor('#c0392b');
    } else {
      doc.fillColor('#000000');
    }
    doc.text(String(d), xRow2, y + 24, { width: dayColWidth, align: 'center' });
    xRow2 += dayColWidth;
  }

  doc.fillColor('#000000').fontSize(8);
  const sumLabels = ['H', 'S', 'I', 'A'];
  sumLabels.forEach(label => {
    doc.text(label, xRow2, y + 24, { width: sumWidth, align: 'center' });
    xRow2 += sumWidth;
  });

  // Draw intermediate horizontal line separating Row 1 and Row 2 of header (after NISN)
  doc.moveTo(x, y + 18).lineTo(startX + tableWidth, y + 18).stroke();

  y += 36;

  // Draw header bottom border
  doc.moveTo(startX, y).lineTo(startX + tableWidth, y).stroke();

  // Draw rows
  studentRecsMap.forEach(({ student, monthlyRecs }, index) => {
    // Check overflow (landscape height is 595, wrap at 540)
    if (y > 540) {
      // Close borders for current page table
      doc.moveTo(startX, y).lineTo(startX + tableWidth, y).stroke();
      drawVerticalGridLines(doc, startX, startYActive, y, noWidth, namaWidth, nisWidth, dayColWidth, numDays, sumWidth);

      doc.addPage();
      y = 70; // Start higher since no Kop Surat on page 2+
      startYActive = y;

      // Redraw headers on new page (two-row header)
      doc.fillColor('#f5f5f5').rect(startX, y, tableWidth, 36).fill();
      for (let d = 1; d <= numDays; d++) {
        const isSunday = new Date(targetYear, targetMonth, d).getDay() === 0;
        if (isSunday) {
          const colX = startX + noWidth + namaWidth + nisWidth + (d - 1) * dayColWidth;
          doc.fillColor('#fcedeb').rect(colX, y + 18, dayColWidth, 18).fill();
        }
      }
      doc.fillColor('#000000');
      doc.strokeColor('#aaaaaa').lineWidth(0.5);

      let xNew = startX;
      doc.font('Helvetica-Bold').fontSize(8.5);
      doc.text('No', xNew, y + 14, { width: noWidth, align: 'center' });
      xNew += noWidth;

      doc.text('Nama Siswa', xNew + 3, y + 14, { width: namaWidth - 3, align: 'left' });
      xNew += namaWidth;

      doc.text('NISN', xNew, y + 14, { width: nisWidth, align: 'center' });
      xNew += nisWidth;

      // Spanning headers
      const dayHeaderWidthNew = dayColWidth * numDays;
      doc.text(`Periode Bulan ${monthLabel}`, xNew, y + 5, { width: dayHeaderWidthNew, align: 'center' });
      doc.text('Rekap', xNew + dayHeaderWidthNew, y + 5, { width: sumWidth * 4, align: 'center' });

      // Row 2
      doc.fontSize(7);
      let xRow2New = xNew;
      for (let d = 1; d <= numDays; d++) {
        const isSunday = new Date(targetYear, targetMonth, d).getDay() === 0;
        if (isSunday) {
          doc.fillColor('#c0392b');
        } else {
          doc.fillColor('#000000');
        }
        doc.text(String(d), xRow2New, y + 24, { width: dayColWidth, align: 'center' });
        xRow2New += dayColWidth;
      }

      doc.fillColor('#000000').fontSize(8);
      sumLabels.forEach(label => {
        doc.text(label, xRow2New, y + 24, { width: sumWidth, align: 'center' });
        xRow2New += sumWidth;
      });

      doc.moveTo(xNew, y + 18).lineTo(startX + tableWidth, y + 18).stroke();

      y += 36;
      doc.moveTo(startX, y).lineTo(startX + tableWidth, y).stroke();
    }

    // Highlight Sunday cells
    for (let d = 1; d <= numDays; d++) {
      const isSunday = new Date(targetYear, targetMonth, d).getDay() === 0;
      if (isSunday) {
        const cellX = startX + noWidth + namaWidth + nisWidth + (d - 1) * dayColWidth;
        doc.fillColor('#fdf2f2').rect(cellX, y, dayColWidth, 18).fill();
      }
    }
    doc.fillColor('#000000');

    // Create status mapping for student
    const statusMap = {};
    monthlyRecs.forEach(r => {
      if (!r.date) return;
      const parts = r.date.split('-');
      const dayNum = parseInt(parts[2], 10);
      statusMap[dayNum] = r.status;
    });

    let xRow = startX;

    // 1. No
    doc.font('Helvetica').fontSize(8);
    doc.text(String(index + 1), xRow, y + 5, { width: noWidth, align: 'center' });
    xRow += noWidth;

    // 2. Nama Siswa
    doc.text(student.name, xRow + 3, y + 5, { width: namaWidth - 4, align: 'left', height: 12, ellipsis: true });
    xRow += namaWidth;

    // 3. NIS
    doc.text(student.nis || '-', xRow, y + 5, { width: nisWidth, align: 'center' });
    xRow += nisWidth;

    // 4. Days
    let sakit = 0, izin = 0, alpha = 0, hadir = 0;
    doc.font('Helvetica-Bold').fontSize(7);

    for (let d = 1; d <= numDays; d++) {
      const status = statusMap[d];
      let char = '';
      let color = '#000000';

      if (status === 'hadir') {
        hadir++;
        char = ''; // Blank for present (hadir)
      } else if (status === 'sakit') {
        sakit++;
        char = 'S';
        color = '#000000';
      } else if (status === 'izin') {
        izin++;
        char = 'I';
        color = '#000000';
      } else if (status === 'alpha') {
        alpha++;
        char = 'A';
        color = '#000000';
      }

      if (char) {
        doc.fillColor(color);
        doc.text(char, xRow, y + 5, { width: dayColWidth, align: 'center' });
        doc.fillColor('#000000');
      }
      xRow += dayColWidth;
    }

    // 5. Totals H, S, I, A
    doc.font('Helvetica').fontSize(8);
    doc.text(String(hadir), xRow, y + 5, { width: sumWidth, align: 'center' });
    xRow += sumWidth;
    doc.text(String(sakit), xRow, y + 5, { width: sumWidth, align: 'center' });
    xRow += sumWidth;
    doc.text(String(izin), xRow, y + 5, { width: sumWidth, align: 'center' });
    xRow += sumWidth;
    doc.text(String(alpha), xRow, y + 5, { width: sumWidth, align: 'center' });
    xRow += sumWidth;

    y += 18;
    // Draw row bottom line
    doc.moveTo(startX, y).lineTo(startX + tableWidth, y).stroke();
  });

  // Draw final vertical grid lines
  drawVerticalGridLines(doc, startX, startYActive, y, noWidth, namaWidth, nisWidth, dayColWidth, numDays, sumWidth);

  return y;
}

/**
 * Generate PDF Rekap Absensi Kelas
 * @param {string} className 
 * @returns {Promise<string>} Path to temporary PDF file
 */
export async function generateClassReportPDF(className, month = null, year = null, metadata = {}) {
  return new Promise(async (resolve, reject) => {
    try {
      const today = new Date().toISOString().split('T')[0];
      const now = new Date();
      const targetMonth = (month !== null) ? parseInt(month, 10) - 1 : now.getMonth();
      const targetYear = (year !== null) ? parseInt(year, 10) : now.getFullYear();

      const tempDate = new Date(targetYear, targetMonth, 1);
      const monthLabel = tempDate.toLocaleDateString('id-ID', { year: 'numeric', month: 'long' });
      const numDays = new Date(targetYear, targetMonth + 1, 0).getDate();
      const students = await db.getStudentsByClass(className);

      if (students.length === 0) {
        throw new Error(`Kelas ${className} tidak memiliki data siswa.`);
      }

      // Fetch monthly records for students in this class
      let totalRecords = 0;
      const studentRecsMap = [];
      for (const student of students) {
        const attendance = await db.getAttendanceByStudent(student.id);
        const monthlyRecs = attendance.filter(r => {
          if (!r.date) return false;
          const parts = r.date.split('-');
          const m = parseInt(parts[1], 10) - 1;
          const y = parseInt(parts[0], 10);
          return m === targetMonth && y === targetYear;
        });
        totalRecords += monthlyRecs.length;
        studentRecsMap.push({ student, monthlyRecs });
      }

      if (totalRecords === 0) {
        throw new Error(`Data absensi bulan ini untuk kelas ${className} kosong.`);
      }

      const filePath = path.join(TMP_DIR, `Laporan_Absen_${className.replace(/\s+/g, '_')}_${Date.now()}.pdf`);
      const doc = new PDFDocument({ margins: { top: 36, bottom: 36, left: 36, right: 36 }, size: 'A4', layout: 'landscape' });
      const writeStream = fs.createWriteStream(filePath);
      doc.pipe(writeStream);

      // Header Kop Surat & Coordinate calculation
      let titleY, subtitleY, waliKelasY, downloadTimeY, tableY;
      const hasKop = fs.existsSync(KOP_LANDSCAPE_PATH);
      if (!hasKop) {
        if (fs.existsSync(LOGO_PATH)) {
          doc.image(LOGO_PATH, 36, 25, { width: 30, height: 30 });
          doc.fillColor('#0b6623').fontSize(13).font('Helvetica-Bold').text('SIAKANUDA — SMK NU DARUSSALAM', 76, 28);
          doc.fillColor('#555555').fontSize(8).font('Helvetica').text(`Dokumen Rekapitulasi Presensi KBM Kelas • Generated: ${today}`, 76, 43);
        } else {
          doc.fillColor('#0b6623').fontSize(14).font('Helvetica-Bold').text('SIAKANUDA — SMK NU DARUSSALAM', 36, 30);
          doc.fillColor('#555555').fontSize(8.5).font('Helvetica').text(`Dokumen Rekapitulasi Presensi KBM Kelas • Generated: ${today}`, 36, 45);
        }
        doc.moveTo(36, 60).lineTo(806, 60).strokeColor('#0b6623').lineWidth(1.5).stroke();
        
        titleY = 75;
        subtitleY = 88;
        waliKelasY = 108;
        downloadTimeY = 120;
        tableY = 134;
      } else {
        titleY = 118;
        subtitleY = 131;
        waliKelasY = 159;
        downloadTimeY = 172;
        tableY = 193;
      }

      // Report Info
      const tpLabel = metadata.tp || '2025/2026';
      const downloaderLabel = metadata.downloader || 'Guru';
      const downloadTimeLabel = metadata.downloadTime || today;
      const waliKelasLabel = metadata.waliKelas || '-';

      doc.fillColor('#000000').fontSize(11).font('Helvetica-Bold').text(`REKAP ABSENSI BULANAN — KELAS ${className.toUpperCase()}`, 36, titleY, { align: 'center', width: 770 });
      doc.text(`Tahun Pelajaran ${tpLabel}`, 36, subtitleY, { align: 'center', width: 770 });
      
      doc.fontSize(9.5).font('Helvetica-Bold').text(`Wali Kelas : ${waliKelasLabel}`, 36, waliKelasY);
      doc.fontSize(7.5).font('Helvetica-Oblique').text(`( SIAKANUDA - Tanggal Unduh: ${downloadTimeLabel}, Pengunduh: ${downloaderLabel} )`, 36, downloadTimeY);

      const endY = drawMonthlyAttendanceTable(doc, 36, tableY, numDays, studentRecsMap, targetMonth, targetYear);

      doc.end();

      writeStream.on('finish', async () => {
        try {
          await applyKopSurat(filePath);
          resolve(filePath);
        } catch (err) {
          reject(err);
        }
      });
      writeStream.on('error', (err) => reject(err));
    } catch (e) {
      reject(e);
    }
  });
}
