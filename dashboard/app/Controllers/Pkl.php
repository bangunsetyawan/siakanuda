<?php

namespace App\Controllers;

use App\Models\KelompokPklModel;
use App\Models\AttendancePklModel;
use App\Models\AllowedNumberModel;
use App\Models\StudentModel;
use App\Models\AttendanceModel;
use App\Models\SettingModel;

class Pkl extends BaseController
{
    public function index()
    {
        $session = session();
        $userType = $session->get('user_type');
        $phone = $session->get('phone');
        $tpId = $this->getActiveTPId();
        
        $attendancePklModel = new AttendancePklModel();
        $kelompokPklModel = new KelompokPklModel();
        $allowedNumberModel = new AllowedNumberModel();
        $studentModel = new StudentModel();
        $settingModel = new SettingModel();
        
        $pklActive = $settingModel->getSetting('pkl_active', '1');
        $pklTimeLimit = $settingModel->getSetting('pkl_time_limit', '23:59');

        if ($userType === 'student') {
            // Find student's group
            $group = null;
            if (!empty($phone)) {
                $group = $kelompokPklModel->where('ketua_phone', $phone)
                                          ->where('tahun_pelajaran_id', $tpId)
                                          ->first();
            }
            $isKetua = ($group !== null);
            
            if (!$group) {
                // Check if they are a member
                $studentName = $session->get('name');
                if (!empty($studentName)) {
                    $allGroups = $kelompokPklModel->where('tahun_pelajaran_id', $tpId)->findAll();
                    foreach ($allGroups as $g) {
                        $members = explode(',', $g['anggota']);
                        $members = array_map('trim', $members);
                        if (in_array($studentName, $members)) {
                            $group = $g;
                            break;
                        }
                    }
                }
            }

            if (!$group) {
                return redirect()->to('/dashboard')->with('error', 'Akses ditolak. Menu PKL hanya untuk siswa PKL.');
            }

            if (empty($group['ketua_phone'])) {
                return redirect()->to('/dashboard')->with('error', 'Akses ditolak. Kelompok PKL Anda belum memiliki Ketua Kelompok. Silakan hubungi Admin.');
            }

            // Get group history
            $reports = $attendancePklModel->where('ketua_phone', $group['ketua_phone'])
                                           ->where('tahun_pelajaran_id', $tpId)
                                           ->orderBy('date', 'DESC')
                                           ->findAll();
            $parsedReports = [];
            foreach ($reports as $r) {
                $rawUrls = json_decode($r['photo_url'] ?: '[]', true) ?: [];
                $urls = [];
                foreach ($rawUrls as $name => $url) {
                    $urls[$name] = get_photo_display_url($url);
                }
                $parsedReports[] = [
                    'id' => $r['id'],
                    'date' => $r['date'],
                    'tempat_pkl' => $r['tempat_pkl'],
                    'ketua_phone' => $r['ketua_phone'],
                    'status_libur' => $r['status_libur'],
                    'location_data' => $r['location_data'],
                    'photo_urls' => $urls,
                    'jurnal' => json_decode($r['jurnal_kegiatan'] ?: '{}', true) ?: [],
                    'attendance_data' => json_decode($r['attendance_data'] ?: '{}', true) ?: [],
                    'is_takeover' => $r['is_takeover']
                ];
            }

            // Get selected date's report
            $today = date('Y-m-d');
            $selectedDate = $this->request->getGet('date') ?: $today;
            
            // Validate selectedDate
            if ($selectedDate > $today) {
                return redirect()->to('/pkl')->with('error', 'Tanggal tidak boleh lebih dari hari ini.');
            }
            
            $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
            $isLocked = ($selectedDate < $sevenDaysAgo);

            $todayReport = $attendancePklModel->where('date', $selectedDate)
                                              ->where('ketua_phone', $group['ketua_phone'])
                                              ->where('tahun_pelajaran_id', $tpId)
                                              ->first();
            
            if ($todayReport) {
                $rawUrls = json_decode($todayReport['photo_url'] ?: '[]', true) ?: [];
                $urls = [];
                foreach ($rawUrls as $name => $url) {
                    $urls[$name] = get_photo_display_url($url);
                }
                $todayReport['photo_urls'] = $urls;
                $todayReport['jurnal'] = json_decode($todayReport['jurnal_kegiatan'] ?: '{}', true) ?: [];
                $todayReport['attendance_data'] = json_decode($todayReport['attendance_data'] ?: '{}', true) ?: [];
            }

            $membersList = explode(',', $group['anggota']);
            $membersList = array_map('trim', $membersList);

            $data = [
                'title' => 'Laporan Harian PKL',
                'group' => $group,
                'isKetua' => $isKetua,
                'members' => $membersList,
                'reports' => $parsedReports,
                'todayReport' => $todayReport,
                'today' => $today,
                'selectedDate' => $selectedDate,
                'isLocked' => $isLocked,
                'isTakeover' => false,
                'pklActive' => $pklActive,
                'pklTimeLimit' => $pklTimeLimit
            ];

            return view('pkl/student_report', $data);
        }

        // Administrative View (Staff / Admin / Kepsek)
        $date = $this->request->getGet('date') ?: date('Y-m-d');
        
        $reports = $attendancePklModel->where('date', $date)
                                      ->where('tahun_pelajaran_id', $tpId)
                                      ->orderBy('tempat_pkl', 'ASC')
                                      ->findAll();

        $role = $session->get('role');

        $parsedReports = [];
        foreach ($reports as $r) {
            $rawUrls = json_decode($r['photo_url'] ?: '[]', true) ?: [];
            $urls = [];
            foreach ($rawUrls as $name => $url) {
                $urls[$name] = get_photo_display_url($url);
            }
            $parsedReports[] = [
                'id' => $r['id'],
                'date' => $r['date'],
                'tempat_pkl' => $r['tempat_pkl'],
                'ketua_phone' => $r['ketua_phone'],
                'status_libur' => $r['status_libur'],
                'location_data' => $r['location_data'],
                'photo_urls' => $urls,
                'jurnal' => json_decode($r['jurnal_kegiatan'] ?: '{}', true) ?: [],
                'attendance_data' => json_decode($r['attendance_data'] ?: '{}', true) ?: [],
                'is_takeover' => $r['is_takeover']
            ];
        }

        $totalGroups = $kelompokPklModel->where('tahun_pelajaran_id', $tpId)->countAllResults();

        $data = [
            'title' => 'Laporan Harian PKL',
            'reports' => $parsedReports,
            'date' => $date,
            'totalGroups' => $totalGroups,
            'totalReported' => count($parsedReports),
            'userRole' => $role,
            'pklActive' => $pklActive,
            'pklTimeLimit' => $pklTimeLimit
        ];

        return view('pkl/index', $data);
    }

