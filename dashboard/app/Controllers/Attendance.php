<?php

namespace App\Controllers;

use App\Models\StudentModel;
use App\Models\AttendanceModel;
use App\Models\AttendanceKelasModel;
use App\Models\WaliKelasModel;
use App\Models\AllowedNumberModel;

class Attendance extends BaseController
{
    public function index()
    {
        $session = session();

        // Siswa → tampilkan rekap absensi pribadi
        if ($session->get('user_type') === 'student') {
            return $this->personalRecap();
        }

        $attendanceKelasModel = new AttendanceKelasModel();
        $studentModel = new StudentModel();
        $waliKelasModel = new WaliKelasModel();
        $allowedNumberModel = new AllowedNumberModel();
        
        $date = $this->request->getGet('date') ?: date('Y-m-d');
        $tpId = $this->getActiveTPId();

        $attendance = $attendanceKelasModel->where('date', $date)
                                           ->where('tahun_pelajaran_id', $tpId)
                                           ->orderBy('class_name', 'ASC')
                                           ->findAll();

        // Get student counts per class from DB (active only)
        $allStudents = $studentModel->select('class, COUNT(*) as total')
                                    ->groupStart()
                                        ->where('is_active', 1)
                                        ->orWhere('is_active IS NULL')
                                    ->groupEnd()
                                    ->groupBy('class')
                                    ->orderBy('class', 'ASC')
                                    ->findAll();
        $classCounts = [];
        $activeClasses = [];
        foreach ($allStudents as $row) {
            $classCounts[$row['class']] = $row['total'];
            $activeClasses[] = $row['class'];
        }

        // Also include wali_kelas classes that may not have students yet
        $waliKelasAll = $waliKelasModel->orderBy('class_name', 'ASC')->findAll();
        $waliKelasMap = [];
        foreach ($waliKelasAll as $wk) {
            $waliKelasMap[$wk['class_name']] = $wk['teacher_phone'];
            if (!in_array($wk['class_name'], $activeClasses)) {
                $activeClasses[] = $wk['class_name'];
            }
        }
        sort($activeClasses);

        // Teachers for wali kelas dropdown
        $teachers = $allowedNumberModel->whereIn('role', ['admin', 'kepsek', 'guru_bk', 'guru', 'guru_mapel'])
                                       ->where('active', 1)
                                       ->orderBy('name', 'ASC')
                                       ->findAll();

        // Tracker 30 hari terakhir
        $recapDays = [];
        for ($i = 0; $i < 30; $i++) {
            $d = date('Y-m-d', strtotime("-$i days"));
            // Skip hari minggu (0)
            if (date('w', strtotime($d)) == 0) continue;
            
            $attDate = $attendanceKelasModel->where('date', $d)->where('tahun_pelajaran_id', $tpId)->findAll();
            $reportedClasses = array_column($attDate, 'class_name');
            $missingClasses = array_diff($activeClasses, $reportedClasses);
            
            $recapDays[] = [
                'date' => $d,
                'total_classes' => count($activeClasses),
                'reported_count' => count($reportedClasses),
                'missing_classes' => array_values($missingClasses)
            ];
        }

        $data = [
            'title' => 'Rekap Absensi Harian KBM',
            'attendance' => $attendance,
            'date' => $date,
            'activeClasses' => $activeClasses,
            'classCounts' => $classCounts,
            'waliKelasMap' => $waliKelasMap,
            'teachers' => $teachers,
            'role' => $session->get('role'),
            'recapDays' => $recapDays
        ];

        return view('attendance/index', $data);
    }

    /**
     * Rekap absensi personal untuk siswa yang sedang login.
     */
    private function personalRecap()
    {
        $session = session();
        $studentId = $session->get('student_id');
        $studentModel = new StudentModel();
        $attendanceModel = new AttendanceModel();

        $student = $studentModel->find($studentId);

        // Ambil semua data absensi siswa ini, urutkan dari terbaru
        $records = $attendanceModel->where('student_id', $studentId)
                                   ->where('tahun_pelajaran_id', $this->getActiveTPId())
                                   ->orderBy('date', 'DESC')
                                   ->findAll();

        // Hitung summary
        $summary = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0];
        foreach ($records as $r) {
            $key = strtolower($r['status']);
            if (isset($summary[$key])) {
                $summary[$key]++;
            }
        }

