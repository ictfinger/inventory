<?php
include 'config.php';

// Ambil data bandwidth (misal 30 hari terakhir)
$result = mysqli_query($conn, "SELECT tanggal, download_mbps, upload_mbps FROM bandwidth_log ORDER BY tanggal ASC");
$labels = [];
$downloads = [];
$uploads = [];
while($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['tanggal'];
    $downloads[] = (float)$row['download_mbps'];
    $uploads[] = (float)$row['upload_mbps'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Grafik Pemakaian Bandwidth</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .chart-area {
            background: linear-gradient(120deg, #e3f2fd 0%, #fff 100%);
            border-radius: 16px;
            box-shadow: 0 4px 24px #2c84c22a, 0 1.5px 7px #b5e4ff33;
            padding: 28px 20px 15px 20px;
            margin: 40px auto;
            max-width: 700px;
        }
    </style>
</head>
<body class="bg-light">
<div class="container">
    <div class="chart-area">
        <h3 class="mb-4 text-center text-primary">Grafik Pemakaian Bandwidth</h3>
        <canvas id="bandwidthChart" height="80"></canvas>
    </div>
</div>
<script>
const ctx = document.getElementById('bandwidthChart').getContext('2d');
const bandwidthChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [
            {
                label: 'Download (Mbps)',
                data: <?= json_encode($downloads) ?>,
                borderColor: 'rgba(44,132,194,1)',
                backgroundColor: 'rgba(44,132,194,0.12)',
                fill: true,
                tension: 0.2
            },
            {
                label: 'Upload (Mbps)',
                data: <?= json_encode($uploads) ?>,
                borderColor: 'rgba(94,200,230,1)',
                backgroundColor: 'rgba(94,200,230,0.12)',
                fill: true,
                tension: 0.2
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: {
                mode: 'index',
                intersect: false,
                backgroundColor: '#fff',
                borderColor: '#2c84c2',
                borderWidth: 1.5,
                titleColor: '#2467a3',
                bodyColor: '#1e3a5f',
                padding: 12,
                displayColors: true
            }
        },
        interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: false
        },
        scales: {
            x: {
                title: { display: true, text: "Tanggal", color: "#2c84c2", font:{weight:'bold',size:14} },
                ticks: { color: "#1e3a5f" },
                grid: { color: "#b7e3fe44" }
            },
            y: {
                title: { display: true, text: "Mbps", color: "#2c84c2", font:{weight:'bold',size:14} },
                beginAtZero: true,
                ticks: { color: "#1e3a5f" },
                grid: { color: "#b7e3fe44" }
            }
        }
    }
});
</script>
</body>
</html>