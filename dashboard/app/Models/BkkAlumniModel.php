<?php

namespace App\Models;

use CodeIgniter\Model;

class BkkAlumniModel extends Model
{
    protected $table            = 'alumni';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama_lengkap', 'nisn', 'nik', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 
        'agama', 'kompetensi_keahlian', 'tahun_lulus', 'alamat', 'kota_kab', 'no_hp_wa', 
        'email_aktif', 'status_utama', 'nama_tempat_kerja', 'jabatan_pekerjaan', 
        'sektor_usaha_bidang', 'lokasi_kerja_kota', 'keselarasan_jurusan', 'rentang_pendapatan', 
        'keterangan_tambahan'
    ];

    

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}

