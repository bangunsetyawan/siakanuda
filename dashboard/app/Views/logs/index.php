<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>
<div class="content-header p-0 mb-4">
    <div class="container-fluid">
        <h1 class="m-0 font-weight-bold text-dark" style="font-size: 28px;">Log Audit Chat WhatsApp</h1>
        <p class="text-secondary mb-0">Histori log aktivitas pesan masuk dan keluar yang diproses secara real-time oleh bot WhatsApp sekolah.</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header border-bottom">
        <h3 class="card-title font-weight-bold text-dark"><i class="fab fa-whatsapp mr-2 text-success" style="font-size: 20px;"></i> Histori 100 Pesan Terakhir</h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width: 80px;">#</th>
                        <th style="width: 150px;">Arah Pesan</th>
                        <th>Pengirim / Kontak</th>
                        <th>Isi Pesan WhatsApp</th>
                        <th style="width: 180px;">Waktu Kirim</th>
                    </tr>
                </thead>
                <tbody id="logs-table-body">
                    <?php if (empty($logs)): ?>
                        <tr id="empty-logs-row">
                            <td colspan="5" class="text-center text-secondary py-5">
                                <i class="fas fa-history mb-2" style="font-size: 32px;"></i>
                                <p class="mb-0">Belum ada log histori chat yang tercatat.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $i = 1; foreach ($logs as $log): ?>
                            <tr>
                                <td class="log-index"><?= $i++ ?></td>
                                <td>
                                    <?php if ($log['direction'] === 'in'): ?>
                                        <span class="badge badge-success px-2 py-1 font-weight-bold"><i class="fas fa-arrow-circle-down mr-1"></i> MASUK</span>
                                    <?php else: ?>
                                        <span class="badge badge-primary px-2 py-1 font-weight-bold"><i class="fas fa-arrow-circle-up mr-1"></i> KELUAR</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="font-weight-bold text-dark"><?= htmlspecialchars($log['sender_name']) ?></div>
                                    <small class="text-muted"><code class="text-secondary">+<?= htmlspecialchars(explode('@', $log['phone'])[0]) ?></code></small>
                                </td>
                                <td style="max-width: 450px; white-space: pre-wrap;" class="text-secondary"><?= htmlspecialchars($log['message']) ?></td>
                                <td>
                                    <span class="text-dark font-weight-bold" style="font-size: 13px;">
                                        <?= date('d M Y, H:i', strtotime($log['created_at'])) ?> WIB
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= $waBotUrl ?>/socket.io/socket.io.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const nameMap = <?= json_encode($nameMap) ?>;
        const waBotUrl = "<?= $waBotUrl ?>";
        const tableBody = document.getElementById("logs-table-body");
        
        console.log("[SOCKET] Connecting to WA Bot service at:", waBotUrl);
        const socket = io(waBotUrl, {
            reconnectionAttempts: 10,
            timeout: 5000
        });

        socket.on("connect", () => {
            console.log("[SOCKET] Log page connected to websocket.");
        });

        socket.on("bot:log", (data) => {
            console.log("[SOCKET] Real-time log event received:", data);
            
            const emptyRow = document.getElementById("empty-logs-row");
            if (emptyRow) {
                emptyRow.remove();
            }

            const rawPhone = data.phone;
            const cleanPhone = rawPhone.split('@')[0];
            
            let senderName = nameMap[cleanPhone] || nameMap[rawPhone] || null;
            if (!senderName) {
                if (rawPhone.includes('@g.us')) {
                    senderName = "Grup WhatsApp";
                } else {
                    senderName = "Nomor +" + cleanPhone + " (Belum Whitelisted)";
                }
            }

            const tr = document.createElement("tr");
            
            const directionBadge = data.direction === 'in' 
                ? '<span class="badge badge-success px-2 py-1 font-weight-bold"><i class="fas fa-arrow-circle-down mr-1"></i> MASUK</span>'
                : '<span class="badge badge-primary px-2 py-1 font-weight-bold"><i class="fas fa-arrow-circle-up mr-1"></i> KELUAR</span>';

            const dateObj = new Date(data.created_at || new Date());
            const options = { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
            const formattedDate = dateObj.toLocaleDateString('id-ID', options) + " WIB";

            tr.innerHTML = `
                <td class="log-index">#</td>
                <td>${directionBadge}</td>
                <td>
                    <div class="font-weight-bold text-dark">${escapeHtml(senderName)}</div>
                    <small class="text-muted"><code class="text-secondary">+${escapeHtml(cleanPhone)}</code></small>
                </td>
                <td style="max-width: 450px; white-space: pre-wrap;" class="text-secondary">${escapeHtml(data.message)}</td>
                <td>
                    <span class="text-dark font-weight-bold" style="font-size: 13px;">
                        ${formattedDate}
                    </span>
                </td>
            `;

            tableBody.insertBefore(tr, tableBody.firstChild);

            const rows = tableBody.querySelectorAll("tr");
            rows.forEach((row, index) => {
                if (index >= 100) {
                    row.remove();
                } else {
                    const idxCell = row.querySelector(".log-index");
                    if (idxCell) {
                        idxCell.textContent = index + 1;
                    }
                }
            });
        });

        function escapeHtml(text) {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    });
</script>
<?= $this->endSection() ?>
