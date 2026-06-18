<?php

namespace App\Models;

use CodeIgniter\Model;

class TahunPelajaranModel extends Model
{
    protected $table = 'tahun_pelajaran';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama', 'semester', 'tanggal_mulai', 'tanggal_selesai', 'is_active'];
    protected $useTimestamps = false;

    protected $validationRules = [
        'nama'     => 'required|max_length[20]',
        'semester' => 'required|in_list[Ganjil,Genap]',
        'tanggal_mulai'   => 'permit_empty|valid_date[Y-m-d]',
        'tanggal_selesai' => 'permit_empty|valid_date[Y-m-d]',
    ];

    /**
     * Ambil tahun pelajaran aktif (is_active = 1).
     */
    public function getActive(): ?array
    {
        return $this->where('is_active', 1)->first();
    }
}
