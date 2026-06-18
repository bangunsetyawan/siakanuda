<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendancePklModel extends Model
{
    protected $table            = 'attendance_pkl';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['date', 'tempat_pkl', 'ketua_phone', 'status_libur', 'libur_reason', 'location_data', 'attendance_data', 'photo_url', 'jurnal_kegiatan', 'is_takeover', 'tahun_pelajaran_id'];
    protected $validationRules = [
        'date'        => 'required|valid_date[Y-m-d]',
        'tempat_pkl'  => 'required|max_length[200]',
        'ketua_phone' => 'required|max_length[20]',
    ];
}
