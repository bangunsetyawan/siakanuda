<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Buku Panduan - SIAKANUDA</title>
    <meta name="theme-color" content="#0e7a52">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="<?= base_url('/logo-smk.png') ?>">
    <style>
        :root {
            --primary: #0e7a52;
            --surface: #ffffff;
            --background: #f4f6f9;
        }
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; 
            background-color: var(--background); 
            padding-top: 60px; /* Space for fixed header */
            padding-bottom: 30px;
        }
        
        /* App Header */
        .app-header {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 60px;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            padding: 0 16px;
            z-index: 1030;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .app-header .btn-back {
            color: white;
            font-size: 1.2rem;
            text-decoration: none;
            padding: 8px;
            margin-right: 12px;
        }
        .app-header .title {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }

        /* Branding Banner */
        .branding-banner {
            text-align: center;
            padding: 24px 16px;
            background: white;
            margin-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .branding-banner img {
            width: 64px; height: 64px;
            margin-bottom: 12px;
        }
        .branding-banner h1 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .branding-banner p {
            font-size: 0.85rem;
            color: #64748b;
            margin: 0;
        }

        /* Mobile Accordion Menu */
        .guide-menu {
            background: white;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
        }
        .guide-item {
            border-bottom: 1px solid #f1f5f9;
        }
        .guide-item:last-child {
            border-bottom: none;
        }
        .guide-header {
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #1e293b;
            font-weight: 600;
            text-decoration: none;
            background: transparent;
            border: none;
            width: 100%;
            text-align: left;
        }
        .guide-header:focus { outline: none; background-color: #f8fafc; }
        .guide-header .icon-left {
            width: 32px;
            color: var(--primary);
            font-size: 1.2rem;
        }
        .guide-header .title-text { flex-grow: 1; }
        .guide-header .icon-right {
            color: #94a3b8;
            font-size: 0.9rem;
            transition: transform 0.3s ease;
        }
        .guide-header[aria-expanded="true"] .icon-right {
            transform: rotate(90deg);
        }

        /* Content Area */
        .guide-content {
            padding: 16px;
            background-color: #f8fafc;
            font-size: 0.9rem;
            color: #334155;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: inset 0 3px 6px -3px rgba(0,0,0,0.05);
        }
        .guide-content h5 {
            font-size: 1rem;
            font-weight: 600;
            color: #0f172a;
            margin-top: 16px;
            margin-bottom: 8px;
        }
        .guide-content h5:first-child { margin-top: 0; }
        
        .screenshot-placeholder { 
            background-color: #e2e8f0; 
            border: 2px dashed #cbd5e1; 
            border-radius: 8px; 
            padding: 30px 16px; 
            text-align: center; 
            color: #64748b; 
            margin: 16px 0;
            font-size: 0.8rem;
        }
        .screenshot-placeholder i { font-size: 2rem; margin-bottom: 8px; color: #94a3b8; }
        
        /* Helper blocks */
        .info-box {
            background: #e0f2fe;
            border-left: 4px solid #0284c7;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 16px;
            font-size: 0.85rem;
        }
        .step-list { padding-left: 20px; margin-bottom: 16px; }
        .step-list li { margin-bottom: 8px; }
    </style>
</head>
<body>

    <!-- App Header (Native App Style) -->
    <div class="app-header">
        <a href="<?= base_url('/login') ?>" class="btn-back"><i class="fas fa-arrow-left"></i></a>
        <h1 class="title">Buku Panduan</h1>
    </div>

    <!-- Branding Banner -->
    <div class="branding-banner">
        <img src="<?= base_url('/logo-smk.png') ?>" alt="Logo SMK">
        <h1>SIAKANUDA</h1>
        <p>Sistem Informasi Akademik dan Kesiswaan<br>SMK NU Darussalam</p>
    </div>

    <!-- Mobile Accordion List -->
    <div class="guide-menu" id="guideAccordion">
        
        <!-- Pengenalan -->
        <div class="guide-item">
            <button class="guide-header" type="button" data-bs-toggle="collapse" data-bs-target="#collapseIntro" aria-expanded="true">
                <div class="icon-left"><i class="fas fa-info-circle"></i></div>
                <div class="title-text">Pengenalan SIAKANUDA</div>
                <div class="icon-right"><i class="fas fa-chevron-right"></i></div>
            </button>
            <div id="collapseIntro" class="collapse show" data-bs-parent="#guideAccordion">
                <div class="guide-content">
                    <p><strong>SIAKANUDA</strong> (Sistem Informasi Akademik dan Kesiswaan SMK NU Darussalam) mempermudah proses pemantauan dan pelaporan harian Praktik Kerja Lapangan (PKL) serta berbagai informasi akademik lainnya.</p>
                    <div class="info-box">
                        <strong><i class="fas fa-mobile-alt me-1"></i> Akses Kapan Saja</strong><br>
                        Isi absensi dan jurnal harian langsung dari HP Anda kapan pun dan di mana pun. Laporan akan direkap otomatis ke dalam PDF yang siap dicetak.
                    </div>
                </div>
            </div>
        </div>

        <!-- Cara Login -->
        <div class="guide-item">
            <button class="guide-header collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLogin" aria-expanded="false">
                <div class="icon-left"><i class="fas fa-sign-in-alt"></i></div>
                <div class="title-text">Cara Mendapatkan Akun & Login</div>
                <div class="icon-right"><i class="fas fa-chevron-right"></i></div>
            </button>
            <div id="collapseLogin" class="collapse" data-bs-parent="#guideAccordion">
                <div class="guide-content">
                    <h5>1. Dapatkan Akun</h5>
                    <p>Setiap siswa dan guru akan diberikan <strong>Username</strong> (NISN) dan <strong>Password</strong> oleh pihak admin sekolah.</p>
                    
                    <h5>2. Akses Aplikasi</h5>
                    <p>Buka browser (Google Chrome/Safari) di HP Anda, lalu kunjungi alamat web SIAKANUDA.</p>
                    
                    <h5>3. Masuk ke Sistem</h5>
                    <p>Masukkan username dan password Anda, lalu tekan tombol <strong>Login Masuk</strong>.</p>
                    
                    <div class="screenshot-placeholder">
                        <i class="fas fa-image d-block"></i>
                        <span>[ TEMPAT SCREENSHOT HALAMAN LOGIN ]</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panduan Siswa -->
        <div class="guide-item">
            <button class="guide-header collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSiswa" aria-expanded="false">
                <div class="icon-left"><i class="fas fa-user-graduate"></i></div>
                <div class="title-text">Panduan Khusus Siswa (PKL)</div>
                <div class="icon-right"><i class="fas fa-chevron-right"></i></div>
            </button>
            <div id="collapseSiswa" class="collapse" data-bs-parent="#guideAccordion">
                <div class="guide-content">
                    <div class="info-box">
                        <i class="fas fa-exclamation-triangle text-warning me-1"></i> <strong>Aturan Penting:</strong> 
                        <ul class="mb-0 mt-1 ps-3">
                            <li>Hanya <strong>Ketua Kelompok</strong> yang bertugas mengisi Absensi dan Jurnal. Anggota hanya bisa melihat riwayat.</li>
                            <li>Foto dokumentasi wajib berorientasi <strong>Landscape (Mendatar)</strong>, tidak boleh Portrait.</li>
                            <li>Foto harus menampilkan tanggal/waktu (bisa menggunakan aplikasi Timestamp Camera).</li>
                        </ul>
                    </div>
                    
                    <h5>Cara Mengisi Absensi & Foto Harian</h5>
                    <ul class="step-list">
                        <li>Buka menu <strong>Isi Absen & Jurnal PKL</strong>.</li>
                        <li>Pastikan tanggal laporan sudah benar.</li>
                        <li>Pilih status kehadiran anggota (Hadir/Sakit/Izin/Alpha).</li>
                        <li><strong class="text-danger">Catatan:</strong> Jika anggota berstatus Sakit, Izin, atau Alpha, Anda <strong>wajib melampirkan foto bukti</strong>. Misalnya foto Surat Keterangan Dokter, atau foto lokasi/acara keluarga jika izin.</li>
                        <li>Klik <strong>Pilih File</strong> untuk upload foto kegiatan/bukti.</li>
                    </ul>
                    
                    <div class="screenshot-placeholder">
                        <i class="fas fa-image d-block"></i>
                        <span>[ TEMPAT SCREENSHOT FORM ABSENSI ]</span>
                    </div>

                    <h5>Cara Mengisi Jurnal Harian</h5>
                    <p>Di bagian bawah halaman yang sama, ketik detail kegiatan hari ini. Jika sudah, klik tombol <strong>Kirim Laporan</strong>.</p>

                    <hr class="my-4" style="border-color: #cbd5e1;">

                    <h5>🖨️ Cara Cetak Laporan (Save as PDF) dari HP</h5>
                    <p>Siswa dapat menyimpan laporan PKL menjadi PDF langsung dari HP Anda tanpa menggunakan komputer.</p>
                    <ul class="step-list">
                        <li>Buka menu Laporan/Riwayat yang ingin dicetak, lalu klik tombol <strong>Cetak</strong>.</li>
                        <li>Saat tampilan tabel laporan terbuka di tab baru, tekan ikon menu browser (titik tiga di pojok kanan atas untuk Chrome/Android).</li>
                        <li>Pilih menu <strong>Bagikan (Share)</strong>, lalu geser ikon ke kanan dan pilih <strong>Cetak (Print)</strong>.</li>
                        <li>Pada pilihan Printer di bagian paling atas layar, ubah menjadi <strong>Simpan sebagai PDF (Save as PDF)</strong>.</li>
                        <li>Klik ikon bulat kuning/biru bertanda panah bawah (unduh PDF) untuk menyimpannya ke memori HP.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Panduan Guru -->
        <div class="guide-item">
            <button class="guide-header collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuru" aria-expanded="false">
                <div class="icon-left"><i class="fas fa-chalkboard-teacher"></i></div>
                <div class="title-text">Panduan Guru Pembimbing</div>
                <div class="icon-right"><i class="fas fa-chevron-right"></i></div>
            </button>
            <div id="collapseGuru" class="collapse" data-bs-parent="#guideAccordion">
                <div class="guide-content">
                    <h5>Mengecek Riwayat Siswa</h5>
                    <ul class="step-list">
                        <li>Login dengan akun Guru.</li>
                        <li>Buka menu <strong>Laporan Harian PKL</strong>.</li>
                        <li>Pilih kelompok PKL yang dibimbing.</li>
                    </ul>

                    <div class="screenshot-placeholder">
                        <i class="fas fa-image d-block"></i>
                        <span>[ TEMPAT SCREENSHOT DAFTAR KELOMPOK GURU ]</span>
                    </div>

                    <h5>Cetak Rekap PDF</h5>
                    <p>Klik tombol <strong>Riwayat</strong> pada sebuah kelompok, lalu klik ikon 🖨️ <strong>Cetak Rekap Absensi Individu</strong> di sebelah nama siswa.</p>
                </div>
            </div>
        </div>

        <!-- FAQ -->
        <div class="guide-item">
            <button class="guide-header collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFaq" aria-expanded="false">
                <div class="icon-left"><i class="fas fa-question-circle"></i></div>
                <div class="title-text">Kendala & FAQ</div>
                <div class="icon-right"><i class="fas fa-chevron-right"></i></div>
            </button>
            <div id="collapseFaq" class="collapse" data-bs-parent="#guideAccordion">
                <div class="guide-content">
                    <h5>Lupa Password?</h5>
                    <p>Silakan hubungi Admin / Operator TU sekolah untuk melakukan reset password ke bawaan (*default*).</p>
                    
                    <h5>Gagal Upload Foto?</h5>
                    <p>Pastikan ukuran foto tidak melebihi batas (maksimal 5MB). Format yang diterima adalah JPG dan PNG.</p>

                    <h5>Menu PKL Kosong?</h5>
                    <p>Admin belum memasukkan nama Anda ke kelompok PKL manapun. Segera lapor ke Admin.</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
