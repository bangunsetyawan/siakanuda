<?php

namespace App\Models;

use CodeIgniter\Model;

class BkkMouModel extends Model
{
    protected $table            = 'mou';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'mou_no', 'instansi', 'alamat', 'bidang', 'konsentrasi', 'kerjasama'
    ];

    

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}

