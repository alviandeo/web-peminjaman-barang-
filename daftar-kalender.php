<?php
include 'config.php';

// Fungsi untuk menerjemahkan nama hari dan bulan ke Bahasa Indonesia
// Fungsi ini tidak dipakai langsung di file ini, tapi ada di file lain yang terkait (seperti daftar-peminjaman-barang.php)
// Saya tetap menyertakannya untuk konsistensi meskipun tidak dipanggil di sini.
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


$bulan = isset($_GET['bulan']) ? intval($_GET['bulan']) : date('n');
$tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : date('Y');
$filterNama = isset($_GET['nama']) ? mysqli_real_escape_string($conn, $_GET['nama']) : '';
$filterJenisBarang = isset($_GET['jenis_barang']) ? mysqli_real_escape_string($conn, $_GET['jenis_barang']) : '';

$jumlahHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
$startDayOfWeek = date('w', strtotime("$tahun-$bulan-01"));

$startDateOfMonth = "$tahun-$bulan-01 00:00:00";
$endDateOfMonth = "$tahun-$bulan-$jumlahHari 23:59:59";

$queryStr = "SELECT * FROM peminjaman_barang
             WHERE (
                 (tanggal_peminjaman BETWEEN '$startDateOfMonth' AND '$endDateOfMonth')
                 OR
                 (tanggal_pengembalian IS NOT NULL AND tanggal_pengembalian BETWEEN '$startDateOfMonth' AND '$endDateOfMonth')
                 OR
                 (tanggal_peminjaman < '$startDateOfMonth' AND (tanggal_pengembalian IS NULL OR tanggal_pengembalian > '$endDateOfMonth'))
             )";

if (!empty($filterNama)) {
    $queryStr .= " AND nama LIKE '%$filterNama%'";
}
if (!empty($filterJenisBarang)) {
    $queryStr .= " AND jenis_barang = '$filterJenisBarang'";
}

$queryStr .= " ORDER BY tanggal_peminjaman ASC";

$query = mysqli_query($conn, $queryStr);

$dataPeminjaman = [];
$totalPeminjamanEvent = 0;

while ($row = mysqli_fetch_assoc($query)) {
    $timestampPinjam = strtotime($row['tanggal_peminjaman']);
    $tanggalPinjam = date('j', $timestampPinjam);
    $bulanPinjam = date('n', $timestampPinjam);
    $tahunPinjam = date('Y', $timestampPinjam);

    $namaTampil = htmlspecialchars($row['nama']); // Nama ini akan otomatis berupa nama pengembali jika sudah dikembalikan

    if ($bulanPinjam == $bulan && $tahunPinjam == $tahun) {
        $dataPeminjaman[$tanggalPinjam][] = array_merge($row, [
            'tipe_event' => 'pinjam',
            'status_tampilan' => $row['status'],
            'nama_tampilan' => $namaTampil
        ]);
        $totalPeminjamanEvent++;
    }

    if ($row['status'] == 'dikembalikan' && !empty($row['tanggal_pengembalian'])) {
        $timestampKembali = strtotime($row['tanggal_pengembalian']);
        $tanggalKembali = date('j', $timestampKembali);
        $bulanKembali = date('n', $timestampKembali);
        $tahunKembali = date('Y', $timestampKembali);

        if ($bulanKembali == $bulan && $tahunKembali == $tahun) {
            if (!($tanggalPinjam == $tanggalKembali && $bulanPinjam == $bulanKembali && $tahunPinjam == $tahunKembali)) {
                $dataPeminjaman[$tanggalKembali][] = array_merge($row, [
                    'tipe_event' => 'kembali',
                    'status_tampilan' => 'dikembalikan',
                    'nama_tampilan' => $namaTampil
                ]);
                $totalPeminjamanEvent++;
            }
        }
    }
}

$bulanList = [
    1 => "Januari", 2 => "Februari", 3 => "Maret", 4 => "April",
    5 => "Mei", 6 => "Juni", 7 => "Juli", 8 => "Agustus",
    9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember"
];

