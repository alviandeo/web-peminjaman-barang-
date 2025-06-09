<?php
$host = "localhost";
$user = "root";
$pass = ""; // sesuaikan dengan password database kamu
$dbname = "web-peminjaman-barang"; // ganti dengan nama database kamu

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// *** PENTING: Atur zona waktu PHP ke Asia/Jakarta (WIB) ***
date_default_timezone_set('Asia/Jakarta');

?>




