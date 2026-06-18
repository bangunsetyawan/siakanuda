<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table            = 'attendance';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['student_id', 'date', 'status', 'note', 'tahun_pelajaran_id'];
    protected $validationRules = [
        'student_id' => 'required|integer',
        'date'       => 'required|valid_date[Y-m-d]',
        'status'     => 'required|in_list[hadir,sakit,izin,alpha]',
    ];
}