    public function takeoverReport()
    {
        $session = session();
        $role = $session->get('role');
        if (!in_array($role, ['admin', 'kepsek', 'guru', 'guru_mapel', 'guru_bk'])) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        $phone = $session->get('phone');
        $tpId = $this->getActiveTPId();
        
        $attendancePklModel = new AttendancePklModel();
        $kelompokPklModel = new KelompokPklModel();
        $settingModel = new SettingModel();

        $groupId = $this->request->getGet('group_id');
        $selectedDate = $this->request->getGet('date') ?: date('Y-m-d');
        
        $group = $kelompokPklModel->where('id', $groupId)->where('tahun_pelajaran_id', $tpId)->first();
        if (!$group) {
            return redirect()->to('/pkl')->with('error', 'Kelompok tidak ditemukan.');
        }

        // For guru/guru_mapel, verify they are the pembimbing
        if (in_array($role, ['guru', 'guru_mapel'])) {
            if ($group['pembimbing_phone'] !== $phone) {
                return redirect()->to('/pkl')->with('error', 'Akses ditolak. Anda hanya dapat mengisi laporan susulan kelompok bimbingan Anda.');
            }
        }

        if (empty($group['ketua_phone'])) {
            return redirect()->to('/pkl?date=' . $selectedDate)->with('error', 'Kelompok ini belum memiliki ketua, tidak bisa mengisi laporan.');
        }

        // We act as "Ketua" for the view to allow editing
        $isKetua = true;
        
        // Get group history
        $reports = $attendancePklModel->where('ketua_phone', $group['ketua_phone'])
                                       ->where('tahun_pelajaran_id', $tpId)
                                       ->orderBy('date', 'DESC')
                                       ->findAll();
        $parsedReports = [];
        foreach ($reports as $r) {
            $rawUrls = json_decode($r['photo_url'] ?: '[]', true) ?: [];
            $urls = [];
            foreach ($rawUrls as $name => $url) {
                $urls[$name] = get_photo_display_url($url);
            }
            $parsedReports[] = [
                'id' => $r['id'],
                'date' => $r['date'],
                'tempat_pkl' => $r['tempat_pkl'],
                'ketua_phone' => $r['ketua_phone'],
                'status_libur' => $r['status_libur'],
                'location_data' => $r['location_data'],
                'photo_urls' => $urls,
                'jurnal' => json_decode($r['jurnal_kegiatan'] ?: '{}', true) ?: [],
                'attendance_data' => json_decode($r['attendance_data'] ?: '{}', true) ?: [],
                'is_takeover' => $r['is_takeover']
            ];
        }

        $todayReport = $attendancePklModel->where('date', $selectedDate)
                                          ->where('ketua_phone', $group['ketua_phone'])
                                          ->where('tahun_pelajaran_id', $tpId)
                                          ->first();
        
        if ($todayReport) {
            $rawUrls = json_decode($todayReport['photo_url'] ?: '[]', true) ?: [];
            $urls = [];
            foreach ($rawUrls as $name => $url) {
                $urls[$name] = get_photo_display_url($url);
            }
            $todayReport['photo_urls'] = $urls;
            $todayReport['jurnal'] = json_decode($todayReport['jurnal_kegiatan'] ?: '{}', true) ?: [];
            $todayReport['attendance_data'] = json_decode($todayReport['attendance_data'] ?: '{}', true) ?: [];
        }

        $membersList = explode(',', $group['anggota']);
        $membersList = array_map('trim', $membersList);

        $data = [
            'title' => 'Input Laporan Susulan PKL (Takeover)',
            'group' => $group,
            'isKetua' => $isKetua,
            'members' => $membersList,
            'reports' => $parsedReports,
            'todayReport' => $todayReport,
            'today' => date('Y-m-d'),
            'selectedDate' => $selectedDate,
            'isLocked' => false,
            'isTakeover' => true,
            'pklActive' => $settingModel->getSetting('pkl_active', '1'),
            'pklTimeLimit' => $settingModel->getSetting('pkl_time_limit', '23:59')
        ];

        return view('pkl/student_report', $data);
    }

