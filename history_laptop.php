<?php
include 'config.php';

// Query: tampilkan serial yang pernah dikembalikan (pernah ada tanggal_kembali_laptop)
$serials = mysqli_query($conn, "
    SELECT s.serial_number, b.nama_barang, s.id as id_serial
    FROM serial_laptop s
    JOIN barang b ON s.id_barang = b.id
    WHERE EXISTS (
        SELECT 1 FROM barang_keluar k WHERE k.id_serial_laptop = s.id AND k.tanggal_kembali_laptop IS NOT NULL AND k.tanggal_kembali_laptop != ''
    )
    ORDER BY b.nama_barang, s.serial_number
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Serial Laptop yang Pernah Dikembalikan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #e3f2fd 0%, #fff 80%);
            min-height: 100vh;
        }
        .dashboard-header {
            font-weight: 700;
            background: linear-gradient(90deg, #2c84c2 0%, #5ec8e6 100%);
            border-radius: 18px 18px 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #fff;
            padding: 14px 22px;
            font-size: 1.15rem;
        }
        .list-group-item {
            background: #f8faff;
            border-radius: 12px !important;
            margin-bottom: 10px;
            border: none;
            box-shadow: 0 2px 12px #b7e3fe22;
            font-size: 1.08rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .serial-label {
            font-weight: bold;
            color: #2c84c2;
            font-size: 1rem;
        }
        .barang-label {
            color: #2467a3;
            font-size: .97rem;
            margin-left: 7px;
        }
        .btn-lastuser {
            font-size: .98rem;
            padding: 3px 12px;
        }
        .modal-header {
            background: linear-gradient(90deg, #2c84c2 0%, #5ec8e6 100%);
            color: #fff;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-box-seam"></i> InventoryApp</a>
        <div>
            <a href="barang_keluar.php" class="btn btn-outline-light me-2"><i class="bi bi-box-arrow-right"></i> Barang Keluar</a>
            <a href="index.php" class="btn btn-outline-light"><i class="bi bi-house"></i> Dashboard</a>
        </div>
    </div>
</nav>
<div class="container my-4">
    <div class="dashboard-header mb-3">
        <b><i class="bi bi-clock-history"></i> Serial Laptop yang Pernah Dikembalikan</b>
    </div>
    <div class="card mb-4" style="border-radius:18px;">
        <div class="card-body">
            <?php if(mysqli_num_rows($serials)): ?>
            <ul class="list-group list-group-flush">
                <?php while($row = mysqli_fetch_assoc($serials)): ?>
                <li class="list-group-item">
                    <span>
                        <span class="serial-label"><i class="bi bi-laptop"></i> <?= htmlspecialchars($row['serial_number']) ?></span>
                        <span class="barang-label">(<?= htmlspecialchars($row['nama_barang']) ?>)</span>
                    </span>
                    <button 
                        class="btn btn-info btn-lastuser"
                        data-serial="<?= htmlspecialchars($row['serial_number']) ?>"
                        data-idserial="<?= (int)$row['id_serial'] ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#modalLastUser"
                    >
                        <i class="bi bi-person"></i> Pemakai Terakhir
                    </button>
                </li>
                <?php endwhile; ?>
            </ul>
            <?php else: ?>
                <div class="alert alert-info text-center mb-0">
                    Belum ada serial laptop yang pernah dikembalikan.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Pemakai Terakhir -->
<div class="modal fade" id="modalLastUser" tabindex="-1" aria-labelledby="modalLastUserLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLastUserLabel">Pemakai Terakhir</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div id="lastUserContent" class="mb-2">
            <div class="text-center text-muted"><i class="bi bi-arrow-repeat"></i> Memuat data ...</div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modalTitle = document.getElementById('modalLastUserLabel');
    var modalBody = document.getElementById('lastUserContent');
    document.querySelectorAll('.btn-lastuser').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var serial = this.getAttribute('data-serial');
            var id_serial = this.getAttribute('data-idserial');
            modalTitle.textContent = 'Pemakai Terakhir: ' + serial;
            modalBody.innerHTML = '<div class="text-center text-muted"><i class="bi bi-arrow-repeat"></i> Memuat data ...</div>';

            fetch('history_laptop_lastuser.php?id_serial=' + encodeURIComponent(id_serial))
                .then(response => response.json())
                .then(function(json) {
                    if (json && json.nama_karyawan) {
                        modalBody.innerHTML = '<div class="text-center">'+
                            '<i class="bi bi-person-circle" style="font-size:2rem;color:#2c84c2"></i><br>' +
                            '<b style="font-size:1.15rem;">'+json.nama_karyawan+'</b><br>' +
                            (json.tanggal_ambil_laptop ? '<span class="badge bg-primary mt-2">Terakhir ambil: '+json.tanggal_ambil_laptop+'</span><br>' : '') +
                            (json.tanggal_kembali_laptop ? '<span class="badge bg-success mt-2">Terakhir kembali: '+json.tanggal_kembali_laptop+'</span>' : '') +
                            '</div>';
                    } else {
                        modalBody.innerHTML = '<div class="alert alert-warning text-center mb-0">Tidak ditemukan data pemakai terakhir.</div>';
                    }
                })
                .catch(function() {
                    modalBody.innerHTML = '<div class="alert alert-danger text-center mb-0">Gagal memuat data.</div>';
                });
        });
    });
});
</script>
</body>
</html>