<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-3">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1 class="m-0 font-weight-bold text-dark" style="font-size: 1.375rem;"><i class="fas fa-calendar-week text-success mr-2"></i>Kalender Akademik</h1>
                <p class="text-secondary mb-0" style="font-size: 0.8125rem;">Tahun Pelajaran <?= htmlspecialchars($selectedTahun) ?></p>
            </div>
            <?php if (!empty($tahunList) && count($tahunList) > 1): ?>
            <form method="get" action="<?= base_url('/kalender-akademik') ?>" class="mt-2 mt-md-0">
                <input type="hidden" name="tab" value="<?= htmlspecialchars($activeTab) ?>">
                <select name="tahun" class="form-control form-control-sm" style="border-radius: 0.5rem; min-width: 9rem;" onchange="this.form.submit()">
                    <?php foreach ($tahunList as $t): ?>
                        <option value="<?= htmlspecialchars($t['tahun_pelajaran']) ?>" <?= $t['tahun_pelajaran'] === $selectedTahun ? 'selected' : '' ?>><?= htmlspecialchars($t['tahun_pelajaran']) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Tab Navigation -->
<ul class="nav nav-tabs mb-4" style="border-bottom: 2px solid #e2e8f0;">
    <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'semester' ? 'active font-weight-bold' : '' ?>" 
           href="<?= base_url('/kalender-akademik?tab=semester' . (!empty($selectedTahun) ? '&tahun=' . urlencode($selectedTahun) : '')) ?>"
           style="border: none; border-bottom: 3px solid <?= $activeTab === 'semester' ? '#16a34a' : 'transparent' ?>; border-radius: 0.5rem 0.5rem 0 0; font-size: 0.875rem; color: <?= $activeTab === 'semester' ? '#16a34a' : '#64748b' ?>;">
            <i class="fas fa-calendar-alt mr-1"></i> Kalender Semester
        </a>
    </li>
    <?php if ($role === 'admin'): ?>
    <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'holidays' ? 'active font-weight-bold' : '' ?>" 
           href="<?= base_url('/kalender-akademik?tab=holidays') ?>"
           style="border: none; border-bottom: 3px solid <?= $activeTab === 'holidays' ? '#dc3545' : 'transparent' ?>; border-radius: 0.5rem 0.5rem 0 0; font-size: 0.875rem; color: <?= $activeTab === 'holidays' ? '#dc3545' : '#64748b' ?>;">
            <i class="fas fa-calendar-times mr-1"></i> Hari Libur
            <?php if (!empty($holidays)): ?>
                <span class="badge badge-pill badge-danger ml-1" style="font-size: 10px;"><?= count($holidays) ?></span>
            <?php endif; ?>
        </a>
    </li>
    <?php endif; ?>
</ul>

<?php if ($activeTab === 'holidays' && $role === 'admin'): ?>
<!-- ============ TAB HARI LIBUR ============ -->
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
            setTimeout(() => location.href = '<?= base_url('/kalender-akademik?tab=holidays') ?>', 800);
        } else {
            alertBox.innerHTML = '<div class="alert alert-danger py-2 px-3 mb-0" style="font-size: 12.5px;">' + data.error + '</div>';
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
        if (!confirm('Hapus hari libur "' + this.dataset.reason + '"?')) return;
        const id = this.dataset.id;
        try {
            const res = await fetch('<?= base_url('/hari-libur/delete') ?>/' + id, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.ok) {
                location.href = '<?= base_url('/kalender-akademik?tab=holidays') ?>';
            } else {
                alert('Gagal menghapus: ' + (data.error || 'unknown'));
            }
        } catch (err) {
            alert('Terjadi kesalahan jaringan.');
        }
    });
});
</script>

<?php else: ?>
<!-- ============ TAB SEMESTER ============ -->
<?php
$semesters = [
    ['label' => 'Semester Ganjil', 'key' => 'Ganjil', 'data' => $ganjil, 'color' => '#0284c7', 'icon' => 'fas fa-sun'],
    ['label' => 'Semester Genap',  'key' => 'Genap',  'data' => $genap,  'color' => '#16a34a', 'icon' => 'fas fa-leaf'],
];
?>

