<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Laporan Harian PKL</h1>
            <p class="text-secondary mb-0">Rangkuman rekap absen, jurnal kegiatan, dan bukti foto harian PKL siswa.</p>
        </div>
        <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
            <div class="input-group input-group-sm mr-2" style="width: 250px;">
                <input type="text" id="tableSearch" class="form-control" placeholder="Cari Tempat / Ketua...">
                <div class="input-group-append">
                    <button type="button" class="btn btn-default">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <?php if (in_array($userRole, ['admin', 'kepsek'])): ?>
                <form action="<?= base_url('/pkl/delete-all-reports') ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS SEMUA laporan PKL pada tanggal <?= date('d M Y', strtotime($date)) ?>? Tindakan ini tidak dapat dibatalkan!');" class="m-0">
                    <?= csrf_field() ?>
                    <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
                    <button type="submit" class="btn btn-danger font-weight-bold btn-sm">
                        <i class="fas fa-trash-alt mr-1"></i> Hapus Semua Laporan
                    </button>
                </form>
                <button type="button" class="btn btn-warning font-weight-bold btn-sm" data-toggle="modal" data-target="#pklSettingsModal">
                    <i class="fas fa-cog"></i> Pengaturan PKL
                </button>
            <?php endif; ?>
            <form action="" method="get" class="form-inline m-0">
                <input type="date" name="date" class="form-control form-control-sm mr-2" value="<?= htmlspecialchars($date) ?>" onchange="this.form.submit()">
                <noscript><button type="submit" class="btn btn-outline-primary btn-sm">Pilih Tanggal</button></noscript>
            </form>
        </div>
    </div>
</div>

