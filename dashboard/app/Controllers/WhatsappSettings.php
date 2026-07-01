<?php

namespace App\Controllers;

use Config\Services;

class WhatsappSettings extends BaseController
{
    private $waBotUrl;

    public function __construct()
    {
        $this->waBotUrl = env('WA_BOT_URL') ?: 'http://127.0.0.1:7860';
    }

    private function checkAdmin()
    {
        $role = session()->get('role');
        if ($role !== 'admin') {
            throw new \CodeIgniter\Router\Exceptions\RedirectException(
                redirect()->to('/dashboard')->with('error', 'Akses ditolak. Hanya Admin yang dapat mengakses Pengaturan WA.')
            );
        }
    }

    public function index()
    {
        $this->checkAdmin();
        $db = \Config\Database::connect();

        // 1. Fetch templates
        $templates = $db->table('bot_templates')->get()->getResultArray();

        // 2. Fetch cron configs
        $cronConfigs = $db->table('cron_configs')->get()->getResultArray();

        // 3. Fetch bot status from Node.js
        $botStatus = [
            'connected' => false,
            'phone' => null,
            'name' => null,
            'version' => 'N/A'
        ];

        try {
            $client = Services::curlrequest();
            $response = $client->get($this->waBotUrl . '/api/status', [
                'timeout' => 3,
                'http_errors' => false
            ]);

            if ($response->getStatusCode() === 200) {
                $statusData = json_decode($response->getBody(), true);
                if (is_array($statusData)) {
                    $botStatus = array_merge($botStatus, $statusData);
                }
            }
        } catch (\Exception $e) {
            // Node.js background service might be offline
        }

        // 4. Fetch Whitelist (Guru & Staf)
        $allowedNumberModel = new \App\Models\AllowedNumberModel();
        $whitelist = $allowedNumberModel->where('active', 1)->orderBy('name', 'ASC')->findAll();

        // 5. Fetch Active Group JIDs (Populated from Node.js status API)
        $broadcastGroupJid = $botStatus['broadcast_group_jid'] ?? '';
        $schoolGroupJid = $botStatus['school_group_jid'] ?? '';
        $teacherGroupJid = $botStatus['teacher_group_jid'] ?? '';
        $monitoringGroupJid = $botStatus['monitoring_group_jid'] ?? '';
        // 6. Fetch Broadcast Targets Settings
        $broadcastTargets = [];
        $settings = $db->table('system_settings')->like('key', 'broadcast_pkl_')->get()->getResultArray();
        foreach ($settings as $s) {
            $broadcastTargets[$s['key']] = $s['value'];
        }

        $data = [
            'title' => 'Pengaturan Bot WhatsApp',
            'templates' => $templates,
            'cronConfigs' => $cronConfigs,
            'botStatus' => $botStatus,
            'waBotUrl' => $this->waBotUrl,
            'whitelist' => $whitelist,
            'broadcastGroupJid' => $broadcastGroupJid,
            'schoolGroupJid' => $schoolGroupJid,
            'teacherGroupJid' => $teacherGroupJid,
            'monitoringGroupJid' => $monitoringGroupJid,
            'broadcastTargets' => $broadcastTargets
        ];

        return view('whatsapp_settings/index', $data);
    }

    public function logout()
    {
        $this->checkAdmin();

        try {
            $client = Services::curlrequest();
            $response = $client->post($this->waBotUrl . '/api/bot/logout', [
                'timeout' => 10,
                'http_errors' => false
            ]);

            if ($response->getStatusCode() === 200) {
                return redirect()->to('/whatsapp-settings')->with('success', 'Sesi WhatsApp berhasil diputus. Silakan pindai QR code baru untuk mendaftarkan nomor.');
            } else {
                return redirect()->to('/whatsapp-settings')->with('error', 'Gagal memutus sesi WhatsApp.');
            }
        } catch (\Exception $e) {
            return redirect()->to('/whatsapp-settings')->with('error', 'Gagal berkomunikasi dengan layanan WhatsApp: ' . $e->getMessage());
        }
    }

