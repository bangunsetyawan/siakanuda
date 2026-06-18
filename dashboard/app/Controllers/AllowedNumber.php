<?php

namespace App\Controllers;

use App\Models\AllowedNumberModel;

class AllowedNumber extends BaseController
{
    public function index()
    {
        return redirect()->to('/whatsapp-settings?tab=whitelist');
    }

    public function create()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'kepsek'])) {
            return redirect()->to('/whatsapp-settings?tab=whitelist')->with('error', 'Akses ditolak.');
        }
        $allowedNumberModel = new AllowedNumberModel();

        $phone = $this->request->getPost('phone');
        $name = $this->request->getPost('name');
        $roleInput = $this->request->getPost('role') ?: 'guru_mapel';
        $tugas_tambahan = $this->request->getPost('tugas_tambahan') ?: null;

        if (empty($phone) || empty($name)) {
            return redirect()->to('/whatsapp-settings?tab=whitelist')->with('error', 'Nama dan Nomor WhatsApp wajib diisi.');
        }

        // Validate role against whitelist
        $allowedRoles = ['admin', 'kepsek', 'guru', 'guru_bk', 'guru_mapel'];
        if (!in_array($roleInput, $allowedRoles)) {
            return redirect()->to('/whatsapp-settings?tab=whitelist')->with('error', 'Role tidak valid.');
        }
        $role = $roleInput;

        // Clean phone number format
        $phone = preg_replace('/[^\d]/', '', $phone);
        if (str_starts_with($phone, '08')) {
            $phone = '628' . substr($phone, 2);
        }

        // Check if phone already whitelisted
        $existing = $allowedNumberModel->where('phone', $phone)->first();
        if ($existing) {
            if ($existing['active'] == 0) {
                // Reactivate
                $allowedNumberModel->update($existing['id'], [
                    'name' => $name,
                    'role' => $role,
                    'tugas_tambahan' => $tugas_tambahan,
                    'active' => 1
                ]);
                return redirect()->to('/whatsapp-settings?tab=whitelist')->with('success', 'Nomor guru berhasil didaftarkan kembali.');
            }
            return redirect()->to('/whatsapp-settings?tab=whitelist')->with('error', 'Nomor WhatsApp sudah terdaftar.');
        }

        $allowedNumberModel->insert([
            'phone' => $phone,
            'name' => $name,
            'role' => $role,
            'tugas_tambahan' => $tugas_tambahan,
            'active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/whatsapp-settings?tab=whitelist')->with('success', 'Guru/Staf berhasil ditambahkan ke whitelist.');
    }

    public function update($id)
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'kepsek'])) {
            return redirect()->to('/whatsapp-settings?tab=whitelist')->with('error', 'Akses ditolak.');
        }
        $allowedNumberModel = new AllowedNumberModel();

        $phone = $this->request->getPost('phone');
        $name = $this->request->getPost('name');
        $roleInput = $this->request->getPost('role');
        $tugas_tambahan = $this->request->getPost('tugas_tambahan') ?: null;

        if (empty($phone) || empty($name)) {
            return redirect()->to('/whatsapp-settings?tab=whitelist')->with('error', 'Nama dan Nomor WhatsApp wajib diisi.');
        }

        // Validate role against whitelist
        $allowedRoles = ['admin', 'kepsek', 'guru', 'guru_bk', 'guru_mapel'];
        if (!in_array($roleInput, $allowedRoles)) {
            return redirect()->to('/whatsapp-settings?tab=whitelist')->with('error', 'Role tidak valid.');
        }
        $role = $roleInput;

        $phone = preg_replace('/[^\d]/', '', $phone);
        if (str_starts_with($phone, '08')) {
            $phone = '628' . substr($phone, 2);
        }

        $allowedNumberModel->update($id, [
            'phone' => $phone,
            'name' => $name,
            'role' => $role,
            'tugas_tambahan' => $tugas_tambahan
        ]);

        return redirect()->to('/whatsapp-settings?tab=whitelist')->with('success', 'Data guru/staf berhasil diperbarui.');
    }

    public function delete($id)
    {
        $role = session()->get('role');
        if ($role !== 'admin') {
            return redirect()->to('/whatsapp-settings?tab=whitelist')->with('error', 'Akses ditolak. Hanya Admin yang dapat menghapus data guru/staf.');
        }
        $allowedNumberModel = new AllowedNumberModel();
        
        // Soft delete: set active = 0 so it remains in db but whitelisting is revoked
        $allowedNumberModel->update($id, ['active' => 0]);

        return redirect()->to('/whatsapp-settings?tab=whitelist')->with('success', 'Akses guru/staf berhasil dicabut (Whitelisting dinonaktifkan).');
    }

    public function import()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'kepsek'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }

        $json = $this->request->getJSON(true);
        if (!$json || !isset($json['teachers']) || !is_array($json['teachers'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Format data tidak valid.']);
        }

        $allowedNumberModel = new AllowedNumberModel();
        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($json['teachers'] as $index => $row) {
            $phone = isset($row['phone']) ? trim($row['phone']) : '';
            $name = isset($row['name']) ? trim($row['name']) : '';
            $teacherRole = isset($row['role']) ? trim($row['role']) : 'guru_mapel';
            $tugas_tambahan = isset($row['tugas_tambahan']) ? trim($row['tugas_tambahan']) : (isset($row['tugas tambahan']) ? trim($row['tugas tambahan']) : null);

            if (empty($phone) || empty($name)) {
                $skippedCount++;
                $errors[] = "Baris " . ($index + 1) . ": Nama dan No. WhatsApp wajib diisi.";
                continue;
            }

            // Clean phone format
            $phone = preg_replace('/[^\d]/', '', $phone);
            if (str_starts_with($phone, '08')) {
                $phone = '628' . substr($phone, 2);
            }

            // Validate role
            $allowedRoles = ['admin', 'kepsek', 'guru', 'guru_bk', 'guru_mapel'];
            if (!in_array($teacherRole, $allowedRoles)) {
                $teacherRole = 'guru_mapel';
            }

            // Check if already exists
            $existing = $allowedNumberModel->where('phone', $phone)->first();

            $teacherData = [
                'phone' => $phone,
                'name' => $name,
                'role' => $teacherRole,
                'active' => 1,
                'tugas_tambahan' => $tugas_tambahan,
                'password_hash' => password_hash($phone, PASSWORD_BCRYPT),
                'first_login' => 0
            ];

            if ($existing) {
                // Update
                $allowedNumberModel->update($existing['id'], $teacherData);
            } else {
                // Insert
                $allowedNumberModel->insert($teacherData);
            }
            $importedCount++;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => "Berhasil memproses $importedCount data guru. (Skipped: $skippedCount)",
            'errors' => $errors
        ]);
    }
}
