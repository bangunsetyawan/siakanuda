<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-3">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1 class="m-0 font-weight-bold text-dark" style="font-size: 26px;">Dashboard SIAKANUDA</h1>
                <p class="text-secondary mb-0" style="font-size: 14px;">Selamat datang di Aplikasi Sistem Informasi SMK NU Darussalam</p>
            </div>
            <div class="text-md-right mt-2 mt-md-0">
                <div id="realtime-clock" class="font-weight-bold text-dark" style="font-size: 14px; letter-spacing: 0.5px; background: rgba(22, 163, 74, 0.05); padding: 8px 16px; border-radius: 12px; border: 1px solid rgba(22, 163, 74, 0.15); display: inline-block;">
                    <i class="far fa-clock text-success mr-2"></i><span id="clock-time"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Role-based Welcome Card -->
<div class="card mb-4 border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
    <div class="card-body p-0">
        <div class="d-flex align-items-stretch" style="min-height: 80px;">
            <!-- Accent strip -->
            <div style="width: 6px; background: linear-gradient(180deg, #16a34a, #0ea5e9);"></div>
            <div class="p-3 p-md-4 flex-grow-1">
                <div class="d-flex align-items-center mb-2">
                    <div class="d-flex align-items-center justify-content-center rounded-circle mr-3"
                         style="width:44px; height:44px; min-width:44px; background: linear-gradient(135deg, #16a34a, #15803d);">
                        <i class="fas fa-user-circle text-white" style="font-size: 20px;"></i>
                    </div>
                    <div>
                        <h5 class="font-weight-bold text-dark mb-0" style="font-size: 16px;">Halo, <?= htmlspecialchars($userName ?? 'Pengguna') ?>!</h5>
                        <span class="badge px-2 py-1 font-weight-bold" style="font-size: 11px; background: rgba(22,163,74,0.1); color: #16a34a; border-radius: 6px;">
                            <i class="fas fa-shield-alt mr-1"></i><?= htmlspecialchars($roleLabel ?? 'Pengguna') ?>
                        </span>
                    </div>
                </div>
                <p class="text-secondary mb-0" style="font-size: 13px; line-height: 1.6;">
                    <?php
                    $roleDescriptions = [
                        'admin'      => 'Kamu bisa akses <strong>semua menu</strong>: Manajemen Siswa & Guru, Absensi KBM, Laporan & Kelompok PKL, Pelanggaran, Catatan BK, Jadwal, Kotak Suara, Log WA, dan Alumni & BKK.',
                        'kepsek'     => 'Kamu bisa melihat <strong>laporan lengkap</strong>: Rekap Absensi KBM, Laporan & Kelompok PKL, Poin Pelanggaran, Jadwal Pelajaran, dan Kotak Suara Siswa.',
                        'guru_bk'    => 'Kamu bisa akses menu <strong>Absensi KBM</strong>, <strong>Poin Pelanggaran (Penuh)</strong>, <strong>Catatan BK</strong>, <strong>Laporan & Kelompok PKL</strong>, Manajemen Siswa, dan Jadwal Pelajaran.',
                        'guru'       => 'Kamu bisa akses menu <strong>Absensi KBM</strong>, <strong>Jadwal Pelajaran</strong>, <strong>Manajemen Siswa (Hanya Lihat)</strong>, <strong>Poin Pelanggaran (Hanya Lihat)</strong>, dan <strong>Kelompok PKL</strong> yang Anda bimbing.',
                        'guru_mapel' => 'Kamu bisa akses menu <strong>Input Absensi KBM</strong>, <strong>Jadwal Pelajaran</strong>, dan <strong>Laporan PKL</strong>.',
                        'ketua_pkl'  => 'Kamu bisa mengisi <strong>Absensi PKL harian</strong>, upload Jurnal & Foto PKL, melihat Riwayat Laporan, Poin Pelanggaran, dan Kotak Suara.',
                        'anggotapkl' => 'Kamu bisa melihat <strong>Jadwal Pelajaran</strong>, <strong>Rekap Absensi</strong>, <strong>Laporan PKL</strong>, Poin Pelanggaran, Catatan BK, dan mengisi Kotak Suara.',
                        'siswa'      => 'Kamu bisa melihat <strong>Jadwal Pelajaran</strong>, <strong>Rekap Absensi</strong>, ' . (!empty($isPklMember) ? '<strong>Laporan PKL</strong>, ' : '') . 'Poin Pelanggaran, dan mengisi Kotak Suara.',
                    ];
                    echo $roleDescriptions[$userRole] ?? 'Selamat menggunakan aplikasi SIAKANUDA.';
                    ?>
                </p>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($notifications)): ?>
<!-- Smart Notifications Widget (v1.6.8) -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-lg" style="border-radius: 16px;">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="font-weight-bold text-dark mb-0">
                    <i class="fas fa-bell text-success mr-2"></i> Notifikasi
                </h6>
            </div>
            <div class="card-body pt-2 pb-3">
                <div class="d-flex flex-column" style="gap: 10px;">
                    <?php foreach ($notifications as $n): ?>
                        <?php 
                        $bgClasses = [
                            'danger'    => 'bg-danger-soft',
                            'warning'   => 'bg-warning-soft',
                            'success'   => 'bg-success-soft',
                            'info'      => 'bg-info-soft',
                            'secondary' => 'bg-secondary-soft'
                        ];
                        $bgClass = $bgClasses[$n['type']] ?? 'bg-light';
                        
                        $textColors = [
                            'danger'    => 'text-danger',
                            'warning'   => 'text-warning',
                            'success'   => 'text-success',
                            'info'      => 'text-info',
                            'secondary' => 'text-secondary'
                        ];
                        $textColor = $textColors[$n['type']] ?? 'text-dark';
                        
                        $borderStyles = [
                            'danger'    => 'border: 1px solid rgba(220,53,69,0.12)',
                            'warning'   => 'border: 1px solid rgba(255,193,7,0.12)',
                            'success'   => 'border: 1px solid rgba(40,167,69,0.12)',
                            'info'      => 'border: 1px solid rgba(23,162,184,0.12)',
                            'secondary' => 'border: 1px solid rgba(108,117,125,0.12)'
                        ];
                        $borderStyle = $borderStyles[$n['type']] ?? 'border: 1px solid rgba(0,0,0,0.05)';
                        ?>
                        <div class="p-3 rounded-lg d-flex align-items-center justify-content-between flex-wrap <?= $bgClass ?>" style="border-radius: 12px; <?= $borderStyle ?>; gap: 10px;">
                            <div class="d-flex align-items-center flex-grow-1">
                                <div class="d-flex align-items-center justify-content-center rounded-circle mr-3 <?= $bgClass ?>" style="width: 36px; height: 36px; min-width: 36px;">
                                    <i class="<?= $n['icon'] ?> <?= $textColor ?>" style="font-size: 16px;"></i>
                                </div>
                                <span style="font-size: 13.5px;" class="text-dark"><?= $n['message'] ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Custom CSS for Premium Shortcuts -->
