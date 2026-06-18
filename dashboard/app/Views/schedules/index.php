<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Manajemen Jadwal Pelajaran</h1>
            <p class="text-secondary mb-0">Kelola dan atur jadwal pembelajaran mingguan untuk setiap kelas dan jurusan.</p>
        </div>
        <div>
            <?php if (in_array(session()->get('role'), ['admin', 'kepsek'])): ?>
                <button class="btn btn-primary" data-toggle="modal" data-target="#modal-add-schedule">
                    <i class="fas fa-plus-circle mr-2"></i> Tambah Jadwal
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-calendar-alt mr-2 text-primary"></i> Jadwal Pelajaran Mingguan</h3>
        <div class="card-tools ml-auto">
            <form action="" method="get" class="form-inline">
                <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Cari Kelas/Mapel/Hari" value="<?= htmlspecialchars($search ?? '') ?>">
                <button type="submit" class="btn btn-sm btn-outline-primary">Cari</button>
            </form>
        </div>
    </div>
    <!-- /.card-header -->
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th class="border-0">#</th>
                        <th class="border-0">Hari</th>
                        <th class="border-0">Kelas</th>
                        <th class="border-0">Mata Pelajaran</th>
                        <th class="border-0">Guru Pengajar</th>
                        <th class="border-0">Jam Belajar</th>
                        <?php if (in_array(session()->get('role'), ['admin', 'kepsek'])): ?>
                            <th class="border-0">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($schedules)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-5">
                                <i class="fas fa-calendar-times mb-2" style="font-size: 32px;"></i>
                                <p class="mb-0">Belum ada jadwal pelajaran yang dicatatkan.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $i = 1; foreach ($schedules as $s): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><span class="badge badge-light border text-dark font-weight-bold px-2 py-1"><?= htmlspecialchars($s['day']) ?></span></td>
                                <td class="font-weight-bold text-dark"><?= htmlspecialchars($s['class_name']) ?></td>
                                <td class="font-weight-bold text-primary"><?= htmlspecialchars($s['subject']) ?></td>
                                <td>
                                    <?php
                                        $teacherName = 'Tidak Diketahui';
                                        foreach ($teachers as $t) {
                                            if ($t['phone'] === $s['teacher_phone']) {
                                                $teacherName = $t['name'];
                                                break;
                                            }
                                        }
                                        echo htmlspecialchars($teacherName);
                                    ?>
                                </td>
                                <td><code class="text-secondary font-weight-bold"><?= htmlspecialchars($s['start_time']) ?> - <?= htmlspecialchars($s['end_time']) ?></code></td>
                                <?php if (in_array(session()->get('role'), ['admin', 'kepsek'])): ?>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-primary mr-2 rounded" 
                                                    data-toggle="modal" 
                                                    data-target="#modal-edit-schedule-<?= $s['id'] ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <form action="<?= base_url('/schedules/delete/' . $s['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal pelajaran ini?')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>

                            <!-- Edit Schedule Modal -->
                            <div class="modal fade" id="modal-edit-schedule-<?= $s['id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content border-0 shadow rounded-lg">
                                        <div class="modal-header border-bottom">
                                            <h5 class="modal-title font-weight-bold text-dark">Edit Jadwal Pelajaran</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form action="<?= base_url('/schedules/update/' . $s['id']) ?>" method="post">
                                            <?= csrf_field() ?>
                                            <div class="modal-body">
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-secondary">Hari</label>
                                                    <select name="day" class="form-control" required>
                                                        <option value="Senin" <?= $s['day'] === 'Senin' ? 'selected' : '' ?>>Senin</option>
                                                        <option value="Selasa" <?= $s['day'] === 'Selasa' ? 'selected' : '' ?>>Selasa</option>
                                                        <option value="Rabu" <?= $s['day'] === 'Rabu' ? 'selected' : '' ?>>Rabu</option>
                                                        <option value="Kamis" <?= $s['day'] === 'Kamis' ? 'selected' : '' ?>>Kamis</option>
                                                        <option value="Jumat" <?= $s['day'] === 'Jumat' ? 'selected' : '' ?>>Jumat</option>
                                                        <option value="Sabtu" <?= $s['day'] === 'Sabtu' ? 'selected' : '' ?>>Sabtu</option>
                                                    </select>
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-secondary">Kelas</label>
                                                    <input type="text" name="class_name" class="form-control" value="<?= htmlspecialchars($s['class_name']) ?>" required>
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-secondary">Mata Pelajaran</label>
                                                    <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($s['subject']) ?>" required>
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-secondary">Guru Pengajar</label>
                                                    <select name="teacher_phone" class="form-control" required>
                                                        <option value="">-- Pilih Guru --</option>
                                                        <?php foreach ($teachers as $t): ?>
                                                            <option value="<?= $t['phone'] ?>" <?= $s['teacher_phone'] === $t['phone'] ? 'selected' : '' ?>><?= htmlspecialchars($t['name']) ?> (<?= htmlspecialchars($t['phone']) ?>)</option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="row">
                                                    <div class="col-6 mb-3">
                                                        <label class="font-weight-bold text-secondary">Jam Mulai</label>
                                                        <input type="time" name="start_time" class="form-control" value="<?= htmlspecialchars($s['start_time']) ?>" required>
                                                    </div>
                                                    <div class="col-6 mb-3">
                                                        <label class="font-weight-bold text-secondary">Jam Selesai</label>
                                                        <input type="time" name="end_time" class="form-control" value="<?= htmlspecialchars($s['end_time']) ?>" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top">
                                                <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Schedule Modal -->
<div class="modal fade" id="modal-add-schedule" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow rounded-lg">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark">Tambah Jadwal Pelajaran Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('/schedules/create') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Hari</label>
                        <select name="day" class="form-control" required>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Kelas</label>
                        <input type="text" name="class_name" class="form-control" placeholder="Contoh: XII TKJ / XI AKL" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Mata Pelajaran</label>
                        <input type="text" name="subject" class="form-control" placeholder="Masukkan Nama Mata Pelajaran" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Guru Pengajar</label>
                        <select name="teacher_phone" class="form-control" required>
                            <option value="">-- Pilih Guru --</option>
                            <?php foreach ($teachers as $t): ?>
                                <option value="<?= $t['phone'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="font-weight-bold text-secondary">Jam Mulai</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="font-weight-bold text-secondary">Jam Selesai</label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
