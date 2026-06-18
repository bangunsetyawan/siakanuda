<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Absensi Kelas: <?= htmlspecialchars($class_name) ?></h1>
            <p class="text-secondary mb-0">Isi dan simpan kehadiran siswa untuk Tanggal: <strong><?= date('d F Y', strtotime($date)) ?></strong></p>
        </div>
        <div>
            <a href="<?= base_url('/attendance?date=' . $date) ?>" class="btn btn-light"><i class="fas fa-arrow-left mr-2"></i> Kembali</a>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-clipboard-list mr-2 text-primary"></i> Lembar Absensi</h3>
        <?php if (session()->get('role') !== 'kepsek'): ?>
        <div class="card-tools ml-auto">
            <button type="button" class="btn btn-sm btn-outline-success font-weight-bold" onclick="markAllHadir()">
                <i class="fas fa-check-double mr-1"></i> Hadir Semua
            </button>
        </div>
        <?php endif; ?>
    </div>
    <!-- /.card-header -->
    <form action="<?= base_url('/attendance/save-class') ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="class_name" value="<?= htmlspecialchars($class_name) ?>">
        <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px;">No Urut</th>
                            <th>Nama Lengkap</th>
                            <th style="width: 320px;">Status Kehadiran</th>
                            <th>Catatan Opsional</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-5">
                                    <i class="fas fa-user-slash mb-2" style="font-size: 32px;"></i>
                                    <p class="mb-0">Tidak ada siswa terdaftar di kelas ini.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $i = 1; foreach ($students as $student): ?>
                                <?php
                                $existing = isset($existingMap[$student['id']]) ? $existingMap[$student['id']] : null;
                                $status = $existing ? $existing['status'] : 'hadir';
                                $note = $existing ? $existing['note'] : '';
                                ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td class="font-weight-bold text-dark"><?= htmlspecialchars($student['name']) ?></td>
                                    <td>
                                        <?php $disabled = (session()->get('role') === 'kepsek') ? 'disabled' : ''; ?>
                                        <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                                            <label class="btn btn-sm btn-outline-success flex-fill <?= $status == 'hadir' ? 'active' : '' ?> <?= $disabled ? 'disabled' : '' ?>">
                                                <input type="radio" name="attendance[<?= $student['id'] ?>]" value="hadir" id="rad-hadir-<?= $student['id'] ?>" autocomplete="off" <?= $status == 'hadir' ? 'checked' : '' ?> <?= $disabled ?>> Hadir
                                            </label>
                                            <label class="btn btn-sm btn-outline-warning flex-fill <?= $status == 'sakit' ? 'active' : '' ?> <?= $disabled ? 'disabled' : '' ?>">
                                                <input type="radio" name="attendance[<?= $student['id'] ?>]" value="sakit" id="rad-sakit-<?= $student['id'] ?>" autocomplete="off" <?= $status == 'sakit' ? 'checked' : '' ?> <?= $disabled ?>> Sakit
                                            </label>
                                            <label class="btn btn-sm btn-outline-info flex-fill <?= $status == 'izin' ? 'active' : '' ?> <?= $disabled ? 'disabled' : '' ?>">
                                                <input type="radio" name="attendance[<?= $student['id'] ?>]" value="izin" id="rad-izin-<?= $student['id'] ?>" autocomplete="off" <?= $status == 'izin' ? 'checked' : '' ?> <?= $disabled ?>> Izin
                                            </label>
                                            <label class="btn btn-sm btn-outline-danger flex-fill <?= $status == 'alpha' ? 'active' : '' ?> <?= $disabled ? 'disabled' : '' ?>">
                                                <input type="radio" name="attendance[<?= $student['id'] ?>]" value="alpha" id="rad-alpha-<?= $student['id'] ?>" autocomplete="off" <?= $status == 'alpha' ? 'checked' : '' ?> <?= $disabled ?>> Alpha
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="note_<?= $student['id'] ?>" class="form-control form-control-sm" placeholder="Sakit Demam, dll" value="<?= htmlspecialchars($note) ?>" <?= $disabled ?>>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer border-top d-flex justify-content-end py-3">
            <a href="<?= base_url('/attendance?date=' . $date) ?>" class="btn btn-light mr-2">Kembali</a>
            <?php if (session()->get('role') !== 'kepsek'): ?>
                <button type="submit" class="btn btn-success"><i class="fas fa-save mr-2"></i> Simpan Absensi Kelas</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- JS helper for Hadir Semua & Form Validation -->
<script>
    function markAllHadir() {
        <?php foreach ($students as $student): ?>
            // Click/check the hadir button label
            $('#rad-hadir-<?= $student['id'] ?>').parent().addClass('active').siblings().removeClass('active');
            document.getElementById('rad-hadir-<?= $student['id'] ?>').checked = true;
        <?php endforeach; ?>
        // Automatically submit the form to save and return
        document.querySelector('form').submit();
    }

    document.querySelector('form').addEventListener('submit', function(e) {
        let isValid = true;
        let errMessage = "";
        
        document.querySelectorAll('tbody tr').forEach(row => {
            const nameEl = row.querySelector('.font-weight-bold');
            if (!nameEl) return;
            const studentName = nameEl.textContent.trim();
            
            const radSakit = row.querySelector('input[value="sakit"]');
            const radIzin = row.querySelector('input[value="izin"]');
            const radAlpha = row.querySelector('input[value="alpha"]');
            
            if ((radSakit && radSakit.checked) || (radIzin && radIzin.checked) || (radAlpha && radAlpha.checked)) {
                const noteInput = row.querySelector('input[type="text"]');
                const noteValue = noteInput ? noteInput.value.trim() : "";
                
                if (noteValue === "") {
                    isValid = false;
                    const statusStr = radSakit.checked ? "Sakit" : (radIzin.checked ? "Izin" : "Alpha");
                    errMessage = `Siswa "${studentName}" berstatus ${statusStr} wajib diberi keterangan/catatan.`;
                    
                    noteInput.classList.add('is-invalid');
                    noteInput.focus();
                } else {
                    noteInput.classList.remove('is-invalid');
                }
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert(errMessage);
        }
    });
</script>
<?= $this->endSection() ?>
