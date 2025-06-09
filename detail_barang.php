<?php
include 'koneksi.php';

// Menentukan URL dasar berdasarkan apakah kita di lingkungan lokal atau di hosting
$host = $_SERVER['HTTP_HOST']; // Mendapatkan host saat ini (IP lokal atau domain)

// Tentukan URL dasar yang sesuai
if (strpos($host, 'localhost') !== false || strpos($host, '10.10.11.114') !== false) {
    // Jika di lokal (localhost atau IP lokal)
    $base_url = "http://10.10.11.114/web-peminjaman-barang/";   // Ganti dengan URL lokal Anda
} else {
    // Jika di hosting (domain publik)
    $base_url = "https://www.example.com/web-peminjaman-barang/";   // Ganti dengan domain hosting Anda
}

// Cek apakah ada kode barang yang dikirimkan melalui URL
if (!isset($_GET['kode'])) {
    echo "Kode barang tidak ditemukan.";
    exit;
}

$kode = $_GET['kode'];
$angka_id = intval(substr($kode, 3)); // Ambil angka dari BRG001 jadi 1
$query = $koneksi->query("SELECT * FROM inventaris WHERE id = $angka_id");

if ($query->num_rows == 0) {
    echo "Barang dengan kode $kode tidak ditemukan.";
    exit;
}

$barang = $query->fetch_assoc();
$kode_barang = 'BRG' . str_pad($barang['id'], 3, '0', STR_PAD_LEFT);
$status = $barang["rusak"] ? "Rusak" : "Baik";

// Menghasilkan link detail barang (ini sebenarnya tidak digunakan di halaman ini, tapi bagus untuk ada)
$detail_link = $base_url . "detail_barang.php?kode=" . $kode_barang;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Detail Barang <?= htmlspecialchars($kode_barang) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body.animated-bg {
            background: linear-gradient(-45deg, #f8fbff, #dbeafe, #e0e7ff, #f5f3ff);
            background-size: 400% 400%;
            animation: gradientMove 15s ease infinite;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
        }
        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .page-header {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            color: white;
            padding: 40px 0;
            text-align: center;
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .card {
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }
        .card-img-top {
            /* Menghapus tinggi tetap dan menggunakan aspect-ratio untuk responsivitas yang lebih baik */
            width: 100%;
            aspect-ratio: 16 / 9; /* Rasio aspek gambar 16:9, sesuaikan jika perlu (misal 4 / 3) */
            object-fit: cover; /* Memastikan gambar menutupi area tanpa terdistorsi */
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }
        .btn-custom {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            color: white;
            border-radius: 50px;
        }
        .btn-custom:hover {
            background: linear-gradient(135deg, #6610f2, #0d6efd);
        }
        /* Penyesuaian untuk list-group-item agar lebih responsif */
        .list-group-item.item-detail {
            background: transparent;
            border: none;
            padding-left: 0;
            display: flex; /* Menggunakan flexbox untuk layout */
            flex-wrap: wrap; /* Memungkinkan item melipat ke baris baru */
            align-items: baseline; /* Menyelaraskan teks di baris dasar */
        }
        .list-group-item.item-detail strong {
            /* Sesuaikan lebar label, gunakan col-sm untuk responsivitas */
            flex: 0 0 auto; /* Tidak menyusut, tidak membesar */
            width: 100px; /* Default width for labels */
            padding-right: 10px; /* Jarak antara label dan nilai */
        }
        /* Media query untuk layar kecil */
        @media (max-width: 575.98px) { /* Extra small devices (ponsel) */
            .list-group-item.item-detail strong {
                width: 100%; /* Label mengambil lebar penuh di layar sangat kecil */
                padding-right: 0;
                margin-bottom: 5px; /* Jarak bawah untuk label */
            }
        }
        /* Menggunakan kelas font-size responsif dari Bootstrap */
        .card-title {
            font-size: calc(1.3rem + .6vw); /* Font size responsif */
        }
        @media (min-width: 768px) { /* Medium devices and up */
            .card-title {
                font-size: 1.75rem; /* Ukuran default h4 Bootstrap */
            }
        }
    </style>
</head>
<body class="animated-bg">
    <div class="page-header">
        <h1>📦 Detail Barang</h1>
        <p class="lead mb-0">Informasi lengkap barang berdasarkan kode</p>
    </div>

    <div class="container py-4 py-md-5"> <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6"> <div class="card">
                    <?php if (!empty($barang['gambar']) && file_exists("img/{$barang['gambar']}")): ?>
                        <img src="img/<?= htmlspecialchars($barang['gambar']) ?>" class="card-img-top" alt="<?= htmlspecialchars($barang['nama']) ?>">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/600x320?text=No+Image" class="card-img-top" alt="Gambar tidak tersedia">
                    <?php endif; ?>
                    <div class="card-body">
                        <h4 class="card-title text-center mb-3"><?= htmlspecialchars($barang['nama']) ?> <span class="text-muted">(<?= htmlspecialchars($kode_barang) ?>)</span></h4>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item item-detail"><strong>Spesifikasi:</strong> <span><?= htmlspecialchars($barang['spek']) ?></span></li>
                            <li class="list-group-item item-detail"><strong>Tahun:</strong> <span><?= htmlspecialchars($barang['tahun']) ?></span></li>
                            <li class="list-group-item item-detail"><strong>Seri:</strong> <span><?= htmlspecialchars($barang['seri']) ?></span></li>
                            <li class="list-group-item item-detail"><strong>Stok:</strong> <span><?= htmlspecialchars($barang['stok']) ?></span></li>
                            <li class="list-group-item item-detail"><strong>Kondisi:</strong> <span><?= htmlspecialchars($status) ?></span></li>
                        </ul>
                        <div class="d-grid">
                            <a href="daftar-inventaris-barang.php" class="btn btn-custom">← Kembali</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>