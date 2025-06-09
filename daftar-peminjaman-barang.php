<?php
include 'config.php'; // config.php sekarang mengatur zona waktu

// Fungsi untuk menerjemahkan nama hari dan bulan ke Bahasa Indonesia
function formatTanggalIndonesia($dateString) {
    if (empty($dateString) || $dateString == '-' || $dateString == '0000-00-00 00:00:00') {
        return '-';
    }
    $timestamp = strtotime($dateString);
    $hari = date('w', $timestamp); // 0=Minggu, 1=Senin, dst.
    $bulan = date('n', $timestamp); // 1=Januari, dst.

    $namaHari = [
        'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
    ];
    $namaBulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
        'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    return $namaHari[$hari] . ', ' . date('d', $timestamp) . ' ' . $namaBulan[$bulan] . ' ' . date('Y H:i', $timestamp);
}

// Week offset dari URL
$weekOffset = isset($_GET['weekOffset']) ? intval($_GET['weekOffset']) : 0;

// Ambil tanggal hari ini (akan mengikuti zona waktu WIB)
$today = date('Y-m-d');
$todayTime = strtotime($today);

// Cari hari dalam minggu ini
$dayOfWeek = date('w', $todayTime); // 0 = Minggu, 1 = Senin, ..., 6 = Sabtu

// Hitung Senin minggu ini (jika Minggu, mundur 6 hari ke Senin sebelumnya)
$offsetToMonday = ($dayOfWeek == 0) ? -6 : 1 - $dayOfWeek;
$startOfWeek = strtotime("{$offsetToMonday} days", $todayTime) + ($weekOffset * 7 * 86400);
$endOfWeek = strtotime("+6 days", $startOfWeek);

// Format untuk query SQL
$startDate = date('Y-m-d 00:00:00', $startOfWeek);
$endDate = date('Y-m-d 23:59:59', $endOfWeek);

// Ambil data dari database
// Kolom jenis_barang, dengan_siapa, dan dikembalikan_kepada kini diharapkan berisi teks langsung
// PERHATIAN: Gunakan Prepared Statement untuk mencegah SQL Injection!
$stmt = $conn->prepare("SELECT * FROM peminjaman_barang WHERE tanggal_peminjaman BETWEEN ? AND ? ORDER BY id DESC");
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$query = $stmt->get_result();

$queryInventaris = mysqli_query($conn, "SELECT id, nama, seri FROM inventaris");
$jenisBarangList = [];
while ($rowInventaris = mysqli_fetch_assoc($queryInventaris)) {
    $display_name = $rowInventaris['nama'];
    if (!empty($rowInventaris['seri'])) {
        $display_name = $rowInventaris['nama'] . ' (' . $rowInventaris['seri'] . ')';
    }
    $jenisBarangList[$rowInventaris['id']] = $display_name;
}

