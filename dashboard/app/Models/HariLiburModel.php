<?php

namespace App\Models;

use CodeIgniter\Model;

class HariLiburModel extends Model
{
    protected $table = 'hari_libur';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['date', 'reason', 'type'];
    protected $useTimestamps = false;

    /**
     * Get hari libur by specific date
     */
    public function getByDate(string $date)
    {
        return $this->where('date', $date)->first();
    }

    /**
     * Get all upcoming holidays (date >= today)
     */
    public function getUpcoming(int $limit = 30)
    {
        $today = date('Y-m-d');
        return $this->where('date >=', $today)
                    ->orderBy('date', 'ASC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get all holidays ordered by date desc
     */
    public function getAllOrdered()
    {
        return $this->orderBy('date', 'DESC')->findAll();
    }
}
