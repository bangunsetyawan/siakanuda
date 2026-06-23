<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Dokumen MOU IDUKA</h1>
            <p class="text-secondary mb-0">Daftar kerja sama dengan Industri, Dunia Usaha, dan Dunia Kerja.</p>
        </div>
        <div>
            <button class="btn btn-primary" data-toggle="modal" data-target="#modal-add-mou">
                <i class="fas fa-plus mr-2"></i> Tambah MOU
            </button>
        </div>
    </div>
</div>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header bg-white pt-4 pb-0 border-bottom-0">
        <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-handshake mr-2 text-primary"></i> Daftar Dokumen MOU</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="mouTable" class="table table-bordered table-striped table-hover">
                <thead class="bg-light">
                    <tr>
                        <th width="5%">No</th>
                        <th>No MOU</th>
                        <th>IDUKA / Instansi</th>
                        <th>Alamat</th>
                        <th>Bidang Kerjasama</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($mou as $row) : ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><span class="badge badge-info"><?= esc($row['mou_no'] ?? '') ?></span></td>
                        <td><strong><?= esc($row['instansi'] ?? '') ?></strong></td>
                        <td><?= esc($row['alamat'] ?? '') ?></td>
                        <td><?= esc(($row['bidang'] ?? '') ?: ($row['kerjasama'] ?? '')) ?></td>
                        <td>
                            <button class="btn btn-sm btn-info btn-edit" 
                                data-id="<?= $row['id'] ?>"
                                data-nomou="<?= esc($row['mou_no'] ?? '') ?>"
                                data-instansi="<?= esc($row['instansi'] ?? '') ?>"
                                data-alamat="<?= esc($row['alamat'] ?? '') ?>"
                                data-bidang="<?= esc($row['bidang'] ?? '') ?>"
                                data-bentuk="<?= esc($row['kerjasama'] ?? '') ?>"
                                data-toggle="modal" data-target="#modal-add-mou">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-delete" 
                                data-id="<?= $row['id'] ?>"
                                data-toggle="modal" data-target="#modal-delete-mou">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Add/Edit MOU -->
<div class="modal fade" id="modal-add-mou" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="<?= base_url('bkk/store_mou') ?>" method="post">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title font-weight-bold" id="mouModalLabel"><i class="fas fa-handshake mr-2"></i> Form MOU IDUKA</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <input type="hidden" name="id" id="mou_id">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Nomor MOU <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="mou_no" id="mou_no" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Nama IDUKA / Instansi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="instansi" id="instansi" required>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Alamat IDUKA</label>
                            <textarea class="form-control" name="alamat" id="alamat" rows="2"></textarea>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Bidang Kerja Sama</label>
                            <input type="text" class="form-control" name="bidang" id="bidang" placeholder="Misal: Otomotif, IT">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Bentuk Kerja Sama</label>
                            <input type="text" class="form-control" name="kerjasama" id="kerjasama" placeholder="Misal: Rekrutmen, PKL">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan MOU</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Delete -->
<div class="modal fade" id="modal-delete-mou" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="<?= base_url('bkk/delete_mou') ?>" method="post">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i> Hapus MOU</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 text-center">
                    <input type="hidden" name="id" id="delete_mou_id">
                    <i class="fas fa-trash-alt text-danger mb-3" style="font-size: 3rem;"></i>
                    <p class="mb-0" style="font-size: 1.1rem;">Apakah Anda yakin ingin menghapus data MOU ini?</p>
                </div>
                <div class="modal-footer bg-light border-0 justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger px-4"><i class="fas fa-trash mr-1"></i> Hapus</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $('#mouTable').DataTable({
        "responsive": true,
        "autoWidth": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
        }
    });

    $('.btn-edit').on('click', function() {
        $('#mouModalLabel').html('<i class="fas fa-edit mr-2"></i> Edit MOU');
        $('#mou_id').val($(this).data('id'));
        $('#no_mou').val($(this).data('nomou'));
        $('#iduka_instansi').val($(this).data('instansi'));
        $('#alamat').val($(this).data('alamat'));
        $('#bidang').val($(this).data('bidang'));
        $('#bentuk_kerjasama').val($(this).data('bentuk'));
    });

    $('#modal-add-mou').on('hidden.bs.modal', function () {
        $('#mouModalLabel').html('<i class="fas fa-handshake mr-2"></i> Tambah MOU IDUKA');
        $('#mou_id').val('');
        $(this).find('form')[0].reset();
    });

    $('.btn-delete').on('click', function() {
        $('#delete_mou_id').val($(this).data('id'));
    });
});
</script>
<?= $this->endSection() ?>
