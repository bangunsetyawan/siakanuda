<?php

namespace App\Controllers;

use App\Models\MessageLogModel;
use App\Models\AllowedNumberModel;
use App\Models\StudentModel;

class MessageLog extends BaseController
{
    public function index()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin'])) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak. Hanya Admin yang dapat melihat log pesan.');
        }

        $messageLogModel = new MessageLogModel();
        $allowedNumberModel = new AllowedNumberModel();
        $studentModel = new StudentModel();

        // Get 100 most recent logs
        $logs = $messageLogModel->orderBy('created_at', 'DESC')->limit(100)->findAll();

        // Build name mappings
        $teachers = $allowedNumberModel->findAll();
        $students = $studentModel->findAll();

        $nameMap = [];
        foreach ($teachers as $t) {
            $nameMap[$t['phone']] = $t['name'] . ' (Guru/' . ucfirst($t['role']) . ')';
        }
        foreach ($students as $s) {
            if ($s['phone']) {
                $nameMap[$s['phone']] = $s['name'] . ' (Siswa/' . $s['class'] . ')';
            }
        }

        $parsedLogs = [];
        foreach ($logs as $log) {
            $senderName = $nameMap[$log['phone']] ?? 'Nomor +' . $log['phone'] . ' (Belum Whitelisted)';
            $parsedLogs[] = [
                'id' => $log['id'],
                'phone' => $log['phone'],
                'sender_name' => $senderName,
                'direction' => $log['direction'],
                'message' => $log['message'],
                'ai_action' => $log['ai_action'],
                'status' => $log['status'],
                'created_at' => $log['created_at']
            ];
        }

        $data = [
            'title' => 'Log Audit Chat WhatsApp',
            'logs' => $parsedLogs,
            'nameMap' => $nameMap,
            'waBotUrl' => env('WA_BOT_URL') ?: 'http://127.0.0.1:7860'
        ];

        return view('logs/index', $data);
    }
}
