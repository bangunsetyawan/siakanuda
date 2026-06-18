<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid">
        <h1 class="m-0 font-weight-bold text-dark" style="font-size: 26px;">Rekap Absensi Saya</h1>
        <p class="text-secondary mb-0">Riwayat kehadiran KBM (Kegiatan Belajar Mengajar) kamu.</p>
    </div>
</div>

<!-- Kartu Identitas Siswa -->
<?php if (!empty($student)): ?>
<div class="card mb-4 border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
    <div class="card-body p-0">
        <div class="d-flex align-items-stretch">
            <div style="width: 6px; background: linear-gradient(180deg, #0ea5e9, #16a34a);"></div>
            <div class="p-3 p-md-4 flex-grow-1">
                <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center justify-content-center rounded-circle mr-3"
                         style="width:50px; height:50px; min-width:50px; background: linear-gradient(135deg, #0284c7, #0369a1);">
                        <i class="fas fa-user-graduate text-white" style="font-size: 20px;"></i>
                    </div>
                    <div>
                        <h5 class="font-weight-bold text-dark mb-0" style="font-size: 17px;"><?= htmlspecialchars($student['name']) ?></h5>
                        <div class="d-flex flex-wrap align-items-center" style="gap: 8px; margin-top: 4px;">
                            <span class="badge px-2 py-1" style="font-size: 11px; background: rgba(2,132,199,0.1); color: #0284c7; border-radius: 6px;">
                                <i class="fas fa-id-card mr-1"></i>NIS: <?= htmlspecialchars($student['nis'] ?? '-') ?>
                            </span>
                            <span class="badge px-2 py-1" style="font-size: 11px; background: rgba(22,163,74,0.1); color: #16a34a; border-radius: 6px;">
                                <i class="fas fa-chalkboard mr-1"></i><?= htmlspecialchars($student['class'] ?? '-') ?>
                            </span>
                            <?php if (!empty($student['gender'])): ?>
                            <span class="badge px-2 py-1" style="font-size: 11px; background: rgba(108,117,125,0.1); color: #6c757d; border-radius: 6px;">
                                <i class="fas fa-<?= $student['gender'] === 'L' ? 'mars' : 'venus' ?> mr-1"></i><?= $student['gender'] === 'L' ? 'Laki-laki' : 'Perempuan' ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Ringkasan Kehadiran -->
<div class="card mb-4 border-0 shadow-sm" style="border-radius: 16px;">
    <div class="card-header border-0 pb-0" style="background: transparent;">
        <h6 class="font-weight-bold text-dark mb-0"><i class="fas fa-chart-pie text-primary mr-2"></i>Ringkasan Kehadiran</h6>
    </div>
    <div class="card-body pt-3">
        <div class="row text-center">
            <div class="col-3">
                <div class="p-3 rounded-lg" style="background: rgba(40,167,69,0.08);">
                    <h3 class="font-weight-bold text-success mb-0"><?= $summary['hadir'] ?></h3>
                    <small class="text-secondary font-weight-bold" style="font-size: 12px;">Hadir</small>
                </div>
            </div>
            <div class="col-3">
                <div class="p-3 rounded-lg" style="background: rgba(255,193,7,0.08);">
                    <h3 class="font-weight-bold text-warning mb-0"><?= $summary['sakit'] ?></h3>
                    <small class="text-secondary font-weight-bold" style="font-size: 12px;">Sakit</small>
                </div>
            </div>
            <div class="col-3">
                <div class="p-3 rounded-lg" style="background: rgba(23,162,184,0.08);">
                    <h3 class="font-weight-bold text-info mb-0"><?= $summary['izin'] ?></h3>
                    <small class="text-secondary font-weight-bold" style="font-size: 12px;">Izin</small>
                </div>
            </div>
            <div class="col-3">
                <div class="p-3 rounded-lg" style="background: rgba(220,53,69,0.08);">
                    <h3 class="font-weight-bold text-danger mb-0"><?= $summary['alpha'] ?></h3>
                    <small class="text-secondary font-weight-bold" style="font-size: 12px;">Alpha</small>
                </div>
            </div>
        </div>
        <?php $total = $summary['hadir'] + $summary['sakit'] + $summary['izin'] + $summary['alpha']; ?>
        <?php if ($total > 0): ?>
        <div class="mt-3">
            <div class="progress" style="height: 8px; border-radius: 8px; overflow: hidden;">
                <div class="progress-bar bg-success" style="width: <?= round($summary['hadir'] / $total * 100) ?>%"></div>
                <div class="progress-bar bg-warning" style="width: <?= round($summary['sakit'] / $total * 100) ?>%"></div>
                <div class="progress-bar bg-info" style="width: <?= round($summary['izin'] / $total * 100) ?>%"></div>
                <div class="progress-bar bg-danger" style="width: <?= round($summary['alpha'] / $total * 100) ?>%"></div>
            </div>
            <small class="text-secondary mt-1 d-block" style="font-size: 11px;">
                Total <?= $total ?> hari tercatat — Persentase hadir: <strong class="text-success"><?= round($summary['hadir'] / $total * 100) ?>%</strong>
            </small>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tabel Riwayat Absensi -->
<div class="card mb-4">
    <div class="card-header border-bottom">
        <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-list-alt mr-2 text-info"></i>Riwayat Absensi Harian</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Tanggal</th>
                        <th>Hari</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-5">
                                <i class="fas fa-calendar-times mb-2" style="font-size: 32px;"></i>
                                <p class="mb-0">Belum ada data absensi tercatat.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php
                        $days = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
                        $statusBadge = [
                            'hadir' => ['bg' => 'success', 'icon' => 'fa-check-circle',    'label' => 'Hadir'],
                            'sakit' => ['bg' => 'warning', 'icon' => 'fa-thermometer-half', 'label' => 'Sakit'],
                            'izin'  => ['bg' => 'info',    'icon' => 'fa-envelope',         'label' => 'Izin'],
                            'alpha' => ['bg' => 'danger',  'icon' => 'fa-times-circle',     'label' => 'Alpha'],
                        ];
                        $i = 1;
                        foreach ($records as $r):
                            $st = strtolower($r['status']);
                            $badge = $statusBadge[$st] ?? ['bg' => 'secondary', 'icon' => 'fa-question', 'label' => ucfirst($r['status'])];
                            $dayEn = date('l', strtotime($r['date']));
                            $dayId = $days[$dayEn] ?? $dayEn;
                        ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td class="font-weight-bold"><?= date('d M Y', strtotime($r['date'])) ?></td>
                            <td><?= $dayId ?></td>
                            <td>
                                <span class="badge badge-<?= $badge['bg'] ?> px-2 py-1 rounded font-weight-bold" style="font-size: 12px;">
                                    <i class="fas <?= $badge['icon'] ?> mr-1"></i><?= $badge['label'] ?>
                                </span>
                            </td>
                            <td class="text-secondary" style="font-size: 13px;"><?= htmlspecialchars($r['note'] ?: '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
