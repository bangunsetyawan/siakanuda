<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="font-weight-bold text-dark mb-1">
                <i class="fas fa-calendar-times text-danger mr-2"></i> Manajemen Hari Libur
            </h2>
            <p class="text-secondary mb-0" style="font-size: 13.5px;">
                Kelola hari libur nasional, sekolah, dan cuti bersama. Sistem tidak akan mengirim notifikasi KBM pada hari libur.
            </p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Form Tambah -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-header bg-white border-bottom">
                <h6 class="font-weight-bold text-dark mb-0">
                    <i class="fas fa-plus-circle text-success mr-2"></i> Tambah Hari Libur
                </h6>
            </div>
            <div class="card-body">
                <form id="form-tambah-libur">
                    <?= csrf_field() ?>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary" style="font-size: 12.5px;">Tanggal</label>
                        <input type="date" name="date" id="input-date" class="form-control" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary" style="font-size: 12.5px;">Keterangan / Alasan</label>
                        <input type="text" name="reason" class="form-control" required maxlength="200" placeholder="Contoh: Hari Raya Idul Adha 2026">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary" style="font-size: 12.5px;">Jenis</label>
                        <select name="type" class="form-control" required>
                            <option value="nasional">🇮🇩 Nasional</option>
                            <option value="sekolah">🏫 Sekolah</option>
                            <option value="cuti bersama">🌴 Cuti Bersama</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success btn-block font-weight-bold" id="btn-submit-libur">
                        <i class="fas fa-save mr-1"></i> Simpan Hari Libur
                    </button>
                    <div id="form-alert" class="mt-2"></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar -->
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="font-weight-bold text-dark mb-0">
                    <i class="fas fa-list text-info mr-2"></i> Daftar Hari Libur
                </h6>
                <span class="badge badge-secondary font-weight-bold"><?= count($holidays) ?> Total</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($holidays)): ?>
                    <div class="p-5 text-center text-secondary">
                        <i class="fas fa-calendar-day mb-2" style="font-size: 32px; opacity: 0.3;"></i>
                        <p class="mb-0">Belum ada hari libur yang ditambahkan.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover mb-0" style="font-size: 13.5px;">
                            <thead style="background: #fef2f2; position: sticky; top: 0;">
                                <tr>
                                    <th style="padding: 12px;">Tanggal</th>
                                    <th style="padding: 12px;">Keterangan</th>
                                    <th style="padding: 12px;">Jenis</th>
                                    <th style="padding: 12px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $today = date('Y-m-d');
                                foreach ($holidays as $h): 
                                    $isPast = ($h['date'] < $today);
                                    $isToday = ($h['date'] === $today);
                                    $typeColors = [
                                        'nasional' => 'danger',
                                        'sekolah' => 'warning',
                                        'cuti bersama' => 'info'
                                    ];
                                    $typeIcons = [
                                        'nasional' => '🇮🇩',
                                        'sekolah' => '🏫',
                                        'cuti bersama' => '🌴'
                                    ];
                                    $typeColor = $typeColors[$h['type']] ?? 'secondary';
                                    $typeIcon = $typeIcons[$h['type']] ?? '📅';
                                ?>
                                <tr style="<?= $isToday ? 'background: rgba(239, 68, 68, 0.05);' : '' ?>">
                                    <td style="padding: 12px;">
                                        <strong class="text-dark"><?= date('d M Y', strtotime($h['date'])) ?></strong>
                                        <small class="d-block text-secondary"><?= date('l', strtotime($h['date'])) ?></small>
                                        <?php if ($isToday): ?>
                                            <span class="badge badge-danger mt-1 font-weight-bold">HARI INI</span>
                                        <?php elseif (!$isPast): ?>
                                            <span class="badge badge-success mt-1 font-weight-bold">AKAN DATANG</span>
                                        <?php else: ?>
                                            <span class="badge badge-light mt-1 text-muted">LEWAT</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 12px;"><?= htmlspecialchars($h['reason']) ?></td>
                                    <td style="padding: 12px;">
                                        <span class="badge badge-<?= $typeColor ?> font-weight-bold">
                                            <?= $typeIcon ?> <?= ucfirst($h['type']) ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px;" class="text-center">
                                        <button class="btn btn-xs btn-outline-danger btn-delete-libur font-weight-bold" 
                                                data-id="<?= $h['id'] ?>" 
                                                data-reason="<?= htmlspecialchars($h['reason']) ?>"
                                                style="border-radius: 8px;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('form-tambah-libur').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-submit-libur');
    const alertBox = document.getElementById('form-alert');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';
    alertBox.innerHTML = '';

    const formData = new FormData(this);
    try {
        const res = await fetch('<?= base_url('/hari-libur/add') ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (data.ok) {
            alertBox.innerHTML = '<div class="alert alert-success py-2 px-3 mb-0" style="font-size: 12.5px;"><i class="fas fa-check-circle"></i> Berhasil ditambahkan!</div>';
            setTimeout(() => location.reload(), 800);
        } else {
            alertBox.innerHTML = `<div class="alert alert-danger py-2 px-3 mb-0" style="font-size: 12.5px;">${data.error}</div>`;
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save mr-1"></i> Simpan Hari Libur';
        }
    } catch (err) {
        alertBox.innerHTML = '<div class="alert alert-danger py-2 px-3 mb-0" style="font-size: 12.5px;">Terjadi kesalahan jaringan.</div>';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save mr-1"></i> Simpan Hari Libur';
    }
});

document.querySelectorAll('.btn-delete-libur').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (!confirm(`Hapus hari libur "${this.dataset.reason}"?`)) return;
        const id = this.dataset.id;
        try {
            const res = await fetch(`<?= base_url('/hari-libur/delete') ?>/${id}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.ok) {
                location.reload();
            } else {
                alert('Gagal menghapus: ' + (data.error || 'unknown'));
            }
        } catch (err) {
            alert('Terjadi kesalahan jaringan.');
        }
    });
});
</script>
<?= $this->endSection() ?>
