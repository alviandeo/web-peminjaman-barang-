<?php
include 'config.php';
$stmt = $conn->prepare("UPDATE barang SET nama=?, spek=?, seri=?, tahun=?, stok=?, rusak=? WHERE id=?");
$stmt->bind_param("sssiiii", $_POST['nama'], $_POST['spek'], $_POST['seri'], $_POST['tahun'], $_POST['stok'], $_POST['rusak'], $_POST['id']);
$stmt->execute();
header("Location: daftar-inventaris-barang.php");
