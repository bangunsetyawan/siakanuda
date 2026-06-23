<?php

namespace App\Models;

use CodeIgniter\Model;

class BkkKunjunganModel extends Model
{
    protected $table            = 'kunjungan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'tempat', 'alamat_cp', 'waktu', 'jumlah_peserta', 'dokumentasi'
    ];

    

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}