    public function submitReport()
    {
        $session = session();
        $isTakeover = $this->request->getPost('is_takeover') == '1';
        $userType = $session->get('user_type');
        $role = $session->get('role');
        
        if ($isTakeover) {
            if (!in_array($role, ['admin', 'kepsek', 'guru', 'guru_mapel', 'guru_bk'])) {
                return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
            }
            $phone = $this->request->getPost('ketua_phone');
        } else {
            if ($userType !== 'student') {
                return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
            }
            $phone = $session->get('phone');
        }

        $kelompokPklModel = new KelompokPklModel();
        $attendancePklModel = new AttendancePklModel();
        $studentModel = new StudentModel();
        $attendanceModel = new AttendanceModel();
        $tpId = $this->getActiveTPId();

        // Verify they are indeed the ketua of their group
        $group = $kelompokPklModel->where('ketua_phone', $phone)
                                  ->where('tahun_pelajaran_id', $tpId)
                                  ->first();
        if (!$group) {
            return redirect()->to('/pkl')->with('error', 'Kelompok tidak ditemukan.');
        }

        $settingModel = new SettingModel();
        if ($settingModel->getSetting('pkl_active', '1') !== '1') {
            return redirect()->to('/pkl')->with('error', 'Fitur laporan harian PKL sedang dinonaktifkan oleh Admin.');
        }
        
        $timeLimit = $settingModel->getSetting('pkl_time_limit', '23:59');
        $currentTime = date('H:i');
        if (!$isTakeover && $currentTime > $timeLimit) {
            return redirect()->to('/pkl')->with('error', "Batas waktu pelaporan harian PKL adalah pukul {$timeLimit} WIB.");
        }

        $today = date('Y-m-d');
        $reportDate = $this->request->getPost('report_date') ?: $today;

        if ($reportDate > $today) {
            return redirect()->back()->withInput()->with('error', 'Tanggal laporan tidak boleh lebih dari hari ini.');
        }
        
        if (!$isTakeover) {
            $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
            if ($reportDate < $sevenDaysAgo) {
                return redirect()->back()->withInput()->with('error', 'Laporan susulan maksimal H-7 dari hari ini.');
            }
        }

        $statusLibur = $this->request->getPost('status_libur') ? 1 : 0;
        $liburReason = $this->request->getPost('libur_reason') ?: null;
        $locationData = $this->request->getPost('location_data') ?: null;
        
        $attendanceInput = $this->request->getPost('attendance') ?: []; // array of name => status
        $jurnalInput = $this->request->getPost('jurnal') ?: []; // array of name => text
        
        $members = explode(',', $group['anggota']);
        $members = array_map('trim', $members);

        // Server-side journal capitalization & formatting
        foreach ($members as $name) {
            if (isset($jurnalInput[$name])) {
                $jurnalVal = trim($jurnalInput[$name]);
                if ($jurnalVal !== '') {
                    $jurnalVal = ucfirst($jurnalVal);
                    $jurnalInput[$name] = $jurnalVal;
                }
            }
        }

        // Fetch existing report if exists to keep photos and check for validation
        $existing = $attendancePklModel->where('date', $reportDate)
                                       ->where('ketua_phone', $phone)
                                       ->where('tahun_pelajaran_id', $tpId)
                                       ->first();
        $photoUrls = [];
        if ($existing && !empty($existing['photo_url'])) {
            $photoUrls = json_decode($existing['photo_url'], true) ?: [];
        }

        $sakitDetailInput = $this->request->getPost('sakit_detail') ?: [];
        $izinDetailInput = $this->request->getPost('izin_detail') ?: [];
        $alphaHubungiInput = $this->request->getPost('alpha_hubungi') ?: [];
        $alphaDetailInput = $this->request->getPost('alpha_detail') ?: [];

        // Server-side validation
        if ($statusLibur == 1) {
            if (empty(trim($liburReason))) {
                return redirect()->back()->withInput()->with('error', 'Alasan tempat PKL libur/tutup wajib diisi.');
            }
        } else {
            $hasExistingKelompokPhoto = isset($photoUrls['kelompok']) && !empty($photoUrls['kelompok']);
            $fileKelompok = $this->request->getFile('photo_kelompok');
            $hasNewKelompokPhoto = $fileKelompok && $fileKelompok->isValid() && !$fileKelompok->hasMoved();

            if (!$hasNewKelompokPhoto && !$hasExistingKelompokPhoto) {
                return redirect()->back()->withInput()->with('error', 'Wajib mengunggah foto dokumentasi kelompok hari ini!');
            }

            foreach ($members as $name) {
                $status = $attendanceInput[$name] ?? 'hadir';
                $hasExistingBukti = isset($photoUrls[$name]) && !empty($photoUrls[$name]);
                
                $inputName = 'photo_bukti_' . preg_replace('/[^a-zA-Z0-9]/', '_', $name);
                $fileBukti = $this->request->getFile($inputName);
                $hasNewBukti = $fileBukti && $fileBukti->isValid() && !$fileBukti->hasMoved();

                if ($status === 'hadir') {
                    $jurnalVal = trim($jurnalInput[$name] ?? '');
                    if (strlen($jurnalVal) < 75) {
                        return redirect()->back()->withInput()->with('error', 'Isian jurnal kegiatan untuk ' . $name . ' minimal harus 75 karakter! (Saat ini: ' . strlen($jurnalVal) . ' karakter)');
                    }
                } elseif ($status === 'sakit') {
                    $sakitText = trim($sakitDetailInput[$name] ?? '');
                    if (empty($sakitText)) {
                        return redirect()->back()->withInput()->with('error', 'Wajib mengisi keterangan sakit apa untuk ' . $name . '!');
                    }
                    if (!$hasNewBukti && !$hasExistingBukti) {
                        return redirect()->back()->withInput()->with('error', 'Wajib mengunggah foto bukti (surat dokter / screenshot izin ortu) untuk ' . $name . ' yang berstatus Sakit!');
                    }
                } elseif ($status === 'izin') {
                    $izinText = trim($izinDetailInput[$name] ?? '');
                    if (empty($izinText)) {
                        return redirect()->back()->withInput()->with('error', 'Wajib mengisi alasan izin untuk ' . $name . '!');
                    }
                    if (!$hasNewBukti && !$hasExistingBukti) {
                        return redirect()->back()->withInput()->with('error', 'Wajib mengunggah foto bukti izin (surat izin / screenshot chat persetujuan) untuk ' . $name . ' yang berstatus Izin!');
                    }
                }
            }
        }

        // Ensure directories exist
        $uploadPath = ROOTPATH . '../dashboard/public/uploads/pkl';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Helper to handle upload
        $handleUpload = function($file, $keyName) use ($uploadPath, &$photoUrls) {
            if ($file && $file->isValid() && !$file->hasMoved()) {
                if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/jpg'])) {
                    return 'Format foto harus JPG atau PNG.';
                }
                if ($file->getSizeByUnit('mb') > 5) {
                    return 'Ukuran foto maksimal 5MB.';
                }
                $newName = $file->getRandomName();
                $file->move($uploadPath, $newName);
                $photoUrls[$keyName] = '/uploads/pkl/' . $newName;
            }
            return null;
        };

