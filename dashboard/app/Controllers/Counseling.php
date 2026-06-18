<?php

namespace App\Controllers;

use App\Models\CounselingModel;
use App\Models\StudentModel;

class Counseling extends BaseController
{
    public function index()
    {
        $role = session()->get('role');
        $isStudent = in_array($role, ['siswa', 'ketua_pkl', 'anggotapkl']);

        if (!in_array($role, ['admin', 'guru_bk']) && !$isStudent) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }
        $counselingModel = new CounselingModel();
        $studentModel = new StudentModel();
        $tpId = $this->getActiveTPId();

        if ($isStudent) {
            $studentId = session()->get('student_id');
            $counseling = $counselingModel->where('student_id', $studentId)
                                          ->where('tahun_pelajaran_id', $tpId)
                                          ->orderBy('date', 'DESC')
                                          ->findAll();
            $students = [];
        } else {
            $counseling = $counselingModel->where('tahun_pelajaran_id', $tpId)
                                          ->orderBy('date', 'DESC')
                                          ->findAll();
            $students = $studentModel->orderBy('name', 'ASC')->findAll();
        }

        $parsedCounseling = [];
        foreach ($counseling as $c) {
            $student = $studentModel->find($c['student_id']);
            $parsedCounseling[] = [
                'id' => $c['id'],
                'student_id' => $c['student_id'],
                'student_name' => $student ? $student['name'] : 'Tidak dikenal',
                'student_class' => $student ? $student['class'] : 'Tidak dikenal',
                'date' => $c['date'],
                'type' => $c['type'],
                'content' => $c['content'],
                'counselor' => $c['counselor']
            ];
        }

        $data = [
            'title' => 'Catatan Bimbingan Konseling (BK)',
            'counseling' => $parsedCounseling,
            'students' => $students,
            'isStudent' => $isStudent
        ];

        return view('counseling/index', $data);
    }

    public function create()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'guru_bk'])) {
            return redirect()->to('/counseling')->with('error', 'Akses ditolak.');
        }
        $counselingModel = new CounselingModel();

        $counselingData = [
            'student_id' => $this->request->getPost('student_id'),
            'date' => $this->request->getPost('date') ?: date('Y-m-d'),
            'type' => $this->request->getPost('type') ?: 'catatan',
            'content' => $this->request->getPost('content'),
            'counselor' => session()->get('name') ?: 'Konselor Sekolah',
            'tahun_pelajaran_id' => $this->getActiveTPId()
        ];

        if (!$counselingModel->validate($counselingData)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $counselingModel->errors()));
        }

        $counselingModel->insert($counselingData);

        return redirect()->to('/counseling')->with('success', 'Catatan BK berhasil ditambahkan.');
    }

    public function delete($id)
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'guru_bk'])) {
            return redirect()->to('/counseling')->with('error', 'Akses ditolak.');
        }
        $counselingModel = new CounselingModel();
        $counselingModel->delete($id);

        return redirect()->to('/counseling')->with('success', 'Catatan BK berhasil dihapus.');
    }
}
