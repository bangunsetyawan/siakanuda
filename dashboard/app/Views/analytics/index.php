<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-12">
        <h2 class="font-weight-bold text-dark"><i class="fas fa-chart-pie text-info mr-2"></i> Dashboard Analytics</h2>
        <p class="text-muted">Statistik dan tren data aktivitas sekolah.</p>
    </div>
</div>

<div class="row">
    <!-- Tren Absensi KBM -->
    <div class="col-lg-8">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Tren Kehadiran KBM (6 Bulan Terakhir)</h3>
            </div>
            <div class="card-body">
                <canvas id="kbmAttendanceChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>

    <!-- Ketidakhadiran KBM Pie Chart -->
    <div class="col-lg-4">
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Ketidakhadiran KBM</h3>
            </div>
            <div class="card-body">
                <canvas id="kbmAbsenPieChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mt-2">
    <!-- Tren Absensi PKL -->
    <div class="col-lg-8">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Tren Kehadiran PKL (6 Bulan Terakhir)</h3>
            </div>
            <div class="card-body">
                <canvas id="pklAttendanceChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>

    <!-- Ketidakhadiran PKL Pie Chart -->
    <div class="col-lg-4">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Ketidakhadiran PKL</h3>
            </div>
            <div class="card-body">
                <canvas id="pklAbsenPieChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Tren Harian Laporan PKL -->
    <div class="col-lg-12">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Tren Harian Laporan PKL Masuk (14 Hari Terakhir)</h3>
            </div>
            <div class="card-body">
                <canvas id="dailyPklChart" style="min-height: 350px; height: 350px; max-height: 350px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. KBM Line Chart
    const ctxKbmAtt = document.getElementById('kbmAttendanceChart').getContext('2d');
    new Chart(ctxKbmAtt, {
        type: 'line',
        data: {
            labels: <?= $monthLabels ?>,
            datasets: [
                {
                    label: 'Siswa KBM Hadir',
                    data: <?= $hadirKbmMonth ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.15)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#10b981',
                    pointRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, suggestedMax: 10 } }
        }
    });

    // 2. PKL Line Chart
    const ctxPklAtt = document.getElementById('pklAttendanceChart').getContext('2d');
    new Chart(ctxPklAtt, {
        type: 'line',
        data: {
            labels: <?= $monthLabels ?>,
            datasets: [
                {
                    label: 'Siswa PKL Hadir',
                    data: <?= $hadirPklMonth ?>,
                    borderColor: '#0284c7',
                    backgroundColor: 'rgba(2, 132, 199, 0.15)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#0284c7',
                    pointRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, suggestedMax: 10 } }
        }
    });

    // 3. KBM Ketidakhadiran Pie Chart
    const ctxKbmAbsen = document.getElementById('kbmAbsenPieChart').getContext('2d');
    new Chart(ctxKbmAbsen, {
        type: 'doughnut',
        data: {
            labels: ['Sakit', 'Izin', 'Alpha'],
            datasets: [{
                data: <?= $kbmAbsenData ?>,
                backgroundColor: ['#f59e0b', '#3b82f6', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // 4. PKL Ketidakhadiran Pie Chart
    const ctxPklAbsen = document.getElementById('pklAbsenPieChart').getContext('2d');
    new Chart(ctxPklAbsen, {
        type: 'doughnut',
        data: {
            labels: ['Sakit', 'Izin', 'Alpha'],
            datasets: [{
                data: <?= $pklAbsenData ?>,
                backgroundColor: ['#f59e0b', '#3b82f6', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // 5. Tren Harian PKL Bar Chart
    const ctxDailyPkl = document.getElementById('dailyPklChart').getContext('2d');
    new Chart(ctxDailyPkl, {
        type: 'bar',
        data: {
            labels: <?= $dailyPklLabels ?>,
            datasets: [
                {
                    label: 'Total Kelompok Lapor',
                    data: <?= $dailyPklData ?>,
                    backgroundColor: 'rgba(14, 165, 233, 0.85)',
                    borderColor: '#0284c7',
                    borderWidth: 1,
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, suggestedMax: 5 } },
            plugins: { legend: { display: false } }
        }
    });
});
</script>
<?= $this->endSection() ?>