<?php foreach ($semesters as $sem): ?>
<div class="card mb-4 border-0 shadow-sm" style="border-radius: 1rem; overflow: hidden;">
    <div class="card-header border-0 d-flex justify-content-between align-items-center" style="background: <?= $sem['color'] ?>; padding: 0.875rem 1.25rem;">
        <h5 class="font-weight-bold text-white mb-0" style="font-size: 0.9375rem;"><i class="<?= $sem['icon'] ?> mr-2"></i><?= $sem['label'] ?> — TP <?= htmlspecialchars($selectedTahun) ?></h5>
        <?php if ($role === 'admin'): ?>
            <button type="button" class="btn btn-sm text-white font-weight-bold" style="background: rgba(255,255,255,0.2); border-radius: 0.5rem; font-size: 0.75rem;" onclick="document.getElementById('form-<?= $sem['key'] ?>').submit()"><i class="fas fa-save mr-1"></i>Simpan</button>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <form id="form-<?= $sem['key'] ?>" method="post" action="<?= base_url('/kalender-akademik/save') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="tahun_pelajaran" value="<?= htmlspecialchars($selectedTahun) ?>">
            <input type="hidden" name="semester" value="<?= $sem['key'] ?>">
            <div class="table-responsive">
                <table class="table table-sm mb-0" style="font-size: 0.8125rem;">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 0.625rem 0.75rem; width: 2.5rem; border: none;">No</th>
                            <th style="padding: 0.625rem 0.75rem; border: none;">Bulan</th>
                            <th style="padding: 0.625rem 0.75rem; width: 5.5rem; border: none; text-align: center;">Total</th>
                            <th style="padding: 0.625rem 0.75rem; width: 5.5rem; border: none; text-align: center;">Efektif</th>
                            <th style="padding: 0.625rem 0.75rem; width: 6.5rem; border: none; text-align: center;">Tdk Efektif</th>
                            <th style="padding: 0.625rem 0.75rem; border: none;">Keterangan</th>
                            <?php if ($role === 'admin'): ?><th style="padding: 0.625rem 0.75rem; width: 2.5rem; border: none;"></th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sem['data'])): ?>
                            <tr><td colspan="<?= $role === 'admin' ? 7 : 6 ?>" class="text-center text-muted p-4">Belum ada data. <?= $role === 'admin' ? 'Klik "+ Tambah Baris" untuk mulai mengisi.' : '' ?></td></tr>
                        <?php else: ?>
                            <?php foreach ($sem['data'] as $idx => $row): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <?php if ($role === 'admin'): ?>
                                        <input type="hidden" name="id[]" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="nomor[]" value="<?= $row['nomor'] ?>">
                                        <td style="padding: 0.5rem 0.75rem; vertical-align: middle; color: #94a3b8;"><?= $row['nomor'] ?></td>
                                        <td style="padding: 0.5rem 0.75rem;"><input type="text" name="bulan[]" value="<?= htmlspecialchars($row['bulan']) ?>" class="form-control form-control-sm" style="border-radius: 0.375rem; font-size: 0.8125rem;"></td>
                                        <td style="padding: 0.5rem 0.75rem;"><input type="number" name="total_pekan[]" value="<?= $row['total_pekan'] ?>" class="form-control form-control-sm text-center" style="border-radius: 0.375rem; font-size: 0.8125rem;" min="0"></td>
                                        <td style="padding: 0.5rem 0.75rem;"><input type="number" name="pekan_efektif[]" value="<?= $row['pekan_efektif'] ?>" class="form-control form-control-sm text-center" style="border-radius: 0.375rem; font-size: 0.8125rem;" min="0"></td>
                                        <td style="padding: 0.5rem 0.75rem;"><input type="number" name="pekan_tidak_efektif[]" value="<?= $row['pekan_tidak_efektif'] ?>" class="form-control form-control-sm text-center" style="border-radius: 0.375rem; font-size: 0.8125rem;" min="0"></td>
                                        <td style="padding: 0.5rem 0.75rem;"><input type="text" name="keterangan[]" value="<?= htmlspecialchars($row['keterangan']) ?>" class="form-control form-control-sm" style="border-radius: 0.375rem; font-size: 0.8125rem;" placeholder="—"></td>
                                        <td style="padding: 0.5rem 0.75rem; vertical-align: middle;">
                                            <form action="<?= base_url('/kalender-akademik/delete/' . $row['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Hapus baris ini?')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-link text-danger p-0" title="Hapus" style="font-size: 0.75rem;"><i class="fas fa-trash-alt"></i></button>
                                            </form>
                                        </td>
                                    <?php else: ?>
                                        <td style="padding: 0.5rem 0.75rem; vertical-align: middle; color: #94a3b8;"><?= $row['nomor'] ?></td>
                                        <td style="padding: 0.5rem 0.75rem; font-weight: 600;"><?= htmlspecialchars($row['bulan']) ?></td>
                                        <td style="padding: 0.5rem 0.75rem; text-align: center;"><?= $row['total_pekan'] ?></td>
                                        <td style="padding: 0.5rem 0.75rem; text-align: center; font-weight: 600; color: <?= $sem['color'] ?>;"><?= $row['pekan_efektif'] ?></td>
                                        <td style="padding: 0.5rem 0.75rem; text-align: center; color: #ef4444;"><?= $row['pekan_tidak_efektif'] ?></td>
                                        <td style="padding: 0.5rem 0.75rem; color: #64748b;"><?= htmlspecialchars($row['keterangan']) ?: '—' ?></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($sem['data'])): ?>
                    <tfoot style="background: #f8fafc; font-weight: 700;">
                        <tr>
                            <td style="padding: 0.625rem 0.75rem; border: none;" colspan="2">Total</td>
                            <td style="padding: 0.625rem 0.75rem; border: none; text-align: center;"><?= array_sum(array_column($sem['data'], 'total_pekan')) ?></td>
                            <td style="padding: 0.625rem 0.75rem; border: none; text-align: center; color: <?= $sem['color'] ?>;"><?= array_sum(array_column($sem['data'], 'pekan_efektif')) ?></td>
                            <td style="padding: 0.625rem 0.75rem; border: none; text-align: center; color: #ef4444;"><?= array_sum(array_column($sem['data'], 'pekan_tidak_efektif')) ?></td>
                            <td style="padding: 0.625rem 0.75rem; border: none;" <?= $role === 'admin' ? 'colspan="2"' : '' ?>></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
            <?php if ($role === 'admin'): ?>
            <div class="p-3 border-top d-flex justify-content-between align-items-center" style="background: #fafafa;">
                <button type="button" class="btn btn-sm btn-outline-secondary" style="border-radius: 0.5rem; font-size: 0.75rem;" onclick="addRow('form-<?= $sem['key'] ?>', '<?= $sem['key'] ?>')">
                    <i class="fas fa-plus mr-1"></i>Tambah Baris
                </button>
                <button type="submit" class="btn btn-sm btn-success" style="border-radius: 0.5rem; font-size: 0.75rem;">
                    <i class="fas fa-save mr-1"></i>Simpan Perubahan
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>
<?php endforeach; ?>

