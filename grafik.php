<?php
include 'config.php';

$weekOffset = isset($_GET['weekOffset']) ? intval($_GET['weekOffset']) : 0;

// Hitung awal dan akhir minggu (Senin - Minggu)
$today = date('Y-m-d');
$todayTime = strtotime($today);
$dayOfWeek = date('w', $todayTime);
$offsetToMonday = ($dayOfWeek == 0) ? -6 : 1 - $dayOfWeek;
$startOfWeek = strtotime("{$offsetToMonday} days", $todayTime) + ($weekOffset * 7 * 86400);
$endOfWeek = strtotime("+6 days", $startOfWeek);

$labels = [];
$peminjamanCounts = [];
$pengembalianCounts = [];

$hariIndo = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

for ($i = 0; $i < 7; $i++) {
    $timestamp = strtotime("+$i days", $startOfWeek);
    $tanggal = date('Y-m-d', $timestamp);
    $hari = $hariIndo[date('w', $timestamp)];
    $label = $hari . ', ' . date('d M', $timestamp);
    $labels[] = $label;

    // Jumlah peminjaman per hari (berdasarkan tanggal_peminjaman)
    $queryPeminjaman = "SELECT COUNT(*) as total FROM peminjaman_barang 
                        WHERE DATE(tanggal_peminjaman) = '$tanggal'";
    $res1 = mysqli_query($conn, $queryPeminjaman);
    $peminjamanCounts[] = (int)(mysqli_fetch_assoc($res1)['total'] ?? 0);

    // Jumlah pengembalian per hari yang benar-benar dikembalikan
    $queryPengembalian = "SELECT COUNT(*) as total FROM peminjaman_barang 
                          WHERE DATE(tanggal_pengembalian) = '$tanggal' AND status = 'Dikembalikan'";
    $res2 = mysqli_query($conn, $queryPengembalian);
    $pengembalianCounts[] = (int)(mysqli_fetch_assoc($res2)['total'] ?? 0);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Grafik Peminjaman dan Pengembalian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-image: url('img/solopos8.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .chart-container {
            background: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 950px;
            margin: 50px auto;
        }
        h3 small {
            display: block;
            font-size: 16px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="chart-container">
        <h3 class="text-center">Grafik Peminjaman & Pengembalian Barang<br>
            <small><?= date('d M', $startOfWeek) ?> - <?= date('d M Y', $endOfWeek) ?></small>
        </h3>

        <canvas id="logbookChart" height="100"></canvas>

        <div class="text-center mt-4">
            <a href="?weekOffset=<?= $weekOffset - 1 ?>" class="btn btn-outline-secondary btn-sm">&laquo; Minggu Sebelumnya</a>
            <a href="?weekOffset=0" class="btn btn-outline-primary btn-sm">Minggu Ini</a>
            <a href="?weekOffset=<?= $weekOffset + 1 ?>" class="btn btn-outline-secondary btn-sm">Minggu Berikutnya &raquo;</a>
        </div>

        <div class="text-center mt-4">
            <a href="daftar-peminjaman-barang.php" class="btn btn-primary"> ⬅ Kembali ke Logbook</a>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('logbookChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [
                {
                    label: 'Peminjaman',
                    data: <?= json_encode($peminjamanCounts) ?>,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.2)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 5,
                    pointHoverRadius: 7
                },
                {
                    label: 'Pengembalian',
                    data: <?= json_encode($pengembalianCounts) ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Jumlah Peminjaman dan Pengembalian per Hari',
                    font: { size: 18 }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                },
                legend: {
                    display: true,
                    position: 'bottom'
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            },
            scales: {
                y: {
                    beginAtZero: true,
                    precision: 0,
                    stepSize: 1
                }
            }
        }
    });
</script>
</body>
</html>
