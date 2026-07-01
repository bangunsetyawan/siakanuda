<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Edit Kunjungan Monitoring</h1>
            <p class="text-secondary mb-0">Ubah laporan kunjungan pembimbing ke lokasi PKL/DU-DI.</p>
        </div>
        <div>
            <a href="<?= base_url('/pkl/monitoring') ?>" class="btn btn-light font-weight-bold btn-sm shadow-sm" style="border-radius: 8px;">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius: 10px;">
                <i class="icon fas fa-ban mr-2"></i> <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-body p-4">
                <form action="<?= base_url('/pkl/monitoring/update/' . $record['id']) ?>" method="post" enctype="multipart/form-data" id="monitoringForm" onsubmit="return submitFormWithCompression(event)">
                    <?= csrf_field() ?>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-secondary mb-1">Kelompok PKL (DU/DI)</label>
                        <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($group['tempat_pkl']) ?> (<?= htmlspecialchars($group['anggota']) ?>)" readonly style="border-radius: 8px;">
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-secondary mb-1">Tanggal Kunjungan <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($record['tanggal']) ?>" required style="border-radius: 8px;">
                    </div>

                    <div>
                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-secondary mb-1">Catatan Hasil Monitoring <span class="text-danger">*</span></label>
                            <textarea name="catatan" id="catatan" class="form-control" rows="6" required style="border-radius: 8px; min-height: 120px;"><?= htmlspecialchars($record['catatan']) ?></textarea>
                            <small class="text-muted d-block mt-1" id="char_count">Karakter: 0/10</small>
                        </div>

                        <!-- Existing photos with delete option -->
                        <?php if (!empty($record['parsed_photo_urls'])): ?>
                            <div class="form-group mb-4">
                                <label class="font-weight-bold text-secondary mb-1">Foto Dokumentasi Saat Ini</label>
                                <div class="d-flex flex-wrap" style="gap: 10px;">
                                    <?php foreach ($record['parsed_photo_urls'] as $idx => $url): ?>
                                        <div class="d-flex flex-column align-items-center rounded border p-2 bg-light text-center" style="width: 90px; gap: 4px;">
                                            <img src="<?= $url ?>" class="rounded" style="width: 55px; height: 55px; object-fit: cover;">
                                            <div class="custom-control custom-checkbox mt-1">
                                                <input type="checkbox" class="custom-control-input" id="del_photo_<?= $idx ?>" name="delete_photos[]" value="<?= $idx ?>">
                                                <label class="custom-control-label text-danger font-weight-bold" for="del_photo_<?= $idx ?>" style="font-size: 11px; cursor: pointer;">Hapus</label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <small class="text-muted d-block mt-1"><i class="fas fa-info-circle mr-1"></i>Centang opsi 'Hapus' di atas untuk menghapus foto lama.</small>
                            </div>
                        <?php endif; ?>

                        <div class="form-group mb-5">
                            <label class="font-weight-bold text-secondary mb-1">Tambah Foto Baru <small class="text-muted">(Maks total 3 foto)</small></label>
                            <div class="custom-file mb-2">
                                <input type="file" name="photo_monitoring[]" class="custom-file-input" id="photo_input" multiple accept="image/*">
                                <label class="custom-file-label" for="photo_input" style="border-radius: 8px;">Pilih foto...</label>
                            </div>
                            <div id="photo_previews" class="d-flex" style="gap: 10px; flex-wrap: wrap;"></div>
                        </div>

                        <div class="text-right">
                            <a href="<?= base_url('/pkl/monitoring') ?>" class="btn btn-light font-weight-bold px-4 py-2 mr-2" style="border-radius: 8px;">Batal</a>
                            <button type="submit" class="btn btn-primary font-weight-bold px-4 py-2" id="submit_btn" style="border-radius: 8px;">Perbarui Laporan</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.2/dist/browser-image-compression.js"></script>
