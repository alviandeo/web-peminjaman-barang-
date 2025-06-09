<?php
include 'koneksi.php';

// Ambil data inventaris, pastikan sub_kode tidak kosong dan urutkan berdasarkan kategori
$barang_query = $koneksi->query("SELECT * FROM inventaris WHERE sub_kode != '' ORDER BY sub_kode ASC, id ASC");

$barang_groups = [];
while ($barang = $barang_query->fetch_assoc()) {
    $kategori = $barang['sub_kode'];   // Memisahkan berdasarkan sub_kode
    $barang_groups[$kategori][] = $barang;   // Kelompokkan barang berdasarkan sub_kode
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Daftar Kategori Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .category-item {
            background-color: #f9f9f9;
            border-left: 5px solid #007bff;
        }
        .category-item:hover {
            background-color: #f1f1f1;
        }
        .badge.bg-info {
            background-color: #0dcaf0 !important;
            color: #000 !important;
        }

        /* CSS tambahan untuk responsivitas tabel */
        @media (max-width: 767.98px) {
            .table-responsive-sm .table {
                font-size: 0.85rem; /* Ukuran font lebih kecil untuk layar kecil */
            }
            .table-responsive-sm .table th,
            .table-responsive-sm .table td {
                padding: 0.5rem; /* Padding lebih kecil */
            }
            .table-responsive-sm .btn-sm {
                padding: 0.25rem 0.5rem; /* Ukuran tombol lebih kecil */
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body class="bg-light p-4">
<div class="container">
    <div class="card shadow p-4">
        <h3 class="mb-4 text-primary">Daftar Kategori Barang (Sub Kode)</h3>
        <div class="mb-3">
            <a href="daftar-inventaris-barang.php" class="btn btn-secondary">← Kembali ke Inventaris</a>
        </div>

        <?php
        $no = 1; // Nomor urut untuk kategori utama
        foreach ($barang_groups as $kategori_group_key => $barang_list):
            // The $kategori_group_key here is actually the full sub_kode from the database (e.g., BRG001a)
            // Extract the main code (e.g., BRG001) for the category title
            preg_match('/^(BRG\d{3})/', $kategori_group_key, $matches);
            $kode_kategori_display = $matches[1] ?? 'N/A'; // Fallback if pattern not found

            // Extract the last character for the sub_kode in the title
            $sub_kode_title_display = substr($kategori_group_key, -1);
        ?>
            <h4 class="mb-3">Kategori Barang: <?= htmlspecialchars($kode_kategori_display) ?> (Sub Kode: <?= htmlspecialchars($sub_kode_title_display) ?>)</h4>
            <div class="table-responsive"> <table class="table table-bordered table-striped">
                    <thead class="table-primary">
                    <tr>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Spesifikasi</th>
                        <th>Tahun</th>
                        <th>Seri</th>
                        <th>Stok</th>
                        <th>Kondisi</th>
                        <th>Sub Kode</th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($barang_list as $row):
                        // The 'sub_kode' in $row directly contains the combined code from insertion (e.g., BRG004b)
                        $full_sub_kode_from_db = $row['sub_kode'];

                        // Kode for the 'Kode' column: this is the full sub_kode from the database
                        $kode_display = htmlspecialchars($full_sub_kode_from_db);

                        // Sub Kode for the 'Sub Kode' column: this is the last character of the sub_kode from the database
                        $sub_kode_display = htmlspecialchars(substr($full_sub_kode_from_db, -1));

                        $kondisi = $row['rusak'] == 1 ? 'Rusak' : 'Baik';
                    ?>
                        <tr class="category-item">
                            <td><?= $kode_display ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['spek']) ?></td>
                            <td><?= htmlspecialchars($row['tahun']) ?></td>
                            <td><?= htmlspecialchars($row['seri']) ?></td>
                            <td><?= htmlspecialchars($row['stok']) ?></td>
                            <td>
                                <span class="badge bg-<?= $row['rusak'] == 1 ? 'danger' : 'success' ?>">
                                    <?= htmlspecialchars($kondisi) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= $sub_kode_display ?></span>
                            </td>
                            <td>
                                 
                                <a href="hapus_kategori.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (count($barang_list) == 0): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">Belum ada data kategori barang.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div> <?php
            $no++; // Increment for the main category grouping, though not directly used for the 'BRGxxx' part now as it's derived from DB.
        endforeach; ?>
    </div>
</div>
</body>
</html>