<?php
include 'config.php'; // Pastikan config.php sudah mengatur zona waktu dengan benar

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil nama_pengembali dari input form pengembalian
    $nama_pengembali_form = mysqli_real_escape_string($conn, $_POST['nama_pengembali']); // Ini yang akan kita gunakan untuk update kolom 'nama'

    $borrowed_item_ids = $_POST['borrowed_item_ids'];
    $dikembalikan_kepada_id = $_POST['dengan_siapa'];
    $signature_pengembali = mysqli_real_escape_string($conn, $_POST['signature_pengembali']);

    if (empty($nama_pengembali_form) || empty($borrowed_item_ids) || empty($dikembalikan_kepada_id) || empty($signature_pengembali)) {
        echo "<script>alert('Semua kolom wajib diisi, termasuk tanda tangan!'); window.history.back();</script>";
        exit;
    }

    $tanggal_pengembalian = date('Y-m-d H:i:s');
    $status_kembali = 'dikembalikan';

    $orangList = [
        1 => "Pak Trio", 2 => "Pak Budi", 3 => "Pak Yeyen", 4 => "Pak Aang",
        5 => "Pak Eko", 6 => "Pak Danang", 7 => "Pak Gatot"
    ];
    $dikembalikan_kepada_nama_untuk_db = $orangList[$dikembalikan_kepada_id] ?? 'Tidak Diketahui';
    $dikembalikan_kepada_nama_untuk_db = mysqli_real_escape_string($conn, $dikembalikan_kepada_nama_untuk_db);

    foreach ($borrowed_item_ids as $peminjaman_id) {
        $query = "UPDATE peminjaman_barang
                    SET status = '$status_kembali',
                        tanggal_pengembalian = '$tanggal_pengembalian',
                        tanda_tangan_pengembalian = '$signature_pengembali',
                        dikembalikan_kepada = '$dikembalikan_kepada_nama_untuk_db',
                        nama = '$nama_pengembali_form' /* BARIS PENTING: Mengganti nama peminjam dengan nama pengembali */
                    WHERE id = '$peminjaman_id'";

        $result = mysqli_query($conn, $query);

        if (!$result) {
            echo "<script>alert('Gagal mengembalikan barang (ID: " . htmlspecialchars($peminjaman_id) . "): " . mysqli_error($conn) . "'); window.history.back();</script>";
            exit;
        }
    }

    mysqli_close($conn);
    echo "<script>window.location.href='pengembalian-barang.php';</script>";
    exit;
} else {
    echo "<script>alert('Metode tidak diizinkan!'); window.history.back();</script>";
    exit;
}
?>