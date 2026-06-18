<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceKelasModel extends Model
{
    protected $table            = 'attendance_kelas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['date', 'class_name', 'teacher_phone', 'attendance_data', 'tahun_pelajaran_id'];
    protected $validationRules = [
        'date'       => 'required|valid_date[Y-m-d]',
        'class_name' => 'required|max_length[20]',
    ];
}
