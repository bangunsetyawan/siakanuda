<?php

namespace App\Controllers;

use App\Models\TahunPelajaranModel;

class TahunPelajaran extends BaseController
{
    /**
     * Daftar semua Tahun Pelajaran.
     */
    public function index()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        $model = new TahunPelajaranModel();

        $data = [
            'title' => 'Tahun Pelajaran',
            'tahunPelajaran' => $model->orderBy('id', 'DESC')->findAll(),
        ];

        return view('tahun_pelajaran/index', $data);
    }

    /**
     * Tambah Tahun Pelajaran baru.
     */
    public function create()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        $rules = [
            'nama'            => 'required|max_length[20]',
            'tanggal_mulai'   => 'permit_empty|valid_date[Y-m-d]',
            'tanggal_selesai' => 'permit_empty|valid_date[Y-m-d]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new TahunPelajaranModel();
        $model->insert([
            'nama'            => $this->request->getPost('nama'),
            'semester'        => 'Ganjil',
            'tanggal_mulai'   => $this->request->getPost('tanggal_mulai') ?: null,
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai') ?: null,
            'is_active'       => 0,
        ]);

        return redirect()->to('/tahun-pelajaran')->with('success', 'Tahun Pelajaran berhasil ditambahkan.');
    }

    /**
     * Update Tahun Pelajaran.
     */
    public function update($id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        $rules = [
            'nama'            => 'required|max_length[20]',
            'tanggal_mulai'   => 'permit_empty|valid_date[Y-m-d]',
            'tanggal_selesai' => 'permit_empty|valid_date[Y-m-d]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new TahunPelajaranModel();
        $model->update($id, [
            'nama'            => $this->request->getPost('nama'),
            'semester'        => 'Ganjil',
            'tanggal_mulai'   => $this->request->getPost('tanggal_mulai') ?: null,
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai') ?: null,
        ]);

        return redirect()->to('/tahun-pelajaran')->with('success', 'Tahun Pelajaran berhasil diperbarui.');
    }

    /**
     * Set Tahun Pelajaran aktif.
     */
    public function setActive($id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        $model = new TahunPelajaranModel();
        $db = \Config\Database::connect();

        // Reset semua ke non-aktif
        $db->table('tahun_pelajaran')->update(['is_active' => 0]);

        // Set yang dipilih sebagai aktif
        $model->update($id, ['is_active' => 1]);

        $tp = $model->find($id);
        return redirect()->to('/tahun-pelajaran')->with('success', 'Tahun Pelajaran ' . ($tp['nama'] ?? '') . ' sekarang AKTIF.');
    }

    /**
     * Hapus Tahun Pelajaran (tidak boleh hapus yang aktif).
     */
    public function delete($id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        $model = new TahunPelajaranModel();
        $tp = $model->find($id);

        if (!$tp) {
            return redirect()->to('/tahun-pelajaran')->with('error', 'Data tidak ditemukan.');
        }

        if ($tp['is_active'] == 1) {
            return redirect()->to('/tahun-pelajaran')->with('error', 'Tidak bisa menghapus Tahun Pelajaran yang sedang aktif.');
        }

        $model->delete($id);
        return redirect()->to('/tahun-pelajaran')->with('success', 'Tahun Pelajaran berhasil dihapus.');
    }
}
