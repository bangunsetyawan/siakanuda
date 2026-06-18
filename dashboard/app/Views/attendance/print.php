<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
            background: #fff;
            font-size: 10px;
        }
        .print-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .print-header h2 {
            margin: 0 0 5px 0;
            font-size: 16px;
        }
        .print-header h4 {
            margin: 0;
            font-size: 12px;
            font-weight: normal;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 11px;
        }
        .info-table td {
            padding: 2px 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            page-break-inside: auto;
        }
        .data-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }
        .data-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .text-left { text-align: left !important; }
        .text-bold { font-weight: bold; }
        .bg-sunday { background-color: #fcedeb !important; }
        .text-red { color: #c0392b; }
        
        .footer-info {
            margin-top: 15px;
            font-size: 9px;
            font-style: italic;
            color: #555;
        }

        /* Hide elements on screen */
        .no-print {
            display: block;
            margin-bottom: 20px;
            text-align: center;
            background: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        .btn-print {
            background: #0284c7;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <p>Gunakan orientasi <strong>Landscape</strong> saat mencetak dokumen ini.</p>
        <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
    </div>

    <div class="print-header">
        <h2>SIAKANUDA — SMK NU DARUSSALAM</h2>
        <h4>Dokumen Rekapitulasi Presensi KBM Kelas • Generated: <?= htmlspecialchars($downloadTime) ?></h4>
    </div>

    <table class="info-table">
        <tr>
            <td width="120"><strong>Kelas</strong></td>
            <td width="10">:</td>
            <td><strong><?= htmlspecialchars(strtoupper($className)) ?></strong></td>
            <td width="120"><strong>Wali Kelas</strong></td>
            <td width="10">:</td>
            <td><?= htmlspecialchars($waliName) ?></td>
        </tr>
        <tr>
            <td><strong>Bulan</strong></td>
            <td>:</td>
            <td>
                <?php
                $months = [
                    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                ];
                echo $months[$targetMonth] . ' ' . $targetYear;
                ?>
            </td>
            <td><strong>Tahun Pelajaran</strong></td>
            <td>:</td>
            <td><?= htmlspecialchars($tpLabel) ?></td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" width="20">No</th>
                <th rowspan="2" class="text-left" width="150">Nama Siswa</th>
                <th rowspan="2" width="50">NISN</th>
                <th colspan="<?= $numDays ?>">Tanggal</th>
                <th colspan="4">Rekap</th>
            </tr>
            <tr>
                <?php for ($d = 1; $d <= $numDays; $d++): ?>
                    <?php 
                        $dateStr = sprintf('%04d-%02d-%02d', $targetYear, $targetMonth, $d);
                        $isSunday = (date('w', strtotime($dateStr)) == 0);
                        $class = $isSunday ? 'bg-sunday text-red' : '';
                    ?>
                    <th class="<?= $class ?>"><?= $d ?></th>
                <?php endfor; ?>
                <th width="20" title="Hadir">H</th>
                <th width="20" title="Sakit">S</th>
                <th width="20" title="Izin">I</th>
                <th width="20" title="Alpha">A</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $index => $student): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td class="text-left text-bold"><?= htmlspecialchars($student['name']) ?></td>
                    <td><?= htmlspecialchars($student['nis'] ?? '-') ?></td>
                    
                    <?php 
                        $h = 0; $s = 0; $i = 0; $a = 0;
                        for ($d = 1; $d <= $numDays; $d++): 
                            $dateStr = sprintf('%04d-%02d-%02d', $targetYear, $targetMonth, $d);
                            $isSunday = (date('w', strtotime($dateStr)) == 0);
                            $status = $attendanceMap[$student['id']][$d] ?? null;
                            
                            $char = '';
                            if ($status === 'hadir') { $h++; $char = '.'; }
                            elseif ($status === 'sakit') { $s++; $char = 'S'; }
                            elseif ($status === 'izin') { $i++; $char = 'I'; }
                            elseif ($status === 'alpha') { $a++; $char = 'A'; }
                    ?>
                        <td class="<?= $isSunday ? 'bg-sunday' : '' ?>">
                            <?= $char ?>
                        </td>
                    <?php endfor; ?>
                    
                    <td class="text-bold"><?= $h ?></td>
                    <td class="text-bold"><?= $s ?></td>
                    <td class="text-bold"><?= $i ?></td>
                    <td class="text-bold text-red"><?= $a ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer-info">
        (SIAKANUDA - Tanggal Unduh: <?= htmlspecialchars($downloadTime) ?>, Pengunduh: <?= htmlspecialchars($downloader) ?>)
    </div>

    <script>
        // Auto print dialog when loaded
        setTimeout(() => {
            window.print();
        }, 500);
    </script>
</body>
</html>