        if ($statusLibur == 0) {
            $fileKelompok = $this->request->getFile('photo_kelompok');
            if ($err = $handleUpload($fileKelompok, 'kelompok')) {
                return redirect()->back()->withInput()->with('error', 'Foto Kelompok: ' . $err);
            }

            foreach ($members as $name) {
                $status = $attendanceInput[$name] ?? 'hadir';
                if (in_array($status, ['sakit', 'izin', 'alpha'])) {
                    $inputName = 'photo_bukti_' . preg_replace('/[^a-zA-Z0-9]/', '_', $name);
                    $fileBukti = $this->request->getFile($inputName);
                    if ($err = $handleUpload($fileBukti, $name)) {
                        return redirect()->back()->withInput()->with('error', 'Foto Bukti ' . $name . ': ' . $err);
                    }
                }
            }
        }

        $finalJurnalInput = [];
        if ($statusLibur == 0) {
            foreach ($members as $name) {
                $status = $attendanceInput[$name] ?? 'hadir';
                if ($status === 'hadir') {
                    $finalJurnalInput[$name] = trim($jurnalInput[$name] ?? '');
                } elseif ($status === 'sakit') {
                    $detail = trim($sakitDetailInput[$name] ?? '');
                    $finalJurnalInput[$name] = 'Sakit: ' . $detail;
                } elseif ($status === 'izin') {
                    $detail = trim($izinDetailInput[$name] ?? '');
                    $finalJurnalInput[$name] = 'Izin: ' . $detail;
                } elseif ($status === 'alpha') {
                    $hubungi = $alphaHubungiInput[$name] ?? 'Tidak';
                    $detail = trim($alphaDetailInput[$name] ?? '');
                    $finalJurnalInput[$name] = 'Sudah dihubungi: ' . $hubungi . ($detail ? '. Alasan: ' . $detail : '');
                }
            }
        }

        // Save individual student attendances if not libur
        if ($statusLibur == 0) {
            foreach ($members as $name) {
                $status = $attendanceInput[$name] ?? 'hadir';
                $jurnal = $finalJurnalInput[$name] ?? '';

                // Find student ID in DB to sync with main KBM attendance
                $student = $studentModel->where('name', $name)->first();
                if ($student) {
                    $existingAtt = $attendanceModel->where('student_id', $student['id'])
                                                   ->where('date', $reportDate)
                                                   ->where('tahun_pelajaran_id', $tpId)
                                                   ->first();
                    $attData = [
                        'student_id' => $student['id'],
                        'date' => $reportDate,
                        'status' => $status,
                        'note' => 'PKL: ' . $group['tempat_pkl'] . ($jurnal ? ' (' . $jurnal . ')' : ''),
                        'tahun_pelajaran_id' => $tpId
                    ];
                    if ($existingAtt) {
                        $attendanceModel->update($existingAtt['id'], $attData);
                    } else {
                        $attendanceModel->insert($attData);
                    }
                }
            }
        }

        // Save to attendance_pkl
        $saveData = [
            'date' => $reportDate,
            'tempat_pkl' => $group['tempat_pkl'],
            'ketua_phone' => $phone,
            'status_libur' => $statusLibur,
            'libur_reason' => $liburReason,
            'location_data' => $locationData,
            'attendance_data' => json_encode($attendanceInput),
            'photo_url' => json_encode($photoUrls),
            'jurnal_kegiatan' => json_encode($finalJurnalInput),
            'is_takeover' => $isTakeover ? 1 : 0,
            'tahun_pelajaran_id' => $tpId
        ];

        if ($existing) {
            $attendancePklModel->update($existing['id'], $saveData);
        } else {
            $attendancePklModel->insert($saveData);
        }

