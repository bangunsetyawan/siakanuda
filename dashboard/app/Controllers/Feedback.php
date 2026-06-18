<?php

namespace App\Controllers;

use App\Models\FeedbackModel;

class Feedback extends BaseController
{
    public function index()
    {
        $feedbackModel = new FeedbackModel();

        $session = session();
        $role = $session->get('role');
        $isAdmin = in_array($role, ['admin', 'kepsek']);
        $isSiswa = in_array($role, ['siswa', 'ketua_pkl']);
        $tpId = $this->getActiveTPId();

        // Admin/Staff can see everything, students only see public ones OR their own
        if ($isAdmin) {
            $feedbacks = $feedbackModel->where('tahun_pelajaran_id', $tpId)->orderBy('date', 'DESC')->findAll();
        } else {
            $studentName = $session->get('name');
            $feedbacks = $feedbackModel->where('tahun_pelajaran_id', $tpId)
                ->groupStart()
                    ->where('is_public', 1)
                    ->orWhere('sender_name', $studentName)
                ->groupEnd()
                ->orderBy('date', 'DESC')
                ->findAll();
        }

        $data = [
            'title' => 'Kotak Suara & Aspirasi Siswa',
            'feedbacks' => $feedbacks,
            'isAdmin' => $isAdmin,
            'isSiswa' => $isSiswa,
        ];

        return view('feedbacks/index', $data);
    }

    public function create()
    {
        $session = session();
        $message = trim($this->request->getPost('message') ?? '');

        if (empty($message)) {
            return redirect()->to('/feedbacks')->with('error', 'Pesan aspirasi tidak boleh kosong.');
        }

        // Cek kata-kata kasar / tidak pantas (Indonesian & English common bad words)
        $badWords = [
            'anjing', 'babi', 'monyet', 'bangsat', 'keparat', 'bajingan', 
            'kontol', 'memek', 'jembut', 'ngentot', 'asu', 'goblog', 'goblok', 
            'bego', 'tolol', 'jancok', 'jancuk', 'fuck', 'shit', 'perek', 'lonte',
            'peli', 'tempik', 'titit', 'gatel', 'lonte', 'sinting', 'gila'
        ];
        
        $messageLower = strtolower($message);
        foreach ($badWords as $word) {
            if (strpos($messageLower, $word) !== false) {
                return redirect()->to('/feedbacks')->with('error', 'Aspirasi ditolak karena mengandung kata-kata kasar, kotor, atau tidak pantas.');
            }
        }

        $feedbackModel = new FeedbackModel();
        $feedbackModel->insert([
            'sender_name'        => $session->get('name'),
            'class_name'         => $session->get('class') ?: $session->get('role'),
            'message'            => $message,
            'date'               => date('Y-m-d H:i:s'),
            'is_public'          => 1, // Otomatis terbit
            'tahun_pelajaran_id' => $this->getActiveTPId(),
        ]);

        return redirect()->to('/feedbacks')->with('success', 'Aspirasi berhasil dikirim dan diterbitkan secara anonim.');
    }

    public function toggle($id)
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'kepsek'])) {
            return redirect()->to('/feedbacks')->with('error', 'Akses ditolak.');
        }
        $feedbackModel = new FeedbackModel();
        $feedback = $feedbackModel->find($id);

        if ($feedback) {
            $newStatus = $feedback['is_public'] ? 0 : 1;
            $feedbackModel->update($id, ['is_public' => $newStatus]);
            return redirect()->to('/feedbacks')->with('success', 'Status visibilitas aspirasi berhasil diubah.');
        }

        return redirect()->to('/feedbacks')->with('error', 'Aspirasi tidak ditemukan.');
    }

    public function delete($id)
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'kepsek'])) {
            return redirect()->to('/feedbacks')->with('error', 'Akses ditolak.');
        }
        $feedbackModel = new FeedbackModel();
        $feedbackModel->delete($id);

        return redirect()->to('/feedbacks')->with('success', 'Aspirasi berhasil dihapus.');
    }
}
