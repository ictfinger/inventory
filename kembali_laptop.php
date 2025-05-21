<?php
include 'config.php';

// Form pengembalian/proses update tanggal kembali
if(isset($_POST['kembalikan'])){
    $id_keluar = intval($_POST['id_keluar']);
    $tgl_kembali = $_POST['tanggal_kembali'];
    mysqli_query($conn, "UPDATE barang_keluar SET tanggal_kembali_laptop='$tgl_kembali' WHERE id=$id_keluar");
    header("Location: kembali_laptop.php");
    exit;
}

// Ambil semua transaksi yang belum kembali, urutkan dari id terbesar/terakhir diinput
$pinjam = mysqli_query($conn, "
    SELECT k.id, b.nama_barang, s.serial_number, k.nama_karyawan, k.tanggal_ambil_laptop
    FROM barang_keluar k
    JOIN serial_laptop s ON k.id_serial_laptop = s.id
    JOIN barang b ON s.id_barang = b.id
    WHERE k.tanggal_kembali_laptop IS NULL
    ORDER BY k.id ASC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengembalian Laptop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #e3f2fd 0%, #fff 80%);
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
            background: linear-gradient(90deg, #2c84c2 0%, #5ec8e6 100%);
            border-radius: 18px 18px 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.1rem 1.4rem;
        }
        .dashboard-header b {
            letter-spacing: 1px;
        }
        .btn-success {
            font-weight: 500;
        }
        .btn-secondary {
            font-weight: 500;
        }
        .form-control-sm {
            border-radius: 7px;
        }
        .table {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 12px #b7e3fe29;
        }
        .table thead {
            background: #f7faff;
        }
        .table-hover tbody tr:hover {
            background: #eaf6fd !important;
        }
        .card-body {
            padding-bottom: 2rem;
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-box-seam"></i> InventoryApp</a>
            <div>
                <a href="index.php" class="btn btn-outline-light"><i class="bi bi-house"></i> Dashboard</a>
            </div>
        </div>
    </nav>
    <div class="container my-4">
        <div class="card dashboard-card mb-4">
            <div class="dashboard-header text-white fs-5">
                <b><i class="bi bi-arrow-counterclockwise"></i> Pengembalian Laptop</b>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Barang</th>
                                <th>Serial Number</th>
                                <th>Nama Karyawan</th>
                                <th>Tanggal Ambil</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no=1; while($row = mysqli_fetch_assoc($pinjam)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                <td><?= htmlspecialchars($row['serial_number']) ?></td>
                                <td><?= htmlspecialchars($row['nama_karyawan']) ?></td>
                                <td><?= htmlspecialchars($row['tanggal_ambil_laptop']) ?></td>
                                <td>
                                    <form method="post" class="d-flex flex-nowrap" style="gap:2px">
                                        <input type="hidden" name="id_keluar" value="<?= $row['id'] ?>">
                                        <input type="date" name="tanggal_kembali" value="<?= date('Y-m-d') ?>" required class="form-control form-control-sm">
                                        <button type="submit" name="kembalikan" class="btn btn-success btn-sm ms-1">
                                            <i class="bi bi-check2-circle"></i> Kembalikan
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if ($no==1): ?>
                                <tr><td colspan="6" class="text-center text-muted">Tidak ada laptop yang sedang dipinjam.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <a href="barang_keluar.php" class="btn btn-secondary mt-3">
                    <i class="bi bi-arrow-left"></i> Kembali ke Riwayat
                </a>
            </div>
        </div>
    </div>
</body>
</html>