        // Call Node.js API to broadcast WA notification
        try {
            $client = \Config\Services::curlrequest();
            $client->post('http://localhost:7860/api/pkl/broadcast', [
                'json' => [
                    'date' => $reportDate,
                    'ketua_phone' => $phone,
                    'is_update' => $existing ? true : false
                ],
                'timeout' => 5
            ]);
        } catch (\Exception $e) {
            // Log to prevent blocking submission if bot is down
            log_message('error', 'Failed to send PKL WA broadcast: ' . $e->getMessage());
        }

        // Format success message with current day, date, month, and real time in Indonesian
        $timezone = new \DateTimeZone('Asia/Jakarta');
        $dateTime = new \DateTime('now', $timezone);
        
        $daysIndo = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $monthsIndo = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        
        $dayIndo = $daysIndo[$dateTime->format('l')];
        $monthIndo = $monthsIndo[$dateTime->format('m')];
        $timeString = $dayIndo . ', ' . $dateTime->format('d') . ' ' . $monthIndo . ' ' . $dateTime->format('Y') . ' pukul ' . $dateTime->format('H:i:s') . ' WIB';
        
        $successMsg = "Berhasil mengisi absensi pada {$timeString}, Notifikasi berhasil dikirim ke Grup Whatsapp Sekolah dan pembimbing PKL.";

