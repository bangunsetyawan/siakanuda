<?php

namespace App\Controllers;

use App\Models\AllowedNumberModel;
use App\Models\StudentModel;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }
        
        $allowedNumberModel = new AllowedNumberModel();
        $teachers = $allowedNumberModel->where('active', 1)->orderBy('name', 'ASC')->findAll();
        
        return view('auth/login', ['teachers' => $teachers]);
    }

    public function attemptLogin()
    {
        $loginType = $this->request->getPost('login_type');
        
        if ($loginType === 'guru') {
            $username = $this->request->getPost('username_guru');
        } else {
            $username = $this->request->getPost('username_siswa');
        }
        
        $password = $this->request->getPost('password');

        if (empty($username) || empty($password)) {
            return redirect()->back()->withInput()->with('error', 'Username dan password wajib diisi.');
        }

        $allowedNumberModel = new AllowedNumberModel();
        $studentModel = new StudentModel();

        // 1. Cek di allowed_numbers (Guru / Admin / Kepsek)
        $staffUser = $allowedNumberModel->where('phone', $username)
                                        ->where('active', 1)
                                        ->first();

        if ($staffUser) {
            if (empty($staffUser['password_hash'])) {
                return redirect()->back()->withInput()->with('error', 'Akun belum memiliki password. Hubungi Admin untuk reset password.');
            }

            if (password_verify($password, $staffUser['password_hash'])) {
                $sessionData = [
                    'phone'        => $staffUser['phone'],
                    'name'         => $staffUser['name'],
                    'role'         => $staffUser['role'],
                    'isSuperAdmin' => ($staffUser['role'] === 'admin'),
                    'logged_in'    => true,
                    'user_type'    => 'staff'
                ];
                session()->set($sessionData);
                session()->regenerate();
                return redirect()->to('/dashboard')->with('success', 'Selamat datang, ' . $staffUser['name']);
            }
        }

        // 2. Cek di students (Siswa — NISN)
        $studentUser = $studentModel->where('nis', $username)->first();

        if ($studentUser) {
            if (empty($studentUser['password_hash'])) {
                return redirect()->back()->withInput()->with('error', 'Akun belum memiliki password. Hubungi Admin untuk reset password.');
            }

            if (password_verify($password, $studentUser['password_hash'])) {
                // Check if they are PKL member/ketua
                $kelompokPklModel = new \App\Models\KelompokPklModel();
                $phone = $studentUser['phone'] ?: $studentUser['nis'];
                $name = $studentUser['name'];
                
                $group = $kelompokPklModel->where('ketua_phone', $phone)->first();
                if (!$group) {
                    $allGroups = $kelompokPklModel->findAll();
                    foreach ($allGroups as $g) {
                        $members = array_map('trim', explode(',', $g['anggota']));
                        if (in_array($name, $members)) {
                            $group = $g;
                            break;
                        }
                    }
                }
                $isPklMember = ($group !== null);

                // Determine if ketua_pkl and using default password (which is NIS)
                $isKetua = ($studentUser['role'] === 'ketua_pkl');
                $isDefaultPassword = password_verify($studentUser['nis'], $studentUser['password_hash']);

                $sessionData = [
                    'student_id'   => $studentUser['id'],
                    'phone'        => $phone,
                    'name'         => $name,
                    'class'        => $studentUser['class'],
                    'role'         => $studentUser['role'] ?: 'siswa',
                    'isSuperAdmin' => false,
                    'logged_in'    => true,
                    'user_type'    => 'student',
                    'is_pkl_member'=> $isPklMember
                ];

                if ($isKetua && $isDefaultPassword) {
                    $sessionData['force_change_password'] = true;
                }

                session()->set($sessionData);
                session()->regenerate();
                
                if ($isKetua && $isDefaultPassword) {
                    return redirect()->to('/profile')->with('warning', 'Sebagai Ketua PKL, Anda wajib mengubah password default demi keamanan laporan kelompok Anda.');
                }
                
                return redirect()->to('/dashboard')->with('success', 'Selamat datang, ' . $studentUser['name']);
            }
        }

        return redirect()->back()->withInput()->with('error', 'Username atau password salah.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Anda telah berhasil logout.');
    }

    public function profile()
    {
        return view('auth/profile');
    }

    public function changePassword()
    {
        $oldPassword = $this->request->getPost('oldPassword');
        $newPassword = $this->request->getPost('newPassword');

        if (empty($newPassword) || strlen($newPassword) < 6) {
            return redirect()->back()->with('error', 'Password baru minimal 6 karakter.');
        }

        $session = session();
        $phone = $session->get('phone');
        $userType = $session->get('user_type');

        if ($userType === 'student') {
            $studentModel = new StudentModel();
            $student = $studentModel->where('id', $session->get('student_id'))->first();
            if ($student) {
                if (!empty($student['password_hash'])) {
                    if (!password_verify($oldPassword, $student['password_hash'])) {
                        return redirect()->back()->with('error', 'Password lama salah.');
                    }
                } else {
                    if ($oldPassword !== $student['nis']) {
                        return redirect()->back()->with('error', 'Password lama salah.');
                    }
                }

                $studentModel->update($student['id'], [
                    'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT)
                ]);
                $session->remove('force_change_password');
                return redirect()->to('/dashboard')->with('success', 'Password berhasil diubah.');
            }
        } else {
            $allowedNumberModel = new AllowedNumberModel();
            $staff = $allowedNumberModel->where('phone', $phone)->first();
            if ($staff) {
                if (!empty($staff['password_hash'])) {
                    if (!password_verify($oldPassword, $staff['password_hash'])) {
                        return redirect()->back()->with('error', 'Password lama salah.');
                    }
                } else {
                    return redirect()->back()->with('error', 'Akun belum memiliki password. Hubungi Admin untuk reset password.');
                }

                // SQLite allowed_numbers table might not have password_hash column or needs it, we can update password_hash.
                // Let's first ensure we can perform a dynamic update of fields.
                $allowedNumberModel->update($staff['id'], [
                    'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT)
                ]);
                return redirect()->to('/dashboard')->with('success', 'Password berhasil diubah.');
            }
        }

        return redirect()->back()->with('error', 'Gagal memproses perubahan password.');
    }
}
