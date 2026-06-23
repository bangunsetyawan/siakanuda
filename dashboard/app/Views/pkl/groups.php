<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Kelompok PKL (DU/DI)</h1>
            <p class="text-secondary mb-0">Atur pemetaan tempat PKL, ketua kelompok, anggota kelompok, dan guru pembimbing.</p>
        </div>
        <?php if (in_array(session()->get('role'), ['admin', 'kepsek', 'guru', 'guru_mapel', 'guru_bk'])): ?>
        <div>
            <?php if (in_array(session()->get('role'), ['admin', 'kepsek'])): ?>
            <form action="<?= base_url('/pkl/reset-all-ketua-passwords') ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin RESET SEMUA password ketua PKL ke default (NISN)?\n\nSemua ketua harus login ulang dan mengganti password.');" class="d-inline m-0">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-warning mr-2">
                    <i class="fas fa-key mr-2"></i> Reset Password Ketua
                </button>
            </form>
            <?php endif; ?>
            <button class="btn btn-outline-success mr-2" data-toggle="modal" data-target="#modal-import-group">
                <i class="fas fa-file-excel mr-2"></i> Import Excel
            </button>
            <button class="btn btn-primary" data-toggle="modal" data-target="#modal-add-group">
                <i class="fas fa-plus mr-2"></i> Hubungkan Kelompok
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold text-dark m-0"><i class="fas fa-map-marked-alt mr-2 text-primary"></i> Daftar Tempat PKL</h3>
        <div class="card-tools m-0">
            <div class="input-group input-group-sm" style="width: 250px;">
                <input type="text" id="tableSearch" class="form-control float-right" placeholder="Cari Tempat / Anggota / Ketua...">
                <div class="input-group-append">
                    <button type="button" class="btn btn-default">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- /.card-header -->
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width: 80px;">#</th>
                        <th>Tempat DU/DI</th>
                        <th>Ketua Kelompok</th>
                        <th>Daftar Anggota</th>
                        <th>Guru Pembimbing</th>
                        <th>Instruktur DU/DI</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="groupTableBody">
                    <?php if (empty($groups)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-5">
                                <i class="fas fa-map-pin mb-2" style="font-size: 32px;"></i>
                                <p class="mb-0">Belum ada pemetaan kelompok PKL.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $i = 1; foreach ($groups as $group): ?>
                            <?php
                            $pembimbingName = 'Belum Dipasang';
                            foreach ($teachers as $t) {
                                if ($t['phone'] === $group['pembimbing_phone']) {
                                    $pembimbingName = $t['name'];
                                    break;
                                }
                            }
                            $ketuaName = '';
                            $ketuaPhone = $group['ketua_phone'];
                            foreach ($students as $s) {
                                $sPhone = $s['phone'] ?: $s['nis'];
                                if ($sPhone === $ketuaPhone) {
                                    $ketuaName = $s['name'];
                                    break;
                                }
                            }
                            ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td class="font-weight-bold text-dark"><?= htmlspecialchars($group['tempat_pkl']) ?></td>
                                <td>
                                    <?php if ($ketuaName): ?>
                                        <div class="font-weight-bold text-primary" style="font-size: 14px;"><?= htmlspecialchars($ketuaName) ?></div>
                                        <small class="text-secondary"><i class="fab fa-whatsapp mr-1"></i><?= htmlspecialchars($group['ketua_phone']) ?></small>
                                    <?php else: ?>
                                        <div class="text-secondary font-italic" style="font-size: 13px;">Nama tidak ditemukan</div>
                                        <small class="text-secondary"><i class="fab fa-whatsapp mr-1"></i><?= htmlspecialchars($group['ketua_phone']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $members = explode(',', $group['anggota']);
                                    foreach ($members as $m) {
                                        echo '<span class="badge badge-light px-2 py-1 font-weight-bold text-secondary mr-1 mb-1 border">' . htmlspecialchars(trim($m)) . '</span>';
                                    }
                                    ?>
                                </td>
                                <td class="font-weight-bold text-success"><?= htmlspecialchars($pembimbingName) ?></td>
                                <td>
                                    <?php if (!empty($group['instruktur_phone'])): ?>
                                        <div class="font-weight-bold text-dark" style="font-size: 13px;">Tersimpan</div>
                                        <small class="text-secondary"><i class="fab fa-whatsapp mr-1"></i><?= htmlspecialchars($group['instruktur_phone']) ?></small>
                                    <?php else: ?>
                                        <div class="text-danger font-italic" style="font-size: 13px;">Belum Diisi</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $role = session()->get('role');
                                    $userPhone = session()->get('phone');
                                    $canEditGroup = in_array($role, ['admin', 'kepsek']) || (in_array($role, ['guru', 'guru_mapel', 'guru_bk']) && $group['pembimbing_phone'] === $userPhone);
                                    $canDeleteGroup = in_array($role, ['admin', 'kepsek']);
                                    ?>
                                    <div class="btn-group">
                                        <?php if ($canEditGroup): ?>
                                            <a href="<?= base_url('/pkl/takeover?group_id=' . $group['id']) ?>" class="btn btn-sm btn-outline-warning mr-1 rounded" title="Input Laporan Susulan (Manual/Override)">
                                                <i class="fas fa-file-signature"></i> Susulan
                                            </a>
                                            <button class="btn btn-sm btn-outline-primary mr-1 rounded" 
                                                    data-toggle="modal" 
                                                    data-target="#modal-edit-group-<?= $group['id'] ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted font-italic" style="font-size:12px;">Hanya Baca</span>
                                        <?php endif; ?>
                                        
                                        <?php if (in_array($role, ['admin', 'kepsek', 'guru', 'guru_mapel', 'guru_bk'])): ?>
                                            <a href="<?= base_url('/pkl/riwayat/' . $group['ketua_phone']) ?>" 
                                               class="btn btn-sm btn-outline-info ml-1 rounded" 
                                               title="Lihat Riwayat & Cetak Laporan">
                                                <i class="fas fa-history"></i> Riwayat
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($canDeleteGroup): ?>
                                            <form action="<?= base_url('/pkl/groups/delete/' . $group['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelompok ini?')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Group Modal -->
                            <div class="modal fade" id="modal-edit-group-<?= $group['id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content border-0 shadow rounded-lg">
                                        <div class="modal-header border-bottom">
                                            <h5 class="modal-title font-weight-bold text-dark">Edit Kelompok PKL</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form action="<?= base_url('/pkl/groups/update/' . $group['id']) ?>" method="post">
                                            <?= csrf_field() ?>
                                            <div class="modal-body">
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-secondary">Nama Tempat DU/DI</label>
                                                    <input type="text" name="tempat_pkl" class="form-control" value="<?= htmlspecialchars($group['tempat_pkl']) ?>" required>
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-secondary">Ketua Kelompok PKL</label>
                                                    <input type="hidden" name="ketua_phone" id="editKetuaPhoneHidden-<?= $group['id'] ?>" value="<?= htmlspecialchars($group['ketua_phone']) ?>">
                                                    <div class="pkl-single-student-picker" data-target-phone="editKetuaPhoneHidden-<?= $group['id'] ?>" id="picker-edit-ketua-<?= $group['id'] ?>" data-initial-name="<?= htmlspecialchars($ketuaName) ?>" data-initial-phone="<?= htmlspecialchars($group['ketua_phone']) ?>">
                                                        <div class="pkl-selected-tag-single mb-1"></div>
                                                        <input type="text" class="pkl-search-input-single form-control form-control-sm mt-1" placeholder="🔍 Cari nama ketua...">
                                                        <div class="pkl-dropdown-single"></div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-secondary">Daftar Anggota Kelompok</label>
                                                    <input type="hidden" name="anggota" id="editAnggotaHidden-<?= $group['id'] ?>" value="<?= htmlspecialchars($group['anggota']) ?>" required>
                                                    <div class="pkl-student-picker" data-target="editAnggotaHidden-<?= $group['id'] ?>" data-initial="<?= htmlspecialchars($group['anggota']) ?>">
                                                        <div class="pkl-selected-tags"></div>
                                                        <input type="text" class="pkl-search-input form-control form-control-sm mt-1" placeholder="🔍 Cari nama siswa...">
                                                        <div class="pkl-dropdown"></div>
                                                    </div>
                                                </div>
                                                <?php if (in_array($role, ['admin', 'kepsek'])): ?>
                                                    <div class="form-group mb-3">
                                                        <label class="font-weight-bold text-secondary">Guru Pembimbing PKL</label>
                                                        <select name="pembimbing_phone" class="form-control">
                                                            <option value="">-- Pilih Pembimbing --</option>
                                                            <?php foreach ($teachers as $t): ?>
                                                                <option value="<?= $t['phone'] ?>" <?= $t['phone'] === $group['pembimbing_phone'] ? 'selected' : '' ?>><?= htmlspecialchars($t['name']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                <?php else: ?>
                                                    <input type="hidden" name="pembimbing_phone" value="<?= htmlspecialchars($group['pembimbing_phone']) ?>">
                                                <?php endif; ?>
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-secondary">No. WA Instruktur / Tempat PKL</label>
                                                    <input type="text" name="instruktur_phone" class="form-control" value="<?= htmlspecialchars($group['instruktur_phone'] ?? '') ?>" placeholder="Contoh: 628xxxx">
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

<!-- Add Group Modal -->
<div class="modal fade" id="modal-add-group" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow rounded-lg">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark">Hubungkan Kelompok PKL Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('/pkl/groups/create') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Nama Tempat DU/DI</label>
                        <input type="text" name="tempat_pkl" class="form-control" placeholder="Masukkan Nama DU/DI (Perusahaan)" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Ketua Kelompok PKL</label>
                        <input type="hidden" name="ketua_phone" id="addKetuaPhoneHidden" value="">
                        <div class="pkl-single-student-picker" data-target-phone="addKetuaPhoneHidden" id="picker-add-ketua" data-initial-name="" data-initial-phone="">
                            <div class="pkl-selected-tag-single mb-1"></div>
                            <input type="text" class="pkl-search-input-single form-control form-control-sm mt-1" placeholder="🔍 Cari nama ketua...">
                            <div class="pkl-dropdown-single"></div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Daftar Anggota Kelompok</label>
                        <input type="hidden" name="anggota" id="addAnggotaHidden" value="" required>
                        <div class="pkl-student-picker" data-target="addAnggotaHidden" data-initial="">
                            <div class="pkl-selected-tags"></div>
                            <input type="text" class="pkl-search-input form-control form-control-sm mt-1" placeholder="🔍 Cari nama siswa...">
                            <div class="pkl-dropdown"></div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Guru Pembimbing PKL</label>
                        <select name="pembimbing_phone" class="form-control">
                            <option value="">-- Pilih Pembimbing --</option>
                            <?php foreach ($teachers as $t): ?>
                                <option value="<?= $t['phone'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">No. WA Instruktur / Tempat PKL</label>
                        <input type="text" name="instruktur_phone" class="form-control" placeholder="Contoh: 628xxxx">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Kelompok</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Group Modal -->
<div class="modal fade" id="modal-import-group" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow rounded-lg">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-file-excel mr-2 text-success"></i> Import Kelompok PKL via Excel</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2" style="font-size: 13px;">
                    <h6 class="font-weight-bold mb-1"><i class="fas fa-info-circle mr-1"></i>Dua Format Excel Didukung (Otomatis Dideteksi):</h6>
                    <hr class="my-1 border-light">
                    <strong>1. Format Per Siswa (Satu Baris Per Siswa - Direkomendasikan):</strong>
                    <div class="font-weight-bold mt-1 text-monospace">
                        [Nama] &nbsp;|&nbsp; [Tempat Pkl] &nbsp;|&nbsp; [No WA / NISN Ketua] &nbsp;|&nbsp; [No WA Pembimbing]
                    </div>
                    <span class="text-secondary small">Sistem otomatis mengelompokkan siswa dengan Tempat Pkl yang sama menjadi satu kelompok. Isi nomor WA atau NISN Ketua di salah satu baris siswa untuk menetapkan ketua kelompok tersebut.</span>
                    
                    <strong class="d-block mt-2">2. Format Per Kelompok (Satu Baris Per Kelompok):</strong>
                    <div class="font-weight-bold mt-1 text-monospace">
                        [tempat_pkl] &nbsp;|&nbsp; [anggota] &nbsp;|&nbsp; [ketua_phone] &nbsp;|&nbsp; [pembimbing_phone] &nbsp;|&nbsp; [instruktur_phone]
                    </div>
                    <span class="text-secondary small">Kolom <code>anggota</code> berisi nama-nama siswa dipisahkan koma. Contoh: <code>ADIN MI`ROJUL KANA, Aditya Putra Pratama</code>.</span>
                    
                    <div class="mt-3 text-right">
                        <button type="button" class="btn btn-xs btn-success text-white font-weight-bold" id="btnDownloadTemplateGroup">
                            <i class="fas fa-download mr-1"></i> Download Template Excel (Format Per Siswa)
                        </button>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark">Pilih File Excel / CSV</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="groupExcelFile" accept=".xlsx, .xls, .csv">
                        <label class="custom-file-label" for="groupExcelFile">Pilih file...</label>
                    </div>
                </div>

                <!-- Preview Area -->
                <div id="groupPreviewContainer" class="d-none">
                    <h6 class="font-weight-bold text-dark mb-2"><i class="fas fa-eye mr-1"></i> Preview Data (Maks. 10 baris pertama)</h6>
                    <div class="table-responsive border rounded mb-3" style="max-height: 250px;">
                        <table class="table table-sm table-striped mb-0" style="font-size: 12px;">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th>Tempat PKL</th>
                                    <th>Anggota</th>
                                    <th>WA Ketua</th>
                                    <th>WA Pembimbing</th>
                                    <th>WA Instruktur</th>
                                </tr>
                            </thead>
                            <tbody id="groupPreviewBody"></tbody>
                        </table>
                    </div>
                    <div class="text-right text-secondary mb-3" style="font-size: 11px;">
                        Total data terdeteksi: <strong id="groupTotalRowsText" class="text-dark">0</strong> baris
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnSubmitGroupImport" disabled>
                    <i class="fas fa-upload mr-1"></i> Simpan Data Import
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Student Picker Styles -->
<style>
.pkl-student-picker {
    position: relative;
}
.pkl-single-student-picker {
    position: relative;
}
.pkl-selected-tag-single {
    min-height: 28px;
}
.pkl-dropdown-single {
    display: none;
    position: absolute;
    left: 0;
    right: 0;
    z-index: 1050;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 10px;
    max-height: 220px;
    overflow-y: auto;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    margin-top: 2px;
}
.pkl-dropdown-single.show { display: block; }
.pkl-dropdown-single .pkl-group-header {
    padding: 6px 12px;
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    background: #f8fafc;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    position: sticky;
    top: 0;
    border-bottom: 1px solid #f1f5f9;
}
.pkl-dropdown-single .pkl-option {
    padding: 7px 12px;
    font-size: 13px;
    cursor: pointer;
    transition: background 0.15s;
    display: flex;
    align-items: center;
    gap: 6px;
}
.pkl-dropdown-single .pkl-option:hover { background: #f0f9ff; }
.pkl-dropdown-single .pkl-option.selected {
    background: #e0f2fe;
    color: #0284c7;
    font-weight: 600;
}
.pkl-dropdown-single .pkl-option .pkl-check {
    width: 16px;
    text-align: center;
    font-size: 12px;
}
.pkl-dropdown-single .pkl-no-result {
    padding: 12px;
    text-align: center;
    color: #94a3b8;
    font-size: 13px;
}
.pkl-selected-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    min-height: 28px;
}
.pkl-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
    border-radius: 8px;
    padding: 3px 8px;
    font-size: 12px;
    font-weight: 600;
    animation: fadeIn 0.15s ease;
}
@keyframes fadeIn { from { opacity:0; transform:scale(0.9); } to { opacity:1; transform:scale(1); } }
.pkl-tag .pkl-tag-remove {
    cursor: pointer;
    color: #dc3545;
    font-weight: 700;
    font-size: 14px;
    line-height: 1;
    margin-left: 2px;
}
.pkl-tag .pkl-tag-remove:hover {
    color: #a71d2a;
}
.pkl-dropdown {
    display: none;
    position: absolute;
    left: 0;
    right: 0;
    z-index: 1050;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 10px;
    max-height: 220px;
    overflow-y: auto;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    margin-top: 2px;
}
.pkl-dropdown.show { display: block; }
.pkl-dropdown .pkl-group-header {
    padding: 6px 12px;
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    background: #f8fafc;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    position: sticky;
    top: 0;
    border-bottom: 1px solid #f1f5f9;
}
.pkl-dropdown .pkl-option {
    padding: 7px 12px;
    font-size: 13px;
    cursor: pointer;
    transition: background 0.15s;
    display: flex;
    align-items: center;
    gap: 6px;
}
.pkl-dropdown .pkl-option:hover { background: #f0f9ff; }
.pkl-dropdown .pkl-option.selected {
    background: #e0f2fe;
    color: #0284c7;
    font-weight: 600;
}
.pkl-dropdown .pkl-option .pkl-check {
    width: 16px;
    text-align: center;
    font-size: 12px;
}
.pkl-dropdown .pkl-no-result {
    padding: 12px;
    text-align: center;
    color: #94a3b8;
    font-size: 13px;
}
</style>

<!-- SheetJS Library -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<!-- Student Picker Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // === Table Search Logic ===
    const searchInput = document.getElementById('tableSearch');
    const tableBody = document.getElementById('groupTableBody');
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

    // All students data from PHP
    const allStudents = <?= json_encode(array_map(fn($s) => ['name' => $s['name'], 'class' => $s['class'], 'phone' => $s['phone'], 'nis' => $s['nis'], 'role' => $s['role'] ?? 'siswa'], $students)) ?>;

    // Group students by class
    const studentsByClass = {};
    allStudents.forEach(s => {
        if (s.role === 'siswa') return; // Hanya izinkan Anggota PKL & Ketua PKL
        if (!studentsByClass[s.class]) studentsByClass[s.class] = [];
        studentsByClass[s.class].push(s.name);
    });
    const sortedClasses = Object.keys(studentsByClass).sort();

    // Initialize all pickers
    document.querySelectorAll('.pkl-student-picker').forEach(initPicker);
    document.querySelectorAll('.pkl-single-student-picker').forEach(initSinglePicker);

    function initSinglePicker(picker) {
        const hiddenPhoneId = picker.dataset.targetPhone;
        const hiddenPhoneInput = document.getElementById(hiddenPhoneId);
        const tagContainer = picker.querySelector('.pkl-selected-tag-single');
        const searchInput = picker.querySelector('.pkl-search-input-single');
        const dropdown = picker.querySelector('.pkl-dropdown-single');

        let selectedName = picker.dataset.initialName || '';
        let selectedPhone = picker.dataset.initialPhone || '';

        function syncHidden() {
            hiddenPhoneInput.value = selectedPhone;
        }

        function renderTag() {
            tagContainer.innerHTML = '';
            if (selectedName) {
                const tag = document.createElement('span');
                tag.className = 'pkl-tag';
                const displayPhone = selectedPhone || '(Belum ada WA/NISN)';
                tag.innerHTML = `${escHtml(selectedName)} <small class="text-muted">(${escHtml(displayPhone)})</small> <span class="pkl-tag-remove" title="Hapus">&times;</span>`;
                tag.querySelector('.pkl-tag-remove').addEventListener('click', () => {
                    selectedName = '';
                    selectedPhone = '';
                    renderTag();
                    renderDropdown(searchInput.value);
                    syncHidden();
                });
                tagContainer.appendChild(tag);
                searchInput.style.display = 'none';
            } else {
                searchInput.style.display = 'block';
            }
        }

        function renderDropdown(query) {
            const q = (query || '').toLowerCase();
            dropdown.innerHTML = '';
            let hasResult = false;

            sortedClasses.forEach(cls => {
                const filtered = allStudents.filter(s => {
                    return s.class === cls && s.role !== 'siswa' && (s.name.toLowerCase().includes(q) || s.class.toLowerCase().includes(q));
                });
                if (filtered.length === 0) return;
                hasResult = true;

                const header = document.createElement('div');
                header.className = 'pkl-group-header';
                header.textContent = cls + ' (' + filtered.length + ')';
                dropdown.appendChild(header);

                filtered.forEach(s => {
                    const opt = document.createElement('div');
                    const isSel = (selectedName === s.name);
                    opt.className = 'pkl-option' + (isSel ? ' selected' : '');
                    const sPhone = s.phone || s.nis || '';
                    const displayWA = s.phone ? `WA: ${s.phone}` : `NISN: ${s.nis} (No WA)`;
                    opt.innerHTML = `<span class="pkl-check">${isSel ? '✓' : ''}</span> ${escHtml(s.name)} <small class="text-secondary ml-1">(${escHtml(displayWA)})</small>`;
                    opt.addEventListener('click', (e) => {
                        e.stopPropagation();
                        selectedName = s.name;
                        selectedPhone = sPhone;
                        renderTag();
                        dropdown.classList.remove('show');
                        syncHidden();
                    });
                    dropdown.appendChild(opt);
                });
            });

            if (!hasResult) {
                dropdown.innerHTML = '<div class="pkl-no-result">Tidak ada siswa ditemukan.</div>';
            }
        }

        searchInput.addEventListener('focus', () => {
            renderDropdown(searchInput.value);
            dropdown.classList.add('show');
        });

        searchInput.addEventListener('input', () => {
            renderDropdown(searchInput.value);
            dropdown.classList.add('show');
        });

        document.addEventListener('click', (e) => {
            if (!picker.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });

        renderTag();
        syncHidden();
    }

    function initPicker(picker) {
        const hiddenId = picker.dataset.target;
        const hiddenInput = document.getElementById(hiddenId);
        const tagsContainer = picker.querySelector('.pkl-selected-tags');
        const searchInput = picker.querySelector('.pkl-search-input');
        const dropdown = picker.querySelector('.pkl-dropdown');

        // Parse initial values
        let selected = [];
        const initial = picker.dataset.initial || '';
        if (initial.trim()) {
            selected = initial.split(',').map(s => s.trim()).filter(Boolean);
        }

        function syncHidden() {
            hiddenInput.value = selected.join(', ');
        }

        function renderTags() {
            tagsContainer.innerHTML = '';
            selected.forEach(name => {
                const tag = document.createElement('span');
                tag.className = 'pkl-tag';
                tag.innerHTML = `${escHtml(name)} <span class="pkl-tag-remove" title="Hapus">&times;</span>`;
                tag.querySelector('.pkl-tag-remove').addEventListener('click', () => {
                    selected = selected.filter(n => n !== name);
                    renderTags();
                    renderDropdown(searchInput.value);
                    syncHidden();
                });
                tagsContainer.appendChild(tag);
            });
        }

        function renderDropdown(query) {
            const q = (query || '').toLowerCase();
            dropdown.innerHTML = '';
            let hasResult = false;

            sortedClasses.forEach(cls => {
                const filtered = studentsByClass[cls].filter(name => {
                    return name.toLowerCase().includes(q) || cls.toLowerCase().includes(q);
                });
                if (filtered.length === 0) return;
                hasResult = true;

                const header = document.createElement('div');
                header.className = 'pkl-group-header';
                header.textContent = cls + ' (' + filtered.length + ')';
                dropdown.appendChild(header);

                filtered.forEach(name => {
                    const opt = document.createElement('div');
                    opt.className = 'pkl-option' + (selected.includes(name) ? ' selected' : '');
                    const checkMark = selected.includes(name) ? '✓' : '';
                    opt.innerHTML = `<span class="pkl-check">${checkMark}</span> ${escHtml(name)}`;
                    opt.addEventListener('click', (e) => {
                        e.stopPropagation();
                        if (selected.includes(name)) {
                            selected = selected.filter(n => n !== name);
                        } else {
                            selected.push(name);
                        }
                        renderTags();
                        renderDropdown(searchInput.value);
                        syncHidden();
                        searchInput.focus();
                    });
                    dropdown.appendChild(opt);
                });
            });

            if (!hasResult) {
                dropdown.innerHTML = '<div class="pkl-no-result">Tidak ada siswa ditemukan.</div>';
            }
        }

        // Events
        searchInput.addEventListener('focus', () => {
            renderDropdown(searchInput.value);
            dropdown.classList.add('show');
        });

        searchInput.addEventListener('input', () => {
            renderDropdown(searchInput.value);
            dropdown.classList.add('show');
        });

        // Close dropdown on outside click
        document.addEventListener('click', (e) => {
            if (!picker.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });

        // Init
        renderTags();
        syncHidden();
    }

    // === Import Group Excel Logic ===
    let parsedGroups = [];
    const groupFileInput = document.getElementById('groupExcelFile');
    const groupPreviewContainer = document.getElementById('groupPreviewContainer');
    const groupPreviewBody = document.getElementById('groupPreviewBody');
    const groupTotalRowsText = document.getElementById('groupTotalRowsText');
    const btnSubmitGroup = document.getElementById('btnSubmitGroupImport');
    const btnDownloadTemplateGroup = document.getElementById('btnDownloadTemplateGroup');

    if (btnDownloadTemplateGroup) {
        btnDownloadTemplateGroup.addEventListener('click', function() {
            const templateData = [
                {
                    'No.': 1,
                    'Nama': 'AMELIA FITRIANA',
                    'Kelas': 'XII AKL',
                    'Tempat Pkl': 'Balai Desa Bagorejo',
                    'No WA Ketua': '628123456789',
                    'No WA Pembimbing': '628987654321'
                },
                {
                    'No.': 2,
                    'Nama': 'DEVA ANANTA BALQEUS',
                    'Kelas': 'XII AKL',
                    'Tempat Pkl': 'Balai Desa Bagorejo',
                    'No WA Ketua': '',
                    'No WA Pembimbing': ''
                },
                {
                    'No.': 3,
                    'Nama': 'GEBY AZIZATUL ZANAH',
                    'Kelas': 'XII AKL',
                    'Tempat Pkl': 'Balai Desa Bagorejo',
                    'No WA Ketua': '',
                    'No WA Pembimbing': ''
                },
                {
                    'No.': 4,
                    'Nama': 'RANI JULIA NUR ANGGRAINI',
                    'Kelas': 'XII AKL',
                    'Tempat Pkl': 'Balai Desa Bagorejo',
                    'No WA Ketua': '',
                    'No WA Pembimbing': ''
                },
                {
                    'No.': 5,
                    'Nama': 'AURA MALIKA',
                    'Kelas': 'XII AKL',
                    'Tempat Pkl': 'KSP Gentha Sumbersewu',
                    'No WA Ketua': '',
                    'No WA Pembimbing': ''
                },
                {
                    'No.': 6,
                    'Nama': 'NUR ALAINA',
                    'Kelas': 'XII AKL',
                    'Tempat Pkl': 'KSP Gentha Sumbersewu',
                    'No WA Ketua': '',
                    'No WA Pembimbing': ''
                }
            ];
            const worksheet = XLSX.utils.json_to_sheet(templateData);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "Kelompok PKL");
            XLSX.writeFile(workbook, "template_kelompok_pkl.xlsx");
        });
    }

    if (groupFileInput) {
        groupFileInput.addEventListener('change', function(e) {
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
                        alert("File Excel kosong atau tidak terbaca.");
                        resetGroupImportForm();
                        return;
                    }

                    // Detect format: check if first row contains 'anggota'
                    let hasAnggotaColumn = false;
                    const firstRowKeys = Object.keys(jsonData[0]);
                    firstRowKeys.forEach(k => {
                        const cleanKey = k.toLowerCase().trim();
                        if (cleanKey === 'anggota' || cleanKey === 'anggota kelompok' || cleanKey === 'member') {
                            hasAnggotaColumn = true;
                        }
                    });

                    parsedGroups = [];
                    groupPreviewBody.innerHTML = '';

                    if (hasAnggotaColumn) {
                        // Format 2: Per Kelompok (One row per group)
                        jsonData.forEach((row, index) => {
                            let tempatPkl = '';
                            let anggota = '';
                            let ketuaPhone = '';
                            let pembimbingPhone = '';

                            Object.keys(row).forEach(key => {
                                const cleanKey = key.toLowerCase().trim();
                                const val = String(row[key] ?? '').trim();
                                
                                if (cleanKey === 'tempat_pkl' || cleanKey === 'tempat pkl' || cleanKey === 'du/di' || cleanKey === 'perusahaan') tempatPkl = val;
                                else if (cleanKey === 'anggota' || cleanKey === 'anggota kelompok' || cleanKey === 'member') anggota = val;
                                else if (cleanKey === 'ketua_phone' || cleanKey === 'no wa ketua' || cleanKey === 'ketua whatsapp') ketuaPhone = val;
                                else if (cleanKey === 'pembimbing_phone' || cleanKey === 'no wa pembimbing' || cleanKey === 'pembimbing whatsapp') pembimbingPhone = val;
                                else if (cleanKey === 'instruktur_phone' || cleanKey === 'no wa instruktur' || cleanKey === 'instruktur whatsapp') instrukturPhone = val;
                            });

                            if (tempatPkl && anggota) {
                                parsedGroups.push({
                                    tempat_pkl: tempatPkl,
                                    anggota: anggota,
                                    ketua_phone: ketuaPhone,
                                    pembimbing_phone: pembimbingPhone,
                                    instruktur_phone: instrukturPhone
                                });
                            }
                        });
                    } else {
                        // Format 1: Per Siswa (One row per student) - group by tempat_pkl
                        const groupsMap = {};

                        jsonData.forEach((row, index) => {
                            let nama = '';
                            let tempatPkl = '';
                            let ketuaPhone = '';
                            let pembimbingPhone = '';

                            Object.keys(row).forEach(key => {
                                const cleanKey = key.toLowerCase().trim();
                                const val = String(row[key] ?? '').trim();
                                
                                if (cleanKey === 'nama' || cleanKey === 'nama siswa' || cleanKey === 'student' || cleanKey === 'name') nama = val;
                                else if (cleanKey === 'tempat_pkl' || cleanKey === 'tempat pkl' || cleanKey === 'du/di' || cleanKey === 'perusahaan') tempatPkl = val;
                                else if (cleanKey === 'ketua_phone' || cleanKey === 'no wa ketua' || cleanKey === 'ketua whatsapp' || cleanKey === 'wa ketua') ketuaPhone = val;
                                else if (cleanKey === 'pembimbing_phone' || cleanKey === 'no wa pembimbing' || cleanKey === 'pembimbing whatsapp' || cleanKey === 'wa pembimbing') pembimbingPhone = val;
                                else if (cleanKey === 'instruktur_phone' || cleanKey === 'no wa instruktur' || cleanKey === 'instruktur whatsapp' || cleanKey === 'wa instruktur') instrukturPhone = val;
                            });

                            if (nama && tempatPkl) {
                                const key = tempatPkl.toLowerCase().trim();
                                if (!groupsMap[key]) {
                                    groupsMap[key] = {
                                        tempat_pkl: tempatPkl,
                                        anggota: [],
                                        ketua_phone: '',
                                        pembimbing_phone: '',
                                        instruktur_phone: ''
                                    };
                                }
                                groupsMap[key].anggota.push(nama);
                                
                                if (ketuaPhone && !groupsMap[key].ketua_phone) {
                                    groupsMap[key].ketua_phone = ketuaPhone;
                                }
                                if (pembimbingPhone && !groupsMap[key].pembimbing_phone) {
                                    groupsMap[key].pembimbing_phone = pembimbingPhone;
                                }
                                if (instrukturPhone && !groupsMap[key].instruktur_phone) {
                                    groupsMap[key].instruktur_phone = instrukturPhone;
                                }
                            }
                        });

                        Object.keys(groupsMap).forEach(key => {
                            const g = groupsMap[key];
                            parsedGroups.push({
                                tempat_pkl: g.tempat_pkl,
                                anggota: g.anggota.join(', '),
                                ketua_phone: g.ketua_phone,
                                pembimbing_phone: g.pembimbing_phone,
                                instruktur_phone: g.instruktur_phone
                            });
                        });
                    }

                    // Render preview list
                    parsedGroups.forEach((g, idx) => {
                        if (idx < 10) {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td><strong>${escHtml(g.tempat_pkl)}</strong></td>
                                <td>${escHtml(g.anggota)}</td>
                                <td>${escHtml(g.ketua_phone || '-')}</td>
                                <td>${escHtml(g.pembimbing_phone || '-')}</td>
                                <td>${escHtml(g.instruktur_phone || '-')}</td>
                            `;
                            groupPreviewBody.appendChild(tr);
                        }
                    });

                    if (parsedGroups.length === 0) {
                        alert("Data tidak valid. Pastikan ada kolom nama & tempat_pkl (untuk format siswa) atau tempat_pkl & anggota (untuk format kelompok).");
                        resetGroupImportForm();
                        return;
                    }

                    groupTotalRowsText.innerText = parsedGroups.length;
                    groupPreviewContainer.classList.remove('d-none');
                    btnSubmitGroup.disabled = false;

                } catch (err) {
                    console.error(err);
                    alert("Gagal membaca file Excel. Hubungi Admin.");
                    resetGroupImportForm();
                }
            };
            reader.readAsArrayBuffer(file);
        });
    }

    if (btnSubmitGroup) {
        btnSubmitGroup.addEventListener('click', function() {
            if (parsedGroups.length === 0) return;

            btnSubmitGroup.disabled = true;
            btnSubmitGroup.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';

            const csrfToken = document.querySelector('meta[name="csrf-hash"]').getAttribute('content');

            fetch('/pkl/groups/import', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ groups: parsedGroups })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    location.reload();
                } else {
                    alert("Error: " + data.message);
                    btnSubmitGroup.disabled = false;
                    btnSubmitGroup.innerHTML = '<i class="fas fa-upload mr-1"></i> Simpan Data Import';
                }
            })
            .catch(err => {
                console.error(err);
                alert("Terjadi kesalahan koneksi saat mengimpor data.");
                btnSubmitGroup.disabled = false;
                btnSubmitGroup.innerHTML = '<i class="fas fa-upload mr-1"></i> Simpan Data Import';
            });
        });
    }

    function resetGroupImportForm() {
        groupFileInput.value = '';
        groupFileInput.nextElementSibling.innerText = 'Pilih file...';
        groupPreviewContainer.classList.add('d-none');
        groupPreviewBody.innerHTML = '';
        parsedGroups = [];
        btnSubmitGroup.disabled = true;
    }

    function escHtml(text) {
        if (!text) return '';
        return text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
});
</script>
<?= $this->endSection() ?>
