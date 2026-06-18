<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class BackupDb extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:backup';
    protected $description = 'Backup SQLite database ke folder backups/';
    protected $usage       = 'db:backup [--clean]';
    protected $options     = [
        '--clean' => 'Hapus backup yang lebih dari 7 hari',
    ];

    public function run(array $params)
    {
        $dbPath = realpath(ROOTPATH . '../../siakanuda.db');
        if (!$dbPath || !file_exists($dbPath)) {
            CLI::error('Database tidak ditemukan: ' . ROOTPATH . '../../siakanuda.db');
            return;
        }

        $backupDir = realpath(ROOTPATH . '../..') . DIRECTORY_SEPARATOR . 'backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
            CLI::write('Folder backups/ dibuat.', 'yellow');
        }

        // Run integrity check
        CLI::write('Menjalankan integrity check...', 'light_gray');
        $db = new \SQLite3($dbPath, SQLITE3_OPEN_READONLY);
        $result = $db->querySingle("PRAGMA integrity_check");
        if ($result !== 'ok') {
            CLI::error('Database integrity check GAGAL: ' . $result);
            $db->close();
            return;
        }
        CLI::write('  ✓ Integrity check: OK', 'green');

        // Checkpoint WAL to merge pending writes
        $dbRW = new \SQLite3($dbPath);
        $dbRW->exec('PRAGMA wal_checkpoint(TRUNCATE)');
        $dbRW->close();
        CLI::write('  ✓ WAL checkpoint: OK', 'green');
        $db->close();

        // Create backup copy
        $timestamp = date('Ymd_His');
        $backupFile = $backupDir . DIRECTORY_SEPARATOR . "siakanuda_{$timestamp}.db";

        if (!copy($dbPath, $backupFile)) {
            CLI::error('Gagal membuat backup!');
            return;
        }

        $size = round(filesize($backupFile) / 1024, 1);
        CLI::write("  ✓ Backup berhasil: siakanuda_{$timestamp}.db ({$size} KB)", 'green');

        // Clean old backups if --clean flag
        if (CLI::getOption('clean')) {
            $this->cleanOldBackups($backupDir);
        }

        CLI::write('Backup selesai.', 'green');
    }

    private function cleanOldBackups(string $backupDir)
    {
        $files = glob($backupDir . DIRECTORY_SEPARATOR . 'siakanuda_*.db');
        $cutoff = strtotime('-7 days');
        $deleted = 0;

        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            CLI::write("  ✓ {$deleted} backup lama dihapus (> 7 hari).", 'yellow');
        } else {
            CLI::write('  ✓ Tidak ada backup lama untuk dihapus.', 'light_gray');
        }
    }
}
