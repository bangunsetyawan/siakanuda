<?php

namespace App\Models;

use CodeIgniter\Model;

class ViolationModel extends Model
{
    protected $table            = 'violations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['student_id', 'date', 'category', 'description', 'points', 'proof_url', 'follow_up', 'tahun_pelajaran_id'];
    protected $validationRules = [
        'student_id'  => 'required|integer',
        'date'        => 'required|valid_date[Y-m-d]',
        'category'    => 'required|max_length[100]',
        'points'      => 'required|integer|greater_than[0]',
        'description' => 'permit_empty|max_length[500]',
    ];
}
