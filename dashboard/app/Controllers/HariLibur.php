<?php

namespace App\Controllers;

use App\Models\HariLiburModel;

class HariLibur extends BaseController
{
    public function index()
    {
        $session = session();
        $userRole = $session->get('role');

        if ($userRole !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak punya akses ke menu ini.');
        }

        $model = new HariLiburModel();
        $holidays = $model->getAllOrdered();

        $data = [
            'title' => 'Manajemen Hari Libur',
            'holidays' => $holidays,
        ];

        return view('hari_libur/index', $data);
    }

    public function add()
    {
        $session = session();
        if ($session->get('role') !== 'admin') {
            return $this->response->setJSON(['ok' => false, 'error' => 'Unauthorized']);
        }

        $rules = [
            'date'   => 'required|valid_date',
            'reason' => 'required|min_length[3]|max_length[200]',
            'type'   => 'required|in_list[nasional,sekolah,cuti bersama]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'ok' => false,
                'error' => implode(' | ', $this->validator->getErrors())
            ]);
        }

        $model = new HariLiburModel();
        try {
            $model->insert([
                'date'   => $this->request->getPost('date'),
                'reason' => $this->request->getPost('reason'),
                'type'   => $this->request->getPost('type') ?: 'sekolah',
            ]);
            return $this->response->setJSON(['ok' => true]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'ok' => false,
                'error' => 'Tanggal sudah ada atau terjadi kesalahan.'
            ]);
        }
    }

    public function delete($id)
    {
        $session = session();
        if ($session->get('role') !== 'admin') {
            return $this->response->setJSON(['ok' => false, 'error' => 'Unauthorized']);
        }

        $model = new HariLiburModel();
        $deleted = $model->delete($id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['ok' => (bool) $deleted]);
        }
        return redirect()->to('/hari-libur')->with('message', 'Hari libur berhasil dihapus.');
    }

    /**
     * API: JSON status hari ini — digunakan oleh JS dashboard
     */
    public function todayStatus()
    {
        $model = new HariLiburModel();
        $today = date('Y-m-d');
        $holiday = $model->getByDate($today);
        return $this->response->setJSON([
            'is_holiday' => ($holiday !== null),
            'reason'     => $holiday['reason'] ?? '',
            'type'       => $holiday['type'] ?? '',
            'date'       => $today,
        ]);
    }
}
