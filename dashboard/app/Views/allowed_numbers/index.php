<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Manajemen Guru & Staf Whitelist</h1>
            <p class="text-secondary mb-0">Kelola nomor telepon WhatsApp resmi guru dan staf pendidik yang diizinkan mengakses bot sekolah.</p>
        </div>
        <div>
            <?php if (in_array(session()->get('role'), ['admin', 'kepsek'])): ?>
                <button class="btn btn-outline-success mr-2" data-toggle="modal" data-target="#modal-import-teacher">
                    <i class="fas fa-file-excel mr-2"></i> Import Excel
                </button>
                <button class="btn btn-primary" data-toggle="modal" data-target="#modal-add-teacher">
                    <i class="fas fa-plus-circle mr-2"></i> Daftarkan Guru/Staf
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-chalkboard-user mr-2 text-primary"></i> Whitelist Guru & Staf Pendidik</h3>
        <div class="card-tools ml-auto">
            <form action="" method="get" class="form-inline">
                <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Cari Nama/Nomor WA/Peran" value="<?= htmlspecialchars($search ?? '') ?>">
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
                        <th class="border-0">Nama Lengkap</th>
                        <th class="border-0">Nomor WhatsApp</th>
                        <th class="border-0">Tugas Tambahan</th>
                        <th class="border-0">Tingkat Akses (Peran)</th>
                        <?php if (in_array(session()->get('role'), ['admin', 'kepsek'])): ?>
                            <th class="border-0">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($teachers)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-5">
                                <i class="fas fa-user-lock mb-2" style="font-size: 32px;"></i>
                                <p class="mb-0">Belum ada guru/staf terdaftar di whitelist.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $i = 1; foreach ($teachers as $t): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td class="font-weight-bold text-dark"><?= htmlspecialchars($t['name']) ?></td>
                                <td><code class="text-dark font-weight-bold">+<?= htmlspecialchars($t['phone']) ?></code></td>
                                <td>
                                    <?php if (!empty($t['tugas_tambahan'])): ?>
                                        <span class="badge badge-light border text-secondary font-weight-bold px-2 py-1"><?= htmlspecialchars($t['tugas_tambahan']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
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
                                <?php if (in_array(session()->get('role'), ['admin', 'kepsek'])): ?>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-primary mr-2 rounded" 
                                                    data-toggle="modal" 
                                                    data-target="#modal-edit-teacher-<?= $t['id'] ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <?php if (session()->get('role') === 'admin'): ?>
                                                <form action="<?= base_url('/allowed-numbers/delete/' . $t['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan hak akses nomor guru ini?')">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded">
                                                        <i class="fas fa-trash"></i> Cabut Akses
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>

                            <!-- Edit Teacher Modal -->
                            <div class="modal fade" id="modal-edit-teacher-<?= $t['id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content border-0 shadow rounded-lg">
                                        <div class="modal-header border-bottom">
                                            <h5 class="modal-title font-weight-bold text-dark">Edit Data Guru / Whitelist</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form action="<?= base_url('/allowed-numbers/update/' . $t['id']) ?>" method="post">
                                            <?= csrf_field() ?>
                                            <div class="modal-body">
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-secondary">Nama Lengkap</label>
                                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($t['name']) ?>" required>
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-secondary">Nomor WhatsApp</label>
                                                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($t['phone']) ?>" placeholder="Contoh: 628xxxxx" required>
                                                    <small class="text-muted">Masukkan format internasional tanpa simbol (contoh: 6285334xxxx).</small>
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-secondary">Tugas Tambahan (Opsional)</label>
                                                    <input type="text" name="tugas_tambahan" class="form-control" value="<?= htmlspecialchars($t['tugas_tambahan'] ?? '') ?>" placeholder="Contoh: Wali Kelas, Wakakur, Pembina Osis">
                                                </div>
                                                <div class="form-group mb-3">
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
</div>

<!-- Add Teacher Modal -->
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
                        <input type="text" name="tugas_tambahan" class="form-control" placeholder="Contoh: Wali Kelas, Wakakur, Pembina Osis">
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

<!-- Import Teacher Modal (v1.6.8 - Client-side Excel Parser) -->
<div class="modal fade" id="modal-import-teacher" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow rounded-lg">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-file-excel mr-2 text-success"></i> Import Data Guru via Excel</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2" style="font-size: 13px;">
                    <h6 class="font-weight-bold mb-1"><i class="fas fa-info-circle mr-1"></i>Format Kolom Excel:</h6>
                    Pastikan file Excel Anda (`.xlsx`, `.xls`, atau `.csv`) memiliki baris header dengan nama kolom berikut (urutan bebas):
                    <div class="font-weight-bold mt-1 text-monospace text-center">
                        [nama] &nbsp;|&nbsp; [whatsapp] &nbsp;|&nbsp; [tugas tambahan] &nbsp;|&nbsp; [role]
                    </div>
                    <ul class="mb-0 pl-3 mt-1">
                        <li><strong>whatsapp</strong>: Nomor telepon/WhatsApp guru (misal: <code>628123456789</code>).</li>
                        <li><strong>tugas tambahan</strong>: Opsional (misal: <code>Wali Kelas</code>, <code>Wakakur</code>, <code>Pembina Osis</code>). Ditulis manual.</li>
                        <li><strong>role</strong>: Isikan salah satu: <code>admin</code>, <code>kepsek</code>, <code>guru</code>, <code>guru_bk</code>, <code>guru_mapel</code>. (Default: <code>guru_mapel</code>).</li>
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
                    <h6 class="font-weight-bold text-dark mb-2"><i class="fas fa-eye mr-1"></i> Preview Data (Maks. 10 baris pertama)</h6>
                    <div class="table-responsive border rounded mb-3" style="max-height: 250px;">
                        <table class="table table-sm table-striped mb-0" style="font-size: 12px;">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th>Nama Guru</th>
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

<script>
document.addEventListener("DOMContentLoaded", function() {
    let parsedTeachers = [];

    const fileInput = document.getElementById('teacherExcelFile');
    const previewContainer = document.getElementById('teacherPreviewContainer');
    const previewBody = document.getElementById('teacherPreviewBody');
    const totalRowsText = document.getElementById('teacherTotalRowsText');
    const btnSubmit = document.getElementById('btnSubmitTeacherImport');

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Update file input label
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
                    resetImportForm();
                    return;
                }

                parsedTeachers = [];
                previewBody.innerHTML = '';

                jsonData.forEach((row, index) => {
                    // Map headers dynamically (case-insensitive)
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

                        // Show preview for first 10 rows
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
                    alert("Data tidak valid. Pastikan ada kolom nama/name dan whatsapp/phone.");
                    resetImportForm();
                    return;
                }

                totalRowsText.innerText = parsedTeachers.length;
                previewContainer.classList.remove('d-none');
                btnSubmit.disabled = false;

            } catch (err) {
                console.error(err);
                alert("Gagal membaca file Excel. Hubungi Admin.");
                resetImportForm();
            }
        };
        reader.readAsArrayBuffer(file);
    });

    btnSubmit.addEventListener('click', function() {
        if (parsedTeachers.length === 0) return;

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';

        // Read CSRF token from meta tag (rendered by PHP)
        const csrfToken = document.querySelector('meta[name="csrf-hash"]').getAttribute('content');

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
                location.reload();
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
});
</script>

<?= $this->endSection() ?>