<style>
    .bg-primary-soft { background-color: rgba(2, 132, 199, 0.08) !important; }
    .bg-success-soft { background-color: rgba(40, 167, 69, 0.08) !important; }
    .bg-warning-soft { background-color: rgba(255, 193, 7, 0.08) !important; }
    .bg-info-soft { background-color: rgba(23, 162, 184, 0.08) !important; }
    .bg-secondary-soft { background-color: rgba(108, 117, 125, 0.08) !important; }
    .bg-dark-soft { background-color: rgba(52, 58, 64, 0.08) !important; }
    .bg-danger-soft { background-color: rgba(220, 53, 69, 0.08) !important; }
    .bg-pink-soft { background-color: rgba(236, 72, 153, 0.08) !important; }
    .bg-teal-soft { background-color: rgba(32, 201, 151, 0.08) !important; }
    .bg-whatsapp-soft { background-color: rgba(40, 167, 69, 0.08) !important; }
    .bg-green-soft { background-color: rgba(22, 163, 74, 0.08) !important; }
    .bg-indigo-soft { background-color: rgba(99, 102, 241, 0.08) !important; }

    .text-pink { color: #ec4899 !important; }
    .text-teal { color: #20c997 !important; }
    .text-whatsapp { color: #28a745 !important; }
    .text-green { color: #16a34a !important; }
    .text-indigo { color: #6366f1 !important; }

    .shortcut-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px 10px;
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 18px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none !important;
        height: 100%;
    }
    .shortcut-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.06);
        border-color: rgba(0, 0, 0, 0.08);
    }
    .shortcut-card.shortcut-primary {
        border: 2px solid rgba(22, 163, 74, 0.3);
        background: linear-gradient(135deg, rgba(22, 163, 74, 0.06), #ffffff);
    }
    .shortcut-icon-wrapper {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        font-size: 22px;
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .shortcut-card:hover .shortcut-icon-wrapper {
        transform: scale(1.12) rotate(3deg);
    }
    .shortcut-title {
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        margin: 0;
        line-height: 1.3;
    }
    .pkl-status-badge {
        font-size: 11px;
        padding: 3px 10px;
        border-radius: 20px;
        font-weight: 700;
        letter-spacing: 0.3px;
    }
    .member-status-dot {
        width: 10px; height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 4px;
    }
</style>

<?php
$role = session()->get('role');
$shortcuts = [];

if ($role === 'admin') {
    $shortcuts = [
        ['title' => 'Manajemen Siswa',   'url' => '/students',        'icon' => 'fas fa-users',               'bg' => 'bg-primary-soft',   'text' => 'text-primary'],
        ['title' => 'Manajemen Guru',    'url' => '/allowed-numbers', 'icon' => 'fas fa-chalkboard-teacher',  'bg' => 'bg-success-soft',   'text' => 'text-success'],
        ['title' => 'Jadwal Pelajaran',  'url' => '/schedules',       'icon' => 'fas fa-calendar-alt',        'bg' => 'bg-warning-soft',   'text' => 'text-warning'],
        ['title' => 'Kalender Akademik',  'url' => '/kalender-akademik', 'icon' => 'fas fa-calendar-week',     'bg' => 'bg-success-soft',   'text' => 'text-success'],
        ['title' => 'Absensi KBM',       'url' => '/attendance',      'icon' => 'fas fa-calendar-check',      'bg' => 'bg-info-soft',      'text' => 'text-info'],
        ['title' => 'Laporan PKL',       'url' => '/pkl',             'icon' => 'fas fa-briefcase',           'bg' => 'bg-secondary-soft', 'text' => 'text-secondary'],
        ['title' => 'Kelompok PKL',      'url' => '/pkl/groups',      'icon' => 'fas fa-map-marked-alt',      'bg' => 'bg-dark-soft',      'text' => 'text-dark'],
        ['title' => 'Poin Pelanggaran', 'url' => '/violations',      'icon' => 'fas fa-exclamation-triangle', 'bg' => 'bg-danger-soft',    'text' => 'text-danger'],
        ['title' => 'Catatan BK',        'url' => '/counseling',      'icon' => 'fas fa-heart',               'bg' => 'bg-pink-soft',      'text' => 'text-pink'],
        ['title' => 'Prestasi Siswa',    'url' => '/prestasi',        'icon' => 'fas fa-trophy',               'bg' => 'bg-green-soft',     'text' => 'text-green'],
        ['title' => 'Kotak Suara',       'url' => '/feedbacks',       'icon' => 'fas fa-comment-alt',         'bg' => 'bg-teal-soft',      'text' => 'text-teal'],
        ['title' => 'Log Audit WA',      'url' => '/logs',            'icon' => 'fab fa-whatsapp',            'bg' => 'bg-whatsapp-soft',  'text' => 'text-whatsapp'],
        ['title' => 'Alumni & BKK',       'url' => 'https://smknudarussalam.sch.id/bkk', 'icon' => 'fas fa-graduation-cap', 'bg' => 'bg-indigo-soft', 'text' => 'text-indigo', 'external' => true],
    ];
} elseif ($role === 'kepsek') {
    $shortcuts = [
        ['title' => 'Absensi KBM',       'url' => '/attendance',  'icon' => 'fas fa-calendar-check',       'bg' => 'bg-info-soft',      'text' => 'text-info'],
        ['title' => 'Kalender Akademik',  'url' => '/kalender-akademik', 'icon' => 'fas fa-calendar-week',  'bg' => 'bg-success-soft',   'text' => 'text-success'],
        ['title' => 'Laporan PKL',       'url' => '/pkl',         'icon' => 'fas fa-briefcase',            'bg' => 'bg-secondary-soft', 'text' => 'text-secondary'],
        ['title' => 'Kelompok PKL',      'url' => '/pkl/groups',  'icon' => 'fas fa-map-marked-alt',       'bg' => 'bg-dark-soft',      'text' => 'text-dark'],
        ['title' => 'Jadwal Pelajaran',  'url' => '/schedules',   'icon' => 'fas fa-calendar-alt',         'bg' => 'bg-warning-soft',   'text' => 'text-warning'],
        ['title' => 'Poin Pelanggaran', 'url' => '/violations',  'icon' => 'fas fa-exclamation-triangle',  'bg' => 'bg-danger-soft',    'text' => 'text-danger'],
        ['title' => 'Prestasi Siswa',    'url' => '/prestasi',    'icon' => 'fas fa-trophy',               'bg' => 'bg-green-soft',     'text' => 'text-green'],
        ['title' => 'Kotak Suara',       'url' => '/feedbacks',   'icon' => 'fas fa-comment-alt',          'bg' => 'bg-teal-soft',      'text' => 'text-teal'],
        ['title' => 'Alumni & BKK',      'url' => 'https://smknudarussalam.sch.id/bkk', 'icon' => 'fas fa-graduation-cap', 'bg' => 'bg-indigo-soft', 'text' => 'text-indigo', 'external' => true],
    ];
} elseif ($role === 'guru_bk') {
    $shortcuts = [
        ['title' => 'Absensi KBM',       'url' => '/attendance',  'icon' => 'fas fa-calendar-check',       'bg' => 'bg-info-soft',    'text' => 'text-info'],
        ['title' => 'Kalender Akademik',  'url' => '/kalender-akademik', 'icon' => 'fas fa-calendar-week',  'bg' => 'bg-success-soft',   'text' => 'text-success'],
        ['title' => 'Jadwal Pelajaran',  'url' => '/schedules',   'icon' => 'fas fa-calendar-alt',         'bg' => 'bg-warning-soft',   'text' => 'text-warning'],
        ['title' => 'Poin Pelanggaran', 'url' => '/violations',  'icon' => 'fas fa-exclamation-triangle',  'bg' => 'bg-danger-soft',  'text' => 'text-danger'],
        ['title' => 'Catatan BK',        'url' => '/counseling',  'icon' => 'fas fa-heart',                'bg' => 'bg-pink-soft',    'text' => 'text-pink'],
        ['title' => 'Laporan PKL',       'url' => '/pkl',         'icon' => 'fas fa-briefcase',            'bg' => 'bg-secondary-soft', 'text' => 'text-secondary'],
        ['title' => 'Kelompok PKL',      'url' => '/pkl/groups',  'icon' => 'fas fa-map-marked-alt',       'bg' => 'bg-dark-soft',    'text' => 'text-dark'],
        ['title' => 'Manajemen Siswa',   'url' => '/students',    'icon' => 'fas fa-users',                'bg' => 'bg-primary-soft',   'text' => 'text-primary'],
        ['title' => 'Alumni & BKK',      'url' => 'https://smknudarussalam.sch.id/bkk', 'icon' => 'fas fa-graduation-cap', 'bg' => 'bg-indigo-soft', 'text' => 'text-indigo', 'external' => true],
    ];
} elseif (in_array($role, ['guru', 'guru_mapel'])) {
    $shortcuts = [
        ['title' => 'Absensi KBM',       'url' => '/attendance',  'icon' => 'fas fa-calendar-check',       'bg' => 'bg-info-soft',      'text' => 'text-info'],
        ['title' => 'Kalender Akademik',  'url' => '/kalender-akademik', 'icon' => 'fas fa-calendar-week',  'bg' => 'bg-success-soft',   'text' => 'text-success'],
        ['title' => 'Jadwal Pelajaran',  'url' => '/schedules',   'icon' => 'fas fa-calendar-alt',         'bg' => 'bg-warning-soft',   'text' => 'text-warning'],
        ['title' => 'Poin Pelanggaran', 'url' => '/violations',  'icon' => 'fas fa-exclamation-triangle',  'bg' => 'bg-danger-soft',    'text' => 'text-danger'],
        ['title' => 'Prestasi Siswa',    'url' => '/prestasi',    'icon' => 'fas fa-trophy',               'bg' => 'bg-green-soft',     'text' => 'text-green'],
        ['title' => 'Laporan PKL',       'url' => '/pkl',         'icon' => 'fas fa-briefcase',            'bg' => 'bg-secondary-soft', 'text' => 'text-secondary'],
        ['title' => 'Kelompok PKL',      'url' => '/pkl/groups',  'icon' => 'fas fa-map-marked-alt',       'bg' => 'bg-dark-soft',      'text' => 'text-dark'],
        ['title' => 'Alumni & BKK',      'url' => 'https://smknudarussalam.sch.id/bkk', 'icon' => 'fas fa-graduation-cap', 'bg' => 'bg-indigo-soft', 'text' => 'text-indigo', 'external' => true],
    ];
} elseif ($role === 'ketua_pkl') {
    $shortcuts = [
        ['title' => 'Isi Absen & Jurnal PKL', 'url' => '/pkl',        'icon' => 'fas fa-clipboard-list',       'bg' => 'bg-green-soft',   'text' => 'text-green',    'primary' => true],
        ['title' => 'Jadwal Pelajaran',  'url' => '/schedules',       'icon' => 'fas fa-calendar-alt',        'bg' => 'bg-warning-soft',   'text' => 'text-warning'],
        ['title' => 'Kalender Akademik',  'url' => '/kalender-akademik', 'icon' => 'fas fa-calendar-week',     'bg' => 'bg-success-soft',   'text' => 'text-success'],
        ['title' => 'Absensi KBM',       'url' => '/attendance',      'icon' => 'fas fa-calendar-check',      'bg' => 'bg-info-soft',      'text' => 'text-info'],
        ['title' => 'Poin Pelanggaran', 'url' => '/violations',  'icon' => 'fas fa-exclamation-triangle', 'bg' => 'bg-danger-soft',  'text' => 'text-danger'],
        ['title' => 'Prestasi Saya',     'url' => '/prestasi',   'icon' => 'fas fa-trophy',               'bg' => 'bg-green-soft',   'text' => 'text-green'],
        ['title' => 'Catatan BK',        'url' => '/counseling', 'icon' => 'fas fa-heart',                'bg' => 'bg-pink-soft',    'text' => 'text-pink'],
        ['title' => 'Kotak Suara',       'url' => '/feedbacks',  'icon' => 'fas fa-comment-alt',          'bg' => 'bg-teal-soft',    'text' => 'text-teal'],
        ['title' => 'Alumni & BKK',      'url' => 'https://smknudarussalam.sch.id/bkk', 'icon' => 'fas fa-graduation-cap', 'bg' => 'bg-indigo-soft', 'text' => 'text-indigo', 'external' => true],
    ];
} else {
    // siswa & fallback — selaras dengan sidebar
    $shortcuts = [
        ['title' => 'Absensi KBM',       'url' => '/attendance',  'icon' => 'fas fa-calendar-check',       'bg' => 'bg-info-soft',      'text' => 'text-info'],
        ['title' => 'Kalender Akademik',  'url' => '/kalender-akademik', 'icon' => 'fas fa-calendar-week',  'bg' => 'bg-success-soft',   'text' => 'text-success'],
        ['title' => 'Jadwal Pelajaran',  'url' => '/schedules',   'icon' => 'fas fa-calendar-alt',         'bg' => 'bg-warning-soft',   'text' => 'text-warning'],
        ['title' => 'Poin Pelanggaran', 'url' => '/violations',  'icon' => 'fas fa-exclamation-triangle',  'bg' => 'bg-danger-soft',    'text' => 'text-danger'],
        ['title' => 'Prestasi Saya',     'url' => '/prestasi',   'icon' => 'fas fa-trophy',               'bg' => 'bg-green-soft',     'text' => 'text-green'],
        ['title' => 'Catatan BK',        'url' => '/counseling', 'icon' => 'fas fa-heart',                'bg' => 'bg-pink-soft',    'text' => 'text-pink'],
        ['title' => 'Kotak Suara',       'url' => '/feedbacks',  'icon' => 'fas fa-comment-alt',          'bg' => 'bg-teal-soft',      'text' => 'text-teal'],
        ['title' => 'Alumni & BKK',      'url' => 'https://smknudarussalam.sch.id/bkk', 'icon' => 'fas fa-graduation-cap', 'bg' => 'bg-indigo-soft', 'text' => 'text-indigo', 'external' => true],
    ];
    // Tambahkan PKL hanya jika siswa adalah anggota/ketua PKL
    if (!empty($isPklMember)) {
        array_splice($shortcuts, 2, 0, [
            ['title' => 'Laporan PKL Saya', 'url' => '/pkl', 'icon' => 'fas fa-briefcase', 'bg' => 'bg-secondary-soft', 'text' => 'text-secondary'],
        ]);
    }
}
?>

<!-- Premium Shortcut Grid Section -->
<div class="card mb-4 border-0 shadow-none bg-transparent">
    <div class="card-body p-0">
        <div class="row">
            <?php foreach ($shortcuts as $shortcut): ?>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                    <a href="<?= !empty($shortcut['external']) ? $shortcut['url'] : base_url($shortcut['url']) ?>" class="shortcut-card <?= !empty($shortcut['primary']) ? 'shortcut-primary' : '' ?>" <?= !empty($shortcut['external']) ? 'target="_blank" rel="noopener"' : '' ?>>
                        <div class="shortcut-icon-wrapper <?= $shortcut['bg'] ?> <?= $shortcut['text'] ?>">
                            <i class="<?= $shortcut['icon'] ?>"></i>
                        </div>
                        <p class="shortcut-title text-center text-truncate w-100" title="<?= $shortcut['title'] ?>"><?= $shortcut['title'] ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php /* ================================================================
   PKL ABSENSI WIDGET — hanya tampil untuk ketua_pkl yang punya grup PKL
   ================================================================ */ ?>
<?php if ($role === 'ketua_pkl' && !empty($pklGroupData)): ?>
<div class="row mb-4">
    <div class="col-12">
        <a href="<?= base_url('/pkl') ?>" class="text-decoration-none card-attendance-link" style="display: block;">
            <?php if (isset($pklActive) && $pklActive !== '1'): ?>
            <div class="card border-0 shadow-sm status-card-pkl" 
                 style="border-radius: 20px; overflow: hidden; background: linear-gradient(135deg, #3b82f6, #1d4ed8); padding: 24px; position: relative;">
                <div class="position-absolute" style="right: 20px; bottom: -10px; font-size: 100px; opacity: 0.12; color: #fff; pointer-events: none;">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="d-flex align-items-center justify-content-between flex-wrap position-relative" style="z-index: 2;">
                    <div class="d-flex align-items-center">
                        <div class="mr-3 d-flex align-items-center justify-content-center bg-white rounded-circle shadow-sm"
                             style="width: 52px; height: 52px; min-width: 52px;">
                            <i class="fas fa-briefcase text-primary" style="font-size: 22px;"></i>
                        </div>
                        <div>
                            <h4 class="font-weight-bold text-white mb-1" style="font-size: 18px; letter-spacing: 0.3px;">Info PKL</h4>
                            <p class="text-white mb-0" style="opacity: 0.9; font-size: 13.5px; font-weight: 500;">
                                📍 <?= htmlspecialchars($pklGroupData['tempat_pkl']) ?>
                            </p>
                        </div>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <div class="px-4 py-2 font-weight-bold rounded-pill text-center"
                             style="background: rgba(255, 255, 255, 0.2); color: #fff; border: 1px solid rgba(255, 255, 255, 0.4); font-size: 13px; letter-spacing: 0.5px;">
                            <i class="fas fa-info-circle mr-1"></i> FITUR DINONAKTIFKAN
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card border-0 shadow-sm status-card-pkl" 
                 style="border-radius: 20px; overflow: hidden; background: <?= !empty($pklTodayReport) ? ($pklTodayReport['status_libur'] ? 'linear-gradient(135deg, #f59e0b, #d97706)' : 'linear-gradient(135deg, #16a34a, #15803d)') : 'linear-gradient(135deg, #ef4444, #dc2626)' ?>; padding: 24px; position: relative;">
                
                <!-- Background pattern/icon for depth -->
                <div class="position-absolute" style="right: 20px; bottom: -10px; font-size: 100px; opacity: 0.12; color: #fff; pointer-events: none;">
                    <i class="fas fa-clipboard-list"></i>
                </div>

                <div class="d-flex align-items-center justify-content-between flex-wrap position-relative" style="z-index: 2;">
                    <div class="d-flex align-items-center">
                        <div class="mr-3 d-flex align-items-center justify-content-center bg-white rounded-circle shadow-sm"
                             style="width: 52px; height: 52px; min-width: 52px;">
                            <i class="fas fa-briefcase <?= !empty($pklTodayReport) ? ($pklTodayReport['status_libur'] ? 'text-warning' : 'text-success') : 'text-danger' ?>" style="font-size: 22px;"></i>
                        </div>
                        <div>
                            <h4 class="font-weight-bold text-white mb-1" style="font-size: 18px; letter-spacing: 0.3px;">Absensi PKL Hari Ini</h4>
                            <p class="text-white mb-0" style="opacity: 0.9; font-size: 13.5px; font-weight: 500;">
                                📍 <?= htmlspecialchars($pklGroupData['tempat_pkl']) ?> &nbsp;|&nbsp;
                                📅 <?= date('l, d F Y') ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-3 mt-md-0">
                        <?php if (!empty($pklTodayReport)): ?>
                            <?php if ($pklTodayReport['status_libur']): ?>
                                <div class="px-4 py-2 font-weight-bold rounded-pill text-center"
                                     style="background: rgba(255, 255, 255, 0.2); color: #fff; border: 1px solid rgba(255, 255, 255, 0.4); font-size: 13px; letter-spacing: 0.5px;">
                                    🏢 DU/DI LIBUR HARI INI
                                </div>
                            <?php else: ?>
                                <div class="px-4 py-2 font-weight-bold rounded-pill text-center"
                                     style="background: rgba(255, 255, 255, 0.2); color: #fff; border: 1px solid rgba(255, 255, 255, 0.4); font-size: 13px; letter-spacing: 0.5px;">
                                    <i class="fas fa-check-circle mr-1"></i> ABSENSI SUDAH DIKIRIM
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="px-4 py-2 font-weight-bold rounded-pill text-center pulse-animation"
                                 style="background: #fff; color: #ef4444; border: 1px solid #fff; font-size: 13px; letter-spacing: 0.5px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);">
                                <i class="fas fa-exclamation-circle mr-1"></i> BELUM MENGISI ABSENSI
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </a>
    </div>
</div>

<style>
    .status-card-pkl {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .card-attendance-link:hover .status-card-pkl {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px rgba(0,0,0,0.1) !important;
    }
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.03); }
        100% { transform: scale(1); }
    }
    .pulse-animation {
        animation: pulse 2s infinite ease-in-out;
    }
</style>
<?php endif; ?>

<?php /* Siswa biasa — tampilkan status PKL hari ini jika punya grup */ ?>
<?php if (in_array($role, ['siswa', 'anggotapkl']) && !empty($pklGroupData)): ?>
<div class="card mb-4 border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body d-flex align-items-center" style="gap:16px;">
        <div class="d-flex align-items-center justify-content-center rounded-circle bg-success-soft"
             style="width:52px;height:52px;min-width:52px;">
            <i class="fas fa-briefcase text-success" style="font-size:20px;"></i>
        </div>
        <div class="flex-grow-1">
            <h6 class="font-weight-bold text-dark mb-0">PKL di <?= htmlspecialchars($pklGroupData['tempat_pkl']) ?></h6>
            <?php if (isset($pklActive) && $pklActive !== '1'): ?>
                <small class="text-secondary font-weight-bold">Fitur pelaporan PKL saat ini sedang dinonaktifkan Admin.</small>
            <?php else: ?>
                <?php if (!empty($pklTodayReport)): ?>
                    <?php if ($pklTodayReport['status_libur']): ?>
                        <small class="text-warning font-weight-bold">🏢 DU/DI Libur: <?= htmlspecialchars($pklTodayReport['libur_reason'] ?? 'Tanpa alasan') ?></small>
                    <?php else: ?>
                        <?php
                        $myName = session()->get('name');
                        $myStatus = $pklTodayReport['attendance_data'][$myName] ?? null;
                        $statusColors = ['hadir' => 'success', 'sakit' => 'warning', 'izin' => 'info', 'alpha' => 'danger'];
                        $statusColor = $statusColors[$myStatus] ?? 'secondary';
                        ?>
                        <small class="text-secondary">Status hari ini: </small>
                        <?php if ($myStatus): ?>
                            <span class="badge badge-<?= $statusColor ?> font-weight-bold"><?= ucfirst($myStatus) ?></span>
                        <?php else: ?>
                            <span class="badge badge-secondary font-weight-bold">Menunggu ketua mengisi</span>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <small class="text-secondary">Belum ada laporan masuk hari ini.</small>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <a href="<?= base_url('/pkl') ?>" class="btn btn-sm btn-outline-success" style="border-radius:10px; white-space:nowrap;">
            Lihat Detail <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Widgets Row — stat cards (role-aware) -->
<?php if (in_array($role, ['admin', 'kepsek', 'guru_bk', 'guru_mapel'])): ?>
<!-- GURU/ADMIN: Rekap Global -->
<div class="row justify-content-center">
    <div class="col-xl-2.4 col-lg-3 col-md-4 col-6">
        <div class="small-box bg-white p-3 card mb-3" style="border-radius: 14px; position: relative; overflow: hidden; min-height: 110px;">
            <div class="inner">
                <h3 class="font-weight-bold text-dark" style="font-size: 24px;"><?= $totalStudents ?></h3>
                <p class="text-secondary mb-0" style="font-size: 11.5px; font-weight: 500; line-height: 1.3;">Siswa Terdaftar</p>
            </div>
            <div class="icon text-primary position-absolute" style="right: 15px; top: 15px; font-size: 32px; opacity: 0.12;">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-2.4 col-lg-3 col-md-4 col-6">
        <div class="small-box bg-white p-3 card mb-3" style="border-radius: 14px; position: relative; overflow: hidden; min-height: 110px;">
            <div class="inner">
                <h3 class="font-weight-bold text-success" style="font-size: 24px;"><?= $attendanceToday['hadir'] ?></h3>
                <p class="text-secondary mb-0" style="font-size: 11.5px; font-weight: 500; line-height: 1.3;">Siswa KBM Hadir</p>
            </div>
            <div class="icon text-success position-absolute" style="right: 15px; top: 15px; font-size: 32px; opacity: 0.12;">
                <i class="fas fa-user-check"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-2.4 col-lg-3 col-md-4 col-6">
        <div class="small-box bg-white p-3 card mb-3" style="border-radius: 14px; position: relative; overflow: hidden; min-height: 110px;">
            <div class="inner">
                <h3 class="font-weight-bold text-teal" style="font-size: 24px; color: #0d9488 !important;"><?= $totalPklSiswaHadirToday ?></h3>
                <p class="text-secondary mb-0" style="font-size: 11.5px; font-weight: 500; line-height: 1.3;">Siswa PKL Hadir</p>
            </div>
            <div class="icon text-teal position-absolute" style="right: 15px; top: 15px; font-size: 32px; opacity: 0.12; color: #0d9488 !important;">
                <i class="fas fa-user-shield"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-2.4 col-lg-3 col-md-4 col-6">
        <div class="small-box bg-white p-3 card mb-3" style="border-radius: 14px; position: relative; overflow: hidden; min-height: 110px;">
            <div class="inner">
                <h3 class="font-weight-bold text-info" style="font-size: 24px;"><?= $totalPklToday ?></h3>
                <p class="text-secondary mb-0" style="font-size: 11.5px; font-weight: 500; line-height: 1.3;">Laporan PKL Diterima</p>
            </div>
            <div class="icon text-info position-absolute" style="right: 15px; top: 15px; font-size: 32px; opacity: 0.12;">
                <i class="fas fa-briefcase"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-2.4 col-lg-3 col-md-4 col-6">
        <div class="small-box bg-white p-3 card mb-3" style="border-radius: 14px; position: relative; overflow: hidden; min-height: 110px;">
            <div class="inner">
                <h3 class="font-weight-bold text-danger" style="font-size: 24px;"><?= $totalViolationsToday ?></h3>
                <p class="text-secondary mb-0" style="font-size: 11.5px; font-weight: 500; line-height: 1.3;">Pelanggaran Hari Ini</p>
            </div>
            <div class="icon text-danger position-absolute" style="right: 15px; top: 15px; font-size: 32px; opacity: 0.12;">
                <i class="fas fa-exclamation-circle"></i>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styling khusus grid 5 kolom (col-xl-2.4) untuk Bootstrap 4 */
    @media (min-width: 1200px) {
        .col-xl-2\.4 {
            flex: 0 0 20%;
            max-width: 20%;
        }
    }
</style>
<?php elseif (in_array($role, ['siswa', 'ketua_pkl', 'anggotapkl'])): ?>
<!-- SISWA/KETUA PKL: Rekap Kehadiran Pribadi -->
<?php if (!empty($personalAttendance)): ?>
<div class="card mb-4 border-0 shadow-sm" style="border-radius: 16px;">
    <div class="card-header border-0 pb-0" style="background: transparent;">
        <h6 class="font-weight-bold text-dark mb-0"><i class="fas fa-chart-bar text-primary mr-2"></i>Rekap Kehadiranmu</h6>
    </div>
    <div class="card-body pt-3">
        <div class="row text-center">
            <div class="col-3">
                <div class="p-2 rounded-lg" style="background: rgba(40,167,69,0.08);">
                    <h4 class="font-weight-bold text-success mb-0"><?= $personalAttendance['hadir'] ?></h4>
                    <small class="text-secondary font-weight-bold" style="font-size: 11px;">Hadir</small>
                </div>
            </div>
            <div class="col-3">
                <div class="p-2 rounded-lg" style="background: rgba(255,193,7,0.08);">
                    <h4 class="font-weight-bold text-warning mb-0"><?= $personalAttendance['sakit'] ?></h4>
                    <small class="text-secondary font-weight-bold" style="font-size: 11px;">Sakit</small>
                </div>
            </div>
            <div class="col-3">
                <div class="p-2 rounded-lg" style="background: rgba(23,162,184,0.08);">
                    <h4 class="font-weight-bold text-info mb-0"><?= $personalAttendance['izin'] ?></h4>
                    <small class="text-secondary font-weight-bold" style="font-size: 11px;">Izin</small>
                </div>
            </div>
            <div class="col-3">
                <div class="p-2 rounded-lg" style="background: rgba(220,53,69,0.08);">
                    <h4 class="font-weight-bold text-danger mb-0"><?= $personalAttendance['alpha'] ?></h4>
                    <small class="text-secondary font-weight-bold" style="font-size: 11px;">Alpha</small>
                </div>
            </div>
        </div>
</div>
<?php endif; ?>

<?php if (!empty($personalAchievements)): ?>
<div class="card mb-4 border-0 shadow-sm" style="border-radius: 16px;">
    <div class="card-header border-0 pb-0" style="background: transparent;">
        <h6 class="font-weight-bold text-dark mb-0"><i class="fas fa-trophy text-success mr-2"></i>Catatan Prestasimu</h6>
    </div>
    <div class="card-body pt-3">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Prestasi</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($personalAchievements as $pa): ?>
                    <tr>
                        <td style="white-space: nowrap;"><?= date('d M Y', strtotime($pa['date'])) ?></td>
                        <td>
                            <span class="badge badge-<?= $pa['category'] === 'Akademik' ? 'primary' : 'warning' ?> rounded font-weight-bold px-2 py-1 text-xs">
                                <?= htmlspecialchars($pa['category']) ?>
                            </span>
                        </td>
                        <td class="font-weight-bold text-dark"><?= htmlspecialchars($pa['title']) ?></td>
                        <td class="text-secondary"><?= htmlspecialchars($pa['description'] ?: '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<div class="row">
    <!-- Left column: School info + Kalender Akademik -->
    <div class="col-md-7">
        <div class="card card-outline card-success mb-4">
            <div class="card-header">
                <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-school mr-2 text-success"></i> Profil Akademik &amp; Agenda Kesiswaan</h3>
            </div>
            <div class="card-body">
                <h5 class="font-weight-bold">SMK NU Darussalam</h5>
                <p class="text-secondary" style="font-size: 0.875rem; line-height: 1.7;">Sistem Informasi Akademik (SIAKANUDA) yang dikembangkan oleh TIM TKJ ini mengintegrasikan WhatsApp Bot dan Aplikasi Android untuk mempermudah pemantauan absensi KBM kelas, bimbingan konseling siswa, dan pelaporan harian program PKL (Praktek Kerja Lapangan).</p>
                <hr>
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-light p-3 rounded-lg mr-3 text-center" style="width: 60px;">
                        <h4 class="font-weight-bold text-success mb-0"><?= date('d') ?></h4>
                        <small class="text-uppercase text-secondary font-weight-bold" style="font-size: 10px;"><?= date('M') ?></small>
                    </div>
                    <div>
                        <h6 class="font-weight-bold mb-0" id="dashboard-day-label">Memuat...</h6>
                        <small class="text-secondary">Tahun Pelajaran <?= esc($activeTahunPelajaran['nama'] ?? '2025/2026') ?></small>
                    </div>
                </div>

                <!-- Embedded Kalender Akademik -->
                <?php if (!empty($kalender)): ?>
                <div class="mt-3">
                    <h6 class="font-weight-bold text-dark mb-2" style="font-size: 0.8125rem;">
                        <i class="fas fa-calendar-week text-success mr-1"></i> Kalender Akademik — TP <?= esc($activeTahunPelajaran['nama'] ?? '2025/2026') ?>
                    </h6>
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm table-bordered mb-0" style="font-size: 0.75rem;">
                            <thead style="background: #f0fdf4;" class="sticky-top">
                                <tr>
                                    <th style="padding: 0.375rem 0.5rem; text-align: center;">No</th>
                                    <th style="padding: 0.375rem 0.5rem;">Bulan</th>
                                    <th style="padding: 0.375rem 0.5rem; text-align: center;">Semester</th>
                                    <th style="padding: 0.375rem 0.5rem; text-align: center;">Total</th>
                                    <th style="padding: 0.375rem 0.5rem; text-align: center;">Efektif</th>
                                    <th style="padding: 0.375rem 0.5rem; text-align: center;">Tdk Efektif</th>
                                    <th style="padding: 0.375rem 0.5rem;">Ket.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($kalender as $k): ?>
                                <tr>
                                    <td style="padding: 0.375rem 0.5rem; text-align: center; color: #94a3b8;"><?= $no++ ?></td>
                                    <td style="padding: 0.375rem 0.5rem; font-weight: 600;"><?= htmlspecialchars($k['bulan']) ?></td>
                                    <td style="padding: 0.375rem 0.5rem; text-align: center;"><span class="badge badge-light border"><?= htmlspecialchars($k['semester']) ?></span></td>
                                    <td style="padding: 0.375rem 0.5rem; text-align: center;"><?= $k['total_pekan'] ?></td>
                                    <td style="padding: 0.375rem 0.5rem; text-align: center; color: #16a34a; font-weight: 600;"><?= $k['pekan_efektif'] ?></td>
                                    <td style="padding: 0.375rem 0.5rem; text-align: center; color: #ef4444;"><?= $k['pekan_tidak_efektif'] ?></td>
                                    <td style="padding: 0.375rem 0.5rem; color: #64748b;"><?= htmlspecialchars($k['keterangan']) ?: '—' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot style="background: #f8fafc; font-weight: 700; font-size: 0.75rem;">
                                <tr>
                                    <td colspan="3" style="padding: 0.375rem 0.5rem;">Total</td>
                                    <td style="padding: 0.375rem 0.5rem; text-align: center;"><?= array_sum(array_column($kalender, 'total_pekan')) ?></td>
                                    <td style="padding: 0.375rem 0.5rem; text-align: center; color: #16a34a;"><?= array_sum(array_column($kalender, 'pekan_efektif')) ?></td>
                                    <td style="padding: 0.375rem 0.5rem; text-align: center; color: #ef4444;"><?= array_sum(array_column($kalender, 'pekan_tidak_efektif')) ?></td>
                                    <td style="padding: 0.375rem 0.5rem;"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="text-right mt-2">
                        <a href="<?= base_url('/kalender-akademik') ?>" class="text-success font-weight-bold" style="font-size: 0.75rem;">
                            Lihat Kalender Lengkap <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                <div id="holiday-data" 
                     data-is-holiday="<?= !empty($todayHoliday) ? '1' : '0' ?>" 
                     data-reason="<?= esc($todayHoliday['reason'] ?? '') ?>" 
                     style="display: none;"></div>
            </div>
        </div>
    </div>

    <!-- Right column: Kotak Suara / Feedbacks -->
    <div class="col-md-5">
        <div class="card card-outline card-info mb-4">
            <div class="card-header">
                <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-comment-alt mr-2 text-info"></i> Kotak Suara Siswa Terbaru</h3>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentFeedbacks)): ?>
                    <div class="p-4 text-center text-secondary">
                        <i class="fas fa-comments mb-2" style="font-size: 28px;"></i>
                        <p class="mb-0">Belum ada aspirasi siswa publik masuk.</p>
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($recentFeedbacks as $fb): ?>
                            <li class="list-group-item p-4 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="font-weight-bold text-dark" style="font-size: 14px;"><?= session()->get('role') === 'admin' ? htmlspecialchars($fb['sender_name']) : 'Anonim' ?></span>
                                    <span class="badge badge-info rounded-pill font-weight-bold px-2 py-1" style="font-size: 10px;"><?= htmlspecialchars($fb['class_name']) ?></span>
                                </div>
                                <p class="text-secondary mb-0" style="font-size: 13px;">"<?= htmlspecialchars($fb['message']) ?>"</p>
                                <small class="text-secondary" style="font-size: 10px;"><?= date('d M Y, H:i', strtotime($fb['date'])) ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
        </div>

        <?php if (!empty($recentAchievements)): ?>
        <div class="card card-outline card-success mb-4">
            <div class="card-header">
                <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-trophy mr-2 text-success"></i> Prestasi Siswa Terbaru</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($recentAchievements as $ra): ?>
                        <li class="list-group-item p-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="font-weight-bold text-dark" style="font-size: 13.5px;"><?= htmlspecialchars($ra['student_name']) ?></span>
                                <span class="badge badge-success rounded-pill font-weight-bold px-2 py-1" style="font-size: 10px;"><?= htmlspecialchars($ra['student_class']) ?></span>
                            </div>
                            <h6 class="font-weight-bold mb-1 text-success" style="font-size: 13px;"><?= htmlspecialchars($ra['title']) ?></h6>
                            <small class="text-secondary" style="font-size: 10px;">📅 <?= date('d M Y', strtotime($ra['date'])) ?> &nbsp;|&nbsp; Kategori: <?= htmlspecialchars($ra['category']) ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function updateClock() {
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const dayName = days[now.getDay()];
    const dateNum = now.getDate();
    const monthName = months[now.getMonth()];
    const year = now.getFullYear();
    
    // Format: 18 :45: 00  Rabu, 3 Juni 2026
    document.getElementById('clock-time').innerHTML = `${hours} :${minutes}: ${seconds} &nbsp;&nbsp; ${dayName}, ${dateNum} ${monthName} ${year}`;
}

function formatDashJournalInput(input) {
    let val = input.value.trim();
    if (val.length > 0) {
        input.value = val.charAt(0).toUpperCase() + val.slice(1);
    }
}

function updateDayLabel() {
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const now = new Date();
    const dayName = days[now.getDay()];
    const dateNum = now.getDate();
    const monthName = months[now.getMonth()];
    const year = now.getFullYear();
    const el = document.getElementById('dashboard-day-label');
    if (!el) return;

    fetch('<?= base_url("api/holiday-status") ?>')
        .then(response => response.json())
        .then(data => {
            if (now.getDay() === 0 || data.is_holiday) {
                const reason = data.is_holiday ? data.reason : 'Minggu';
                el.innerHTML = `<span class="text-danger">🛌 Hari Libur: ${reason}</span>, ${dateNum} ${monthName} ${year}`;
            } else {
                el.innerHTML = `<span class="text-success">📚 Hari Aktif Sekolah</span>, ${dayName} ${dateNum} ${monthName} ${year}`;
            }
        })
        .catch(() => {
            const holidayData = document.getElementById('holiday-data');
            const isHoliday = holidayData && holidayData.dataset.isHoliday === '1';
            const holidayReason = holidayData ? holidayData.dataset.reason : '';
            if (now.getDay() === 0 || isHoliday) {
                const reason = isHoliday ? holidayReason : 'Minggu';
                el.innerHTML = `<span class="text-danger">🛌 Hari Libur: ${reason}</span>, ${dateNum} ${monthName} ${year}`;
            } else {
                el.innerHTML = `<span class="text-success">📚 Hari Aktif Sekolah</span>, ${dayName} ${dateNum} ${monthName} ${year}`;
            }
        });
}

document.addEventListener('DOMContentLoaded', function () {
    // Start Clock
    updateClock();
    setInterval(updateClock, 1000);

    // Realtime day label (cek perubahan hari setiap 60 detik)
    updateDayLabel();
    setInterval(updateDayLabel, 60000);
});
</script>
<?= $this->endSection() ?>
```
