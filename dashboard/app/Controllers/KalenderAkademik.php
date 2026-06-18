<?php

namespace App\Controllers;

use App\Models\KalenderAkademikModel;

class KalenderAkademik extends BaseController
{
    public function index()
    {
        $model = new KalenderAkademikModel();
        $role = session()->get('role');

        // Get distinct tahun_pelajaran values from the master table tahun_pelajaran
        $tpModel = new \App\Models\TahunPelajaranModel();
        $tahunList = $tpModel->select('nama as tahun_pelajaran')
            ->distinct()
            ->orderBy('nama', 'DESC')
            ->findAll();

        // Get the active Tahun Pelajaran as default
        $activeTP = $tpModel->getActive();
        $defaultTahun = $activeTP['nama'] ?? ($tahunList[0]['tahun_pelajaran'] ?? '2025/2026');

        // Current filter
        $selectedTahun = $this->request->getGet('tahun') ?? $defaultTahun;

        $ganjil = $model->where('tahun_pelajaran', $selectedTahun)
            ->where('semester', 'Ganjil')
            ->orderBy('nomor', 'ASC')
            ->findAll();

        $genap = $model->where('tahun_pelajaran', $selectedTahun)
            ->where('semester', 'Genap')
            ->orderBy('nomor', 'ASC')
            ->findAll();

        // Hari Libur data (admin only)
        $holidays = [];
        if ($role === 'admin') {
            $hlModel = new \App\Models\HariLiburModel();
            $holidays = $hlModel->getAllOrdered();
        }

        // Active tab
        $activeTab = $this->request->getGet('tab') ?? 'semester';

        return view('kalender_akademik/index', [
            'title' => 'Kalender Akademik',
            'role' => $role,
            'ganjil' => $ganjil,
            'genap' => $genap,
            'tahunList' => $tahunList,
            'selectedTahun' => $selectedTahun,
            'holidays' => $holidays,
            'activeTab' => $activeTab,
        ]);
    }

    public function save()
    {
        $role = session()->get('role');
        if ($role !== 'admin') {
            return redirect()->to('/kalender-akademik')->with('error', 'Hanya admin yang bisa mengedit.');
        }

        $model = new KalenderAkademikModel();
        $tahun = $this->request->getPost('tahun_pelajaran');
        $semester = $this->request->getPost('semester');
        $ids = $this->request->getPost('id') ?? [];
        $nomors = $this->request->getPost('nomor') ?? [];
        $bulans = $this->request->getPost('bulan') ?? [];
        $totalPekans = $this->request->getPost('total_pekan') ?? [];
        $pekanEfektifs = $this->request->getPost('pekan_efektif') ?? [];
        $pekanTidakEfektifs = $this->request->getPost('pekan_tidak_efektif') ?? [];
        $keterangans = $this->request->getPost('keterangan') ?? [];

        for ($i = 0; $i < count($bulans); $i++) {
            $data = [
                'tahun_pelajaran' => $tahun,
                'semester' => $semester,
                'nomor' => $nomors[$i] ?? ($i + 1),
                'bulan' => $bulans[$i],
                'total_pekan' => (int)($totalPekans[$i] ?? 0),
                'pekan_efektif' => (int)($pekanEfektifs[$i] ?? 0),
                'pekan_tidak_efektif' => (int)($pekanTidakEfektifs[$i] ?? 0),
                'keterangan' => $keterangans[$i] ?? '',
            ];

            if (!empty($ids[$i])) {
                $model->update($ids[$i], $data);
            } else {
                $model->insert($data);
            }
        }

        return redirect()->to('/kalender-akademik?tahun=' . urlencode($tahun))->with('success', 'Kalender Akademik semester ' . $semester . ' berhasil disimpan.');
    }

    public function deleteRow($id)
    {
        $role = session()->get('role');
        if ($role !== 'admin') {
            return redirect()->to('/kalender-akademik')->with('error', 'Hanya admin yang bisa menghapus.');
        }
        $model = new KalenderAkademikModel();
        $model->delete($id);
        return redirect()->to('/kalender-akademik')->with('success', 'Baris berhasil dihapus.');
    }

    /**
     * Add Hari Libur (AJAX)
     */
    public function addHoliday()
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

        $model = new \App\Models\HariLiburModel();
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

    /**
     * Delete Hari Libur (AJAX)
     */
    public function deleteHoliday($id)
    {
        $session = session();
        if ($session->get('role') !== 'admin') {
            return $this->response->setJSON(['ok' => false, 'error' => 'Unauthorized']);
        }

        $model = new \App\Models\HariLiburModel();
        $deleted = $model->delete($id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['ok' => (bool) $deleted]);
        }
        return redirect()->to('/kalender-akademik?tab=holidays');
    }

    /**
     * API: JSON status hari ini — untuk JS dashboard label
     */
    public function todayStatus()
    {
        $model = new \App\Models\HariLiburModel();
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
