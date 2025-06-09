<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Peminjaman IT Solopos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        /* Variabel CSS untuk kemudahan pengelolaan warna */
        :root {
            --bs-primary: #0d6efd;
            --bs-success: #198754;
            --bs-warning: #ffc107;
            --bs-purple: #6610f2;
            --bs-indigo: #6f42c1;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-image: url('img/solopos10.jpg');
            background-size: cover; /* Pastikan gambar latar menyesuaikan ukuran layar */
            background-position: center;
            background-attachment: fixed; /* Gambar tetap saat discroll */
            min-height: 100vh;
            overflow-x: hidden; /* Mencegah scroll horizontal yang tidak diinginkan */
            position: relative;
        }

        .dashboard-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2.5rem; /* Menggunakan rem untuk padding yang adaptif */
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.35);
            margin-top: 4rem; /* Menggunakan rem untuk margin atas */
            margin-bottom: 4rem; /* Menggunakan rem untuk margin bawah */
            backdrop-filter: blur(5px);
            position: relative;
            z-index: 1;
        }

        .navbar {
            background: linear-gradient(45deg, var(--bs-primary), var(--bs-purple));
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            /* Pastikan navbar tetap di atas */
            position: sticky; /* atau fixed-top */
            top: 0;
            z-index: 1020; /* Z-index lebih tinggi dari konten */
        }

        .card {
            transition: all 0.4s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            position: relative;
            height: 100%; /* Penting untuk kartu yang tingginya konsisten */
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
        }

        .card .card-body i {
            transition: transform 0.3s ease;
            font-size: 3.5rem; /* Ukuran ikon menggunakan rem agar adaptif */
            margin-bottom: 1rem;
        }

        .card:hover .card-body i {
            transform: scale(1.15);
        }

        /* Perbaikan kontras untuk kartu warning */
        .card.bg-warning {
            background-color: var(--bs-warning) !important;
            color: #343a40 !important; /* Teks gelap agar kontras dengan kuning terang */
        }

        .btn-dashboard {
            background: linear-gradient(135deg, var(--bs-indigo), var(--bs-primary));
            color: #fff;
            border-radius: 30px;
            padding: 0.75rem 1.5rem; /* Menggunakan rem untuk padding tombol */
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .btn-dashboard:hover {
            background: linear-gradient(135deg, var(--bs-primary), var(--bs-indigo));
            color: #fff;
        }

        /* Bubble Animation */
        .bubble {
            position: absolute;
            bottom: -100px;
            background: linear-gradient(45deg, #ff0000, #ff7f00, #ffff00, #00ff00, #0000ff, #4b0082, #9400d3);
            background-size: 400% 400%;
            border-radius: 50%;
            animation: rise 20s infinite ease-in-out, colorChange 8s infinite linear;
            z-index: 0;
            opacity: 0.7;
        }

        @keyframes rise {
            0% { transform: translateY(0) scale(1); opacity: 0.7; }
            100% { transform: translateY(-1200px) scale(1.5); opacity: 0; }
        }

        @keyframes colorChange {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Variasi ukuran dan posisi gelembung (default untuk layar besar) */
        .bubble:nth-child(1) { width: 120px; height: 120px; left: 5%; animation-duration: 18s; animation-delay: 0s;}
        .bubble:nth-child(2) { width: 130px; height: 130px; left: 15%; animation-duration: 20s; animation-delay: 2s;}
        .bubble:nth-child(3) { width: 150px; height: 150px; left: 25%; animation-duration: 22s; animation-delay: 4s;}
        .bubble:nth-child(4) { width: 160px; height: 160px; left: 35%; animation-duration: 24s; animation-delay: 6s;}
        .bubble:nth-child(5) { width: 140px; height: 140px; left: 45%; animation-duration: 26s; animation-delay: 8s;}
        .bubble:nth-child(6) { width: 170px; height: 170px; left: 55%; animation-duration: 28s; animation-delay: 10s;}
        .bubble:nth-child(7) { width: 180px; height: 180px; left: 65%; animation-duration: 30s; animation-delay: 12s;}
        .bubble:nth-child(8) { width: 200px; height: 200px; left: 75%; animation-duration: 32s; animation-delay: 14s;}
        .bubble:nth-child(9) { width: 210px; height: 210px; left: 85%; animation-duration: 34s; animation-delay: 16s;}
        .bubble:nth-child(10) { width: 220px; height: 220px; left: 95%; animation-duration: 36s; animation-delay: 18s;}
        
        /* Media Queries untuk Responsivitas */

        /* Layar Extra Small (Potret HP, di bawah 576px) */
        @media (max-width: 575.98px) {
            .dashboard-container {
                padding: 1.5rem; /* Padding lebih kecil */
                margin-top: 3rem;
                margin-bottom: 3rem;
            }
            .text-center h1 {
                font-size: 2rem; /* Ukuran judul lebih kecil */
            }
            .text-center p {
                font-size: 0.9rem; /* Ukuran paragraf lebih kecil */
            }
            .card .card-body i {
                font-size: 3rem; /* Ikon sedikit lebih kecil */
            }
            /* Sembunyikan sebagian besar gelembung di HP */
            .bubble:nth-child(n+4) {
                display: none;
            }
            .bubble {
                width: 60px !important; /* Ukuran gelembung lebih kecil */
                height: 60px !important;
                animation-duration: 12s !important; /* Animasi lebih cepat */
                opacity: 0.5;
            }
            .bubble:nth-child(1) { left: 15%; }
            .bubble:nth-child(2) { left: 50%; animation-delay: 4s;}
            .bubble:nth-child(3) { left: 85%; animation-delay: 8s;}
        }

        /* Layar Small (HP Landscape, Tablet Potret, 576px - 767.98px) */
        @media (min-width: 576px) and (max-width: 767.98px) {
            .dashboard-container {
                padding: 2rem; /* Padding menengah */
            }
            .text-center h1 {
                font-size: 2.5rem; /* Ukuran judul menengah */
            }
            .text-center p {
                font-size: 1rem;
            }
            .card .card-body i {
                font-size: 3.25rem; /* Ikon menengah */
            }
            /* Kurangi jumlah gelembung di Tablet Potret */
            .bubble:nth-child(n+7) {
                display: none;
            }
            .bubble {
                width: 90px !important;
                height: 90px !important;
                animation-duration: 18s !important;
                opacity: 0.6;
            }
        }

        /* Layar Medium (Tablet Landscape, Desktop Kecil, 768px - 991.98px) */
        @media (min-width: 768px) and (max-width: 991.98px) {
            .col-md-6 { /* Kartu akan menjadi 2 kolom per baris */
                flex: 0 0 auto;
                width: 50%;
            }
            .dashboard-container {
                padding: 2.5rem;
            }
            .text-center h1 {
                font-size: 3rem;
            }
            .text-center p {
                font-size: 1.1rem;
            }
            .card .card-body i {
                font-size: 3.5rem;
            }
            /* Jumlah gelembung lebih banyak dari HP */
            .bubble:nth-child(n+10) {
                display: none;
            }
            .bubble {
                width: 100px !important;
                height: 100px !important;
                animation-duration: 22s !important;
            }
        }

        /* Layar Large (Desktop, 992px - 1199.98px) dan Extra Large (1200px ke atas) */
        /* Default CSS yang sudah ada akan berlaku, karena ini adalah ukuran dasar */
        @media (min-width: 992px) {
            .col-lg-4 { /* Kartu akan menjadi 3 kolom per baris */
                flex: 0 0 auto;
                width: 33.33333333%;
            }
            .text-center h1 {
                font-size: 3.5rem; /* Ukuran judul standar desktop */
            }
            .text-center p {
                font-size: 1.25rem; /* Ukuran paragraf standar desktop */
            }
            .card .card-body i {
                font-size: 4rem; /* Ukuran ikon standar desktop */
            }
        }
    </style>
</head>
<body>

    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-box"></i> IT Solopos</a>
        </div>
    </nav>

    <main class="container dashboard-container">
        <div class="text-center mb-5">
            <h1 class="fw-bold display-5">Dashboard Peminjaman Inventaris Barang</h1>
            <p class="text-muted fs-5">Selamat datang di sistem peminjaman barang IT Solopos. Silakan pilih menu di bawah ini untuk melanjutkan.</p>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-12 col-sm-6 col-md-6 col-lg-4"> <a href="peminjaman-barang.php" class="text-decoration-none">
                    <div class="card bg-success text-white">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center">
                            <i class="bi bi-arrow-down-circle-fill"></i>
                            <h5 class="card-title fw-bold">Peminjaman</h5>
                            <p class="card-text text-white-50">Lakukan  peminjaman barang IT.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                <a href="pengembalian-barang.php" class="text-decoration-none">
                    <div class="card bg-primary text-white">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center">
                            <i class="bi bi-arrow-up-circle-fill"></i>
                            <h5 class="card-title fw-bold">Pengembalian</h5>
                            <p class="card-text text-white-50">Proses pengembalian barang yang telah dipinjam.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                <a href="daftar-peminjaman-barang.php" class="text-decoration-none">
                    <div class="card bg-warning text-dark">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center">
                            <i class="bi bi-list-check"></i>
                            <h5 class="card-title fw-bold">Daftar Log</h5>
                            <p class="card-text text-muted">Lihat riwayat dan status peminjaman.</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>