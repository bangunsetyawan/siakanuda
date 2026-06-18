<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Force password change for Ketua PKL using default password
        $uri = trim($request->getUri()->getPath(), '/');
        if ($session->get('force_change_password') && $uri !== 'profile' && $uri !== 'profile/change-password' && $uri !== 'logout') {
            return redirect()->to('/profile')->with('warning', 'Sebagai Ketua PKL, Anda wajib mengubah password default demi keamanan laporan kelompok Anda.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
