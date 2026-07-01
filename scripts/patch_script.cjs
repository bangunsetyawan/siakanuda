const fs = require('fs');
let code = fs.readFileSync('dashboard/app/Views/pkl/student_report.php', 'utf8');

// 1. Add ID to group photo label
code = code.replace(
  '<label class="font-weight-bold text-dark mb-1" style="font-size: 12px;">\r\n                                        📸 Foto Dokumentasi Kelompok Hari Ini (Wajib)\r\n                                    </label>',
  '<label class="font-weight-bold text-dark mb-1" style="font-size: 12px;" id="label_photo_kelompok">\r\n                                        📸 Foto Dokumentasi Kelompok Hari Ini (Wajib)\r\n                                    </label>'
);
code = code.replace(
  '<label class="font-weight-bold text-dark mb-1" style="font-size: 12px;">\n                                        📸 Foto Dokumentasi Kelompok Hari Ini (Wajib)\n                                    </label>',
  '<label class="font-weight-bold text-dark mb-1" style="font-size: 12px;" id="label_photo_kelompok">\n                                        📸 Foto Dokumentasi Kelompok Hari Ini (Wajib)\n                                    </label>'
);


// 2. Add Hapus Foto for kelompok
code = code.replace(
  '<small class="text-success d-block" style="font-size: 10px;"><i class="fas fa-check-circle"></i> Sudah upload foto kelompok.</small>\r\n                                        </div>',
  '<small class="text-success d-block" style="font-size: 10px;"><i class="fas fa-check-circle"></i> Sudah upload foto kelompok.</small>\r\n                                            <div class="form-check mt-1">\r\n                                                <input class="form-check-input" type="checkbox" name="delete_photo_kelompok" value="1" id="del_kelompok">\r\n                                                <label class="form-check-label text-danger font-weight-bold" style="font-size: 11px; cursor: pointer;" for="del_kelompok"><i class="fas fa-trash-alt"></i> Hapus Foto Ini</label>\r\n                                            </div>\r\n                                        </div>'
);
code = code.replace(
  '<small class="text-success d-block" style="font-size: 10px;"><i class="fas fa-check-circle"></i> Sudah upload foto kelompok.</small>\n                                        </div>',
  '<small class="text-success d-block" style="font-size: 10px;"><i class="fas fa-check-circle"></i> Sudah upload foto kelompok.</small>\n                                            <div class="form-check mt-1">\n                                                <input class="form-check-input" type="checkbox" name="delete_photo_kelompok" value="1" id="del_kelompok">\n                                                <label class="form-check-label text-danger font-weight-bold" style="font-size: 11px; cursor: pointer;" for="del_kelompok"><i class="fas fa-trash-alt"></i> Hapus Foto Ini</label>\n                                            </div>\n                                        </div>'
);


// 3. Add Hapus Foto for member
code = code.replace(
  '<small class="text-success d-block" style="font-size: 10px;"><i class="fas fa-check-circle"></i> Sudah upload foto bukti.</small>\r\n                                            </div>',
  '<small class="text-success d-block" style="font-size: 10px;"><i class="fas fa-check-circle"></i> Sudah upload foto bukti.</small>\r\n                                                <div class="form-check mt-1">\r\n                                                    <input class="form-check-input" type="checkbox" name="delete_photo_<?= htmlspecialchars($name_sanitized) ?>" value="1" id="del_<?= htmlspecialchars($name_sanitized) ?>">\r\n                                                    <label class="form-check-label text-danger font-weight-bold" style="font-size: 11px; cursor: pointer;" for="del_<?= htmlspecialchars($name_sanitized) ?>"><i class="fas fa-trash-alt"></i> Hapus Foto Ini</label>\r\n                                                </div>\r\n                                            </div>'
);
code = code.replace(
  '<small class="text-success d-block" style="font-size: 10px;"><i class="fas fa-check-circle"></i> Sudah upload foto bukti.</small>\n                                            </div>',
  '<small class="text-success d-block" style="font-size: 10px;"><i class="fas fa-check-circle"></i> Sudah upload foto bukti.</small>\n                                                <div class="form-check mt-1">\n                                                    <input class="form-check-input" type="checkbox" name="delete_photo_<?= htmlspecialchars($name_sanitized) ?>" value="1" id="del_<?= htmlspecialchars($name_sanitized) ?>">\n                                                    <label class="form-check-label text-danger font-weight-bold" style="font-size: 11px; cursor: pointer;" for="del_<?= htmlspecialchars($name_sanitized) ?>"><i class="fas fa-trash-alt"></i> Hapus Foto Ini</label>\n                                                </div>\n                                            </div>'
);

// 4. Update JS logic for handleAttendanceChange
// Find the exact JS string
const oldJS = `    function handleAttendanceChange(name, status) {
        const sanitized = name.replace(/[^a-zA-Z0-9]/g, '_');
        
        // Hide all containers
        document.getElementById('container-hadir-' + sanitized).style.display = 'none';
        document.getElementById('container-sakit-' + sanitized).style.display = 'none';
        document.getElementById('container-izin-' + sanitized).style.display = 'none';
        document.getElementById('container-alpha-' + sanitized).style.display = 'none';
        
        // Show selected container
        document.getElementById('container-' + status + '-' + sanitized).style.display = 'block';
        
        // Update photo container visibility and label
        const photoContainer = document.getElementById('photo-container-' + sanitized);
        if (photoContainer) {
            if (status === 'sakit' || status === 'izin') {
                photoContainer.style.display = 'block';
            } else {
                photoContainer.style.display = 'none';
            }
        }`;

const newJS = `    function updateKelompokPhotoLabel() {
        const labelEl = document.getElementById('label_photo_kelompok');
        if (!labelEl) return;
        let hasHadir = false;
        document.querySelectorAll('input[name^="attendance["]:checked').forEach(radio => {
            if (radio.value === 'hadir') hasHadir = true;
        });
        if (hasHadir) {
            labelEl.innerHTML = '📸 Foto Dokumentasi Kelompok Hari Ini (Wajib)';
            labelEl.classList.add('text-dark');
            labelEl.classList.remove('text-secondary');
        } else {
            labelEl.innerHTML = '📸 Foto Dokumentasi Kelompok Hari Ini (Opsional)';
            labelEl.classList.remove('text-dark');
            labelEl.classList.add('text-secondary');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateKelompokPhotoLabel();
    });

    function handleAttendanceChange(name, status) {
        const sanitized = name.replace(/[^a-zA-Z0-9]/g, '_');
        
        // Hide all containers
        document.getElementById('container-hadir-' + sanitized).style.display = 'none';
        document.getElementById('container-sakit-' + sanitized).style.display = 'none';
        document.getElementById('container-izin-' + sanitized).style.display = 'none';
        document.getElementById('container-alpha-' + sanitized).style.display = 'none';
        
        // Show selected container
        document.getElementById('container-' + status + '-' + sanitized).style.display = 'block';
        
        // Update photo container visibility and label
        const photoContainer = document.getElementById('photo-container-' + sanitized);
        if (photoContainer) {
            if (status === 'sakit' || status === 'izin') {
                photoContainer.style.display = 'block';
            } else {
                photoContainer.style.display = 'none';
            }
        }
        
        updateKelompokPhotoLabel();`;

// replace CRLF and LF versions of JS
code = code.replace(oldJS, newJS);
code = code.replace(oldJS.replace(/\n/g, '\r\n'), newJS.replace(/\n/g, '\r\n'));

fs.writeFileSync('dashboard/app/Views/pkl/student_report.php', code, 'utf8');
console.log('Update successful!');
