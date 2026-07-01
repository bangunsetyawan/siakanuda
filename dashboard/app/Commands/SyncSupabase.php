<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\BkkAlumniModel;

class SyncSupabase extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'BKK';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'bkk:sync_supabase';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Migrasi data awal dari Supabase ke SQLite lokal (Tabel Alumni)';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'bkk:sync_supabase';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        CLI::write("Memulai sinkronisasi data dari Supabase...", 'yellow');

        $supabaseUrl = 'https://ilnfzebpoczlwocpzlop.supabase.co/rest/v1/alumni?select=*';
        $supabaseKey = 'sb_publishable_75Y_xJl_9ntQt3R2TnkSUw_pktf_FbV';

        $ch = curl_init($supabaseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $supabaseKey,
            'Authorization: Bearer ' . $supabaseKey,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ]);
        // Bypassing SSL verification for ease of CLI execution in some environments
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode !== 200) {
            CLI::write("Gagal terhubung ke Supabase! HTTP Code: $httpCode. Error: $error", 'light_red');
            return;
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            CLI::write("Data Supabase tidak valid atau kosong.", 'light_red');
            return;
        }

        $totalData = count($data);
        CLI::write("Berhasil mengambil $totalData baris data dari Supabase.", 'green');

        $alumniModel = new BkkAlumniModel();
        
        // Hapus data lama agar sinkronisasi ulang aman (opsional, tapi baik untuk testing)
        // $alumniModel->truncate(); // SQLite mungkin butuh raw query untuk truncate, kita biarkan saja / hapus semua
        $db = \Config\Database::connect();
        $db->table('alumni')->emptyTable();

        $inserted = 0;
        foreach ($data as $row) {
            // Map Supabase columns to SQLite columns if needed.
            // Assuming the columns are mostly identical based on BkkAlumniModel
            $insertData = [
                'nama_lengkap'         => $row['nama_lengkap'] ?? null,
                'nisn'                 => $row['nisn'] ?? null,
                'nik'                  => $row['nik'] ?? null,
                'jenis_kelamin'        => $row['jenis_kelamin'] ?? null,
                'tempat_lahir'         => $row['tempat_lahir'] ?? null,
                'tanggal_lahir'        => $row['tanggal_lahir'] ?? null,
                'agama'                => $row['agama'] ?? null,
                'kompetensi_keahlian'  => $row['kompetensi_keahlian'] ?? null,
                'tahun_lulus'          => $row['tahun_lulus'] ?? null,
                'alamat'               => $row['alamat'] ?? null,
                'kota_kab'             => $row['kota_kab'] ?? null,
                'no_hp_wa'             => $row['no_hp_wa'] ?? null,
                'email_aktif'          => $row['email_aktif'] ?? null,
                'status_utama'         => $row['status_utama'] ?? null,
                'nama_tempat_kerja'    => $row['nama_tempat_kerja'] ?? null,
                'jabatan_pekerjaan'    => $row['jabatan_pekerjaan'] ?? null,
                'sektor_usaha_bidang'  => $row['sektor_usaha_bidang'] ?? null,
                'lokasi_kerja_kota'    => $row['lokasi_kerja_kota'] ?? null,
                'keselarasan_jurusan'  => $row['keselarasan_jurusan'] ?? null,
                'rentang_pendapatan'   => $row['rentang_pendapatan'] ?? null,
                'keterangan_tambahan'  => $row['keterangan_tambahan'] ?? null,
            ];

            if ($alumniModel->insert($insertData)) {
                $inserted++;
            }
        }

        CLI::write("Sinkronisasi Selesai! Berhasil memasukkan $inserted dari $totalData data ke database SQLite lokal.", 'green');
    }
}
