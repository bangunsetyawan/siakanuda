<?php

namespace App\Models;

use CodeIgniter\Model;

class AchievementModel extends Model
{
    protected $table            = 'achievements';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['student_id', 'date', 'category', 'title', 'description', 'tahun_pelajaran_id'];
    protected $validationRules = [
        'student_id'  => 'required|integer',
        'date'        => 'required|valid_date[Y-m-d]',
        'category'    => 'required|in_list[Akademik,Non-Akademik,Lomba,Ekstrakurikuler]',
        'title'       => 'required|min_length[3]|max_length[200]',
        'description' => 'permit_empty|max_length[1000]',
    ];
}
