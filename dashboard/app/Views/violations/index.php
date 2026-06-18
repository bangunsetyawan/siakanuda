<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <?php if (!empty($isStudent)): ?>
                <h1 class="m-0 font-weight-bold text-dark" style="font-size: 26px;">Poin Pelanggaran Saya</h1>
                <p class="text-secondary mb-0">Rekap catatan poin kedisiplinan kamu di sekolah.</p>
            <?php else: ?>
                <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Buku Kasus Pelanggaran Siswa</h1>
                <p class="text-secondary mb-0">Monitor rekam jejak poin kedisiplinan dan pencatatan pelanggaran tata tertib.</p>
            <?php endif; ?>
        </div>
        <div>
            <?php if (in_array(session()->get('role'), ['admin', 'guru_bk'])): ?>
                <button class="btn btn-danger" data-toggle="modal" data-target="#modal-add-violation">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Catat Pelanggaran
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($isStudent)): ?>
<!-- Kartu Identitas + Total Poin Siswa -->
<?php
    $studentName = session()->get('name');
    $studentClass = session()->get('class') ?: '-';
    $studentNis = '';
    // Ambil NIS dari session jika user_type=student
    if (session()->get('user_type') === 'student') {
        $studentPhone = session()->get('phone');
        // phone bisa berisi NIS sebagai fallback
        $studentNis = $studentPhone;
    }
?>
<div class="card mb-4 border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
    <div class="card-body p-0">
        <div class="d-flex align-items-stretch">
            <div style="width: 6px; background: linear-gradient(180deg, #ef4444, #f59e0b);"></div>
            <div class="p-3 p-md-4 flex-grow-1">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center justify-content-center rounded-circle mr-3"
                             style="width:50px; height:50px; min-width:50px; background: linear-gradient(135deg, #dc2626, #b91c1c);">
                            <i class="fas fa-user-graduate text-white" style="font-size: 20px;"></i>
                        </div>
                        <div>
                            <h5 class="font-weight-bold text-dark mb-0" style="font-size: 17px;"><?= htmlspecialchars($studentName) ?></h5>
                            <div class="d-flex flex-wrap align-items-center" style="gap: 8px; margin-top: 4px;">
                                <span class="badge px-2 py-1" style="font-size: 11px; background: rgba(220,53,69,0.1); color: #dc2626; border-radius: 6px;">
                                    <i class="fas fa-id-card mr-1"></i>NIS: <?= htmlspecialchars($studentNis) ?>
                                </span>
                                <span class="badge px-2 py-1" style="font-size: 11px; background: rgba(22,163,74,0.1); color: #16a34a; border-radius: 6px;">
                                    <i class="fas fa-chalkboard mr-1"></i><?= htmlspecialchars($studentClass) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-2 mt-md-0">
                        <div class="p-3 rounded-lg" style="background: rgba(220,53,69,0.08); min-width: 100px;">
                            <h3 class="font-weight-bold text-danger mb-0"><?= $totalPoin ?></h3>
                            <small class="text-secondary font-weight-bold" style="font-size: 11px;">Total Poin</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header border-bottom">
        <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-exclamation-circle mr-2 text-danger"></i> Daftar Kasus Pelanggaran Terbaru</h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width: 80px;">#</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Tanggal</th>
                        <th>Kategori Kasus</th>
                        <th>Detail Pelanggaran</th>
                        <th style="width: 100px;">Poin</th>
                        <th>Tindak Lanjut</th>
                        <?php if (in_array(session()->get('role'), ['admin', 'guru_bk'])): ?>
                            <th>Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($violations)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-secondary py-5">
                                <i class="fas fa-shield-alt mb-2 text-success" style="font-size: 32px;"></i>
                                <p class="mb-0 text-success font-weight-bold">Aman & Tertib. Tidak ada catatan pelanggaran baru.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $i = 1; foreach ($violations as $v): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td class="font-weight-bold text-dark"><?= htmlspecialchars($v['student_name']) ?></td>
                                <td><span class="badge badge-light border font-weight-bold text-secondary"><?= htmlspecialchars($v['student_class']) ?></span></td>
                                <td><?= date('d M Y', strtotime($v['date'])) ?></td>
                                <td><span class="badge badge-danger px-2 py-1 rounded"><?= htmlspecialchars($v['category']) ?></span></td>
                                <td class="text-secondary" style="font-size: 13px;"><?= htmlspecialchars($v['description']) ?></td>
                                <td class="font-weight-bold text-danger" style="font-size: 15px;">+<?= $v['points'] ?></td>
                                <td class="text-secondary" style="font-size: 13px;"><?= htmlspecialchars($v['follow_up'] ?: '-') ?></td>
                                <?php if (in_array(session()->get('role'), ['admin', 'guru_bk'])): ?>
                                    <td>
                                        <form action="<?= base_url('/violations/delete/' . $v['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan pelanggaran ini?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Violation Modal -->
<?php if (in_array(session()->get('role'), ['admin', 'guru_bk'])): ?>
<div class="modal fade" id="modal-add-violation" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow rounded-lg">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark">Catat Pelanggaran Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('/violations/create') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Pilih Siswa</label>
                        <select name="student_id" class="form-control" required>
                            <option value="">-- Pilih Siswa --</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($student['class']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Tanggal Pelanggaran</label>
                        <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Kategori Kasus</label>
                        <input type="text" name="category" class="form-control" placeholder="Contoh: Terlambat, Atribut Tidak Lengkap, dll" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Rincian Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Masukkan kronologi singkat..." required></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Skor Poin Penalti</label>
                        <input type="number" name="points" class="form-control" value="5" min="1" max="100" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Tindak Lanjut / Tindakan BK</label>
                        <input type="text" name="follow_up" class="form-control" placeholder="Contoh: Pembinaan, Peringatan 1, dll">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Simpan Pelanggaran</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
