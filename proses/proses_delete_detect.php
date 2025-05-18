<?php
    session_start();
    include "connect.php";
    $id = (isset($_POST['student_id'])) ? htmlentities($_POST['student_id']) : "";

    if(!empty($_POST['hapus_detect_validate'])){
        // Only delete one specific record based on the student_id
        // This ensures only one record is deleted even if there are multiple with the same name
        $query = mysqli_query($conn, "DELETE FROM assessment_results WHERE student_id='$id' LIMIT 1");
        
        if($query){
            $message = '<script>alert("Data hasil deteksi berhasil dihapus");
                        window.location="../detect"</script>';
        }else{
            $message = '<script>alert("Data hasil deteksi gagal dihapus")</script>';
        }
    }
    echo $message;
?>