$jenisBarangStaticList = [
    "Asus ROG", "TV", "Acer", "Monitor", "Mouse",
    "Blower", "Keyboard", "Obeng", "PC", "Lenovo"
];

$queryBarang = "SELECT jenis_barang, COUNT(*) as jumlah
                 FROM peminjaman_barang
                 GROUP BY jenis_barang
                 ORDER BY jumlah DESC
                 LIMIT 5";
$resultBarang = mysqli_query($conn, $queryBarang);
$topBarang = [];
while ($row = mysqli_fetch_assoc($resultBarang)) {
    $topBarang[] = "{$row['jenis_barang']} ({$row['jumlah']}x)";
}

$queryNama = "SELECT nama, COUNT(*) as jumlah
              FROM peminjaman_barang
              GROUP BY nama
              ORDER BY jumlah DESC
              LIMIT 5";
$resultNama = mysqli_query($conn, $queryNama);
$topNama = [];
while ($row = mysqli_fetch_assoc($resultNama)) {
    $topNama[] = "{$row['nama']} ({$row['jumlah']}x)";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Peminjaman Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('img/solopos5.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .container {
            background-color: rgba(255,255,255,0.95);
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            flex-grow: 1;
            margin-top: 20px;
            margin-bottom: 20px;
            padding-top: 40px;
        }
        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }
        .header {
            font-weight: bold;
            text-align: center;
            background-color: #343a40;
            color: white;
            padding: 10px 0;
            border-radius: 10px;
        }
        .day {
            border: 1px solid #dee2e6;
            min-height: 130px;
            padding: 8px;
            background: #ffffff;
            position: relative;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .day:hover {
            transform: scale(1.03);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        .day .date {
            font-weight: bold;
            font-size: 16px;
            color: #333;
            margin-bottom: 6px;
        }
        .item {
            padding: 4px 6px;
            font-size: 12px;
            border-radius: 6px;
            margin-bottom: 3px;
            cursor: pointer;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: background-color 0.2s, box-shadow 0.2s;
            line-height: 1.3;
        }
        .item:hover {
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .form-select, .form-control, .btn {
            border-radius: 8px;
        }
        .modal-body ul {
            padding-left: 0;
            list-style: none;
        }
        .modal-body li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .modal-body li:last-child {
            border-bottom: none;
        }
        .modal-body strong {
            display: inline-block;
            min-width: 120px;
        }
        .navbar {
            padding: 0.5rem 1rem;
        }
        .navbar-brand {
            font-size: 1.5rem;
        }
        .nav-link {
            font-weight: 500;
        }
    </style>
    <script>
        // Memperbarui fungsi showDetail untuk menerima 2 parameter tambahan
        function showDetail(nama, barang, tglPinjam, tglKembali, status, dipinjamDari, dikembalikanKepada) {
            document.getElementById('modalNama').textContent = nama;
            document.getElementById('modalBarang').textContent = barang;
            document.getElementById('modalTglPinjam').textContent = tglPinjam;
            document.getElementById('modalTglKembali').textContent = tglKembali;
            // Menampilkan data baru
            document.getElementById('modalDipinjamDari').textContent = dipinjamDari;
            document.getElementById('modalDikembalikanKepada').textContent = dikembalikanKepada;

            const badge = document.getElementById('modalStatusBadge');
            if (status === 'dipinjam') {
                badge.textContent = "📦 Masih dipinjam";
                badge.className = "badge bg-warning text-dark rounded-pill px-3 py-2";
                document.getElementById('liTglKembali').style.display = 'none'; // Sembunyikan jika belum kembali
                document.getElementById('liDikembalikanKepada').style.display = 'none'; // Sembunyikan jika belum kembali
            } else {
                badge.textContent = "✅ Sudah dikembalikan";
                badge.className = "badge bg-success text-white rounded-pill px-3 py-2";
                document.getElementById('liTglKembali').style.display = ''; // Tampilkan jika sudah kembali
                document.getElementById('liDikembalikanKepada').style.display = ''; // Tampilkan jika sudah kembali
            }

            new bootstrap.Modal(document.getElementById('detailModal')).show();
        }
    </script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="#">IT Solopos</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="home.php">Beranda | </a></li>
        <li class="nav-item"><a class="nav-link" href="peminjaman-barang.php">Peminjaman | </a></li>
        <li class="nav-item"><a class="nav-link" href="pengembalian-barang.php">Pengembalian | </a></li>
        <li class="nav-item"><a class="nav-link" href="daftar-peminjaman-barang.php">Daftar Logbook | </a></li>
        <li class="nav-item"><a class="nav-link active" href="daftar-kalender.php">Daftar Kalender</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-5">
    <h2 class="text-center mb-3">
        <span class="badge bg-primary fs-4 shadow-sm">📅 Kalender Peminjaman Barang</span>
    </h2>

    <div class="text-center mb-4">
        <span class="badge bg-success fs-5">📊 Total Peminjaman yang Tampil: <?= $totalPeminjamanEvent ?> Barang</span>
    </div>

    <div class="row justify-content-center mb-4">
        <div class="col-md-5">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white fw-bold">📌 Jenis Barang Paling Sering Dipinjam</div>
                <div class="card-body">
                    <ul class="mb-0">
                        <?php if (empty($topBarang)): ?>
                            <li>Belum ada data peminjaman barang.</li>
                        <?php else: ?>
                            <?php foreach ($topBarang as $barang): ?>
                                <li><?= htmlspecialchars($barang) ?></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-success text-white fw-bold">👤 Peminjam Paling Aktif</div>
                <div class="card-body">
                    <ul class="mb-0">
                        <?php if (empty($topNama)): ?>
                            <li>Belum ada data peminjam.</li>
                        <?php else: ?>
                            <?php foreach ($topNama as $nama): ?>
                                <li><?= htmlspecialchars($nama) ?></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <form method="get" class="row g-3 justify-content-center mb-2">
        <div class="col-auto">
            <label for="selectBulan" class="visually-hidden">Pilih Bulan</label>
            <select name="bulan" id="selectBulan" class="form-select">
                <?php foreach ($bulanList as $num => $nama): ?>
                    <option value="<?= $num ?>" <?= $bulan == $num ? 'selected' : '' ?>><?= $nama ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <label for="selectTahun" class="visually-hidden">Pilih Tahun</label>
            <select name="tahun" id="selectTahun" class="form-select">
                <?php for ($y = date('Y') - 3; $y <= date('Y') + 3; $y++): ?>
                    <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-auto">
            <label for="filterNama" class="visually-hidden">Filter Nama</label>
            <input type="text" name="nama" id="filterNama" class="form-control" placeholder="Filter Nama Peminjam" value="<?= htmlspecialchars($filterNama) ?>">
        </div>
        <div class="col-auto">
            <label for="filterJenisBarang" class="visually-hidden">Filter Barang</label>
            <select name="jenis_barang" id="filterJenisBarang" class="form-select">
                <option value="">Semua Barang</option>
                <?php foreach ($jenisBarangStaticList as $barangName): ?>
                    <option value="<?= htmlspecialchars($barangName) ?>" <?= $filterJenisBarang == $barangName ? 'selected' : '' ?>>
                        <?= htmlspecialchars($barangName) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" type="submit">Tampilkan & Filter</button>
        </div>
    </form>

    <div class="calendar mb-4">
        <?php
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        foreach ($days as $dayName) {
            echo "<div class='header'>{$dayName}</div>";
        }

        for ($i = 0; $i < $startDayOfWeek; $i++) {
            echo "<div class='day bg-light'></div>";
        }

        for ($day = 1; $day <= $jumlahHari; $day++) {
            echo "<div class='day'>";
            echo "<div class='date'>{$day}</div>";

            if (isset($dataPeminjaman[$day])) {
                usort($dataPeminjaman[$day], function($a, $b) {
                    $timeA = strtotime($a['tipe_event'] == 'pinjam' ? $a['tanggal_peminjaman'] : $a['tanggal_pengembalian']);
                    $timeB = strtotime($b['tipe_event'] == 'pinjam' ? $b['tanggal_peminjaman'] : $b['tanggal_pengembalian']);
                    return $timeA <=> $timeB;
                });

                foreach ($dataPeminjaman[$day] as $item) {
                    $nama = htmlspecialchars($item['nama_tampilan']);
                    $barang = htmlspecialchars($item['jenis_barang']);
                    $status = $item['status'];
                    $dipinjamDari = htmlspecialchars($item['dengan_siapa']); // Ambil 'dengan_siapa'
                    $dikembalikanKepada = htmlspecialchars($item['dikembalikan_kepada'] ?? '-'); // Ambil 'dikembalikan_kepada'

                    $tanggalPeminjamanFormatted = date('d-m-Y H:i', strtotime($item['tanggal_peminjaman']));

                    $tanggalPengembalianFormatted = '-';
                    if ($status == 'dikembalikan' && !empty($item['tanggal_pengembalian']) && $item['tanggal_pengembalian'] != '0000-00-00 00:00:00') {
                           $tanggalPengembalianFormatted = date('d-m-Y H:i', strtotime($item['tanggal_pengembalian']));
                    }

                    $label = '';
                    $bg = '';
                    if ($status == 'dipinjam') {
                        $label = "📦 {$nama} - {$barang}";
                        $bg = "bg-warning text-dark";
                    } else if ($status == 'dikembalikan') {
                        $label = "✅ {$nama} - {$barang}";
                        $bg = "bg-success text-white";
                    }
                    // Mengirimkan parameter baru ke showDetail
                    echo "<div class='item $bg' onclick=\"showDetail('$nama', '$barang', '$tanggalPeminjamanFormatted', '$tanggalPengembalianFormatted', '$status', '$dipinjamDari', '$dikembalikanKepada')\">$label</div>";
                }
            }

            echo "</div>";
        }

        $lastDayOfWeek = date('w', strtotime("$tahun-$bulan-$jumlahHari"));
        $remainingDays = (7 - ($jumlahHari + $startDayOfWeek) % 7) % 7;
        for ($i = 0; $i < $remainingDays; $i++) {
            echo "<div class='day bg-light'></div>";
        }

        if (empty($dataPeminjaman) && ($filterNama == '' && $filterJenisBarang == '')) {
            echo "<div class='col-12 text-center mt-3 fw-bold text-info' style='grid-column: 1 / -1;'>";
            echo "Tidak ada data peminjaman/pengembalian untuk bulan ini.";
            echo "</div>";
        } else if (empty($dataPeminjaman) && (!empty($filterNama) || !empty($filterJenisBarang))) {
            echo "<div class='col-12 text-center mt-3 fw-bold text-danger' style='grid-column: 1 / -1;'>";
            echo "Tidak ada data yang sesuai dengan filter yang diterapkan.";
            echo "</div>";
        }
        ?>
    </div>
</div>

<div class="text-center mt-3 mb-5">
    <a href="daftar-peminjaman-barang.php" class="btn btn-secondary">⬅ Kembali ke Logbook</a>
</div>

<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg rounded-4">
      <div class="modal-header bg-gradient bg-primary text-white">
        <h5 class="modal-title" id="detailModalLabel">📋 Detail Peminjaman</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <ul class="list-group list-group-flush fs-6">
          <li class="list-group-item"><strong>👤 Nama:</strong> <span id="modalNama"></span></li>
          <li class="list-group-item"><strong>🧰 Barang:</strong> <span id="modalBarang"></span></li>
          <li class="list-group-item"><strong>👨‍💻 Dipinjam Dari:</strong> <span id="modalDipinjamDari"></span></li> <li class="list-group-item"><strong>📅 Tanggal Pinjam:</strong> <span id="modalTglPinjam"></span></li>
          <li class="list-group-item" id="liTglKembali"><strong>📦 Tanggal Kembali:</strong> <span id="modalTglKembali"></span></li>
          <li class="list-group-item" id="liDikembalikanKepada"><strong>🤝 Dikembalikan Kepada:</strong> <span id="modalDikembalikanKepada"></span></li> <li class="list-group-item">
            <strong>✅ Status:</strong>
            <span id="modalStatusBadge" class="badge rounded-pill px-3 py-2"></span>
          </li>
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">❎ Tutup</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>