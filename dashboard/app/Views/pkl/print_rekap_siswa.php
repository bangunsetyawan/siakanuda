<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        /* Pengaturan Cetak Halaman (A4 Landscape dengan margin 1.27cm) */
        @page {
            size: A4 landscape;
            margin: 1.5cm 1.27cm 1.27cm 1.27cm;
        }

        /* Styling Umum */
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            color: #000;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }

        .page {
            width: 297mm;
            min-height: 210mm;
            background: #fff;
            padding: 1.5cm 1.27cm 1.27cm 1.27cm;
            box-sizing: border-box;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }
            .page {
                box-shadow: none;
                margin: 0;
                padding: 0;
                width: 100%;
                min-height: auto; 
                display: block; 
            }
            .no-print {
                display: none;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .font-italic { font-style: italic; }
        
        .title-container h2 {
            margin: 0;
            font-size: 15px;
        }
        .title-container {
            margin-bottom: 40px; /* Diperbesar sesuai permintaan user */
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 2px 4px;
        }

        .biodata-row td {
            padding: 2px 4px;
        }

        .input-row td {
            height: 40px; /* Diperbesar agar tabel mengisi kertas A4 dengan baik */
        }

        .bg-khaki { background-color: #eaddb6 !important; }
        .bg-orange { background-color: #ffc000 !important; }
        .bg-green { background-color: #92d050 !important; }
        .bg-gray { background-color: #808080 !important; }

        .footer-cell {
            vertical-align: top;
            padding: 8px 8px;
            height: 120px; /* Tinggi kotak TTD */
            position: relative; 
        }
        
        .keterangan-text {
            font-size: 11px;
            line-height: 1.3;
        }

        .ttd-box {
            position: relative;
            height: 100%;
        }

        .signature-line {
            position: absolute;
            bottom: 2px;
            left: 0;
            width: 85%;
            border-bottom: 1px solid #4a86e8; 
            text-align: center;
            font-weight: bold;
            font-size: 13px;
        }
    </style>
</head>
<body>

    <button class="no-print btn-print" onclick="window.print()" style="position: fixed; bottom: 20px; right: 20px; background-color: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 14px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 1000;">🖨️ Cetak Halaman</button>

    <div class="page">
        <div class="title-container text-center">
            <h2 style="text-decoration: underline; font-weight: bold;">REKAPITULASI ABSENSI KEHADIRAN PESERTA PKL</h2>
            <h2 style="font-weight: bold;">SMK NU DARUSSALAM TAHUN PELAJARAN <?= date('Y') ?>/<?= date('Y')+1 ?></h2>
        </div>

        <table>
            <colgroup>
                <col style="width: 2.6%;"><col style="width: 2.6%;"><col style="width: 2.6%;"><col style="width: 2.6%;"><col style="width: 2.6%;">
                <col style="width: 2.6%;"><col style="width: 2.6%;"><col style="width: 2.6%;"><col style="width: 2.6%;"><col style="width: 2.6%;">
                <col style="width: 2.6%;"><col style="width: 2.6%;"><col style="width: 2.6%;"><col style="width: 2.6%;"><col style="width: 2.6%;">
                <col style="width: 2.6%;"><col style="width: 2.6%;"><col style="width: 2.6%;"><col style="width: 2.6%;"><col style="width: 2.6%;">
                <col style="width: 2.6%;"><col style="width: 2.6%;"><col style="width: 2.6%;"><col style="width: 2.6%;"><col style="width: 2.6%;">
                <col style="width: 2.6%;"><col style="width: 2.6%;"><col style="width: 2.6%;"><col style="width: 2.6%;"><col style="width: 2.6%;">
                <col style="width: 2.6%;">
                <col style="width: 2%;">
                <col style="width: 3.5%;"><col style="width: 3.5%;"><col style="width: 3.5%;"><col style="width: 3.5%;"><col style="width: 3.5%;">
            </colgroup>

            <tbody>
                <tr class="biodata-row">
                    <td colspan="5" class="font-bold text-left" style="border-right: none;">Nama Siswa PKL</td>
                    <td colspan="14" style="border-left: none;">: <?= htmlspecialchars($studentName) ?></td>
                    <td colspan="6" class="font-bold text-left" style="border-right: none;">Guru Pembimbing</td>
                    <td colspan="12" style="border-left: none;">: <?= htmlspecialchars($guruPembimbing) ?></td>
                </tr>
                <tr class="biodata-row">
                    <td colspan="5" class="font-bold text-left" style="border-right: none;">Kelas/Jurusan</td>
                    <td colspan="14" style="border-left: none;">: <?= htmlspecialchars($kelasJurusan) ?></td>
                    <td colspan="6" class="font-bold text-left" style="border-right: none;">Ketua Kelompok</td>
                    <td colspan="12" style="border-left: none;">: <?= htmlspecialchars($ketuaName) ?></td>
                </tr>
                <tr class="biodata-row">
                    <td colspan="5" class="font-bold text-left" style="border-right: none;">Tempat PKL</td>
                    <td colspan="32" style="border-left: none;">: <?= htmlspecialchars($group['tempat_pkl'] ?? '-') ?></td>
                </tr>

                <tr>
                    <td colspan="37" class="text-center bg-khaki font-italic" style="font-size: 11px; border-bottom: 2px solid #000;">
                        Absensi Kehadiran ini dibuat Otomatis berdasarkan isian pada Aplikasi SIAKANUDA SMK NU Darussalam
                    </td>
                </tr>

                <script>
                    const monthsList = <?= json_encode($monthsList) ?>;
                    const formattedMonths = <?= json_encode($formattedMonths) ?>;
                    const attendanceMap = <?= json_encode($attendanceMap) ?>;
                    
                    monthsList.forEach(month => {
                        let totalH = 0;
                        let totalL = 0;
                        let totalS = 0;
                        let totalI = 0;
                        let totalA = 0;

                        // Header Bulan (Background Oranye)
                        document.write(`
                            <tr>
                                <td colspan="31" class="text-center font-bold bg-orange">BULAN : ${formattedMonths[month]}</td>
                                <td class="bg-gray"></td>
                                <td colspan="5" class="text-center font-bold">Jumlah</td>
                            </tr>
                            <tr>
                        `);
                        
                        // Deretan Angka Tanggal 1-31 (Miring)
                        for(let i=1; i<=31; i++) {
                            document.write(`<td class="text-center font-italic" style="font-size: 11px;">${i}</td>`);
                        }
                        
                        // Kolom Abu-abu + Kolom H L S I A (Background Hijau)
                        document.write(`
                                <td class="bg-gray"></td>
                                <td class="text-center font-italic font-bold bg-green">H</td>
                                <td class="text-center font-italic font-bold bg-green">L</td>
                                <td class="text-center font-italic font-bold bg-green">S</td>
                                <td class="text-center font-italic font-bold bg-green">I</td>
                                <td class="text-center font-italic font-bold bg-green">A</td>
                            </tr>
                            <tr class="input-row">
                        `);
                        
                        // Baris isi kehadiran
                        for(let i=1; i<=31; i++) {
                            const dateStr = month + '-' + i.toString().padStart(2, '0');
                            let cellVal = '';
                            let isL = false;
                            
                            // Cek jika tanggal valid dalam bulan ini
                            const d = new Date(dateStr);
                            // js date parsing issues workaround, manual valid date check:
                            const parts = dateStr.split('-');
                            const y = parseInt(parts[0], 10);
                            const m = parseInt(parts[1], 10) - 1;
                            const dt = parseInt(parts[2], 10);
                            const jsDate = new Date(y, m, dt);
                            
                            if (jsDate.getMonth() !== m) {
                                // Tanggal tidak valid (misal 31 Februari)
                                cellVal = '-';
                            } else if (attendanceMap[dateStr]) {
                                const status = attendanceMap[dateStr];
                                if (status === 'H' || status.toLowerCase() === 'hadir') { cellVal = '√'; totalH++; }
                                else if (status === 'S' || status.toLowerCase() === 'sakit') { cellVal = 'S'; totalS++; }
                                else if (status === 'I' || status.toLowerCase() === 'izin') { cellVal = 'I'; totalI++; }
                                else if (status === 'A' || status.toLowerCase() === 'alpha') { cellVal = 'A'; totalA++; }
                                else if (status === 'L' || status.toLowerCase() === 'libur') { cellVal = 'L'; totalL++; isL = true; }
                                else cellVal = status;
                            }
                            
                            document.write(`<td class="text-center font-bold" style="font-size:12px; ${isL ? 'color:red;' : ''}">${cellVal}</td>`);
                        }

                        document.write(`
                                <td class="bg-gray"></td>
                                <td class="text-center font-bold">${totalH > 0 ? totalH : ''}</td>
                                <td class="text-center font-bold">${totalL > 0 ? totalL : ''}</td>
                                <td class="text-center font-bold">${totalS > 0 ? totalS : ''}</td>
                                <td class="text-center font-bold">${totalI > 0 ? totalI : ''}</td>
                                <td class="text-center font-bold">${totalA > 0 ? totalA : ''}</td>
                            </tr>
                        `);
                    });
                </script>

                <!-- Footer: Keterangan & Area Tanda Tangan -->
                <tr>
                    <td colspan="15" class="footer-cell">
                        <div class="keterangan-text font-bold" style="margin-bottom: 2px;">Keterangan :</div>
                        <div class="keterangan-text font-italic">
                            Berikan tanda centang (√) untuk hari saat aktif atau masuk PKL. Berikan (S) untuk "sakit", (I) untuk "izin", (A) untuk Alfa/Tanpa Keterangan, dan (L) untuk Libur.
                        </div>
                    </td>
                    
                    <td colspan="11" class="footer-cell" style="border-right: none;">
                        <div class="ttd-box">
                            <div class="font-bold text-center">Guru Pembimbing</div>
                            <div class="signature-line" style="margin-left: auto; margin-right: auto; left: 0; right: 0; bottom: 5px;">
                                <?= htmlspecialchars($guruPembimbing) !== '-' ? htmlspecialchars($guruPembimbing) : '' ?>
                            </div>
                        </div>
                    </td>
                    
                    <td colspan="11" class="footer-cell" style="border-left: none;">
                        <div class="ttd-box text-center">
                            <div class="font-bold">Pembimbing Tempat PKL</div>
                            <div class="font-italic" style="font-size: 10px;">(Tanda tangan pimpinan & stempel tempat PKL)</div>
                            <div class="signature-line" style="margin-left: auto; margin-right: auto; left: 0; right: 0;"></div>
                        </div>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</body>
</html>