    public function testBot()
    {
        $this->checkAdmin();
        $target = $this->request->getPost('target_number');
        
        if (empty($target)) {
            return redirect()->to('/whatsapp-settings')->with('error', 'Nomor tujuan harus diisi.');
        }

        try {
            $client = Services::curlrequest();
            $response = $client->post($this->waBotUrl . '/api/bot/test', [
                'json' => ['target' => $target],
                'timeout' => 10,
                'http_errors' => false
            ]);

            if ($response->getStatusCode() === 200) {
                return redirect()->to('/whatsapp-settings')->with('success', 'Pesan tes berhasil dikirimkan ke ' . esc($target));
            } else {
                $body = json_decode($response->getBody(), true);
                $apiError = $body['error'] ?? 'Gagal mengirim pesan tes. Pastikan bot dalam keadaan terhubung.';
                return redirect()->to('/whatsapp-settings')->with('error', $apiError);
            }
        } catch (\Exception $e) {
            return redirect()->to('/whatsapp-settings')->with('error', 'Gagal berkomunikasi dengan layanan bot: ' . $e->getMessage());
        }
    }

    public function updateTemplates()
    {
        $this->checkAdmin();
        $db = \Config\Database::connect();

        $postedTemplates = $this->request->getPost('templates');
        if (!is_array($postedTemplates)) {
            return redirect()->to('/whatsapp-settings')->with('error', 'Data tidak valid.');
        }

        $db->transBegin();
        try {
            foreach ($postedTemplates as $key => $body) {
                $db->table('bot_templates')
                   ->where('key', $key)
                   ->update(['body' => $body, 'updated_at' => date('Y-m-d H:i:s')]);
            }
            $db->transCommit();
            return redirect()->to('/whatsapp-settings?tab=templates')->with('success', 'Template pesan WhatsApp berhasil disimpan.');
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->to('/whatsapp-settings?tab=templates')->with('error', 'Gagal memperbarui template: ' . $e->getMessage());
        }
    }

    public function updateCron()
    {
        $this->checkAdmin();
        $db = \Config\Database::connect();

        $cronInputs = $this->request->getPost('cron');
        if (!is_array($cronInputs)) {
            return redirect()->to('/whatsapp-settings')->with('error', 'Data tidak valid.');
        }

        $db->transBegin();
        try {
            $configsForApi = [];
            foreach ($cronInputs as $key => $data) {
                $isActive = isset($data['is_active']) ? 1 : 0;
                $cronExpression = $data['cron_expression'] ?? '* * * * *';

                $db->table('cron_configs')
                   ->where('key', $key)
                   ->update([
                       'cron_expression' => $cronExpression,
                       'is_active' => $isActive,
                       'updated_at' => date('Y-m-d H:i:s')
                   ]);

                $configsForApi[] = [
                    'key' => $key,
                    'cronExpression' => $cronExpression,
                    'isActive' => $isActive
                ];
            }
            $db->transCommit();

            // Send reload request to Node.js backend
            try {
                $client = Services::curlrequest();
                $client->post($this->waBotUrl . '/api/settings/cron', [
                    'json' => ['configs' => $configsForApi],
                    'timeout' => 5,
                    'http_errors' => false
                ]);
            } catch (\Exception $apiEx) {
                // Log API failure, but DB is updated
                return redirect()->to('/whatsapp-settings')->with('success', 'Jadwal berhasil disimpan di database local, namun gagal menerapkan perubahan jadwal secara langsung (reload failed): ' . $apiEx->getMessage());
            }

            return redirect()->to('/whatsapp-settings')->with('success', 'Jadwal otomatis (Cron Jobs) WhatsApp berhasil diperbarui.');
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->to('/whatsapp-settings')->with('error', 'Gagal memperbarui jadwal: ' . $e->getMessage());
        }
    }

