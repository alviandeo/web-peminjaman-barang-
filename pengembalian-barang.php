<?php
include 'config.php';

$queryInventaris = mysqli_query($conn, "SELECT id, nama, seri FROM inventaris");
$jenisBarangList = [];
while ($rowInventaris = mysqli_fetch_assoc($queryInventaris)) {
    $display_name = htmlspecialchars($rowInventaris['nama']);
    if (!empty($rowInventaris['seri'])) {
        $display_name .= ' (' . htmlspecialchars($rowInventaris['seri']) . ')';
    }
    $jenisBarangList[$rowInventaris['id']] = $display_name;
}

$query_barang_dipinjam = mysqli_query($conn, "SELECT id, jenis_barang FROM peminjaman_barang WHERE status = 'dipinjam' ORDER BY tanggal_peminjaman ASC");
$barangDipinjamUntukPilihan = [];
while ($row = mysqli_fetch_assoc($query_barang_dipinjam)) {
    $jenis_barang_text_from_db = $row['jenis_barang'];

    $barangDipinjamUntukPilihan[] = [
        'id' => $row['id'],
        'jenis_barang_nama' => $jenis_barang_text_from_db
    ];
}

$orangList = [
    1 => "Pak Trio",
    2 => "Pak Budi",
    3 => "Pak Yeyen",
    4 => "Pak Aang",
    5 => "Pak Eko",
    6 => "Pak Danang",
    7 => "Pak Gatot"
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengembalian Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body {
            background-image: url('img/solopos6.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            display: flex; /* Menggunakan flexbox untuk memposisikan konten di tengah vertikal */
            flex-direction: column; /* Mengatur arah flex menjadi kolom */
        }
        .form-container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        .floating-alert {
            animation: floatText 3s ease-in-out infinite;
            background: linear-gradient(to right,rgb(255, 89, 89),rgb(255, 246, 81));
            color: #4b2c20;
            font-weight: bold;
            border-radius: 12px;
            box-shadow: 0 0 15px rgb(255, 176, 7);
            margin-top: 40px;
        }
        @keyframes floatText {
            0% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
            100% { transform: translateY(0); }
        }
        .signature-pad {
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
            width: 100%; /* Pastikan ini 100% untuk responsivitas */
            max-width: 400px; /* Batasi lebar maksimum */
            height: 150px;
        }
        /* Media Query untuk signature-pad di layar kecil */
        @media (max-width: 576px) {
            .signature-pad {
                height: 120px; /* Sedikit lebih kecil di ponsel */
                max-width: 100%; /* Memastikan tidak ada overflow */
            }
        }
        /* Menyesuaikan jarak agar konten tidak tersembunyi di bawah navbar */
        .container.d-flex {
            padding-top: 90px; /* Sesuaikan dengan tinggi navbar jika fixed-top */
            padding-bottom: 20px; /* Tambahkan sedikit padding di bawah */
        }
        /* Tambahkan padding untuk navbar agar konten tidak tertutup (alternatif untuk margin-top) */
        /* .navbar.fixed-top + .container { margin-top: 56px; } */
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#">IT Solopos</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="home.php">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="peminjaman-barang.php">Peminjaman Barang</a></li>
                <li class="nav-item"><a class="nav-link active" href="pengembalian-barang.php">Pengembalian Barang</a></li>
                <li class="nav-item"><a class="nav-link" href="daftar-peminjaman-barang.php">Daftar Logbook</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container d-flex justify-content-center align-items-center flex-grow-1" style="min-height: 100vh;">
    <div class="col-12 col-md-8 col-lg-6"> <div class="form-container mt-4">
            <div class="text-center mb-4">
                <h4 class="text-danger fw-bold">Form Pengembalian Barang</h4>
            </div>
            <form method="POST" action="proses_pengembalian.php" id="pengembalianForm">
                <div class="mb-3">
                    <label for="nama_pengembali" class="form-label">Nama :</label>
                    <input type="text" name="nama_pengembali" id="nama_pengembali" class="form-control" required placeholder="Masukkan nama pengembali">
                </div>

                <div class="mb-3">
                    <label class="form-label">Pilih Barang yang Dikembalikan :</label>
                    <div id="borrowedItemsContainer" class="row">
                        <?php if (!empty($barangDipinjamUntukPilihan)): ?>
                            <?php foreach ($barangDipinjamUntukPilihan as $item): ?>
                                <div class="col-6 col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="borrowed_item_ids[]" value="<?= htmlspecialchars($item['id']) ?>" id="borrowedItem<?= htmlspecialchars($item['id']) ?>">
                                        <label class="form-check-label" for="borrowedItem<?= htmlspecialchars($item['id']) ?>">
                                            <?= htmlspecialchars($item['jenis_barang_nama']) ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-muted">Tidak ada barang yang sedang dipinjam saat ini.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="dengan_siapa" class="form-label">Dikembalikan Kepada :</label>
                    <select name="dengan_siapa" id="dengan_siapa" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <?php foreach ($orangList as $id => $namaOrang): ?>
                            <option value="<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($namaOrang) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3 text-center"> <label for="signature" class="form-label d-block mb-2">Tanda Tangan Pengembali : </label>
                    <canvas id="signaturePadPengembali" class="signature-pad"></canvas>
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="clearSignaturePengembali">Hapus Tanda Tangan</button>
                    <input type="hidden" name="signature_pengembali" id="signatureInputPengembali">
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-danger fw-semibold">
                        <i class="bi bi-box-arrow-in-left me-2"></i>Kembalikan Barang
                    </button>
                </div>
            </form>

            <div class="text-center floating-alert fs-6 py-2 px-3 mt-5">
                🌟 Terima Kasih Sudah Mengembalikan Barang Dengan Baik 🌟
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<script>
$(document).ready(function() {
    var canvasPengembali = document.getElementById('signaturePadPengembali');
    var signaturePadPengembali = new SignaturePad(canvasPengembali, {
        backgroundColor: 'rgb(255, 255, 255)'
    });

    function resizeCanvas() {
        var ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvasPengembali.width = canvasPengembali.offsetWidth * ratio;
        canvasPengembali.height = canvasPengembali.offsetHeight * ratio;
        canvasPengembali.getContext("2d").scale(ratio, ratio);
        signaturePadPengembali.clear(); // Hapus tanda tangan setelah resize agar tidak terdistorsi
    }
    window.onresize = resizeCanvas;
    resizeCanvas(); // Panggil saat halaman dimuat

    $('#clearSignaturePengembali').on('click', function() {
        signaturePadPengembali.clear();
    });

    $('#pengembalianForm').on('submit', function(event) {
        if ($('input[name="borrowed_item_ids[]"]:checked').length === 0) {
            alert('Pilih setidaknya satu barang yang akan dikembalikan!');
            event.preventDefault();
            return false;
        }

        if (signaturePadPengembali.isEmpty()) {
            alert('Tanda tangan pengembali wajib diisi!');
            event.preventDefault();
            return false;
        }
        var dataURL = signaturePadPengembali.toDataURL();
        $('#signatureInputPengembali').val(dataURL);
    });
});
</script>
</body>
</html>