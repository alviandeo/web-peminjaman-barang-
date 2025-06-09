<?php
include 'koneksi.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Hapus data dengan ID yang ditentukan
    $hapus = $koneksi->query("DELETE FROM inventaris WHERE id = $id");

    if ($hapus) {
        // Ambil semua data setelah ID yang dihapus
        $result = $koneksi->query("SELECT * FROM inventaris WHERE id > $id ORDER BY id ASC");

        while ($row = $result->fetch_assoc()) {
            $oldId = $row['id'];
            $newId = $oldId - 1;
            $kode_barang = 'BRG' . str_pad($newId, 3, '0', STR_PAD_LEFT);

            // Update id dan kode_barang
            $koneksi->query("UPDATE inventaris SET id = $newId WHERE id = $oldId");
        }

        echo "<script>alert('Barang berhasil dihapus dan kode disusun ulang!'); location='daftar-inventaris-barang.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus barang!'); history.back();</script>";
    }
}
?>

