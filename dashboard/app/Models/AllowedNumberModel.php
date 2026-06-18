<?php

namespace App\Models;

use CodeIgniter\Model;

class AllowedNumberModel extends Model
{
    protected $table            = 'allowed_numbers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['phone', 'name', 'role', 'lid', 'active', 'password_hash', 'first_login', 'tugas_tambahan'];
    protected $validationRules = [
        'phone' => 'required|max_length[20]',
        'name'  => 'required|min_length[2]|max_length[100]',
        'role'  => 'required|in_list[admin,kepsek,guru,guru_bk,guru_mapel]',
    ];
}
