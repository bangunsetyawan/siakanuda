<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BkkAlumniModel;
use App\Models\BkkMouModel;
use App\Models\BkkKunjunganModel;

class Bkk extends BaseController
{
    protected $alumniModel;
    protected $mouModel;
    protected $kunjunganModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $this->alumniModel = new BkkAlumniModel();
        $this->mouModel = new BkkMouModel();
        $this->kunjunganModel = new BkkKunjunganModel();
    }

    public function index()
    {
        // Get counts for dashboard
        $alumni = $this->alumniModel->findAll();
        $totalAlumni = count($alumni);
        $bekerja = count(array_filter($alumni, function($a) { return stripos($a['status_utama'] ?? '', 'bekerja') !== false; }));
        $studi = count(array_filter($alumni, function($a) { return stripos($a['status_utama'] ?? '', 'studi') !== false; }));
        $wirausaha = count(array_filter($alumni, function($a) { return stripos($a['status_utama'] ?? '', 'wirausaha') !== false; }));
        $mencari = count(array_filter($alumni, function($a) { return stripos($a['status_utama'] ?? '', 'mencari') !== false || stripos($a['status_utama'] ?? '', 'belum') !== false; }));

        $data = [
            'title' => 'Dashboard BKK',
            'stats' => [
                'total' => $totalAlumni,
                'bekerja' => $bekerja,
                'studi' => $studi,
                'wirausaha' => $wirausaha,
                'mencari' => $mencari
            ]
        ];
        return view('bkk/dashboard', $data);
    }

    public function data_alumni()
    {
        $data = [
            'title' => 'Data Tracer Study Alumni',
            'alumni' => $this->alumniModel->findAll()
        ];
        return view('bkk/data_alumni', $data);
    }

    public function store_alumni()
    {
        $data = $this->request->getPost();
        
        if (empty($data['id'])) {
            $this->alumniModel->insert($data);
            session()->setFlashdata('success', 'Data alumni berhasil ditambahkan.');
        } else {
            $this->alumniModel->update($data['id'], $data);
            session()->setFlashdata('success', 'Data alumni berhasil diubah.');
        }

        return redirect()->to(base_url('bkk/data_alumni'));
    }

    public function delete_alumni()
    {
        $id = $this->request->getPost('id');
        if ($id) {
            $this->alumniModel->delete($id);
            session()->setFlashdata('success', 'Data alumni berhasil dihapus.');
        }
        return redirect()->to(base_url('bkk/data_alumni'));
    }

    public function mitra_industri()
    {
        // Mitra industri bisa menggunakan tabel MOU, ini kita arahkan ke view terpisah jika perlu
        // Atau ambil data dari MOU
        $data = [
            'title' => 'Mitra Industri / IDUKA',
            'mou' => $this->mouModel->findAll()
        ];
        return view('bkk/mitra_industri', $data);
    }

    public function mou_iduka()
    {
        $data = [
            'title' => 'Dokumen MOU IDUKA',
            'mou' => $this->mouModel->findAll()
        ];
        return view('bkk/mou_iduka', $data);
    }

    public function store_mou()
    {
        $data = $this->request->getPost();
        
        if (empty($data['id'])) {
            $this->mouModel->insert($data);
            session()->setFlashdata('success', 'MOU berhasil ditambahkan.');
        } else {
            $this->mouModel->update($data['id'], $data);
            session()->setFlashdata('success', 'MOU berhasil diubah.');
        }

        return redirect()->to(base_url('bkk/mou_iduka'));
    }

    public function delete_mou()
    {
        $id = $this->request->getPost('id');
        if ($id) {
            $this->mouModel->delete($id);
            session()->setFlashdata('success', 'MOU berhasil dihapus.');
        }
        return redirect()->to(base_url('bkk/mou_iduka'));
    }

    public function kunjungan_industri()
    {
        $data = [
            'title' => 'Kunjungan Industri',
            'kunjungan' => $this->kunjunganModel->findAll()
        ];
        return view('bkk/kunjungan_industri', $data);
    }

    public function store_kunjungan()
    {
        $data = $this->request->getPost();
        
        if (empty($data['id'])) {
            $this->kunjunganModel->insert($data);
            session()->setFlashdata('success', 'Kunjungan berhasil ditambahkan.');
        } else {
            $this->kunjunganModel->update($data['id'], $data);
            session()->setFlashdata('success', 'Kunjungan berhasil diubah.');
        }

        return redirect()->to(base_url('bkk/kunjungan_industri'));
    }

    public function delete_kunjungan()
    {
        $id = $this->request->getPost('id');
        if ($id) {
            $this->kunjunganModel->delete($id);
            session()->setFlashdata('success', 'Kunjungan berhasil dihapus.');
        }
        return redirect()->to(base_url('bkk/kunjungan_industri'));
    }
}
