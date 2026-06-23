<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid">
        <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Mitra Industri / IDUKA</h1>
        <p class="text-secondary mb-0">Daftar perusahaan dan instansi mitra SMK NU Darussalam.</p>
    </div>
</div>

<div class="row">
    <?php if(empty($mou)): ?>
        <div class="col-12 text-center text-secondary py-5">
            <i class="fas fa-building fa-3x mb-3 text-muted"></i>
            <p>Belum ada mitra industri yang terdaftar.</p>
        </div>
    <?php endif; ?>

    <?php foreach ($mou as $row) : ?>
    <div class="col-md-4">
        <div class="card mb-4 shadow-sm border-0" style="border-radius: 10px;">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-building text-primary" style="font-size: 3rem;"></i>
                </div>
                <h5 class="font-weight-bold text-dark mb-1"><?= esc($row['instansi'] ?? '') ?></h5>
                <p class="text-secondary small mb-2"><i class="fas fa-map-marker-alt mr-1"></i> <?= esc($row['alamat'] ?? '') ?></p>
                <hr>
                <div class="d-flex justify-content-between text-left small">
                    <div>
                        <span class="text-muted d-block">Bidang:</span>
                        <strong class="text-dark"><?= esc(($row['bidang'] ?? '') ?: '-') ?></strong>
                    </div>
                    <div class="text-right">
                        <span class="text-muted d-block">MOU:</span>
                        <span class="badge badge-info"><?= esc($row['mou_no'] ?? '') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?= $this->endSection() ?>