$orangList = [
    1 => "Pak Trio", 2 => "Pak Budi", 3 => "Pak Yeyen", 4 => "Pak Aang",
    5 => "Pak Eko", 6 => "Pak Danang", 7 => "Pak Gatot"
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Logbook Peminjaman Mingguan</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('img/solopos7.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed; /* Memastikan gambar latar belakang tetap saat discroll */
            min-height: 100vh;
        }

        /* Menyesuaikan padding untuk container utama agar tidak tertutup navbar fixed-top */
        .container {
            padding-top: 80px; /* Sesuaikan dengan tinggi navbar */
            padding-bottom: 40px; /* Padding di bagian bawah halaman */
        }

        .book-container {
            max-width: 1200px; /* Batasi lebar maksimum agar tidak terlalu lebar di layar besar */
            margin: 0 auto; /* Tengah secara horizontal */
            background: #fff;
            padding: 20px; /* Kurangi padding untuk layar kecil */
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            position: relative;
            border-left: 5px solid #0d6efd;
        }

        @media (min-width: 768px) { /* Padding normal untuk tablet dan desktop */
            .book-container {
                padding: 40px;
            }
        }

        .table thead th {
            background-color: #343a40;
            color: white;
            vertical-align: middle;
            font-size: 0.85rem; /* Sedikit lebih kecil untuk responsivitas */
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Responsive font size untuk judul tanggal */
        h3 small {
            font-weight: normal;
            font-size: calc(0.9rem + 0.3vw); /* Ukuran font responsif */
            color: #6c757d;
            display: block; /* Memastikan sub-judul berada di baris baru */
            margin-top: 5px;
        }

        .week-nav {
            display: flex; /* Menggunakan flexbox */
            justify-content: center; /* Tombol rata tengah */
            gap: 8px; /* Jarak antar tombol */
            flex-wrap: wrap; /* Memungkinkan tombol melipat ke baris baru di layar kecil */
            margin-bottom: 20px;
        }

        .week-nav a {
            flex-grow: 1; /* Biarkan tombol sedikit membesar jika ada ruang */
            max-width: 150px; /* Batasi lebar maksimum per tombol */
        }

        .btn-primary, .btn-outline-secondary, .btn-danger, .btn-success {
            margin: 0; /* Hapus margin individu karena sudah ada gap */
            padding: 8px 16px; /* Padding yang lebih konsisten */
            font-size: 0.9rem; /* Ukuran font yang responsif */
        }

        .btn-group-utilities {
            margin-top: 30px;
            display: flex;
            flex-wrap: wrap; /* Memungkinkan tombol melipat ke baris baru */
            justify-content: center;
            gap: 10px; /* Jarak antar tombol */
        }
        .signature-img {
            max-width: 80px; /* Batasi lebar gambar agar tidak terlalu besar di tabel */
            height: auto;
            border: 1px solid #eee;
            background-color: #fff;
            display: block;
            margin: 0 auto; /* Tengah gambar */
            object-fit: contain; /* Memastikan gambar terlihat penuh */
        }
        /* Penyesuaian untuk sel tabel di layar kecil */
        .table td, .table th {
            white-space: nowrap; /* Mencegah teks di sel tabel melipat */
            font-size: 0.8rem; /* Ukuran font lebih kecil untuk sel */
            padding: 0.5rem; /* Kurangi padding sel */
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="#">IT Solopos</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="home.php">Beranda</a></li>
        <li class="nav-item"><a class="nav-link" href="peminjaman-barang.php">Peminjaman</a></li>
        <li class="nav-item"><a class="nav-link" href="pengembalian-barang.php">Pengembalian</a></li>
        <li class="nav-item"><a class="nav-link active" href="daftar-peminjaman-barang.php">Daftar Logbook</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
    <div class="book-container">
        <h3 class="text-center mb-4">Logbook Peminjaman Barang<br>
            <small>
                <?= formatTanggalIndonesia(date('Y-m-d H:i', $startOfWeek)) . ' - ' . formatTanggalIndonesia(date('Y-m-d H:i', $endOfWeek)) ?>
            </small>
        </h3>

        <div class="week-nav text-center mb-4">
            <a href="?weekOffset=<?= $weekOffset - 1 ?>" class="btn btn-outline-secondary">&laquo; Sebelumnya</a>
            <a href="?weekOffset=0" class="btn btn-outline-primary">Minggu Ini</a>
            <a href="?weekOffset=<?= $weekOffset + 1 ?>" class="btn btn-outline-secondary">Berikutnya &raquo;</a>
        </div>

        <div class="table-responsive"> <table class="table table-bordered table-hover">
                <thead class="text-center">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Jenis Barang</th>
                        <th>Dengan Siapa Meminjam</th>
                        <th>Status</th>
                        <th>Tgl Peminjaman</th>
                        <th>Ttd Peminjaman</th>
                        <th>Tgl Pengembalian</th>
                        <th>Dikembalikan Kepada</th>
                        <th>Ttd Pengembalian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($query)) {
                        $namaYangDitampilkan = htmlspecialchars($row['nama']);
                        $jenisBarang = htmlspecialchars($row['jenis_barang']);
                        $denganSiapa = htmlspecialchars($row['dengan_siapa']);

                        if ($row['status'] == 'dipinjam') {
                            $statusText = 'Masih Dipinjam';
                            $badgeClass = 'warning';
                            $tanggalPengembalian = '-';
                            $dikembalikanKepada = '-'; // Belum dikembalikan, jadi kosong
                            $tandaTanganPengembali = 'Belum Dikembalikan';
                        } else {
                            $statusText = 'Sudah Kembali';
                            $badgeClass = 'success';
                            $tanggalPengembalian = formatTanggalIndonesia($row['tanggal_pengembalian']);
                            $dikembalikanKepada = htmlspecialchars($row['dikembalikan_kepada']);
                            $tandaTanganPengembali = !empty($row['tanda_tangan_pengembalian']) ? "<img src='{$row['tanda_tangan_pengembalian']}' class='signature-img' alt='Tanda Tangan Pengembali'>" : '-'; // Ubah agar tidak menampilkan nama jika tanpa gambar
                        }

                        $tanggalPeminjaman = formatTanggalIndonesia($row['tanggal_peminjaman']);
                        $tandaTanganPeminjam = !empty($row['tanda_tangan_peminjaman']) ? "<img src='{$row['tanda_tangan_peminjaman']}' class='signature-img' alt='Tanda Tangan Peminjam'>" : '-';

                        echo "
                            <tr>
                                <td class='text-center'>{$no}</td>
                                <td>{$namaYangDitampilkan}</td>
                                <td>{$jenisBarang}</td>
                                <td>{$denganSiapa}</td>
                                <td class='text-center'><span class='badge bg-{$badgeClass}'>{$statusText}</span></td>
                                <td>{$tanggalPeminjaman}</td>
                                <td class='text-center'>{$tandaTanganPeminjam}</td>
                                <td>{$tanggalPengembalian}</td>
                                <td>{$dikembalikanKepada}</td>
                                <td class='text-center'>{$tandaTanganPengembali}</td>
                            </tr>
                        ";
                        $no++;
                    }

                    if ($no === 1) {
                        echo "<tr><td colspan='10' class='text-center'>Tidak ada data peminjaman pada minggu ini.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="text-center btn-group-utilities">
            <a href="home.php" class="btn btn-primary">⬅ Kembali ke Home</a>
            <a href="daftar-kalender.php" class="btn btn-primary">📅 Daftar Kalender</a>
            <a href="daftar-inventaris-barang.php" class="btn btn-primary">📦 Daftar Inventaris</a>
            <a href="grafik.php" class="btn btn-primary">📊 Grafik Peminjaman</a>
            <a href="export_excel.php?weekOffset=<?= $weekOffset ?>" class="btn btn-success">📈 Download Excel</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>