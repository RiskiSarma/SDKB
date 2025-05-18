<?php
// Buat koneksi
$conn = mysqli_connect("localhost", "root", "", "deteksi");

// Periksa koneksi
if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>
