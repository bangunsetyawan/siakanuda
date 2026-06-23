<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<style>
@page {
    size: A4;
    margin: 2cm 1.5cm 1.5cm 1.5cm; /* Top, Right, Bottom, Left */
}

@media print {
    /* Hide unneeded elements */
    .main-header, .main-sidebar, .card-header, .btn, form, footer, .no-print {
        display: none !important;
    }
    
    body {
        margin: 0;
        padding: 0;
        background: white;
        font-family: Arial, sans-serif;
        color: black;
    }
    
    .content-wrapper {
        margin-left: 0 !important;
        background: white !important;
        padding: 0 !important;
    }

    /* Optimize Backgrounds & Borders */
    .bg-light, .card, .card-body, .alert {
        background-color: transparent !important;
        box-shadow: none !important;
    }

    .report-card {
        page-break-inside: avoid;
        border: 1px solid #000 !important;
        margin-bottom: 15px !important;
        border-radius: 0 !important;
    }

    .table-bordered th, .table-bordered td {
        border: 1px solid #000 !important;
    }

    .badge {
        border: 1px solid #000 !important;
        color: #000 !important;
        background: transparent !important;
    }

    /* Print header */
    .print-header {
        display: block !important;
        text-align: center;
        margin-bottom: 20px;
        border-bottom: 2px solid black;
        padding-bottom: 10px;
    }
}

@media screen {
    .print-header {
        display: none;
    }
}
</style>

