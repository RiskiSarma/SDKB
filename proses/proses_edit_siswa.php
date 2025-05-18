<?php
session_start();
include "connect.php";

// Sanitize and validate input
$name = isset($_POST['nama']) ? htmlentities($_POST['nama']) : "";
$kelas = isset($_POST['kelas']) ? htmlentities($_POST['kelas']) : "";
$jenis_kelamin = isset($_POST['jenis_kelamin']) ? htmlentities($_POST['jenis_kelamin']) : "";
$ortu_id = isset($_POST['ortu_id']) ? htmlentities($_POST['ortu_id']) : "";
$usia = isset($_POST['usia']) ? htmlentities($_POST['usia']) : "";
$student_id = isset($_POST['student_id']) ? htmlentities($_POST['student_id']) : "";

// Validate form submission
if (!empty($_POST['edit_siswa_validate'])) {
    // Check if connection to the database is successful
    if ($conn) {
        // Prepare UPDATE query for students table
        $query = mysqli_query($conn, "UPDATE students SET 
            name='$name', 
            class='$kelas', 
            gender='$jenis_kelamin', 
            parent_id='$ortu_id', 
            usia='$usia' 
            WHERE student_id='$student_id'");

        // Check if update was successful
        if ($query) {
            $message = '<script>alert("Data siswa berhasil diupdate");
                        window.location="../student";</script>';
        } else {
            $message = '<script>alert("Gagal mengupdate data siswa: ' . mysqli_error($conn) . '");</script>';
        }
    } else {
        $message = '<script>alert("Koneksi ke database gagal");</script>';
    }
}

// Output the message
echo $message;
?>
