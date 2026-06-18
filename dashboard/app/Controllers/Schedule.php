<?php

namespace App\Controllers;

use App\Models\ScheduleModel;
use App\Models\AllowedNumberModel;

class Schedule extends BaseController
{
    public function index()
    {
        $scheduleModel = new ScheduleModel();
        $allowedNumberModel = new AllowedNumberModel();

        $search = $this->request->getGet('search');
        if (!empty($search)) {
            $scheduleModel->groupStart()
                          ->like('class_name', $search)
                          ->orLike('subject', $search)
                          ->orLike('day', $search)
                          ->groupEnd();
        }

        $schedules = $scheduleModel->orderBy('day', 'ASC')->orderBy('class_name', 'ASC')->orderBy('start_time', 'ASC')->findAll();
        
        // Fetch teachers list for dropdown (active and teacher roles)
        $teachers = $allowedNumberModel->where('active', 1)
                                       ->whereIn('role', ['admin', 'kepsek', 'guru', 'guru_mapel', 'guru_bk'])
                                       ->orderBy('name', 'ASC')
                                       ->findAll();

        $data = [
            'title' => 'Jadwal Pelajaran',
            'schedules' => $schedules,
            'teachers' => $teachers,
            'search' => $search
        ];

        return view('schedules/index', $data);
    }

    public function create()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'kepsek'])) {
            return redirect()->to('/schedules')->with('error', 'Akses ditolak.');
        }
        $scheduleModel = new ScheduleModel();

        $scheduleData = [
            'day' => $this->request->getPost('day'),
            'class_name' => $this->request->getPost('class_name'),
            'subject' => $this->request->getPost('subject'),
            'teacher_phone' => $this->request->getPost('teacher_phone'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time')
        ];

        if (!$scheduleModel->validate($scheduleData)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $scheduleModel->errors()));
        }

        $scheduleModel->insert($scheduleData);

        return redirect()->to('/schedules')->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    public function update($id)
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'kepsek'])) {
            return redirect()->to('/schedules')->with('error', 'Akses ditolak.');
        }
        $scheduleModel = new ScheduleModel();

        $scheduleData = [
            'day' => $this->request->getPost('day'),
            'class_name' => $this->request->getPost('class_name'),
            'subject' => $this->request->getPost('subject'),
            'teacher_phone' => $this->request->getPost('teacher_phone'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time')
        ];

        if (!$scheduleModel->validate($scheduleData)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $scheduleModel->errors()));
        }

        $scheduleModel->update($id, $scheduleData);

        return redirect()->to('/schedules')->with('success', 'Jadwal pelajaran berhasil diperbarui.');
    }

    public function delete($id)
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'kepsek'])) {
            return redirect()->to('/schedules')->with('error', 'Akses ditolak.');
        }
        $scheduleModel = new ScheduleModel();
        $scheduleModel->delete($id);

        return redirect()->to('/schedules')->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }
}
