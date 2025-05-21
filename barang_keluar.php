<?php
include 'config.php';

// Proses tambah barang keluar dari modal
if(isset($_POST['submit_tambah_keluar'])){
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
        echo "<script>location.href='barang_keluar.php';</script>";
        exit;
    }
}

// PAGINATION SETUP
$limit = 20; // Jumlah baris per halaman
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Ambil daftar barang untuk filter
$daftar_barang = mysqli_query($conn, "SELECT DISTINCT b.id, b.nama_barang FROM barang b JOIN serial_laptop s ON b.id=s.id_barang");

// Tangkap filter
$filter_id_barang = isset($_GET['id_barang']) ? intval($_GET['id_barang']) : 0;

// Hitung total data untuk pagination
$where = "";
if ($filter_id_barang) {
    $where = "AND b.id = $filter_id_barang";
}
$total_sql = "
    SELECT COUNT(*) as jumlah
    FROM barang_keluar k
    JOIN serial_laptop s ON k.id_serial_laptop = s.id
    JOIN barang b ON s.id_barang = b.id
    WHERE 1=1 $where
";
$total_result = mysqli_query($conn, $total_sql);
$total_row = mysqli_fetch_assoc($total_result);
$total_data = $total_row ? $total_row['jumlah'] : 0;
$total_pages = max(1, ceil($total_data / $limit));

