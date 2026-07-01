<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="theme-color" content="#1a5632">
    <link rel="icon" type="image/png" href="/logo-smk.png">
    <link rel="apple-touch-icon" href="/logo-smk.png">
    <link rel="manifest" href="/manifest.json">
    <title>Login | SIAKANUDA</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --primary: #0e7a52;
            --primary-light: #12a06b;
            --primary-dark: #065f3e;
            --accent: #0284c7;
            --surface: rgba(255, 255, 255, 0.88);
            --surface-border: rgba(255, 255, 255, 0.55);
            --text-main: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --bg-start: #f0fdf4;
            --bg-end: #ecfeff;
            --input-border: #cbd5e1;
            --input-focus: #10b981;
            --radius-lg: 1.25rem;
            --radius-md: 0.75rem;
        }

        html {
            font-size: 100%; /* 16px base */
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(160deg, var(--bg-start) 0%, var(--bg-end) 50%, #f8fafc 100%);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            padding: 1.5rem 1rem;
            -webkit-font-smoothing: antialiased;
        }

        /* === Mesh Glow Circles === */
        .glow-circle {
            position: absolute;
            border-radius: 50%;
            filter: blur(5rem);
            z-index: 1;
            pointer-events: none;
        }
        .glow-1 {
            width: 18rem;
            height: 18rem;
            background: var(--primary);
            opacity: 0.07;
            top: 5%;
            left: -5%;
            animation: drift1 12s ease-in-out infinite;
        }
        .glow-2 {
            width: 22rem;
            height: 22rem;
            background: var(--accent);
            opacity: 0.06;
            bottom: 5%;
            right: -8%;
            animation: drift2 14s ease-in-out infinite;
        }
        .glow-3 {
            width: 14rem;
            height: 14rem;
            background: #f59e0b;
            opacity: 0.05;
            top: 45%;
            right: 20%;
            animation: drift3 10s ease-in-out infinite;
        }

        @keyframes drift1 {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(1rem, -1.5rem); }
        }
        @keyframes drift2 {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-1.2rem, 1rem); }
        }
        @keyframes drift3 {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(0.8rem, 1.2rem); }
        }

        /* === Login Card (Glass) === */
        .login-card {
            position: relative;
            z-index: 10;
            background: var(--surface);
            backdrop-filter: blur(1.25rem);
            -webkit-backdrop-filter: blur(1.25rem);
            border: 1px solid var(--surface-border);
            border-radius: 1.5rem;
            box-shadow:
                0 0.625rem 2.5rem -0.625rem rgba(0, 0, 0, 0.06),
                0 0 0 1px rgba(255, 255, 255, 0.4) inset;
            width: 100%;
            max-width: 22.5rem; /* 360px */
            padding: 2rem 1.5rem;
            transition: box-shadow 0.3s ease;
        }
        .login-card:hover {
            box-shadow:
                0 1.25rem 3.125rem -0.625rem rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.4) inset;
        }

        /* === Logo & Brand === */
        .brand-area {
            text-align: center;
            margin-bottom: 1.25rem;
        }
        .logo-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 5rem;
            height: 5rem;
            border-radius: 50%;
            background: #ffffff;
            box-shadow:
                0 0.25rem 1rem rgba(14, 122, 82, 0.12),
                0 0 0 0.25rem rgba(14, 122, 82, 0.06);
            margin-bottom: 1rem;
            animation: floatLogo 5s ease-in-out infinite;
        }
        .logo-wrapper img {
            width: 3.5rem;
            height: 3.5rem;
            object-fit: contain;
        }

        @keyframes floatLogo {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-0.375rem); }
        }

        .brand-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.02em;
            margin-bottom: 0.125rem;
        }
        .brand-title span {
            color: var(--primary);
        }
        .brand-subtitle {
            font-size: 0.8125rem;
            color: var(--text-secondary);
            line-height: 1.4;
            font-weight: 400;
        }

        /* === Instruction Badge === */
        .badge-instruction {
            background: linear-gradient(135deg, #ecfdf5, #f0fdf4);
            color: var(--primary-dark);
            border: 1px solid rgba(14, 122, 82, 0.1);
            border-radius: var(--radius-md);
            padding: 0.625rem 0.875rem;
            font-size: 0.8125rem;
            font-weight: 500;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .badge-instruction i {
            font-size: 0.875rem;
            flex-shrink: 0;
        }

        /* === Alert Messages === */
        .alert {
            border-radius: var(--radius-md);
            border: none;
            padding: 0.75rem 1rem;
            font-size: 0.8125rem;
            font-weight: 500;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
        }
        .alert-success {
            background: #f0fdf4;
            color: #166534;
        }

        /* === Form Styles === */
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group:last-of-type {
            margin-bottom: 1.25rem;
        }
        .form-label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 0.375rem;
        }
        .form-control {
            width: 100%;
            height: 2.875rem;
            padding: 0 1rem;
            font-size: 0.875rem;
            font-family: inherit;
            color: var(--text-main);
            background: #ffffff;
            border: 1px solid var(--input-border);
            border-radius: var(--radius-md);
            outline: none;
            transition: border-color 0.25s, box-shadow 0.25s;
        }
        .form-control::placeholder {
            color: var(--text-muted);
        }
        .form-control:focus {
            border-color: var(--input-focus);
            box-shadow: 0 0 0 0.1875rem rgba(16, 185, 129, 0.12);
        }

        /* === Password Field === */
        .password-container {
            position: relative;
        }
        .password-container .form-control {
            padding-right: 2.75rem;
        }
        .password-toggle {
            position: absolute;
            right: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-muted);
            font-size: 0.9375rem;
            transition: color 0.2s;
            padding: 0.25rem;
            background: none;
            border: none;
        }
        .password-toggle:hover {
            color: var(--text-secondary);
        }

        /* === Login Button === */
        .btn-login {
            width: 100%;
            height: 2.875rem;
            border: none;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: #ffffff;
            font-family: inherit;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 0.25rem 0.75rem rgba(14, 122, 82, 0.2);
            position: relative;
            overflow: hidden;
        }
        .btn-login::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent 0%, rgba(255,255,255,0.1) 100%);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-0.0625rem);
            box-shadow: 0 0.5rem 1.25rem rgba(14, 122, 82, 0.28);
        }
        .btn-login:hover::after {
            opacity: 1;
        }
        .btn-login:active {
            transform: translateY(0);
            box-shadow: 0 0.125rem 0.5rem rgba(14, 122, 82, 0.2);
        }

        /* === Divider === */
        .divider {
            display: flex;
            align-items: center;
            margin: 1.25rem 0;
            gap: 0.75rem;
        }
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(0, 0, 0, 0.08);
        }
        .divider span {
            font-size: 0.6875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            color: var(--text-muted);
        }

        /* === Footer === */
        .login-footer {
            text-align: center;
            margin-top: 1.25rem;
        }
        .login-footer p {
            font-size: 0.6875rem;
            color: var(--text-muted);
            letter-spacing: 0.01em;
        }

        /* === Responsive (larger screens) === */
        @media (min-width: 48em) {
            .login-card {
                padding: 2.5rem 2rem;
            }
        }

        /* === Login Tabs & Helpers === */
        .login-tabs {
            display: flex;
            background: rgba(0, 0, 0, 0.04);
            border-radius: var(--radius-md);
            padding: 0.25rem;
            margin-bottom: 1.25rem;
            border: 1px solid rgba(0, 0, 0, 0.03);
        }
        .tab-btn {
            flex: 1;
            border: none;
            background: transparent;
            padding: 0.625rem 0.5rem;
            font-size: 0.8125rem;
            font-weight: 600;
            font-family: inherit;
            color: var(--text-secondary);
            border-radius: calc(var(--radius-md) - 0.125rem);
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
        }
        .tab-btn.active {
            background: var(--primary);
            color: #ffffff;
            box-shadow: 0 0.125rem 0.5rem rgba(14, 122, 82, 0.2);
        }
        .d-none {
            display: none !important;
        }
    </style>
