<?php
include 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil semua data barang_keluar ASC untuk dapatkan nomor urut baris
$all = mysqli_query($conn, "
    SELECT k.id
    FROM barang_keluar k
    ORDER BY k.id ASC
");

$no_urut = 0;
$baris = 1;
while($r = mysqli_fetch_assoc($all)) {
    if ($r['id'] == $id) {
        $no_urut = $baris;
        break;
    }
    $baris++;
}

// Ambil data detail serah terima
$data = mysqli_query($conn, "
    SELECT k.id, s.serial_number, b.nama_barang, b.deskripsi, k.nama_karyawan
    FROM barang_keluar k
    JOIN serial_laptop s ON k.id_serial_laptop = s.id
    JOIN barang b ON s.id_barang = b.id
    WHERE k.id = $id
");
$row = mysqli_fetch_assoc($data);

if (!$row) {
    echo "Data tidak ditemukan.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tanda Terima Serah Terima Laptop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 12mm 8mm 12mm 8mm;
            }
            html, body {
                width: 100%;
                height: 100%;
                background: #fff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .container-custom {
                box-shadow: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100vw !important;
                min-width: unset !important;
                max-width: unset !important;
            }
            .print-btn-area { display: none !important; }
            .table-responsive { box-shadow: none !important; border-radius: 0 !important; }
        }
        html, body {
            min-height: 100%;
            background: #f4f8fb;
        }
        body {
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            color: #222;
            margin: 0;
            padding: 0;
        }
        .container-custom {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 60, 150, 0.08), 0 1.5px 7px #b5e4ff55;
            max-width: 1100px;
            min-width: 860px;
            margin: 24px auto 16px auto;
            padding: 28px 24px 20px 24px;
            position: relative;
        }
        .logo-area {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .logo-area img {
            height: 54px;
            margin-right: 16px;
        }
        .logo-title-block {
            display: flex;
            flex-direction: column;
        }
        .logo-title-main {
            font-size: 27px;
            font-weight: 700;
            color: #3280c7;
            letter-spacing: 1.2px;
            font-family: 'Segoe UI Semibold', Arial, sans-serif;
        }
        .logo-title-sub {
            font-size: 14px;
            color: #3280c7;
            font-weight: 400;
            margin-top: 0;
            letter-spacing: 0.3px;
        }
        .header-title {
            text-align: center;
            font-size: 22px;
            font-weight: 600;
            margin: 12px 0 18px 0;
            letter-spacing: 1.2px;
            color: #17477a;
            text-shadow: 0 1.5px 0 #b9e7fa66;
        }
        .spec-area {
            margin: 12px 0 16px 0;
            background: linear-gradient(90deg, #e3f2fd 0%, #fff 100%);
            border-radius: 9px;
            box-shadow: 0 0 0 1.5px #e3eafc;
            padding: 12px 18px 9px 18px;
        }
        .spec-area .type {
            font-size: 16px;
            font-weight: 600;
            color: #2261aa;
            margin-bottom: 2px;
            letter-spacing: 0.7px;
        }
        .spec-area .specs {
            margin-top: 3px;
        }
        .spec-area .specs .label {
            font-weight: bold;
            color: #2c84c2;
        }
        /* TABLE STYLING */
        .table-responsive {
            background: #f7fafd;
            border-radius: 12px;
            box-shadow: 0 1.5px 8px #e9f2fd44;
            padding: 0;
            margin-bottom: 0;
        }
        .laptop-table {
            border-collapse: collapse;
            width: 100%;
            font-size: 13.2px;
            background: #f6fbff;
        }
        .laptop-table th, .laptop-table td {
            border: 1.2px solid #3280c7;
            padding: 12px 6px;
        }
        .laptop-table th {
            background: linear-gradient(90deg, #3280c7 0%, #5ec8e6 100%);
            color: #fff;
            text-align: center;
            font-size: 13.5px;
            font-weight: 700;
            border-bottom: 2.5px solid #3280c7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .laptop-table .sub-head {
            background: linear-gradient(90deg, #3e98e1 0%, #6ed3f7 100%);
            color: #fff;
            font-weight: 500;
            font-size: 12.2px;
            text-align: center;
            border-bottom: 1.5px solid #3280c7;
            letter-spacing: 0.3px;
            padding: 7px 2px;
        }
        .laptop-table td {
            background: #fff;
            min-width: 82px;
            border-bottom: 1.2px solid #e7f1fa;
            text-align: center;
            font-size: 13.2px;
            height: 38px;
        }
        .laptop-table tr:last-child td {
            border-bottom: 2.2px solid #3280c7;
        }
        .laptop-table td[colspan] {
            background: #eaf6fd;
            font-weight: 500;
            font-size: 13.3px;
        }
        .laptop-table b {
            color: #2f91c7;
            font-weight: 600;
            font-size: 13.3px;
        }
        .print-btn-area {
            margin-top: 32px;
            text-align: right;
        }
        .print-btn-area .btn-print {
            background: linear-gradient(90deg, #3280c7 0%, #5ec8e6 100%);
            color: #fff;
            font-weight: 600;
            border: none;
            padding: 9px 32px;
            border-radius: 8px;
            font-size: 15px;
            box-shadow: 0 2px 8px #b9eafc55;
            cursor: pointer;
            transition: background 0.2s;
        }
        .print-btn-area .btn-print:hover {
            background: linear-gradient(90deg, #2667a4 0%, #38b0d9 100%);
        }
        @media (max-width: 900px) {
            .container-custom {
                padding: 10px 2px 22px 2px;
                min-width: unset;
            }
            .spec-area { padding: 8px 4px 7px 6px;}
        }
    </style>
</head>
<body>
<div class="container-custom">
    <div class="logo-area">
        <img src="pgasol.png" alt="pgnsolution logo">
        <div class="logo-title-block"></div>
    </div>
    <div class="header-title">
        TANDA TERIMA SERAH TERIMA LAPTOP
    </div>
    <div class="spec-area">
        <div class="type"><?= htmlspecialchars($row['nama_barang']) ?></div>
        <div class="specs">
            <span class="label">Details spesifikasi:</span><br>
            <?= nl2br(htmlspecialchars($row['deskripsi'])) ?>
        </div>
    </div>
    <div class="table-responsive">
    <table class="laptop-table">
        <tr>
            <th style="width:60px;">
                No Urut Laptop
                
            </th>
            <th style="width:220px;">NOMOR REGISTRASI LAPTOP</th>
            <th colspan="3">TANDA TERIMA USER</th>
            <th colspan="3">TANDA TERIMA PEKERJA</th>
        </tr>
        <tr>
            <td class="sub-head"></td>
            <td class="sub-head"></td>
            <td class="sub-head">Nama</td>
            <td class="sub-head">Tanggal</td>
            <td class="sub-head">Tanda tangan</td>
            <td class="sub-head">Nama</td>
            <td class="sub-head">Tanggal</td>
            <td class="sub-head">Tanda tangan</td>
        </tr>
        <tr>
            <td><b><?= $no_urut ?></b></td>
            <td style="text-align:left;"><b>Serial Number: <?= htmlspecialchars($row['serial_number']) ?></b></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>
    </div>
    <div class="print-btn-area">
        <button onclick="window.print()" class="btn-print">Print</button>
    </div>
</div>
</body>
</html>