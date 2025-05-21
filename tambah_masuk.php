<?php
include 'config.php';
if(isset($_POST['submit'])){
    $id_barang = intval($_POST['id_barang']);
    $jumlah = intval($_POST['jumlah']);
    $ket = $_POST['keterangan'];
    mysqli_query($conn, "INSERT INTO barang_masuk (id_barang, jumlah, keterangan) VALUES ($id_barang, $jumlah, '$ket')");
    mysqli_query($conn, "UPDATE barang SET stok = stok + $jumlah WHERE id = $id_barang");
    header("Location: barang_masuk.php");
    exit;
}
$barang = mysqli_query($conn, "SELECT * FROM barang");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Barang Masuk</title>
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
            background: linear-gradient(90deg, #4e54c8 0%, #8f94fb 100%);
            border-radius: 18px 18px 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.1rem 1.4rem;
        }
        .dashboard-header b {
            letter-spacing: 1px;
        }
        .form-label {
            font-weight: 500;
            color: #2c84c2;
        }
        .form-control, .form-select {
            border-radius: 8px;
            font-size: 15px;
        }
        .btn {
            border-radius: 8px;
            font-weight: 500;
        }
        .btn-success {
            font-weight: 600;
        }
        .btn-secondary {
            font-weight: 500;
        }
        footer {
            margin-top: 32px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-box-seam"></i> InventoryApp</a>
            <div>
                <a href="barang_masuk.php" class="btn btn-outline-light"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
        </div>
    </nav>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card dashboard-card mb-4">
                    <div class="dashboard-header text-white fs-5">
                        <b><i class="bi bi-box-arrow-in-down"></i> Tambah Barang Masuk (Stok Awal)</b>
                    </div>
                    <div class="card-body">
                        <form method="post" autocomplete="off">
                            <div class="mb-3">
                                <label class="form-label">Nama Barang</label>
                                <select name="id_barang" class="form-select" required>
                                    <option value="">Pilih Barang</option>
                                    <?php while($row = mysqli_fetch_assoc($barang)): ?>
                                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nama_barang']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jumlah</label>
                                <input type="number" name="jumlah" class="form-control" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <input type="text" name="keterangan" class="form-control" placeholder="(Opsional)">
                            </div>
                            <button type="submit" name="submit" class="btn btn-success">
                                <i class="bi bi-plus-lg"></i> Simpan
                            </button>
                            <a href="barang_masuk.php" class="btn btn-secondary ms-2">
                                <i class="bi bi-x-lg"></i> Batal
                            </a>
                        </form>
                    </div>
                </div>
                <footer class="text-center text-secondary small">
                    &copy; <?= date('Y') ?> InventoryApp | Bootstrap 5
                </footer>
            </div>
        </div>
    </div>
</body>
</html>