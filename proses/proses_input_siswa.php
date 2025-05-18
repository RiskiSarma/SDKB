<?php
session_start();
include "connect.php";

// Sanitize and retrieve form inputs
$nama = (isset($_POST['nama'])) ? htmlentities($_POST['nama']) : "";
$kelas = (isset($_POST['kelas'])) ? htmlentities($_POST['kelas']) : "";
$jenis_kelamin = (isset($_POST['jenis_kelamin'])) ? htmlentities($_POST['jenis_kelamin']) : "";
$ortu_id = (isset($_POST['ortu_id'])) ? htmlentities($_POST['ortu_id']) : "";
$usia = (isset($_POST['usia'])) ? htmlentities($_POST['usia']) : "";

// Check if the form submission is validated
if(!empty($_POST['input_siswa_validate'])){
    // Prepare SQL query to insert student data
    $query = mysqli_query($conn, "INSERT INTO students 
        (name, class, gender, parent_id, usia) 
        VALUES ('$nama', '$kelas', '$jenis_kelamin', '$ortu_id', '$usia')");
    
    // Check if query was successful
    if($query){
        $message = '<script>alert("Data siswa berhasil dimasukkan");
                    window.location="../student";</script>';
    } else {
        $message = '<script>alert("Data siswa gagal dimasukkan: ' . mysqli_error($conn) . '")</script>';
    }
    echo $message;
}
?>