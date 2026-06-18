<?php

namespace App\Models;

use CodeIgniter\Model;

class FeedbackModel extends Model
{
    protected $table            = 'feedbacks';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['date', 'sender_name', 'class_name', 'message', 'is_public', 'tahun_pelajaran_id'];
    protected $validationRules = [
        'sender_name' => 'required|max_length[100]',
        'class_name'  => 'required|max_length[50]',
        'message'     => 'required|min_length[10]|max_length[2000]',
    ];
}
