<?php include 'koneksi.php'; ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaris Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('img/solopos5.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding-top: 70px; /* Tambahkan padding atas untuk mengimbangi fixed-top navbar */
        }
        .content-box {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }
        .table-img {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .qr-box {
            width: 100px;
            height: 100px;
            margin: auto;
            /* Pastikan QR code terpusat */
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .qr-box canvas, .qr-box img { /* Atur juga elemen di dalam qr-box */
            max-width: 100%;
            height: auto;
        }

        /* Responsiveness adjustments */
        @media (max-width: 768px) { /* Untuk tablet dan HP */
            body {
                padding-top: 60px; /* Sesuaikan padding atas untuk layar lebih kecil */
                background-attachment: scroll; /* Ubah menjadi scroll di mobile/tablet untuk performa */
            }
            .content-box {
                padding: 20px;
            }
            .table-responsive { /* Buat tabel bisa discroll secara horizontal */
                overflow-x: auto;
            }
            .table th, .table td {
                font-size: 0.8rem; /* Perkecil ukuran font tabel, gunakan rem untuk konsistensi */
                padding: 0.5rem; /* Perkecil padding tabel */
            }
            .table-img {
                width: 60px;
                height: 45px;
            }
            .qr-box {
                width: 70px;
                height: 70px;
            }
            .btn {
                font-size: 0.85rem; /* Perkecil ukuran font tombol */
                padding: 0.4rem 0.8rem;
            }
            h2 {
                font-size: 1.8rem; /* Perkecil ukuran judul */
            }
        }

        @media (max-width: 576px) { /* Untuk HP */
            body {
                padding-top: 56px; /* Sesuaikan padding atas untuk layar sangat kecil */
            }
            .d-flex.justify-content-between.align-items-center.mb-4 {
                flex-direction: column; /* Ubah tata letak tombol ke kolom */
                align-items: flex-start !important; /* Ratakan ke kiri */
            }
            /* Bootstrap d-grid gap-2 akan menangani ini dengan baik */
            /* Anda bisa menghapus aturan spesifik untuk .d-flex.justify-content-between.align-items-center.mb-4 > div jika menggunakan d-grid */

            .table th, .table td {
                font-size: 0.7rem; /* Lebih perkecil ukuran font tabel */
                padding: 0.3rem; /* Lebih perkecil padding tabel */
            }
            .table-img {
                width: 50px;
                height: 35px;
            }
            .qr-box {
                width: 60px;
                height: 60px;
            }
            .btn {
                font-size: 0.75rem; /* Lebih perkecil ukuran font tombol */
                padding: 0.3rem 0.6rem;
            }
            h2 {
                font-size: 1.5rem; /* Lebih perkecil ukuran judul */
            }
        }

        @media print {
            .no-print { display: none; }
            body { background: none; padding-top: 0 !important; } /* Hilangkan padding atas saat print */
            .content-box {
                box-shadow: none;
                padding: 0;
                background-color: transparent;
            }
            .table {
                width: 100%;
            }
            .qr-box {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="content-box">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary">📦 Inventaris Barang</h2>
            <div class="d-grid gap-2 d-md-block"> <a href="tambah-barang.php" class="btn btn-success no-print">+ Tambah Barang</a>
                <a href="tambah-kategori-barang.php" class="btn btn-secondary no-print">+ Tambah Kategori</a>
                 <a href="cetak-qr.php" class="btn btn-secondary no-print"> 🖨 Cetak QR</a>
                <a href="daftar-peminjaman-barang.php" class="btn btn-info no-print">⬅️ Kembali ke Logbook</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center align-middle table-hover shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Kode Barang</th>
                        <th>Gambar</th>
                        <th>Nama</th>
                        <th class="d-none d-md-table-cell">Spesifikasi</th> <th class="d-none d-md-table-cell">Seri & Tahun</th> <th>Stok</th>
                        <th>Kondisi</th>
                        <th>QR Code</th>
                        <th class="no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Pastikan query ini sudah benar di database Anda
                    // Mengambil data barang yang sub_kode-nya kosong (barang utama)
                    $query = $koneksi->query("SELECT * FROM inventaris WHERE sub_kode = '' ORDER BY id ASC");

                    while ($barang = $query->fetch_assoc()):
                        $id_barang = $barang["id"];
                        $sub_kode = $barang["sub_kode"];
                        // Pastikan format kode barang sesuai dengan yang Anda inginkan
                        $kode_barang = 'BRG' . str_pad($id_barang, 3, '0', STR_PAD_LEFT) . $sub_kode;
                        $status = $barang["rusak"]
                            ? "<span class='badge bg-danger'>Rusak</span>"
                            : "<span class='badge bg-success'>Baik</span>";
                    ?>
                    <tr>
                        <td><strong><?= $kode_barang ?></strong></td>
                        <td><img src="img/<?= $barang["gambar"] ?>" class="table-img" alt="Gambar Barang"></td>
                        <td><strong><?= $barang["nama"] ?></strong></td>
                        <td class="d-none d-md-table-cell"><?= $barang["spek"] ?></td> <td class="d-none d-md-table-cell"><?= $barang["seri"] ?><br><small>(<?= $barang["tahun"] ?>)</small></td> <td><?= $barang["stok"] ?></td>
                        <td><?= $status ?></td>
                        <td><div class="qr-box" id="qrcode-<?= $id_barang . $sub_kode ?>"></div></td>
                        <td class="no-print">
                            <a href="detail_barang.php?kode=<?= $kode_barang ?>" class="btn btn-info btn-sm mb-1">Detail</a>
                            <a href="kategori-barang.php?id=<?= $id_barang ?>" class="btn btn-secondary btn-sm mb-1">Kategori</a>
                            <a href="edit_barang.php?id=<?= $id_barang ?>" class="btn btn-warning btn-sm mb-1">Edit</a>
                            <a href="hapus_barang.php?id=<?= $id_barang ?>" class="btn btn-danger btn-sm mb-1"
                                onclick="return confirm('Yakin ingin menghapus barang ini?');">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
<?php
// Query untuk QR code juga perlu disesuaikan jika sub_kode selalu kosong untuk QR
$query_qr = $koneksi->query("SELECT id, sub_kode FROM inventaris WHERE sub_kode = ''"); // Ambil hanya yang utama untuk QR
while ($row_qr = $query_qr->fetch_assoc()):
    $qr_id = $row_qr['id'];
    $qr_sub = $row_qr['sub_kode']; // Ini akan kosong jika query di atas benar
    $qr_kode = 'BRG' . str_pad($qr_id, 3, '0', STR_PAD_LEFT) . $qr_sub;
    $link = "http://10.10.11.114/web-peminjaman-barang/detail_barang.php?kode=" . $qr_kode;
?>
new QRCode(document.getElementById("qrcode-<?= $qr_id . $qr_sub ?>"), {
    text: "<?= $link ?>",
    width: 100,
    height: 100
});
<?php endwhile; ?>
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>