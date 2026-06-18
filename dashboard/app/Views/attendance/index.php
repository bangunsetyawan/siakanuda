<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Absensi Kelas KBM</h1>
            <p class="text-secondary mb-0">Kelola dan pantau kehadiran harian siswa per kelas.</p>
        </div>
        <div>
            <form action="" method="get" class="form-inline">
                <input type="date" name="date" class="form-control mr-2" value="<?= htmlspecialchars($date) ?>" onchange="this.form.submit()">
                <noscript><button type="submit" class="btn btn-outline-primary">Pilih Tanggal</button></noscript>
            </form>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header border-bottom">
        <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-calendar-check mr-2 text-success"></i> Status Pelaporan Kelas - Tanggal: <?= date('d M Y', strtotime($date)) ?></h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body p-4">
        <?php
        // Group attendance by class name for fast lookup
        $attendanceMap = [];
        foreach ($attendance as $att) {
            $attendanceMap[$att['class_name']] = $att;
        }

        // Build teacher name lookup
        $teacherNameMap = [];
        foreach ($teachers as $t) {
            $teacherNameMap[$t['phone']] = $t['name'];
        }
        ?>

        <div class="row">
            <?php foreach ($activeClasses as $className): ?>
                <?php
                $reported = isset($attendanceMap[$className]);
                $attData = $reported ? $attendanceMap[$className] : null;
                $details = $reported ? json_decode($attData['attendance_data'], true) : [];
                
                $hadir = 0; $sakit = 0; $izin = 0; $alpha = 0;
                foreach ($details as $name => $status) {
                    if ($status == 'hadir') $hadir++;
                    elseif ($status == 'sakit') $sakit++;
                    elseif ($status == 'izin') $izin++;
                    elseif ($status == 'alpha') $alpha++;
                }

                $studentCount = $classCounts[$className] ?? 0;
                $waliPhone = $waliKelasMap[$className] ?? null;
                $waliName = $waliPhone ? ($teacherNameMap[$waliPhone] ?? $waliPhone) : null;
                ?>
                <div class="col-md-4 col-sm-6">
                    <div class="card shadow-sm mb-4 border rounded-lg <?= $reported ? 'border-left-success' : 'border-left-warning' ?>" style="border-left: 5px solid <?= $reported ? '#28a745' : '#ffc107' ?> !important;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h5 class="font-weight-bold text-dark mb-0"><?= htmlspecialchars($className) ?></h5>
                                    <span class="text-secondary" style="font-size: 12px;"><i class="fas fa-users mr-1"></i><?= $studentCount ?> siswa</span>
                                </div>
                                <?php if ($reported): ?>
                                    <span class="badge badge-success px-2 py-1 rounded">Sudah Diabsen</span>
                                <?php else: ?>
                                    <span class="badge badge-warning px-2 py-1 rounded text-dark">Belum Diabsen</span>
                                <?php endif; ?>
                            </div>

                            <!-- Wali Kelas -->
                            <div class="mb-3" style="font-size: 12px;">
                                <?php if ($waliName): ?>
                                    <span class="text-primary"><i class="fas fa-user-tie mr-1"></i> Wali: <strong><?= htmlspecialchars($waliName) ?></strong></span>
                                <?php else: ?>
                                    <span class="text-muted"><i class="fas fa-user-tie mr-1"></i> Wali: <em>Belum ditentukan</em></span>
                                <?php endif; ?>
                                <?php if ($role === 'admin'): ?>
                                    <button class="btn btn-xs btn-outline-secondary ml-1 py-0 px-1" data-toggle="modal" data-target="#modal-wali-<?= md5($className) ?>" title="Atur Wali Kelas" style="font-size: 10px;">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                <?php endif; ?>
                            </div>

                            <?php if ($reported): ?>
                                <div class="row mb-3" style="font-size: 13px;">
                                    <div class="col-3 text-center text-success"><strong style="font-size: 18px;"><?= $hadir ?></strong><br><small class="text-secondary">Hadir</small></div>
                                    <div class="col-3 text-center text-warning"><strong style="font-size: 18px;"><?= $sakit ?></strong><br><small class="text-secondary">Sakit</small></div>
                                    <div class="col-3 text-center text-info"><strong style="font-size: 18px;"><?= $izin ?></strong><br><small class="text-secondary">Izin</small></div>
                                    <div class="col-3 text-center text-danger"><strong style="font-size: 18px;"><?= $alpha ?></strong><br><small class="text-secondary">Alpha</small></div>
                                </div>
                                <p class="text-secondary mb-0" style="font-size: 11px;"><i class="fas fa-user-edit mr-1"></i> Pengisi: <?= htmlspecialchars($teacherNameMap[$attData['teacher_phone']] ?? $attData['teacher_phone'] ?: '-') ?></p>
                            <?php else: ?>
                                <div class="text-center py-3 text-secondary" style="font-size: 13px;">
                                    <p class="mb-0">Data absensi kelas hari ini belum dilaporkan oleh guru mapel.</p>
                                </div>
                            <?php endif; ?>

                            <hr class="my-3">

                            <div class="d-flex gap-2">
                                <a href="<?= base_url('/attendance/class/' . urlencode($className) . '?date=' . $date) ?>" class="btn btn-sm <?= $reported ? 'btn-outline-success' : 'btn-primary' ?> mr-1 flex-grow-1">
                                    <i class="fas <?= $reported ? 'fa-edit' : 'fa-clipboard-list' ?> mr-1"></i> <?= $reported ? 'Edit Absensi' : 'Isi Absensi' ?>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-success mr-1" data-toggle="modal" data-target="#modal-rekap-<?= md5($className) ?>" title="Cetak Rekap Bulanan">
                                    <i class="fas fa-file-pdf"></i>
                                </button>
                                <?php if ($role === 'admin' && $reported): ?>
                                    <form action="<?= base_url('/attendance/delete/' . $attData['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Hapus riwayat absensi kelas <?= htmlspecialchars($className) ?> tanggal <?= $date ?>?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Riwayat">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Wali Kelas Modal (Admin) -->
                <?php if ($role === 'admin'): ?>
                <div class="modal fade" id="modal-wali-<?= md5($className) ?>" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                        <div class="modal-content border-0 shadow rounded-lg">
                            <div class="modal-header border-bottom py-2">
                                <h6 class="modal-title font-weight-bold text-dark">Wali Kelas: <?= htmlspecialchars($className) ?></h6>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <form action="<?= base_url('/attendance/set-wali-kelas') ?>" method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="class_name" value="<?= htmlspecialchars($className) ?>">
                                <div class="modal-body py-3">
                                    <select name="teacher_phone" class="form-control form-control-sm">
                                        <option value="">-- Tidak Ada --</option>
                                        <?php foreach ($teachers as $t): ?>
                                            <option value="<?= $t['phone'] ?>" <?= ($waliPhone === $t['phone']) ? 'selected' : '' ?>><?= htmlspecialchars($t['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer border-top py-2">
                                    <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Rekap Absen Modal -->
                <div class="modal fade" id="modal-rekap-<?= md5($className) ?>" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                        <div class="modal-content border-0 shadow rounded-lg">
                            <div class="modal-header border-bottom py-2">
                                <h6 class="modal-title font-weight-bold text-dark">Rekap Bulanan: <?= htmlspecialchars($className) ?></h6>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <form action="<?= base_url('/attendance/print-html/' . urlencode($className)) ?>" method="get" target="_blank">
                                <div class="modal-body py-3">
                                    <div class="form-group mb-2">
                                        <label class="text-secondary" style="font-size: 12px;">Pilih Bulan</label>
                                        <select name="month" class="form-control form-control-sm">
                                            <?php
                                            $months = [
                                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                            ];
                                            $currentMonth = (int)date('m');
                                            foreach ($months as $mNum => $mName):
                                            ?>
                                                <option value="<?= $mNum ?>" <?= ($currentMonth === $mNum) ? 'selected' : '' ?>><?= $mName ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="text-secondary" style="font-size: 12px;">Pilih Tahun</label>
                                        <select name="year" class="form-control form-control-sm">
                                            <?php
                                            $currentYear = (int)date('Y');
                                            for ($y = $currentYear - 2; $y <= $currentYear + 2; $y++):
                                            ?>
                                                <option value="<?= $y ?>" <?= ($currentYear === $y) ? 'selected' : '' ?>><?= $y ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer border-top py-2 justify-content-between">
                                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-file-pdf mr-1"></i> Cetak PDF</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Tracker Rekapitulasi 30 Hari -->
<div class="card mb-4">
    <div class="card-header border-bottom bg-white">
        <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-chart-line mr-2 text-primary"></i> Tracker Laporan Kelas (30 Hari Terakhir)</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="bg-light" style="position: sticky; top: 0; z-index: 1;">
                    <tr>
                        <th width="15%" class="pl-4 border-bottom-0">Tanggal</th>
                        <th width="15%" class="text-center border-bottom-0">Progres</th>
                        <th width="70%" class="border-bottom-0">Kelas Belum Diabsen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($recapDays)): ?>
                        <?php foreach ($recapDays as $recap): ?>
                            <?php 
                                $isComplete = $recap['reported_count'] == $recap['total_classes'] && $recap['total_classes'] > 0;
                                $isEmpty = $recap['reported_count'] == 0;
                            ?>
                            <tr>
                                <td class="pl-4 font-weight-bold align-middle">
                                    <a href="<?= base_url('/attendance?date=' . $recap['date']) ?>" class="text-dark text-decoration-none">
                                        <?= date('d M Y', strtotime($recap['date'])) ?>
                                    </a>
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge <?= $isComplete ? 'badge-success' : ($isEmpty ? 'badge-danger' : 'badge-warning') ?> px-2 py-1" style="font-size: 12px;">
                                        <?= $recap['reported_count'] ?> / <?= $recap['total_classes'] ?>
                                    </span>
                                </td>
                                <td class="align-middle py-3">
                                    <?php if ($recap['total_classes'] == 0): ?>
                                        <span class="text-muted font-italic" style="font-size: 13px;">Belum ada kelas aktif.</span>
                                    <?php elseif (empty($recap['missing_classes'])): ?>
                                        <span class="text-success font-weight-bold" style="font-size: 13px;"><i class="fas fa-check-circle mr-1"></i> Selesai: Semua kelas sudah dilaporkan.</span>
                                    <?php else: ?>
                                        <div class="d-flex flex-wrap gap-1">
                                        <?php foreach ($recap['missing_classes'] as $mc): ?>
                                            <span class="badge badge-light border border-secondary text-dark mr-1 mb-1 px-2 py-1" style="font-weight: 500; font-size: 12px;"><?= htmlspecialchars($mc) ?></span>
                                        <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
