<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Data Tracer Study Alumni</h1>
            <p class="text-secondary mb-0">Kelola dan pantau sebaran lulusan SMK NU Darussalam.</p>
        </div>
        <div>
            <button class="btn btn-primary" data-toggle="modal" data-target="#modal-add-alumni">
                <i class="fas fa-plus mr-2"></i> Tambah Alumni
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
        <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-table mr-2 text-primary"></i> Daftar Alumni</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="alumniTable" class="table table-bordered table-striped table-hover">
                <thead class="bg-light">
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama Lengkap</th>
                        <th>Tahun Lulus</th>
                        <th>Jurusan</th>
                        <th>Status</th>
                        <th>Tempat Kerja / Kuliah</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($alumni as $row) : ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= esc($row['nama_lengkap']) ?></td>
                        <td><?= esc($row['tahun_lulus']) ?></td>
                        <td><?= esc($row['kompetensi_keahlian']) ?></td>
                        <td>
                            <?php
                                $status = strtolower($row['status_utama']);
                                if (strpos($status, 'bekerja') !== false) {
                                    echo '<span class="badge badge-success">Bekerja</span>';
                                } elseif (strpos($status, 'studi') !== false || strpos($status, 'kuliah') !== false) {
                                    echo '<span class="badge badge-info">Studi Lanjut</span>';
                                } elseif (strpos($status, 'wirausaha') !== false) {
                                    echo '<span class="badge badge-warning">Wirausaha</span>';
                                } else {
                                    echo '<span class="badge badge-danger">Mencari Kerja</span>';
                                }
                            ?>
                        </td>
                        <td><?= esc($row['nama_tempat_kerja'] ?: '-') ?></td>
                        <td>
                            <button class="btn btn-sm btn-info btn-edit" 
                                data-id="<?= $row['id'] ?>"
                                data-nama="<?= esc($row['nama_lengkap']) ?>"
                                data-tahun="<?= esc($row['tahun_lulus']) ?>"
                                data-jurusan="<?= esc($row['kompetensi_keahlian']) ?>"
                                data-status="<?= esc($row['status_utama']) ?>"
                                data-tempat="<?= esc($row['nama_tempat_kerja']) ?>"
                                data-toggle="modal" data-target="#modal-add-alumni">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-delete" 
                                data-id="<?= $row['id'] ?>"
                                data-toggle="modal" data-target="#modal-delete-alumni">
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

<!-- Modal Add/Edit Alumni -->
<div class="modal fade" id="modal-add-alumni" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="<?= base_url('bkk/store_alumni') ?>" method="post">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title font-weight-bold" id="alumniModalLabel"><i class="fas fa-user-graduate mr-2"></i> Form Alumni</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <input type="hidden" name="id" id="alumni_id">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_lengkap" id="nama_lengkap" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Tahun Lulus <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="tahun_lulus" id="tahun_lulus" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Jurusan <span class="text-danger">*</span></label>
                            <select class="form-control" name="kompetensi_keahlian" id="kompetensi_keahlian" required>
                                <option value="">Pilih Jurusan</option>
                                <option value="Teknik Komputer dan Jaringan">Teknik Komputer dan Jaringan</option>
                                <option value="Akuntansi dan Keuangan Lembaga">Akuntansi dan Keuangan Lembaga</option>
                                <option value="Teknik Kendaraan Ringan">Teknik Kendaraan Ringan Otomotif</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Status Utama <span class="text-danger">*</span></label>
                            <select class="form-control" name="status_utama" id="status_utama" required>
                                <option value="">Pilih Status</option>
                                <option value="Bekerja">Bekerja</option>
                                <option value="Studi Lanjut">Studi Lanjut / Kuliah</option>
                                <option value="Wirausaha">Wirausaha</option>
                                <option value="Belum Bekerja">Mencari Kerja / Belum Bekerja</option>
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Tempat Kerja / Kampus / Usaha</label>
                            <input type="text" class="form-control" name="nama_tempat_kerja" id="nama_tempat_kerja" placeholder="Opsional">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Data</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Delete -->
<div class="modal fade" id="modal-delete-alumni" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="<?= base_url('bkk/delete_alumni') ?>" method="post">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i> Hapus Data</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 text-center">
                    <input type="hidden" name="id" id="delete_alumni_id">
                    <i class="fas fa-trash-alt text-danger mb-3" style="font-size: 3rem;"></i>
                    <p class="mb-0" style="font-size: 1.1rem;">Apakah Anda yakin ingin menghapus data alumni ini? Tindakan ini tidak dapat dibatalkan.</p>
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
    $('#alumniTable').DataTable({
        "responsive": true,
        "autoWidth": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
        }
    });

    $('.btn-edit').on('click', function() {
        $('#alumniModalLabel').html('<i class="fas fa-edit mr-2"></i> Edit Alumni');
        $('#alumni_id').val($(this).data('id'));
        $('#nama_lengkap').val($(this).data('nama'));
        $('#tahun_lulus').val($(this).data('tahun'));
        $('#nama_tempat_kerja').val($(this).data('tempat'));
        
        // Handle Jurusan Dropdown matching
        let jurusan = $(this).data('jurusan');
        $("#kompetensi_keahlian option").filter(function() {
            return $(this).text().indexOf(jurusan) > -1 || jurusan.indexOf($(this).val()) > -1;
        }).prop('selected', true);

        // Handle Status Dropdown matching
        let status = $(this).data('status');
        $("#status_utama option").filter(function() {
            return $(this).text().toLowerCase().indexOf(status.toLowerCase()) > -1 || status.toLowerCase().indexOf($(this).val().toLowerCase()) > -1;
        }).prop('selected', true);
    });

    $('#modal-add-alumni').on('hidden.bs.modal', function () {
        $('#alumniModalLabel').html('<i class="fas fa-user-graduate mr-2"></i> Tambah Alumni');
        $('#alumni_id').val('');
        $(this).find('form')[0].reset();
    });

    $('.btn-delete').on('click', function() {
        $('#delete_alumni_id').val($(this).data('id'));
    });
});
</script>
<?= $this->endSection() ?>
