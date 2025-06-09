<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $dengan_siapa_id = $_POST['dengan_siapa'];
    $jenis_barang_ids = $_POST['jenis_barang'];
    
    $tanggal_pinjam = date("Y-m-d H:i:s");
    $status = 'dipinjam';
    $signature_peminjam = mysqli_real_escape_string($conn, $_POST['signature_peminjam']);

    if (empty($nama) || empty($dengan_siapa_id) || empty($jenis_barang_ids) || empty($signature_peminjam)) {
        echo "<script>alert('Semua kolom wajib diisi, termasuk tanda tangan!'); window.history.back();</script>";
        exit;
    }

    $orangList = [
        1 => "Pak Trio", 2 => "Pak Budi", 3 => "Pak Yeyen", 4 => "Pak Aang",
        5 => "Pak Eko", 6 => "Pak Danang", 7 => "Pak Gatot"
    ];
    $dengan_siapa_nama_untuk_db = $orangList[$dengan_siapa_id] ?? 'Tidak Diketahui';
    $dengan_siapa_nama_untuk_db = mysqli_real_escape_string($conn, $dengan_siapa_nama_untuk_db);

    $jenis_barang_nama_lengkap_array = [];
    if (!empty($jenis_barang_ids)) {
        $ids_untuk_query_in = implode(',', array_map('intval', $jenis_barang_ids));
        $query_inventaris = "SELECT id, nama, seri FROM inventaris WHERE id IN ($ids_untuk_query_in)";
        $result_inventaris = mysqli_query($conn, $query_inventaris);

        if ($result_inventaris) {
            while ($row_inventaris = mysqli_fetch_assoc($result_inventaris)) {
                $display_name = $row_inventaris['nama'];
                if (!empty($row_inventaris['seri'])) {
                    $display_name .= ' (' . $row_inventaris['seri'] . ')';
                }
                $jenis_barang_nama_lengkap_array[$row_inventaris['id']] = $display_name;
            }
        } else {
            echo "<script>alert('Gagal mengambil data inventaris: " . mysqli_error($conn) . "'); window.history.back();</script>";
            exit;
        }
    }

    foreach ($jenis_barang_ids as $barang_id_dari_form) {
        $jenis_barang_nama_untuk_db = $jenis_barang_nama_lengkap_array[$barang_id_dari_form] ?? 'Nama Barang Tidak Ditemukan';
        $jenis_barang_nama_untuk_db = mysqli_real_escape_string($conn, $jenis_barang_nama_untuk_db);

        $query = "INSERT INTO peminjaman_barang (nama, jenis_barang, dengan_siapa, status, tanggal_peminjaman, tanda_tangan_peminjaman)
        VALUES (
            '$nama', 
            '$jenis_barang_nama_untuk_db', 
            '$dengan_siapa_nama_untuk_db', 
            '$status', 
            '$tanggal_pinjam', 
            '$signature_peminjam'
        )";

        $result = mysqli_query($conn, $query);

        if (!$result) {
            echo "<script>alert('Gagal menyimpan data: " . mysqli_error($conn) . "'); window.history.back();</script>";
            exit;
        }
    }

    mysqli_close($conn);
    echo "<script>window.location='peminjaman-barang.php';</script>";
    exit;
} else {
    echo "<script>alert('Metode tidak diizinkan!'); window.history.back();</script>";
    exit;
}
?>