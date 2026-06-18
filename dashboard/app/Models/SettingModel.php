<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table = 'system_settings';
    protected $primaryKey = 'key';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $allowedFields = ['key', 'value', 'updated_at'];

    public function getSetting($key, $default = null)
    {
        $setting = $this->find($key);
        return $setting ? $setting['value'] : $default;
    }

    public function setSetting($key, $value)
    {
        $existing = $this->find($key);
        if ($existing) {
            return $this->update($key, [
                'value' => $value,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            return $this->insert([
                'key' => $key,
                'value' => $value,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
}