<!-- Summary Stats -->
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 14px;">
            <div class="card-body py-3 d-flex align-items-center" style="gap: 14px;">
                <div class="d-flex align-items-center justify-content-center rounded-circle" style="width:44px;height:44px;min-width:44px;background:rgba(23,162,184,0.1);">
                    <i class="fas fa-briefcase text-info" style="font-size:18px;"></i>
                </div>
                <div>
                    <h4 class="font-weight-bold text-dark mb-0"><?= $totalGroups ?? 0 ?></h4>
                    <small class="text-secondary">Total Kelompok PKL</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 14px;">
            <div class="card-body py-3 d-flex align-items-center" style="gap: 14px;">
                <div class="d-flex align-items-center justify-content-center rounded-circle" style="width:44px;height:44px;min-width:44px;background:rgba(40,167,69,0.1);">
                    <i class="fas fa-check-circle text-success" style="font-size:18px;"></i>
                </div>
                <div>
                    <h4 class="font-weight-bold text-success mb-0"><?= $totalReported ?? 0 ?></h4>
                    <small class="text-secondary">Sudah Lapor Hari Ini</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 14px;">
            <div class="card-body py-3 d-flex align-items-center" style="gap: 14px;">
                <div class="d-flex align-items-center justify-content-center rounded-circle" style="width:44px;height:44px;min-width:44px;background:rgba(220,53,69,0.1);">
                    <i class="fas fa-exclamation-circle text-danger" style="font-size:18px;"></i>
                </div>
                <div>
                    <h4 class="font-weight-bold text-danger mb-0"><?= ($totalGroups ?? 0) - ($totalReported ?? 0) ?></h4>
                    <small class="text-secondary">Belum Lapor</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header border-bottom">
        <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-briefcase mr-2 text-info"></i> Laporan Masuk - Tanggal: <?= date('d M Y', strtotime($date)) ?></h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Tempat DU/DI & Kelompok</th>
                        <th style="width: 100px; text-align: center;">Jam Lapor</th>
                        <th style="width: 280px; text-align: center;">Status Kehadiran</th>
                        <th style="width: 200px; text-align: center;">Jurnal & Foto</th>
                        <th style="width: 180px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="reportTableBody">
                    <?php if (empty($reports)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-5">
                                <i class="fas fa-folder-open mb-2" style="font-size: 32px;"></i>
                                <p class="mb-0">Belum ada laporan PKL masuk untuk tanggal ini.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $time = strtotime($date);
                        $dayOfWeek = date('N', $time);
                        $mondayTime = $time - (($dayOfWeek - 1) * 86400);
                        $mondayDate = date('Y-m-d', $mondayTime);
                        ?>
                        <?php $i = 1; foreach ($reports as $report): ?>
                            <?php 
                            // Calculate attendance summaries
                            $attCounts = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0];
                            foreach ($report['attendance_data'] as $name => $status) {
                                $statusLower = strtolower($status);
                                if (isset($attCounts[$statusLower])) {
                                    $attCounts[$statusLower]++;
                                }
                            }
                            // Calculate journal entries
                            $journalCount = 0;
                            foreach ($report['jurnal'] as $name => $text) {
                                if (trim($text) !== '') $journalCount++;
                            }
                            // First photo thumbnail
                            $firstPhoto = null;
                            if (!empty($report['photo_urls'])) {
                                $firstPhoto = reset($report['photo_urls']);
                            }
                            ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td>
                                    <?php 
                                    $ketuaName = '';
                                    foreach ($students as $s) {
                                        $sPhone = $s['phone'] ?: $s['nis'];
                                        if ($sPhone === $report['ketua_phone']) {
                                            $ketuaName = $s['name'];
                                            break;
                                        }
                                    }
                                    $hiddenMembers = implode(' ', array_keys($report['attendance_data']));
                                    ?>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($report['tempat_pkl']) ?></strong>
                                    <?php if ($report['is_takeover']): ?>
                                        <span class="badge badge-light text-warning mb-1" style="font-size: 10px;">Diisi Guru (Takeover)</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($ketuaName): ?>
                                        <div class="text-primary font-weight-bold" style="font-size: 13px;"><?= htmlspecialchars($ketuaName) ?></div>
                                        <small class="text-secondary"><i class="fas fa-user-tie mr-1"></i><?= htmlspecialchars($report['ketua_phone']) ?></small>
                                    <?php else: ?>
                                        <div class="text-secondary font-italic" style="font-size: 13px;">Nama tidak ditemukan</div>
                                        <small class="text-secondary"><i class="fas fa-user-tie mr-1"></i><?= htmlspecialchars($report['ketua_phone']) ?></small>
                                    <?php endif; ?>
                                    
                                    <!-- Hidden text for search filter to find member names -->
                                    <span class="d-none"><?= htmlspecialchars($hiddenMembers) ?></span>
                                </td>
                                <td class="text-center align-middle">
                                    <?php if ($report['created_at']): ?>
                                        <span class="badge badge-light border text-dark py-1 px-2"><i class="far fa-clock mr-1"></i> <?= date('H:i', strtotime($report['created_at'])) ?></span>
                                    <?php else: ?>
                                        <span class="text-secondary text-xs">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center align-middle">
                                    <?php if ($report['status_libur']): ?>
                                        <span class="badge badge-warning px-3 py-1 font-weight-bold text-dark" style="font-size: 11px;"><i class="fas fa-store-alt-slash mr-1"></i> LIBUR / TUTUP</span>
                                    <?php else: ?>
                                        <div style="font-size: 13px;">
                                            <?php if ($attCounts['hadir'] > 0): ?>
                                                <span class="badge badge-success px-2 py-1 font-weight-bold" title="Hadir"><?= $attCounts['hadir'] ?> Hadir</span>
                                            <?php endif; ?>
                                            <?php if ($attCounts['sakit'] > 0): ?>
                                                <span class="badge badge-warning text-dark px-2 py-1 font-weight-bold" title="Sakit"><?= $attCounts['sakit'] ?> Sakit</span>
                                            <?php endif; ?>
                                            <?php if ($attCounts['izin'] > 0): ?>
                                                <span class="badge badge-info px-2 py-1 font-weight-bold" title="Izin"><?= $attCounts['izin'] ?> Izin</span>
                                            <?php endif; ?>
                                            <?php if ($attCounts['alpha'] > 0): ?>
                                                <span class="badge badge-danger px-2 py-1 font-weight-bold" title="Alpha"><?= $attCounts['alpha'] ?> Alpha</span>
                                            <?php endif; ?>
                                            <div class="text-secondary text-xs mt-1 font-weight-bold"><?= count($report['attendance_data']) ?> Anggota</div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center align-middle">
                                    <?php if ($report['status_libur']): ?>
                                        <span class="text-secondary text-xs"><em>Alasan: "<?= htmlspecialchars($report['jurnal']['keterangan'] ?? '-') ?>"</em></span>
                                    <?php else: ?>
                                        <span class="badge badge-light border font-weight-bold text-dark px-2 py-1" style="font-size: 11px;"><i class="fas fa-pen-nib mr-1 text-primary"></i> <?= $journalCount ?> Jurnal</span>
                                        <?php if ($firstPhoto): ?>
                                            <div class="mt-1 d-flex align-items-center justify-content-center">
                                                <img src="<?= htmlspecialchars($firstPhoto) ?>" class="rounded border shadow-xs" style="width: 28px; height: 28px; object-fit: cover;">
                                                <?php if (count($report['photo_urls']) > 1): ?>
                                                    <span class="text-secondary font-weight-bold text-xs ml-1">+<?= count($report['photo_urls']) - 1 ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-secondary text-xs mt-1">Tanpa Foto</div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                 <td class="align-middle">
                                     <div class="d-flex justify-content-center align-items-center flex-wrap" style="gap: 4px;">
                                         <button type="button" class="btn btn-xs btn-info font-weight-bold rounded px-2" onclick='showReportDetail(<?= json_encode($report, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="Lihat Detail Laporan">
                                             <i class="fas fa-eye mr-1"></i> Detail
                                         </button>
                                         <!-- Riwayat & Cetak PDF -->
                                         <?php if (in_array($userRole, ['admin', 'kepsek', 'guru', 'guru_bk'])): ?>
                                             <a href="<?= base_url('/pkl/riwayat/' . $report['ketua_phone']) ?>" class="btn btn-xs btn-primary font-weight-bold rounded px-2" title="Riwayat Laporan & Cetak">
                                                 <i class="fas fa-history mr-1"></i> Riwayat
                                             </a>
                                         <?php endif; ?>
                                         <!-- Rekap Mingguan: Dihapus dari view Admin (hanya Ketua yang bisa cetak via student_report.php) -->
                                         <?php if (in_array($userRole, ['admin', 'kepsek'])): ?>
                                             <form action="<?= base_url('/pkl/delete-report/' . $report['id']) ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus laporan PKL ini? Semua data absensi KBM siswa terkait pada tanggal ini juga akan dihapus.');" class="d-inline m-0">
                                                 <?= csrf_field() ?>
                                                 <button type="submit" class="btn btn-xs btn-danger font-weight-bold rounded" title="Hapus Laporan">
                                                     <i class="fas fa-trash-alt"></i>
                                                 </button>
                                             </form>
                                         <?php endif; ?>
                                         <!-- Kirim Ulang Notifikasi -->
                                         <?php if (in_array($userRole, ['admin', 'kepsek'])): ?>
                                             <form action="<?= base_url('/pkl/resend-broadcast') ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin mengirim ulang notifikasi WhatsApp untuk laporan ini?');" class="d-inline m-0">
                                                 <?= csrf_field() ?>
                                                 <input type="hidden" name="date" value="<?= htmlspecialchars($report['date']) ?>">
                                                 <input type="hidden" name="ketua_phone" value="<?= htmlspecialchars($report['ketua_phone']) ?>">
                                                 <button type="submit" class="btn btn-xs btn-success font-weight-bold rounded px-2" title="Kirim Ulang Notifikasi WA">
                                                     <i class="fab fa-whatsapp mr-1"></i> Notif
                                                 </button>
                                             </form>
                                         <?php endif; ?>
                                     </div>
                                 </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail Laporan PKL -->