        $data = [
            'title'   => 'Rekap Absensi Saya',
            'student' => $student,
            'records' => $records,
            'summary' => $summary,
        ];

        return view('attendance/personal', $data);
    }

    public function class($cls)
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'kepsek', 'guru_bk', 'guru', 'guru_mapel'])) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        $cls = urldecode($cls);
        $studentModel = new StudentModel();
        $attendanceModel = new AttendanceModel();

        $date = $this->request->getGet('date') ?: date('Y-m-d');
        $students = $studentModel->where('class', $cls)
                                 ->groupStart()
                                     ->where('is_active', 1)
                                     ->orWhere('is_active IS NULL')
                                 ->groupEnd()
                                 ->orderBy('name', 'ASC')
                                 ->findAll();

        $existingAttendance = $attendanceModel->where('date', $date)
                                              ->where('tahun_pelajaran_id', $this->getActiveTPId())
                                              ->findAll();
        $existingMap = [];
        foreach ($existingAttendance as $att) {
            $existingMap[$att['student_id']] = $att;
        }

        $data = [
            'title' => 'Absensi Kelas: ' . $cls,
            'class_name' => $cls,
            'students' => $students,
            'date' => $date,
            'existingMap' => $existingMap
        ];

        return view('attendance/class', $data);
    }

    public function saveClass()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'guru_bk', 'guru', 'guru_mapel'])) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }
        $className = $this->request->getPost('class_name');
        $date = $this->request->getPost('date');
        $attendanceData = $this->request->getPost('attendance'); // Array of student_id => status

        if (empty($className) || empty($date) || !is_array($attendanceData)) {
            return redirect()->back()->with('error', 'Data absensi tidak lengkap.');
        }

        $attendanceModel = new AttendanceModel();
        $attendanceKelasModel = new AttendanceKelasModel();
        $studentModel = new StudentModel();        $teacherPhone = session()->get('phone') ?: 'system';
        $tpId = $this->getActiveTPId();

        // Server-side validation: Sakit, Izin, Alpha must have notes
        foreach ($attendanceData as $studentId => $status) {
            $statusLower = strtolower($status);
            if ($statusLower === 'sakit' || $statusLower === 'izin' || $statusLower === 'alpha') {
                $note = trim($this->request->getPost('note_' . $studentId) ?: '');
                if (empty($note)) {
                    $student = $studentModel->find($studentId);
                    $studentName = $student ? $student['name'] : 'Siswa';
                    return redirect()->back()->withInput()->with('error', 'Siswa "' . $studentName . '" dengan status ' . ucfirst($status) . ' wajib diberi keterangan/catatan.');
                }
            }
        }

        $classAttendanceData = [];
        $absentDetails = [];

        foreach ($attendanceData as $studentId => $status) {
            $student = $studentModel->find($studentId);
            if ($student) {
                $classAttendanceData[$student['name']] = $status;
                $note = trim($this->request->getPost('note_' . $studentId) ?: '');

                if (strtolower($status) === 'sakit' || strtolower($status) === 'izin' || strtolower($status) === 'alpha') {
                    $absentDetails[] = [
                        'name' => $student['name'],
                        'status' => strtolower($status),
                        'note' => $note
                    ];
                }

                // Save or update in attendance table
                $existing = $attendanceModel->where('student_id', $studentId)
                                            ->where('date', $date)
                                            ->where('tahun_pelajaran_id', $tpId)
                                            ->first();

                if ($existing) {
                    $attendanceModel->update($existing['id'], [
                        'status' => $status,
                        'note'   => $note
                    ]);
                } else {
                    $attendanceModel->insert([
                        'student_id'         => $studentId,
                        'date'               => $date,
                        'status'             => $status,
                        'note'               => $note,
                        'tahun_pelajaran_id' => $tpId
                    ]);
                }
            }
        }

        // Save class rekap in attendance_kelas
        $existingKelas = $attendanceKelasModel->where('date', $date)
                                              ->where('class_name', $className)
                                              ->where('tahun_pelajaran_id', $tpId)
                                              ->first();

        $isUpdate = ($existingKelas) ? true : false;

        $saveData = [
            'date'               => $date,
            'class_name'         => $className,
            'teacher_phone'      => $teacherPhone,
            'attendance_data'    => json_encode($classAttendanceData),
            'tahun_pelajaran_id' => $tpId
        ];

        if ($existingKelas) {
            $attendanceKelasModel->update($existingKelas['id'], $saveData);
        } else {
            $attendanceKelasModel->insert($saveData);
        }

        // Calculate KBM attendance summary counts
        $hadirCount = 0;
        $sakitCount = 0;
        $izinCount = 0;
        $alphaCount = 0;
        foreach ($attendanceData as $studentId => $status) {
            $statusLower = strtolower($status);
            if ($statusLower === 'hadir') $hadirCount++;
            elseif ($statusLower === 'sakit') $sakitCount++;
            elseif ($statusLower === 'izin') $izinCount++;
            elseif ($statusLower === 'alpha') $alphaCount++;
        }

        // Get Wali Kelas phone if any
        $waliKelasModel = new \App\Models\WaliKelasModel();
        $waliKelas = $waliKelasModel->where('class_name', $className)->first();
        $waliPhone = ($waliKelas && !empty($waliKelas['teacher_phone'])) ? $waliKelas['teacher_phone'] : null;

        // Call Node.js API to broadcast WA notification for KBM class attendance
        $waStatus = 'Laporan absensi gagal dikirim ke WhatsApp.';
        $isError = false;
        try {
            $client = \Config\Services::curlrequest();
            $waBotUrl = env('WA_BOT_URL') ?: 'http://127.0.0.1:7860';
            
            $response = $client->post("{$waBotUrl}/api/attendance/broadcast", [
                'json' => [
                    'class_name' => $className,
                    'date'       => $date,
                    'teacher'    => session()->get('name') ?: 'Guru',
                    'wali_phone' => $waliPhone,
                    'is_update'  => $isUpdate,
                    'summary'    => [
                        'hadir' => $hadirCount,
                        'sakit' => $sakitCount,
                        'izin'  => $izinCount,
                        'alpha' => $alphaCount
                    ],
                    'details'    => $absentDetails
                ],
                'timeout' => 12
            ]);

            if ($response->getStatusCode() === 200) {
                $body = json_decode($response->getBody(), true);
                if (isset($body['sent_to_group']) && $body['sent_to_group'] === true) {
                    $waStatus = 'Laporan absensi berhasil dikirim ke grup WhatsApp.';
                } else {
                    $waStatus = 'Laporan absensi berhasil dikirim ke WhatsApp Admin (karena grup WA belum diatur atau gagal dijangkau).';
                }
            } else {
                $isError = true;
                $body = json_decode($response->getBody(), true);
                if (isset($body['error'])) {
                    $waStatus = 'Laporan absensi gagal dikirim ke WhatsApp: ' . $body['error'];
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to send KBM WA broadcast: ' . $e->getMessage());
            $waStatus = 'Laporan absensi gagal dikirim ke WhatsApp: ' . $e->getMessage();
            $isError = true;
        }

        session()->setFlashdata('success', 'Absensi kelas ' . $className . ' berhasil disimpan.');
        if ($isError) {
            session()->setFlashdata('error', $waStatus);
        } else {
            session()->setFlashdata('info', $waStatus);
        }

        return redirect()->to('/attendance?date=' . $date);
    }

    public function setWaliKelas()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/attendance')->with('error', 'Akses ditolak.');
        }

        $className = $this->request->getPost('class_name');
        $teacherPhone = $this->request->getPost('teacher_phone') ?: null;

        $waliKelasModel = new WaliKelasModel();
        $existing = $waliKelasModel->where('class_name', $className)->first();

        if ($existing) {
            $waliKelasModel->update($existing['id'], ['teacher_phone' => $teacherPhone]);
        } else {
            $waliKelasModel->insert(['class_name' => $className, 'teacher_phone' => $teacherPhone]);
        }

        return redirect()->to('/attendance')->with('success', 'Wali kelas ' . $className . ' berhasil diperbarui.');
    }

    public function deleteAttendanceHistory($id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/attendance')->with('error', 'Akses ditolak. Hanya Admin.');
        }

        $attendanceKelasModel = new AttendanceKelasModel();
        $attendanceModel = new AttendanceModel();
        $studentModel = new StudentModel();

        $record = $attendanceKelasModel->find($id);
        if (!$record) {
            return redirect()->to('/attendance')->with('error', 'Data tidak ditemukan.');
        }

        // Delete individual student attendance for that class + date
        $students = $studentModel->where('class', $record['class_name'])->findAll();
        foreach ($students as $s) {
            $attendanceModel->where('student_id', $s['id'])
                            ->where('date', $record['date'])
                            ->where('tahun_pelajaran_id', $record['tahun_pelajaran_id'])
                            ->delete();
        }

        // Delete class record
        $attendanceKelasModel->delete($id);

        return redirect()->to('/attendance?date=' . $record['date'])->with('success', 'Riwayat absensi kelas ' . $record['class_name'] . ' berhasil dihapus.');
    }

    public function printHtml($className)
    {
        $session = session();
        $role = $session->get('role');
        if (!in_array($role, ['admin', 'kepsek', 'guru_bk', 'guru', 'guru_mapel'])) {
            return redirect()->to('/attendance')->with('error', 'Akses ditolak.');
        }

        $className = urldecode($className);
        $month = $this->request->getGet('month');
        $year = $this->request->getGet('year');

        $targetMonth = ($month !== null) ? (int)$month : (int)date('m');
        $targetYear = ($year !== null) ? (int)$year : (int)date('Y');

        $studentModel = new \App\Models\StudentModel();
        $attendanceModel = new \App\Models\AttendanceModel();

        $students = $studentModel->where('class', $className)->findAll();
        if (empty($students)) {
            return redirect()->to('/attendance')->with('error', "Kelas $className tidak memiliki data siswa.");
        }

        $studentIds = array_column($students, 'id');
        
        $attendances = [];
        if (!empty($studentIds)) {
            $monthStr = sprintf('%04d-%02d-', $targetYear, $targetMonth);
            $attendances = $attendanceModel
                ->whereIn('student_id', $studentIds)
                ->like('date', $monthStr, 'after')
                ->findAll();
        }

        $attendanceMap = [];
        foreach ($attendances as $att) {
            $day = (int)date('d', strtotime($att['date']));
            $attendanceMap[$att['student_id']][$day] = $att['status'];
        }

        $waliKelasModel = new \App\Models\WaliKelasModel();
        $allowedNumberModel = new \App\Models\AllowedNumberModel();
        $waliKelas = $waliKelasModel->where('class_name', $className)->first();
        $waliName = '-';
        if ($waliKelas && $waliKelas['teacher_phone']) {
            $teacher = $allowedNumberModel->where('phone', $waliKelas['teacher_phone'])->first();
            if ($teacher) {
                $waliName = $teacher['name'];
            }
        }

        $data = [
            'className' => $className,
            'targetMonth' => $targetMonth,
            'targetYear' => $targetYear,
            'students' => $students,
            'attendanceMap' => $attendanceMap,
            'waliName' => $waliName,
            'tpLabel' => $this->activeTP['nama'] ?? '2025/2026',
            'downloader' => $session->get('name') ?: 'Guru',
            'downloadTime' => date('Y-m-d H:i:s'),
            'numDays' => cal_days_in_month(CAL_GREGORIAN, $targetMonth, $targetYear),
            'title' => "Rekap Absensi $className"
        ];

        return view('attendance/print', $data);
    }
}
