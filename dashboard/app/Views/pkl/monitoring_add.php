<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Tambah Kunjungan Monitoring</h1>
            <p class="text-secondary mb-0">Input laporan kunjungan pembimbing ke lokasi PKL/DU-DI.</p>
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
                <form action="<?= base_url('/pkl/monitoring/store') ?>" method="post" enctype="multipart/form-data" id="monitoringForm" onsubmit="return submitFormWithCompression(event)">
                    <?= csrf_field() ?>

                    <!-- Locked Group ID Field -->
                    <?php if (!empty($selectedGroupId)): ?>
                        <input type="hidden" name="kelompok_pkl_id" value="<?= htmlspecialchars($selectedGroupId) ?>">
                    <?php endif; ?>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-secondary mb-1">Kelompok PKL (DU/DI) <span class="text-danger">*</span></label>
                        <select name="kelompok_pkl_id" class="form-control custom-select" required style="border-radius: 8px;" <?= !empty($selectedGroupId) ? 'disabled' : '' ?>>
                            <option value="">-- Pilih Kelompok PKL --</option>
                            <?php foreach ($groups as $g): ?>
                                <option value="<?= $g['id'] ?>" <?= ($g['id'] == $selectedGroupId) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($g['tempat_pkl']) ?> (<?= htmlspecialchars($g['anggota']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($selectedGroupId)): ?>
                            <small class="text-muted d-block mt-1"><i class="fas fa-lock mr-1"></i>Kelompok terkunci karena diakses melalui tombol bimbingan.</small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-secondary mb-1">Tanggal Kunjungan <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required style="border-radius: 8px;">
                    </div>

                    <div>
                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-secondary mb-1">Catatan Hasil Monitoring <span class="text-danger">*</span></label>
                            <textarea name="catatan" id="catatan" class="form-control" rows="6" placeholder="Ketik hasil monitoring siswa, respon DU/DI, kendala, atau hal penting lainnya... Min 10 karakter." required style="border-radius: 8px; min-height: 120px;"></textarea>
                            <small class="text-muted d-block mt-1" id="char_count">Karakter: 0/10</small>
                        </div>

                        <div class="form-group mb-5">
                            <label class="font-weight-bold text-secondary mb-1">Foto Dokumentasi Kunjungan <small class="text-muted">(Maks 3 file, JPG/PNG)</small></label>
                            <div class="custom-file mb-2">
                                <input type="file" name="photo_monitoring[]" class="custom-file-input" id="photo_input" multiple accept="image/*">
                                <label class="custom-file-label" for="photo_input" style="border-radius: 8px;">Pilih 1-3 foto dokumentasi...</label>
                            </div>
                            <div id="photo_previews" class="d-flex" style="gap: 10px; flex-wrap: wrap;"></div>
                        </div>

                        <div class="text-right">
                            <a href="<?= base_url('/pkl/monitoring') ?>" class="btn btn-light font-weight-bold px-4 py-2 mr-2" style="border-radius: 8px;">Batal</a>
                            <button type="submit" class="btn btn-primary font-weight-bold px-4 py-2" id="submit_btn" style="border-radius: 8px;" disabled>Simpan Laporan</button>
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
            label.innerHTML = "Pilih 1-3 foto dokumentasi...";
            return;
        }

        if (files.length > 3) {
            alert("Anda hanya dapat mengunggah maksimal 3 foto!");
            e.target.value = "";
            label.innerHTML = "Pilih 1-3 foto dokumentasi...";
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

