<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<?php
// Generate Date Selector Options for Student Dropdown
$dropdownOptions = '';
$sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
for ($i = 0; $i <= 7; $i++) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $label = date('d M Y', strtotime($d));
    if ($i === 0) $label = "Hari Ini ($label)";
    elseif ($i === 1) $label = "Kemarin ($label)";
    else $label = "H-$i ($label)";
    $selected = ($d === $selectedDate) ? 'selected' : '';
    $dropdownOptions .= "<option value=\"$d\" $selected>$label</option>";
}
if ($selectedDate < $sevenDaysAgo) {
    $dropdownOptions .= "<option value=\"$selectedDate\" selected>Terkunci (" . date('d M Y', strtotime($selectedDate)) . ")</option>";
}
$groupIdParam = isset($_GET['group_id']) ? '&group_id=' . htmlspecialchars($_GET['group_id']) : '';
?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid">
        <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Laporan Harian PKL</h1>
        <p class="text-secondary mb-0">Halaman pengisian jurnal, kehadiran kelompok, dan foto dokumentasi PKL.</p>
    </div>
</div>

<div class="row">
    <!-- Form Pengisian Laporan (Hanya untuk Ketua Kelompok) -->
    <?php if ($isKetua): ?>
        <div class="col-lg-8 mb-4">
            <?php if ($todayReport): ?>
                <!-- Card Tampilan Laporan Terkirim (Read-Only) -->
                <div class="card card-outline card-success shadow-sm border-0 rounded-lg mb-4" id="today-report-preview-card">
                    <div class="card-header border-bottom d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3 class="card-title font-weight-bold text-dark mb-0">
                                <i class="fas fa-check-circle text-success mr-2"></i> Laporan Terkirim
                            </h3>
                            <br>
                            <small class="text-secondary font-weight-bold">🏭 DU/DI: <?= htmlspecialchars($group['tempat_pkl']) ?></small>
                        </div>
                        <?php if (isset($isTakeover) && $isTakeover): ?>
                            <div class="d-flex align-items-center bg-light rounded-pill p-1 border shadow-sm">
                                <div class="bg-warning text-dark rounded-pill px-3 py-1 font-weight-bold text-sm" title="Mode Override Admin">
                                    <i class="fas fa-user-shield mr-1"></i> Takeover
                                </div>
                                <input type="date" class="form-control form-control-sm border-0 bg-transparent font-weight-bold text-dark mx-2" 
                                       style="cursor: pointer; max-width: 140px; outline: none; box-shadow: none;" 
                                       max="<?= date('Y-m-d') ?>" 
                                       value="<?= htmlspecialchars($selectedDate) ?>" 
                                       onchange="window.location.href='?date=' + this.value + '<?= $groupIdParam ?>'">
                            </div>
                        <?php else: ?>
                            <div class="d-flex align-items-center bg-light rounded-pill p-1 border shadow-sm">
                                <div class="bg-success text-white rounded-pill px-3 py-1 font-weight-bold text-sm">
                                    <i class="fas fa-calendar-alt mr-1"></i> Laporan
                                </div>
                                <select class="form-control form-control-sm border-0 bg-transparent font-weight-bold text-dark mx-2" 
                                        style="cursor: pointer; min-width: 180px; outline: none; box-shadow: none;" 
                                        onchange="window.location.href='?date=' + this.value">
                                    <?= $dropdownOptions ?>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if ($todayReport['status_libur'] == 1): ?>
                            <div class="alert alert-warning text-center border-0 rounded-lg p-3 mb-0">
                                <h6 class="font-weight-bold mb-1"><i class="fas fa-store-alt-slash mr-2"></i>DU/DI Sedang Libur</h6>
                                <p class="mb-0 text-sm">Alasan: "<?= htmlspecialchars($todayReport['libur_reason'] ?? '-') ?>"</p>
                            </div>
                        <?php else: ?>
                            <!-- Lokasi Presensi Kelompok -->
                            <?php if (!empty($todayReport['location_data'])): ?>
                                <div class="mb-3 p-3 bg-light rounded-lg border">
                                    <span class="text-secondary font-weight-bold text-xs d-block mb-1"><i class="fas fa-map-marker-alt text-success mr-1"></i> LOKASI PRESENSI KELOMPOK:</span>
                                    <strong class="text-dark" style="font-size: 14px;"><?= htmlspecialchars($todayReport['location_data']) ?></strong>
                                </div>
                            <?php endif; ?>

                            <h5 class="font-weight-bold text-dark mb-3"><i class="fas fa-users text-primary mr-1"></i> Kehadiran & Jurnal Harian Anggota</h5>
                            <div class="list-group">
                                <?php foreach ($members as $index => $name): ?>
                                    <?php 
                                    $status = $todayReport['attendance_data'][$name] ?? 'hadir';
                                    $jurnal = $todayReport['jurnal'][$name] ?? '—';
                                    $photo = $todayReport['photo_urls'][$name] ?? null;
                                    $colors = ['hadir' => 'success', 'sakit' => 'warning', 'izin' => 'info', 'alpha' => 'danger'];
                                    $color = $colors[strtolower($status)] ?? 'secondary';
                                    ?>
                                    <div class="list-group-item p-3 border rounded-lg mb-3 bg-light shadow-xs">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="font-weight-bold text-dark mb-0">
                                                <span class="text-secondary font-weight-normal"><?= $index + 1 ?>.</span> <?= htmlspecialchars($name) ?>
                                            </h6>
                                            <span class="badge badge-<?= $color ?> font-weight-bold px-2 py-1" style="font-size: 11px;"><?= strtoupper($status) ?></span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-secondary font-weight-bold" style="font-size: 12px;">Jurnal:</span>
                                            <p class="text-dark bg-white p-2 rounded border mb-0" style="font-size: 13px;">
                                                <?= htmlspecialchars($jurnal) ?>
                                            </p>
                                        </div>
                                        <?php if ($photo): ?>
                                            <div class="mt-2">
                                                <span class="text-secondary d-block font-weight-bold mb-1" style="font-size: 12px;">Foto Dokumentasi:</span>
                                                <a href="<?= htmlspecialchars($photo) ?>" target="_blank">
                                                    <img src="<?= htmlspecialchars($photo) ?>" class="rounded border" style="max-width: 120px; max-height: 120px; object-fit: cover;">
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white border-top text-right">
                        <button type="button" class="btn btn-warning px-4 py-2 font-weight-bold text-dark rounded-lg" onclick="showEditForm()">
                            <i class="fas fa-edit mr-2"></i> Ubah Laporan Hari Ini
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($isLocked) && $isLocked && !(isset($isTakeover) && $isTakeover)): ?>
                <div class="alert alert-danger border-0 shadow-sm rounded-lg mb-4 p-4 text-center bg-white">
                    <h5 class="font-weight-bold text-danger mb-2"><i class="fas fa-lock mr-2"></i>Laporan Terkunci</h5>
                    <p class="text-dark mb-3">Laporan susulan hanya dapat diisi maksimal 7 hari ke belakang (H-7). Laporan untuk tanggal <strong><?= date('d M Y', strtotime($selectedDate)) ?></strong> sudah terkunci.</p>
                    <p class="text-secondary text-sm mb-0">Silakan hubungi Admin atau Guru Pembimbing jika ada kendala dan perlu bantuan pengisian susulan.</p>
                </div>
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center bg-white rounded-pill p-1 border border-danger shadow-sm">
                        <div class="bg-danger text-white rounded-pill px-3 py-1 font-weight-bold text-sm">
                            <i class="fas fa-lock mr-1"></i> Terkunci
                        </div>
                        <select class="form-control form-control-sm border-0 bg-transparent font-weight-bold text-dark mx-2" 
                                style="cursor: pointer; min-width: 180px; outline: none; box-shadow: none;" 
                                onchange="window.location.href='?date=' + this.value">
                            <?= $dropdownOptions ?>
                        </select>
                    </div>
                </div>
            <?php elseif (isset($pklActive) && $pklActive !== '1'): ?>
                <div class="alert alert-danger border-0 shadow-sm rounded-lg mb-4 p-4 text-center bg-white">
                    <h5 class="font-weight-bold text-danger mb-2"><i class="fas fa-lock mr-2"></i>Fitur PKL Dinonaktifkan</h5>
                    <p class="text-dark mb-0">Mohon maaf, pengisian laporan harian PKL saat ini sedang ditutup/dinonaktifkan oleh Admin Sekolah. Silakan hubungi Admin atau pembimbing PKL Anda jika ada pertanyaan.</p>
                </div>
            <?php else: ?>
                <div class="card card-outline card-success shadow-sm border-0 rounded-lg" id="report-form-card" style="display: <?= $todayReport ? 'none' : 'block' ?>;">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h3 class="card-title font-weight-bold text-dark mb-0">
                            <?php
                            $formTitle = 'Form Laporan Hari Ini';
                            if (isset($isTakeover) && $isTakeover) {
                                $formTitle = 'Laporan Susulan (Takeover)';
                            } elseif ($selectedDate !== $today) {
                                $formTitle = 'Form Laporan Harian (Susulan)';
                            }
                            ?>
                            <i class="fas fa-edit text-success mr-2"></i> <?= $todayReport ? 'Ubah Laporan' : $formTitle ?>
                        </h3>
                        <br>
                        <small class="text-secondary font-weight-bold">🏭 DU/DI: <?= htmlspecialchars($group['tempat_pkl']) ?></small>
                    </div>
                    <?php if (isset($isTakeover) && $isTakeover): ?>
                        <div class="d-flex align-items-center bg-white rounded-pill p-1 border border-warning shadow-sm">
                            <div class="bg-warning text-dark rounded-pill px-3 py-1 font-weight-bold text-sm" title="Mode Override Admin">
                                <i class="fas fa-user-shield mr-1"></i> Takeover
                            </div>
                            <input type="date" class="form-control form-control-sm border-0 bg-transparent font-weight-bold text-dark mx-2" 
                                   style="cursor: pointer; max-width: 140px; outline: none; box-shadow: none;" 
                                   max="<?= date('Y-m-d') ?>" 
                                   value="<?= htmlspecialchars($selectedDate) ?>" 
                                   onchange="window.location.href='?date=' + this.value + '<?= $groupIdParam ?>'">
                        </div>
                    <?php else: ?>
                        <div class="d-flex align-items-center bg-white rounded-pill p-1 border border-success shadow-sm">
                            <div class="bg-success text-white rounded-pill px-3 py-1 font-weight-bold text-sm">
                                <i class="fas fa-calendar-alt mr-1"></i> Laporan
                            </div>
                            <select class="form-control form-control-sm border-0 bg-transparent font-weight-bold text-dark mx-2" 
                                    style="cursor: pointer; min-width: 180px; outline: none; box-shadow: none;" 
                                    onchange="window.location.href='?date=' + this.value">
                                <?= $dropdownOptions ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- /.card-header -->
                <form action="<?= base_url('/pkl/submit') ?>" method="post" enctype="multipart/form-data" id="studentReportForm" onsubmit="return submitFormWithCompression(event)">
                    <?= csrf_field() ?>
                    <input type="hidden" name="report_date" value="<?= htmlspecialchars($selectedDate) ?>">
                    <input type="hidden" name="is_takeover" value="<?= (isset($isTakeover) && $isTakeover) ? '1' : '0' ?>">
                    <?php if (isset($isTakeover) && $isTakeover): ?>
                        <input type="hidden" name="ketua_phone" value="<?= htmlspecialchars($group['ketua_phone']) ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <!-- Status DU/DI Hari Ini -->
                        <?php 
                        $isLibur = (isset($todayReport) && $todayReport['status_libur'] == 1);
                        ?>
                        <div class="mb-4 p-3 border rounded-lg bg-light">
                            <label class="font-weight-bold text-dark d-block mb-2" style="font-size: 14.5px;">🏢 Status DU/DI Hari Ini</label>
                            <div class="d-flex w-100 mb-2" style="gap: 12px; display: flex;">
                                <button type="button" class="btn font-weight-bold py-2 transition-all" 
                                        style="flex: 1; border-radius: 12px; transition: all 0.25s ease; <?= !$isLibur ? 'background: linear-gradient(135deg, #16a34a, #15803d); color: white; border: none; box-shadow: 0 4px 10px rgba(22, 163, 74, 0.25);' : 'background: transparent; color: #16a34a; border: 2px solid #16a34a;' ?>" 
                                        id="btn-status-masuk" onclick="setStatusLibur(0)">
                                    💼 Masuk PKL
                                </button>
                                <button type="button" class="btn font-weight-bold py-2 transition-all" 
                                        style="flex: 1; border-radius: 12px; transition: all 0.25s ease; <?= $isLibur ? 'background: linear-gradient(135deg, #ef4444, #b91c1c); color: white; border: none; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.25);' : 'background: transparent; color: #ef4444; border: 2px solid #ef4444;' ?>" 
                                        id="btn-status-libur" onclick="setStatusLibur(1)">
                                    🏢 Libur / Tutup
                                </button>
                            </div>
                            <input type="hidden" name="status_libur" id="status_libur" value="<?= $isLibur ? '1' : '0' ?>">
                            
                            <!-- Alasan Libur -->
                            <div id="libur-reason-section" class="mt-3" style="display: <?= $isLibur ? 'block' : 'none' ?>;">
                                <label class="font-weight-bold text-secondary mb-1" style="font-size: 12px;">📝 Alasan Tempat PKL Libur/Tutup (Wajib Diisi):</label>
                                <textarea name="libur_reason" id="libur_reason" class="form-control mb-3" rows="2" placeholder="Contoh: Hari Minggu / Toko sedang renovasi / Instansi tutup nasional..."><?= htmlspecialchars($todayReport['libur_reason'] ?? '') ?></textarea>
                                
                                <!-- Tombol Simpan Cepat saat Libur -->
                                <div class="d-flex justify-content-end mb-2">
                                    <?php if ($todayReport): ?>
                                        <button type="button" class="btn btn-secondary btn-sm mr-2 px-3 py-2 font-weight-bold rounded-lg" onclick="hideEditForm()">
                                            <i class="fas fa-times mr-1"></i> Batal
                                        </button>
                                    <?php endif; ?>
                                    <button type="submit" class="btn btn-warning btn-sm px-4 py-2 font-weight-bold rounded-lg text-dark shadow-sm">
                                        <i class="fas fa-save mr-1"></i> <?= $todayReport ? 'Simpan Perubahan' : 'Kirim Laporan Libur' ?>
                                    </button>
                                </div>
                            </div>

                            <!-- Tempat Melakukan Presensi -->
                            <div id="presensi-location-section" class="mt-3" style="display: <?= !$isLibur ? 'block' : 'none' ?>;">
                                <label class="font-weight-bold text-dark mb-1" style="font-size: 13.5px;">📍 Lokasi Melakukan Absensi PKL (Wajib di isi)</label>
                                <div class="alert alert-warning border-0 rounded-lg p-2 mb-2 text-xs d-flex align-items-center" style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a !important;">
                                    <i class="fas fa-exclamation-triangle mr-2" style="font-size: 14px;"></i>
                                    <span>Contoh: Bengkel Mobil Cak Bagong, Kantor PT. Abadi Jaya, dll.</span>
                                </div>
                                <input type="text" name="location_data" id="location_data" class="form-control" placeholder="Contoh: Bengkel Mobil Cak Bagong, Kantor PT. Abadi Jaya, dll." value="<?= htmlspecialchars($todayReport['location_data'] ?? '') ?>" oninput="checkLocationFilled()">
                                
                                <div class="mt-3 form-group p-2 rounded" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                                    <label class="font-weight-bold text-dark mb-1" style="font-size: 12px;">
                                        📸 Foto Dokumentasi Kelompok Hari Ini (Wajib)
                                    </label>
                                    <small class="d-block text-secondary mb-2" style="font-size: 11px;">Satu foto bersama seluruh anggota kelompok yang hadir.</small>
                                    <?php if (isset($todayReport) && isset($todayReport['photo_urls']['kelompok'])): ?>
                                        <div class="mb-2 mt-1">
                                            <a href="<?= htmlspecialchars($todayReport['photo_urls']['kelompok']) ?>" target="_blank">
                                                <img src="<?= htmlspecialchars($todayReport['photo_urls']['kelompok']) ?>" class="rounded border shadow-xs" style="width: 70px; height: 70px; object-fit: cover;">
                                            </a>
                                            <small class="text-success d-block" style="font-size: 10px;"><i class="fas fa-check-circle"></i> Sudah upload foto kelompok.</small>
                                        </div>
                                    <?php endif; ?>
                                    <div class="custom-file" style="max-width: 280px;">
                                        <input type="file" class="custom-file-input" name="photo_kelompok" accept="image/*" onchange="$(this).next('.custom-file-label').html(this.files[0].name)">
                                        <label class="custom-file-label" style="font-size: 11px; padding: 4px 8px; height: 26px; line-height: 1.5;">Pilih Foto...</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Alert Silakan Isi Lokasi Terlebih Dahulu -->
                        <div id="location-warning-message" class="alert alert-danger border-0 rounded-lg p-3 mb-3" style="display: none; background-color: #fef2f2; color: #991b1b; border: 1px solid #fee2e2 !important;">
                            <h6 class="font-weight-bold mb-1"><i class="fas fa-lock mr-2"></i>Pengisian Jurnal &amp; Absensi Dikunci</h6>
                            <p class="mb-0 text-sm">Silakan isi <strong>📍 Lokasi Melakukan Absensi PKL (Wajib di isi)</strong> terlebih dahulu di atas untuk membuka pengisian jurnal &amp; absensi anggota.</p>
                        </div>

                        <!-- Bagian Laporan Harian (Presensi & Jurnal) -->
                        <div id="attendance-section">
                            <h5 class="font-weight-bold text-dark mb-3"><i class="fas fa-users text-primary mr-1"></i> Kehadiran & Jurnal Harian Anggota</h5>
                            
                            <?php foreach ($members as $index => $name): ?>
                                <?php 
                                $status = 'hadir';
                                $jurnalVal = '';
                                if (isset($todayReport) && isset($todayReport['attendance_data'][$name])) {
                                    $status = $todayReport['attendance_data'][$name];
                                }
                                if (isset($todayReport) && isset($todayReport['jurnal'][$name])) {
                                    $jurnalVal = $todayReport['jurnal'][$name];
                                }
                                
                                $name_sanitized = preg_replace('/[^a-zA-Z0-9]/', '_', $name);
                                $sakitDetail = '';
                                $izinDetail = '';
                                $alphaHubungi = 'Ya';
                                $alphaDetail = '';

                                if ($status === 'sakit') {
                                    $sakitDetail = str_starts_with($jurnalVal, 'Sakit: ') ? substr($jurnalVal, 7) : $jurnalVal;
                                } elseif ($status === 'izin') {
                                    $izinDetail = str_starts_with($jurnalVal, 'Izin: ') ? substr($jurnalVal, 6) : $jurnalVal;
                                } elseif ($status === 'alpha') {
                                    if (strpos($jurnalVal, 'Sudah dihubungi: Tidak') !== false) {
                                        $alphaHubungi = 'Tidak';
                                        $alphaDetail = trim(str_replace('Sudah dihubungi: Tidak', '', $jurnalVal));
                                    } else {
                                        $alphaHubungi = 'Ya';
                                        $alphaDetail = trim(str_replace('Sudah dihubungi: Ya', '', $jurnalVal));
                                    }
                                    if (str_starts_with($alphaDetail, '. Alasan: ')) {
                                        $alphaDetail = substr($alphaDetail, 10);
                                    } elseif (str_starts_with($alphaDetail, '.')) {
                                        $alphaDetail = trim(substr($alphaDetail, 1));
                                    }
                                }
                                ?>
                                <div class="member-row p-3 mb-3 border rounded-lg bg-white shadow-xs">
                                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                                        <h6 class="font-weight-bold text-dark mb-0">
                                            <span class="text-secondary font-weight-normal"><?= $index + 1 ?>.</span> <?= htmlspecialchars($name) ?>
                                        </h6>
                                        <!-- Status Radios -->
                                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                            <label class="btn btn-xs btn-outline-success rounded px-2 mr-1 <?= $status === 'hadir' ? 'active' : '' ?>" onclick="handleAttendanceChange('<?= $name ?>', 'hadir')">
                                                <input type="radio" name="attendance[<?= $name ?>]" value="hadir" autocomplete="off" <?= $status === 'hadir' ? 'checked' : '' ?>> Hadir
                                            </label>
                                            <label class="btn btn-xs btn-outline-warning rounded px-2 mr-1 <?= $status === 'sakit' ? 'active' : '' ?>" onclick="handleAttendanceChange('<?= $name ?>', 'sakit')">
                                                <input type="radio" name="attendance[<?= $name ?>]" value="sakit" autocomplete="off" <?= $status === 'sakit' ? 'checked' : '' ?>> Sakit
                                            </label>
                                            <label class="btn btn-xs btn-outline-info rounded px-2 mr-1 <?= $status === 'izin' ? 'active' : '' ?>" onclick="handleAttendanceChange('<?= $name ?>', 'izin')">
                                                <input type="radio" name="attendance[<?= $name ?>]" value="izin" autocomplete="off" <?= $status === 'izin' ? 'checked' : '' ?>> Izin
                                            </label>
                                            <label class="btn btn-xs btn-outline-danger rounded px-2 <?= $status === 'alpha' ? 'active' : '' ?>" onclick="handleAttendanceChange('<?= $name ?>', 'alpha')">
                                                <input type="radio" name="attendance[<?= $name ?>]" value="alpha" autocomplete="off" <?= $status === 'alpha' ? 'checked' : '' ?>> Alpha
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <!-- Container untuk status Hadir -->
                                    <div id="container-hadir-<?= $name_sanitized ?>" style="display: <?= $status === 'hadir' ? 'block' : 'none' ?>;">
                                        <div class="form-group mb-2">
                                            <label class="font-weight-bold text-secondary mb-1" style="font-size: 11px;">📝 Jurnal Kegiatan Hari Ini (Minimal 75 Karakter):</label>
                                            <textarea name="jurnal[<?= $name ?>]" data-name-sanitized="<?= $name_sanitized ?>" class="form-control form-control-sm text-journal-input" rows="4" style="resize: none; overflow-y: hidden; min-height: 90px;" placeholder="Contoh: Melakukan perbaikan jaringan komputer client yang terputus, melakukan instalasi sistem operasi Windows 11 pro, merapikan kabel LAN di rak server utama, serta membuat laporan pengerjaan harian." onblur="formatJournalInput(this)" oninput="updateCharCount(this, '<?= $name_sanitized ?>'); this.style.height = ''; this.style.height = this.scrollHeight + 'px'"><?= $status === 'hadir' ? htmlspecialchars($jurnalVal) : '' ?></textarea>
                                            <div class="d-flex justify-content-end mt-1">
                                                <small class="font-weight-bold text-xs text-danger" id="char-count-<?= $name_sanitized ?>">0/75</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Container untuk status Sakit -->
                                    <div id="container-sakit-<?= $name_sanitized ?>" style="display: <?= $status === 'sakit' ? 'block' : 'none' ?>;">
                                        <div class="form-group mb-2">
                                            <label class="font-weight-bold text-secondary mb-1" style="font-size: 11px;">🏥 Sakit apa, Tuliskan disini:</label>
                                            <textarea name="sakit_detail[<?= $name ?>]" class="form-control form-control-sm" rows="4" style="resize: none; overflow-y: hidden; min-height: 90px;" placeholder="Contoh: Demam tinggi sejak tadi malam, flu berat, kepala pusing, dan sudah berobat ke puskesmas terdekat." oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"><?= htmlspecialchars($sakitDetail) ?></textarea>
                                        </div>
                                    </div>

                                    <!-- Container untuk status Izin -->
                                    <div id="container-izin-<?= $name_sanitized ?>" style="display: <?= $status === 'izin' ? 'block' : 'none' ?>;">
                                        <div class="form-group mb-2">
                                            <label class="font-weight-bold text-secondary mb-1" style="font-size: 11px;">📋 Tuliskan Alasan Izin:</label>
                                            <textarea name="izin_detail[<?= $name ?>]" class="form-control form-control-sm" rows="4" style="resize: none; overflow-y: hidden; min-height: 90px;" placeholder="Contoh: Ada acara keluarga penting di luar kota yang tidak bisa ditinggalkan, sudah meminta izin ke pembimbing instansi." oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"><?= htmlspecialchars($izinDetail) ?></textarea>
                                        </div>
                                    </div>

                                    <!-- Container untuk status Alpha -->
                                    <div id="container-alpha-<?= $name_sanitized ?>" style="display: <?= $status === 'alpha' ? 'block' : 'none' ?>;">
                                        <div class="form-group mb-2 bg-light p-2 rounded border">
                                            <label class="font-weight-bold text-secondary mb-1 d-block" style="font-size: 11.5px;">📞 Hubungi Anggota:</label>
                                            <div class="form-check form-check-inline mb-2">
                                                <input class="form-check-input" type="radio" name="alpha_hubungi[<?= $name ?>]" id="hubungi_ya_<?= $name_sanitized ?>" value="Ya" <?= $alphaHubungi === 'Ya' ? 'checked' : '' ?>>
                                                <label class="form-check-label text-xs font-weight-bold text-success" for="hubungi_ya_<?= $name_sanitized ?>"><i class="fas fa-check-circle"></i> Sudah dihubungi oleh Ketua</label>
                                            </div>
                                            <div class="form-check form-check-inline mb-2 ml-3">
                                                <input class="form-check-input" type="radio" name="alpha_hubungi[<?= $name ?>]" id="hubungi_tidak_<?= $name_sanitized ?>" value="Tidak" <?= $alphaHubungi === 'Tidak' ? 'checked' : '' ?>>
                                                <label class="form-check-label text-xs font-weight-bold text-danger" for="hubungi_tidak_<?= $name_sanitized ?>"><i class="fas fa-times-circle"></i> Belum dihubungi</label>
                                            </div>
                                            
                                            <label class="font-weight-bold text-secondary mb-1 d-block mt-2" style="font-size: 11px;">📝 Alasan/keterangan lain (opsional):</label>
                                            <textarea name="alpha_detail[<?= $name ?>]" class="form-control form-control-sm" rows="4" style="resize: none; overflow-y: hidden; min-height: 90px;" placeholder="Contoh: Handphone tidak aktif saat dihubungi, tidak ada kabar sejak pagi, atau tidak hadir tanpa keterangan." oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"><?= htmlspecialchars($alphaDetail) ?></textarea>
                                        </div>
                                    </div>
                                    
                                    <!-- Photo Upload per Student -->
                                    <div class="form-group mb-0 mt-3 p-2 rounded" style="background: #f8fafc; border: 1px dashed #cbd5e1; display: <?= in_array($status, ['sakit', 'izin']) ? 'block' : 'none' ?>;" id="photo-container-<?= $name_sanitized ?>">
                                        <label id="photo-label-<?= $name_sanitized ?>" class="font-weight-bold text-secondary mb-1" style="font-size: 11px;">
                                            <?php if ($status === 'sakit'): ?>
                                                📸 Wajib Unggah Foto Bukti (Surat keterangan sakit atau screenshot izin orang tua ke guru/tempat PKL)
                                            <?php elseif ($status === 'izin'): ?>
                                                📸 Wajib Unggah Foto Bukti Izin (Surat izin atau screenshot persetujuan guru/tempat PKL)
                                            <?php else: ?>
                                                📸 Unggah Foto Bukti (Surat keterangan sakit / izin)
                                            <?php endif; ?>
                                        </label>
                                        
                                        <?php if (isset($todayReport) && isset($todayReport['photo_urls'][$name])): ?>
                                            <div class="mb-2 mt-1">
                                                <a href="<?= htmlspecialchars($todayReport['photo_urls'][$name]) ?>" target="_blank">
                                                    <img src="<?= htmlspecialchars($todayReport['photo_urls'][$name]) ?>" class="rounded border shadow-xs" style="width: 70px; height: 70px; object-fit: cover;">
                                                </a>
                                                <small class="text-success d-block" style="font-size: 10px;"><i class="fas fa-check-circle"></i> Sudah upload foto bukti.</small>
                                            </div>
                                        <?php endif; ?>

                                        <div class="custom-file" style="max-width: 280px;">
                                            <input type="file" class="custom-file-input" name="photo_bukti_<?= htmlspecialchars($name_sanitized) ?>" accept="image/*" onchange="updateMemberPhotoLabel(this)">
                                            <label class="custom-file-label" style="font-size: 11px; padding: 4px 8px; height: 26px; line-height: 1.5;">Pilih Foto...</label>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top text-right">
                        <?php if ($todayReport): ?>
                            <button type="button" class="btn btn-secondary mr-2 px-4 py-2 font-weight-bold rounded-lg" onclick="hideEditForm()">
                                <i class="fas fa-times mr-2"></i> Batal
                            </button>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-success px-4 py-2 font-weight-bold rounded-lg">
                            <i class="fas fa-paper-plane mr-2"></i> <?= $todayReport ? 'Simpan Perubahan' : 'Kirim Laporan PKL' ?>
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Read-Only View for Anggota PKL -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0 rounded-lg bg-white">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h3 class="card-title font-weight-bold text-dark mb-0">
                            <i class="fas fa-briefcase text-info mr-2"></i> Laporan PKL (Hanya Lihat)
                        </h3>
                        <br>
                        <small class="text-secondary font-weight-bold">🏭 DU/DI: <?= htmlspecialchars($group['tempat_pkl']) ?></small>
                    </div>
                    <div class="d-flex align-items-center bg-light rounded-pill p-1 border border-info shadow-sm">
                        <div class="bg-info text-white rounded-pill px-3 py-1 font-weight-bold text-sm">
                            <i class="fas fa-calendar-alt mr-1"></i> Laporan
                        </div>
                        <select class="form-control form-control-sm border-0 bg-transparent font-weight-bold text-dark mx-2" 
                                style="cursor: pointer; min-width: 180px; outline: none; box-shadow: none;" 
                                onchange="window.location.href='?date=' + this.value">
                            <?= $dropdownOptions ?>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($todayReport) && $todayReport['status_libur'] == 1): ?>
                        <div class="alert alert-warning text-center border-0 rounded-lg p-4 mb-0">
                            <h5 class="font-weight-bold mb-1"><i class="fas fa-store-alt-slash mr-2"></i>DU/DI Sedang Libur</h5>
                            <p class="mb-0 text-sm">Ketua kelompok telah melaporkan bahwa hari ini tempat PKL sedang libur/tutup.</p>
                            <?php if (!empty($todayReport['libur_reason'])): ?>
                                <hr class="my-2 border-warning">
                                <p class="mb-0 text-sm font-weight-bold">Alasan: "<?= htmlspecialchars($todayReport['libur_reason']) ?>"</p>
                            <?php endif; ?>
                        </div>
                    <?php elseif (!isset($todayReport)): ?>
                        <div class="alert alert-secondary text-center border-0 rounded-lg p-4 mb-0">
                            <h5 class="font-weight-bold mb-1"><i class="fas fa-clock mr-2"></i>Menunggu Laporan Ketua</h5>
                            <p class="mb-0 text-sm">Ketua kelompok Anda belum mengirimkan laporan harian PKL untuk hari ini.</p>
                        </div>
                    <?php else: ?>
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                            <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-users text-primary mr-1"></i> Kehadiran & Jurnal Harian Kelompok</h5>
                        </div>
                        <?php foreach ($members as $index => $name): ?>
                            <?php 
                            $status = $todayReport['attendance_data'][$name] ?? 'Belum diabsen';
                            $jurnalVal = $todayReport['jurnal'][$name] ?? '—';
                            $statusColors = ['hadir' => 'success', 'sakit' => 'warning', 'izin' => 'info', 'alpha' => 'danger'];
                            $statusColor = $statusColors[strtolower($status)] ?? 'secondary';
                            ?>
                            <div class="member-row p-3 mb-3 border rounded-lg bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                                    <h6 class="font-weight-bold text-dark mb-0">
                                        <span class="text-secondary font-weight-normal"><?= $index + 1 ?>.</span> <?= htmlspecialchars($name) ?>
                                    </h6>
                                    <span class="badge badge-<?= $statusColor ?> font-weight-bold px-2 py-1" style="font-size: 11px;">
                                        <?= strtoupper($status) ?>
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <span class="text-secondary font-weight-bold" style="font-size: 12px;">Jurnal:</span>
                                    <p class="text-dark mb-0 bg-white p-2 rounded border" style="font-size: 13px;">
                                        <?= htmlspecialchars($jurnalVal) ?>
                                    </p>
                                </div>
                                <?php if (isset($todayReport['photo_urls'][$name])): ?>
                                    <div class="mt-2">
                                        <span class="text-secondary d-block font-weight-bold mb-1" style="font-size: 12px;">Foto Dokumentasi:</span>
                                        <a href="<?= htmlspecialchars($todayReport['photo_urls'][$name]) ?>" target="_blank">
                                            <img src="<?= htmlspecialchars($todayReport['photo_urls'][$name]) ?>" class="rounded border" style="max-width: 150px; max-height: 150px; object-fit: cover;">
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Riwayat Laporan Kelompok -->
    <div class="col-lg-4 mb-4">
        <!-- Rekap Mingguan Card (HANYA untuk Ketua Kelompok) -->
        <?php if ($isKetua): ?>
        <div class="card shadow-sm border-0 rounded-lg bg-white mb-4">
            <div class="card-header border-bottom">
                <h3 class="card-title font-weight-bold text-dark mb-0"><i class="fas fa-calendar-week text-success mr-2"></i> Rekap Mingguan</h3>
            </div>
            <div class="card-body">
                <p class="text-secondary text-xs mb-3">Unduh atau preview gabungan laporan jurnal & absensi selama satu minggu penuh (Senin s.d Sabtu). Anggota hanya dapat melihat riwayat, tidak dapat mencetak PDF.</p>
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-secondary text-xs" for="weekly_start_date">PILIH TANGGAL (Pilih hari apa saja di minggu tersebut):</label>
                    <input type="date" id="weekly_start_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="d-flex w-100 mb-2" style="gap: 8px; display: flex;">
                    <button type="button" class="btn btn-sm btn-info font-weight-bold flex-fill" onclick="previewWeeklyReport()">
                        <i class="fas fa-eye mr-1"></i> Preview Data Mingguan
                    </button>
                </div>
                    <div class="d-flex w-100 flex-column mt-2" style="gap: 6px;">
                        <a href="<?= base_url('/pkl/riwayat/' . $group['ketua_phone']) ?>" class="btn btn-sm btn-primary font-weight-bold text-left">
                            <i class="fas fa-history mr-2"></i> Riwayat Laporan
                        </a>
                        <hr class="my-1">
                        <label class="font-weight-bold text-secondary text-xs mb-1">Cetak Rekap Absensi Individu:</label>
                        <?php foreach ($members as $member): ?>
                        <a href="<?= base_url('/pkl/rekap-siswa/' . htmlspecialchars($group['ketua_phone']) . '?name=' . urlencode(trim($member))) ?>" target="_blank" class="btn btn-sm btn-outline-success font-weight-bold text-left" style="font-size: 11px;">
                            <i class="fas fa-print mr-2"></i> <?= htmlspecialchars(trim($member)) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 rounded-lg bg-white">
            <div class="card-header border-bottom">
                <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-history text-secondary mr-2"></i> Riwayat Laporan</h3>
            </div>
            <div class="card-body p-0" style="max-height: 520px; overflow-y: auto;">
                <?php if (empty($reports)): ?>
                    <div class="p-5 text-center text-secondary">
                        <i class="fas fa-folder-open mb-2" style="font-size: 32px;"></i>
                        <p class="mb-0">Belum ada riwayat laporan terkirim.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($reports as $r): ?>
                            <div class="list-group-item p-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="font-weight-bold text-dark" style="font-size: 13.5px;">
                                        <?= date('d M Y', strtotime($r['date'])) ?>
                                    </span>
                                    <?php if ($r['status_libur']): ?>
                                        <span class="badge badge-warning text-dark font-weight-bold">LIBUR</span>
                                    <?php else: ?>
                                        <span class="badge badge-success font-weight-bold">TERKIRIM</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-secondary mb-2" style="font-size: 12.5px;">
                                    <?php if ($r['status_libur']): ?>
                                        <em>Instansi tutup / libur</em>
                                    <?php else: ?>
                                        Presensi terkirim: <?= count($r['attendance_data']) ?> siswa
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex">
                                        <?php foreach ($r['photo_urls'] as $url): ?>
                                            <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="mr-1">
                                                <img src="<?= htmlspecialchars($url) ?>" class="rounded border" style="width: 32px; height: 32px; object-fit: cover;">
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php // Per-report PDF button removed: Ketua can only print weekly rekap; Admin prints daily via admin index view ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.2/dist/browser-image-compression.js"></script>
<script>
    async function submitFormWithCompression(event) {
        event.preventDefault(); // Mencegah submit langsung
        
        // 1. Lakukan validasi data teks seperti biasa
        if (!validateStudentReportForm(event)) {
            return false;
        }

        const form = document.getElementById('studentReportForm');
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        
        try {
            // Ubah tombol jadi status loading agar siswa tidak klik 2x
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Mengompres Foto...';

            const fileInputs = form.querySelectorAll('input[type="file"]');
            
            // Konfigurasi kompresi: max 500KB, max lebar/tinggi 1280px
            const options = {
                maxSizeMB: 0.5,
                maxWidthOrHeight: 1280,
                useWebWorker: true,
                initialQuality: 0.7
            };

            // Loop semua input file gambar
            for (let i = 0; i < fileInputs.length; i++) {
                const input = fileInputs[i];
                if (input.files && input.files.length > 0) {
                    const originalFile = input.files[0];
                    
                    // Pastikan file adalah gambar
                    if (!originalFile.type.startsWith('image/')) continue;
                    
                    try {
                        console.log(`Asli (${originalFile.name}): ${(originalFile.size / 1024).toFixed(2)} KB`);
                        
                        // Proses kompresi
                        const compressedFile = await imageCompression(originalFile, options);
                        
                        // Ganti file di input dengan file hasil kompresi
                        const newFile = new File([compressedFile], originalFile.name, {
                            type: compressedFile.type,
                            lastModified: Date.now()
                        });
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(newFile);
                        input.files = dataTransfer.files;
                        
                        console.log(`Terkompresi (${originalFile.name}): ${(newFile.size / 1024).toFixed(2)} KB`);
                    } catch (error) {
                        console.error('Gagal mengompres foto:', error);
                        // Jika gagal kompresi, abaikan dan lanjut upload foto aslinya
                    }
                }
            }
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Mengirim Laporan...';
            // Submit form secara manual setelah semua kompresi selesai
            form.submit();
        } catch (error) {
            console.error('Error saat submit form:', error);
            alert('Terjadi kesalahan sistem saat mencoba mengirim laporan. Silakan coba lagi.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
        
        return false;
    }

    function setStatusLibur(status) {
        const isLibur = (status === 1);
        document.getElementById('status_libur').value = status;
        
        const btnMasuk = document.getElementById('btn-status-masuk');
        const btnLibur = document.getElementById('btn-status-libur');
        
        if (btnMasuk && btnLibur) {
            if (isLibur) {
                btnLibur.style.background = 'linear-gradient(135deg, #ef4444, #b91c1c)';
                btnLibur.style.color = 'white';
                btnLibur.style.border = 'none';
                btnLibur.style.boxShadow = '0 4px 10px rgba(239, 68, 68, 0.25)';
                
                btnMasuk.style.background = 'transparent';
                btnMasuk.style.color = '#16a34a';
                btnMasuk.style.border = '2px solid #16a34a';
                btnMasuk.style.boxShadow = 'none';
            } else {
                btnMasuk.style.background = 'linear-gradient(135deg, #16a34a, #15803d)';
                btnMasuk.style.color = 'white';
                btnMasuk.style.border = 'none';
                btnMasuk.style.boxShadow = '0 4px 10px rgba(22, 163, 74, 0.25)';
                
                btnLibur.style.background = 'transparent';
                btnLibur.style.color = '#ef4444';
                btnLibur.style.border = '2px solid #ef4444';
                btnLibur.style.boxShadow = 'none';
            }
        }
        
        // Show/hide sections
        document.getElementById('libur-reason-section').style.display = isLibur ? 'block' : 'none';
        
        const locSection = document.getElementById('presensi-location-section');
        if (locSection) {
            locSection.style.display = isLibur ? 'none' : 'block';
        }
        
        if (isLibur) {
            toggleLiburState(true);
            const warningBox = document.getElementById('location-warning-message');
            if (warningBox) warningBox.style.display = 'none';
        } else {
            checkLocationFilled();
        }
    }

    function toggleLiburState(isLibur) {
        const section = document.getElementById('attendance-section');
        if (section) {
            section.style.opacity = isLibur ? '0.35' : '1';
            section.style.pointerEvents = isLibur ? 'none' : 'auto';
        }
    }

    function checkLocationFilled() {
        const locationInput = document.getElementById('location_data');
        const statusLiburVal = document.getElementById('status_libur').value;
        const isLibur = (statusLiburVal === '1');
        
        const section = document.getElementById('attendance-section');
        const warningBox = document.getElementById('location-warning-message');
        
        if (!section) return;
        
        if (isLibur) {
            if (warningBox) warningBox.style.display = 'none';
            return;
        }
        
        const isFilled = locationInput && locationInput.value.trim().length > 0;
        
        // Target all form controls inside attendance section
        const inputs = section.querySelectorAll('input, textarea');
        inputs.forEach(inp => {
            inp.disabled = !isFilled;
        });
        
        if (isFilled) {
            section.style.opacity = '1';
            section.style.pointerEvents = 'auto';
            if (warningBox) warningBox.style.display = 'none';
        } else {
            section.style.opacity = '0.4';
            section.style.pointerEvents = 'none';
            if (warningBox) warningBox.style.display = 'block';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const locInput = document.getElementById('location_data');
        if (locInput) {
            locInput.addEventListener('input', checkLocationFilled);
            // Run check on initial load
            checkLocationFilled();
        }
    });

    function handleAttendanceChange(name, status) {
        const sanitized = name.replace(/[^a-zA-Z0-9]/g, '_');
        
        // Hide all containers
        document.getElementById('container-hadir-' + sanitized).style.display = 'none';
        document.getElementById('container-sakit-' + sanitized).style.display = 'none';
        document.getElementById('container-izin-' + sanitized).style.display = 'none';
        document.getElementById('container-alpha-' + sanitized).style.display = 'none';
        
        // Show selected container
        document.getElementById('container-' + status + '-' + sanitized).style.display = 'block';
        
        // Update photo container visibility and label
        const photoContainer = document.getElementById('photo-container-' + sanitized);
        if (photoContainer) {
            if (status === 'sakit' || status === 'izin') {
                photoContainer.style.display = 'block';
            } else {
                photoContainer.style.display = 'none';
            }
        }
        
        // Update photo label text dynamically
        const label = document.getElementById('photo-label-' + sanitized);
        if (label) {
            if (status === 'sakit') {
                label.innerHTML = '📸 Wajib Unggah Foto Bukti (Surat keterangan sakit atau screenshot izin orang tua ke guru/tempat PKL)';
            } else if (status === 'izin') {
                label.innerHTML = '📸 Wajib Unggah Foto Bukti Izin (Surat izin atau screenshot persetujuan guru/tempat PKL)';
            }
        }
    }

    function formatJournalInput(input) {
        let val = input.value.trim();
        if (val.length > 0) {
            input.value = val.charAt(0).toUpperCase() + val.slice(1);
        }
    }

    function updateMemberPhotoLabel(input) {
        const label = input.nextElementSibling;
        if (input.files.length > 0) {
            label.textContent = input.files[0].name;
        } else {
            label.textContent = 'Pilih Foto...';
        }
    }

    function validateStudentReportForm(event) {
        const statusLiburVal = document.getElementById('status_libur').value;
        const isLibur = (statusLiburVal === '1');
        
        if (isLibur) {
            const reason = document.getElementById('libur_reason').value.trim();
            if (reason.length === 0) {
                alert('Wajib mengisi alasan tempat PKL libur/tutup!');
                event.preventDefault();
                return false;
            }
            return true;
        }

        // Validate location_data if PKL is active
        const locationInput = document.getElementById('location_data');
        if (locationInput && locationInput.value.trim().length === 0) {
            alert('Wajib mengisi Lokasi Melakukan Absensi PKL!');
            event.preventDefault();
            return false;
        }

        // Validate photo_kelompok
        const fileKelompok = document.querySelector('input[name="photo_kelompok"]');
        const hasNewKelompokPhoto = fileKelompok && fileKelompok.files.length > 0;
        const existingKelompokPhotoStr = "<?= isset($todayReport['photo_urls']['kelompok']) ? 'true' : 'false' ?>";
        const hasExistingKelompokPhoto = existingKelompokPhotoStr === 'true';

        if (!hasNewKelompokPhoto && !hasExistingKelompokPhoto) {
            alert('Wajib mengunggah foto dokumentasi kelompok hari ini!');
            event.preventDefault();
            return false;
        }

        let isValid = true;
        const memberRows = document.querySelectorAll('.member-row');
        
        memberRows.forEach(row => {
            const activeRadio = row.querySelector('input[type="radio"]:checked');
            if (activeRadio) {
                const studentName = activeRadio.name.replace('attendance[', '').replace(']', '');
                const status = activeRadio.value;

                // Validate photo
                const fileInput = row.querySelector('input[type="file"]');
                const hasNewPhoto = fileInput && fileInput.files.length > 0;
                const hasExistingPhoto = row.querySelector('img') !== null;

                if (status === 'hadir') {
                    const journalInput = row.querySelector('[name^="jurnal["]');
                    const journalText = journalInput ? journalInput.value.trim() : '';
                    if (journalText.length < 75) {
                        alert('Isian jurnal kegiatan untuk ' + studentName + ' minimal harus 75 karakter! (Saat ini: ' + journalText.length + ' karakter)');
                        isValid = false;
                    }
                } else if (status === 'sakit') {
                    const sakitInput = row.querySelector('[name^="sakit_detail["]');
                    const sakitText = sakitInput ? sakitInput.value.trim() : '';
                    if (sakitText.length === 0) {
                        alert('Wajib mengisi keterangan sakit apa untuk ' + studentName + '!');
                        isValid = false;
                    }
                    if (!hasNewPhoto && !hasExistingPhoto) {
                        alert('Wajib mengunggah foto bukti (surat dokter / screenshot izin ortu) untuk ' + studentName + ' yang berstatus Sakit!');
                        isValid = false;
                    }
                } else if (status === 'izin') {
                    const izinInput = row.querySelector('[name^="izin_detail["]');
                    const izinText = izinInput ? izinInput.value.trim() : '';
                    if (izinText.length === 0) {
                        alert('Wajib mengisi alasan izin untuk ' + studentName + '!');
                        isValid = false;
                    }
                    if (!hasNewPhoto && !hasExistingPhoto) {
                        alert('Wajib mengunggah foto bukti izin (surat izin / screenshot chat persetujuan) untuk ' + studentName + ' yang berstatus Izin!');
                        isValid = false;
                    }
                }
            }
        });
        
        if (!isValid) {
            event.preventDefault();
            return false;
        }
        return true;
    }

    function updateCharCount(textarea, nameSanitized) {
        const len = textarea.value.trim().length;
        const counter = document.getElementById('char-count-' + nameSanitized);
        if (counter) {
            counter.textContent = `${len}/75`;
            if (len < 75) {
                counter.className = 'font-weight-bold text-xs text-danger';
            } else {
                counter.className = 'font-weight-bold text-xs text-success';
            }
        }
    }

    function showEditForm() {
        const previewCard = document.getElementById('today-report-preview-card');
        const formCard = document.getElementById('report-form-card');
        if (previewCard) previewCard.style.display = 'none';
        if (formCard) formCard.style.display = 'block';
    }

    function hideEditForm() {
        const previewCard = document.getElementById('today-report-preview-card');
        const formCard = document.getElementById('report-form-card');
        if (previewCard) previewCard.style.display = 'block';
        if (formCard) formCard.style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const statusLiburVal = document.getElementById('status_libur').value;
        setStatusLibur(statusLiburVal === '1' ? 1 : 0);
        
        // Auto-adjust height for all textareas on load
        document.querySelectorAll('textarea[style*="resize: none"]').forEach(el => {
            el.style.height = '';
            el.style.height = el.scrollHeight + 'px';
        });

        // Initialize character counters for journal textareas
        document.querySelectorAll('.text-journal-input').forEach(el => {
            const nameSanitized = el.getAttribute('data-name-sanitized');
            if (nameSanitized) {
                updateCharCount(el, nameSanitized);
            }
        });
        
        // Initial setup for member fields based on load state
        const memberRows = document.querySelectorAll('.member-row');
        memberRows.forEach(row => {
            const activeRadio = row.querySelector('input[type="radio"]:checked');
            if (activeRadio) {
                const studentName = activeRadio.name.replace('attendance[', '').replace(']', '');
                handleAttendanceChange(studentName, activeRadio.value);
            }
        });
    });

    const groupReports = <?= json_encode($reports) ?>;
    const groupMembers = <?= json_encode($members) ?>;
    const ketuaPhone = "<?= $group['ketua_phone'] ?>";

    function getMonday(d) {
        d = new Date(d);
        var day = d.getDay(),
            diff = d.getDate() - day + (day == 0 ? -6:1);
        return new Date(d.setDate(diff));
     }

    function formatDateStr(date) {
        let d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
    }

    function previewWeeklyReport() {
        const inputVal = document.getElementById('weekly_start_date').value;
        if (!inputVal) {
            alert('Silakan pilih tanggal terlebih dahulu!');
            return;
        }

        const monday = getMonday(inputVal);
        const dates = [];
        for (let i = 0; i < 6; i++) {
            const current = new Date(monday);
            current.setDate(monday.getDate() + i);
            dates.push(formatDateStr(current));
        }

        // Format label periode: Senin (Tanggal) - Sabtu (Tanggal)
        const opt = { day: 'numeric', month: 'short', year: 'numeric' };
        const labelPeriode = `${monday.toLocaleDateString('id-ID', opt)} - ${new Date(monday.getTime() + 5 * 24 * 60 * 60 * 1000).toLocaleDateString('id-ID', opt)}`;

        document.getElementById('weeklyModalTitle').textContent = `Preview Rekap Mingguan (${labelPeriode})`;

        let html = '';
        dates.forEach((dateStr) => {
            const report = groupReports.find(r => r.date === dateStr);
            const formattedDayStr = new Date(dateStr + 'T00:00:00').toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

            html += `<div class="card card-body shadow-xs mb-3 border bg-light">`;
            html += `<h6 class="font-weight-bold text-dark border-bottom pb-1 mb-2"><i class="far fa-calendar-alt text-primary mr-1"></i> ${formattedDayStr.toUpperCase()}</h6>`;

            if (!report) {
                html += `<div class="text-center py-3 text-secondary"><em>Laporan Hari Ini Belum Diisi / Belum Dikirim oleh Ketua Kelompok.</em></div>`;
            } else if (report.status_libur == 1) {
                html += `<div class="alert alert-warning text-center py-2 mb-0">🏢 <strong>TEMPAT PKL LIBUR / TUTUP</strong><br><small>Alasan: "${report.jurnal.keterangan || report.libur_reason || '-'}"</small></div>`;
            } else {
                html += `<div class="list-group">`;
                groupMembers.forEach((name, idx) => {
                    const status = report.attendance_data && report.attendance_data[name] ? report.attendance_data[name] : 'hadir';
                    const jurnal = report.jurnal && report.jurnal[name] ? report.jurnal[name] : '—';
                    const photo = report.photo_urls && report.photo_urls[name] ? report.photo_urls[name] : null;

                    const statusClass = { hadir: 'success', sakit: 'warning text-dark', izin: 'info', alpha: 'danger' }[status.toLowerCase()] || 'secondary';

                    html += `<div class="list-group-item p-2 mb-2 rounded bg-white border">`;
                    html += `  <div class="d-flex justify-content-between align-items-center mb-1">`;
                    html += `    <span class="font-weight-bold text-sm text-dark">${idx+1}. ${name}</span>`;
                    html += `    <span class="badge badge-${statusClass} font-weight-bold text-xs">${status.toUpperCase()}</span>`;
                    html += `  </div>`;
                    html += `  <p class="text-xs text-secondary mb-1"><strong>Jurnal:</strong> ${jurnal}</p>`;
                    if (photo) {
                        html += `  <div class="mt-1">`;
                        html += `    <a href="${photo}" target="_blank"><img src="${photo}" class="rounded border" style="width: 50px; height: 50px; object-fit: cover;"></a>`;
                        html += `  </div>`;
                    }
                    html += `</div>`;
                });
                html += `</div>`;
            }
            html += `</div>`;
        });

        document.getElementById('weeklyModalBody').innerHTML = html;
        $('#weeklyPreviewModal').modal('show');
    }

    function printWeeklyAbsensi() {
        const inputVal = document.getElementById('weekly_start_date').value;
        if (!inputVal) {
            alert('Silakan pilih tanggal terlebih dahulu!');
            return;
        }
        const monday = getMonday(inputVal);
        const mondayStr = formatDateStr(monday);
        window.open(`<?= base_url('/pkl/print-weekly-pdf/') ?>/${ketuaPhone}/${mondayStr}?type=absensi`, '_blank');
    }
</script>

<!-- Modal Preview Mingguan -->
<div class="modal fade" id="weeklyPreviewModal" tabindex="-1" role="dialog" aria-labelledby="weeklyModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title font-weight-bold text-dark" id="weeklyModalTitle">Preview Rekap Mingguan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;" id="weeklyModalBody">
                <!-- Content populated by JS -->
            </div>
            <div class="modal-footer bg-light border-top">
                <button type="button" class="btn btn-secondary font-weight-bold rounded-lg px-4" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
