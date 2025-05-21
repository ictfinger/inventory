<?php
include 'config.php';

// --------- Tambahan: Proses simpan/edit link dokumen ---------
if (isset($_POST['submit_link_dokumen'])) {
    $id_keluar = intval($_POST['id_keluar']);
    $link_dokumen = trim($_POST['link_dokumen']);
    mysqli_query($conn, "UPDATE barang_keluar SET link_dokumen='$link_dokumen' WHERE id=$id_keluar");
    echo "<script>location.href='barang_keluar.php';</script>";
    exit;
}
// --------- End Tambahan ---------

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
$limit = 20;
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

// Ambil serial yang tidak sedang dipinjam (belum dikembalikan) untuk modal tambah keluar
$serials_modal = mysqli_query($conn, "
    SELECT s.id, s.serial_number, b.nama_barang
    FROM serial_laptop s
    JOIN barang b ON s.id_barang = b.id
    WHERE s.id NOT IN (
        SELECT id_serial_laptop FROM barang_keluar WHERE tanggal_kembali_laptop IS NULL
    )
    ORDER BY b.nama_barang, s.serial_number
");
$serial_count = mysqli_num_rows($serials_modal);
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
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
        .table-custom thead th { background: linear-gradient(90deg, #2c84c2 0%, #5ec8e6 100%);
            color: #fff !important; border-bottom: 2px solid #1976d2; text-transform: uppercase; font-size: 14px; letter-spacing: 0.5px;}
        .table-custom td, .table-custom th {
            vertical-align: middle !important;
            font-size: 13.5px;
            padding-top: 9px;
            padding-bottom: 9px;
        }
        .table-custom tbody tr:not(:last-child) td { border-bottom: 1.5px dashed #b9dcf4 !important; }
        .table-custom tbody tr:last-child td { border-bottom: none !important; }
        .table-custom .badge.bg-danger { background: #dc3545 !important; }
        .table-custom .badge.bg-success { background: #12a150 !important; }
        .table-custom .badge.bg-info { background: #b9eaff !important; color: #1976d2 !important; }
        .table-custom .badge { font-size: 13px; padding: 5px 12px; border-radius: 7px; }
        .table-custom td.text-center, .table-custom th.text-center { text-align: center !important; }
        .table-custom tr:hover td { background: #eaf6fd !important; }
        .modal-shortcut-btn { display: flex; justify-content: center; gap: 18px; margin-top: 30px; }
        .modal-shortcut-btn .btn { font-size: 15px; min-width: 140px; font-weight: 500; }
        .modal-header { background: linear-gradient(90deg, #2c84c2 0%, #5ec8e6 100%); color: #fff; }
        .modal-title { font-size: 17px; font-weight: 600; letter-spacing: 0.7px; }
        .serial-label { font-weight: bold; color: #2c84c2; font-size: 1rem;}
        .barang-label { color: #2467a3; font-size: .97rem; margin-left: 7px; }
        .btn-lastuser { font-size: .98rem; padding: 3px 12px;}
        .btn-linkdokumen { font-size: .98rem; padding: 3px 12px;}
        .modal-embed-iframe { width: 100%; height: 500px; border: none; }
        @media (max-width: 991px) {
            .navbar .navbar-collapse { background: #1976d2; border-radius: 0 0 16px 16px; }
            .navbar-nav .nav-link { padding-left: 1rem !important; }
        }
        @media (max-width: 600px) {
            .modal-embed-iframe { height: 300px; }
        }
    </style>
</head>
<body>
<!-- ... NAVBAR, BADGE, FILTER, HEADER, dsb TETAP SAMA ... -->
<div class="container my-4">
    <!-- ... badge statistik, filter, tombol dsb tetap ... -->
    <div class="card dashboard-card mb-4">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-custom table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center align-middle" style="width:38px;">No</th>
                        <th class="text-center align-middle">Nama Barang</th>
                        <th class="text-center align-middle">Serial Laptop</th>
                        <th class="text-center align-middle">Nama Karyawan</th>
                        <th class="text-center align-middle">Email/UserID</th>
                        <th class="text-center align-middle">No Badge</th>
                        <th class="text-center align-middle">Lokasi Kerja</th>
                        <th class="text-center align-middle">Serial Charger</th>
                        <th class="text-center align-middle">Tanggal Ambil</th>
                        <th class="text-center align-middle">Tanggal Kembali</th>
                        <th class="text-center align-middle">Status</th>
                        <th class="text-center align-middle">Dokumen</th>
                        <th class="text-center align-middle">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = $offset + 1;
                if ($data && mysqli_num_rows($data) > 0) {
                    while($row = mysqli_fetch_assoc($data)):
                ?>
                    <tr>
                        <td class="text-center align-middle"><?= $no++ ?></td>
                        <td class="align-middle"><?= htmlspecialchars($row['nama_barang']) ?></td>
                        <td class="text-center align-middle"><span class="badge bg-info text-dark"><?= htmlspecialchars($row['serial_number']) ?></span></td>
                        <td class="align-middle">
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
                        <td class="align-middle"><?= htmlspecialchars($row['email']) ?></td>
                        <td class="text-center align-middle"><?= htmlspecialchars($row['no_badge']) ?></td>
                        <td class="align-middle"><?= htmlspecialchars($row['lokasi_kerja']) ?></td>
                        <td class="text-center align-middle"><?= htmlspecialchars($row['serial_number_charger']) ?></td>
                        <td class="text-center align-middle"><?= htmlspecialchars($row['tanggal_ambil_laptop']) ?></td>
                        <td class="text-center align-middle"><?= $row['tanggal_kembali_laptop'] ? htmlspecialchars($row['tanggal_kembali_laptop']) : '-' ?></td>
                        <td class="text-center align-middle">
                            <?php if($row['status'] == 'Dipinjam'): ?>
                                <span class="badge bg-danger">Dipinjam</span>
                            <?php else: ?>
                                <span class="badge bg-success">Kembali</span>
                            <?php endif; ?>
                        </td>
                        <!-- Kolom View Dokumen -->
                        <td class="text-center align-middle">
                            <?php
                            $dokumen_link = isset($row['link_dokumen']) && $row['link_dokumen'] ? $row['link_dokumen'] : '';
                            if ($dokumen_link) {
                            ?>
                                <button type="button" class="btn btn-info btn-sm btn-view-dokumen"
                                    data-link="<?= htmlspecialchars($dokumen_link) ?>"
                                    data-karyawan="<?= htmlspecialchars($row['nama_karyawan']) ?>">
                                    <i class="bi bi-file-earmark-text"></i> View
                                </button>
                            <?php } else { ?>
                                <span class="badge bg-secondary">Belum ada</span>
                            <?php } ?>
                            <button type="button" class="btn btn-outline-primary btn-sm btn-linkdokumen"
                                data-id="<?= $row['id'] ?>"
                                data-link="<?= htmlspecialchars($dokumen_link) ?>"
                                data-bs-toggle="modal"
                                data-bs-target="#modalLinkDokumen">
                                <i class="bi bi-link-45deg"></i>
                            </button>
                        </td>
                        <!-- End kolom View Dokumen -->
                        <td class="text-center align-middle">
                          <button 
                            type="button" 
                            class="btn btn-sm btn-outline-info btn-detail-keluar"
                            data-id="<?= $row['id'] ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#modalDetailKeluar">
                            <i class="bi bi-eye"></i>
                          </button>
                        </td>
                    </tr>
                <?php endwhile; } else { ?>
                    <tr><td colspan="13" class="text-center text-muted">Belum ada data keluar</td></tr>
                <?php } ?>
                </tbody>
            </table>
            </div>
            <!-- PAGINATION NAVIGATION TETAP -->
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

<!-- Modal Link Dokumen -->
<div class="modal fade" id="modalLinkDokumen" tabindex="-1" aria-labelledby="modalLinkDokumenLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content shadow">
      <form method="post" autocomplete="off">
        <input type="hidden" name="id_keluar" id="linkdokumen_id_keluar">
        <div class="modal-header">
          <h5 class="modal-title" id="modalLinkDokumenLabel">Tambah/Edit Link Dokumen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="link_dokumen" class="form-label">Link Dokumen (format: https://...)</label>
            <input type="url" class="form-control" name="link_dokumen" id="linkdokumen_link" required placeholder="Tempel link dokumen di sini">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="submit_link_dokumen" class="btn btn-primary">
            <i class="bi bi-save"></i> Simpan Link
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal View Dokumen (iframe) -->
<div class="modal fade" id="modalDokumenViewer" tabindex="-1" aria-labelledby="modalDokumenViewerLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow">
      <div class="modal-header">
        <h5 class="modal-title" id="modalDokumenViewerLabel">Dokumen Viewer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body" style="padding:0;">
        <iframe id="iframeDokumen" class="modal-embed-iframe" src="" allowfullscreen></iframe>
      </div>
    </div>
  </div>
</div>

<!-- Modal, script dan elemen lain TETAP seperti semula (detail, grafik, pemakai terakhir, shortcut, dsb) -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
  $('#modal_serial_select').select2({
    dropdownParent: $('#modalTambahKeluar'),
    placeholder: '-- Pilih Serial Number Laptop --',
    allowClear: true,
    width: '100%'
  });
});

// Modal embed dokumen
document.addEventListener('DOMContentLoaded', function(){
    document.body.addEventListener('click', function(e){
        if (e.target && e.target.classList.contains('btn-view-dokumen')) {
            var link = e.target.getAttribute('data-link');
            var nama = e.target.getAttribute('data-karyawan');
            document.getElementById('iframeDokumen').src = link;
            document.getElementById('modalDokumenViewerLabel').textContent = "Dokumen - " + nama;
            var modal = new bootstrap.Modal(document.getElementById('modalDokumenViewer'));
            modal.show();
        }
    });

    var modalEl = document.getElementById('modalDokumenViewer');
    if (modalEl) {
      modalEl.addEventListener('hidden.bs.modal', function () {
          document.getElementById('iframeDokumen').src = '';
      });
    }

    var modalLink = document.getElementById('modalLinkDokumen');
    if (modalLink) {
      modalLink.addEventListener('show.bs.modal', function (event) {
          var button = event.relatedTarget;
          var id = button.getAttribute('data-id');
          var link = button.getAttribute('data-link') || '';
          document.getElementById('linkdokumen_id_keluar').value = id;
          document.getElementById('linkdokumen_link').value = link;
      });
    }
});
</script>
<!-- ... Script lain tetap ... -->
</body>
</html>