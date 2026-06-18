<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Profil Detail Siswa</h1>
            <p class="text-secondary mb-0">Rincian profil akademik, riwayat kehadiran KBM, dan rekam jejak pelanggaran.</p>
        </div>
        <div>
            <a href="<?= base_url('/students') ?>" class="btn btn-light"><i class="fas fa-arrow-left mr-2"></i> Kembali</a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Student Profile Card -->
    <div class="col-md-4">
        <div class="card mb-4 card-outline card-primary">
            <div class="card-body text-center py-5">
                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white mb-4 shadow" style="width: 100px; height: 100px; font-size: 40px;">
                    <i class="fas fa-user"></i>
                </div>
                <h4 class="font-weight-bold text-dark mb-1"><?= htmlspecialchars($student['name']) ?></h4>
                <span class="badge badge-light font-weight-bold text-secondary px-3 py-2 rounded-pill border mb-3"><?= htmlspecialchars($student['class']) ?></span>
                
                <hr class="w-100 my-4">

                <div class="text-left">
                    <div class="mb-3">
                        <small class="text-secondary font-weight-bold text-uppercase" style="font-size: 10px;">NISN</small>
                        <p class="font-weight-bold text-dark mb-0"><?= htmlspecialchars($student['nis'] ?: '-') ?></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-secondary font-weight-bold text-uppercase" style="font-size: 10px;">Jenis Kelamin</small>
                        <p class="font-weight-bold text-dark mb-0"><?= htmlspecialchars($student['gender'] == 'L' ? 'Laki-laki' : ($student['gender'] == 'P' ? 'Perempuan' : '-')) ?></p>
                    </div>
                    <div class="mb-0">
                        <small class="text-secondary font-weight-bold text-uppercase" style="font-size: 10px;">No. WhatsApp</small>
                        <p class="font-weight-bold text-dark mb-0"><?= htmlspecialchars($student['phone'] ?: '-') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Point Pelanggaran widget -->
        <div class="card mb-4 bg-gradient-danger text-white border-0 shadow">
            <div class="card-body p-4 text-center">
                <h5 class="font-weight-bold mb-2">Total Poin Pelanggaran</h5>
                <h1 class="display-3 font-weight-bold mb-2" style="font-size: 60px;"><?= $totalPoints ?></h1>
                <p class="mb-0 text-white-50" style="font-size: 13px;">Batas maksimal poin pelanggaran akademik adalah 100.</p>
            </div>
        </div>
    </div>

    <!-- Student logs & timelines -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active font-weight-bold rounded-lg mr-2" href="#attendance-tab" data-toggle="tab">Riwayat Kehadiran</a></li>
                    <li class="nav-item"><a class="nav-link font-weight-bold rounded-lg" href="#violations-tab" data-toggle="tab">Buku Pelanggaran</a></li>
                </ul>
            </div><!-- /.card-header -->
            <div class="card-body">
                <div class="tab-content">
                    <!-- Attendance Tab -->
                    <div class="tab-pane active" id="attendance-tab">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($attendance)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-secondary py-5">
                                                <i class="fas fa-calendar-times mb-2" style="font-size: 28px;"></i>
                                                <p class="mb-0">Belum ada riwayat absensi KBM.</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($attendance as $att): ?>
                                            <tr>
                                                <td class="font-weight-bold text-dark"><?= date('d F Y', strtotime($att['date'])) ?></td>
                                                <td>
                                                    <?php if ($att['status'] == 'hadir'): ?>
                                                        <span class="badge badge-success px-2 py-1 rounded">Hadir</span>
                                                    <?php elseif ($att['status'] == 'sakit'): ?>
                                                        <span class="badge badge-warning px-2 py-1 rounded">Sakit</span>
                                                    <?php elseif ($att['status'] == 'izin'): ?>
                                                        <span class="badge badge-info px-2 py-1 rounded">Izin</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger px-2 py-1 rounded">Alpha</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($att['note'] ?: '-') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Violations Tab -->
                    <div class="tab-pane" id="violations-tab">
                        <div class="timeline timeline-inverse mb-0">
                            <?php if (empty($violations)): ?>
                                <div class="text-center text-secondary py-5">
                                    <i class="fas fa-shield-alt mb-2 text-success" style="font-size: 32px;"></i>
                                    <p class="mb-0 text-success font-weight-bold">Siswa berprestasi. Belum pernah melakukan pelanggaran.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($violations as $v): ?>
                                    <!-- timeline time label -->
                                    <div class="time-label">
                                        <span class="bg-danger px-3 py-1 font-weight-bold text-white rounded">
                                            <?= date('d M Y', strtotime($v['date'])) ?>
                                        </span>
                                    </div>
                                    <!-- /.timeline-label -->
                                    <!-- timeline item -->
                                    <div>
                                        <i class="fas fa-exclamation-triangle bg-danger text-white"></i>
                                        <div class="timeline-item card shadow-sm p-4 border rounded-lg bg-light" style="margin-left: 60px;">
                                            <span class="time text-secondary" style="font-size: 11px;"><i class="far fa-clock"></i> Poin: <strong class="text-danger"><?= $v['points'] ?></strong></span>
                                            <h4 class="timeline-header font-weight-bold text-dark border-0 pb-1 mb-2" style="font-size: 16px;"><?= htmlspecialchars($v['category']) ?></h4>
                                            <div class="timeline-body text-secondary" style="font-size: 13px;">
                                                <?= htmlspecialchars($v['description']) ?>
                                            </div>
                                            <?php if (!empty($v['follow_up'])): ?>
                                                <div class="timeline-footer mt-3 pt-2 border-top text-secondary" style="font-size: 12px;">
                                                    <strong>Tindak Lanjut:</strong> <?= htmlspecialchars($v['follow_up']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <!-- END timeline item -->
                                <?php endforeach; ?>
                                <div>
                                    <i class="far fa-clock bg-gray"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