    public function updateGroups()
    {
        $this->checkAdmin();
        $broadcastGroupJid = $this->request->getPost('broadcast_group_jid');
        $schoolGroupJid = $this->request->getPost('school_group_jid');
        $teacherGroupJid = $this->request->getPost('teacher_group_jid');
        $monitoringGroupJid = $this->request->getPost('monitoring_group_jid');

        try {
            $client = Services::curlrequest();
            $response = $client->post($this->waBotUrl . '/api/settings/groups', [
                'json' => [
                    'broadcast_group_jid' => $broadcastGroupJid,
                    'school_group_jid' => $schoolGroupJid,
                    'teacher_group_jid' => $teacherGroupJid,
                    'monitoring_group_jid' => $monitoringGroupJid
                ],
                'timeout' => 5,
                'http_errors' => false
            ]);

            if ($response->getStatusCode() === 200) {
                return redirect()->to('/whatsapp-settings?tab=groups')->with('success', 'Konfigurasi Grup WhatsApp berhasil disimpan.');
            } else {
                return redirect()->to('/whatsapp-settings?tab=groups')->with('error', 'Gagal memperbarui konfigurasi grup.');
            }
        } catch (\Exception $e) {
            return redirect()->to('/whatsapp-settings?tab=groups')->with('error', 'Gagal berkomunikasi dengan layanan bot: ' . $e->getMessage());
        }
    }

