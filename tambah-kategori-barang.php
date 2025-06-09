<?php
include 'koneksi.php';

if (isset($_POST['submit'])) {
    $id_asal = $_POST['id_asal'];
    $sub_kode = $_POST['sub_kode'];
    $seri = $_POST['seri'];
    $tahun = $_POST['tahun'];
    $stok = $_POST['stok'];
    $gambar = $_FILES['gambar']['name'];
    $spek = $_POST['spek'];
    $rusak = $_POST['rusak'];

    // Ambil nama dan kode barang asal
    $asal = $koneksi->query("SELECT * FROM inventaris WHERE id = '$id_asal'")->fetch_assoc();
    $nama = $asal['nama'];
    $kode_barang = 'BRG' . str_pad($asal['id'], 3, '0', STR_PAD_LEFT); // Kode barang (misalnya: BRG001)

    // Gabungkan kode barang dengan sub_kode untuk mendapatkan kategori (misal: BRG001a)
    $kategori = $kode_barang . $sub_kode;

    // Validasi file gambar
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($gambar, PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_extensions)) {
        echo "Hanya gambar dengan ekstensi JPG, JPEG, PNG, atau GIF yang diperbolehkan.";
        exit;
    }

    // Pindahkan file gambar yang diupload
    $gambar_tmp_name = $_FILES['gambar']['tmp_name'];
    $gambar_path = 'img/' . uniqid() . '.' . $file_extension;  // Nama file gambar yang unik
    if (!move_uploaded_file($gambar_tmp_name, $gambar_path)) {
        echo "Gagal mengupload gambar.";
        exit;
    }

    // Insert barang kategori ke dalam tabel inventaris dengan sub_kode
    $query = "INSERT INTO inventaris (nama, spek, tahun, seri, gambar, stok, rusak, sub_kode) 
              VALUES ('$nama', '$spek', '$tahun', '$seri', '$gambar_path', '$stok', '$rusak', '$kategori')";
    if ($koneksi->query($query)) {
        // Redirect kembali ke halaman kategori barang
        header("Location: kategori-barang.php");
    } else {
        echo "Error: " . $koneksi->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Kategori Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container">
    <div class="card shadow p-4">
        <h3 class="mb-4 text-primary">Tambah Kategori Barang (Sub Kode)</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="id_asal" class="form-label">Pilih Barang Utama</label>
                <select name="id_asal" class="form-select" required>
                    <option value="">-- Pilih --</option>
                    <?php
                    // Menampilkan barang yang tidak memiliki sub_kode (barang utama)
                    $barang = $koneksi->query("SELECT * FROM inventaris WHERE sub_kode = ''");
                    while ($b = $barang->fetch_assoc()):
                        $kode = 'BRG' . str_pad($b['id'], 3, '0', STR_PAD_LEFT);
                        echo "<option value='{$b['id']}'>{$kode} - {$b['nama']}</option>";
                    endwhile;
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="sub_kode" class="form-label">Sub Kode (contoh: a, b, c)</label>
                <input type="text" name="sub_kode" class="form-control" required maxlength="2">
            </div>
            <div class="mb-3">
                <label for="seri" class="form-label">Seri</label>
                <input type="text" name="seri" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="tahun" class="form-label">Tahun</label>
                <input type="number" name="tahun" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="stok" class="form-label">Stok</label>
                <input type="number" name="stok" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="gambar" class="form-label">Gambar</label>
                <input type="file" name="gambar" class="form-control" accept="image/*" required>
            </div>
            <div class="mb-3">
                <label for="spek" class="form-label">Spesifikasi</label>
                <textarea name="spek" class="form-control" required></textarea>
            </div>
            <div class="mb-3">
                <label for="rusak" class="form-label">Kondisi</label>
                <select name="rusak" class="form-select">
                    <option value="0">Baik</option>
                    <option value="1">Rusak</option>
                </select>
            </div>
            <button type="submit" name="submit" class="btn btn-success">Tambah Kategori</button>
            <a href="daftar-inventaris-barang.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
</body>
</html>
