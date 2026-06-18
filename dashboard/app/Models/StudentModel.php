<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentModel extends Model
{
    protected $table            = 'students';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['name', 'class', 'nis', 'gender', 'phone', 'role', 'created_at', 'password_hash', 'first_login', 'is_active'];
    protected $validationRules = [
        'name'   => 'required|min_length[2]|max_length[100]',
        'class'  => 'required|max_length[20]',
        'nis'    => 'permit_empty|max_length[20]',
        'gender' => 'permit_empty|in_list[L,P]',
        'phone'  => 'permit_empty|max_length[20]',
        'role'   => 'permit_empty|in_list[siswa,ketua_pkl,anggotapkl]',
        'is_active' => 'permit_empty|integer|in_list[0,1]',
    ];

    public function syncPklRoles($tpId)
    {
        $kelompokPklModel = new \App\Models\KelompokPklModel();
        
        // 1. Get all groups for this Tahun Pelajaran
        $groups = $kelompokPklModel->where('tahun_pelajaran_id', $tpId)->findAll();
        
        // 2. Map all ketua phones and member names currently in PKL groups
        $ketuaPhones = [];
        $memberNames = [];
        
        foreach ($groups as $g) {
            if (!empty($g['ketua_phone'])) {
                $ketuaPhones[] = $g['ketua_phone'];
            }
            if (!empty($g['anggota'])) {
                $members = explode(',', $g['anggota']);
                foreach ($members as $m) {
                    $mName = trim($m);
                    if ($mName !== '') {
                        $memberNames[] = $mName;
                    }
                }
            }
        }
        
        // Remove duplicates
        $ketuaPhones = array_unique($ketuaPhones);
        $memberNames = array_unique($memberNames);
        
        // 3. Get all students
        $students = $this->findAll();
        
        // 4. Update role for each student
        foreach ($students as $student) {
            $studentPhone = $student['phone'] ?: $student['nis'];
            $currentRole = $student['role'] ?: 'siswa';
            $newRole = 'siswa';
            
            // Check if they are a ketua
            if (in_array($studentPhone, $ketuaPhones)) {
                $newRole = 'ketua_pkl';
            }
            // Check if they are a member OR if they are in class XII
            elseif (in_array($student['name'], $memberNames) || str_starts_with(trim($student['class'] ?: ''), 'XII')) {
                $newRole = 'anggotapkl';
            }
            
            // Only update if role actually changed
            if ($currentRole !== $newRole) {
                $this->update($student['id'], ['role' => $newRole]);
            }
        }
    }
}