<div class="modal fade" id="reportDetailModal" tabindex="-1" role="dialog" aria-labelledby="reportDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title font-weight-bold text-dark" id="reportDetailModalLabel">
                    <i class="fas fa-briefcase text-info mr-2"></i> Detail Laporan PKL
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <!-- Info DU/DI & Tanggal -->
                <div class="row mb-3">
                    <div class="col-md-6 mb-2">
                        <span class="text-secondary font-weight-bold text-xs d-block">TEMPAT DU/DI:</span>
                        <h5 class="font-weight-bold text-dark mb-0" id="modal-tempat-pkl">-</h5>
                    </div>
                    <div class="col-md-6 mb-2">
                        <span class="text-secondary font-weight-bold text-xs d-block">TANGGAL LAPORAN:</span>
                        <h5 class="font-weight-bold text-dark mb-0" id="modal-date">-</h5>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-6 mb-2">
                        <span class="text-secondary font-weight-bold text-xs d-block">STATUS PRESENSI:</span>
                        <div id="modal-status-badge">-</div>
                    </div>
                    <div class="col-md-6 mb-2" id="modal-location-container">
                        <span class="text-secondary font-weight-bold text-xs d-block">LOKASI PRESENSI:</span>
                        <h5 class="font-weight-bold text-dark mb-0" id="modal-location-data">-</h5>
                    </div>
                </div>

                <hr class="my-3">

                <!-- Detail Kehadiran Anggota -->
                <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-users text-primary mr-1"></i> Kehadiran & Jurnal Harian Kelompok</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="modal-detail-table">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th style="width: 180px;">Nama Siswa</th>
                                <th style="width: 100px; text-align: center;">Status</th>
                                <th>Jurnal Kegiatan</th>
                                <th style="width: 120px; text-align: center;">Foto Bukti</th>
                            </tr>
                        </thead>
                        <tbody id="modal-detail-tbody">
                            <!-- Rows populated by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light border-top">
                <button type="button" class="btn btn-secondary font-weight-bold rounded-lg px-4" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // === Table Search Logic ===
    const searchInput = document.getElementById('tableSearch');
    const tableBody = document.getElementById('reportTableBody');
    if (searchInput && tableBody) {
        searchInput.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const rows = tableBody.getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                if (rows[i].cells.length === 1) continue; // Skip empty message row
                const text = rows[i].textContent.toLowerCase();
                if (text.includes(filter)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });
    }
});

