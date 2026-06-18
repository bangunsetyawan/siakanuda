<?php

namespace App\Models;

use CodeIgniter\Model;

class CounselingModel extends Model
{
    protected $table            = 'counseling';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['student_id', 'date', 'type', 'content', 'counselor', 'tahun_pelajaran_id'];
    protected $validationRules = [
        'student_id' => 'required|integer',
        'date'       => 'required|valid_date[Y-m-d]',
        'type'       => 'required|in_list[catatan,panggilan,kunjungan]',
        'content'    => 'required|min_length[5]|max_length[2000]',
    ];
}
