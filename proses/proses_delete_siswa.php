<?php
    session_start();
    include "connect.php";
    $id =  (isset($_POST['student_id'])) ? htmlentities($_POST['student_id']) : "" ;

    if(!empty($_POST['hapus_siswa_validate'])){
        $query = mysqli_query($conn, "DELETE FROM students WHERE student_id='$id'");
        if($query){
            $message = '<script>alert("Data siswa berhasil dihapus");
                        window.location="../student"</script>';
        }else{
            $message = '<script>alert("Data siswa gagal dihapus")</script>';
        }
    }
    echo $message;
?>