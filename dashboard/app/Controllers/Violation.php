<?php

namespace App\Controllers;

use App\Models\ViolationModel;
use App\Models\StudentModel;

class Violation extends BaseController
{
    public function index()
    {
        $violationModel = new ViolationModel();
        $studentModel = new StudentModel();

        $session = session();
        $userType = $session->get('user_type');
        $isStudent = ($userType === 'student');
        $tpId = $this->getActiveTPId();

        // Siswa hanya lihat pelanggaran miliknya sendiri
        if ($isStudent) {
            $studentId = $session->get('student_id');
            $violations = $violationModel->where('student_id', $studentId)
                                         ->where('tahun_pelajaran_id', $tpId)
                                         ->orderBy('date', 'DESC')
                                         ->findAll();
        } else {
            $violations = $violationModel->where('tahun_pelajaran_id', $tpId)
                                         ->orderBy('date', 'DESC')
                                         ->findAll();
        }

        $students = $studentModel->orderBy('name', 'ASC')->findAll();

        $parsedViolations = [];
        foreach ($violations as $v) {
            $student = $studentModel->find($v['student_id']);
            $parsedViolations[] = [
                'id' => $v['id'],
                'student_id' => $v['student_id'],
                'student_name' => $student ? $student['name'] : 'Tidak dikenal',
                'student_class' => $student ? $student['class'] : 'Tidak dikenal',
                'date' => $v['date'],
                'category' => $v['category'],
                'description' => $v['description'],
                'points' => $v['points'],
                'proof_url' => $v['proof_url'],
                'follow_up' => $v['follow_up']
            ];
        }

        // Hitung total poin untuk siswa yang login
        $totalPoin = 0;
        if ($isStudent) {
            foreach ($parsedViolations as $pv) {
                $totalPoin += (int)$pv['points'];
            }
        }

        $data = [
            'title' => 'Catatan Pelanggaran & Poin',
            'violations' => $parsedViolations,
            'students' => $students,
            'isStudent' => $isStudent,
            'totalPoin' => $totalPoin,
        ];

        return view('violations/index', $data);
    }

    public function create()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'guru_bk'])) {
            return redirect()->to('/violations')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mencatat pelanggaran.');
        }
        $violationModel = new ViolationModel();

        $violationData = [
            'student_id' => $this->request->getPost('student_id'),
            'date' => $this->request->getPost('date') ?: date('Y-m-d'),
            'category' => $this->request->getPost('category'),
            'description' => $this->request->getPost('description'),
            'points' => $this->request->getPost('points') ?: 5,
            'proof_url' => $this->request->getPost('proof_url') ?: '',
            'follow_up' => $this->request->getPost('follow_up') ?: '',
            'tahun_pelajaran_id' => $this->getActiveTPId()
        ];

        if (!$violationModel->validate($violationData)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $violationModel->errors()));
        }

        $violationModel->insert($violationData);

        return redirect()->to('/violations')->with('success', 'Catatan pelanggaran berhasil disimpan.');
    }

    public function delete($id)
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'guru_bk'])) {
            return redirect()->to('/violations')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk menghapus pelanggaran.');
        }
        $violationModel = new ViolationModel();
        $violationModel->delete($id);

        return redirect()->to('/violations')->with('success', 'Catatan pelanggaran berhasil dihapus.');
    }
}
