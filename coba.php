<?php
// Pastikan file config.php sudah ada dan terhubung ke database
include 'config.php';

// Fungsi untuk menghasilkan kode barang (barcode)
function generateBarcode($id, $sub_kode = '') {
    // Menambahkan logika untuk 'membersihkan' sub_kode jika sudah berbentuk BRGxxx
    // Contoh: Jika sub_kode adalah 'BRG001b', kita ingin menggunakannya saja
    // Jika sub_kode adalah 'b', kita ingin menggabungkannya dengan BRG+ID
    if (preg_match('/^BRG\d{3}.*$/', $sub_kode)) {
        // Jika sub_kode sudah dalam format BRGxxx, gunakan saja sub_kode itu
        return $sub_kode;
    } else {
        // Jika sub_kode hanya berupa bagian tambahan, gabungkan seperti biasa
        return 'BRG' . str_pad($id, 3, '0', STR_PAD_LEFT) . $sub_kode;
    }
}

// Ambil data barang dari database inventaris
// Menggunakan ORDER BY id ASC untuk urutan yang konsisten
$query = mysqli_query($conn, "SELECT id, nama, seri, sub_kode FROM inventaris ORDER BY id ASC");

$inventarisItems = [];
while ($row = mysqli_fetch_assoc($query)) {
    $inventarisItems[] = $row;
}

