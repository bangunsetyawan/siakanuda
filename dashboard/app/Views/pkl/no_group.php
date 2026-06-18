<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="card p-5 text-center shadow-sm border-0 rounded-lg bg-white mb-4">
    <div class="card-body">
        <div class="text-danger mb-4" style="font-size: 50px;">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h4 class="font-weight-bold text-dark mb-2"><?= $title ?></h4>
        <p class="text-secondary"><?= $message ?></p>
        <a href="<?= base_url('/dashboard') ?>" class="btn btn-primary mt-3 px-4">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Dashboard
        </a>
    </div>
</div>
<?= $this->endSection() ?>
