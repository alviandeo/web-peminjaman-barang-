<?php 
include 'koneksi.php'; 

// Cek apakah ID barang diterima
if (isset($_GET['id'])) {
    $id_barang = $_GET['id'];

    // Ambil data barang berdasarkan ID
    $query = $koneksi->query("SELECT * FROM inventaris WHERE id = '$id_barang'");
    $barang = $query->fetch_assoc();
} else {
    // Redirect jika ID tidak ditemukan
    header("Location: tabel-inventaris-barang.php");
    exit();
}

if (isset($_POST['update'])) {
    // Ambil data yang dikirimkan melalui form
    $nama = $_POST['nama'];
    $spek = $_POST['spek'];
    $seri = $_POST['seri'];
    $tahun = $_POST['tahun'];
    $stok = $_POST['stok'];
    $rusak = isset($_POST['rusak']) ? 1 : 0;
    
    // Gambar Barang
    $gambar = $barang['gambar']; // Gambar lama jika tidak diganti

    if ($_FILES['gambar']['name'] != '') {
        // Jika ada gambar baru, upload gambar
        $target_dir = "img/";
        $target_file = $target_dir . basename($_FILES["gambar"]["name"]);
        
        if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
            $gambar = $_FILES['gambar']['name']; // Update nama gambar
        }
    }

    // Update data barang ke database
    $update_query = "UPDATE inventaris SET nama = '$nama', spek = '$spek', seri = '$seri', tahun = '$tahun', stok = '$stok', rusak = '$rusak', gambar = '$gambar' WHERE id = '$id_barang'";

    if ($koneksi->query($update_query)) {
        echo "<script>alert('Data berhasil diperbarui'); window.location.href='daftar-inventaris-barang.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat memperbarui data');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .content-box {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }
        .content-box h2 {
            font-size: 2rem;
            font-weight: bold;
            color: #495057;
        }
        .form-label {
            font-weight: 600;
        }
        .btn-primary, .btn-success {
            font-size: 1rem;
            padding: 10px 20px;
            border-radius: 5px;
        }
        .btn-secondary {
            font-size: 1rem;
            padding: 10px 20px;
            border-radius: 5px;
            background-color: #6c757d;
            border: none;
        }
        .form-control {
            font-size: 1rem;
            padding: 10px;
            border-radius: 5px;
        }
        .form-check-label {
            font-size: 1rem;
            font-weight: 500;
        }
        .img-thumbnail {
            width: 150px;
            height: 100px;
            object-fit: cover;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #6c757d;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="content-box">
        <h2>Edit Barang</h2>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Barang</label>
                <input type="text" name="nama" class="form-control" value="<?= $barang['nama'] ?>" required>
            </div>

            <div class="mb-3">
                <label for="spek" class="form-label">Spesifikasi</label>
                <input type="text" name="spek" class="form-control" value="<?= $barang['spek'] ?>" required>
            </div>

            <div class="mb-3">
                <label for="seri" class="form-label">Seri</label>
                <input type="text" name="seri" class="form-control" value="<?= $barang['seri'] ?>" required>
            </div>

            <div class="mb-3">
                <label for="tahun" class="form-label">Tahun</label>
                <input type="text" name="tahun" class="form-control" value="<?= $barang['tahun'] ?>" required>
            </div>

            <div class="mb-3">
                <label for="stok" class="form-label">Stok</label>
                <input type="number" name="stok" class="form-control" value="<?= $barang['stok'] ?>" required>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="rusak" class="form-check-input" id="rusak" <?= $barang['rusak'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="rusak">Barang Rusak</label>
            </div>

            <div class="mb-3">
                <label for="gambar" class="form-label">Gambar Barang</label>
                <input type="file" name="gambar" class="form-control">
                <small>Gambar saat ini:</small><br>
                <img src="img/<?= $barang['gambar'] ?>" class="img-thumbnail" alt="Gambar Barang">
            </div>

            <button type="submit" name="update" class="btn btn-success">Update Barang</button>
            <a href="daftar-inventaris-barang.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>

    <div class="footer">
        <p>&copy; 2025 Inventaris Barang | All Rights Reserved</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
