<?php
include 'config.php';
$id_barang = intval($_GET['id_barang']);
$barang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM barang WHERE id=$id_barang"));
$error = "";

if(isset($_POST['submit'])){
    $serial = trim($_POST['serial_number']);
    if($serial != "") {
        // Cek duplikat serial pada barang yang sama
        $cek = mysqli_query($conn, "SELECT 1 FROM serial_laptop WHERE id_barang=$id_barang AND serial_number='$serial'");
        if(mysqli_num_rows($cek) > 0){
            $error = "Serial number sudah terdaftar pada barang ini!";
        } else {
            mysqli_query($conn, "INSERT INTO serial_laptop (id_barang, serial_number) VALUES ($id_barang, '$serial')");
            header("Location: serial_laptop.php?id_barang=".$id_barang);
            exit;
        }
    } else {
        $error = "Serial number tidak boleh kosong.";
    }
}
if(isset($_GET['hapus'])){
    $id_del = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM serial_laptop WHERE id=$id_del AND id_barang=$id_barang");
    header("Location: serial_laptop.php?id_barang=".$id_barang);
    exit;
}
$serials = mysqli_query($conn, "SELECT * FROM serial_laptop WHERE id_barang=$id_barang");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Serial Laptop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
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
        .form-control, .btn {
            border-radius: 8px;
        }
        .btn-success, .btn-danger, .btn-secondary {
            font-weight: 500;
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
        .table-hover tbody tr:hover {
            background: #e9f4fb;
        }
        @media (max-width: 768px) {
            .dashboard-card {
                margin-bottom: 1rem;
            }
            .table {
                font-size: 0.95rem;
            }
        }
        .fade-in {
            animation: fadeIn 0.7s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-box-seam"></i> InventoryApp</a>
            <div class="d-flex align-items-center ms-auto">
                <a href="index.php" class="btn btn-light logout-btn">
                    <i class="bi bi-house-door"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>
    <div class="container my-4">
        <div class="card dashboard-card fade-in mb-4">
            <div class="card-header dashboard-header text-white fs-5">
                <b><i class="bi bi-hdd-network"></i> Kelola Serial Laptop</b>
                <span class="badge badge-barang ms-auto">
                    <i class="bi bi-list-ul"></i> Total Serial: <?= mysqli_num_rows($serials) ?>
                </span>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Nama Barang:</strong>
                    <span class="badge bg-primary"><?= htmlspecialchars($barang['nama_barang']) ?></span>
                </div>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?=$error?></div>
                <?php endif; ?>
                <form method="post" class="mb-3 d-flex flex-wrap gap-2">
                    <input type="text" name="serial_number" class="form-control me-2" style="max-width:300px;" placeholder="Serial Number Laptop" required>
                    <button type="submit" name="submit" class="btn btn-success">
                        <i class="bi bi-plus-lg"></i> Tambah Serial
                    </button>
                </form>
                <div class="table-responsive rounded-3">
                    <table class="table table-bordered table-hover align-middle mb-0" style="max-width:500px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px;">No</th>
                                <th><i class="bi bi-hash"></i> Serial Number</th>
                                <th style="width:100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no=1;
                            // refresh serials
                            $serials = mysqli_query($conn, "SELECT * FROM serial_laptop WHERE id_barang=$id_barang");
                            while($row = mysqli_fetch_assoc($serials)): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['serial_number']) ?></td>
                                <td>
                                    <a href="?id_barang=<?= $id_barang ?>&hapus=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus serial ini?')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if($no==1): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted">Belum ada serial laptop</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <a href="barang.php" class="btn btn-secondary mt-3">
                    <i class="bi bi-arrow-left"></i> Kembali ke Master Barang
                </a>
            </div>
        </div>
    </div>
</body>
</html>