        return redirect()->to('/pkl?date=' . $reportDate)->with('success', $successMsg);
    }

    public function groups()
    {
        $session = session();
        $role = $session->get('role');
        if (!in_array($role, ['admin', 'kepsek', 'guru', 'guru_mapel', 'guru_bk'])) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        $kelompokPklModel = new KelompokPklModel();
        $allowedNumberModel = new AllowedNumberModel();
        $tpId = $this->getActiveTPId();

        $groups = $kelompokPklModel->where('tahun_pelajaran_id', $tpId)->orderBy('tempat_pkl', 'ASC')->findAll();
        $teachers = $allowedNumberModel->whereIn('role', ['admin', 'kepsek', 'guru_bk', 'guru', 'guru_mapel'])
                                       ->where('active', 1)
                                       ->findAll();

        $studentModel = new StudentModel();
        $studentModel->syncPklRoles($tpId);
        $students = $studentModel->groupStart()
                                     ->where('is_active', 1)
                                     ->orWhere('is_active IS NULL')
                                 ->groupEnd()
                                 ->like('class', 'XII', 'after')
                                 ->orderBy('class', 'ASC')
                                 ->orderBy('name', 'ASC')
                                 ->findAll();

        $data = [
            'title' => 'Kelompok PKL (DU/DI)',
            'groups' => $groups,
            'teachers' => $teachers,
            'students' => $students
        ];

        return view('pkl/groups', $data);
    }

    public function createGroup()
    {
        $session = session();
        $role = $session->get('role');
        if (!in_array($role, ['admin', 'kepsek', 'guru', 'guru_mapel', 'guru_bk'])) {
            return redirect()->to('/pkl/groups')->with('error', 'Akses ditolak.');
        }

        $kelompokPklModel = new KelompokPklModel();

        $groupData = [
            'tempat_pkl' => $this->request->getPost('tempat_pkl'),
            'ketua_phone' => $this->request->getPost('ketua_phone') ?: '',
            'anggota' => $this->request->getPost('anggota'),
            'pembimbing_phone' => $this->request->getPost('pembimbing_phone') ?: null,
            'tahun_pelajaran_id' => $this->getActiveTPId()
        ];

        if (!$kelompokPklModel->validate($groupData)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $kelompokPklModel->errors()));
        }

        $kelompokPklModel->insert($groupData);
        $studentModel = new StudentModel();
        $studentModel->syncPklRoles($this->getActiveTPId());

        return redirect()->to('/pkl/groups')->with('success', 'Kelompok PKL berhasil ditambahkan.');
    }

    public function importGroups()
    {
        $session = session();
        $role = $session->get('role');
        if (!in_array($role, ['admin', 'kepsek', 'guru', 'guru_mapel', 'guru_bk'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }

        $json = $this->request->getJSON(true);
        if (!$json || !isset($json['groups']) || !is_array($json['groups'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Format data tidak valid.']);
        }

        $kelompokPklModel = new KelompokPklModel();
        $studentModel = new StudentModel();
        $tpId = $this->getActiveTPId();
        
        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($json['groups'] as $index => $row) {
            $tempatPkl = isset($row['tempat_pkl']) ? trim($row['tempat_pkl']) : '';
            $anggota = isset($row['anggota']) ? trim($row['anggota']) : '';
            $ketuaPhone = isset($row['ketua_phone']) ? trim($row['ketua_phone']) : '';
            $pembimbingPhone = isset($row['pembimbing_phone']) ? trim($row['pembimbing_phone']) : null;

            if (empty($tempatPkl) || empty($anggota)) {
                $skippedCount++;
                $errors[] = "Baris " . ($index + 1) . ": Tempat PKL dan Anggota wajib diisi.";
                continue;
            }

            // Clean phone formats
            if (!empty($ketuaPhone)) {
                $ketuaPhone = preg_replace('/[^\d]/', '', $ketuaPhone);
                if (str_starts_with($ketuaPhone, '08')) {
                    $ketuaPhone = '628' . substr($ketuaPhone, 2);
                }
            } else {
                $ketuaPhone = '';
            }

            if (!empty($pembimbingPhone)) {
                $pembimbingPhone = preg_replace('/[^\d]/', '', $pembimbingPhone);
                if (str_starts_with($pembimbingPhone, '08')) {
                    $pembimbingPhone = '628' . substr($pembimbingPhone, 2);
                }
            } else {
                $pembimbingPhone = null;
            }

            // Format anggota names to be comma-separated properly
            $membersArr = array_map('trim', explode(',', $anggota));
            $membersArr = array_filter($membersArr);
            $formattedAnggota = implode(', ', $membersArr);

            $groupData = [
                'tempat_pkl' => $tempatPkl,
                'ketua_phone' => $ketuaPhone,
                'anggota' => $formattedAnggota,
                'pembimbing_phone' => $pembimbingPhone,
                'tahun_pelajaran_id' => $tpId
            ];

            if (!$kelompokPklModel->validate($groupData)) {
                $skippedCount++;
                $errors[] = "Baris " . ($index + 1) . ": " . implode(', ', $kelompokPklModel->errors());
                continue;
            }

            // Check if group already exists for the same active year by DU/DI name (case-insensitive check)
            $existing = $kelompokPklModel->where('tempat_pkl', $tempatPkl)
                                         ->where('tahun_pelajaran_id', $tpId)
                                         ->first();

            if ($existing) {
                // Update
                $kelompokPklModel->update($existing['id'], $groupData);
            } else {
                // Insert
                $kelompokPklModel->insert($groupData);
            }
            $importedCount++;
        }

        // Sync roles at the end
        $studentModel->syncPklRoles($tpId);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => "Berhasil memproses $importedCount data kelompok PKL. (Skipped: $skippedCount)",
            'errors' => $errors
        ]);
    }

    public function updateGroup($id)
    {
        $session = session();
        $role = $session->get('role');
        $phone = $session->get('phone');

        $kelompokPklModel = new KelompokPklModel();
        $group = $kelompokPklModel->find($id);

        if (!$group) {
            return redirect()->to('/pkl/groups')->with('error', 'Kelompok tidak ditemukan.');
        }

        // Access check: Admin/Kepsek can edit any group. Guru can only edit if they are the pembimbing of this group.
        if (!in_array($role, ['admin', 'kepsek']) && (!in_array($role, ['guru', 'guru_mapel', 'guru_bk']) || $group['pembimbing_phone'] !== $phone)) {
            return redirect()->to('/pkl/groups')->with('error', 'Akses ditolak. Anda hanya dapat mengatur kelompok yang Anda bimbing.');
        }

        $groupData = [
            'tempat_pkl' => $this->request->getPost('tempat_pkl'),
            'ketua_phone' => $this->request->getPost('ketua_phone') ?: '',
            'anggota' => $this->request->getPost('anggota'),
            'pembimbing_phone' => $this->request->getPost('pembimbing_phone') ?: null
        ];

        // For guru, keep the same pembimbing_phone (they cannot change the pembimbing)
        if (in_array($role, ['guru', 'guru_mapel', 'guru_bk'])) {
            $groupData['pembimbing_phone'] = $group['pembimbing_phone'];
        }

        if (!$kelompokPklModel->validate($groupData)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $kelompokPklModel->errors()));
        }

        $kelompokPklModel->update($id, $groupData);
        $studentModel = new StudentModel();
        $studentModel->syncPklRoles($this->getActiveTPId());

        return redirect()->to('/pkl/groups')->with('success', 'Kelompok PKL berhasil diperbarui.');
    }

    public function deleteGroup($id)
    {
        $session = session();
        $role = $session->get('role');
        if (!in_array($role, ['admin', 'kepsek'])) {
            return redirect()->to('/pkl/groups')->with('error', 'Akses ditolak. Hanya Admin dan Kepsek yang dapat menghapus kelompok.');
        }

        $kelompokPklModel = new KelompokPklModel();
        $kelompokPklModel->delete($id);
        $studentModel = new StudentModel();
        $studentModel->syncPklRoles($this->getActiveTPId());

        return redirect()->to('/pkl/groups')->with('success', 'Kelompok PKL berhasil dihapus.');
    }

    public function riwayat($ketuaPhone)
    {
        $session = session();
        $userType = $session->get('user_type');
        $phone = $session->get('phone');
        $role = $session->get('role');

        if ($userType === 'student') {
            $kelompokPklModel = new KelompokPklModel();
            $group = $kelompokPklModel->where('ketua_phone', $ketuaPhone)
                                      ->where('tahun_pelajaran_id', $this->getActiveTPId())
                                      ->first();
            if (!$group) {
                return redirect()->to('/pkl')->with('error', 'Kelompok tidak ditemukan.');
            }
            $studentModel = new StudentModel();
            $studentId = $session->get('student_id');
            $student = $studentModel->find($studentId);
            if (!$student) {
                 return redirect()->to('/dashboard')->with('error', 'Data siswa tidak ditemukan.');
            }
            $members = array_map('trim', explode(',', $group['anggota']));
            if ($ketuaPhone !== $phone && !in_array($student['name'], $members)) {
                return redirect()->to('/pkl')->with('error', 'Akses ditolak. Anda bukan anggota kelompok ini.');
            }
        } else {
            if (!in_array($role, ['admin', 'kepsek', 'guru', 'guru_mapel', 'guru_bk'])) {
                return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
            }
        }

        $attendancePklModel = new AttendancePklModel();
        
        $month = $this->request->getGet('month') ?: date('Y-m');
        
        $reports = $attendancePklModel->where('ketua_phone', $ketuaPhone)
                                     ->where('tahun_pelajaran_id', $this->getActiveTPId())
                                     ->like('date', $month, 'after')
                                     ->orderBy('date', 'ASC')
                                     ->findAll();

        $kelompokPklModel = new KelompokPklModel();
        $groupInfo = $kelompokPklModel->where('ketua_phone', $ketuaPhone)->where('tahun_pelajaran_id', $this->getActiveTPId())->first();

        $data = [
            'title' => 'Riwayat Laporan PKL',
            'reports' => $reports,
            'group' => $groupInfo,
            'ketuaPhone' => $ketuaPhone,
            'currentMonth' => $month
        ];

        return view('pkl/riwayat', $data);
    }

    public function deleteReport($id)
    {
        $session = session();
        $role = $session->get('role');
        if ($role !== 'admin') {
            return redirect()->to('/pkl')->with('error', 'Akses ditolak. Hanya Admin yang dapat menghapus laporan.');
        }

        $attendancePklModel = new AttendancePklModel();
        $report = $attendancePklModel->find($id);

        if (!$report) {
            return redirect()->to('/pkl')->with('error', 'Laporan tidak ditemukan.');
        }

        $date = $report['date'];
        $ketuaPhone = $report['ketua_phone'];
        $tpId = $report['tahun_pelajaran_id'];

        // 1. Delete associated KBM attendance entries for the students
        $kelompokPklModel = new KelompokPklModel();
        $studentModel = new StudentModel();
        $attendanceModel = new AttendanceModel();

        $group = $kelompokPklModel->where('ketua_phone', $ketuaPhone)
                                  ->where('tahun_pelajaran_id', $tpId)
                                  ->first();

        if ($group) {
            $members = explode(',', $group['anggota']);
            $members = array_map('trim', $members);
            foreach ($members as $name) {
                $student = $studentModel->where('name', $name)->first();
                if ($student) {
                    $attendanceModel->where('student_id', $student['id'])
                                     ->where('date', $date)
                                     ->where('tahun_pelajaran_id', $tpId)
                                     ->delete();
                }
            }
        }

        // 2. Delete photo files from uploads directory
        $photoUrls = json_decode($report['photo_url'] ?: '[]', true) ?: [];
        $urlsArray = is_array($photoUrls) ? $photoUrls : [$photoUrls];
        foreach ($urlsArray as $url) {
            if (is_string($url) && str_starts_with($url, '/uploads/pkl/')) {
                $filePath = ROOTPATH . '../dashboard/public' . $url;
                if (is_file($filePath)) {
                    @unlink($filePath);
                }
            }
        }

        // 3. Delete from attendance_pkl table
        $attendancePklModel->delete($id);

        return redirect()->to('/pkl?date=' . $date)->with('success', 'Laporan PKL berhasil dihapus.');
    }

    public function printWeeklyPdf($ketuaPhone, $startDate)
    {
        $session = session();
        $userType = $session->get('user_type');
        $phone = $session->get('phone');
        $role = $session->get('role');

        // Access check - Weekly PDF: ONLY Ketua of this group OR Admin/Kepsek
        if ($userType === 'student') {
            // Must be the ketua of this specific group
            if ($phone !== $ketuaPhone) {
                return redirect()->to('/pkl')->with('error', 'Akses ditolak. Hanya ketua kelompok yang dapat mencetak rekap mingguan.');
            }
        } else {
            // Non-student: must be Admin/Kepsek/Guru
            if (!in_array($role, ['admin', 'kepsek', 'guru', 'guru_mapel', 'guru_bk'])) {
                return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
            }
        }

        // Cek apakah akses lokal. Jika tidak, batalkan proses cetak PDF
        if (!is_local_access()) {
            return redirect()->to('/pkl')->with('error', 'Akses ditolak. Pengunduhan PDF laporan mingguan PKL (berisi foto asli kualitas tinggi) hanya dapat dilakukan saat terhubung ke WiFi Sekolah.');
        }

        // Call Node.js API to get the PDF
        try {
            $client = \Config\Services::curlrequest();
            $waBotUrl = env('WA_BOT_URL') ?: 'http://127.0.0.1:7860';
            
            $type = $this->request->getGet('type');
            if ($type !== 'absensi') {
                return redirect()->to('/pkl/riwayat/' . $ketuaPhone);
            }

            $apiUrl = "{$waBotUrl}/api/pkl-report/pdf-weekly/{$ketuaPhone}/{$startDate}?type=absensi";

            $response = $client->get($apiUrl, [
                'timeout' => 30,
                'http_errors' => false,
                'allow_redirects' => false
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                return $this->response
                    ->setHeader('Content-Type', 'application/pdf')
                    ->setHeader('Content-Disposition', 'inline; filename="Laporan_PKL_Mingguan_' . $startDate . '_' . $ketuaPhone . '.pdf"')
                    ->setBody($response->getBody());
            } elseif ($statusCode === 302 || $statusCode === 301) {
                $location = $response->getHeaderLine('Location');
                if (!empty($location)) {
                    return redirect()->to($location);
                } else {
                    return redirect()->to('/pkl')->with('error', 'Gagal membuat laporan PDF: Redirect URL tidak ditemukan.');
                }
            } else {
                $errBody = json_decode($response->getBody(), true);
                $reason = isset($errBody['error']) ? $errBody['error'] : $response->getReasonPhrase();
                return redirect()->to('/pkl')->with('error', 'Gagal membuat rekap mingguan PDF: ' . $reason);
            }
        } catch (\Exception $e) {
            return redirect()->to('/pkl')->with('error', 'Gagal membuat PDF: Layanan latar belakang tidak aktif.');
        }
    }

    public function updateSettings()
    {
        $session = session();
        if (!in_array($session->get('role'), ['admin', 'kepsek'])) {
            return redirect()->to('/pkl')->with('error', 'Akses ditolak.');
        }

        $settingModel = new SettingModel();
        $pklActive = $this->request->getPost('pkl_active') ? '1' : '0';
        $pklTimeLimit = $this->request->getPost('batas_waktu') ?: '23:59';

        $settingModel->setSetting('pkl_active', $pklActive);
        $settingModel->setSetting('pkl_time_limit', $pklTimeLimit);

        return redirect()->to('/pkl')->with('success', 'Pengaturan masa PKL berhasil diperbarui.');
    }

    public function rekapSiswa($ketuaPhone)
    {
        $session = session();
        $role = $session->get('role');
        $userType = $session->get('user_type');
        if (!in_array($role, ['admin', 'kepsek', 'guru', 'guru_bk']) && $userType !== 'student') {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        $studentName = $this->request->getGet('name');
        if (empty($studentName)) {
            return redirect()->back()->with('error', 'Nama siswa tidak ditemukan.');
        }

        $kelompokPklModel = new KelompokPklModel();
        $attendancePklModel = new AttendancePklModel();
        $studentModel = new StudentModel();
        
        $tpId = $this->getActiveTPId();
        $group = $kelompokPklModel->where('ketua_phone', $ketuaPhone)->where('tahun_pelajaran_id', $tpId)->first();
        
        if (!$group) {
            return redirect()->back()->with('error', 'Kelompok tidak ditemukan.');
        }

        if ($session->get('user_type') === 'student' && $session->get('phone') !== $ketuaPhone) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya ketua kelompok yang dapat mencetak.');
        }

        $student = $studentModel->where('name', $studentName)->first();
        $kelasJurusan = $student ? $student['class'] : '-';
        $reports = $attendancePklModel->where('ketua_phone', $ketuaPhone)
                                      ->where('tahun_pelajaran_id', $tpId)
                                      ->orderBy('date', 'ASC')
                                      ->findAll();

        $attendanceMap = [];
        $monthsSet = [];

        foreach ($reports as $report) {
            $date = $report['date'];
            $month = date('Y-m', strtotime($date));
            $monthsSet[$month] = true;

            $data = json_decode($report['attendance_data'] ?: '{}', true) ?: [];
            
            if ($report['status_libur'] == 1) {
                $status = 'L';
            } else {
                $status = isset($data[$studentName]) ? $data[$studentName] : 'A';
            }
            $attendanceMap[$date] = $status;
        }

        $monthsList = [];
        $startMonthStr = !empty($monthsSet) ? min(array_keys($monthsSet)) : date('Y').'-07';
        
        // Selalu mulai dari Juli pada tahun pelajaran aktif
        $tahunPelajaranModel = new \App\Models\TahunPelajaranModel();
        $tp = $tahunPelajaranModel->find($tpId);
        $startYear = date('Y');
        if ($tp && preg_match('/^(\d{4})/', $tp['nama'], $matches)) {
            $startYear = (int)$matches[1];
        } else {
            $startYear = date('Y', strtotime($startMonthStr));
            if (date('m', strtotime($startMonthStr)) < 7) {
                $startYear--;
            }
        }

        $indonesianMonths = [
            '01' => 'JANUARI', '02' => 'FEBRUARI', '03' => 'MARET',
            '04' => 'APRIL', '05' => 'MEI', '06' => 'JUNI',
            '07' => 'JULI', '08' => 'AGUSTUS', '09' => 'SEPTEMBER',
            '10' => 'OKTOBER', '11' => 'NOVEMBER', '12' => 'DESEMBER'
        ];

        $formattedMonths = [];
        // HANYA 4 BULAN: Juli, Agustus, September, Oktober
        for ($i = 0; $i < 4; $i++) {
            $ym = date('Y-m', strtotime("+$i month", strtotime($startYear . '-07-01')));
            list($y, $m) = explode('-', $ym);
            $monthsList[] = $ym;
            $formattedMonths[$ym] = $indonesianMonths[$m] . ' ' . $y;
        }

        // Get Ketua Name
        $ketuaName = '-';
        $ketua = $studentModel->where('phone', $ketuaPhone)
                              ->orWhere('nis', $ketuaPhone)
                              ->first();
        if ($ketua) {
            $ketuaName = $ketua['name'];
        } else {
            // Fallback jika nomor hp ketua tidak ditemukan di tabel students
            $anggota = explode(',', $group['anggota']);
            $ketuaName = trim($anggota[0] ?? '-');
        }

        $allowedNumberModel = new AllowedNumberModel();
        $pembimbingName = '-';
        if (!empty($group['pembimbing_phone'])) {
            $pembimbing = $allowedNumberModel->where('phone', $group['pembimbing_phone'])->first();
            if ($pembimbing) {
                $pembimbingName = $pembimbing['name'];
            }
        }

        $data = [
            'title' => 'Rekap Kehadiran PKL - ' . htmlspecialchars($studentName),
            'studentName' => $studentName,
            'kelasJurusan' => $kelasJurusan,
            'group' => $group,
            'ketuaName' => $ketuaName,
            'guruPembimbing' => $pembimbingName,
            'attendanceMap' => $attendanceMap,
            'monthsList' => $monthsList,
            'formattedMonths' => $formattedMonths,
        ];

        return view('pkl/print_rekap_siswa', $data);
    }
}

