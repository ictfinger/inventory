<?php
include 'config.php';
// Query data barang masuk beserta total jumlah masuk per barang
$query = "
SELECT bm.*, 
       b.nama_barang, 
       (SELECT SUM(jumlah) FROM barang_masuk WHERE id_barang = bm.id_barang) AS total_masuk
FROM barang_masuk bm
JOIN barang b ON bm.id_barang = b.id
ORDER BY bm.tanggal DESC
";
$result = mysqli_query($conn, $query);

// Persiapan untuk subtotal jumlah masuk & rekap per keterangan
$subtotal = 0;
$rekap_keterangan = []; // ['keterangan' => jumlah]
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Barang Masuk</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #8ec5fc 0%, #e0c3fc 100%);
            min-height: 100vh;
            font-family: 'Montserrat', Arial, sans-serif;
        }
        .dashboard-card {
            border-radius: 18px;
            box-shadow: 0 6px 24px #b7e3fe44;
            background: #fff;
            border: none;
        }
        .dashboard-header {
            font-weight: 700;
            background: linear-gradient(90deg, #4e54c8 0%, #8f94fb 100%);
            border-radius: 18px 18px 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .dashboard-header b {
            letter-spacing: 1px;
        }
        .badge-barang {
            background: #fff;
            color: #4e54c8;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 2px 6px #b7e3fe33;
        }
        .table {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 12px #b7e3fe29;
            transition: box-shadow 0.2s;
        }
        .table:hover {
            box-shadow: 0 8px 32px #b7e3fe44;
        }
        .table thead {
            background: #f7faff;
        }
        .table th {
            letter-spacing: 1px;
        }
        .table-hover tbody tr:hover {
            background: #e9f4fb;
        }
        .btn {
            border-radius: 8px;
        }
        .btn-success, .btn-secondary {
            font-weight: 500;
        }
        .card-header {
            border-bottom: none;
        }
        @media (max-width: 600px) {
            .dashboard-header {
                font-size: 1.1rem !important;
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 shadow">
      <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-box-seam"></i> InventoryApp</a>
        <div>
          <a href="index.php" class="btn btn-outline-light me-2"><i class="bi bi-speedometer2"></i> Dashboard</a>
          <a href="master_barang.php" class="btn btn-outline-light me-2"><i class="bi bi-box"></i> Data Barang</a>
          <a href="barang_keluar.php" class="btn btn-outline-light"><i class="bi bi-box-arrow-right"></i> Barang Keluar</a>
        </div>
      </div>
    </nav>
    <div class="container my-4">
        <div class="card dashboard-card mb-4">
            <div class="card-header dashboard-header text-white fs-5">
                <b><i class="bi bi-box-arrow-in-down"></i> Data Barang Masuk</b>
                <span class="badge badge-barang ms-auto">
                    <i class="bi bi-list-ul"></i> Total Transaksi: <?= mysqli_num_rows($result) ?>
                </span>
            </div>
            <div class="card-body">
                <a href="tambah_masuk.php" class="btn btn-success mb-3">
                    <i class="bi bi-plus-lg"></i> Tambah Barang Masuk
                </a>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Barang</th>
                                <th>Jumlah Masuk</th>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no=1; 
                            while($row = mysqli_fetch_assoc($result)):
                                $subtotal += $row['jumlah'];
                                // Rekap jumlah per keterangan
                                $ket = trim($row['keterangan']) ?: '(Tanpa Keterangan)';
                                if (!isset($rekap_keterangan[$ket])) {
                                    $rekap_keterangan[$ket] = 0;
                                }
                                $rekap_keterangan[$ket] += $row['jumlah'];
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                <td><?= $row['jumlah'] ?></td>
                                <td><?= $row['tanggal'] ?></td>
                                <td><?= htmlspecialchars($row['keterangan']) ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if ($no===1): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada data barang masuk</td>
                            </tr>
                            <?php else: ?>
                            <tr class="fw-bold table-success">
                                <td colspan="2" class="text-end">Total</td>
                                <td><?= $subtotal ?></td>
                                <td colspan="2"></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (count($rekap_keterangan) > 0): ?>
                <div class="alert alert-info mt-3">
                    <strong>Catatan:</strong><br>
                    <?php foreach($rekap_keterangan as $ket => $jumlah): ?>
                        <div>
                            <b><?= htmlspecialchars($ket) ?></b>: <?= $jumlah ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <a href="index.php" class="btn btn-secondary mt-2">
                    <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
        <footer class="mt-4 text-center text-secondary small">
            &copy; <?= date('Y') ?> InventoryApp | Bootstrap 5
        </footer>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>