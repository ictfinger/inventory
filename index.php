<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
include 'config.php';
$result = mysqli_query($conn, "SELECT * FROM barang");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Inventory</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;400&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #8ec5fc 0%, #e0c3fc 100%);
            min-height: 100vh;
            font-family: 'Montserrat', Arial, sans-serif;
        }
        .navbar {
            border-radius: 0 0 16px 16px;
            box-shadow: 0 4px 24px #b7e3fe44;
            margin-bottom: 32px;
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
        }
        .dashboard-header b {
            letter-spacing: 1px;
        }
        .nav-shortcut .btn {
            font-weight: 600;
            font-size: 1.1rem;
            padding: 12px 24px;
            margin-right: 12px;
            border-radius: 8px;
            transition: 0.15s;
        }
        .nav-shortcut .btn:hover {
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 6px 24px #d0e6fd90;
        }
        .table {
            border-radius: 8px;
            overflow: hidden;
        }
        .table thead {
            background: #e9f4fb;
        }
        .table th {
            letter-spacing: 1px;
        }
        .table-hover tbody tr:hover {
            background: #f2f9ff;
        }
        .logout-btn {
            font-size: 1rem;
            border-radius: 8px;
        }
        @media (max-width: 600px) {
            .nav-shortcut .btn {
                margin-bottom: 8px;
                width: 100%;
            }
            .dashboard-header {
                font-size: 1.1rem !important;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">
            <i class="bi bi-box-seam"></i> InventoryApp
        </a>
        <div class="d-flex align-items-center ms-auto">
            <span class="me-3 text-white fst-italic d-none d-md-inline">
                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['admin_user']) ?>
            </span>
            <a href="logout.php" class="btn btn-danger logout-btn">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
</nav>
<div class="container">
    <div class="text-center mb-4">
        <h1 class="fw-bold" style="letter-spacing:2px;">Dashboard Inventory</h1>
        <p class="text-muted mb-1">Selamat datang di sistem inventory barang. Kelola data barang dengan mudah dan cepat!</p>
    </div>
    <div class="nav-shortcut d-flex flex-wrap justify-content-center mb-4">
        <a href="barang.php" class="btn btn-primary mb-2"><i class="bi bi-archive-fill me-2"></i>Master Barang</a>
        <a href="barang_masuk.php" class="btn btn-success mb-2"><i class="bi bi-box-arrow-in-down me-2"></i>Barang Masuk</a>
        <a href="barang_keluar.php" class="btn btn-warning text-white mb-2"><i class="bi bi-box-arrow-up me-2"></i>Barang Keluar</a>
    </div>
    <div class="card dashboard-card">
        <div class="card-header dashboard-header text-white fs-5">
            <b><i class="bi bi-clipboard-data"></i> Data Master Barang</b>
        </div>
        <div class="card-body">
            <div class="table-responsive rounded-3">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width:60px;">No</th>
                            <th><i class="bi bi-box"></i> Nama Barang</th>
                            <th><i class="bi bi-card-text"></i> Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no=1; while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                            <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if($no==1): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted">Belum ada data barang</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <footer class="mt-5 text-center text-secondary small">
        &copy; <?= date('Y') ?> InventoryApp — Crafted with <i class="bi bi-heart-fill text-danger"></i>
    </footer>
</div>
</body>
</html>