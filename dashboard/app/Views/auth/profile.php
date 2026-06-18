<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid">
        <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Profil Pengguna</h1>
        <p class="text-secondary mb-0">Kelola informasi profil pribadi Anda dan ganti kata sandi keamanan Anda.</p>
    </div>
</div>

<div class="row">
    <!-- User Information Card -->
    <div class="col-md-5 mb-4">
        <div class="card h-100 border-0 shadow-sm rounded-lg overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom">
                <h3 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-user-circle mr-2 text-primary"></i> Detail Akun
                </h3>
            </div>
            <div class="card-body d-flex flex-column align-items-center text-center pt-4">
                <!-- User Avatar/Icon -->
                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 100px; height: 100px; border: 3px solid #e2e8f0;">
                    <i class="fas fa-user-graduate text-success" style="font-size: 48px;"></i>
                </div>
                
                <h4 class="font-weight-bold text-dark mb-1"><?= htmlspecialchars(session()->get('name') ?? 'User') ?></h4>
                <div class="mb-4">
                    <?php
                    $role = session()->get('role');
                    $badgeClass = 'badge-secondary';
                    $roleLabel = 'Siswa';
                    if ($role === 'admin') { $badgeClass = 'badge-danger'; $roleLabel = 'Admin'; }
                    elseif ($role === 'kepsek') { $badgeClass = 'badge-dark'; $roleLabel = 'Kepala Sekolah'; }
                    elseif ($role === 'guru_bk') { $badgeClass = 'badge-warning'; $roleLabel = 'Guru BK'; }
                    elseif ($role === 'guru') { $badgeClass = 'badge-info'; $roleLabel = 'Guru'; }
                    elseif ($role === 'guru_mapel') { $badgeClass = 'badge-info'; $roleLabel = 'Guru Mapel'; }
                    elseif ($role === 'ketua_pkl') { $badgeClass = 'badge-success'; $roleLabel = 'Ketua PKL'; }
                    elseif ($role === 'anggotapkl') { $badgeClass = 'badge-primary'; $roleLabel = 'Anggota PKL'; }
                    ?>
                    <span class="badge <?= $badgeClass ?> px-3 py-2 text-white font-weight-bold rounded-pill" style="font-size: 13px;">
                        <?= $roleLabel ?>
                    </span>
                </div>

                <div class="w-100 px-3 border-top pt-3 text-left">
                    <div class="row mb-2">
                        <div class="col-5 text-secondary font-weight-bold">WhatsApp / HP</div>
                        <div class="col-7 text-dark font-weight-bold"><?= htmlspecialchars(session()->get('phone') ?: '-') ?></div>
                    </div>
                    <?php if (session()->get('user_type') === 'student'): ?>
                    <div class="row mb-2">
                        <div class="col-5 text-secondary font-weight-bold">Kelas</div>
                        <div class="col-7 text-dark font-weight-bold"><?= htmlspecialchars(session()->get('class') ?: '-') ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="row mb-2">
                        <div class="col-5 text-secondary font-weight-bold">Status Login</div>
                        <div class="col-7 text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Aktif</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Card -->
    <div class="col-md-7 mb-4">
        <div class="card h-100 border-0 shadow-sm rounded-lg">
            <div class="card-header bg-white py-3 border-bottom">
                <h3 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-key mr-2 text-warning"></i> Ganti Password
                </h3>
            </div>
            <form action="<?= base_url('/profile/change-password') ?>" method="post" id="form-change-password">
                <?= csrf_field() ?>
                <div class="card-body">
                    <?php if (session()->get('force_change_password')): ?>
                    <div class="alert alert-warning py-2 rounded-lg border-0 mb-3" style="font-size: 13px;">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Sebagai <strong>Ketua PKL</strong>, Anda wajib mengganti password default (NISN) demi alasan keamanan laporan kelompok Anda.
                    </div>
                    <?php endif; ?>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Password Lama / Saat Ini</label>
                        <div class="input-group">
                            <input type="password" name="oldPassword" id="oldPassword" class="form-control" placeholder="Masukkan password saat ini" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassVisibility('oldPassword', 'toggleIconOld')">
                                    <i class="fas fa-eye" id="toggleIconOld"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Password Baru</label>
                        <div class="input-group">
                            <input type="password" name="newPassword" id="newPassword" class="form-control" placeholder="Minimal 6 karakter" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassVisibility('newPassword', 'toggleIconNew')">
                                    <i class="fas fa-eye" id="toggleIconNew"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-secondary">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <input type="password" name="confirmPassword" id="confirmPassword" class="form-control" placeholder="Ulangi password baru" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassVisibility('confirmPassword', 'toggleIconConfirm')">
                                    <i class="fas fa-eye" id="toggleIconConfirm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top text-right py-3">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save mr-2"></i> Perbarui Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

document.getElementById('form-change-password').addEventListener('submit', function(e) {
    const newPass = document.getElementById('newPassword').value;
    const confirmPass = document.getElementById('confirmPassword').value;

    if (newPass.length < 6) {
        e.preventDefault();
        alert('Password baru minimal harus 6 karakter.');
        return false;
    }

    if (newPass !== confirmPass) {
        e.preventDefault();
        alert('Konfirmasi password baru tidak cocok.');
        return false;
    }
});
</script>
<?= $this->endSection() ?>
