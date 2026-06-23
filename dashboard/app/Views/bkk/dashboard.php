<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Dashboard BKK & Tracer Study</h1>
            <p class="text-secondary mb-0">Pusat Informasi Kerja & Pelayanan Alumni SMK NU Darussalam.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary-soft">
            <div class="inner">
                <h3><?= $stats['total'] ?></h3>
                <p>Total Alumni</p>
            </div>
            <div class="icon">
                <i class="fas fa-users text-primary"></i>
            </div>
            <a href="<?= base_url('bkk/data_alumni') ?>" class="small-box-footer text-primary">Selengkapnya <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success-soft">
            <div class="inner">
                <h3><?= $stats['bekerja'] ?></h3>
                <p>Bekerja</p>
            </div>
            <div class="icon">
                <i class="fas fa-briefcase text-success"></i>
            </div>
            <a href="<?= base_url('bkk/data_alumni') ?>" class="small-box-footer text-success">Selengkapnya <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-pink-soft">
            <div class="inner">
                <h3><?= $stats['studi'] ?></h3>
                <p>Studi Lanjut / Kuliah</p>
            </div>
            <div class="icon">
                <i class="fas fa-university text-pink"></i>
            </div>
            <a href="<?= base_url('bkk/data_alumni') ?>" class="small-box-footer text-pink">Selengkapnya <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning-soft">
            <div class="inner">
                <h3><?= $stats['wirausaha'] ?></h3>
                <p>Wirausaha</p>
            </div>
            <div class="icon">
                <i class="fas fa-store text-warning"></i>
            </div>
            <a href="<?= base_url('bkk/data_alumni') ?>" class="small-box-footer text-warning">Selengkapnya <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-chart-pie mr-2 text-primary"></i> Distribusi Status Alumni</h3>
            </div>
            <div class="card-body">
                <canvas id="statusChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-info-circle mr-2 text-info"></i> Informasi Layanan</h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled text-secondary">
                    <li class="mb-3"><i class="fas fa-check-circle text-success mr-2"></i> Informasi Lowongan Kerja</li>
                    <li class="mb-3"><i class="fas fa-check-circle text-success mr-2"></i> Rekrutmen & Penempatan Kerja</li>
                    <li class="mb-3"><i class="fas fa-check-circle text-success mr-2"></i> Bimbingan Karier & Minat Bakat</li>
                    <li class="mb-3"><i class="fas fa-check-circle text-success mr-2"></i> Pengembangan Kewirausahaan</li>
                </ul>
                <div class="mt-4 p-3 bg-light rounded border-left border-info" style="border-left-width: 4px !important;">
                    <strong>Jam Layanan:</strong> 08.00 - 11.00 WIB<br>
                    <strong>Call Center:</strong> 0838-3028-3379 / 0853-3435-4102
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    var ctx = document.getElementById('statusChart').getContext('2d');
    var statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Bekerja', 'Studi Lanjut', 'Wirausaha', 'Mencari Kerja'],
            datasets: [{
                data: [
                    <?= $stats['bekerja'] ?>, 
                    <?= $stats['studi'] ?>, 
                    <?= $stats['wirausaha'] ?>, 
                    <?= $stats['mencari'] ?>
                ],
                backgroundColor: ['#28a745', '#e83e8c', '#ffc107', '#dc3545'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
});
</script>
<?= $this->endSection() ?>
