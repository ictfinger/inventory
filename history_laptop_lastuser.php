<?php
include 'config.php';
header('Content-Type: application/json');

$id_serial = isset($_GET['id_serial']) ? intval($_GET['id_serial']) : 0;

if ($id_serial > 0) {
    // Ambil pemakai terakhir berdasarkan tanggal_ambil_laptop terakhir (yang sudah dikembalikan)
    $result = mysqli_query($conn, "
        SELECT k.nama_karyawan, k.tanggal_ambil_laptop, k.tanggal_kembali_laptop
        FROM barang_keluar k
        WHERE k.id_serial_laptop = $id_serial AND k.tanggal_kembali_laptop IS NOT NULL AND k.tanggal_kembali_laptop != ''
        ORDER BY k.tanggal_ambil_laptop DESC, k.id DESC
        LIMIT 1
    ");
    if($row = mysqli_fetch_assoc($result)) {
        echo json_encode([
            'nama_karyawan' => $row['nama_karyawan'],
            'tanggal_ambil_laptop' => $row['tanggal_ambil_laptop'],
            'tanggal_kembali_laptop' => $row['tanggal_kembali_laptop']
        ]);
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
?>