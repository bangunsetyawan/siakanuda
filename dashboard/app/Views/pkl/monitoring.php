<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Monitoring PKL</h1>
            <p class="text-secondary mb-0">Layanan pencatatan kunjungan monitoring Guru Pembimbing ke lokasi DU/DI siswa.</p>
        </div>
        <div>
            <?php if (in_array($userRole, ['admin', 'kepsek', 'guru', 'guru_mapel', 'guru_bk']) && !empty($groups)): ?>
                <a href="<?= base_url('/pkl/monitoring/add') ?>" class="btn btn-primary font-weight-bold btn-sm shadow-sm" style="border-radius: 8px;">
                    <i class="fas fa-plus mr-1"></i> Tambah Kunjungan
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 10px;">
        <i class="icon fas fa-check mr-2"></i> <?= session()->getFlashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 10px;">
        <i class="icon fas fa-ban mr-2"></i> <?= session()->getFlashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Section 1: Kelompok PKL Bimbingan (Menyesuaikan Pembimbing & Tempat) -->
<?php if (in_array($userRole, ['admin', 'kepsek', 'guru', 'guru_mapel', 'guru_bk']) && !empty($groups)): ?>
    <h5 class="font-weight-bold text-dark mb-3">
        <i class="fas fa-school text-primary mr-1"></i> 
        <?= in_array($userRole, ['admin', 'kepsek']) ? 'Daftar Tempat PKL (DU/DI)' : 'Kelompok Bimbingan Saya' ?>
    </h5>
    <div class="row mb-4">
        <?php foreach ($groups as $g): ?>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; transition: transform 0.2s; cursor: default;">
                    <div class="card-body d-flex flex-column justify-content-between p-3">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge <?= $g['last_monitoring'] === 'Belum pernah' ? 'badge-secondary' : 'badge-success' ?> font-weight-bold px-2 py-1" style="font-size: 11px;">
                                    <i class="fas fa-eye mr-1"></i> <?= $g['last_monitoring'] === 'Belum pernah' ? 'Belum Dikunjungi' : 'Terakhir: ' . $g['last_monitoring'] ?>
                                </span>
                            </div>
                            <h5 class="font-weight-bold text-dark mb-1">
                                <i class="fas fa-building text-secondary mr-1" style="font-size: 16px;"></i>
                                <?= htmlspecialchars($g['tempat_pkl']) ?>
                            </h5>
                            <p class="text-secondary small mb-3" style="line-height: 1.4;">
                                <strong>Anggota:</strong><br>
                                <?= htmlspecialchars($g['anggota']) ?>
                            </p>
                        </div>
                        <div class="d-flex" style="gap: 8px;">
                            <a href="<?= base_url('/pkl/monitoring/add?group_id=' . $g['id']) ?>" class="btn btn-outline-primary btn-xs font-weight-bold flex-grow-1" 
                                    style="border-radius: 8px; padding: 6px 12px; font-size: 12px; text-align: center;">
                                <i class="fas fa-plus mr-1"></i> Input Kunjungan
                            </a>
                            <button type="button" class="btn btn-outline-info btn-xs font-weight-bold" 
                                    onclick="filterByGroup(<?= $g['id'] ?>, '<?= htmlspecialchars($g['tempat_pkl']) ?>')" 
                                    style="border-radius: 8px; padding: 6px 10px; font-size: 12px;" 
                                    title="Filter Riwayat Kunjungan">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Section 2: Riwayat Monitoring -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="font-weight-bold text-dark mb-0">
        <i class="fas fa-history text-secondary mr-1"></i> Riwayat Kunjungan Monitoring
    </h5>
    <div id="filter-indicator" style="display: none;">
        <span class="badge badge-info py-2 px-3 font-weight-bold" style="border-radius: 8px;">
            Filter DU/DI: <span id="filtered-group-name"></span>
            <a href="javascript:void(0)" onclick="clearGroupFilter()" class="text-white ml-2"><i class="fas fa-times-circle"></i></a>
        </span>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php if (empty($records)): ?>
            <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 14px;">
                <div class="card-body">
                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-briefcase text-secondary" style="font-size: 32px;"></i>
                    </div>
                    <h5 class="font-weight-bold text-dark mb-1">Belum Ada Catatan Monitoring</h5>
                    <p class="text-secondary">Riwayat kunjungan monitoring guru pembimbing belum terdaftar di sistem.</p>
                </div>
            </div>
        <?php else: ?>
            <!-- Desktop view: Table -->
            <div class="card border-0 shadow-sm" style="border-radius: 14px; overflow: hidden;" id="records-card">
                <div class="card-body p-0">
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover align-middle mb-0" id="monitoring-table">
                            <thead class="bg-light text-secondary font-weight-bold">
                                <tr>
                                    <th style="width: 60px;" class="pl-4">No</th>
                                    <th style="width: 130px;">Tanggal</th>
                                    <th>DU/DI (Tempat PKL)</th>
                                    <th>Guru Pembimbing</th>
                                    <th>Catatan Monitoring</th>
                                    <th style="width: 150px;">Dokumentasi</th>
                                    <th style="width: 120px;" class="pr-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($records as $r): ?>
                                    <tr class="mon-row" data-group-id="<?= $r['kelompok_pkl_id'] ?>">
                                        <td class="pl-4 align-middle row-no"><?= $no++ ?></td>
                                        <td class="align-middle font-weight-bold text-dark">
                                            <?= date('d M Y', strtotime($r['tanggal'])) ?>
                                        </td>
                                        <td class="align-middle">
                                            <div class="font-weight-bold text-primary"><?= htmlspecialchars($r['tempat_pkl']) ?></div>
                                            <small class="text-secondary d-block"><?= htmlspecialchars($r['anggota']) ?></small>
                                            <?php if (!empty($r['location_data'])): ?>
                                                <?php 
                                                    $locVal = $r['location_data'];
                                                    if (strpos($locVal, '| GPS:') !== false) {
                                                        $gpsRaw = trim(explode('| GPS:', $locVal)[1]);
                                                        echo '<a href="https://maps.google.com/?q='.$gpsRaw.'" target="_blank" class="text-xs text-success font-weight-bold mt-1 d-inline-block"><i class="fas fa-map-marker-alt mr-1"></i> Lihat Lokasi Map</a>';
                                                    } else {
                                                        echo '<small class="text-xs text-secondary d-block mt-1"><i class="fas fa-map-marker-alt mr-1"></i> '.htmlspecialchars($locVal).'</small>';
                                                    }
                                                ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle text-dark">
                                            <?= htmlspecialchars($r['pembimbing_name']) ?>
                                        </td>
                                        <td class="align-middle text-secondary" style="max-width: 250px;">
                                            <span class="d-inline-block text-truncate" style="max-width: 100%;">
                                                <?= htmlspecialchars($r['catatan']) ?>
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex" style="gap: 5px;">
                                                <?php if (empty($r['parsed_photo_urls'])): ?>
                                                    <span class="text-muted small">No photo</span>
                                                <?php else: ?>
                                                    <?php foreach ($r['parsed_photo_urls'] as $idx => $url): ?>
                                                        <a href="<?= $url ?>" target="_blank" class="d-inline-block">
                                                            <img src="<?= $url ?>" alt="mon-photo" class="rounded border" style="width: 35px; height: 35px; object-fit: cover;">
                                                        </a>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="pr-4 align-middle text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-info text-white font-weight-bold" 
                                                        onclick="viewDetail(<?= htmlspecialchars(json_encode($r)) ?>)" 
                                                        title="Detail Kunjungan" style="border-radius: 6px 0 0 6px;">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if (in_array($userRole, ['admin', 'kepsek']) || ($r['pembimbing_phone'] === $userPhone)): ?>
                                                    <a href="<?= base_url('/pkl/monitoring/edit/' . $r['id']) ?>" class="btn btn-warning text-white font-weight-bold" 
                                                            title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="<?= base_url('/pkl/monitoring/delete/' . $r['id']) ?>" method="post" class="d-inline-block m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan monitoring ini?');">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="btn btn-danger font-weight-bold" title="Hapus" style="border-radius: 0 6px 6px 0;">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile view: Cards list -->
                    <div class="d-block d-md-none p-3" id="monitoring-mobile-list">
                        <?php foreach ($records as $r): ?>
                            <div class="card border border-light shadow-none mb-3 mon-mobile-card" data-group-id="<?= $r['kelompok_pkl_id'] ?>" style="border-radius: 12px; background: #fafafa;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="badge badge-primary px-2 py-1" style="font-size: 11px;">
                                            <?= date('d M Y', strtotime($r['tanggal'])) ?>
                                        </div>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-default" onclick="viewDetail(<?= htmlspecialchars(json_encode($r)) ?>)">
                                                <i class="fas fa-eye text-info"></i>
                                            </button>
                                            <?php if (in_array($userRole, ['admin', 'kepsek']) || ($r['pembimbing_phone'] === $userPhone)): ?>
                                                <a href="<?= base_url('/pkl/monitoring/edit/' . $r['id']) ?>" class="btn btn-default">
                                                    <i class="fas fa-edit text-warning"></i>
                                                </a>
                                                <form action="<?= base_url('/pkl/monitoring/delete/' . $r['id']) ?>" method="post" class="d-inline-block m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan monitoring ini?');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-default">
                                                        <i class="fas fa-trash-alt text-danger"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <h6 class="font-weight-bold text-dark mb-1"><?= htmlspecialchars($r['tempat_pkl']) ?></h6>
                                    <p class="text-secondary small mb-2">
                                        <strong>Pembimbing:</strong> <?= htmlspecialchars($r['pembimbing_name']) ?><br>
                                        <strong>Anggota:</strong> <?= htmlspecialchars($r['anggota']) ?>
                                    </p>
                                    <?php if (!empty($r['location_data'])): ?>
                                        <p class="text-secondary small mb-2">
                                            <strong>Lokasi:</strong> 
                                            <?php 
                                                $locVal = $r['location_data'];
                                                if (strpos($locVal, '| GPS:') !== false) {
                                                    $gpsRaw = trim(explode('| GPS:', $locVal)[1]);
                                                    echo '<a href="https://maps.google.com/?q='.$gpsRaw.'" target="_blank" class="text-success font-weight-bold"><i class="fas fa-map-marker-alt mr-1"></i> Lihat di Peta</a>';
                                                } else {
                                                    echo htmlspecialchars($locVal);
                                                }
                                            ?>
                                        </p>
                                    <?php endif; ?>
                                    <div class="text-secondary mb-2" style="font-size: 13px; line-height: 1.4;">
                                        <?= htmlspecialchars($r['catatan']) ?>
                                    </div>
                                    <?php if (!empty($r['parsed_photo_urls'])): ?>
                                        <div class="d-flex mt-2" style="gap: 8px;">
                                            <?php foreach ($r['parsed_photo_urls'] as $url): ?>
                                                <a href="<?= $url ?>" target="_blank" class="d-inline-block">
                                                    <img src="<?= $url ?>" alt="mon-photo" class="rounded border" style="width: 50px; height: 50px; object-fit: cover;">
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Detail Monitoring -->
<div class="modal fade" id="detailMonitoringModal" tabindex="-1" aria-labelledby="detailMonitoringModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 14px;">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title font-weight-bold text-dark" id="detailMonitoringModalLabel">Detail Kunjungan Monitoring</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-0">
                <div class="mb-3">
                    <small class="text-secondary font-weight-bold">Tanggal Kunjungan</small>
                    <div class="font-weight-bold text-dark" id="detail_tanggal" style="font-size: 16px;"></div>
                </div>
                <div class="mb-3">
                    <small class="text-secondary font-weight-bold">DU/DI (Tempat PKL)</small>
                    <div class="font-weight-bold text-primary" id="detail_tempat_pkl" style="font-size: 16px;"></div>
                    <div class="text-secondary small" id="detail_anggota"></div>
                </div>
                <div class="mb-3">
                    <small class="text-secondary font-weight-bold">Guru Pembimbing</small>
                    <div class="text-dark font-weight-bold" id="detail_pembimbing"></div>
                </div>
                <div class="mb-3">
                    <small class="text-secondary font-weight-bold">Catatan Kunjungan</small>
                    <div class="text-dark p-3 bg-light rounded" id="detail_catatan" style="white-space: pre-wrap; line-height: 1.5; border-radius: 8px; font-size: 14px;"></div>
                </div>
                <div class="mb-3" id="detail_location_section" style="display: none;">
                    <small class="text-secondary font-weight-bold">📍 Lokasi Kunjungan</small>
                    <div class="text-dark font-weight-bold" id="detail_location" style="font-size: 14px;"></div>
                </div>
                <div>
                    <small class="text-secondary font-weight-bold d-block mb-2">Galeri Foto Dokumentasi</small>
                    <div id="detail_photos" class="d-flex flex-wrap" style="gap: 10px;"></div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0 pb-3">
                <button type="button" class="btn btn-secondary font-weight-bold btn-block" data-dismiss="modal" style="border-radius: 8px;">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    // JS Filter logic
    function filterByGroup(groupId, groupName) {
        let no = 1;
        document.querySelectorAll('.mon-row').forEach(row => {
            if (row.getAttribute('data-group-id') == groupId) {
                row.style.display = '';
                row.querySelector('.row-no').textContent = no++;
            } else {
                row.style.display = 'none';
            }
        });

        document.querySelectorAll('.mon-mobile-card').forEach(card => {
            if (card.getAttribute('data-group-id') == groupId) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });

        document.getElementById('filtered-group-name').textContent = groupName;
        document.getElementById('filter-indicator').style.display = 'block';
        document.getElementById('filter-indicator').scrollIntoView({ behavior: 'smooth' });
    }

    function clearGroupFilter() {
        let no = 1;
        document.querySelectorAll('.mon-row').forEach(row => {
            row.style.display = '';
            row.querySelector('.row-no').textContent = no++;
        });

        document.querySelectorAll('.mon-mobile-card').forEach(card => {
            card.style.display = '';
        });

        document.getElementById('filter-indicator').style.display = 'none';
    }

    // View Details Modal Trigger
    function viewDetail(record) {
        $('#detail_tanggal').text(formatTanggal(record.tanggal));
        $('#detail_tempat_pkl').text(record.tempat_pkl);
        $('#detail_anggota').text(record.anggota);
        $('#detail_pembimbing').text(record.pembimbing_name);
        $('#detail_catatan').text(record.catatan);

        let locSection = $('#detail_location_section');
        let locDiv = $('#detail_location');
        if (record.location_data) {
            locSection.show();
            let locVal = record.location_data;
            if (locVal.indexOf('| GPS:') !== -1) {
                let gpsRaw = locVal.split('| GPS:')[1].trim();
                locDiv.html(`<a href="https://maps.google.com/?q=${gpsRaw}" target="_blank" class="text-success"><i class="fas fa-map-marker-alt mr-1"></i> Lihat di Google Maps (Peta)</a>`);
            } else {
                locDiv.text(locVal);
            }
        } else {
            locSection.hide();
        }

        let photosContainer = $('#detail_photos');
        photosContainer.empty();
        
        if (record.parsed_photo_urls && record.parsed_photo_urls.length > 0) {
            record.parsed_photo_urls.forEach(url => {
                photosContainer.append(`
                    <a href="${url}" target="_blank" class="d-inline-block">
                        <img src="${url}" class="rounded border img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                    </a>
                `);
            });
        } else {
            photosContainer.append('<span class="text-muted small">Tidak ada dokumentasi foto.</span>');
        }

        $('#detailMonitoringModal').modal('show');
    }

    function formatTanggal(dateStr) {
        let options = { day: 'numeric', month: 'short', year: 'numeric' };
        return new Date(dateStr).toLocaleDateString('id-ID', options);
    }
</script>
<?= $this->endSection() ?>

