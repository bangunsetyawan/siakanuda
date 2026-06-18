<?php

namespace App\Controllers;

use App\Models\StudentModel;
use App\Models\AttendanceModel;
use App\Models\ViolationModel;

class Student extends BaseController
{
    public function index()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'kepsek', 'guru_bk', 'guru', 'guru_mapel'])) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }
        $studentModel = new StudentModel();
        $studentModel->syncPklRoles($this->getActiveTPId());
        
        $showInactive = $this->request->getGet('show_inactive') == '1';
        if (!$showInactive) {
            $studentModel->groupStart()
                         ->where('is_active', 1)
                         ->orWhere('is_active IS NULL')
                         ->groupEnd();
        }
        
        $search = $this->request->getGet('search');
        if (!empty($search)) {
            $studentModel->groupStart()
                         ->like('name', $search)
                         ->orLike('class', $search)
                         ->orLike('nis', $search)
                         ->groupEnd();
        }
        
        $students = $studentModel->orderBy('class', 'ASC')->orderBy('name', 'ASC')->findAll();

        $data = [
            'title' => 'Manajemen Siswa',
            'students' => $students,
            'search' => $search,
            'showInactive' => $showInactive
        ];

        return view('students/index', $data);
    }

    public function create()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'kepsek', 'guru_bk'])) {
            return redirect()->to('/students')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk menambah siswa.');
        }
        $studentModel = new StudentModel();

        $nis = $this->request->getPost('nis');
        $studentData = [
            'name' => $this->request->getPost('name'),
            'class' => $this->request->getPost('class'),
            'nis' => $nis,
            'gender' => $this->request->getPost('gender'),
            'phone' => $this->request->getPost('phone'),
            'role' => $this->request->getPost('role') ?: (str_starts_with(trim($this->request->getPost('class') ?: ''), 'XII') ? 'anggotapkl' : 'siswa'),
            'password_hash' => password_hash($nis, PASSWORD_BCRYPT),
            'first_login' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        if (!$studentModel->validate($studentData)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $studentModel->errors()));
        }

        $studentModel->insert($studentData);

        return redirect()->to('/students')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function update($id)
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'kepsek', 'guru_bk'])) {
            return redirect()->to('/students')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengedit siswa.');
        }
        $studentModel = new StudentModel();

        $studentData = [
            'name' => $this->request->getPost('name'),
            'class' => $this->request->getPost('class'),
            'nis' => $this->request->getPost('nis'),
            'gender' => $this->request->getPost('gender'),
            'phone' => $this->request->getPost('phone'),
            'role' => $this->request->getPost('role') ?: (str_starts_with(trim($this->request->getPost('class') ?: ''), 'XII') ? 'anggotapkl' : 'siswa')
        ];

        if (!$studentModel->validate($studentData)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $studentModel->errors()));
        }

        $studentModel->update($id, $studentData);

        return redirect()->to('/students')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function delete($id)
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'guru_bk'])) {
            return redirect()->to('/students')->with('error', 'Akses ditolak. Hanya Admin dan Guru BK yang dapat menghapus siswa.');
        }
        $studentModel = new StudentModel();
        $studentModel->delete($id);

        return redirect()->to('/students')->with('success', 'Data siswa berhasil dihapus.');
    }

    public function details($id)
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'kepsek', 'guru_bk', 'guru', 'guru_mapel'])) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }
        $studentModel = new StudentModel();
        $attendanceModel = new AttendanceModel();
        $violationModel = new ViolationModel();

        $student = $studentModel->find($id);

        if (!$student) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Siswa tidak ditemukan.');
        }

        $attendance = $attendanceModel->where('student_id', $id)->orderBy('date', 'DESC')->findAll();
        $violations = $violationModel->where('student_id', $id)->orderBy('date', 'DESC')->findAll();
        
        $totalPoints = 0;
        foreach ($violations as $v) {
            $totalPoints += $v['points'];
        }

        $data = [
            'title' => 'Detail Siswa: ' . $student['name'],
            'student' => $student,
            'attendance' => $attendance,
            'violations' => $violations,
            'totalPoints' => $totalPoints
        ];

        return view('students/details', $data);
    }

    public function resetPassword($id)
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'kepsek', 'guru_bk'])) {
            return redirect()->to('/students')->with('error', 'Akses ditolak.');
        }

        $studentModel = new StudentModel();
        $student = $studentModel->find($id);

        if (!$student) {
            return redirect()->to('/students')->with('error', 'Siswa tidak ditemukan.');
        }

        $studentModel->update($id, [
            'password_hash' => password_hash($student['nis'], PASSWORD_BCRYPT),
            'first_login' => 0
        ]);

        return redirect()->to('/students')->with('success', 'Password siswa ' . $student['name'] . ' berhasil direset ke default (NISN).');
    }

    public function import()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'kepsek', 'guru_bk'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }

        $json = $this->request->getJSON(true);
        if (!$json || !isset($json['students']) || !is_array($json['students'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Format data tidak valid.']);
        }

        $deactivateMissing = isset($json['deactivate_missing']) && $json['deactivate_missing'] === true;

        $studentModel = new StudentModel();
        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];
        $processedNisns = [];

        foreach ($json['students'] as $index => $row) {
            $nis = isset($row['nis']) ? trim($row['nis']) : '';
            $name = isset($row['name']) ? trim($row['name']) : '';
            $class = isset($row['class']) ? trim($row['class']) : '';
            $gender = isset($row['gender']) ? strtoupper(trim($row['gender'])) : 'L';
            $phone = isset($row['phone']) ? trim($row['phone']) : null;
            $studentRole = isset($row['role']) && trim($row['role']) !== '' ? trim($row['role']) : (str_starts_with(trim($class), 'XII') ? 'anggotapkl' : 'siswa');

            if (empty($nis) || empty($name) || empty($class)) {
                $skippedCount++;
                $errors[] = "Baris " . ($index + 1) . ": NISN, Nama, dan Kelas wajib diisi.";
                continue;
            }

            // Clean phone format
            if (!empty($phone)) {
                $phone = preg_replace('/[^\d]/', '', $phone);
                if (str_starts_with($phone, '08')) {
                    $phone = '628' . substr($phone, 2);
                }
            }

            $processedNisns[] = $nis;

            // Check if already exists by NISN
            $existing = $studentModel->where('nis', $nis)->first();

            if ($existing) {
                // Update - do NOT overwrite password_hash or first_login
                $updateData = [
                    'nis' => $nis,
                    'name' => $name,
                    'class' => $class,
                    'gender' => ($gender === 'P' || $gender === 'PEREMPUAN' || str_starts_with($gender, 'P')) ? 'P' : 'L',
                    'phone' => $phone,
                    'role' => $studentRole,
                    'is_active' => 1 // ensure they are active when re-imported
                ];
                $studentModel->update($existing['id'], $updateData);
            } else {
                // Insert
                $insertData = [
                    'nis' => $nis,
                    'name' => $name,
                    'class' => $class,
                    'gender' => ($gender === 'P' || $gender === 'PEREMPUAN' || str_starts_with($gender, 'P')) ? 'P' : 'L',
                    'phone' => $phone,
                    'role' => $studentRole,
                    'password_hash' => password_hash($nis, PASSWORD_BCRYPT),
                    'first_login' => 0,
                    'is_active' => 1
                ];
                $studentModel->insert($insertData);
            }
            $importedCount++;
        }

        $deactivatedCount = 0;
        if ($deactivateMissing && !empty($processedNisns)) {
            // Find all active students whose NISN is not in the processed list
            $studentsToDeactivate = $studentModel->groupStart()
                                                 ->where('is_active', 1)
                                                 ->orWhere('is_active IS NULL')
                                                 ->groupEnd()
                                                 ->whereNotIn('nis', $processedNisns)
                                                 ->findAll();
                                                 
            foreach ($studentsToDeactivate as $std) {
                $studentModel->update($std['id'], ['is_active' => 0]);
                $deactivatedCount++;
            }
        }

        $msg = "Berhasil memproses $importedCount data siswa. (Skipped: $skippedCount)";
        if ($deactivateMissing) {
            $msg .= ", dinonaktifkan: $deactivatedCount siswa.";
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => $msg,
            'errors' => $errors
        ]);
    }

    public function exportExcel()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'kepsek', 'guru_bk'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }
        $studentModel = new StudentModel();
        $students = $studentModel->groupStart()
                                 ->where('is_active', 1)
                                 ->orWhere('is_active IS NULL')
                                 ->groupEnd()
                                 ->orderBy('class', 'ASC')
                                 ->orderBy('name', 'ASC')
                                 ->findAll();
        
        $data = [];
        foreach ($students as $s) {
            $data[] = [
                'nis' => $s['nis'],
                'name' => $s['name'],
                'class' => $s['class'],
                'gender' => $s['gender'],
                'phone' => $s['phone'],
                'role' => $s['role']
            ];
        }
        return $this->response->setJSON($data);
    }
}
