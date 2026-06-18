<?php

namespace App\Controllers;

use App\Models\AttendanceModel;
use App\Models\ViolationModel;
use App\Models\AttendancePklModel;

class Analytics extends BaseController
{
    public function index()
    {
        $session = session();
        if (!in_array($session->get('role'), ['admin', 'kepsek', 'guru', 'guru_bk'])) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        $attendanceModel = new AttendanceModel();
        $violationModel = new ViolationModel();
        $attendancePklModel = new AttendancePklModel();

        $tpId = $this->getActiveTPId();
        
        $monthLabels = [];
        $hadirKbmMonth = [];
        $hadirPklMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthString = date('Y-m', strtotime("-$i months"));
            $monthLabels[] = date('M Y', strtotime("-$i months"));
            
            $hadirKbmMonth[] = $attendanceModel->like('date', $monthString, 'after')
                                               ->where('status', 'hadir')
                                               ->notLike('note', 'PKL:')
                                               ->where('tahun_pelajaran_id', $tpId)
                                               ->countAllResults();

            $hadirPklMonth[] = $attendanceModel->like('date', $monthString, 'after')
                                               ->where('status', 'hadir')
                                               ->like('note', 'PKL:')
                                               ->where('tahun_pelajaran_id', $tpId)
                                               ->countAllResults();
        }

        // KBM Ketidakhadiran
        $kbmSakit = $attendanceModel->where('status', 'sakit')->notLike('note', 'PKL:')->where('tahun_pelajaran_id', $tpId)->countAllResults();
        $kbmIzin  = $attendanceModel->where('status', 'izin')->notLike('note', 'PKL:')->where('tahun_pelajaran_id', $tpId)->countAllResults();
        $kbmAlpha = $attendanceModel->where('status', 'alpha')->notLike('note', 'PKL:')->where('tahun_pelajaran_id', $tpId)->countAllResults();

        // PKL Ketidakhadiran
        $pklSakit = $attendanceModel->where('status', 'sakit')->like('note', 'PKL:')->where('tahun_pelajaran_id', $tpId)->countAllResults();
        $pklIzin  = $attendanceModel->where('status', 'izin')->like('note', 'PKL:')->where('tahun_pelajaran_id', $tpId)->countAllResults();
        $pklAlpha = $attendanceModel->where('status', 'alpha')->like('note', 'PKL:')->where('tahun_pelajaran_id', $tpId)->countAllResults();

        // Tren harian laporan PKL (14 Hari Terakhir)
        $dailyPklLabels = [];
        $dailyPklData = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $dailyPklLabels[] = date('d M', strtotime($d));
            $dailyPklData[] = $attendancePklModel->where('date', $d)->where('tahun_pelajaran_id', $tpId)->countAllResults();
        }

        $data = [
            'title' => 'Dashboard Eksekutif & Analytics',
            'monthLabels' => json_encode($monthLabels),
            'hadirKbmMonth' => json_encode($hadirKbmMonth),
            'hadirPklMonth' => json_encode($hadirPklMonth),
            'kbmAbsenData' => json_encode([$kbmSakit, $kbmIzin, $kbmAlpha]),
            'pklAbsenData' => json_encode([$pklSakit, $pklIzin, $pklAlpha]),
            'dailyPklLabels' => json_encode($dailyPklLabels),
            'dailyPklData' => json_encode($dailyPklData),
        ];

        return view('analytics/index', $data);
    }
}
