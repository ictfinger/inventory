<?php
include 'config.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$q = mysqli_query($conn, "
    SELECT k.*, s.serial_number, b.nama_barang
    FROM barang_keluar k
    JOIN serial_laptop s ON k.id_serial_laptop = s.id
    JOIN barang b ON s.id_barang = b.id
    WHERE k.id = $id
");
if ($row = mysqli_fetch_assoc($q)) {
?>
    <div class="row g-3 px-1">
        <div class="col-md-6 mb-2">
            <label class="form-label fw-semibold text-primary mb-1">Nama Barang</label>
            <div class="form-control bg-light"><?= htmlspecialchars($row['nama_barang']) ?></div>
        </div>
        <div class="col-md-6 mb-2">
            <label class="form-label fw-semibold text-primary mb-1">Serial Laptop</label>
            <div class="form-control bg-light"><?= htmlspecialchars($row['serial_number']) ?></div>
        </div>
        <div class="col-md-6 mb-2">
            <label class="form-label fw-semibold text-primary mb-1">Nama Karyawan</label>
            <div class="form-control bg-light"><?= htmlspecialchars($row['nama_karyawan']) ?></div>
        </div>
        <div class="col-md-6 mb-2">
            <label class="form-label fw-semibold text-primary mb-1">Email/UserID</label>
            <div class="form-control bg-light"><?= htmlspecialchars($row['email']) ?></div>
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label fw-semibold text-primary mb-1">No Badge</label>
            <div class="form-control bg-light"><?= htmlspecialchars($row['no_badge']) ?></div>
        </div>
        <div class="col-md-8 mb-2">
            <label class="form-label fw-semibold text-primary mb-1">Lokasi Kerja</label>
            <div class="form-control bg-light"><?= htmlspecialchars($row['lokasi_kerja']) ?></div>
        </div>
        <div class="col-md-7 mb-2">
            <label class="form-label fw-semibold text-primary mb-1">Serial Charger</label>
            <div class="form-control bg-light"><?= htmlspecialchars($row['serial_number_charger']) ?></div>
        </div>
        <div class="col-md-5 mb-2">
            <label class="form-label fw-semibold text-primary mb-1">Tanggal Ambil</label>
            <div class="form-control bg-light"><?= htmlspecialchars($row['tanggal_ambil_laptop']) ?></div>
        </div>
        <div class="col-md-6 mb-2">
            <label class="form-label fw-semibold text-primary mb-1">Tanggal Kembali</label>
            <div class="form-control bg-light"><?= htmlspecialchars($row['tanggal_kembali_laptop']) ?: '-' ?></div>
        </div>
        <div class="col-md-6 mb-2">
            <label class="form-label fw-semibold text-primary mb-1">Status</label>
            <div class="form-control bg-light">
                <?php if($row['tanggal_kembali_laptop']): ?>
                    <span class="badge bg-success">Kembali</span>
                <?php else: ?>
                    <span class="badge bg-danger">Dipinjam</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php
} else {
    echo '<div class="alert alert-warning text-center mb-0">Data tidak ditemukan.</div>';
}
?>