<script>
    document.getElementById('photo_input').addEventListener('change', function(e) {
        let files = e.target.files;
        let label = e.target.nextElementSibling;
        let container = document.getElementById('photo_previews');
        container.innerHTML = "";

        if (files.length === 0) {
            label.innerHTML = "Pilih foto...";
            return;
        }

        // Check total photo limit
        let existingPhotos = <?= count($record['parsed_photo_urls'] ?? []) ?>;
        let deletedPhotosCount = document.querySelectorAll('input[name="delete_photos[]"]:checked').length;
        let remainingPhotos = existingPhotos - deletedPhotosCount;

        if (remainingPhotos + files.length > 3) {
            alert("Jumlah total foto melebihi batas 3 foto! Anda hanya bisa mengunggah " + (3 - remainingPhotos) + " foto tambahan.");
            e.target.value = "";
            label.innerHTML = "Pilih foto...";
            return;
        }

        if (files.length === 1) {
            label.innerHTML = files[0].name;
        } else {
            label.innerHTML = files.length + " foto terpilih";
        }

        Array.from(files).forEach(file => {
            let reader = new FileReader();
            reader.onload = function(event) {
                let img = document.createElement('img');
                img.src = event.target.result;
                img.className = "rounded border img-thumbnail";
                img.style.width = "75px";
                img.style.height = "75px";
                img.style.objectFit = "cover";
                container.appendChild(img);
            }
            reader.readAsDataURL(file);
        });
    });

    // Update photo limit validation on checkbox changes
    document.querySelectorAll('input[name="delete_photos[]"]').forEach(chk => {
        chk.addEventListener('change', function() {
            let input = document.getElementById('photo_input');
            if (input.files.length > 0) {
                let event = new Event('change');
                input.dispatchEvent(event);
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        let textarea = document.getElementById('catatan');
        let countSpan = document.getElementById('char_count');
        let btn = document.getElementById('submit_btn');

        function checkSubmitState() {
            if (!textarea || !countSpan || !btn) return;
            let len = textarea.value.trim().length;
            countSpan.innerHTML = "Karakter: " + len + "/10";
            
            if (len >= 10) {
                countSpan.className = "text-success d-block mt-1";
                btn.disabled = false;
            } else {
                countSpan.className = "text-danger d-block mt-1";
                btn.disabled = true;
            }
        }

        if (textarea) {
            textarea.addEventListener('input', checkSubmitState);
            checkSubmitState();
        }
    });

    // === Validasi submit eksplisit ===
    function validateMonitoringForm(event) {
        const catatan = document.getElementById('catatan');
        if (!catatan || catatan.value.trim().length < 10) {
            alert('Catatan monitoring wajib diisi minimal 10 karakter!');
            event.preventDefault();
            return false;
        }
        return true;
    }

    // === Kompresi foto client-side ===
    async function submitFormWithCompression(event) {
        event.preventDefault();

        if (!validateMonitoringForm(event)) {
            return false;
        }

        const form = document.getElementById('monitoringForm');
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;

        try {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Mengompres Foto...';

            const fileInputs = form.querySelectorAll('input[type="file"]');

            const options = {
                maxSizeMB: 0.5,
                maxWidthOrHeight: 1280,
                useWebWorker: true,
                initialQuality: 0.7
            };

            for (let i = 0; i < fileInputs.length; i++) {
                const input = fileInputs[i];
                if (input.files && input.files.length > 0) {
                    const dataTransfer = new DataTransfer();
                    for (let j = 0; j < input.files.length; j++) {
                        const originalFile = input.files[j];
                        if (!originalFile.type.startsWith('image/')) {
                            dataTransfer.items.add(originalFile);
                            continue;
                        }
                        try {
                            console.log(`Asli (${originalFile.name}): ${(originalFile.size / 1024).toFixed(2)} KB`);
                            const compressedFile = await imageCompression(originalFile, options);
                            const newFile = new File([compressedFile], originalFile.name, {
                                type: compressedFile.type,
                                lastModified: Date.now()
                            });
                            dataTransfer.items.add(newFile);
                            console.log(`Terkompresi (${originalFile.name}): ${(newFile.size / 1024).toFixed(2)} KB`);
                        } catch (error) {
                            console.error('Gagal mengompres foto:', error);
                            dataTransfer.items.add(originalFile);
                        }
                    }
                    input.files = dataTransfer.files;
                }
            }

            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Mengirim Laporan...';
            form.submit();
        } catch (error) {
            console.error('Error saat submit form:', error);
            alert('Terjadi kesalahan sistem saat mencoba mengirim laporan. Silakan coba lagi.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }

        return false;
    }

    
</script>
<?= $this->endSection() ?>

