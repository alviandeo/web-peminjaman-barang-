


<?php
$koneksi = new mysqli("localhost", "root", "", "web-peminjaman-barang");

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
?>

