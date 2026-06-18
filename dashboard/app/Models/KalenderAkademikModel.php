<?php

namespace App\Models;

use CodeIgniter\Model;

class KalenderAkademikModel extends Model
{
    protected $table            = 'kalender_akademik';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['tahun_pelajaran', 'semester', 'nomor', 'bulan', 'total_pekan', 'pekan_efektif', 'pekan_tidak_efektif', 'keterangan'];
    protected $validationRules = [
        'tahun_pelajaran' => 'required|max_length[20]',
        'semester'        => 'required|in_list[Ganjil,Genap]',
        'bulan'           => 'required|max_length[20]',
        'total_pekan'     => 'required|integer|greater_than_equal_to[0]',
        'pekan_efektif'   => 'required|integer|greater_than_equal_to[0]',
    ];
}
