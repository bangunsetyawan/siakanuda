<?php

namespace App\Models;

use CodeIgniter\Model;

class ScheduleModel extends Model
{
    protected $table            = 'schedules';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['day', 'class_name', 'subject', 'teacher_phone', 'start_time', 'end_time'];
    protected $validationRules = [
        'day'           => 'required|in_list[Senin,Selasa,Rabu,Kamis,Jumat,Sabtu]',
        'class_name'    => 'required|max_length[20]',
        'subject'       => 'required|max_length[100]',
        'teacher_phone' => 'required|max_length[20]',
    ];
}
