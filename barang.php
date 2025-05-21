<?php
include 'config.php';
$data = mysqli_query($conn, "
SELECT b.*, COUNT(s.id) as jumlah_serial
FROM barang b
LEFT JOIN serial_laptop s ON b.id=s.id_barang
GROUP BY b.id
");

// Inisialisasi total serial
$total_serial = 0;
$jumlah_barang = mysqli_num_rows($data);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Master Barang</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;400&display=swap" rel="stylesheet">
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
        .btn-success {
            font-weight: 500;
        }
        .btn-info {
            font-weight: 500;
            color: #fff;
        }
        .btn-secondary {
            font-weight: 500;
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
    <div class="container my-4">
        <div class="card dashboard-card mb-4">
            <div class="card-header dashboard-header text-white fs-5 d-flex justify-content-between align-items-center">
                <span><b><i class="bi bi-clipboard-data"></i> Data Master Barang</b></span>
                <span class="badge badge-barang shadow-sm">
                    <i class="bi bi-list-ul"></i> Total: <?= $jumlah_barang ?>
                </span>
            </div>
            <div class="card-body">
                <a href="tambah_barang.php" class="btn btn-success mb-3">
                    <i class="bi bi-plus-lg"></i> Tambah Barang
                </a>
                <div class="table-responsive rounded-3">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px;">No</th>
                                <th><i class="bi bi-box"></i> Nama Barang</th>
                                <th><i class="bi bi-card-text"></i> Deskripsi</th>
                                <th><i class="bi bi-123"></i> Jumlah Serial Laptop</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no=1; while($row = mysqli_fetch_assoc($data)): ?>
                            <?php $total_serial += (int)$row['jumlah_serial']; ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                                <td><?= (int)$row['jumlah_serial'] ?></td>
                                <td>
                                    <a href="serial_laptop.php?id_barang=<?= $row['id'] ?>" class="btn btn-info btn-sm">
                                        <i class="bi bi-hdd-network"></i> Kelola Serial
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if($no==1): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada data barang</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total Serial Laptop</th>
                                <th><?= $total_serial ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <a href="index.php" class="btn btn-secondary mt-3">
                    <i class="bi bi-arrow-left"></i> Kembali Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>