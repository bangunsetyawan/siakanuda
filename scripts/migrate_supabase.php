<?php
// Script Migrasi Data Supabase ke SQLite Lokal
// Oleh: Antigravity

$supabaseUrl = 'https://ilnfzebpoczlwocpzlop.supabase.co';
$supabaseKey = 'sb_publishable_75Y_xJl_9ntQt3R2TnkSUw_pktf_FbV';
$dbFile = 'siakanuda.db';

if (!file_exists($dbFile)) {
    die("File SQLite (siakanuda.db) tidak ditemukan! Pastikan Anda menjalankan script ini dari folder ~/siakanuda\n");
}

try {
    $db = new PDO("sqlite:" . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Gagal koneksi ke SQLite: " . $e->getMessage() . "\n");
}

function fetchSupabaseData($table, $url, $key) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$url/rest/v1/$table?select=*");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $key",
        "Authorization: Bearer $key"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

echo "Mulai migrasi data dari Supabase ke SQLite...\n\n";

// 1. MIGRASI ALUMNI
echo "[1/3] Mengunduh data tabel 'alumni'...\n";
$alumniData = fetchSupabaseData('alumni', $supabaseUrl, $supabaseKey);
if (is_array($alumniData) && count($alumniData) > 0) {
    $db->exec("DELETE FROM alumni"); // Bersihkan dulu
    $stmt = $db->prepare("INSERT INTO alumni (id, created_at, nama_lengkap, nisn, nik, jenis_kelamin, tempat_lahir, tanggal_lahir, agama, kompetensi_keahlian, tahun_lulus, alamat, kota_kab, no_hp_wa, email_aktif, status_utama, nama_tempat_kerja, jabatan_pekerjaan, sektor_usaha_bidang, lokasi_kerja_kota, keselarasan_jurusan, rentang_pendapatan, keterangan_tambahan) VALUES (:id, :created_at, :nama_lengkap, :nisn, :nik, :jenis_kelamin, :tempat_lahir, :tanggal_lahir, :agama, :kompetensi_keahlian, :tahun_lulus, :alamat, :kota_kab, :no_hp_wa, :email_aktif, :status_utama, :nama_tempat_kerja, :jabatan_pekerjaan, :sektor_usaha_bidang, :lokasi_kerja_kota, :keselarasan_jurusan, :rentang_pendapatan, :keterangan_tambahan)");
    
    foreach ($alumniData as $row) {
        $stmt->execute([
            ':id' => $row['id'] ?? null,
            ':created_at' => $row['created_at'] ?? null,
            ':nama_lengkap' => $row['nama_lengkap'] ?? null,
            ':nisn' => $row['nisn'] ?? null,
            ':nik' => $row['nik'] ?? null,
            ':jenis_kelamin' => $row['jenis_kelamin'] ?? null,
            ':tempat_lahir' => $row['tempat_lahir'] ?? null,
            ':tanggal_lahir' => $row['tanggal_lahir'] ?? null,
            ':agama' => $row['agama'] ?? null,
            ':kompetensi_keahlian' => $row['kompetensi_keahlian'] ?? null,
            ':tahun_lulus' => $row['tahun_lulus'] ?? null,
            ':alamat' => $row['alamat'] ?? null,
            ':kota_kab' => $row['kota_kab'] ?? null,
            ':no_hp_wa' => $row['no_hp_wa'] ?? null,
            ':email_aktif' => $row['email_aktif'] ?? null,
            ':status_utama' => $row['status_utama'] ?? null,
            ':nama_tempat_kerja' => $row['nama_tempat_kerja'] ?? null,
            ':jabatan_pekerjaan' => $row['jabatan_pekerjaan'] ?? null,
            ':sektor_usaha_bidang' => $row['sektor_usaha_bidang'] ?? null,
            ':lokasi_kerja_kota' => $row['lokasi_kerja_kota'] ?? null,
            ':keselarasan_jurusan' => $row['keselarasan_jurusan'] ?? null,
            ':rentang_pendapatan' => $row['rentang_pendapatan'] ?? null,
            ':keterangan_tambahan' => $row['keterangan_tambahan'] ?? null
        ]);
    }
    echo "  -> Berhasil memasukkan " . count($alumniData) . " baris data Alumni.\n";
} else {
    echo "  -> Data Alumni kosong atau gagal diunduh.\n";
}

// 2. MIGRASI MOU
echo "\n[2/3] Mengunduh data tabel 'mou'...\n";
$mouData = fetchSupabaseData('mou', $supabaseUrl, $supabaseKey);
if (is_array($mouData) && count($mouData) > 0) {
    $db->exec("DELETE FROM mou");
    $stmt = $db->prepare("INSERT INTO mou (id, created_at, mou_no, instansi, alamat, bidang, konsentrasi, kerjasama) VALUES (:id, :created_at, :mou_no, :instansi, :alamat, :bidang, :konsentrasi, :kerjasama)");
    
    foreach ($mouData as $row) {
        $stmt->execute([
            ':id' => $row['id'] ?? null,
            ':created_at' => $row['created_at'] ?? null,
            ':mou_no' => $row['mou_no'] ?? null,
            ':instansi' => $row['instansi'] ?? null,
            ':alamat' => $row['alamat'] ?? null,
            ':bidang' => $row['bidang'] ?? null,
            ':konsentrasi' => $row['konsentrasi'] ?? null,
            ':kerjasama' => $row['kerjasama'] ?? null
        ]);
    }
    echo "  -> Berhasil memasukkan " . count($mouData) . " baris data MOU.\n";
} else {
    echo "  -> Data MOU kosong atau gagal diunduh.\n";
}

// 3. MIGRASI KUNJUNGAN
echo "\n[3/3] Mengunduh data tabel 'kunjungan'...\n";
$kunjData = fetchSupabaseData('kunjungan', $supabaseUrl, $supabaseKey);
if (is_array($kunjData) && count($kunjData) > 0) {
    $db->exec("DELETE FROM kunjungan");
    $stmt = $db->prepare("INSERT INTO kunjungan (id, created_at, tempat, alamat_cp, waktu, jumlah_peserta, dokumentasi) VALUES (:id, :created_at, :tempat, :alamat_cp, :waktu, :jumlah_peserta, :dokumentasi)");
    
    foreach ($kunjData as $row) {
        $stmt->execute([
            ':id' => $row['id'] ?? null,
            ':created_at' => $row['created_at'] ?? null,
            ':tempat' => $row['tempat'] ?? null,
            ':alamat_cp' => $row['alamat_cp'] ?? null,
            ':waktu' => $row['waktu'] ?? null,
            ':jumlah_peserta' => $row['jumlah_peserta'] ?? null,
            ':dokumentasi' => $row['dokumentasi'] ?? null
        ]);
    }
    echo "  -> Berhasil memasukkan " . count($kunjData) . " baris data Kunjungan.\n";
} else {
    echo "  -> Data Kunjungan kosong atau gagal diunduh.\n";
}

echo "\nSelesai! Migrasi berhasil. Silakan cek website Anda.\n";
