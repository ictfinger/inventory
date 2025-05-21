<?php
include 'config.php';

if(isset($_POST['submit'])){
    $id_serial_laptop = intval($_POST['serial_number_laptop']);
    $nama_karyawan = $_POST['nama_karyawan'];
    $email = $_POST['email'];
    $no_badge = $_POST['no_badge'];
    $lokasi_kerja = $_POST['lokasi_kerja'];
    $serial_charger = $_POST['serial_number_charger'];
    $tanggal_ambil = $_POST['tanggal_ambil_laptop'];

    if ($id_serial_laptop > 0) {
        mysqli_query($conn, "INSERT INTO barang_keluar
            (id_serial_laptop, nama_karyawan, email, no_badge, lokasi_kerja, serial_number_charger, tanggal_ambil_laptop)
            VALUES
            ($id_serial_laptop, '$nama_karyawan', '$email', '$no_badge', '$lokasi_kerja', '$serial_charger', '$tanggal_ambil')
        ");
        header("Location: barang_keluar.php");
        exit;
    }
}

// Ambil serial yang tidak sedang dipinjam (belum dikembalikan)
$serials = mysqli_query($conn, "
    SELECT s.id, s.serial_number, b.nama_barang
    FROM serial_laptop s
    JOIN barang b ON s.id_barang = b.id
    WHERE s.id NOT IN (
        SELECT id_serial_laptop FROM barang_keluar WHERE tanggal_kembali_laptop IS NULL
    )
    ORDER BY b.nama_barang, s.serial_number
");
$serial_count = mysqli_num_rows($serials);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Barang Keluar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
        .select2-container .select2-selection--single { height: 38px; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; }
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
                <a href="index.php" class="btn btn-outline-light"><i class="bi bi-house"></i> Dashboard</a>
            </div>
        </div>
    </nav>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card dashboard-card mb-4">
                    <div class="dashboard-header text-white fs-5">
                        <b><i class="bi bi-box-arrow-right"></i> Tambah Data Barang Keluar</b>
                    </div>
                    <div class="card-body">
                        <form method="post" autocomplete="off">
                            <div class="mb-3">
                                <label class="form-label">Nama Karyawan</label>
                                <input type="text" class="form-control" name="nama_karyawan" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email/UserID</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">No Badge</label>
                                <input type="text" class="form-control" name="no_badge" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Lokasi Kerja</label>
                                <input type="text" class="form-control" name="lokasi_kerja" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Serial Number Laptop</label>
                                <select name="serial_number_laptop" id="serial_select" class="form-select" required <?= $serial_count === 0 ? 'disabled' : '' ?>>
                                    <option value="">-- Pilih Serial Number Laptop --</option>
                                    <?php mysqli_data_seek($serials, 0); while($row = mysqli_fetch_assoc($serials)): ?>
                                        <option value="<?= $row['id'] ?>">
                                            <?= htmlspecialchars($row['serial_number']) ?> (<?= htmlspecialchars($row['nama_barang']) ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Serial Number Charger</label>
                                <input type="text" class="form-control" name="serial_number_charger" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tanggal Ambil Laptop</label>
                                <input type="date" class="form-control" name="tanggal_ambil_laptop" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <button type="submit" name="submit" class="btn btn-success" <?= $serial_count === 0 ? 'disabled' : '' ?>>
                                <i class="bi bi-plus-lg"></i> Tambah
                            </button>
                            <a href="barang_keluar.php" class="btn btn-secondary ms-2">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </form>
                        <?php if($serial_count === 0): ?>
                            <div class="alert alert-warning mt-3">
                                Semua serial number laptop sedang dipinjam.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <footer class="text-center text-secondary small">
                    &copy; <?= date('Y') ?> InventoryApp | Bootstrap 5
                </footer>
            </div>
        </div>
    </div>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#serial_select').select2({
            placeholder: '-- Pilih Serial Number Laptop --',
            allowClear: true,
            width: '100%'
        });
    });
    </script>
</body>
</html>