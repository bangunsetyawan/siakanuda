<?php

namespace App\Models;

use CodeIgniter\Model;

class KelompokPklModel extends Model
{
    protected $table            = 'kelompok_pkl';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['tempat_pkl', 'ketua_phone', 'anggota', 'pembimbing_phone', 'tahun_pelajaran_id'];
    protected $validationRules = [
        'tempat_pkl'  => 'required|min_length[3]|max_length[200]',
        'ketua_phone' => 'permit_empty|max_length[20]',
        'anggota'     => 'required|min_length[3]',
    ];
}
