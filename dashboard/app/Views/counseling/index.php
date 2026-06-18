<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Bimbingan Konseling (BK)</h1>
            <p class="text-secondary mb-0">Kelola rujukan, tindak lanjut pembinaan, dan perkembangan psikologis siswa.</p>
        </div>
        <?php if (!$isStudent): ?>
        <div>
            <button class="btn btn-primary" data-toggle="modal" data-target="#modal-add-counseling">
                <i class="fas fa-heart mr-2"></i> Tambah Catatan BK
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header border-bottom">
        <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-history mr-2 text-primary"></i> Histori Catatan Bimbingan Konseling</h3>
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
                        <th>Tipe Catatan</th>
                        <th>Detail Hasil Konseling / Solusi</th>
                        <th>Konselor / Wali BK</th>
                        <?php if (in_array(session()->get('role'), ['admin', 'guru_bk'])): ?>
                            <th>Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($counseling)): ?>
                        <tr>
                            <td colspan="<?= in_array(session()->get('role'), ['admin', 'guru_bk']) ? '8' : '7' ?>" class="text-center text-secondary py-5">
                                <i class="fas fa-book-open mb-2" style="font-size: 32px;"></i>
                                <p class="mb-0">Belum ada catatan BK terdaftar.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $i = 1; foreach ($counseling as $c): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td class="font-weight-bold text-dark"><?= htmlspecialchars($c['student_name']) ?></td>
                                <td><span class="badge badge-light border font-weight-bold text-secondary"><?= htmlspecialchars($c['student_class']) ?></span></td>
                                <td><?= date('d M Y', strtotime($c['date'])) ?></td>
                                <td><span class="badge badge-info px-2 py-1 rounded"><?= htmlspecialchars(ucfirst($c['type'])) ?></span></td>
                                <td class="text-secondary" style="font-size: 13px;"><?= htmlspecialchars($c['content']) ?></td>
                                <td class="font-weight-bold text-dark"><?= htmlspecialchars($c['counselor']) ?></td>
                                <?php if (in_array(session()->get('role'), ['admin', 'guru_bk'])): ?>
                                    <td>
                                        <form action="<?= base_url('/counseling/delete/' . $c['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan bimbingan ini?')">
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

<!-- Add Counseling Modal -->
<?php if (!$isStudent): ?>
<div class="modal fade" id="modal-add-counseling" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow rounded-lg">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark">Tambah Catatan Bimbingan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('/counseling/create') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Siswa Rujukan</label>
                        <select name="student_id" class="form-control" required>
                            <option value="">-- Pilih Siswa --</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($student['class']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Tanggal Pertemuan</label>
                        <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Tipe Bimbingan</label>
                        <select name="type" class="form-control" required>
                            <option value="catatan">Catatan Biasa</option>
                            <option value="pembinaan">Pembinaan Kedisiplinan</option>
                            <option value="konsultasi">Konsultasi Pribadi</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Deskripsi Hasil Konseling & Solusi</label>
                        <textarea name="content" class="form-control" rows="4" placeholder="Tuliskan isi bimbingan serta rekomendasi tindak lanjut..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Catatan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
