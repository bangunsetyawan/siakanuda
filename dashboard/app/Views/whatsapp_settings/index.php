<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid">
        <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Pengaturan Bot WhatsApp</h1>
        <p class="text-secondary mb-0">Kelola koneksi bot, edit template pesan, atur jadwal tugas otomatis (cron), kelola whitelist nomor, dan tentukan target grup WA.</p>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-2"></i> <?= session()->getFlashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i> <?= session()->getFlashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Kolom Atas: Pengaturan & Editor -->
    <div class="col-lg-12 mb-4">
        <!-- Card: Tab Navigasi -->
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header p-2 border-bottom">
                <ul class="nav nav-pills" id="settings-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold" id="templates-tab" data-toggle="pill" href="#tab-templates" role="tab" aria-selected="true">
                            <i class="fas fa-envelope mr-1"></i> Template Pesan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" id="cron-tab" data-toggle="pill" href="#tab-cron" role="tab" aria-selected="false">
                            <i class="fas fa-clock mr-1"></i> Tugas Otomatis (Cron)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" id="whitelist-tab" data-toggle="pill" href="#tab-whitelist" role="tab" aria-selected="false">
                            <i class="fas fa-user-shield mr-1"></i> Whitelist Nomor WA
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" id="groups-tab" data-toggle="pill" href="#tab-groups" role="tab" aria-selected="false">
                            <i class="fas fa-users-cog mr-1"></i> Grup WhatsApp
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="card-body">
                <div class="tab-content" id="settings-tabContent">
                    <!-- TAB 1: Editor Template -->
                    <div class="tab-pane fade show active" id="tab-templates" role="tabpanel">
                        <form action="<?= base_url('whatsapp-settings/update-templates') ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="accordion" id="accordionTemplates">
                                <?php foreach ($templates as $index => $tpl): ?>
                                    <div class="card mb-3 border rounded shadow-none">
                                        <div class="card-header bg-light border-bottom p-2 d-flex justify-content-between align-items-center" id="heading-<?= $tpl['key'] ?>" style="cursor: pointer;" data-toggle="collapse" data-target="#collapse-<?= $tpl['key'] ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 32px; height: 32px; font-size: 14px;">
                                                    <i class="fas fa-file-alt"></i>
                                                </div>
                                                <div>
                                                    <h5 class="mb-0 font-weight-bold text-dark" style="font-size: 15px;"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $tpl['key']))) ?></h5>
                                                    <small class="text-muted"><?= htmlspecialchars($tpl['description']) ?></small>
                                                </div>
                                            </div>
                                            <i class="fas fa-chevron-down text-secondary transition-all"></i>
                                        </div>

                                        <div id="collapse-<?= $tpl['key'] ?>" class="collapse <?= $index === 0 ? 'show' : '' ?>" data-parent="#accordionTemplates">
                                            <div class="card-body bg-white border-top">
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-secondary mb-1">Isi Template Pesan:</label>
                                                    <textarea 
                                                        name="templates[<?= $tpl['key'] ?>]" 
                                                        class="form-control font-mono text-secondary tpl-textarea" 
                                                        rows="8" 
                                                        data-key="<?= $tpl['key'] ?>"
                                                        style="font-family: 'Courier New', Courier, monospace; font-size: 13px; line-height: 1.5;"
                                                        required
                                                    ><?= htmlspecialchars($tpl['body']) ?></textarea>
                                                    
                                                    <!-- Character Counter -->
                                                    <div class="text-right">
                                                        <small class="text-muted" id="counter-<?= $tpl['key'] ?>">0 karakter</small>
                                                    </div>
                                                </div>

                                                <!-- Variable Placeholders -->
                                                <div class="p-3 bg-light rounded border">
                                                    <h6 class="font-weight-bold text-dark mb-2" style="font-size: 13px;"><i class="fas fa-code mr-1 text-primary"></i> Variabel yang Tersedia:</h6>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <?php 
                                                            $vars = explode(',', $tpl['variables']);
                                                            foreach ($vars as $v): $v = trim($v);
                                                        ?>
                                                            <span class="badge badge-secondary px-2 py-1 mr-2 mb-2 bg-dark text-white cursor-pointer select-var-badge" title="Klik untuk menyalin" data-var="{<?= $v ?>}">
                                                                <code>{<?= $v ?>}</code>
                                                            </span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <small class="text-muted d-block mt-1"><i class="fas fa-info-circle mr-1"></i> Klik tag variabel di atas untuk menyisipkannya ke posisi kursor teks.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="text-right mt-4 pt-3 border-top">
                                <button type="submit" class="btn btn-success font-weight-bold px-4">
                                    <i class="fas fa-save mr-1"></i> Simpan Semua Template
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 2: Dynamic Cron Configurations -->
                    <div class="tab-pane fade" id="tab-cron" role="tabpanel">
                        <form action="<?= base_url('whatsapp-settings/update-cron') ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover bg-white mb-0 shadow-none">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 60px;" class="text-center">Aktif</th>
                                            <th>Nama Tugas (Cron Job)</th>
                                            <th style="width: 180px;">Jadwal (Cron Expression)</th>
                                            <th>Aksi & Kaitan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cronConfigs as $cron): ?>
                                            <tr>
                                                <td class="text-center align-middle">
                                                    <!-- Switch/Toggle -->
                                                    <div class="custom-control custom-switch">
                                                        <input 
                                                            type="checkbox" 
                                                            name="cron[<?= $cron['key'] ?>][is_active]" 
                                                            class="custom-control-input" 
                                                            id="switch-<?= $cron['key'] ?>"
                                                            <?= $cron['is_active'] ? 'checked' : '' ?>
                                                        >
                                                        <label class="custom-control-label" for="switch-<?= $cron['key'] ?>"></label>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="font-weight-bold text-dark"><?= htmlspecialchars($cron['name']) ?></span>
                                                    <small class="d-block text-muted">Key: <code><?= htmlspecialchars($cron['key']) ?></code></small>
                                                    <small class="d-block text-secondary mt-1" style="font-size: 12px;"><?= htmlspecialchars($cron['description']) ?></small>
                                                </td>
                                                <td class="align-middle">
                                                    <input 
                                                        type="text" 
                                                        name="cron[<?= $cron['key'] ?>][cron_expression]" 
                                                        class="form-control text-center font-weight-bold text-secondary font-mono" 
                                                        value="<?= htmlspecialchars($cron['cron_expression']) ?>"
                                                        required
                                                    >
                                                    <small class="d-block text-center text-muted mt-1" style="font-size: 11px;">(Menit Jam Hari Bulan Pekan)</small>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="badge badge-light border text-secondary mb-1">
                                                        Aksi: <code><?= htmlspecialchars($cron['action'] ?? $cron['key']) ?></code>
                                                    </span>
                                                    
                                                    <!-- Delete Button for custom crons -->
                                                    <?php if (!in_array($cron['key'], ['class_attendance_check', 'pkl_report_check', 'pkl_escalation_check', 'auto_alpha_job'])): ?>
                                                        <form action="<?= base_url('whatsapp-settings/delete-cron/' . $cron['key']) ?>" method="post" class="d-inline ml-2" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas cron kustom ini?')">
                                                            <?= csrf_field() ?>
                                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Hapus Tugas Cron">
                                                                <i class="fas fa-trash"></i> Hapus
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="alert alert-info mt-4">
                                <h5><i class="icon fas fa-info"></i> Petunjuk Ekspresi Cron:</h5>
                                <ul class="mb-0 pl-4">
                                    <li><code>30 8 * * *</code> = Dijalankan setiap hari pada pukul **08:30 WIB**</li>
                                    <li><code>0 16 * * *</code> = Dijalankan setiap hari pada pukul **16:00 WIB**</li>
                                    <li><code>0 19 * * *</code> = Dijalankan setiap hari pada pukul **19:00 WIB**</li>
                                    <li><code>*/15 * * * *</code> = Dijalankan setiap **15 menit sekali**</li>
                                </ul>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                <button type="button" class="btn btn-outline-primary font-weight-bold" data-toggle="modal" data-target="#modal-add-cron">
                                    <i class="fas fa-plus-circle mr-1"></i> Tambah Jadwal Baru
                                </button>
                                <button type="submit" class="btn btn-success font-weight-bold px-4">
                                    <i class="fas fa-save mr-1"></i> Simpan & Terapkan Jadwal
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 3: Whitelist Nomor WA -->
                    <div class="tab-pane fade" id="tab-whitelist" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                            <div class="mb-2">
                                <input type="text" id="whitelistSearch" class="form-control form-control-sm" placeholder="🔍 Cari Nama / No WA / Tugas...">
                            </div>
                            <div class="mb-2">
                                <button class="btn btn-sm btn-outline-success mr-2 font-weight-bold" data-toggle="modal" data-target="#modal-import-teacher">
                                    <i class="fas fa-file-excel mr-1"></i> Import Excel
                                </button>
                                <button class="btn btn-sm btn-primary font-weight-bold" data-toggle="modal" data-target="#modal-add-teacher">
                                    <i class="fas fa-plus-circle mr-1"></i> Daftarkan Nomor
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive border rounded bg-white">
                            <table class="table table-hover mb-0" id="whitelistTable" style="font-size: 13px;">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Nama Lengkap</th>
                                        <th>Nomor WhatsApp</th>
                                        <th>Tugas Tambahan</th>
                                        <th>Peran Akses</th>
                                        <th style="width: 150px;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($whitelist)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-secondary py-5">
                                                <i class="fas fa-user-lock mb-2" style="font-size: 32px;"></i>
                                                <p class="mb-0">Belum ada nomor WA yang didaftarkan ke whitelist.</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php $i = 1; foreach ($whitelist as $t): ?>
                                            <tr class="whitelist-row">
                                                <td class="align-middle"><?= $i++ ?></td>
                                                <td class="align-middle font-weight-bold text-dark search-name"><?= htmlspecialchars($t['name']) ?></td>
                                                <td class="align-middle search-phone"><code class="text-dark font-weight-bold">+<?= htmlspecialchars($t['phone']) ?></code></td>
                                                <td class="align-middle search-tugas">
                                                    <?php if (!empty($t['tugas_tambahan'])): ?>
                                                        <span class="badge badge-light border text-secondary px-2 py-1"><?= htmlspecialchars($t['tugas_tambahan']) ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="align-middle">
                                                    <?php 
                                                        $roleBadge = 'badge-secondary';
                                                        $roleName = 'Guru';
                                                        if ($t['role'] === 'admin') { $roleBadge = 'badge-danger'; $roleName = '👑 Super Admin'; }
                                                        elseif ($t['role'] === 'kepsek') { $roleBadge = 'badge-warning text-dark'; $roleName = '🏫 Kepala Sekolah'; }
                                                        elseif ($t['role'] === 'guru_bk') { $roleBadge = 'badge-success'; $roleName = '📚 Guru BK'; }
                                                        elseif ($t['role'] === 'guru') { $roleBadge = 'badge-primary'; $roleName = '🎯 Guru Pendidik'; }
                                                        elseif ($t['role'] === 'guru_mapel') { $roleBadge = 'badge-info'; $roleName = '🎯 Guru Mapel'; }
                                                    ?>
                                                    <span class="badge <?= $roleBadge ?> px-2 py-1 font-weight-bold"><?= $roleName ?></span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <div class="btn-group">
                                                        <button class="btn btn-xs btn-outline-primary mr-1 rounded" 
                                                                data-toggle="modal" 
                                                                data-target="#modal-edit-teacher-<?= $t['id'] ?>">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                        <form action="<?= base_url('/allowed-numbers/delete/' . $t['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan hak akses nomor guru ini?')">
                                                            <?= csrf_field() ?>
                                                            <button type="submit" class="btn btn-xs btn-outline-danger rounded">
                                                                <i class="fas fa-trash"></i> Cabut
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Edit Teacher Modal inside loop -->
                                            <div class="modal fade" id="modal-edit-teacher-<?= $t['id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                    <div class="modal-content border-0 shadow rounded-lg">
                                                        <div class="modal-header border-bottom">
                                                            <h5 class="modal-title font-weight-bold text-dark">Edit Data Whitelist</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <form action="<?= base_url('/allowed-numbers/update/' . $t['id']) ?>" method="post">
                                                            <?= csrf_field() ?>
                                                            <div class="modal-body">
                                                                <div class="form-group mb-3 text-left">
                                                                    <label class="font-weight-bold text-secondary">Nama Lengkap</label>
                                                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($t['name']) ?>" required>
                                                                </div>
                                                                <div class="form-group mb-3 text-left">
                                                                    <label class="font-weight-bold text-secondary">Nomor WhatsApp</label>
                                                                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($t['phone']) ?>" placeholder="Contoh: 628xxxxx" required>
                                                                    <small class="text-muted">Format internasional tanpa simbol (contoh: 6285334xxxx).</small>
                                                                </div>
                                                                <div class="form-group mb-3 text-left">
                                                                    <label class="font-weight-bold text-secondary">Tugas Tambahan (Opsional)</label>
                                                                    <input type="text" name="tugas_tambahan" class="form-control" value="<?= htmlspecialchars($t['tugas_tambahan'] ?? '') ?>" placeholder="Contoh: Wali Kelas, Wakakur">
                                                                </div>
                                                                <div class="form-group mb-3 text-left">
                                                                    <label class="font-weight-bold text-secondary">Tingkat Akses (Role)</label>
                                                                    <select name="role" class="form-control" required>
                                                                        <option value="guru" <?= $t['role'] == 'guru' ? 'selected' : '' ?>>🎯 Guru Pendidik</option>
                                                                        <option value="guru_mapel" <?= $t['role'] == 'guru_mapel' ? 'selected' : '' ?>>🎯 Guru Mata Pelajaran</option>
                                                                        <option value="guru_bk" <?= $t['role'] == 'guru_bk' ? 'selected' : '' ?>>📚 Guru BK / Konselor</option>
                                                                        <option value="kepsek" <?= $t['role'] == 'kepsek' ? 'selected' : '' ?>>🏫 Kepala Sekolah</option>
                                                                        <option value="admin" <?= $t['role'] == 'admin' ? 'selected' : '' ?>>👑 Super Admin</option>
                                                                    </select>
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

                    <!-- TAB 4: Grup WhatsApp -->
                    <div class="tab-pane fade" id="tab-groups" role="tabpanel">
                        <form action="<?= base_url('whatsapp-settings/update-groups') ?>" method="post" class="mb-4">
                            <?= csrf_field() ?>
                            <h5 class="font-weight-bold text-dark mb-3"><i class="fas fa-sliders-h mr-1 text-primary"></i> Konfigurasi Target Grup WA</h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="font-weight-bold text-secondary mb-1">JID Grup Broadcast Laporan PKL:</label>
                                    <input 
                                        type="text" 
                                        name="broadcast_group_jid" 
                                        id="inputBroadcastJid"
                                        class="form-control font-mono" 
                                        placeholder="Contoh: 120363248@g.us"
                                        value="<?= htmlspecialchars($broadcastGroupJid) ?>"
                                    >
                                    <small class="text-muted">(Tempat dikirimkannya rangkuman laporan jurnal PKL kelompok harian)</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="font-weight-bold text-secondary mb-1">JID Grup Peringatan Absensi KBM:</label>
                                    <input 
                                        type="text" 
                                        name="school_group_jid" 
                                        id="inputSchoolJid"
                                        class="form-control font-mono" 
                                        placeholder="Contoh: 120363999@g.us"
                                        value="<?= htmlspecialchars($schoolGroupJid) ?>"
                                    >
                                    <small class="text-muted">(Tempat dikirimkannya info warning kelas belum absen pada jam 08:30 WIB)</small>
                                </div>
                            </div>
                            
                            <div class="text-right">
                                <button type="submit" class="btn btn-success font-weight-bold">
                                    <i class="fas fa-save mr-1"></i> Simpan Target JID
                                </button>
                            </div>
                        </form>

                        <div class="border-top pt-4">
                            <!-- Link Group Join Form -->
                            <div class="card card-body bg-light mb-4 border shadow-none">
                                <h6 class="font-weight-bold text-dark mb-2">
                                    <i class="fas fa-link mr-1 text-primary"></i> Hubungkan Grup Baru via Link Undangan
                                </h6>
                                <p class="text-secondary mb-3" style="font-size: 13px;">
                                    Masukkan link undangan grup WhatsApp (contoh: <code>https://chat.whatsapp.com/CWAXdVXIHeP7d7FkOv9ZF3</code>). Bot akan otomatis masuk dan membaca JID (ID Obrolan) grup tersebut.
                                </p>
                                <div class="input-group">
                                    <input type="text" id="invite-link-input" class="form-control" placeholder="https://chat.whatsapp.com/...">
                                    <div class="input-group-append">
                                        <button type="button" id="btnJoinGroupLink" class="btn btn-primary font-weight-bold" style="height: 42px;">
                                            <i class="fas fa-plus mr-1"></i> Hubungkan & Gabung Grup
                                        </button>
                                    </div>
                                </div>
                                <div id="join-group-alert" class="mt-2 d-none"></div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-list-alt mr-1 text-success"></i> Grup WhatsApp yang Diikuti Bot</h5>
                                <button type="button" class="btn btn-xs btn-outline-secondary font-weight-bold" id="btnRefreshGroups">
                                    <i class="fas fa-sync fa-spin mr-1"></i> Reload Live
                                </button>
                            </div>
                            
                            <div class="alert alert-secondary" id="groups-info-box">
                                <i class="fas fa-info-circle mr-1"></i> Memuat daftar grup live dari perangkat WhatsApp terhubung...
                            </div>

                            <div class="table-responsive border rounded bg-white d-none" id="groups-table-container">
                                <table class="table table-hover mb-0" style="font-size: 13px;">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Nama Grup</th>
                                            <th>Group JID (ID Obrolan)</th>
                                            <th style="width: 320px;" class="text-center">Tetapkan Sebagai Target</th>
                                        </tr>
                                    </thead>
                                    <tbody id="groups-table-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Kolom Bawah: Status Koneksi & QR Code (WebSockets) -->
    <div class="col-lg-6 col-md-8 mx-auto mb-4">
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header border-bottom">
                <h3 class="card-title font-weight-bold text-dark">
                    <i class="fab fa-whatsapp text-success mr-2"></i> Koneksi Perangkat Bot
                </h3>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center text-center py-5">
                <!-- Status Badge -->
                <div class="mb-4">
                    <span id="wa-status-badge" class="badge badge-pill <?= $botStatus['connected'] ? 'badge-success' : 'badge-danger' ?> px-3 py-2 font-weight-bold" style="font-size: 14px; letter-spacing: 0.5px; transition: all 0.3s ease;">
                        <i id="wa-status-icon" class="fas <?= $botStatus['connected'] ? 'fa-check-circle' : 'fa-times-circle' ?> mr-1"></i>
                        <span id="wa-status-text"><?= $botStatus['connected'] ? 'TERHUBUNG' : 'TIDAK TERHUBUNG' ?></span>
                    </span>
                </div>

                <!-- QR Code Container -->
                <div id="qr-container" class="border rounded p-3 bg-light shadow-inner mb-4 d-flex align-items-center justify-content-center" style="width: 250px; height: 250px; position: relative; transition: all 0.3s ease;">
                    <?php if ($botStatus['connected']): ?>
                        <div class="text-success text-center">
                            <i class="fas fa-circle-check fa-4x mb-3 animate__animated animate__bounceIn"></i>
                            <p class="font-weight-bold mb-0">Bot Aktif</p>
                            <small class="text-muted">Siap menerima perintah</small>
                        </div>
                    <?php else: ?>
                        <div id="qr-loading" class="text-secondary text-center">
                            <i class="fas fa-spinner fa-spin fa-3x mb-3"></i>
                            <p class="font-weight-bold mb-0" style="font-size: 14px;">Menghubungkan...</p>
                            <small class="text-muted" style="font-size: 12px;">Membuka sesi bot</small>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Device Info -->
                <div class="w-100 px-3 text-left mb-4">
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-secondary">Nama Perangkat</span>
                        <span class="font-weight-bold text-dark" id="wa-device-name"><?= htmlspecialchars($botStatus['name'] ?: '—') ?></span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-secondary">Nomor WhatsApp</span>
                        <span class="font-weight-bold text-dark" id="wa-device-phone"><?= $botStatus['phone'] ? '+' . explode(':', $botStatus['phone'])[0] : '—' ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-secondary">Versi Layanan</span>
                        <span class="font-weight-bold text-dark"><?= htmlspecialchars($botStatus['version']) ?></span>
                    </div>
                </div>

                <!-- Logout Form -->
                <?php if ($botStatus['connected']): ?>
                    <form action="<?= base_url('whatsapp-settings/logout') ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin memutus koneksi nomor WhatsApp saat ini? Anda harus memindai QR code baru untuk menyambungkan kembali.');" class="w-100">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-danger btn-block font-weight-bold px-4" id="btn-wa-logout">
                            <i class="fas fa-sign-out-alt mr-1"></i> Putus Koneksi / Ganti Nomor
                        </button>
                    </form>
                <?php else: ?>
                    <div id="qr-hint-text" class="text-muted" style="font-size: 12px; max-width: 280px; display: none;">
                        <i class="fas fa-info-circle text-info mr-1"></i> Buka WhatsApp di HP Anda -> Menu (Titik 3) / Setelan -> Perangkat Tertaut -> Tautkan Perangkat, lalu arahkan kamera ke kode QR di atas.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Cron Modal -->
<div class="modal fade" id="modal-add-cron" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow rounded-lg">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark">Tambah Jadwal Tugas (Cron Job) Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('whatsapp-settings/create-cron') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Key Unik (ID Tugas)</label>
                        <input type="text" name="key" class="form-control font-mono" placeholder="Contoh: pkl_pengingat_siang" required>
                        <small class="text-muted">Gunakan huruf kecil, angka, dan underscore (tanpa spasi/simbol).</small>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Nama Tugas</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Pengingat PKL Tambahan" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Ekspresi Waktu (Cron Expression)</label>
                        <input type="text" name="cron_expression" class="form-control text-center font-weight-bold font-mono" placeholder="30 13 * * *" required>
                        <small class="text-muted d-block mt-1">Format: <code>Menit Jam Hari Bulan Pekan</code> (contoh: <code>0 13 * * *</code> jam 13:00 WIB).</small>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Aksi Sistem (Runner)</label>
                        <select name="action" class="form-control" required>
                            <option value="class_attendance_check">Pengecekan Absensi Kelas KBM (Early Warning)</option>
                            <option value="pkl_report_check">Pengingat Jurnal PKL Harian (Ketua Kelompok)</option>
                            <option value="pkl_escalation_check">Eskalasi Peringatan Laporan PKL (Guru Pembimbing)</option>
                            <option value="auto_alpha_job">Tutup Buku & Auto-Alpha Harian</option>
                        </select>
                        <small class="text-muted">Fungsi otomatisasi sistem yang akan dieksekusi berdasarkan jadwal kustom ini.</small>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Deskripsi Tugas</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Jelaskan tujuan tugas cron ini..."></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="is_active" class="custom-control-input" id="add-switch-active" checked>
                            <label class="custom-control-label font-weight-bold text-secondary" for="add-switch-active">Langsung Aktifkan Jadwal</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Tugas Cron</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Whitelist Modal -->
<div class="modal fade" id="modal-add-teacher" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow rounded-lg">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark">Daftarkan Guru / Staf Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('/allowed-numbers/create') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" placeholder="Bpk/Ibu Nama Lengkap" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Nomor WhatsApp</label>
                        <input type="text" name="phone" class="form-control" placeholder="Contoh: 62852xxxxxxxx" required>
                        <small class="text-muted">Gunakan format 628xxx (tanpa spasi atau +).</small>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Tugas Tambahan (Opsional)</label>
                        <input type="text" name="tugas_tambahan" class="form-control" placeholder="Contoh: Wali Kelas, Wakakur">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Tingkat Akses (Role)</label>
                        <select name="role" class="form-control" required>
                            <option value="guru" selected>🎯 Guru Pendidik</option>
                            <option value="guru_mapel">🎯 Guru Mata Pelajaran</option>
                            <option value="guru_bk">📚 Guru BK / Konselor</option>
                            <option value="kepsek">🏫 Kepala Sekolah</option>
                            <option value="admin">👑 Super Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Whitelist</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Whitelist Modal -->
<div class="modal fade" id="modal-import-teacher" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow rounded-lg">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-file-excel mr-2 text-success"></i> Import Data Whitelist via Excel</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2" style="font-size: 13px;">
                    <h6 class="font-weight-bold mb-1"><i class="fas fa-info-circle mr-1"></i>Format Kolom Excel:</h6>
                    Pastikan file Excel Anda (`.xlsx`, `.xls`, atau `.csv`) memiliki baris header dengan kolom berikut:
                    <div class="font-weight-bold mt-1 text-monospace text-center">
                        [nama] &nbsp;|&nbsp; [whatsapp] &nbsp;|&nbsp; [tugas tambahan] &nbsp;|&nbsp; [role]
                    </div>
                    <ul class="mb-0 pl-3 mt-1">
                        <li><strong>whatsapp</strong>: Nomor WhatsApp (misal: <code>628123456789</code>).</li>
                        <li><strong>role</strong>: <code>admin</code>, <code>kepsek</code>, <code>guru</code>, <code>guru_bk</code>, <code>guru_mapel</code>. (Default: <code>guru_mapel</code>).</li>
                    </ul>
                    <div class="mt-2 text-right">
                        <a href="<?= base_url('/templates/template_guru.xlsx') ?>" class="btn btn-xs btn-success text-white font-weight-bold" download>
                            <i class="fas fa-download mr-1"></i> Download Template Excel
                        </a>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="font-weight-bold text-dark">Pilih File Excel / CSV</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="teacherExcelFile" accept=".xlsx, .xls, .csv">
                        <label class="custom-file-label" for="teacherExcelFile">Pilih file...</label>
                    </div>
                </div>

                <!-- Preview Area -->
                <div id="teacherPreviewContainer" class="d-none">
                    <h6 class="font-weight-bold text-dark mb-2"><i class="fas fa-eye mr-1"></i> Preview Data (Maks. 10 baris)</h6>
                    <div class="table-responsive border rounded mb-3" style="max-height: 250px;">
                        <table class="table table-sm table-striped mb-0" style="font-size: 12px;">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th>Nama</th>
                                    <th>No. WhatsApp</th>
                                    <th>Tugas Tambahan</th>
                                    <th>Role (Tingkat Akses)</th>
                                </tr>
                            </thead>
                            <tbody id="teacherPreviewBody"></tbody>
                        </table>
                    </div>
                    <div class="text-right text-secondary mb-3" style="font-size: 11px;">
                        Total data terdeteksi: <strong id="teacherTotalRowsText" class="text-dark">0</strong> baris
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnSubmitTeacherImport" disabled>
                    <i class="fas fa-upload mr-1"></i> Simpan Data Import
                </button>
            </div>
        </div>
    </div>
</div>

<!-- SheetJS Library -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<!-- Socket.io Real-Time Integration Script -->
<script src="/socket.io/socket.io.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // --- 1. WebSocket Live Connection Handler ---
        // Di production (via Nginx), gunakan origin sendiri agar Socket.io & API melewati proxy Nginx.
        // Di localhost, tetap langsung ke port 7860.
        const isLocal = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
        const waBotUrl = isLocal ? "<?= $waBotUrl ?>" : window.location.origin;
        const badge = document.getElementById("wa-status-badge");
        const icon = document.getElementById("wa-status-icon");
        const statusText = document.getElementById("wa-status-text");
        const qrContainer = document.getElementById("qr-container");
        const devicePhone = document.getElementById("wa-device-phone");
        const deviceName = document.getElementById("wa-device-name");
        const qrHint = document.getElementById("qr-hint-text");

        console.log("[SOCKET] Connecting to WA Bot background service at:", waBotUrl);
        const socket = io(waBotUrl, {
            reconnectionAttempts: 10,
            timeout: 5000,
            path: '/socket.io/'
        });

        // Event: Connected to Socket.io Server
        socket.on("connect", () => {
            console.log("[SOCKET] Connected to WebSocket channel.");
        });

        // Event: Bot QR Code Received
        socket.on("bot:qr", (qrDataUrl) => {
            console.log("[SOCKET] WhatsApp QR Code received.");
            if (qrDataUrl) {
                qrContainer.innerHTML = `<img src="${qrDataUrl}" alt="Scan QR Code WA" class="img-fluid animate__animated animate__fadeIn" style="max-width: 100%; height: auto; border: 2px solid #ddd; padding: 4px; background: white;">`;
                if (qrHint) qrHint.style.display = "block";
            } else {
                qrContainer.innerHTML = `
                    <div id="qr-loading" class="text-secondary text-center">
                        <i class="fas fa-spinner fa-spin fa-3x mb-3"></i>
                        <p class="font-weight-bold mb-0">Menunggu QR Code...</p>
                    </div>`;
                if (qrHint) qrHint.style.display = "none";
            }
        });

        socket.on("disconnect", () => {
            console.log("[SOCKET] Connection lost from bot server.");
            // Set Badge Status to offline if not already
            badge.className = "badge badge-pill badge-danger px-3 py-2 font-weight-bold";
            icon.className = "fas fa-times-circle mr-1";
            statusText.textContent = "TIDAK TERHUBUNG";
            devicePhone.textContent = "—";
            deviceName.textContent = "—";
        });

        // Event: Bot connected to WhatsApp successfully
        socket.on("bot:status", (data) => {
            console.log("[SOCKET] WhatsApp connection status update:", data);
            
            if (data.connected) {
                badge.className = "badge badge-pill badge-success px-3 py-2 font-weight-bold";
                icon.className = "fas fa-check-circle mr-1";
                statusText.textContent = "TERHUBUNG";
                
                if (data.phone) {
                    const cleanPhone = data.phone.split(':')[0];
                    devicePhone.textContent = "+" + cleanPhone;
                }
                if (data.name) {
                    deviceName.textContent = data.name;
                }
                
                qrContainer.innerHTML = `
                    <div class="text-success text-center animate__animated animate__zoomIn">
                        <i class="fas fa-check-circle fa-4x mb-3"></i>
                        <p class="font-weight-bold mb-0">Bot Aktif</p>
                        <small class="text-muted">WhatsApp Terkoneksi</small>
                    </div>`;
                if (qrHint) qrHint.style.display = "none";

                // Reload page after a short delay if it was just pairing to refresh JID loading
                if (window.location.search !== "?active=1" && !window.location.search.includes("tab=")) {
                    setTimeout(() => {
                        window.location.href = window.location.pathname + "?active=1";
                    }, 1500);
                }
            } else {
                badge.className = "badge badge-pill badge-danger px-3 py-2 font-weight-bold";
                icon.className = "fas fa-times-circle mr-1";
                statusText.textContent = "TIDAK TERHUBUNG";
                devicePhone.textContent = "—";
                deviceName.textContent = "—";
                
                if (data.reason === 'loggedOut') {
                    qrContainer.innerHTML = `
                        <div class="text-secondary text-center">
                            <i class="fas fa-qrcode fa-4x mb-3 text-muted"></i>
                            <p class="font-weight-bold mb-0">Menunggu QR Code...</p>
                        </div>`;
                } else {
                    qrContainer.innerHTML = `
                        <div class="text-secondary text-center">
                            <i class="fas fa-link-slash fa-4x mb-3 text-muted"></i>
                            <p class="font-weight-bold mb-0">Offline</p>
                            <small class="text-muted">Koneksi terputus</small>
                        </div>`;
                }
                if (qrHint) qrHint.style.display = "none";
            }
        });

        // --- 2. Interaksi Textarea & Variable Click-to-insert ---
        document.querySelectorAll(".tpl-textarea").forEach((textarea) => {
            const key = textarea.getAttribute("data-key");
            const counter = document.getElementById("counter-" + key);
            
            // Initial count
            if (counter) counter.textContent = textarea.value.length + " karakter";

            textarea.addEventListener("input", function () {
                if (counter) counter.textContent = this.value.length + " karakter";
            });
        });

        // Click on variable badge to insert at cursor position
        document.querySelectorAll(".select-var-badge").forEach((badge) => {
            badge.addEventListener("click", function () {
                const varText = this.getAttribute("data-var");
                const collapse = this.closest(".collapse");
                const textarea = collapse.querySelector(".tpl-textarea");
                
                if (textarea) {
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    const text = textarea.value;
                    const before = text.substring(0, start);
                    const after = text.substring(end, text.length);
                    
                    textarea.value = before + varText + after;
                    textarea.focus();
                    textarea.selectionStart = textarea.selectionEnd = start + varText.length;
                    
                    // Trigger input event to update character count
                    textarea.dispatchEvent(new Event("input"));
                }
            });
        });

        // --- 3. URL Parameter Tab Controller ---
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab');
        if (activeTab) {
            document.querySelectorAll('#settings-tab .nav-link').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('#settings-tabContent .tab-pane').forEach(el => el.classList.remove('show', 'active'));

            const targetLink = document.getElementById(activeTab + '-tab');
            const targetPane = document.getElementById('tab-' + activeTab);
            if (targetLink && targetPane) {
                targetLink.classList.add('active');
                targetPane.classList.add('show', 'active');
            }
        }

        // --- 4. Whitelist Search Filter ---
        const searchInput = document.getElementById("whitelistSearch");
        if (searchInput) {
            searchInput.addEventListener("keyup", function() {
                const query = this.value.toLowerCase().trim();
                document.querySelectorAll(".whitelist-row").forEach(row => {
                    const name = row.querySelector(".search-name").textContent.toLowerCase();
                    const phone = row.querySelector(".search-phone").textContent.toLowerCase();
                    const tugas = row.querySelector(".search-tugas").textContent.toLowerCase();
                    
                    if (name.includes(query) || phone.includes(query) || tugas.includes(query)) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            });
        }

        // --- 5. SheetJS Import Handler ---
        let parsedTeachers = [];
        const fileInput = document.getElementById('teacherExcelFile');
        const previewContainer = document.getElementById('teacherPreviewContainer');
        const previewBody = document.getElementById('teacherPreviewBody');
        const totalRowsText = document.getElementById('teacherTotalRowsText');
        const btnSubmit = document.getElementById('btnSubmitTeacherImport');

        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                e.target.nextElementSibling.innerText = file.name;

                const reader = new FileReader();
                reader.onload = function(evt) {
                    try {
                        const data = new Uint8Array(evt.target.result);
                        const workbook = XLSX.read(data, { type: 'array' });
                        const firstSheetName = workbook.SheetNames[0];
                        const worksheet = workbook.Sheets[firstSheetName];
                        const jsonData = XLSX.utils.sheet_to_json(worksheet);

                        if (jsonData.length === 0) {
                            alert("File Excel kosong.");
                            resetImportForm();
                            return;
                        }

                        parsedTeachers = [];
                        previewBody.innerHTML = '';

                        jsonData.forEach((row, index) => {
                            let name = '';
                            let phone = '';
                            let role = 'guru_mapel';
                            let tugas_tambahan = '';

                            Object.keys(row).forEach(key => {
                                const cleanKey = key.toLowerCase().trim();
                                const val = String(row[key] ?? '').trim();
                                
                                if (cleanKey === 'nama' || cleanKey === 'name' || cleanKey === 'nama lengkap') name = val;
                                else if (cleanKey === 'whatsapp' || cleanKey === 'phone' || cleanKey === 'no hp' || cleanKey === 'no. whatsapp') phone = val;
                                else if (cleanKey === 'role' || cleanKey === 'peran' || cleanKey === 'akses') role = val;
                                else if (cleanKey === 'tugas_tambahan' || cleanKey === 'tugas tambahan') tugas_tambahan = val;
                            });

                            if (name && phone) {
                                const teacherObj = { name, phone, role, tugas_tambahan };
                                parsedTeachers.push(teacherObj);

                                if (parsedTeachers.length <= 10) {
                                    const tr = document.createElement('tr');
                                    tr.innerHTML = `
                                        <td><strong>${escapeHtml(name)}</strong></td>
                                        <td><code>+${escapeHtml(phone)}</code></td>
                                        <td><span class="badge badge-light border text-dark font-weight-bold">${escapeHtml(tugas_tambahan || '-')}</span></td>
                                        <td><span class="badge badge-info">${escapeHtml(role)}</span></td>
                                    `;
                                    previewBody.appendChild(tr);
                                }
                            }
                        });

                        if (parsedTeachers.length === 0) {
                            alert("Data tidak valid. Periksa header kolom.");
                            resetImportForm();
                            return;
                        }

                        totalRowsText.innerText = parsedTeachers.length;
                        previewContainer.classList.remove('d-none');
                        btnSubmit.disabled = false;

                    } catch (err) {
                        console.error(err);
                        alert("Gagal membaca file Excel.");
                        resetImportForm();
                    }
                };
                reader.readAsArrayBuffer(file);
            });
        }

        if (btnSubmit) {
            btnSubmit.addEventListener('click', function() {
                if (parsedTeachers.length === 0) return;

                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';

                const csrfToken = document.querySelector('input[name="csrf_test_name"]').value;

                fetch('<?= base_url('/allowed-numbers/import') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ teachers: parsedTeachers })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert(data.message);
                        window.location.href = '<?= base_url('/whatsapp-settings?tab=whitelist') ?>';
                    } else {
                        alert("Error: " + data.message);
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = '<i class="fas fa-upload mr-1"></i> Simpan Data Import';
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Terjadi kesalahan koneksi saat mengimpor data.");
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="fas fa-upload mr-1"></i> Simpan Data Import';
                });
            });
        }

        function resetImportForm() {
            fileInput.value = '';
            fileInput.nextElementSibling.innerText = 'Pilih file...';
            previewContainer.classList.add('d-none');
            previewBody.innerHTML = '';
            parsedTeachers = [];
            btnSubmit.disabled = true;
        }

        function escapeHtml(text) {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // --- 6. Live WhatsApp Groups Fetcher ---
        const groupsInfoBox = document.getElementById("groups-info-box");
        const groupsTableContainer = document.getElementById("groups-table-container");
        const groupsTableBody = document.getElementById("groups-table-body");
        const btnRefresh = document.getElementById("btnRefreshGroups");
        const inputBroadcastJid = document.getElementById("inputBroadcastJid");
        const inputSchoolJid = document.getElementById("inputSchoolJid");

        function fetchGroups() {
            if (!groupsInfoBox) return;
            
            groupsInfoBox.className = "alert alert-secondary";
            groupsInfoBox.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menghubungi bot untuk memuat daftar grup WhatsApp secara real-time...';
            groupsTableContainer.classList.add("d-none");
            groupsTableBody.innerHTML = "";

            if (btnRefresh) {
                btnRefresh.disabled = true;
                btnRefresh.innerHTML = '<i class="fas fa-sync fa-spin mr-1"></i> Loading...';
            }

            fetch(`${waBotUrl}/api/bot/groups`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        groupsInfoBox.className = "alert alert-danger";
                        groupsInfoBox.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i> Gagal memuat: ${data.error}`;
                    } else if (!data.connected) {
                        groupsInfoBox.className = "alert alert-warning";
                        groupsInfoBox.innerHTML = '<i class="fas fa-triangle-exclamation mr-1"></i> Bot WhatsApp terdeteksi *OFFLINE*. Pastikan bot dalam kondisi terhubung untuk membaca daftar grup live.';
                    } else if (data.groups.length === 0) {
                        groupsInfoBox.className = "alert alert-info";
                        groupsInfoBox.innerHTML = '<i class="fas fa-info-circle mr-1"></i> Bot tidak mendeteksi adanya grup WhatsApp yang diikuti.';
                    } else {
                        groupsInfoBox.className = "d-none";
                        groupsTableContainer.classList.remove("d-none");
                        
                        data.groups.sort((a,b) => a.subject.localeCompare(b.subject)).forEach(g => {
                            const tr = document.createElement("tr");
                            
                            // Check if active target
                            const isPKL = g.id === inputBroadcastJid.value;
                            const isKBM = g.id === inputSchoolJid.value;
                            
                            let badges = "";
                            if (isPKL) badges += '<span class="badge badge-success px-2 py-1 mr-1">Laporan PKL</span>';
                            if (isKBM) badges += '<span class="badge badge-info px-2 py-1">Absensi KBM</span>';
                            
                            tr.innerHTML = `
                                <td class="align-middle font-weight-bold text-dark">
                                    ${escapeHtml(g.subject)} 
                                    <div class="mt-1">${badges}</div>
                                </td>
                                <td class="align-middle"><code>${escapeHtml(g.id)}</code></td>
                                <td class="align-middle text-center">
                                    <button type="button" class="btn btn-xs btn-outline-success mr-1 btn-set-pkl" data-jid="${escapeHtml(g.id)}">
                                        <i class="fas fa-clipboard-check mr-1"></i> Set PKL
                                    </button>
                                    <button type="button" class="btn btn-xs btn-outline-info mr-1 btn-set-kbm" data-jid="${escapeHtml(g.id)}">
                                        <i class="fas fa-calendar-check mr-1"></i> Set KBM
                                    </button>
                                    <button type="button" class="btn btn-xs btn-outline-primary btn-set-both" data-jid="${escapeHtml(g.id)}">
                                        <i class="fas fa-users-cog mr-1"></i> Set Keduanya
                                    </button>
                                </td>
                            `;
                            groupsTableBody.appendChild(tr);
                        });

                        // Attach button click handlers
                        document.querySelectorAll(".btn-set-pkl").forEach(btn => {
                            btn.addEventListener("click", function() {
                                inputBroadcastJid.value = this.getAttribute("data-jid");
                                highlightInput(inputBroadcastJid);
                                fetchGroups(); // redraw badges
                            });
                        });
                        document.querySelectorAll(".btn-set-kbm").forEach(btn => {
                            btn.addEventListener("click", function() {
                                inputSchoolJid.value = this.getAttribute("data-jid");
                                highlightInput(inputSchoolJid);
                                fetchGroups(); // redraw badges
                            });
                        });
                        document.querySelectorAll(".btn-set-both").forEach(btn => {
                            btn.addEventListener("click", function() {
                                const jid = this.getAttribute("data-jid");
                                inputBroadcastJid.value = jid;
                                inputSchoolJid.value = jid;
                                highlightInput(inputBroadcastJid);
                                highlightInput(inputSchoolJid);
                                fetchGroups(); // redraw badges
                            });
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    groupsInfoBox.className = "alert alert-danger";
                    groupsInfoBox.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i> Terjadi kesalahan koneksi ke server bot: ${err.message}`;
                })
                .finally(() => {
                    if (btnRefresh) {
                        btnRefresh.disabled = false;
                        btnRefresh.innerHTML = '<i class="fas fa-sync mr-1"></i> Reload Live';
                    }
                });
        }

        function highlightInput(input) {
            input.style.backgroundColor = "#e8f5e9";
            input.style.borderColor = "#28a745";
            setTimeout(() => {
                input.style.backgroundColor = "";
                input.style.borderColor = "";
            }, 1000);
        }

        if (btnRefresh) {
            btnRefresh.addEventListener("click", fetchGroups);
        }

        // Join Group via Link Undangan
        const btnJoinGroup = document.getElementById("btnJoinGroupLink");
        const inviteLinkInput = document.getElementById("invite-link-input");
        const joinGroupAlert = document.getElementById("join-group-alert");

        if (btnJoinGroup) {
            btnJoinGroup.addEventListener("click", function() {
                const inviteLink = inviteLinkInput.value.trim();
                if (!inviteLink) {
                    alert("Masukkan link undangan grup WhatsApp.");
                    return;
                }

                btnJoinGroup.disabled = true;
                btnJoinGroup.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';
                joinGroupAlert.className = "d-none";

                const csrfToken = document.querySelector('input[name="csrf_test_name"]').value;
                const formData = new FormData();
                formData.append('invite_link', inviteLink);
                formData.append('csrf_test_name', csrfToken);

                fetch('<?= base_url("whatsapp-settings/join-group") ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        joinGroupAlert.className = "alert alert-success mt-2";
                        joinGroupAlert.innerHTML = `<i class="fas fa-check-circle mr-1"></i> ${data.message} <br> <strong>Nama Grup:</strong> ${data.group.subject} <br> <strong>JID:</strong> <code>${data.group.id}</code>`;
                        inviteLinkInput.value = "";
                        // Refresh group list
                        fetchGroups();
                    } else {
                        joinGroupAlert.className = "alert alert-danger mt-2";
                        joinGroupAlert.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i> ${data.message}`;
                    }
                })
                .catch(err => {
                    console.error(err);
                    joinGroupAlert.className = "alert alert-danger mt-2";
                    joinGroupAlert.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i> Terjadi kesalahan koneksi.`;
                })
                .finally(() => {
                    btnJoinGroup.disabled = false;
                    btnJoinGroup.innerHTML = '<i class="fas fa-plus mr-1"></i> Hubungkan & Gabung Grup';
                });
            });
        }
        
        // Trigger fetch groups once the user clicks on groups tab
        const groupsTab = document.getElementById("groups-tab");
        if (groupsTab) {
            groupsTab.addEventListener("click", function() {
                // Wait small timeout for tab to activate
                setTimeout(fetchGroups, 100);
            });
        }

        // If page loaded with groups tab active, run fetch
        if (activeTab === "groups") {
            setTimeout(fetchGroups, 200);
        }
    });
</script>

<style>
    /* Override global form-control height for template textarea */
    textarea.form-control.tpl-textarea {
        height: auto !important;
        min-height: 280px;
        font-size: 14px;
        line-height: 1.6;
    }

    /* Styling variables badge */
    .select-var-badge {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .select-var-badge:hover {
        background-color: #007bff !important;
        transform: translateY(-1px);
    }
    .transition-all {
        transition: all 0.3s ease;
    }
    .collapse.show + .card-header .fa-chevron-down {
        transform: rotate(180deg);
    }
    .font-mono {
        font-family: 'Courier New', Courier, monospace;
    }
    .gap-2 {
        gap: 0.5rem;
    }
</style>
<?= $this->endSection() ?>