function showReportDetail(report) {
    document.getElementById('modal-tempat-pkl').textContent = report.tempat_pkl + (report.is_takeover ? ' (Takeover)' : '');
    
    // Format Date: e.g. "04 Jun 2026"
    const dateObj = new Date(report.date);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const formattedDate = `${String(dateObj.getDate()).padStart(2, '0')} ${months[dateObj.getMonth()]} ${dateObj.getFullYear()}`;
    document.getElementById('modal-date').textContent = formattedDate;

    // Status
    const statusDiv = document.getElementById('modal-status-badge');
    const tbody = document.getElementById('modal-detail-tbody');
    tbody.innerHTML = '';

    const locContainer = document.getElementById('modal-location-container');
    const locData = document.getElementById('modal-location-data');

    if (report.status_libur == 1) {
        statusDiv.innerHTML = '<span class="badge badge-warning text-dark font-weight-bold px-3 py-1" style="font-size: 13px;"><i class="fas fa-store-alt-slash mr-1"></i> LIBUR / TUTUP</span>';
        if (locContainer) locContainer.style.display = 'none';
        
        // Populate one row for Libur info
        const reason = report.jurnal && report.jurnal.keterangan ? report.jurnal.keterangan : '-';
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-secondary"><em>Instansi Libur / Tutup. Alasan: "${reason}"</em></td></tr>`;
    } else {
        statusDiv.innerHTML = '<span class="badge badge-success font-weight-bold px-3 py-1" style="font-size: 13px;"><i class="fas fa-check-circle mr-1"></i> MASUK PKL</span>';
        if (locContainer) {
            locContainer.style.display = 'block';
            let locText = report.location_data ? report.location_data : 'Tidak dicantumkan';
            if (locText.includes('| GPS:')) {
                let parts = locText.split('| GPS:');
                let nameStr = parts[0].trim();
                let gpsStr = parts[1].trim();
                locData.innerHTML = `${nameStr} <a href="https://maps.google.com/?q=${gpsStr}" target="_blank" class="btn btn-xs btn-outline-primary ml-2 rounded-pill"><i class="fas fa-map-marker-alt"></i> Buka Maps</a>`;
            } else {
                locData.textContent = locText;
            }
        }

        // Loop through members
        let i = 1;
        for (const [name, status] of Object.entries(report.attendance_data)) {
            const tr = document.createElement('tr');
            
            // #
            const tdNo = document.createElement('td');
            tdNo.textContent = i++;
            tr.appendChild(tdNo);

            // Name
            const tdName = document.createElement('td');
            tdName.className = 'font-weight-bold text-dark';
            tdName.textContent = name;
            tr.appendChild(tdName);

            // Status Badge
            const tdStatus = document.createElement('td');
            tdStatus.className = 'text-center';
            const statusUpper = status.toUpperCase();
            let statusClass = 'secondary';
            if (status === 'hadir') statusClass = 'success';
            else if (status === 'sakit') statusClass = 'warning text-dark';
            else if (status === 'izin') statusClass = 'info';
            else if (status === 'alpha') statusClass = 'danger';
            tdStatus.innerHTML = `<span class="badge badge-${statusClass} font-weight-bold px-2 py-1">${statusUpper}</span>`;
            tr.appendChild(tdStatus);

            // Journal
            const tdJournal = document.createElement('td');
            const journalText = report.jurnal && report.jurnal[name] ? report.jurnal[name] : '—';
            tdJournal.textContent = journalText;
            tr.appendChild(tdJournal);

            // Photo
            const tdPhoto = document.createElement('td');
            tdPhoto.className = 'text-center';
            const photoUrl = report.photo_urls && report.photo_urls[name] ? report.photo_urls[name] : null;
            if (photoUrl) {
                tdPhoto.innerHTML = `<a href="${photoUrl}" target="_blank"><img src="${photoUrl}" class="rounded border shadow-xs" style="width: 44px; height: 44px; object-fit: cover;" title="Klik untuk perbesar"></a>`;
            } else {
                tdPhoto.innerHTML = '<span class="text-secondary text-xs">—</span>';
            }
            tr.appendChild(tdPhoto);

            tbody.appendChild(tr);
        }
    }

    // Show bootstrap modal
    $('#reportDetailModal').modal('show');
}
</script>
<!-- Modal Pengaturan Masa PKL -->
<div class="modal fade" id="pklSettingsModal" tabindex="-1" role="dialog" aria-labelledby="pklSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-warning border-bottom">
                <h5 class="modal-title font-weight-bold text-dark" id="pklSettingsModalLabel">
                    <i class="fas fa-cog mr-2"></i> Pengaturan Masa PKL
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('/pkl/update-settings') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Status Mode PKL</label>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="statusPklSwitch" name="pkl_active" <?= (isset($pklActive) && $pklActive === '1') ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="statusPklSwitch">Aktifkan Fitur PKL untuk Siswa</label>
                        </div>
                        <small class="form-text text-muted">Jika dinonaktifkan, siswa tidak bisa mengisi laporan harian PKL.</small>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Batas Waktu Pelaporan Harian (Opsional)</label>
                        <input type="time" class="form-control" name="batas_waktu" value="<?= htmlspecialchars($pklTimeLimit ?? '23:59') ?>">
                        <small class="form-text text-muted">Batas jam pengisian jurnal harian.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary font-weight-bold rounded px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning font-weight-bold rounded px-4">Simpan Pengaturan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
