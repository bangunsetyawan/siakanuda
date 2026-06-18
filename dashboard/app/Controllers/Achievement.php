<?php

namespace App\Controllers;

use App\Models\AchievementModel;
use App\Models\StudentModel;

class Achievement extends BaseController
{
    public function index()
    {
        $role = session()->get('role');
        $userType = session()->get('user_type');
        
        $isStudent = in_array($role, ['siswa', 'ketua_pkl', 'anggotapkl']);
        
        $achievementModel = new AchievementModel();
        $studentModel = new StudentModel();
        $tpId = $this->getActiveTPId();

        $achievements = $achievementModel->where('tahun_pelajaran_id', $tpId)->orderBy('date', 'DESC')->findAll();
        if ($isStudent) {
            $students = [];
        } else {
            $students = $studentModel->orderBy('name', 'ASC')->findAll();
        }

        $parsedAchievements = [];
        foreach ($achievements as $a) {
            $student = $studentModel->find($a['student_id']);
            $parsedAchievements[] = [
                'id' => $a['id'],
                'student_id' => $a['student_id'],
                'student_name' => $student ? $student['name'] : 'Tidak dikenal',
                'student_class' => $student ? $student['class'] : 'Tidak dikenal',
                'date' => $a['date'],
                'category' => $a['category'],
                'title' => $a['title'],
                'description' => $a['description']
            ];
        }

        $data = [
            'title' => 'Catatan Prestasi Siswa',
            'achievements' => $parsedAchievements,
            'students' => $students,
            'isStudent' => $isStudent
        ];

        return view('achievements/index', $data);
    }

    public function create()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'guru_bk', 'guru', 'guru_mapel'])) {
            return redirect()->to('/prestasi')->with('error', 'Akses ditolak.');
        }

        $achievementModel = new AchievementModel();

        $achievementData = [
            'student_id' => $this->request->getPost('student_id'),
            'date' => $this->request->getPost('date') ?: date('Y-m-d'),
            'category' => $this->request->getPost('category') ?: 'Akademik',
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'tahun_pelajaran_id' => $this->getActiveTPId()
        ];

        if (!$achievementModel->validate($achievementData)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $achievementModel->errors()));
        }

        $achievementModel->insert($achievementData);

        return redirect()->to('/prestasi')->with('success', 'Catatan prestasi berhasil ditambahkan.');
    }

    public function delete($id)
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'guru_bk', 'guru', 'guru_mapel'])) {
            return redirect()->to('/prestasi')->with('error', 'Akses ditolak.');
        }

        $achievementModel = new AchievementModel();
        $achievementModel->delete($id);

        return redirect()->to('/prestasi')->with('success', 'Catatan prestasi berhasil dihapus.');
    }
}