// Query riwayat keluar (LIMIT untuk paging)
$data = mysqli_query($conn, "
    SELECT k.*, s.serial_number, b.nama_barang,
        IF(k.tanggal_kembali_laptop IS NULL, 'Dipinjam', 'Kembali') AS status
    FROM barang_keluar k
    JOIN serial_laptop s ON k.id_serial_laptop = s.id
    JOIN barang b ON s.id_barang = b.id
    WHERE 1=1 $where
    ORDER BY k.id ASC
    LIMIT $limit OFFSET $offset
");

// Siapkan data untuk grafik (ambil SEMUA data ASC, bukan paging)
$grafik = [];
$grafik_data = mysqli_query($conn, "
    SELECT k.tanggal_ambil_laptop
    FROM barang_keluar k
    JOIN serial_laptop s ON k.id_serial_laptop = s.id
    JOIN barang b ON s.id_barang = b.id
    WHERE 1=1 $where
    ORDER BY k.id ASC
");
if ($grafik_data) {
    while($row = mysqli_fetch_assoc($grafik_data)) {
        if (!empty($row['tanggal_ambil_laptop'])) {
            $tgl = $row['tanggal_ambil_laptop'];
            if (!isset($grafik[$tgl])) $grafik[$tgl]=0;
            $grafik[$tgl]++;
        }
    }
}

// Query: tampilkan serial yang pernah dikembalikan
$serials = mysqli_query($conn, "
    SELECT s.serial_number, b.nama_barang, s.id as id_serial,
        (SELECT COUNT(*) FROM barang_keluar k WHERE k.id_serial_laptop = s.id) as total_dipinjam
    FROM serial_laptop s
    JOIN barang b ON s.id_barang = b.id
    WHERE EXISTS (
        SELECT 1 FROM barang_keluar k WHERE k.id_serial_laptop = s.id AND k.tanggal_kembali_laptop IS NOT NULL AND k.tanggal_kembali_laptop != ''
    )
    ORDER BY b.nama_barang, s.serial_number
");

// Tambahan: Hitung total serial yang sedang dipinjam dan sudah kembali
$total_dipinjam = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS jml FROM barang_keluar WHERE tanggal_kembali_laptop IS NULL OR tanggal_kembali_laptop=''"
))['jml'];
$total_kembali = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS jml FROM barang_keluar WHERE tanggal_kembali_laptop IS NOT NULL AND tanggal_kembali_laptop != ''"
))['jml'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Barang Keluar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: linear-gradient(120deg, #e3f2fd 0%, #fff 80%);
            min-height: 100vh;
        }
        .navbar-brand { font-weight: bold; letter-spacing: 0.5px; }
        .navbar-nav .nav-link.active, .navbar-nav .nav-link:focus, .navbar-nav .nav-link:hover { color: #ffd700 !important; }
        .navbar .dropdown-menu { border-radius: 12px; box-shadow: 0 4px 18px #2c84c277; }
        .navbar .dropdown-item.active, .navbar .dropdown-item:active, .navbar .dropdown-item:focus, .navbar .dropdown-item:hover {
            background: linear-gradient(90deg, #2c84c2 0%, #5ec8e6 100%); color: #fff;
        }
        .dashboard-card { border-radius: 18px; box-shadow: 0 6px 24px #b7e3fe44; background: #fff; border: none; }
        .dashboard-header { font-weight: 700; background: linear-gradient(90deg, #2c84c2 0%, #5ec8e6 100%);
            border-radius: 18px 18px 0 0; display: flex; align-items: center; justify-content: space-between; }
        .dashboard-header b { letter-spacing: 1px; }
        .badge-barang { background: #fff; color: #2c84c2; font-weight: 700; font-size: 1rem; box-shadow: 0 2px 6px #b7e3fe33; }
        .chart-keren-area { background: linear-gradient(120deg, #e3f2fd 0%, #fff 80%);
            border-radius: 18px; box-shadow: 0 4px 24px #2c84c22a, 0 1.5px 7px #b5e4ff33; padding: 26px 16px 8px 16px; margin-bottom: 5px;}
        .grafik-title { color: #2467a3; font-weight: 700; font-size: 1.25rem; margin-bottom: 0.9rem; letter-spacing: 0.5px; text-align: center; text-shadow: 0 1px 0 #e7f7ff;}
        .table-custom thead th { background: linear-gradient(90deg, #2c84c2 0%, #5ec8e6 100%); color: #fff; border: none; vertical-align: middle; text-align: center; }
        .table-custom td, .table-custom th { vertical-align: middle !important; background: #f8faff; font-size: 13px; }
        .table-custom tbody tr:not(:last-child) td { border-bottom: 1.5px dashed #b9dcf4 !important; }
        .table-custom tbody tr:last-child td { border-bottom: none !important; }
        .table-custom .badge.bg-danger { background: #dc3545 !important; }
        .table-custom .badge.bg-success { background: #12a150 !important; }
        .table-custom td.text-center, .table-custom th.text-center { text-align: center !important; }
        .table-custom td.text-nowrap, .table-custom th.text-nowrap { white-space: nowrap !important; }
        .table-custom .btn { min-width: 100px; }
        .table-custom tr:hover td { background: #eaf6fd !important; }
        .modal-shortcut-btn { display: flex; justify-content: center; gap: 18px; margin-top: 30px; }
        .modal-shortcut-btn .btn { font-size: 15px; min-width: 140px; font-weight: 500; }
        .modal-header { background: linear-gradient(90deg, #2c84c2 0%, #5ec8e6 100%); color: #fff; }
        .modal-title { font-size: 17px; font-weight: 600; letter-spacing: 0.7px; }
        .serial-label { font-weight: bold; color: #2c84c2; font-size: 1rem;}
        .barang-label { color: #2467a3; font-size: .97rem; margin-left: 7px; }
        .btn-lastuser { font-size: .98rem; padding: 3px 12px;}
        @media (max-width: 991px) {
            .navbar .navbar-collapse { background: #1976d2; border-radius: 0 0 16px 16px; }
            .navbar-nav .nav-link { padding-left: 1rem !important; }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="bi bi-box-seam"></i> InventoryApp</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle fw-bold <?= basename($_SERVER['PHP_SELF']) == 'barang_keluar.php' ? 'active' : '' ?>" href="#" id="dropdownBarangKeluar" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-box-arrow-right"></i> Barang Keluar
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownBarangKeluar">
                        <li><a class="dropdown-item" href="tambah_keluar.php"><i class="bi bi-plus-lg"></i> Tambah Barang Keluar</a></li>
                        <li><a class="dropdown-item" href="kembali_laptop.php"><i class="bi bi-arrow-counterclockwise"></i> Pengambilan Laptop</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="master_barang.php" class="nav-link fw-bold <?= basename($_SERVER['PHP_SELF']) == 'master_barang.php' ? 'active' : '' ?>">
                        <i class="bi bi-box"></i> Data Barang
                    </a>
                </li>
                <li class="nav-item">
                    <a href="barang_masuk.php" class="nav-link fw-bold <?= basename($_SERVER['PHP_SELF']) == 'barang_masuk.php' ? 'active' : '' ?>">
                        <i class="bi bi-box-arrow-in-down"></i> Barang Masuk
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php" class="nav-link fw-bold <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                        <i class="bi bi-house"></i> Dashboard
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container my-4">
    <div class="mb-3">
        <span class="badge bg-warning text-dark me-2">
            <i class="bi bi-laptop"></i> Total Dipinjam: <b><?= $total_dipinjam ?></b>
        </span>
        <span class="badge bg-success">
            <i class="bi bi-arrow-counterclockwise"></i> Total Kembali: <b><?= $total_kembali ?></b>
        </span>
    </div>
    <div class="dashboard-header text-white fs-5 mb-3 px-3 py-2">
        <b><i class="bi bi-box-arrow-right"></i> Riwayat Barang Keluar (Laptop)</b>
    </div>
    <div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
        <a href="index.php" class="btn btn-secondary mb-2"><i class="bi bi-house-door"></i> Dashboard</a>
        <button type="button" class="btn btn-success mb-2" data-bs-toggle="modal" data-bs-target="#modalTambahKeluar">
          <i class="bi bi-plus-lg"></i> Tambah Barang Keluar
        </button>
        <button type="button" class="btn btn-grafik mb-2" data-bs-toggle="modal" data-bs-target="#grafikModal">
            <i class="bi bi-bar-chart-line"></i> Lihat Grafik Distribusi
        </button>
        <button type="button" class="btn btn-info mb-2" data-bs-toggle="modal" data-bs-target="#serialKembaliModal">
            <i class="bi bi-clock-history"></i> Serial Pernah Dikembalikan
        </button>
    </div>
    <div class="mb-3">
        <span class="me-2">Filter Nama Barang:</span>
        <a href="barang_keluar.php" class="btn btn-outline-primary btn-sm <?= $filter_id_barang==0?'active':'' ?>">Semua</a>
        <?php mysqli_data_seek($daftar_barang, 0); while($b = mysqli_fetch_assoc($daftar_barang)): ?>
            <a href="barang_keluar.php?id_barang=<?= $b['id'] ?>"
               class="btn btn-outline-primary btn-sm <?= $filter_id_barang==$b['id']?'active':'' ?>">
               <?= htmlspecialchars($b['nama_barang']) ?>
            </a>
        <?php endwhile; ?>
    </div>
    <div class="card dashboard-card mb-4">
        <div class="card-body">
            <div class="table-responsive">
  <table class="table table-custom table-hover align-middle mb-0">
    <thead>
      <tr>
        <th class="text-center align-middle" style="width:38px;">No</th>
        <th class="text-center align-middle">Nama Barang</th>
        <th class="text-center align-middle">Serial Laptop</th>
        <th class="align-middle">Nama Karyawan</th>
        <th class="d-none d-md-table-cell align-middle">Email/UserID</th>
        <th class="d-none d-md-table-cell text-center align-middle">No Badge</th>
        <th class="d-none d-lg-table-cell align-middle">Lokasi Kerja</th>
        <th class="d-none d-lg-table-cell text-center align-middle">Serial Charger</th>
        <th class="text-center align-middle">Tanggal Ambil</th>
        <th class="d-none d-md-table-cell text-center align-middle">Tanggal Kembali</th>
        <th class="text-center align-middle">Status</th>
        <th class="d-none d-sm-table-cell text-center align-middle">Aksi</th>
      </tr>
    </thead>
    <tbody>
    <?php
    $no = $offset + 1;
    if ($data && mysqli_num_rows($data) > 0) {
        while($row = mysqli_fetch_assoc($data)):
    ?>
      <tr>
        <td class="text-center"><?= $no++ ?></td>
        <td><?= htmlspecialchars($row['nama_barang']) ?></td>
        <td class="text-center"><?= htmlspecialchars($row['serial_number']) ?></td>
        <td>
            <a  href="javascript:void(0);"
                class="text-primary fw-semibold karyawan-modal-link"
                data-id="<?= $row['id'] ?>"
                data-nama="<?= htmlspecialchars($row['nama_karyawan']) ?>"
                data-bs-toggle="modal"
                data-bs-target="#shortcutModal"
            >
                <?= htmlspecialchars($row['nama_karyawan']) ?>
            </a>
        </td>
        <td class="d-none d-md-table-cell"><?= htmlspecialchars($row['email']) ?></td>
        <td class="d-none d-md-table-cell text-center"><?= htmlspecialchars($row['no_badge']) ?></td>
        <td class="d-none d-lg-table-cell"><?= htmlspecialchars($row['lokasi_kerja']) ?></td>
        <td class="d-none d-lg-table-cell text-center"><?= htmlspecialchars($row['serial_number_charger']) ?></td>
        <td class="text-center"><?= htmlspecialchars($row['tanggal_ambil_laptop']) ?></td>
        <td class="d-none d-md-table-cell text-center"><?= $row['tanggal_kembali_laptop'] ? htmlspecialchars($row['tanggal_kembali_laptop']) : '-' ?></td>
        <td class="text-center">
            <?php if($row['status'] == 'Dipinjam'): ?>
                <span class="badge bg-danger">Dipinjam</span>
            <?php else: ?>
                <span class="badge bg-success">Kembali</span>
            <?php endif; ?>
        </td>
        <td class="d-none d-sm-table-cell text-center">
          <button 
            type="button" 
            class="btn btn-sm btn-info btn-detail-keluar"
            data-id="<?= $row['id'] ?>"
            data-bs-toggle="modal"
            data-bs-target="#modalDetailKeluar">
            <i class="bi bi-eye"></i> Detail
          </button>
        </td>
      </tr>
    <?php endwhile; } else { ?>
      <tr>
        <td colspan="12" class="text-center text-muted">Belum ada data keluar</td>
      </tr>
    <?php } ?>
    </tbody>
  </table>
</div>

<!-- Tambahkan CSS responsive table mobile di bawah style Anda -->
<style>
@media (max-width: 767.98px) {
  .table-responsive .table thead {
    display: none;
  }
  .table-responsive .table tbody tr {
    display: block;
    margin-bottom: 1.2rem;
    border-bottom: 2px solid #dee2e6;
    border-radius: 10px;
    box-shadow: 0 2px 8px #b7e3fe33;
    background: #fff;
  }
  .table-responsive .table tbody td {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-left: 45%;
    position: relative;
    border: none;
    border-bottom: 1px solid #eee;
    min-height: 38px;
    font-size: 15px;
  }
  .table-responsive .table tbody td:before {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 44%;
    white-space: nowrap;
    font-weight: 600;
    color: #1976d2;
    font-size: 14px;
    content: attr(data-label);
  }
  .table-responsive .table tbody tr:last-child td {
    border-bottom: 0;
  }
  .table-responsive .table tbody td .btn,
  .table-responsive .table tbody td .badge {
    font-size: 14px !important;
    padding: 4px 10px !important;
    min-width: 0 !important;
  }
}
</style>
            <!-- PAGINATION NAVIGATION -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-3">
                  <ul class="pagination justify-content-center">
                    <li class="page-item<?= $page <= 1 ? ' disabled' : '' ?>">
                      <a class="page-link" href="?page=<?= $page-1 ?>&id_barang=<?= $filter_id_barang ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                      </a>
                    </li>
                    <?php for($i=1; $i<=$total_pages; $i++): ?>
                      <li class="page-item<?= $page == $i ? ' active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&id_barang=<?= $filter_id_barang ?>"><?= $i ?></a>
                      </li>
                    <?php endfor; ?>
                    <li class="page-item<?= $page >= $total_pages ? ' disabled' : '' ?>">
                      <a class="page-link" href="?page=<?= $page+1 ?>&id_barang=<?= $filter_id_barang ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                      </a>
                    </li>
                  </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Tambah Barang Keluar -->
<div class="modal fade" id="modalTambahKeluar" tabindex="-1" aria-labelledby="modalTambahKeluarLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTambahKeluarLabel">Tambah Barang Keluar</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <form method="post">
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Nama Karyawan</label>
            <input type="text" class="form-control" name="nama_karyawan" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Email/UserID</label>
            <input type="email" class="form-control" name="email" required>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">No Badge</label>
            <input type="text" class="form-control" name="no_badge" required>
          </div>
          <div class="col-md-8 mb-3">
            <label class="form-label">Lokasi Kerja</label>
            <input type="text" class="form-control" name="lokasi_kerja" required>
          </div>
          <div class="col-md-7 mb-3">
            <label class="form-label">Serial Number Laptop</label>
            <select name="serial_number_laptop" class="form-select" required>
              <option value="">-- Pilih Serial Number Laptop --</option>
              <?php
              $serials_modal = mysqli_query($conn, "
                  SELECT s.id, s.serial_number, b.nama_barang
                  FROM serial_laptop s
                  JOIN barang b ON s.id_barang = b.id
                  WHERE s.id NOT IN (
                      SELECT id_serial_laptop FROM barang_keluar WHERE tanggal_kembali_laptop IS NULL
                  )
                  ORDER BY b.nama_barang, s.serial_number
              ");
              while($s = mysqli_fetch_assoc($serials_modal)): ?>
                <option value="<?= $s['id'] ?>">
                  <?= htmlspecialchars($s['serial_number']) ?> (<?= htmlspecialchars($s['nama_barang']) ?>)
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-5 mb-3">
            <label class="form-label">Serial Number Charger</label>
            <input type="text" class="form-control" name="serial_number_charger" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Tanggal Ambil Laptop</label>
            <input type="date" class="form-control" name="tanggal_ambil_laptop" value="<?= date('Y-m-d') ?>" required>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="submit_tambah_keluar" class="btn btn-success"><i class="bi bi-plus-lg"></i> Tambah</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Detail Barang Keluar -->
<div class="modal fade" id="modalDetailKeluar" tabindex="-1" aria-labelledby="modalDetailKeluarLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow">
      <div class="modal-header">
        <h5 class="modal-title" id="modalDetailKeluarLabel">Detail Barang Keluar</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body" id="modalDetailKeluarBody">
        <div class="text-center text-muted"><i class="bi bi-arrow-repeat"></i> Memuat data ...</div>
      </div>
    </div>
  </div>
</div>

<!-- Grafik Modal -->
<div class="modal fade" id="grafikModal" tabindex="-1" aria-labelledby="grafikModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow">
      <div class="modal-header">
        <h5 class="modal-title" id="grafikModalLabel">Grafik Distribusi Laptop Berdasarkan Tanggal Ambil</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="chart-keren-area">
            <div class="grafik-title">Distribusi Pengambilan Laptop</div>
            <canvas id="chartDistribusi" height="80"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Serial Laptop Pernah Dikembalikan -->
<div class="modal fade" id="serialKembaliModal" tabindex="-1" aria-labelledby="serialKembaliModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow">
      <div class="modal-header">
        <h5 class="modal-title" id="serialKembaliModalLabel"><i class="bi bi-clock-history"></i> Serial Laptop yang Pernah Dikembalikan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <?php if(mysqli_num_rows($serials)): ?>
        <ul class="list-group list-group-flush">
            <?php while($row = mysqli_fetch_assoc($serials)): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center" style="border-radius: 12px; margin-bottom:8px;">
                <span>
                    <span class="serial-label"><i class="bi bi-laptop"></i> <?= htmlspecialchars($row['serial_number']) ?></span>
                    <span class="barang-label">(<?= htmlspecialchars($row['nama_barang']) ?>)</span>
                    <span class="ms-2 badge bg-primary">Dipinjam: <?= (int)$row['total_dipinjam'] ?>x</span>
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

<!-- Modal shortcut -->
<div class="modal fade" id="shortcutModal" tabindex="-1" aria-labelledby="shortcutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow">
      <div class="modal-header">
        <h5 class="modal-title" id="shortcutModalLabel">Aksi untuk <span id="modalNamaKaryawan"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="modal-shortcut-btn">
            <a href="#" id="btnTransmittal" target="_blank" class="btn btn-info">Transmittal</a>
            <a href="#" id="btnSerahTerima" target="_blank" class="btn btn-secondary">Serah Terima</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal shortcut untuk aksi pada nama karyawan
    var modal = document.getElementById('shortcutModal');
    var namaSpan = document.getElementById('modalNamaKaryawan');
    var btnTransmittal = document.getElementById('btnTransmittal');
    var btnSerahTerima = document.getElementById('btnSerahTerima');

    document.querySelectorAll('.karyawan-modal-link').forEach(function(link){
        link.addEventListener('click', function(event){
            var id = this.getAttribute('data-id');
            var nama = this.getAttribute('data-nama');
            namaSpan.textContent = nama;
            btnTransmittal.href = 'transmittal.php?id=' + id;
            btnSerahTerima.href = 'serah_terima.php?id=' + id;
        });
    });

    // Detail Barang Keluar (AJAX)
    document.body.addEventListener('click', function(e){
        if (e.target && e.target.classList.contains('btn-detail-keluar')) {
            var id = e.target.getAttribute('data-id');
            var modalBody = document.getElementById('modalDetailKeluarBody');
            modalBody.innerHTML = '<div class="text-center text-muted"><i class="bi bi-arrow-repeat"></i> Memuat data ...</div>';
            fetch('detail_barang_keluar.php?id=' + encodeURIComponent(id))
                .then(response => response.text())
                .then(function(html) {
                    modalBody.innerHTML = html;
                })
                .catch(function() {
                    modalBody.innerHTML = '<div class="alert alert-danger text-center mb-0">Gagal memuat detail.</div>';
                });
        }
    });

    // Chart.js grafik distribusi
    var grafikModal = document.getElementById('grafikModal');
    var chartInstance = null;
    grafikModal.addEventListener('shown.bs.modal', function () {
        var grafikData = <?php
            ksort($grafik);
            echo json_encode([
                'labels' => array_keys($grafik),
                'counts' => array_values($grafik)
            ]);
        ?>;
        var ctx = document.getElementById('chartDistribusi').getContext('2d');
        if(chartInstance) chartInstance.destroy();
        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: grafikData.labels,
                datasets: [{
                    label: 'Jumlah Laptop Diambil',
                    data: grafikData.counts,
                    backgroundColor: grafikData.labels.map((l,idx) => idx%2==0 ? 'rgba(44,132,194,0.86)' : 'rgba(94,200,230,0.86)'),
                    borderColor: '#1e3a5f',
                    borderWidth: 2,
                    borderRadius: 7,
                    hoverBackgroundColor: 'rgba(255,193,7,0.88)',
                    hoverBorderColor: '#ffc107'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: { display: false },
                    tooltip: {
                        enabled: true,
                        usePointStyle: true,
                        callbacks: {
                            label: function(context) {
                                return '  ' + context.dataset.label + ': ' + context.parsed.y;
                            }
                        },
                        backgroundColor: 'rgba(255,255,255,.99)',
                        titleColor: '#2467a3',
                        bodyColor: '#1e3a5f',
                        borderColor: '#2c84c2',
                        borderWidth: 1.5,
                        padding: 12,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        title: { display: true, text: "Tanggal Ambil", color: "#2c84c2", font:{weight:'bold',size:14} },
                        ticks: { font: { size: 13 }, color: "#1e3a5f" },
                        grid: { color: "#b7e3fe44" }
                    },
                    y: {
                        title: { display: true, text: "Jumlah", color: "#2c84c2", font:{weight:'bold',size:14} },
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { size: 13 }, color: "#1e3a5f" },
                        grid: { color: "#b7e3fe44" }
                    }
                },
                animation: { duration: 800, easing: 'easeOutQuart' }
            }
        });
    });
    grafikModal.addEventListener('hidden.bs.modal', function () {
        if(chartInstance) chartInstance.destroy();
        chartInstance = null;
    });

    // Script untuk tombol "Pemakai Terakhir" di dalam modal
    var modalTitle = document.getElementById('modalLastUserLabel');
    var modalBody = document.getElementById('lastUserContent');
    document.body.addEventListener('click', function(e){
        if (e.target && e.target.classList.contains('btn-lastuser')) {
            var serial = e.target.getAttribute('data-serial');
            var id_serial = e.target.getAttribute('data-idserial');
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
        }
    });
});
</script>
</body>
</html>