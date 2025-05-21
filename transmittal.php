<?php
include 'config.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$data = mysqli_query($conn, "
    SELECT k.*, s.serial_number, b.nama_barang
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

$to = $row['lokasi_kerja'];
$attn = $row['nama_karyawan'];
$date = date('d/m/Y', strtotime($row['tanggal_ambil_laptop']));
$ref_no = "TRS-" . str_pad($row['id'], 4, "0", STR_PAD_LEFT);
$laptop = $row['nama_barang'];
$serial = $row['serial_number'];
$charger = $row['serial_number_charger'];
$tas = "Tas Laptop";
$issuer = "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transmittal</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 15mm 18mm 15mm;
        }
        html, body {
            height: 100%;
            padding: 0;
            margin: 0;
        }
        body {
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #222;
            background: #f8f9fa;
        }
        .transmittal-container {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
        }
        .transmittal-content {
            border: 2px solid #000;
            border-radius: 14px;
            box-shadow: 0 4px 22px rgba(44,132,194,0.11);
            padding: 24px 18px 18px 18px;
            margin-top: 20px;
            margin-bottom: 18px;
            min-height: 240mm;
            display: flex;
            flex-direction: column;
        }
        .header { text-align: center; }
        .header img.logo-main { 
            width: 240px;
            display: block;
            margin: 0 auto 2px auto;
        }
        .header .title { font-size: 22px; font-weight: bold; letter-spacing: 1px; color: #2c84c2; }
        .header .subtitle { font-size: 9px; margin-bottom: 2px; }
        .header .address { font-size: 11px; margin-bottom: 7px; }
        .info-table { width: 100%; margin-bottom: 10px; }
        .info-table td { vertical-align: top; font-size: 12px; }
        .to-box { border: 1px solid #2c84c2; background: #f8faff; border-radius: 6px; min-height: 40px; padding: 4px 8px; margin-bottom: 4px; width: 48%; }
        .right-info { width: 48%; float: right; }
        .right-info td { padding: 1px 4px; }
        .clear { clear: both; }
        /* --- TABEL KEREN --- */
        .desc-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            margin-top: 12px;
            table-layout: fixed;
            background: #fefefe;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1.5px 8px rgba(44,132,194,0.11);
        }
        .desc-table th, .desc-table td {
            padding: 12px 8px;
            font-size: 13px;
            vertical-align: top;
        }
        .desc-table th {
            background: linear-gradient(90deg, #2c84c2 0%, #5ec8e6 100%);
            color: #fff;
            font-weight: 600;
            letter-spacing: .5px;
            border-bottom: 2px solid #2c84c2;
            border-top: none;
            border-left: none;
            border-right: none;
        }
        .desc-table td {
            background: #f8faff;
            border: none;
            border-bottom: 1.5px dashed #b9dcf4;
            transition: background 0.2s;
        }
        .desc-table tr:last-child td {
            border-bottom: none !important;
        }
        .desc-table .qty { text-align: center; width: 60px; font-weight: 600; color: #2c84c2;}
        .desc-table .remark { text-align: center; width: 110px; font-weight: 500;}
        .desc-table .desc { width: 350px; }
        .desc-table .desc span {
            color: #444;
            font-size: 11px;
        }
        .desc-table .remark {
            color: #12a150;
            background: #e7faf2;
            border-radius: 4px;
            letter-spacing: 1px;
        }
        .desc-table .filler-row td {
            height: 340px;
            padding: 0;
            background: transparent;
            border-bottom: none;
        }
        .footer-note { font-size: 11px; margin-top: 15px; font-style: italic; color: #888; }
        .sign-table { width: 100%; margin-top: 40px; }
        .sign-table td { font-size: 12px; text-align: center; padding: 12px 0 0 0; }
        .sign-name { padding-top: 20px; font-weight: normal; }
        @media print {
            .hide-print { display: none; }
            html, body, .transmittal-container {
                width: 210mm !important;
                min-height: 297mm !important;
                background: #fff !important;
            }
            .transmittal-content {
                margin: 0 !important;
                box-shadow: none !important;
            }
        }
        /* Hover effect */
        .desc-table tr:not(.filler-row):hover td {
            background: #e8f5fd;
        }
    </style>
</head>
<body>
<div class="transmittal-container">
    <div class="transmittal-content">
        <div class="header">
            <img src="pgasol.png" alt="pgnsolution logo" class="logo-main"/>
            <div class="subtitle" style="font-size:11px;font-weight:400;letter-spacing:0;font-style:italic;color:#6a6a6a;">
                
            </div><br>
            <div class="address">
                Jl. Aman No.02, Pematang Pudu, Kec. Mandau<br>
                Kabupaten Bengkalis, Riau 28784
            </div>
        </div>
        <br>
        <br>
        <table class="info-table">
            <tr>
                <td style="width:100%;vertical-align:top;">
                    <div class="to-box">
                        <b>To:</b> <?= htmlspecialchars($to) ?>
                    </div>
                </td>
                <td class="right-info" style="width:200%;">
                    <table style="width:100%;">
                        <tr>
                            <td style="width:30%;">ATTN</td>
                            <td>: <?= htmlspecialchars($attn) ?></td>
                        </tr>
                        <tr>
                            <td>Date</td>
                            <td>: <?= htmlspecialchars($date) ?></td>
                        </tr>
                        <tr>
                            <td>Ref. No</td>
                            <td>: </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <div class="clear"></div>
        <div style="margin:12px 0 8px 0;"><b>We herewith transmit the following :</b></div>
        <table class="desc-table">
            <tr>
                <th class="qty">Quantity</th>
                <th class="desc">Description</th>
                <th class="remark">Remark</th>
            </tr>
            <tr>
                <td class="qty">1</td>
                <td class="desc">
                    <?= htmlspecialchars($laptop) ?><br>
                    <span>S/N : <?= htmlspecialchars($serial) ?></span>
                </td>
                <td class="remark">New</td>
            </tr>
            <tr>
                <td class="qty">1</td>
                <td class="desc">Charger Laptop<?= $charger ? "<br><span>S/N : ".htmlspecialchars($charger)."</span>" : "" ?></td>
                <td class="remark">New</td>
            </tr>
            <tr>
                <td class="qty">1</td>
                <td class="desc">Tas Laptop</td>
                <td class="remark">New</td>
            </tr>
            <!-- Filler row to make the table long for A4 -->
            <tr class="filler-row">
                <td>&nbsp;</td>
                <td></td>
                <td></td>
            </tr>
        </table>
        <div class="footer-note">
            Please sign and return one copy
        </div>
        <br>
        <br>
        <br>
        <br>
        <table class="sign-table">
            <tr>
                <td style="width:50%;">
                    <hr style="border:0;border-top:1px solid #000;width:80%;">
                    
                    <span style="font-size:11px;">ISSUED BY</span>
                </td>
                <td style="width:50%;">
                    <hr style="border:0;border-top:1px solid #000;width:80%;">
                    <span style="font-size:11px;">RECEIVED BY</span>
                </td>
            </tr>
        </table>
        <div class="hide-print" style="text-align:right;margin-top:20px;">
            <button onclick="window.print()" class="btn btn-primary btn-sm">Print</button>
            <a href="barang_keluar.php" class="btn btn-secondary btn-sm">Kembali</a>
        </div>
    </div>
</div>
</body>
</html>