<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token-name" content="<?= csrf_token() ?>">
    <meta name="csrf-hash" content="<?= csrf_hash() ?>">
    <meta name="theme-color" content="#1a5632">
    <meta name="description" content="Sistem Informasi Akademik SMK NU Darussalam">
    <link rel="icon" type="image/png" href="/logo-smk.png">
    <link rel="apple-touch-icon" href="/logo-smk.png">
    <link rel="manifest" href="/manifest.json">
    <title><?= $title ?? 'SIAKANUDA' ?> | SMK NU Darussalam</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style (AdminLTE) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <!-- Premium Custom Styles (Light Theme Redesign) -->
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Source Sans Pro', sans-serif;
        }

        /* Solid Navbar (Performance Optimized) */
        .main-header {
            background-color: #ffffff !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08) !important;
        }

        /* Premium Light Sidebar */
        .main-sidebar {
            background-color: #ffffff !important;
            border-right: 1px solid rgba(0, 0, 0, 0.08);
        }
        .brand-link {
            border-bottom: 1px solid rgba(0, 0, 0, 0.08) !important;
            background-color: #ffffff !important;
            color: #1e293b !important;
            font-weight: 700;
        }
        .sidebar {
            background-color: #ffffff !important;
        }
        .nav-sidebar .nav-link {
            color: #475569 !important;
            border-radius: 4px;
            margin-bottom: 4px;
        }
        .nav-sidebar .nav-link:hover {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
        }
        .nav-sidebar .nav-item.menu-open > .nav-link,
        .nav-sidebar .nav-link.active {
            background-color: #e0f2fe !important;
            color: #0284c7 !important;
            font-weight: 600;
        }

        /* Card Polish (Lighter) */
        .card {
            border-radius: 12px;
            border: 1px solid rgba(0, 0, 0, 0.08);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            background: #ffffff;
        }
        .card-header {
            background-color: transparent !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
            padding: 18px 24px;
        }

        /* Buttons & Forms (Native AdminLTE/Bootstrap Layout Preserved) */
        .btn {
            border-radius: 4px;
            font-weight: 600;
        }
        @media (max-width: 576px) {
            /* Global Responsive Card Headers for Mobile */
            .card-header.d-flex {
                flex-direction: column !important;
                align-items: stretch !important;
                text-align: center;
                gap: 0.8rem;
                padding-bottom: 1rem;
            }
            .card-header.d-flex > h1, .card-header.d-flex > h2, .card-header.d-flex > h3, 
            .card-header.d-flex > h4, .card-header.d-flex > h5, .card-header.d-flex > h6 {
                text-align: center;
                justify-content: center;
                width: 100%;
            }
            .card-header.d-flex > div, .card-header.d-flex > form {
                display: flex;
                justify-content: center;
                flex-wrap: wrap;
                gap: 0.5rem;
                width: 100%;
            }
        }
        .btn-primary {
            background-color: #0284c7;
            border-color: #0284c7;
        }
        .btn-primary:hover {
            background-color: #0369a1;
            border-color: #0369a1;
        }
        .form-control {
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            padding: 10px 14px;
            height: 42px !important;
        }
        .form-control:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }
        select.form-control {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background: #ffffff url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23475569' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3E%3C/svg%3E") no-repeat right 14px center/12px 12px !important;
            padding-right: 36px !important;
        }
        .form-control-sm {
            height: 32px !important;
            padding: 4px 8px !important;
            font-size: 0.8125rem !important;
            border-radius: 6px !important;
        }
        select.form-control-sm {
            padding-right: 24px !important;
            background-position: right 8px center !important;
            background-size: 10px 10px !important;
        }
        /* Hide spin buttons globally for number inputs */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield;
        }

        /* Custom widgets */
        .small-box {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
            border: none;
        }

        /* Mobile Premium App Layout (< 768px) */
        @media (max-width: 767.98px) {
            html, body {
                overflow-x: hidden !important;
                width: 100% !important;
                position: relative;
            }
            .wrapper {
                overflow-x: hidden !important;
                width: 100% !important;
            }
            /* Sembunyikan toggle sidebar & sidebar container */
            .main-header .nav-item:has([data-widget="pushmenu"]) {
                display: none !important;
            }
            .main-sidebar {
                display: none !important;
            }
            /* Konten penuh tanpa margin sidebar */
            .content-wrapper, .main-header, .main-footer {
                margin-left: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                overflow-x: hidden !important;
            }
            .container-fluid, .container {
                width: 100% !important;
                max-width: 100% !important;
            }
            /* Margin bawah agar konten tidak tertimbun bottom nav */
            .content-wrapper {
                padding-bottom: 90px !important;
            }
            /* Sembunyikan footer di HP agar lega */
            .main-footer {
                display: none !important;
            }
            /* Beri bayangan lembut pada header atas dan sesuaikan padding */
            .main-header {
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03) !important;
                padding: 0 16px !important;
                display: flex !important;
                align-items: center !important;
                height: 56px !important;
            }

            /* Global Table & Container Responsiveness Override */
            .table-responsive {
                -webkit-overflow-scrolling: touch;
                overflow-x: auto !important;
                width: 100% !important;
                border: 0;
                margin-bottom: 0;
            }
            
            /* Allow tables to scroll horizontally naturally without squishing text */
            .table td, .table th {
                white-space: nowrap;
            }
            
            /* Exceptions for long text columns to wrap naturally */
            .table td.wrap-text, .table td p {
                white-space: normal;
                min-width: 200px;
            }

            /* Fix inline forms, date pickers, and search boxes for Mobile */
            .form-inline {
                display: flex !important;
                flex-direction: column !important;
                align-items: stretch !important;
                width: 100% !important;
                gap: 0.75rem;
            }
            .form-inline .form-control, .form-inline .custom-select, .form-inline .btn {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
            }
            .form-inline .custom-control {
                margin: 0 !important;
                align-self: flex-start !important;
            }
            
            /* Fix Alert and Callout Overflow */
            .alert, .callout, .info-box {
                overflow-wrap: break-word;
            }
        }

        /* Bottom Tab Navigation Bar */
        .mobile-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 64px;
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.05);
            border-top: 1px solid rgba(0, 0, 0, 0.06);
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 1040;
            padding-bottom: env(safe-area-inset-bottom);
        }
        .mobile-bottom-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #64748b !important;
            text-decoration: none !important;
            font-size: 0.7rem;
            font-weight: 600;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            flex: 1;
            height: 100%;
            padding-top: 4px;
        }
        .mobile-bottom-nav-item i {
            font-size: 1.25rem;
            margin-bottom: 2px;
            transition: transform 0.2s ease, color 0.2s ease;
        }
        .mobile-bottom-nav-item.active {
            color: #0284c7 !important;
        }
        .mobile-bottom-nav-item.active i {
            transform: scale(1.15) translateY(-1px);
            color: #0284c7 !important;
        }
    </style>

    <?= $this->renderSection('styles') ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Desktop Header Content (d-none d-md-flex) -->
        <div class="d-none d-md-flex w-100 justify-content-between align-items-center">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <?php if (uri_string() !== 'dashboard' && uri_string() !== ''): ?>
                    <li class="nav-item">
                        <a href="javascript:history.back()" class="nav-link text-primary font-weight-bold">
                            <i class="fas fa-chevron-left mr-1"></i> Kembali
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="<?= base_url('/dashboard') ?>" class="nav-link">Home</a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Center content: Active Academic Year -->
            <?php if (!empty($activeTahunPelajaran)): ?>
            <div class="d-flex align-items-center mx-auto text-muted font-weight-bold" style="font-size: 0.85rem; gap: 0.5rem;">
                <i class="fas fa-graduation-cap text-success"></i>
                <span>Dapodik: TP <?= esc($activeTahunPelajaran['nama']) ?></span>
            </div>
            <?php endif; ?>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link d-flex align-items-center" data-toggle="dropdown" href="#" style="gap: 0.5rem;">
                        <span class="text-dark font-weight-bold" style="font-size: 0.8125rem; max-width: 8rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= session()->get('name') ?: 'User' ?></span>
                        <span class="badge" style="background: linear-gradient(135deg, #0e7a52, #12a06b); color: #fff; font-size: 0.625rem; padding: 0.2rem 0.5rem; border-radius: 1rem; text-transform: uppercase; letter-spacing: 0.03em;"><?= session()->get('role') ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right rounded-lg border-0 shadow" style="min-width: 12rem;">
                        <a href="<?= base_url('/profile') ?>" class="dropdown-item">
                            <i class="fas fa-user-cog mr-2 text-primary"></i> Profil & Password
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?= base_url('/logout') ?>" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                        </a>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Mobile App Bar (d-flex d-md-none) -->
        <div class="mobile-app-bar d-flex d-md-none justify-content-between align-items-center w-100" style="height: 56px;">
            <!-- Left Side -->
            <div>
                <?php if (uri_string() !== 'dashboard' && uri_string() !== ''): ?>
                    <a href="javascript:history.back()" class="text-primary font-weight-bold d-flex align-items-center" style="font-size: 15px; text-decoration: none;">
                        <i class="fas fa-chevron-left mr-1" style="font-size: 18px;"></i> Kembali
                    </a>
                <?php else: ?>
                    <span class="font-weight-bold text-dark" style="font-size: 18px; letter-spacing: 0.5px;">
                        SIAKA<span class="text-success">NUDA</span>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Center (Page Title) -->
            <?php if (uri_string() !== 'dashboard' && uri_string() !== ''): ?>
                <div class="text-center font-weight-bold text-dark" style="font-size: 15px; position: absolute; left: 50%; transform: translateX(-50%); max-width: 50%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    <?= esc($title ?? 'SIAKANUDA') ?>
                </div>
            <?php endif; ?>

            <!-- Right Side: Big Touch-Friendly Logout Button -->
            <div>
                <a href="<?= base_url('/logout') ?>" class="text-danger d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 50%; background-color: rgba(220,53,69,0.06); text-decoration: none;" title="Keluar">
                    <i class="fas fa-sign-out-alt" style="font-size: 18px;"></i>
                </a>
            </div>
        </div>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-light-primary elevation-0">
        <!-- Brand Logo -->
        <a href="<?= base_url('/dashboard') ?>" class="brand-link d-flex justify-content-between align-items-center" style="padding: 0.625rem 0.75rem;">
            <span class="d-flex align-items-center">
                <img src="<?= base_url('/logo-smk.png') ?>" alt="Logo" style="width: 2.25rem; height: 2.25rem; object-fit: contain; margin-right: 0.5rem;">
                <span class="brand-text font-weight-light">SIAKA<strong class="text-success">NUDA</strong></span>
            </span>
            <!-- Close button for mobile sidebar -->
            <button class="btn btn-link text-secondary p-0 d-md-none" data-widget="pushmenu" style="font-size: 1rem; border: none; background: transparent; cursor: pointer;">
                <i class="fas fa-chevron-left mr-1"></i>
            </button>
        </a>

        <!-- Sidebar -->
        <div class="sidebar py-3">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <?php 
                    $role = session()->get('role');
                    $curUri = uri_string();
                    ?>
                    <li class="nav-item">
                        <a href="<?= base_url('/dashboard') ?>" class="nav-link <?= $curUri == 'dashboard' || $curUri == '' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <?php if (in_array($role, ['admin', 'kepsek', 'guru', 'guru_bk'])): ?>
                    <li class="nav-item">
                        <a href="<?= base_url('/analytics') ?>" class="nav-link <?= $curUri == 'analytics' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-chart-pie text-info"></i>
                            <p>Dashboard Analytics</p>
                        </a>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a href="<?= base_url('/kalender-akademik') ?>" class="nav-link <?= $curUri == 'kalender-akademik' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-calendar-week"></i>
                            <p>Kalender Akademik</p>
                        </a>
                    </li>

                    <?php if ($role === 'admin'): ?>
                    <li class="nav-item">
                        <a href="<?= base_url('/tahun-pelajaran') ?>" class="nav-link <?= $curUri == 'tahun-pelajaran' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-book-open"></i>
                            <p>Tahun Pelajaran</p>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (in_array($role, ['admin', 'kepsek', 'guru_bk', 'guru', 'guru_mapel'])): ?>
                    <li class="nav-item">
                        <a href="<?= base_url('/students') ?>" class="nav-link <?= $curUri == 'students' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Manajemen Siswa</p>
                        </a>
                    </li>
                    <?php endif; ?>



                    <?php if (in_array($role, ['admin', 'guru_bk', 'guru', 'guru_mapel', 'kepsek', 'siswa', 'ketua_pkl'])): ?>
                    <li class="nav-item">
                        <a href="<?= base_url('/schedules') ?>" class="nav-link <?= $curUri == 'schedules' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-calendar-alt"></i>
                            <p>Jadwal Pelajaran</p>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (in_array($role, ['admin', 'guru_bk', 'guru', 'guru_mapel', 'kepsek', 'siswa', 'ketua_pkl'])): ?>
                    <li class="nav-item">
                        <a href="<?= base_url('/attendance') ?>" class="nav-link <?= $curUri == 'attendance' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-calendar-check"></i>
                            <p>Absensi KBM</p>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php /* PKL Menu for Admin/Kepsek/Guru/Guru Mapel */ ?>
                    <?php if (in_array($role, ['admin', 'kepsek', 'guru', 'guru_mapel', 'guru_bk'])): ?>
                    <li class="nav-item <?= str_starts_with($curUri, 'pkl') ? 'menu-open' : '' ?>">
                        <a href="#" class="nav-link <?= str_starts_with($curUri, 'pkl') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-briefcase"></i>
                            <p>PKL <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= base_url('/pkl') ?>" class="nav-link <?= $curUri == 'pkl' ? 'active' : '' ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Laporan Harian PKL</p>
                                </a>
                            </li>
                            <?php if (in_array($role, ['admin', 'kepsek', 'guru', 'guru_mapel'])): ?>
                            <li class="nav-item">
                                <a href="<?= base_url('/pkl/groups') ?>" class="nav-link <?= $curUri == 'pkl/groups' ? 'active' : '' ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Kelompok PKL (DU/DI)</p>
                                </a>
                            </li>
                            <?php endif; ?>
                            <li class="nav-item">
                                <a href="<?= base_url('/pkl/monitoring') ?>" class="nav-link <?= $curUri == 'pkl/monitoring' ? 'active' : '' ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Monitoring</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php /* PKL Menu for Ketua PKL */ ?>
                    <?php if ($role === 'ketua_pkl'): ?>
                    <li class="nav-item <?= str_starts_with($curUri, 'pkl') ? 'menu-open' : '' ?>">
                        <a href="#" class="nav-link <?= str_starts_with($curUri, 'pkl') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-clipboard-list text-success"></i>
                            <p class="font-weight-bold" style="color:#16a34a;">PKL <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= base_url('/pkl') ?>" class="nav-link <?= $curUri == 'pkl' ? 'active' : '' ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Isi Absen & Jurnal PKL</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('/pkl/monitoring') ?>" class="nav-link <?= $curUri == 'pkl/monitoring' ? 'active' : '' ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Monitoring</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php /* PKL read-only for siswa/anggotapkl (non-ketua) — hanya tampil jika anggota PKL */ ?>
                    <?php if (in_array($role, ['siswa', 'anggotapkl']) && session()->get('is_pkl_member')): ?>
                    <li class="nav-item <?= str_starts_with($curUri, 'pkl') ? 'menu-open' : '' ?>">
                        <a href="#" class="nav-link <?= str_starts_with($curUri, 'pkl') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-briefcase"></i>
                            <p>PKL <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= base_url('/pkl') ?>" class="nav-link <?= $curUri == 'pkl' ? 'active' : '' ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Laporan PKL Saya</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('/pkl/monitoring') ?>" class="nav-link <?= $curUri == 'pkl/monitoring' ? 'active' : '' ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Monitoring</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (in_array($role, ['admin', 'kepsek', 'guru_bk', 'siswa', 'ketua_pkl', 'anggotapkl'])): ?>
                    <li class="nav-item">
                        <a href="<?= base_url('/violations') ?>" class="nav-link <?= $curUri == 'violations' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-exclamation-triangle"></i>
                            <p>Poin Pelanggaran</p>
                        </a>
                    </li>
                    <?php endif; ?>



                    <li class="nav-item">
                        <a href="<?= base_url('/prestasi') ?>" class="nav-link <?= $curUri == 'prestasi' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-trophy"></i>
                            <p>Prestasi Siswa</p>
                        </a>
                    </li>

                    <?php if (in_array($role, ['admin', 'guru_bk', 'siswa', 'ketua_pkl', 'anggotapkl'])): ?>
                    <li class="nav-item">
                        <a href="<?= base_url('/counseling') ?>" class="nav-link <?= $curUri == 'counseling' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-heart"></i>
                            <p>Catatan BK</p>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (in_array($role, ['admin', 'kepsek', 'siswa', 'ketua_pkl'])): ?>
                    <li class="nav-item">
                        <a href="<?= base_url('/feedbacks') ?>" class="nav-link <?= $curUri == 'feedbacks' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-comment-alt"></i>
                            <p>Kotak Suara</p>
                        </a>
                    </li>
                    <?php endif; ?>



                    <?php if ($role === 'admin'): ?>
                    <li class="nav-item">
                        <a href="<?= base_url('/whatsapp-settings') ?>" class="nav-link <?= $curUri == 'whatsapp-settings' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>Pengaturan WA</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('/logs') ?>" class="nav-link <?= $curUri == 'logs' ? 'active' : '' ?>">
                            <i class="nav-icon fab fa-whatsapp"></i>
                            <p>Log Audit Chat WA</p>
                        </a>
                    </li>
                    <?php endif; ?>


                    <!-- Profil & Ganti Password -->
                    <li class="nav-item" style="margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid rgba(0,0,0,0.06);">
                        <a href="<?= base_url('/profile') ?>" class="nav-link <?= uri_string() === 'profile' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-user-cog"></i>
                            <p>Profil & Password</p>
                        </a>
                    </li>
                    <!-- Logout di bawah sidebar -->
                    <li class="nav-item">
                        <a href="<?= base_url('/logout') ?>" class="nav-link" style="color: #dc3545 !important;">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>Keluar</p>
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show rounded-lg border-0" role="alert">
                        <i class="fas fa-check-circle mr-2"></i> <?= session()->getFlashdata('success') ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('info')): ?>
                    <div class="alert alert-info alert-dismissible fade show rounded-lg border-0" role="alert">
                        <i class="fas fa-info-circle mr-2"></i> <?= session()->getFlashdata('info') ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show rounded-lg border-0" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i> <?= session()->getFlashdata('error') ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </section>



        <!-- Main content -->
        <section class="content px-3 pb-5">
            <div class="container-fluid">
                <?= $this->renderSection('content') ?>
            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <footer class="main-footer bg-white border-top text-center" style="padding: 0.5rem 0.75rem;">
        <a href="https://smknudarussalam.sch.id" target="_blank" style="color: #94a3b8; font-size: 0.6875rem; text-decoration: none;">Copyright &copy; 2026 TKJ SMK NU Darussalam</a>
    </footer>
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    <!-- Bottom Navigation Bar for Mobile -->
    <div class="mobile-bottom-nav d-md-none d-print-none">
        <a href="<?= base_url('/dashboard') ?>" class="mobile-bottom-nav-item <?= $curUri == 'dashboard' || $curUri == '' ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Beranda</span>
        </a>
        
        <?php if (in_array($role, ['admin', 'kepsek', 'guru', 'guru_mapel', 'guru_bk'])): ?>
            <a href="<?= base_url('/attendance') ?>" class="mobile-bottom-nav-item <?= str_starts_with($curUri, 'attendance') ? 'active' : '' ?>">
                <i class="fas fa-calendar-check"></i>
                <span>Absen KBM</span>
            </a>
            <a href="<?= base_url('/pkl') ?>" class="mobile-bottom-nav-item <?= str_starts_with($curUri, 'pkl') ? 'active' : '' ?>">
                <i class="fas fa-briefcase"></i>
                <span>PKL</span>
            </a>
        <?php elseif ($role === 'ketua_pkl' || (in_array($role, ['siswa', 'anggotapkl']) && session()->get('is_pkl_member'))): ?>
            <a href="<?= base_url('/schedules') ?>" class="mobile-bottom-nav-item <?= str_starts_with($curUri, 'schedules') ? 'active' : '' ?>">
                <i class="fas fa-calendar-alt"></i>
                <span>Jadwal</span>
            </a>
            <a href="<?= base_url('/pkl') ?>" class="mobile-bottom-nav-item <?= str_starts_with($curUri, 'pkl') ? 'active' : '' ?>">
                <i class="fas fa-briefcase"></i>
                <span>Lapor PKL</span>
            </a>
        <?php else: ?>
            <a href="<?= base_url('/schedules') ?>" class="mobile-bottom-nav-item <?= str_starts_with($curUri, 'schedules') ? 'active' : '' ?>">
                <i class="fas fa-calendar-alt"></i>
                <span>Jadwal</span>
            </a>
            <a href="<?= base_url('/violations') ?>" class="mobile-bottom-nav-item <?= str_starts_with($curUri, 'violations') ? 'active' : '' ?>">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Poin</span>
            </a>
        <?php endif; ?>

        <a href="<?= base_url('/profile') ?>" class="mobile-bottom-nav-item <?= $curUri == 'profile' ? 'active' : '' ?>">
            <i class="fas fa-user-cog"></i>
            <span>Profil</span>
        </a>
    </div>

    <!-- Smart Mobile Date Label Injector -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.form-inline input[type="date"], .form-inline input[type="month"]').forEach(function(input) {
            var previous = input.previousElementSibling;
            if (!previous || previous.tagName.toLowerCase() !== 'label') {
                var label = document.createElement('label');
                label.className = 'd-md-none text-secondary font-weight-bold mb-1';
                label.style.fontSize = '12px';
                label.style.alignSelf = 'flex-start';
                label.innerText = input.type === 'month' ? 'Pilih Bulan/Tahun :' : 'Pilih Tanggal :';
                
                // Jika input ada di dalam div.input-group, taruh label sebelum input-group
                if (input.parentNode.classList.contains('input-group')) {
                    input.parentNode.parentNode.insertBefore(label, input.parentNode);
                } else {
                    input.parentNode.insertBefore(label, input);
                }
            }
        });
    });
    </script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>
