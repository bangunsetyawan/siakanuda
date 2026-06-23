<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Manajemen Data Siswa</h1>
            <p class="text-secondary mb-0">Kelola informasi data induk siswa dan tentukan hak akses peran.</p>
        </div>
        <?php if (in_array(session()->get('role'), ['admin', 'kepsek', 'guru_bk'])): ?>
        <div>
            <button class="btn btn-outline-info mr-2" id="btnExportStudent">
                <i class="fas fa-download mr-2"></i> Download Data Siswa
            </button>
            <button class="btn btn-outline-success mr-2" data-toggle="modal" data-target="#modal-import-student">
                <i class="fas fa-file-excel mr-2"></i> Import Excel
            </button>
            <button class="btn btn-primary" data-toggle="modal" data-target="#modal-add-student">
                <i class="fas fa-plus mr-2"></i> Tambah Siswa
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-users mr-2 text-primary"></i> Daftar Siswa</h3>
        <div class="card-tools ml-auto">
            <form action="" method="get" class="form-inline">
                <div class="custom-control custom-switch mr-3">
                    <input type="checkbox" name="show_inactive" value="1" class="custom-control-input" id="switchShowInactive" <?= ($showInactive ?? false) ? 'checked' : '' ?> onchange="this.form.submit()">
                    <label class="custom-control-label font-weight-bold text-secondary" for="switchShowInactive" style="font-size: 13px; cursor: pointer;">Tampilkan Siswa Nonaktif</label>
                </div>
                <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Cari Nama/Kelas/NISN" value="<?= htmlspecialchars($search ?? '') ?>">
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
                        <th class="border-0">NISN</th>
                        <th class="border-0">Nama Lengkap</th>
                        <th class="border-0">Kelas</th>
                        <th class="border-0">Gender</th>
                        <th class="border-0">WA Siswa</th>
                        <th class="border-0">WA Ortu</th>
                        <th class="border-0">Peran</th>
                        <th class="border-0">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-secondary py-5">
                                <i class="fas fa-user-slash mb-2" style="font-size: 32px;"></i>
                                <p class="mb-0">Belum ada data siswa ditemukan.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $i = 1; foreach ($students as $student): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><code class="text-dark font-weight-bold"><?= htmlspecialchars($student['nis'] ?: '-') ?></code></td>
                                <td class="font-weight-bold text-dark">
                                    <?= htmlspecialchars($student['name']) ?>
                                    <?php if (isset($student['is_active']) && $student['is_active'] == 0): ?>
                                        <span class="badge badge-danger ml-1" style="font-size: 10px;">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge badge-light px-2 py-1 font-weight-bold text-secondary"><?= htmlspecialchars($student['class']) ?></span></td>
                                <td><?= htmlspecialchars($student['gender'] == 'L' ? 'Laki-laki' : ($student['gender'] == 'P' ? 'Perempuan' : '-')) ?></td>
                                <td><?= htmlspecialchars($student['phone'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($student['orang_tua_phone'] ?: '-') ?></td>
                                <td>
                                    <?php if (($student['role'] ?? 'siswa') === 'ketua_pkl'): ?>
                                        <span class="badge badge-primary px-2 py-1">Ketua PKL</span>
                                    <?php elseif (($student['role'] ?? 'siswa') === 'anggotapkl'): ?>
                                        <span class="badge badge-info px-2 py-1">Anggota PKL</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary px-2 py-1">Siswa</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $role = session()->get('role');
                                    $canWriteStudent = in_array($role, ['admin', 'kepsek', 'guru_bk']);
                                    $canDeleteStudent = in_array($role, ['admin', 'guru_bk']);
                                    ?>
                                    <div class="btn-group">
                                        <a href="<?= base_url('/students/details/' . $student['id']) ?>" class="btn btn-sm btn-outline-info mr-1 rounded">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                        <?php if ($canWriteStudent): ?>
                                            <button class="btn btn-sm btn-outline-primary mr-1 rounded" 
                                                    data-toggle="modal" 
                                                    data-target="#modal-edit-student-<?= $student['id'] ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <form action="<?= base_url('/students/reset-password/' . $student['id']) ?>" method="post" class="d-inline mr-1" onsubmit="return confirm('Apakah Anda yakin ingin mereset password siswa ini ke default (NISN)?')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-warning rounded" title="Reset Password">
                                                    <i class="fas fa-key"></i> Reset
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($canDeleteStudent): ?>
                                            <form action="<?= base_url('/students/delete/' . $student['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data siswa ini?')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Student Modal -->
                            <div class="modal fade" id="modal-edit-student-<?= $student['id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content border-0 shadow rounded-lg">
                                        <div class="modal-header border-bottom">
                                            <h5 class="modal-title font-weight-bold text-dark">Edit Data Siswa</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form action="<?= base_url('/students/update/' . $student['id']) ?>" method="post">
                                            <?= csrf_field() ?>
                                            <div class="modal-body">
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-secondary">NISN</label>
                                                    <input type="text" name="nis" class="form-control" value="<?= htmlspecialchars($student['nis']) ?>" required>
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-secondary">Nama Lengkap</label>
                                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($student['name']) ?>" required>
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-secondary">Kelas</label>
                                                    <input type="text" name="class" class="form-control" value="<?= htmlspecialchars($student['class']) ?>" placeholder="Contoh: XII TKJ" required>
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-secondary">Jenis Kelamin</label>
                                                    <select name="gender" class="form-control" required>
                                                        <option value="L" <?= $student['gender'] == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                                        <option value="P" <?= $student['gender'] == 'P' ? 'selected' : '' ?>>Perempuan</option>
                                                    </select>
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-secondary">No. WhatsApp Siswa</label>
                                                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($student['phone']) ?>" placeholder="Contoh: 628xxxx">
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-secondary">No. WhatsApp Orang Tua</label>
                                                    <input type="text" name="orang_tua_phone" class="form-control" value="<?= htmlspecialchars($student['orang_tua_phone'] ?? '') ?>" placeholder="Contoh: 628xxxx">
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-secondary">Peran / Role</label>
                                                    <select name="role" class="form-control" required>
                                                        <option value="siswa" <?= ($student['role'] ?? 'siswa') === 'siswa' ? 'selected' : '' ?>>Siswa</option>
                                                        <option value="ketua_pkl" <?= ($student['role'] ?? 'siswa') === 'ketua_pkl' ? 'selected' : '' ?>>Ketua PKL</option>
                                                        <option value="anggotapkl" <?= ($student['role'] ?? 'siswa') === 'anggotapkl' ? 'selected' : '' ?>>Anggota PKL</option>
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

<!-- Add Student Modal -->
<div class="modal fade" id="modal-add-student" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow rounded-lg">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark">Tambah Siswa Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('/students/create') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">NISN</label>
                        <input type="text" name="nis" class="form-control" placeholder="Masukkan 10 digit NISN" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" placeholder="Masukkan Nama Lengkap" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Kelas</label>
                        <input type="text" name="class" class="form-control" placeholder="Contoh: X TKJ / XI AKL" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Jenis Kelamin</label>
                        <select name="gender" class="form-control" required>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                     <div class="form-group mb-3">
                         <label class="font-weight-bold text-secondary">No. WhatsApp Siswa</label>
                         <input type="text" name="phone" class="form-control" placeholder="Contoh: 628xxxxx">
                     </div>
                     <div class="form-group mb-3">
                         <label class="font-weight-bold text-secondary">No. WhatsApp Orang Tua</label>
                         <input type="text" name="orang_tua_phone" class="form-control" placeholder="Contoh: 628xxxxx">
                     </div>
                     <div class="form-group mb-3">
                         <label class="font-weight-bold text-secondary">Peran / Role</label>
                         <select name="role" class="form-control" required>
                             <option value="siswa" selected>Siswa</option>
                             <option value="ketua_pkl">Ketua PKL</option>
                             <option value="anggotapkl">Anggota PKL</option>
                         </select>
                     </div>
                 </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Siswa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Student Modal (v1.6.8 - Client-side Excel Parser) -->
<div class="modal fade" id="modal-import-student" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow rounded-lg">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-file-excel mr-2 text-success"></i> Import Data Siswa via Excel</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2" style="font-size: 13px;">
                    <h6 class="font-weight-bold mb-1"><i class="fas fa-info-circle mr-1"></i>Format Kolom Excel:</h6>
                    Pastikan file Excel Anda (`.xlsx`, `.xls`, atau `.csv`) memiliki baris header dengan nama kolom berikut (urutan bebas):
                    <div class="font-weight-bold mt-1 text-monospace">
                        [nisn] &nbsp;|&nbsp; [nama] &nbsp;|&nbsp; [kelas] &nbsp;|&nbsp; [gender] &nbsp;|&nbsp; [whatsapp] &nbsp;|&nbsp; [ortu_wa] &nbsp;|&nbsp; [role]
                    </div>
                    <ul class="mb-0 pl-3 mt-1">
                        <li><strong>gender</strong>: Isikan <code>L</code> untuk Laki-laki, <code>P</code> untuk Perempuan.</li>
                        <li><strong>role</strong>: Opsional. Isikan <code>siswa</code> atau <code>ketua_pkl</code>. Defaultnya adalah <code>siswa</code> jika kosong.</li>
                        <li><strong>whatsapp</strong>: Opsional (misal: <code>628123456789</code>).</li>
                        <li><strong>ortu_wa</strong>: Opsional (No. WA Orang Tua).</li>
                    </ul>
                    <div class="mt-2 text-right">
                        <a href="<?= base_url('/templates/template_siswa.xlsx') ?>" class="btn btn-xs btn-success text-white font-weight-bold" download>
                            <i class="fas fa-download mr-1"></i> Download Template Excel
                        </a>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark">Pilih File Excel / CSV</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="studentExcelFile" accept=".xlsx, .xls, .csv">
                        <label class="custom-file-label" for="studentExcelFile">Pilih file...</label>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="chkDeactivateMissing" name="deactivate_missing">
                        <label class="custom-control-label font-weight-bold text-dark" for="chkDeactivateMissing" style="cursor: pointer;">
                            Nonaktifkan siswa yang NISN-nya tidak ada di file Excel (untuk transisi ajaran baru)
                        </label>
                    </div>
                </div>

                <!-- Preview Area -->
                <div id="studentPreviewContainer" class="d-none">
                    <h6 class="font-weight-bold text-dark mb-2"><i class="fas fa-eye mr-1"></i> Preview Data (Maks. 10 baris pertama)</h6>
                    <div class="table-responsive border rounded mb-3" style="max-height: 250px;">
                        <table class="table table-sm table-striped mb-0" style="font-size: 12px;">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th>NISN</th>
                                    <th>Nama</th>
                                    <th>Kelas</th>
                                    <th>Gender</th>
                                    <th>WA Siswa</th>
                                    <th>WA Ortu</th>
                                    <th>Role</th>
                                </tr>
                            </thead>
                            <tbody id="studentPreviewBody"></tbody>
                        </table>
                    </div>
                    <div class="text-right text-secondary mb-3" style="font-size: 11px;">
                        Total data terdeteksi: <strong id="studentTotalRowsText" class="text-dark">0</strong> baris
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnSubmitStudentImport" disabled>
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
    let parsedStudents = [];

    const fileInput = document.getElementById('studentExcelFile');
    const previewContainer = document.getElementById('studentPreviewContainer');
    const previewBody = document.getElementById('studentPreviewBody');
    const totalRowsText = document.getElementById('studentTotalRowsText');
    const btnSubmit = document.getElementById('btnSubmitStudentImport');

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

                
                parsedStudents = [];
                previewBody.innerHTML = '';
                
                const rawData = XLSX.utils.sheet_to_json(worksheet, { defval: "" });
                
                rawData.forEach(row => {
                    let nis = '', name = '', className = '', gender = 'L', phone = '', orangTuaPhone = '', role = 'siswa';
                    
                    Object.keys(row).forEach(key => {
                        const cleanKey = key.toLowerCase().trim();
                        const val = String(row[key] ?? '').trim();
                        
                        if (cleanKey === 'nisn' || cleanKey === 'nis') nis = val;
                        else if (cleanKey === 'nama' || cleanKey === 'name' || cleanKey === 'nama lengkap') name = val;
                        else if (cleanKey === 'kelas' || cleanKey === 'class') className = val;
                        else if (cleanKey === 'gender' || cleanKey === 'jenis kelamin' || cleanKey === 'jk') gender = val;
                        else if (cleanKey === 'whatsapp' || cleanKey === 'phone' || cleanKey === 'no hp' || cleanKey === 'no. whatsapp') phone = val;
                        else if (cleanKey === 'ortu_wa' || cleanKey === 'wa ortu' || cleanKey === 'orang_tua_phone') orangTuaPhone = val;
                        else if (cleanKey === 'role' || cleanKey === 'peran') role = val;
                    });

                    if (nis && name && className) {
                        const studentObj = { nis, name, class: className, gender, phone, orang_tua_phone: orangTuaPhone, role };
                        parsedStudents.push(studentObj);

                        // Show preview for first 10 rows
                        if (parsedStudents.length <= 10) {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td><code>${escapeHtml(nis)}</code></td>
                                <td><strong>${escapeHtml(name)}</strong></td>
                                <td><span class="badge badge-light border text-secondary">${escapeHtml(className)}</span></td>
                                <td>${escapeHtml(gender)}</td>
                                <td>${escapeHtml(phone || '-')}</td>
                                <td>${escapeHtml(orangTuaPhone || '-')}</td>
                                <td><span class="badge badge-secondary">${escapeHtml(role)}</span></td>
                            `;
                            previewBody.appendChild(tr);
                        }
                    }
                });

                if (parsedStudents.length === 0) {
                    alert("Data tidak valid. Pastikan ada kolom nisn/nis, nama/name, dan kelas/class.");
                    resetImportForm();
                    return;
                }

                totalRowsText.innerText = parsedStudents.length;
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
        if (parsedStudents.length === 0) return;

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';

        // Read CSRF token from meta tag (rendered by PHP)
        const csrfToken = document.querySelector('meta[name="csrf-hash"]').getAttribute('content');
        const deactivateMissing = document.getElementById('chkDeactivateMissing').checked;

        fetch('/students/import', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ 
                students: parsedStudents,
                deactivate_missing: deactivateMissing
            })
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

    const btnExport = document.getElementById('btnExportStudent');
    if (btnExport) {
        btnExport.addEventListener('click', function() {
            btnExport.disabled = true;
            btnExport.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Downloading...';
            
            fetch('/students/export')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'error') {
                        alert("Error: " + data.message);
                        return;
                    }
                    
                    // Format keys to user-friendly headers matching import
                    const formattedData = data.map(s => ({
                        'nisn': s.nis,
                        'nama': s.name,
                        'kelas': s.class,
                        'gender': s.gender,
                        'whatsapp': s.phone || '',
                        'ortu_wa': s.orang_tua_phone || '',
                        'role': s.role || 'siswa'
                    }));
                    
                    // Generate workbook & worksheet
                    const worksheet = XLSX.utils.json_to_sheet(formattedData);
                    const workbook = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(workbook, worksheet, "Siswa");
                    
                    // Write file
                    XLSX.writeFile(workbook, "data_siswa.xlsx");
                })
                .catch(err => {
                    console.error(err);
                    alert("Gagal mengunduh data siswa.");
                })
                .finally(() => {
                    btnExport.disabled = false;
                    btnExport.innerHTML = '<i class="fas fa-download mr-2"></i> Download Data Siswa';
                });
        });
    }

    function resetImportForm() {
        fileInput.value = '';
        fileInput.nextElementSibling.innerText = 'Pilih file...';
        previewContainer.classList.add('d-none');
        previewBody.innerHTML = '';
        parsedStudents = [];
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
