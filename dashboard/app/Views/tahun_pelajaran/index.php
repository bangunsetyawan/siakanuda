<?= $this->extend('layouts/template') ?>
<?= $this->section('content') ?>

<h4 class="mb-4"><i class="fas fa-book-open mr-2 text-primary"></i>Manajemen Tahun Pelajaran</h4>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-lg border-0" role="alert">
        <ul class="mb-0">
        <?php foreach (session()->getFlashdata('errors') as $err): ?>
            <li><?= esc($err) ?></li>
        <?php endforeach; ?>
        </ul>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
<?php endif; ?>

<!-- Form Tambah -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="fas fa-plus-circle mr-1"></i> Tambah Tahun Pelajaran</h5>
    </div>
    <div class="card-body">
        <form action="<?= base_url('/tahun-pelajaran/create') ?>" method="post">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-4 mb-2">
                    <label class="small text-muted mb-1">Nama TP</label>
                    <input type="text" name="nama" class="form-control" placeholder="2025/2026" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="small text-muted mb-1">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control">
                </div>
                <div class="col-md-4 mb-2">
                    <label class="small text-muted mb-1">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control">
                </div>
                <div class="col-md-1 mb-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-save"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Daftar Tahun Pelajaran -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="fas fa-list mr-1"></i> Daftar Tahun Pelajaran</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th style="width:3rem;" class="text-center">#</th>
                        <th>Nama TP</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width:14rem;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tahunPelajaran)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data Tahun Pelajaran.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tahunPelajaran as $i => $tp): ?>
                            <tr style="<?= $tp['is_active'] ? 'background: #f0fdf4;' : '' ?>">
                                <td class="text-center"><?= $i + 1 ?></td>
                                <td>
                                    <form action="<?= base_url('/tahun-pelajaran/update/' . $tp['id']) ?>" method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="text" name="nama" value="<?= esc($tp['nama']) ?>" class="form-control form-control-sm d-inline-block" style="width:8rem;">
                                </td>
                                <td>
                                        <input type="date" name="tanggal_mulai" value="<?= esc($tp['tanggal_mulai'] ?? '') ?>" class="form-control form-control-sm">
                                </td>
                                <td>
                                        <input type="date" name="tanggal_selesai" value="<?= esc($tp['tanggal_selesai'] ?? '') ?>" class="form-control form-control-sm">
                                </td>
                                <td class="text-center">
                                    <?php if ($tp['is_active']): ?>
                                        <span class="badge badge-success" style="font-size: 0.75rem; padding: 0.35rem 0.75rem; border-radius: 1rem;">
                                            <i class="fas fa-check-circle mr-1"></i>AKTIF
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary" style="font-size: 0.7rem; padding: 0.25rem 0.5rem; border-radius: 1rem;">Non-Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                        <button type="submit" class="btn btn-outline-primary btn-sm" title="Simpan perubahan"><i class="fas fa-save"></i></button>
                                    </form>

                                    <?php if (!$tp['is_active']): ?>
                                        <form action="<?= base_url('/tahun-pelajaran/set-active/' . $tp['id']) ?>" method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-success btn-sm" title="Jadikan Aktif" onclick="return confirm('Jadikan TP <?= esc($tp['nama']) ?> sebagai tahun pelajaran aktif?')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="<?= base_url('/tahun-pelajaran/delete/' . $tp['id']) ?>" method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus" onclick="return confirm('Hapus TP <?= esc($tp['nama']) ?>?')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
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
