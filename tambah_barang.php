<?php
include 'config.php';
if(isset($_POST['submit'])){
    $nama = $_POST['nama_barang'];
    $desc = $_POST['deskripsi'];
    mysqli_query($conn, "INSERT INTO barang (nama_barang, deskripsi) VALUES ('$nama', '$desc')");
    header("Location: barang.php");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tambah Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2>Tambah Barang</h2>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Nama Barang</label>
            <input type="text" name="nama_barang" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="deskripsi" class="form-control"></textarea>
        </div>
        <button type="submit" name="submit" class="btn btn-success">Simpan</button>
        <a href="barang.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>