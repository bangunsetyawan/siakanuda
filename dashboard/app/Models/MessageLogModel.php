<?php

namespace App\Models;

use CodeIgniter\Model;

class MessageLogModel extends Model
{
    protected $table            = 'message_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['phone', 'direction', 'message', 'ai_action', 'status', 'created_at'];
}
