<?php

namespace App\Controllers;

use App\Models\StudentModel;
use App\Models\AttendanceModel;
use App\Models\AttendancePklModel;
use App\Models\KelompokPklModel;
use App\Models\ViolationModel;
use App\Models\FeedbackModel;
use App\Models\KalenderAkademikModel;
use App\Models\HariLiburModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $session = session();
        $studentModel = new StudentModel();
        $attendanceModel = new AttendanceModel();
        $attendancePklModel = new AttendancePklModel();
        $kelompokPklModel = new KelompokPklModel();
        $violationModel = new ViolationModel();
        $feedbackModel = new FeedbackModel();
        $hariLiburModel = new HariLiburModel();
        $settingModel = new \App\Models\SettingModel();

        $today = date('Y-m-d');
        $tpId = $this->getActiveTPId();

        // Cek apakah hari ini adalah hari libur
        $todayHoliday = $hariLiburModel->getByDate($today);
        $isHoliday = ($todayHoliday !== null);

        // Statistics (filtered by tahun pelajaran aktif and is_active)
        $totalStudents = $studentModel->groupStart()
                                      ->where('is_active', 1)
                                      ->orWhere('is_active IS NULL')
                                      ->groupEnd()
                                      ->countAllResults();
        
        // Murni Absensi KBM (Bukan Siswa PKL)
        $totalHadirToday = $attendanceModel->where('date', $today)
                                           ->where('status', 'hadir')
                                           ->where('tahun_pelajaran_id', $tpId)
                                           ->groupStart()
                                               ->where('note', null)
                                               ->orWhere('note', '')
                                               ->orNotLike('note', 'PKL:', 'after')
                                           ->groupEnd()
                                           ->countAllResults();

        $totalSakitToday = $attendanceModel->where('date', $today)
                                           ->where('status', 'sakit')
                                           ->where('tahun_pelajaran_id', $tpId)
                                           ->groupStart()
                                               ->where('note', null)
                                               ->orWhere('note', '')
                                               ->orNotLike('note', 'PKL:', 'after')
                                           ->groupEnd()
                                           ->countAllResults();

        $totalIzinToday = $attendanceModel->where('date', $today)
                                          ->where('status', 'izin')
                                          ->where('tahun_pelajaran_id', $tpId)
                                          ->groupStart()
                                              ->where('note', null)
                                              ->orWhere('note', '')
                                              ->orNotLike('note', 'PKL:', 'after')
                                          ->groupEnd()
                                          ->countAllResults();

        $totalAlphaToday = $attendanceModel->where('date', $today)
                                           ->where('status', 'alpha')
                                           ->where('tahun_pelajaran_id', $tpId)
                                           ->groupStart()
                                               ->where('note', null)
                                               ->orWhere('note', '')
                                               ->orNotLike('note', 'PKL:', 'after')
                                           ->groupEnd()
                                           ->countAllResults();

        // Murni Siswa PKL yang Hadir (Note diawali PKL:)
        $totalPklSiswaHadirToday = $attendanceModel->where('date', $today)
                                                   ->where('status', 'hadir')
                                                   ->where('tahun_pelajaran_id', $tpId)
                                                   ->like('note', 'PKL:', 'after')
                                                   ->countAllResults();

        $totalPklToday = $attendancePklModel->where('date', $today)->where('tahun_pelajaran_id', $tpId)->countAllResults();
        $totalViolationsToday = $violationModel->where('date', $today)->where('tahun_pelajaran_id', $tpId)->countAllResults();

        // Feedbacks
        $userRole = $session->get('role');
        $userName = $session->get('name');
        if (in_array($userRole, ['admin', 'kepsek'])) {
            $recentFeedbacks = $feedbackModel->orderBy('date', 'DESC')->limit(5)->findAll();
        } else {
            $recentFeedbacks = $feedbackModel->groupStart()
                ->where('is_public', 1)
                ->orWhere('sender_name', $userName)
                ->groupEnd()
                ->orderBy('date', 'DESC')
                ->limit(5)
                ->findAll();
        }

        // Kalender Akademik (seluruh tahun pelajaran: Ganjil & Genap)
        $kalenderModel = new KalenderAkademikModel();
        $tpNama = $this->activeTP['nama'] ?? '2025/2026';
        $kalender = $kalenderModel->where('tahun_pelajaran', $tpNama)
            ->orderBy('semester', 'ASC') // Ganjil first, then Genap
            ->orderBy('nomor', 'ASC')
            ->findAll();

        // PKL Data for ketua_pkl & siswa
        $pklGroupData = null;
        $pklTodayReport = null;
        $pklIsKetua = false;
        $pklMembers = [];

        $userType = $session->get('user_type');
        $userPhone = $session->get('phone');
        $userName = $session->get('name');
        $userRole = $session->get('role');

        if (!empty($userPhone) && str_starts_with($userPhone, '08')) {
            $userPhone = '628' . substr($userPhone, 2);
        }

        $pklBimbinganGroups = [];
        if (in_array($userRole, ['guru', 'guru_bk', 'guru_mapel'])) {
            $pklBimbinganGroups = $kelompokPklModel->where('tahun_pelajaran_id', $tpId)
                                                   ->where('pembimbing_phone', $userPhone)
                                                   ->orderBy('tempat_pkl', 'ASC')
                                                   ->findAll();
            $monitoringPklModel = new \App\Models\MonitoringPklModel();
            foreach ($pklBimbinganGroups as &$bg) {
                $lastMon = $monitoringPklModel->where('kelompok_pkl_id', $bg['id'])
                                              ->orderBy('tanggal', 'DESC')
                                              ->first();
                $bg['last_monitoring'] = $lastMon ? date('d M Y', strtotime($lastMon['tanggal'])) : 'Belum pernah';
            }
        }

        // Personal attendance and achievements for student
        $personalAttendance = null;
        $personalAchievements = null;
        if ($userType === 'student') {
            $studentId = $session->get('student_id');
            $personalAttendance = [
                'hadir' => $attendanceModel->where('student_id', $studentId)->where('status', 'hadir')->where('tahun_pelajaran_id', $tpId)->countAllResults(),
                'sakit' => $attendanceModel->where('student_id', $studentId)->where('status', 'sakit')->where('tahun_pelajaran_id', $tpId)->countAllResults(),
                'izin'  => $attendanceModel->where('student_id', $studentId)->where('status', 'izin')->where('tahun_pelajaran_id', $tpId)->countAllResults(),
                'alpha' => $attendanceModel->where('student_id', $studentId)->where('status', 'alpha')->where('tahun_pelajaran_id', $tpId)->countAllResults(),
            ];

            $achievementModel = new \App\Models\AchievementModel();
            $personalAchievements = $achievementModel->where('student_id', $studentId)->orderBy('date', 'DESC')->findAll();
        }

        // Recent achievements for staff
        $recentAchievements = null;
        if (in_array($userRole, ['admin', 'guru_bk', 'guru', 'guru_mapel', 'kepsek'])) {
            $achievementModel = new \App\Models\AchievementModel();
            $rawAchievements = $achievementModel->orderBy('date', 'DESC')->limit(5)->findAll();
            $recentAchievements = [];
            foreach ($rawAchievements as $ra) {
                $student = $studentModel->find($ra['student_id']);
                $recentAchievements[] = [
                    'student_name' => $student ? $student['name'] : 'Tidak dikenal',
                    'student_class' => $student ? $student['class'] : 'Tidak dikenal',
                    'date' => $ra['date'],
                    'category' => $ra['category'],
                    'title' => $ra['title']
                ];
            }
        }

        // Role labels for display
        $roleLabels = [
            'admin'      => 'Administrator',
            'kepsek'     => 'Kepala Sekolah',
            'guru_bk'    => 'Guru BK',
            'guru'       => 'Guru Pendidik',
            'guru_mapel' => 'Guru Mata Pelajaran',
            'ketua_pkl'  => 'Ketua Kelompok PKL',
            'siswa'      => 'Siswa',
        ];
        $roleLabel = $roleLabels[$userRole] ?? ucfirst($userRole ?? 'Pengguna');

        if ($userType === 'student') {
            // Check if they are ketua
            $group = $kelompokPklModel->where('ketua_phone', $userPhone)->first();
            $pklIsKetua = ($group !== null);

            if (!$group) {
                // Check if member
                $allGroups = $kelompokPklModel->findAll();
                foreach ($allGroups as $g) {
                    $members = array_map('trim', explode(',', $g['anggota']));
                    if (in_array($userName, $members)) {
                        $group = $g;
                        break;
                    }
                }
            }

            $pklLastMonitoring = null;
            if ($group) {
                $pklGroupData = $group;
                $pklMembers = array_map('trim', explode(',', $group['anggota']));
                $todayRaw = $attendancePklModel
                    ->where('date', $today)
                    ->where('ketua_phone', $group['ketua_phone'])
                    ->first();
                if ($todayRaw) {
                    $todayRaw['attendance_data'] = json_decode($todayRaw['attendance_data'] ?: '{}', true) ?: [];
                    $todayRaw['jurnal'] = json_decode($todayRaw['jurnal_kegiatan'] ?: '{}', true) ?: [];
                    $todayRaw['photo_urls'] = json_decode($todayRaw['photo_url'] ?: '[]', true) ?: [];
                    $pklTodayReport = $todayRaw;
                }

                // Get last monitoring details
                $monitoringPklModel = new \App\Models\MonitoringPklModel();
                $lastMon = $monitoringPklModel->where('kelompok_pkl_id', $group['id'])
                                              ->orderBy('tanggal', 'DESC')
                                              ->first();
                if ($lastMon) {
                    $allowedNumberModel = new \App\Models\AllowedNumberModel();
                    $teacher = $allowedNumberModel->where('phone', $lastMon['pembimbing_phone'])->first();
                    $pklLastMonitoring = [
                        'tanggal' => date('d M Y', strtotime($lastMon['tanggal'])),
                        'pembimbing' => $teacher ? ($teacher['name'] ?? $teacher['name']) : $lastMon['pembimbing_phone'],
                        'catatan' => $lastMon['catatan']
                    ];
                }
            }

            // Set flag isPklMember di session agar sidebar bisa pakai
            $isPklMember = ($group !== null);
            $session->set('is_pkl_member', $isPklMember);
        }

        // === Logika Notifikasi Pintar (v1.6.8 - Refined) ===
        $notifications = [];
        $scheduleModel = new \App\Models\ScheduleModel();
        $attendanceKelasModel = new \App\Models\AttendanceKelasModel();
        // Note: $messageLogModel tidak diperlukan (hanya notif KBM & PKL yang ditampilkan)

        // Hari dalam bahasa Indonesia
        $dayNames = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu'
        ];
        $todayDay = $dayNames[date('l')] ?? '';

        if (in_array($userRole, ['admin', 'kepsek', 'guru', 'guru_bk', 'guru_mapel'])) {
            // Libur = Minggu OTOMATIS atau dari tabel hari_libur → skip notifikasi KBM
            $isMinggu = ($todayDay === 'Minggu');
            if ($isHoliday || $isMinggu) {
                $reason = $isHoliday ? esc($todayHoliday['reason']) : 'Minggu';
                $notifications[] = [
                    'type'    => 'info',
                    'icon'    => 'fas fa-calendar-times',
                    'message' => '<strong>🛌 Hari Ini Libur:</strong> ' . $reason . ' — Notifikasi absensi KBM dinonaktifkan.'
                ];
            } else {
                // 1. KBM belum diabsen (seluruh kelas hari ini)
                $schedulesToday = $scheduleModel->where('day', $todayDay)->findAll();
                $classesToday = array_unique(array_column($schedulesToday, 'class_name'));

                $attendanceTodayRows = $attendanceKelasModel->where('date', $today)->where('tahun_pelajaran_id', $tpId)->findAll();
                $classesDoneToday = array_column($attendanceTodayRows, 'class_name');

                $classesPending = array_diff($classesToday, $classesDoneToday);
                if (!empty($classesPending)) {
                    $notifications[] = [
                        'type'    => 'warning',
                        'icon'    => 'fas fa-calendar-times',
                        'message' => 'Ada <strong>' . count($classesPending) . ' Kelas</strong> belum melakukan absensi KBM hari ini: ' . implode(', ', $classesPending) . '.'
                    ];
                }
            }

            // 2. Kelompok PKL belum lapor (TETAP MUNCUL meskipun hari libur/Minggu)
            if ($settingModel->getSetting('pkl_active', '1') === '1') {
                $allGroups = $kelompokPklModel->where('tahun_pelajaran_id', $tpId)->findAll();
                $reportsToday = $attendancePklModel->where('date', $today)->where('tahun_pelajaran_id', $tpId)->findAll();
                $reportedKetuaPhones = array_column($reportsToday, 'ketua_phone');

                $groupsPending = [];
                foreach ($allGroups as $g) {
                    if (!in_array($g['ketua_phone'], $reportedKetuaPhones)) {
                        $groupsPending[] = $g['tempat_pkl'];
                    }
                }
                if (!empty($groupsPending)) {
                    $notifications[] = [
                        'type'    => 'danger',
                        'icon'    => 'fas fa-briefcase',
                        'message' => 'Ada <strong>' . count($groupsPending) . ' Kelompok PKL</strong> belum mengirimkan laporan hari ini: ' . implode(', ', $groupsPending) . '.'
                    ];
                }
            }

        } elseif (in_array($userRole, ['ketua_pkl', 'anggotapkl'])) {
            if ($settingModel->getSetting('pkl_active', '1') !== '1') {
                $notifications[] = [
                    'type'    => 'info',
                    'icon'    => 'fas fa-info-circle',
                    'message' => '<strong>Info:</strong> Fitur pelaporan PKL belum dimulai atau sedang dinonaktifkan oleh Admin.'
                ];
            } else {
                // Siswa PKL (Ketua / Anggota): Tampilkan notifikasi hanya apabila kelompoknya belum mengisi laporan hari ini
                if (empty($pklTodayReport)) {
                    if ($userRole === 'ketua_pkl') {
                        $notifications[] = [
                            'type'    => 'danger',
                            'icon'    => 'fas fa-exclamation-triangle',
                            'message' => '<strong>PENTING:</strong> Kelompok Anda belum mengirimkan laporan PKL hari ini! Harap segera mengisi laporan.'
                        ];
                    } else {
                        $notifications[] = [
                            'type'    => 'warning',
                            'icon'    => 'fas fa-info-circle',
                            'message' => 'Laporan PKL kelompok Anda hari ini belum dikirimkan oleh Ketua Kelompok. Silakan ingatkan ketua untuk segera melapor.'
                        ];
                    }
                }
            }
        } // Role siswa biasa tidak mendapatkan notifikasi apa pun (kosong)

        // === Logika Data Grafik Analitik (Vite/Chart.js) ===
        $monthLabels = json_encode([]);
        $hadirKbmMonth = json_encode([]);
        $hadirPklMonth = json_encode([]);
        $kbmAbsenData = json_encode([0, 0, 0]);
        $pklAbsenData = json_encode([0, 0, 0]);
        $dailyPklLabels = json_encode([]);
        $dailyPklData = json_encode([]);

        if (in_array($userRole, ['admin', 'kepsek', 'guru', 'guru_bk', 'guru_mapel'])) {
            $monthLabelsArr = [];
            $hadirKbmMonthArr = [];
            $hadirPklMonthArr = [];
            for ($i = 5; $i >= 0; $i--) {
                $monthString = date('Y-m', strtotime("-$i months"));
                $monthLabelsArr[] = date('M Y', strtotime("-$i months"));
                
                $hadirKbmMonthArr[] = $attendanceModel->like('date', $monthString, 'after')
                                                   ->where('status', 'hadir')
                                                   ->notLike('note', 'PKL:')
                                                   ->where('tahun_pelajaran_id', $tpId)
                                                   ->countAllResults();

                $hadirPklMonthArr[] = $attendanceModel->like('date', $monthString, 'after')
                                                   ->where('status', 'hadir')
                                                   ->like('note', 'PKL:')
                                                   ->where('tahun_pelajaran_id', $tpId)
                                                   ->countAllResults();
            }
            $monthLabels = json_encode($monthLabelsArr);
            $hadirKbmMonth = json_encode($hadirKbmMonthArr);
            $hadirPklMonth = json_encode($hadirPklMonthArr);

            // KBM Ketidakhadiran
            $kbmSakit = $attendanceModel->where('status', 'sakit')->notLike('note', 'PKL:')->where('tahun_pelajaran_id', $tpId)->countAllResults();
            $kbmIzin  = $attendanceModel->where('status', 'izin')->notLike('note', 'PKL:')->where('tahun_pelajaran_id', $tpId)->countAllResults();
            $kbmAlpha = $attendanceModel->where('status', 'alpha')->notLike('note', 'PKL:')->where('tahun_pelajaran_id', $tpId)->countAllResults();
            $kbmAbsenData = json_encode([$kbmSakit, $kbmIzin, $kbmAlpha]);

            // PKL Ketidakhadiran
            $pklSakit = $attendanceModel->where('status', 'sakit')->like('note', 'PKL:')->where('tahun_pelajaran_id', $tpId)->countAllResults();
            $pklIzin  = $attendanceModel->where('status', 'izin')->like('note', 'PKL:')->where('tahun_pelajaran_id', $tpId)->countAllResults();
            $pklAlpha = $attendanceModel->where('status', 'alpha')->like('note', 'PKL:')->where('tahun_pelajaran_id', $tpId)->countAllResults();
            $pklAbsenData = json_encode([$pklSakit, $pklIzin, $pklAlpha]);

            // Tren harian laporan PKL (14 Hari Terakhir)
            $dailyPklLabelsArr = [];
            $dailyPklDataArr = [];
            for ($i = 13; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-$i days"));
                $dailyPklLabelsArr[] = date('d M', strtotime($d));
                $dailyPklDataArr[] = $attendancePklModel->where('date', $d)->where('tahun_pelajaran_id', $tpId)->countAllResults();
            }
            $dailyPklLabels = json_encode($dailyPklLabelsArr);
            $dailyPklData = json_encode($dailyPklDataArr);
        }

        $data = [
            'title' => 'Dashboard SIAKANUDA',
            'userName' => $userName,
            'userRole' => $userRole,
            'roleLabel' => $roleLabel,
            'totalStudents' => $totalStudents,
            'attendanceToday' => [
                'hadir' => $totalHadirToday,
                'sakit' => $totalSakitToday,
                'izin' => $totalIzinToday,
                'alpha' => $totalAlphaToday
            ],
            'totalPklSiswaHadirToday' => $totalPklSiswaHadirToday,
            'totalPklToday' => $totalPklToday,
            'totalViolationsToday' => $totalViolationsToday,
            'recentFeedbacks' => $recentFeedbacks,
            'personalAttendance' => $personalAttendance,
            'isPklMember' => $session->get('is_pkl_member') ?? false,
            // PKL personal data
            'pklGroupData' => $pklGroupData,
            'pklTodayReport' => $pklTodayReport,
            'pklIsKetua' => $pklIsKetua,
            'pklMembers' => $pklMembers,
            'pklActive' => $settingModel->getSetting('pkl_active', '1'),
            'pklBimbinganGroups' => $pklBimbinganGroups,
            'pklLastMonitoring' => $pklLastMonitoring ?? null,
            'kalender' => $kalender ?? [],
            'personalAchievements' => $personalAchievements,
            'recentAchievements' => $recentAchievements,
            'notifications' => $notifications, // Kirim notifikasi ke view
            'todayHoliday' => $todayHoliday, // Info hari libur untuk JS realtime
            'monthLabels' => $monthLabels,
            'hadirKbmMonth' => $hadirKbmMonth,
            'hadirPklMonth' => $hadirPklMonth,
            'kbmAbsenData' => $kbmAbsenData,
            'pklAbsenData' => $pklAbsenData,
            'dailyPklLabels' => $dailyPklLabels,
            'dailyPklData' => $dailyPklData,
        ];

        return view('dashboard/index', $data);
    }
}
