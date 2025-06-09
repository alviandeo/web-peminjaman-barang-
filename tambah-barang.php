<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Tambah Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f8f9fa;
            position: relative;
            min-height: 100vh;
            overflow: hidden; /* Tetap hidden jika ini yang diinginkan untuk background animation */
        }
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 200%; /* Agar bisa di-scroll/animasi */
            height: 100%;
            background-image: url('img/naruto.gif');
            background-size: cover; /* Gunakan cover agar gambar memenuhi area */
            background-repeat: no-repeat;
            background-position: center; /* Sesuaikan posisi awal */
            opacity: 0.1;
            animation: moveBackground 30s linear infinite;
            z-index: -1;
        }
        @keyframes moveBackground {
            0% { background-position: 0% center; } /* Mulai dari kiri */
            100% { background-position: 100% center; } /* Geser ke kanan */
        }
        .form-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin-top: 50px; /* Beri jarak dari atas */
            margin-bottom: 50px; /* Beri jarak dari bawah */
        }
        h3 {
            font-weight: bold;
            color: #333;
        }

        /* --- Responsivitas --- */

        /* Untuk perangkat dengan lebar maksimal 768px (Tablet dan HP) */
        @media (max-width: 768px) {
            .form-container {
                padding: 20px; /* Kurangi padding */
                margin-top: 30px;
                margin-bottom: 30px;
            }
            h3 {
                font-size: 1.8rem; /* Perkecil ukuran judul */
            }
            .btn {
                font-size: 0.9rem; /* Sesuaikan ukuran font tombol */
                padding: 0.6rem 1rem;
            }
            .form-control, .form-select {
                font-size: 0.9rem; /* Perkecil ukuran font input */
            }
            /* Sesuaikan background animation agar tidak terlalu besar */
            .bg-animation {
                background-size: contain; /* Ubah ke contain agar gambar penuh dan tidak terpotong */
                animation: moveBackgroundMobile 20s linear infinite; /* Buat animasi lebih cepat */
            }
            @keyframes moveBackgroundMobile {
                0% { background-position: 0% center; }
                100% { background-position: 100% center; }
            }
        }

        /* Untuk perangkat dengan lebar maksimal 576px (HP kecil) */
        @media (max-width: 576px) {
            .form-container {
                padding: 15px; /* Lebih perkecil padding */
                margin-top: 20px;
                margin-bottom: 20px;
            }
            h3 {
                font-size: 1.5rem; /* Lebih perkecil ukuran judul lagi */
            }
            .btn {
                font-size: 0.8rem; /* Lebih perkecil ukuran font tombol */
                padding: 0.5rem 0.8rem;
            }
            .form-control, .form-select {
                font-size: 0.85rem; /* Lebih perkecil ukuran font input */
            }
            /* Tombol bisa kita buat full width dan menumpuk */
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 10px; /* Beri jarak antar tombol */
            }
            .d-flex.justify-content-between .btn {
                width: 100%; /* Jadikan tombol full width */
            }
        }
    </style>
</head>
<body>
<div class="bg-animation"></div>

<div class="container">
    <div class="form-container mx-auto col-md-6 col-lg-5"> <h3 class="mb-4 text-center">Tambah Barang Baru</h3>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="mb-3"><input type="text" name="nama" class="form-control" placeholder="Nama Barang" required></div>
            <div class="mb-3"><input type="text" name="spek" class="form-control" placeholder="Spesifikasi" required></div>
            <div class="mb-3"><input type="text" name="seri" class="form-control" placeholder="Seri" required></div>
            <div class="mb-3"><input type="number" name="tahun" class="form-control" placeholder="Tahun" required></div>
            <div class="mb-3"><input type="number" name="stok" class="form-control" placeholder="Stok" required></div>
            <div class="mb-3"><input type="file" name="gambar" class="form-control" required></div>
            <div class="mb-3">
                <select name="rusak" class="form-select">
                    <option value="0">Baik</option>
                    <option value="1">Rusak</option>
                </select>
            </div>
            <div class="d-flex justify-content-between">
                <button class="btn btn-success" type="submit" name="simpan">💾 Simpan</button>
                <a href="daftar-inventaris-barang.php" class="btn btn-secondary">🔙 Kembali</a>
            </div>
        </form>
    </div>
</div>

<?php
if (isset($_POST['simpan'])) {
    $nama = $_POST['nama'];
    $spek = $_POST['spek'];
    $seri = $_POST['seri'];
    $tahun = $_POST['tahun'];
    $stok = $_POST['stok'];
    $rusak = $_POST['rusak'];

    $gambar = $_FILES['gambar']['name'];
    $tmp = $_FILES['gambar']['tmp_name'];
    // Pastikan folder 'img' ada dan bisa diakses (writeable)
    if (!move_uploaded_file($tmp, "img/$gambar")) {
        echo "<script>alert('Gagal mengunggah gambar. Pastikan folder img ada dan memiliki izin tulis.');</script>";
        exit;
    }

    // Cari ID kosong terkecil
    // Pastikan koneksi database ($koneksi) sudah benar dan tidak ada error
    $result = $koneksi->query("SELECT id FROM inventaris ORDER BY id");
    if (!$result) {
        echo "<script>alert('Error database saat mencari ID: " . $koneksi->error . "');</script>";
        exit;
    }

    $expected = 1;
    while ($row = $result->fetch_assoc()) {
        if ((int)$row['id'] != $expected) break;
        $expected++;
    }
    $nextId = $expected;
    // kode_barang ini sepertinya tidak disimpan di tabel, hanya dibuat untuk tampilan.
    // Jika perlu disimpan, tambahkan kolom kode_barang di tabel inventaris.
    $kode_barang = 'BRG' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

    // Query insert manual tanpa kode_barang (sesuai tabel Anda yang sebelumnya)
    $stmt = $koneksi->prepare("INSERT INTO inventaris (id, nama, spek, tahun, seri, gambar, stok, rusak)
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        echo "<script>alert('Error prepare statement: " . $koneksi->error . "');</script>";
        exit;
    }

    // Bind parameter: i = integer, s = string
    $stmt->bind_param("isssissi", $nextId, $nama, $spek, $tahun, $seri, $gambar, $stok, $rusak);

    // Eksekusi query
    if ($stmt->execute()) {
        echo "<script>alert('Barang berhasil ditambahkan!'); location='daftar-inventaris-barang.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan barang: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>