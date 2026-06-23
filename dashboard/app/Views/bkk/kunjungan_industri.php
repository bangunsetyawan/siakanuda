<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Kunjungan Industri</h1>
            <p class="text-secondary mb-0">Kelola catatan dan jadwal kunjungan industri siswa SMK NU Darussalam.</p>
        </div>
        <div>
            <button class="btn btn-primary" data-toggle="modal" data-target="#modal-add-kunjungan">
                <i class="fas fa-plus mr-2"></i> Tambah Kunjungan
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
        <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-bus mr-2 text-primary"></i> Daftar Kunjungan Industri</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="kunjunganTable" class="table table-bordered table-striped table-hover">
                <thead class="bg-light">
                    <tr>
                        <th width="5%">No</th>
                        <th>Tanggal</th>
                        <th>Tempat Kunjungan</th>
                        <th>Jumlah Peserta</th>
                        <th>Dokumentasi / Keterangan</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($kunjungan as $row) : ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= date('d M Y', strtotime($row['waktu'] ?? date('Y-m-d'))) ?></td>
                        <td><strong><?= esc($row['tempat'] ?? '') ?></strong></td>
                        <td><?= esc($row['jumlah_peserta'] ?? '') ?> Orang</td>
                        <td><?= esc($row['dokumentasi'] ?? '') ?></td>
                        <td>
                            <button class="btn btn-sm btn-info btn-edit" 
                                data-id="<?= $row['id'] ?>"
                                data-tanggal="<?= esc($row['waktu'] ?? '') ?>"
                                data-tujuan="<?= esc($row['tempat'] ?? '') ?>"
                                data-peserta="<?= esc($row['jumlah_peserta'] ?? '') ?>"
                                data-keterangan="<?= esc($row['dokumentasi'] ?? '') ?>"
                                data-toggle="modal" data-target="#modal-add-kunjungan">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-delete" 
                                data-id="<?= $row['id'] ?>"
                                data-toggle="modal" data-target="#modal-delete-kunjungan">
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

<!-- Modal Add/Edit Kunjungan -->
<div class="modal fade" id="modal-add-kunjungan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="<?= base_url('bkk/store_kunjungan') ?>" method="post">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title font-weight-bold" id="kunjunganModalLabel"><i class="fas fa-bus mr-2"></i> Form Kunjungan Industri</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <input type="hidden" name="id" id="kunjungan_id">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Tanggal Kunjungan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="waktu" id="waktu" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Tempat Kunjungan / Instansi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="tempat" id="tempat" required>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Jumlah Peserta <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="jumlah_peserta" id="jumlah_peserta" placeholder="Misal: 45" required>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Dokumentasi / Keterangan</label>
                            <textarea class="form-control" name="dokumentasi" id="dokumentasi" rows="3" placeholder="Tautan dokumentasi atau catatan..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Kunjungan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Delete -->
<div class="modal fade" id="modal-delete-kunjungan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="<?= base_url('bkk/delete_kunjungan') ?>" method="post">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i> Hapus Kunjungan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 text-center">
                    <input type="hidden" name="id" id="delete_kunjungan_id">
                    <i class="fas fa-trash-alt text-danger mb-3" style="font-size: 3rem;"></i>
                    <p class="mb-0" style="font-size: 1.1rem;">Apakah Anda yakin ingin menghapus data kunjungan industri ini?</p>
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
    $('#kunjunganTable').DataTable({
        "responsive": true,
        "autoWidth": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
        }
    });

    $('.btn-edit').on('click', function() {
        $('#kunjunganModalLabel').html('<i class="fas fa-edit mr-2"></i> Edit Kunjungan');
        $('#kunjungan_id').val($(this).data('id'));
        $('#tanggal_kunjungan').val($(this).data('tanggal'));
        $('#tujuan_instansi').val($(this).data('tujuan'));
        $('#peserta_kelas').val($(this).data('peserta'));
        $('#keterangan').val($(this).data('keterangan'));
    });

    $('#modal-add-kunjungan').on('hidden.bs.modal', function () {
        $('#kunjunganModalLabel').html('<i class="fas fa-bus mr-2"></i> Tambah Kunjungan Industri');
        $('#kunjungan_id').val('');
        $(this).find('form')[0].reset();
    });

    $('.btn-delete').on('click', function() {
        $('#delete_kunjungan_id').val($(this).data('id'));
    });
});
</script>
<?= $this->endSection() ?>