</head>
<body>

    <!-- Glow background elements -->
    <div class="glow-circle glow-1"></div>
    <div class="glow-circle glow-2"></div>
    <div class="glow-circle glow-3"></div>

    <div class="login-card">
        <!-- Brand Area with School Logo -->
        <div class="brand-area">
            <div class="logo-wrapper">
                <img src="<?= base_url('/logo-smk.png') ?>" alt="Logo SMK NU Darussalam">
            </div>
            <h1 class="brand-title">SIAKA<span>NUDA</span></h1>
            <p class="brand-subtitle">Sistem Informasi Akademik dan Kesiswaan<br>SMK NU Darussalam</p>
        </div>

        <div class="badge-instruction">
            <i class="fas fa-lock"></i>
            <span>Silakan Login untuk mengakses halaman utama.</span>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= session()->getFlashdata('error') ?></span>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle"></i>
                <span><?= session()->getFlashdata('success') ?></span>
            </div>
        <?php endif; ?>

        <!-- Tabs Section -->
        <div class="login-tabs">
            <button type="button" class="tab-btn active" onclick="switchTab('siswa')">
                <i class="fas fa-user-graduate"></i> Siswa
            </button>
            <button type="button" class="tab-btn" onclick="switchTab('guru')">
                <i class="fas fa-chalkboard-teacher"></i> Guru / Staf
            </button>
        </div>

        <form action="<?= base_url('/login') ?>" method="post" autocomplete="off">
            <?= csrf_field() ?>
            <input type="hidden" name="login_type" id="login_type" value="siswa">
            
            <!-- Honeypot untuk mengecoh Google Password Manager yang bandel -->
            <input type="text" style="width:0;height:0;position:absolute;visibility:hidden;opacity:0" autocomplete="username">
            <input type="password" style="width:0;height:0;position:absolute;visibility:hidden;opacity:0" autocomplete="current-password">
            
            <!-- Siswa Username (NISN) Input -->
            <div class="form-group" id="username-siswa-container">
                <label class="form-label" for="username-siswa">NISN Siswa</label>
                <input type="text" name="username_siswa" id="username-siswa" class="form-control" placeholder="Masukkan 10 digit NISN Anda" value="<?= old('username_siswa') ?>" required autocomplete="off">
            </div>

            <!-- Guru/Staf Dropdown Select -->
            <div class="form-group d-none" id="username-guru-container">
                <label class="form-label" for="username-guru">Pilih Nama Guru / Staf</label>
                <select id="username-guru" name="username_guru" class="form-control" style="background-image: none;">
                    <option value="" disabled selected>-- Pilih Nama Anda --</option>
                    <?php if (!empty($teachers)): ?>
                        <?php foreach ($teachers as $t): ?>
                            <?php 
                                $rLabel = 'Guru';
                                if ($t['role'] === 'admin') $rLabel = 'Admin';
                                elseif ($t['role'] === 'kepsek') $rLabel = 'Kepala Sekolah';
                                elseif ($t['role'] === 'guru_bk') $rLabel = 'Guru BK';
                                elseif ($t['role'] === 'guru_mapel') $rLabel = 'Guru Mapel';
                            ?>
                            <option value="<?= htmlspecialchars($t['phone']) ?>"><?= htmlspecialchars($t['name']) ?> (<?= $rLabel ?>)</option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password *</label>
                <div class="password-container">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan Password Anda" required autocomplete="new-password">
                    <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Toggle password visibility">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="btn-login">Login Masuk</button>
        </form>

        <div style="text-align: center; margin-top: 15px;">
            <a href="/bantuan" style="font-size: 0.8125rem; font-weight: 600; color: var(--primary); text-decoration: none;">
                <i class="fas fa-book-open"></i> Bingung cara pakai? Baca Buku Panduan
            </a>
        </div>



        <div class="login-footer">
            <p>Dikembangkan Oleh Tim TKJ SMKNUDA &copy; 2026</p>
        </div>
    </div>

    <script>
        function togglePassword() {
            var passField = document.getElementById("password");
            var toggleIcon = document.getElementById("toggleIcon");
            if (passField.type === "password") {
                passField.type = "text";
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
            } else {
                passField.type = "password";
                toggleIcon.classList.remove("fa-eye-slash");
                toggleIcon.classList.add("fa-eye");
            }
        }

        function switchTab(role) {
            const btnSiswa = document.querySelector('.tab-btn[onclick*="siswa"]');
            const btnGuru = document.querySelector('.tab-btn[onclick*="guru"]');
            const containerSiswa = document.getElementById('username-siswa-container');
            const containerGuru = document.getElementById('username-guru-container');
            const inputSiswa = document.getElementById('username-siswa');
            const selectGuru = document.getElementById('username-guru');
            const loginType = document.getElementById('login_type');

            if (role === 'siswa') {
                btnSiswa.classList.add('active');
                btnGuru.classList.remove('active');
                containerSiswa.classList.remove('d-none');
                containerGuru.classList.add('d-none');
                inputSiswa.required = true;
                selectGuru.required = false;
                if (loginType) loginType.value = 'siswa';
            } else {
                btnGuru.classList.add('active');
                btnSiswa.classList.remove('active');
                containerGuru.classList.remove('d-none');
                containerSiswa.classList.add('d-none');
                selectGuru.required = true;
                inputSiswa.required = false;
                if (loginType) loginType.value = 'guru';
            }
        }

        // Auto-restore tab after validation failure
        document.addEventListener('DOMContentLoaded', function() {
            const lastTab = "<?= old('login_type', 'siswa') ?>";
            if (lastTab === 'guru') {
                switchTab('guru');
                // Restore selected guru if any
                const lastUsername = "<?= old('username_guru') ?>";
                if (lastUsername) {
                    const select = document.getElementById('username-guru');
                    if (select) select.value = lastUsername;
                }
            } else {
                switchTab('siswa');
            }
        });
    </script>
</body>
</html>