<?php if ($role === 'admin'): ?>
<script>
function addRow(formId, semester) {
    const form = document.getElementById(formId);
    const tbody = form.querySelector('tbody');
    const existingRows = tbody.querySelectorAll('tr');
    // Remove "no data" row if exists
    if (existingRows.length === 1 && existingRows[0].querySelector('td[colspan]')) {
        tbody.innerHTML = '';
    }
    const nextNo = tbody.querySelectorAll('tr').length + 1;
    const tr = document.createElement('tr');
    tr.style.borderBottom = '1px solid #f1f5f9';
    tr.innerHTML = `
        <input type="hidden" name="id[]" value="">
        <input type="hidden" name="nomor[]" value="${nextNo}">
        <td style="padding:0.5rem 0.75rem;vertical-align:middle;color:#94a3b8;">${nextNo}</td>
        <td style="padding:0.5rem 0.75rem;"><input type="text" name="bulan[]" class="form-control form-control-sm" style="border-radius:0.375rem;font-size:0.8125rem;" placeholder="contoh: Juli"></td>
        <td style="padding:0.5rem 0.75rem;"><input type="number" name="total_pekan[]" class="form-control form-control-sm text-center" style="border-radius:0.375rem;font-size:0.8125rem;" value="0" min="0"></td>
        <td style="padding:0.5rem 0.75rem;"><input type="number" name="pekan_efektif[]" class="form-control form-control-sm text-center" style="border-radius:0.375rem;font-size:0.8125rem;" value="0" min="0"></td>
        <td style="padding:0.5rem 0.75rem;"><input type="number" name="pekan_tidak_efektif[]" class="form-control form-control-sm text-center" style="border-radius:0.375rem;font-size:0.8125rem;" value="0" min="0"></td>
        <td style="padding:0.5rem 0.75rem;"><input type="text" name="keterangan[]" class="form-control form-control-sm" style="border-radius:0.375rem;font-size:0.8125rem;" placeholder="—"></td>
        <td style="padding:0.5rem 0.75rem;vertical-align:middle;"><a href="#" class="text-danger" onclick="this.closest('tr').remove();return false;"><i class="fas fa-times" style="font-size:0.75rem;"></i></a></td>
    `;
    tbody.appendChild(tr);
}
</script>
<?php endif; ?>
<?php endif; ?>

<?= $this->endSection() ?>
