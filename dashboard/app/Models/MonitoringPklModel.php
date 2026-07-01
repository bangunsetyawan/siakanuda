<?php

namespace App\Models;

use CodeIgniter\Model;

class MonitoringPklModel extends Model
{
    protected $table            = 'monitoring_pkl';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $allowedFields    = ['kelompok_pkl_id', 'tanggal', 'pembimbing_phone', 'catatan', 'photo_url', 'location_data', 'tahun_pelajaran_id'];
    protected $validationRules = [
        'kelompok_pkl_id' => 'required|integer',
        'tanggal'         => 'required|valid_date[Y-m-d]',
        'pembimbing_phone'=> 'required|max_length[20]',
        'catatan'         => 'required|min_length[10]',
    ];
}