<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-lg">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold"><i class="fas fa-history text-primary mr-2"></i> Riwayat Laporan PKL</h5>
                    <div>
                        <button onclick="window.print()" class="btn btn-sm btn-primary font-weight-bold rounded-lg shadow-sm">
                            <i class="fas fa-print mr-1"></i> Cetak
                        </button>
                        <a href="<?= base_url('/pkl') ?>" class="btn btn-sm btn-secondary font-weight-bold rounded-lg shadow-sm ml-2">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>
                </div>
                
                <div class="card-body bg-light">
                    <!-- Filter Section -->
                    <form action="" method="get" class="mb-4 no-print p-3 bg-white border rounded shadow-sm">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label class="font-weight-bold text-secondary text-sm">Pilih Bulan</label>
                                <input type="month" name="month" class="form-control" value="<?= htmlspecialchars($currentMonth) ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-info btn-block font-weight-bold"><i class="fas fa-filter"></i> Filter</button>
                            </div>
                        </div>
                    </form>

                    <!-- Print Header -->
                    <div class="print-header mb-4">
                        <h4 style="margin:0; font-weight:bold; font-size: 18px;">REKAP JURNAL KEGIATAN PRAKTIK KERJA LAPANGAN (PKL)</h4>
                        <h4 style="margin:5px 0; font-weight:bold; font-size: 18px;">SMK NU DARUSSALAM</h4>
                        <p style="margin:0; font-size: 14px;">Tahun Pelajaran 2026-2027</p>
                        
                        <div style="text-align: left; margin-top: 25px;">
                            <table style="width: 100%; font-size: 14px; line-height: 1.5; border-collapse: collapse; border: none;">
                                <tr>
                                    <td style="width: 150px; vertical-align: top; border: none; padding: 2px;"><strong>Tempat PKL</strong></td>
                                    <td style="width: 10px; vertical-align: top; border: none; padding: 2px;">:</td>
                                    <td style="vertical-align: top; border: none; padding: 2px;"><?= htmlspecialchars($group['tempat_pkl'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <td style="vertical-align: top; border: none; padding: 2px;"><strong>Guru Pembimbing</strong></td>
                                    <td style="vertical-align: top; border: none; padding: 2px;">:</td>
                                    <td style="vertical-align: top; border: none; padding: 2px;"><?= htmlspecialchars($pembimbingName ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <td style="vertical-align: top; border: none; padding: 2px;"><strong>Kelompok</strong></td>
                                    <td style="vertical-align: top; border: none; padding: 2px;">:</td>
                                    <td style="vertical-align: top; border: none; padding: 2px;">
                                        <?php 
                                            $membersList = array_map('trim', explode(',', $group['anggota'] ?? ''));
                                            if (!empty($membersList)): 
                                        ?>
                                            <ol style="margin: 0; padding-left: 18px;">
                                                <?php foreach ($membersList as $index => $member): ?>
                                                    <li>
                                                        <?= htmlspecialchars($member) ?><?= ($member === ($ketuaName ?? '')) ? ' (Ketua)' : '' ?>
                                                        <a href="<?= base_url('/pkl/rekap-siswa/' . htmlspecialchars($group['ketua_phone']) . '?name=' . urlencode($member)) ?>" target="_blank" class="no-print btn btn-xs btn-outline-primary ml-2" title="Cetak Rekap Kehadiran Siswa Ini" style="padding: 0 5px; font-size: 10px; vertical-align: text-top;">
                                                            <i class="fas fa-print"></i> Cetak Rekap Absensi Individu
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ol>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <?php if (empty($reports)): ?>
                        <div class="alert alert-info text-center border-0 shadow-sm no-print">
                            <i class="fas fa-info-circle mr-2"></i> Belum ada laporan PKL untuk bulan <?= htmlspecialchars($currentMonth) ?>.
                        </div>
                    <?php else: ?>
                        <?php foreach ($reports as $report): ?>
                            <?php 
                                $members = array_map('trim', explode(',', $group['anggota'] ?? ''));
                                $attendanceData = json_decode($report['attendance_data'] ?? '{}', true) ?: [];
                                $jurnalData = json_decode($report['jurnal_kegiatan'] ?? '{}', true) ?: [];
                                $photoUrls = json_decode($report['photo_url'] ?? '{}', true) ?: [];
                            ?>
                            <div class="card report-card border mb-3 shadow-sm rounded-lg">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                        <h6 class="font-weight-bold text-dark mb-0">
                                            <i class="far fa-calendar-alt text-primary mr-1"></i>
                                            <?php
                                                $repDateObj = DateTime::createFromFormat('Y-m-d', $report['date']);
                                                $days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                                                $monthNames = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                                if ($repDateObj) {
                                                    echo $days[$repDateObj->format('w')] . ', ' . $repDateObj->format('d') . ' ' . $monthNames[(int)$repDateObj->format('n')] . ' ' . $repDateObj->format('Y');
                                                } else {
                                                    echo $report['date'];
                                                }
                                            ?>
                                        </h6>
                                        <?php if ($report['status_libur'] == 1): ?>
                                            <span class="badge badge-danger">LIBUR</span>
                                        <?php else: ?>
                                            <div class="text-right text-xs text-secondary no-print">
                                                <?php
                                                    $locText = $report['location_data'] ?? 'Lokasi tidak dicantumkan';
                                                    if (strpos($locText, '| GPS:') !== false) {
                                                        $parts = explode('| GPS:', $locText);
                                                        $locName = trim($parts[0]);
                                                        $gpsCoords = trim($parts[1]);
                                                        echo htmlspecialchars($locName) . ' <a href="https://maps.google.com/?q=' . urlencode($gpsCoords) . '" target="_blank" class="ml-1 text-primary font-weight-bold"><i class="fas fa-map-marker-alt"></i> Peta</a>';
                                                    } else {
                                                        echo htmlspecialchars($locText);
                                                    }
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($report['status_libur'] == 1): ?>
                                        <div class="alert alert-warning py-2 mb-0">
                                            <strong>Alasan Libur:</strong> <?= htmlspecialchars($report['alasan_libur'] ?? '-') ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="row">
                                            <div class="col-12">
                                                <table class="table table-sm table-bordered mb-0" style="font-size: 13px;">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th width="30%">Nama</th>
                                                            <th width="15%" class="text-center">Status</th>
                                                            <th>Jurnal / Keterangan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($members as $name): ?>
                                                            <?php 
                                                                $status = $attendanceData[$name] ?? 'alpha';
                                                                $jurnal = $jurnalData[$name] ?? '-';
                                                                $statusColor = ['hadir' => 'success', 'sakit' => 'warning', 'izin' => 'info', 'alpha' => 'danger'][strtolower($status)] ?? 'secondary';
                                                            ?>
                                                            <tr>
                                                                <td class="align-middle font-weight-bold"><?= htmlspecialchars($name) ?></td>
                                                                <td class="text-center align-middle">
                                                                    <span class="badge badge-<?= $statusColor ?>"><?= strtoupper($status) ?></span>
                                                                </td>
                                                                <td class="align-middle"><?= htmlspecialchars($jurnal) ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>

                                                <?php if (!empty($photoUrls)): ?>
                                                    <div class="mt-3">
                                                        <small class="text-secondary d-block mb-2 font-weight-bold"><i class="fas fa-camera mr-1"></i>Lampiran Foto (<?= count($photoUrls) ?> foto):</small>
                                                        <div class="d-flex flex-wrap" style="gap: 8px;">
                                                            <?php foreach ($photoUrls as $key => $url): ?>
                                                                <?php $dispUrl = get_photo_display_url($url); ?>
                                                                <div class="text-center" style="width: 100px;">
                                                                    <a href="<?= htmlspecialchars($dispUrl) ?>" target="_blank">
                                                                        <img src="<?= htmlspecialchars($dispUrl) ?>" class="rounded border shadow-sm" style="width: 100px; height: 100px; object-fit: cover;" title="Klik untuk perbesar">
                                                                    </a>
                                                                    <small class="d-block mt-1 text-muted text-xs text-truncate" title="<?= htmlspecialchars($key) ?>">
                                                                        <?= htmlspecialchars($key === 'kelompok' ? 'Kelompok' : 'Bukti ' . $key) ?>
                                                                    </small>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
