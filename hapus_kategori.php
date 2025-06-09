<?php
include 'koneksi.php'; // Include your database connection file

// Check if the 'id' parameter is set in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Sanitize the ID to prevent SQL injection.
    // It's crucial for security to clean user inputs.
    $id = mysqli_real_escape_string($koneksi, $id);

    // SQL query to delete the record from the 'inventaris' table
    $sql = "DELETE FROM inventaris WHERE id = '$id'";

    // Execute the query
    if (mysqli_query($koneksi, $sql)) {
        // If deletion is successful, redirect back to the main category list page.
        // Assuming your main display page is named 'daftar_kategori.php'.
        header("Location: kategori-barang.php");
        exit(); // Always exit after a header redirect
    } else {
        // If there's an error, display it.
        echo "Error deleting record: " . mysqli_error($koneksi);
    }
} else {
    // If 'id' is not provided in the URL, display an error message.
    echo "ID item tidak disediakan untuk penghapusan.";
}

// Close the database connection
mysqli_close($koneksi);
?>