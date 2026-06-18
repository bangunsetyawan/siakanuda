<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid">
        <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Kotak Suara & Aspirasi Siswa</h1>
        <p class="text-secondary mb-0">
            <?php if (!empty($isSiswa)): ?>
                Kirimkan kritik, saran, atau aspirasi kamu di bawah ini.
            <?php else: ?>
                Moderasi laporan kritik, saran, masukan, dan aspirasi yang dikirimkan oleh siswa.
            <?php endif; ?>
        </p>
    </div>
</div>

<?php if (!empty($isSiswa)): ?>
<!-- Form Kirim Aspirasi — khusus Siswa -->
<div class="card mb-4 border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
    <div class="card-body p-0">
        <div class="d-flex align-items-stretch">
            <div style="width: 6px; background: linear-gradient(180deg, #0ea5e9, #6366f1);"></div>
            <div class="p-4 flex-grow-1">
                <h6 class="font-weight-bold text-dark mb-3">
                    <i class="fas fa-pen-nib text-info mr-2"></i>Tulis Aspirasi Kamu
                </h6>
                <form action="<?= base_url('/feedbacks/create') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="form-group mb-3">
                        <div class="alert alert-warning border-0 rounded-lg p-3 mb-3" style="font-size: 13px; background: rgba(245, 158, 11, 0.08); color: #b45309;">
                            <i class="fas fa-exclamation-triangle mr-2"></i><strong>Aturan Kotak Suara:</strong> Dilarang menggunakan kata kotor, kasar, mengejek, sara, atau tindakan tidak terpuji lainnya. Meskipun dipublikasikan secara <strong>Anonim</strong> untuk umum, <strong>Admin Sekolah tetap dapat memantau identitas pengirim asli</strong> jika diperlukan untuk ketertiban.
                        </div>
                        <textarea name="message" class="form-control" rows="3"
                                  placeholder="Tulis kritik, saran, atau masukan untuk sekolah..."
                                  style="border-radius: 12px; border: 1px solid #e2e8f0; font-size: 14px; resize: vertical;"
                                  required minlength="5" maxlength="1000"></textarea>
                        <small class="text-secondary mt-2 d-block" style="font-size: 11px;">
                            <i class="fas fa-info-circle mr-1"></i>Aspirasi Anda akan diterbitkan secara otomatis untuk seluruh sekolah tanpa menampilkan nama asli Anda.
                        </small>
                    </div>
                    <button type="submit" class="btn btn-primary font-weight-bold px-4"
                            style="border-radius: 12px; background: #0284c7; border-color: #0284c7;">
                        <i class="fas fa-paper-plane mr-2"></i>Kirim Aspirasi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header border-bottom">
        <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-comment-alt mr-2 text-info"></i> Aspirasi Siswa Masuk</h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width: 80px;">#</th>
                        <th>Pengirim</th>
                        <th>Kelas</th>
                        <th>Tanggal</th>
                        <th>Isi Aspirasi</th>
                        <th>Status Moderasi</th>
                        <?php if ($isAdmin): ?>
                            <th>Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($feedbacks)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-5">
                                <i class="fas fa-comments-slash mb-2" style="font-size: 32px;"></i>
                                <p class="mb-0">Belum ada aspirasi terdaftar.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $i = 1; foreach ($feedbacks as $fb): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td class="font-weight-bold text-dark"><?= session()->get('role') === 'admin' ? htmlspecialchars($fb['sender_name']) : 'Anonim' ?></td>
                                <td><span class="badge badge-light border font-weight-bold text-secondary"><?= htmlspecialchars($fb['class_name']) ?></span></td>
                                <td><?= date('d M Y, H:i', strtotime($fb['date'])) ?></td>
                                <td class="text-dark">"<?= htmlspecialchars($fb['message']) ?>"</td>
                                <td>
                                    <?php if ($fb['is_public']): ?>
                                        <span class="badge badge-success px-2 py-1 rounded">Publik (Tampil di Web)</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary px-2 py-1 rounded">Disembunyikan</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($isAdmin): ?>
                                    <td>
                                        <div class="btn-group">
                                            <form action="<?= base_url('/feedbacks/toggle/' . $fb['id']) ?>" method="post" class="d-inline mr-1">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-info rounded">
                                                    <i class="fas <?= $fb['is_public'] ? 'fa-eye-slash' : 'fa-eye' ?> mr-1"></i> <?= $fb['is_public'] ? 'Sembunyikan' : 'Tampilkan' ?>
                                                </button>
                                            </form>
                                            <form action="<?= base_url('/feedbacks/delete/' . $fb['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aspirasi ini?')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
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
<?= $this->endSection() ?>
