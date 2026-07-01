<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ImportSql extends BaseCommand
{
    protected $group       = 'BKK';
    protected $name        = 'bkk:import_sql';
    protected $description = 'Import file SQL ke SQLite database';
    protected $usage       = 'bkk:import_sql [file_path]';
    protected $arguments   = [
        'file_path' => 'Path ke file SQL yang akan diimport (contoh: ../alumni_data.sql)'
    ];

    public function run(array $params)
    {
        $filePath = array_shift($params);

        if (empty($filePath)) {
            CLI::write('Harap masukkan path file SQL yang ingin diimport.', 'red');
            CLI::write('Contoh: php spark bkk:import_sql ../alumni_data.sql', 'yellow');
            return;
        }

        if (!file_exists($filePath)) {
            CLI::write("File tidak ditemukan: $filePath", 'red');
            return;
        }

        $sql = file_get_contents($filePath);
        if (empty($sql)) {
            CLI::write("File SQL kosong.", 'red');
            return;
        }

        CLI::write("Memulai import data SQL...", 'yellow');

        $db = \Config\Database::connect();
        
        try {
            // Because PDO SQLite doesn't always support multiple statements in query(),
            // we will try executing them normally or split by semicolon if needed.
            // Since our python script made it `DELETE FROM alumni; INSERT INTO...;`
            
            // Temporary disable foreign keys if needed
            $db->query("PRAGMA foreign_keys = OFF;");
            
            $statements = explode(";\n", $sql);
            $count = 0;
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $db->query($statement);
                    $count++;
                }
            }
            
            $db->query("PRAGMA foreign_keys = ON;");
            
            CLI::write("Berhasil mengeksekusi $count perintah SQL ke database SQLite.", 'green');
        } catch (\Exception $e) {
            CLI::write("Terjadi kesalahan saat mengeksekusi SQL: " . $e->getMessage(), 'red');
        }
    }
}
