<?php
include 'config.php';

$query = mysqli_query($conn, "SELECT id, nama, seri FROM inventaris ORDER BY nama ASC");
$jenisBarangList = [];
while ($row = mysqli_fetch_assoc($query)) {
    $display_name = htmlspecialchars($row['nama']);
    if (!empty($row['seri'])) {
        $display_name .= ' (' . htmlspecialchars($row['seri']) . ')';
    }
    $jenisBarangList[$row['id']] = $display_name;
}

// Logika untuk disable barang yang sudah dipinjam
$queryDipinjamText = mysqli_query($conn, "SELECT jenis_barang FROM peminjaman_barang WHERE status = 'dipinjam'");
$barangDipinjamText = [];
while ($rowDipinjamText = mysqli_fetch_assoc($queryDipinjamText)) {
    $barangDipinjamText[] = $rowDipinjamText['jenis_barang'];
}

$barangDipinjamIDs = [];
foreach ($barangDipinjamText as $nama_seri_teks) {
    // Cari ID barang berdasarkan nama lengkap (nama + seri)
    $id_from_text = array_search($nama_seri_teks, $jenisBarangList);
    if ($id_from_text !== false) {
        $barangDipinjamIDs[] = $id_from_text;
    }
}
$barangDipinjam = $barangDipinjamIDs;

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
    <title>Form Peminjaman Barang</title>
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
            padding-top: 90px; /* Sesuaikan dengan tinggi navbar jika fix-top */
            padding-bottom: 20px; /* Tambahkan sedikit padding di bawah */
        }
        /* Tambahkan padding untuk navbar agar konten tidak tertutup */
        .navbar.fixed-top + .container {
            margin-top: 56px; /* Tinggi default navbar Bootstrap */
        }
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
                <li class="nav-item"><a class="nav-link active" href="peminjaman-barang.php">Peminjaman Barang</a></li>
                <li class="nav-item"><a class="nav-link" href="pengembalian-barang.php">Pengembalian Barang</a></li>
                <li class="nav-item"><a class="nav-link" href="daftar-peminjaman-barang.php">Daftar Logbook</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container d-flex justify-content-center align-items-center flex-grow-1" style="min-height: 100vh;">
    <div class="col-12 col-md-8 col-lg-6"> <div class="form-container mt-4">
            <div class="text-center mb-4">
                <h4 class="text-primary fw-bold">Form Peminjaman Barang</h4>
            </div>
            <form method="POST" action="simpan.php" id="peminjamanForm">
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama : </label>
                    <input type="text" name="nama" id="nama" class="form-control" required placeholder="Masukkan nama lengkap">
                </div>

                <div class="mb-3">
                    <label for="jenis_barang" class="form-label">Jenis Barang : </label>
                    <select name="jenis_barang[]" id="jenis_barang" class="form-select" multiple required>
                        <?php
                        if (empty($jenisBarangList)) {
                            echo "<option value='' disabled>Tidak ada barang tersedia</option>";
                        } else {
                            foreach ($jenisBarangList as $id_barang => $nama_lengkap_barang) {
                                // Pastikan $barangDipinjam berisi ID numerik
                                $disabled = in_array($id_barang, $barangDipinjam) ? 'disabled' : '';
                                echo "<option value='$id_barang' $disabled>$nama_lengkap_barang</option>";
                            }
                        }
                        ?>
                    </select>
                    <small class="text-muted">* Bisa pilih lebih dari satu barang (barang yang sedang dipinjam tidak bisa dipilih)</small>
                </div>

                <div class="mb-3">
                    <label for="dengan_siapa" class="form-label">Dengan Siapa : </label>
                    <select name="dengan_siapa" id="dengan_siapa" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <?php foreach ($orangList as $id => $namaOrang): ?>
                            <option value="<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($namaOrang) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3 text-center"> <label for="signature" class="form-label d-block mb-2">Tanda Tangan Peminjam : </label>
                    <canvas id="signaturePadPeminjam" class="signature-pad"></canvas>
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="clearSignaturePeminjam">Hapus Tanda Tangan</button>
                    <input type="hidden" name="signature_peminjam" id="signatureInputPeminjam">
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-success fw-semibold">
                        <i class="bi bi-check-circle me-2"></i>Simpan Peminjaman
                    </button>
                </div>
            </form>

            <div class="text-center floating-alert fs-6 py-2 px-3 mt-5">
                🌟 Jangan Lupa Kembalikan Barang Setelah Digunakan! 🌟
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
    $('#jenis_barang').select2({
        placeholder: "-- Pilih Barang --",
        allowClear: true,
        width: '100%'
    });

    var canvasPeminjam = document.getElementById('signaturePadPeminjam');
    var signaturePadPeminjam = new SignaturePad(canvasPeminjam, {
        backgroundColor: 'rgb(255, 255, 255)'
    });

    function resizeCanvas() {
        var ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvasPeminjam.width = canvasPeminjam.offsetWidth * ratio;
        canvasPeminjam.height = canvasPeminjam.offsetHeight * ratio;
        canvasPeminjam.getContext("2d").scale(ratio, ratio);
        signaturePadPeminjam.clear(); // Hapus tanda tangan setelah resize agar tidak terdistorsi
    }
    window.onresize = resizeCanvas;
    resizeCanvas(); // Panggil saat halaman dimuat

    $('#clearSignaturePeminjam').on('click', function() {
        signaturePadPeminjam.clear();
    });

    $('#peminjamanForm').on('submit', function(event) {
        if ($('#jenis_barang').val().length === 0) {
            alert('Pilih setidaknya satu jenis barang!');
            event.preventDefault();
            return false;
        }
        if (signaturePadPeminjam.isEmpty()) {
            alert('Tanda tangan peminjam wajib diisi!');
            event.preventDefault();
            return false;
        }
        var dataURL = signaturePadPeminjam.toDataURL();
        $('#signatureInputPeminjam').val(dataURL);
    });
});
</script>
</body>
</html>