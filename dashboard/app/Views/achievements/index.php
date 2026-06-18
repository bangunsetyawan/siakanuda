<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Prestasi Siswa</h1>
            <p class="text-secondary mb-0">Catatan riwayat peringkat kelas, kompetensi, prestasi akademik &amp; non-akademik siswa.</p>
        </div>
        <div>
            <?php if (!$isStudent && in_array(session()->get('role'), ['admin', 'guru_bk', 'guru', 'guru_mapel'])): ?>
                <button class="btn btn-success" data-toggle="modal" data-target="#modal-add-achievement">
                    <i class="fas fa-trophy mr-2"></i> Tambah Prestasi
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header border-bottom">
        <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-history mr-2 text-success"></i> Histori Catatan Prestasi</h3>
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
                        <th>Kategori</th>
                        <th>Nama Prestasi / Penghargaan</th>
                        <th>Keterangan / Deskripsi</th>
                        <?php if (!$isStudent && in_array(session()->get('role'), ['admin', 'guru_bk', 'guru', 'guru_mapel'])): ?>
                            <th>Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($achievements)): ?>
                        <tr>
                            <td colspan="<?= (!$isStudent && in_array(session()->get('role'), ['admin', 'guru_bk', 'guru', 'guru_mapel'])) ? '8' : '7' ?>" class="text-center text-secondary py-5">
                                <i class="fas fa-award mb-2" style="font-size: 32px; color: #10b981;"></i>
                                <p class="mb-0">Belum ada catatan prestasi terdaftar.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $i = 1; foreach ($achievements as $a): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td class="font-weight-bold text-dark"><?= htmlspecialchars($a['student_name']) ?></td>
                                <td><span class="badge badge-light border font-weight-bold text-secondary"><?= htmlspecialchars($a['student_class']) ?></span></td>
                                <td><?= date('d M Y', strtotime($a['date'])) ?></td>
                                <td>
                                    <?php if ($a['category'] === 'Akademik'): ?>
                                        <span class="badge badge-primary px-2 py-1 rounded">Akademik</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning text-dark px-2 py-1 rounded">Non-Akademik</span>
                                    <?php endif; ?>
                                </td>
                                <td class="font-weight-bold text-dark"><?= htmlspecialchars($a['title']) ?></td>
                                <td class="text-secondary" style="font-size: 13px;"><?= htmlspecialchars($a['description'] ?: '-') ?></td>
                                <?php if (!$isStudent && in_array(session()->get('role'), ['admin', 'guru_bk', 'guru', 'guru_mapel'])): ?>
                                    <td>
                                        <form action="<?= base_url('/prestasi/delete/' . $a['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan prestasi ini?')">
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

<!-- Add Achievement Modal -->
<?php if (!$isStudent && in_array(session()->get('role'), ['admin', 'guru_bk', 'guru', 'guru_mapel'])): ?>
<div class="modal fade" id="modal-add-achievement" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow rounded-lg">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark">Tambah Catatan Prestasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('/prestasi/create') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Siswa Berprestasi</label>
                        <select name="student_id" class="form-control" required>
                            <option value="">-- Pilih Siswa --</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($student['class']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Tanggal Peraihan</label>
                        <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Kategori Prestasi</label>
                        <select name="category" class="form-control" required>
                            <option value="Akademik">Akademik (Juara Kelas, OSN, LKS, dsb)</option>
                            <option value="Non-Akademik">Non-Akademik (Futsal, Pramuka, Seni, dsb)</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Nama Prestasi / Juara</label>
                        <input type="text" name="title" class="form-control" placeholder="Contoh: Juara 1 LKS TKJ Nasional" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Detail / Deskripsi Keterangan</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Tuliskan detail peraihan prestasi siswa..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Prestasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