    public function updateBroadcastTargets()
    {
        $this->checkAdmin();
        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            // Targets 'pembimbing', 'orangtua', 'siswa' are deprecated but we can leave them in DB as inactive
            $targets = ['group', 'pembimbing', 'orangtua', 'instruktur', 'anggota'];
            foreach ($targets as $target) {
                $val = $this->request->getPost("broadcast_pkl_$target") ? '1' : '0';
                $db->table('system_settings')->where('key', "broadcast_pkl_$target")->update(['value' => $val, 'updated_at' => date('Y-m-d H:i:s')]);
            }
            $db->transCommit();
            return redirect()->to('/whatsapp-settings?tab=groups')->with('success', 'Target Broadcast Laporan PKL berhasil disimpan.');
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->to('/whatsapp-settings?tab=groups')->with('error', 'Gagal menyimpan pengaturan target: ' . $e->getMessage());
        }
    }

    public function createCron()
    {
        $this->checkAdmin();
        $db = \Config\Database::connect();

        $key = $this->request->getPost('key');
        $name = $this->request->getPost('name');
        $cronExpression = $this->request->getPost('cron_expression');
        $action = $this->request->getPost('action');
        $description = $this->request->getPost('description') ?: '';
        $isActive = $this->request->getPost('is_active') ? 1 : 0;

        $payloadTarget = $this->request->getPost('payload_target');
        $payloadMessage = $this->request->getPost('payload_message');
        $payloadJson = '{}';

        if ($action === 'custom_message') {
            if (empty($payloadTarget) || empty($payloadMessage)) {
                return redirect()->to('/whatsapp-settings?tab=cron')->with('error', 'Target WA dan Isi Pesan wajib diisi untuk Pesan Kustom.');
            }
            $payloadJson = json_encode([
                'target' => trim($payloadTarget),
                'message' => trim($payloadMessage)
            ]);
        }

        if (empty($key) || empty($name) || empty($cronExpression) || empty($action)) {
            return redirect()->to('/whatsapp-settings?tab=cron')->with('error', 'Semua kolom wajib diisi.');
        }

        // Key validation: alphanumeric + underscores only
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $key)) {
            return redirect()->to('/whatsapp-settings?tab=cron')->with('error', 'Key hanya boleh berisi huruf, angka, dan underscore.');
        }

        // Insert into database
        $db->transBegin();
        try {
            // Check if key already exists
            $existing = $db->table('cron_configs')->where('key', $key)->get()->getRow();
            if ($existing) {
                return redirect()->to('/whatsapp-settings?tab=cron')->with('error', 'Tugas cron dengan Key tersebut sudah ada.');
            }

            $db->table('cron_configs')->insert([
                'key' => $key,
                'name' => $name,
                'cron_expression' => $cronExpression,
                'is_active' => $isActive,
                'description' => $description,
                'action' => $action,
                'payload' => $payloadJson,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $db->transCommit();

            // Notify Node.js server to reload cron configs
            try {
                $client = Services::curlrequest();
                $client->post($this->waBotUrl . '/api/settings/cron/add', [
                    'json' => [
                        'key' => $key,
                        'name' => $name,
                        'cronExpression' => $cronExpression,
                        'isActive' => $isActive,
                        'description' => $description,
                        'action' => $action,
                        'payload' => $payloadJson
                    ],
                    'timeout' => 5,
                    'http_errors' => false
                ]);
            } catch (\Exception $apiEx) {
                // Return success with warning
                return redirect()->to('/whatsapp-settings?tab=cron')->with('success', 'Tugas cron berhasil ditambahkan di database lokal, namun gagal merefresh scheduler: ' . $apiEx->getMessage());
            }

            return redirect()->to('/whatsapp-settings?tab=cron')->with('success', 'Tugas cron baru berhasil ditambahkan.');
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->to('/whatsapp-settings?tab=cron')->with('error', 'Gagal menambahkan tugas cron: ' . $e->getMessage());
        }
    }

    public function deleteCron($key)
    {
        $this->checkAdmin();
        $db = \Config\Database::connect();

        $defaults = ['class_attendance_check', 'pkl_report_check', 'pkl_escalation_check', 'auto_alpha_job'];
        if (in_array($key, $defaults)) {
            return redirect()->to('/whatsapp-settings?tab=cron')->with('error', 'Tugas cron bawaan sistem tidak boleh dihapus.');
        }

        $db->transBegin();
        try {
            $db->table('cron_configs')->where('key', $key)->delete();
            $db->transCommit();

            // Notify Node.js server
            try {
                $client = Services::curlrequest();
                $client->post($this->waBotUrl . '/api/settings/cron/delete/' . urlencode($key), [
                    'timeout' => 5,
                    'http_errors' => false
                ]);
            } catch (\Exception $apiEx) {
                return redirect()->to('/whatsapp-settings?tab=cron')->with('success', 'Tugas cron berhasil dihapus dari database lokal, namun gagal merefresh scheduler: ' . $apiEx->getMessage());
            }

            return redirect()->to('/whatsapp-settings?tab=cron')->with('success', 'Tugas cron berhasil dihapus.');
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->to('/whatsapp-settings?tab=cron')->with('error', 'Gagal menghapus tugas cron: ' . $e->getMessage());
        }
    }

    public function joinGroup()
    {
        $this->checkAdmin();
        $inviteLink = $this->request->getPost('invite_link');
        if (empty($inviteLink)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Link undangan wajib diisi.']);
        }

        try {
            $client = Services::curlrequest();
            $response = $client->post($this->waBotUrl . '/api/bot/join-group', [
                'json' => ['inviteLink' => $inviteLink],
                'timeout' => 15,
                'http_errors' => false
            ]);

            if ($response->getStatusCode() === 200) {
                $body = json_decode($response->getBody(), true);
                if (isset($body['ok']) && $body['ok']) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'Berhasil bergabung ke grup!',
                        'group' => $body['group']
                    ]);
                } else {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => $body['error'] ?? 'Gagal bergabung ke grup.'
                    ]);
                }
            } else {
                $errBody = json_decode($response->getBody(), true);
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $errBody['error'] ?? 'Gagal menghubungi server bot (HTTP ' . $response->getStatusCode() . ').'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Kesalahan koneksi: ' . $e->getMessage()
            ]);
        }
    }
}