// Tutup koneksi database setelah selesai mengambil data
mysqli_close($conn);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Cetak QR Code Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background-color: #f8f9fa; }
        .qr-card {
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            background: white;
            box-shadow: 0 0 8px rgba(0,0,0,0.05);
            /* Pastikan ukuran kartu QR konsisten */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 220px; /* Sesuaikan tinggi minimum agar konsisten */
        }
        .qr-label {
            font-weight: bold;
            margin-top: 10px;
            text-align: center;
            word-break: break-word; /* Memastikan teks panjang tidak keluar batas */
            font-size: 0.9em; /* Ukuran font standar */
        }
        .qr-label small {
            font-size: 0.8em;
            display: block; /* Membuat link dan kode di baris baru */
        }
        .qr-box {
            width: 128px; /* Ukuran QR code */
            height: 128px; /* Ukuran QR code */
            margin: 0 auto; /* Pusatkan QR code */
        }

        /* --- */
        /* Media Queries untuk Responsivitas Tampilan Layar */
        /* --- */

        /* Untuk perangkat mobile (lebar kurang dari 576px) */
        @media (max-width: 575.98px) {
            .qr-card {
                padding: 10px;
                min-height: 180px; /* Lebih kecil untuk HP */
            }
            .qr-box {
                width: 100px;
                height: 100px;
            }
            .qr-label {
                font-size: 0.8em; /* Font lebih kecil di HP */
            }
            .qr-label small {
                font-size: 0.7em;
            }
            .container {
                padding: 10px;
            }
            .btn {
                font-size: 0.9rem;
                padding: 0.5rem 1rem;
            }
        }

        /* Untuk tablet (lebar antara 576px dan 991.98px) */
        @media (min-width: 576px) and (max-width: 991.98px) {
            .qr-card {
                padding: 12px;
                min-height: 200px; /* Sedang untuk tablet */
            }
            .qr-box {
                width: 110px;
                height: 110px;
            }
            .qr-label {
                font-size: 0.85em; /* Font sedikit lebih kecil di tablet */
            }
            .qr-label small {
                font-size: 0.75em;
            }
        }

        /* --- */
        /* Media Queries untuk Cetak */
        /* --- */
        @media print {
            .no-print { display: none; }
            body { background: none; margin: 0; padding: 0; } /* Hapus background dan padding body saat cetak */
            .container { width: 100% !important; max-width: none !important; padding: 0 !important; } /* Sesuaikan lebar container saat cetak */
            .row {
                display: flex;
                flex-wrap: wrap;
                margin-left: 0; /* Hapus margin negatif */
                margin-right: 0; /* Hapus margin negatif */
                justify-content: flex-start; /* Pastikan mulai dari kiri */
            }
            .col-md-3, .col-sm-4, .col-6 {
                flex: 0 0 24.5%; /* Atur agar 4 kolom pas di kertas A4 dengan sedikit celah */
                max-width: 24.5%;
                padding: 5px; /* Kurangi padding antar kartu untuk kerapatan */
                box-sizing: border-box; /* Pastikan padding dihitung dalam lebar */
            }
            .qr-card {
                border: 1px solid #ddd; /* Border lebih ringan untuk cetak */
                box-shadow: none; /* Hapus shadow saat cetak */
                padding: 8px; /* Kurangi padding di dalam kartu */
                margin-bottom: 8px; /* Kurangi margin bawah */
                min-height: 180px; /* Tinggi kartu saat cetak */
                page-break-inside: avoid; /* Hindari pemotongan kartu di tengah halaman */
            }
            .qr-box {
                width: 100px; /* Ukuran QR code saat cetak */
                height: 100px; /* Ukuran QR code saat cetak */
            }
            .qr-label {
                font-size: 0.8em; /* Ukuran font lebih kecil saat cetak */
                margin-top: 5px;
            }
            .qr-label small {
                font-size: 0.65em;
            }
            @page {
                size: A4 portrait; /* atau 'letter portrait' */
                margin: 1cm; /* Margin halaman cetak */
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="text-center mb-4 no-print">
        <h3 class="fw-bold">Cetak QR Code Barang</h3>
        <button class="btn btn-primary" onclick="window.print()">
            <i class="bi bi-printer-fill me-2"></i>Cetak QR
        </button>
        <a href="daftar-inventaris-barang.php" class="btn btn-secondary ms-2">
            <i class="bi bi-arrow-left-circle-fill me-2"></i>Kembali ke Inventaris
        </a>
    </div>

    <div class="row">
        <?php foreach ($inventarisItems as $barang):
            $id_barang = $barang["id"];
            $sub_kode = $barang["sub_kode"];
            $kode_barang_lengkap = generateBarcode($id_barang, $sub_kode);
            // Link ke detail_barang.php menggunakan kode barang lengkap
            $detail_link = "http://10.10.11.114/web-peminjaman-barang/detail_barang.php?kode=" . $kode_barang_lengkap;
        ?>
            <div class="col-md-3 col-sm-4 col-6 mb-4">
                <div class="qr-card text-center">
                    <div class="qr-box" id="qrcode-<?= $id_barang . htmlspecialchars($sub_kode) ?>"></div>
                    <div class="qr-label">
                        <?= htmlspecialchars($barang["nama"]) ?>
                        <?php if (!empty($barang["seri"])): ?>
                            <br><small>(<?= htmlspecialchars($barang["seri"]) ?>)</small>
                        <?php endif; ?>
                        <br>
                        <small><?= htmlspecialchars($kode_barang_lengkap) ?></small><br>
                        <small><a href="<?= htmlspecialchars($detail_link) ?>" target="_blank" class="no-print"><?= htmlspecialchars($detail_link) ?></a></small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
<?php foreach ($inventarisItems as $barang):
    $id_barang = $barang["id"];
    $sub_kode = $barang["sub_kode"];
    // Baris ini memanggil fungsi generateBarcode yang sudah dimodifikasi
    $kode_barang_lengkap = generateBarcode($id_barang, $sub_kode);

    $detail_link = "http://10.10.11.114/web-peminjaman-barang/detail_barang.php?kode=" . $kode_barang_lengkap;
?>
new QRCode(document.getElementById("qrcode-<?= $id_barang . htmlspecialchars($sub_kode) ?>"), {
    text: "<?= htmlspecialchars($detail_link) ?>",
    width: 128,
    height: 128
});
<?php endforeach; ?>
</script>

</body>
</html>