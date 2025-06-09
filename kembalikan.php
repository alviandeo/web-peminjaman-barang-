<?php
include 'config.php';

$log_id = $_POST['log_id'];

// Ambil ID barang
$result = mysqli_query($conn, "SELECT jenis_barang_id FROM log_barang WHERE id = $log_id");
$data = mysqli_fetch_assoc($result);
$barang_id = $data['jenis_barang_id'];

// Update tanggal kembali dan status barang
mysqli_query($conn, "UPDATE log_barang SET tanggal_kembali = NOW() WHERE id = $log_id");
mysqli_query($conn, "UPDATE barang SET status = 'tersedia' WHERE id = $barang_id");

header("Location: daftar-peminjaman-barang.php");
exit;
?>
