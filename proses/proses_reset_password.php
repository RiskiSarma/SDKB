<?php
    session_start();
    include "connect.php";
    $id =  (isset($_POST['id'])) ? htmlentities($_POST['id']) : "" ;

    if(!empty($_POST['input_user_validate'])){
        $query = mysqli_query($conn, "UPDATE users SET password=md5('admin123') WHERE user_id='$id'");
        if($query){
            $message = '<script>alert("Password berhasil direset");
                        window.location="../user"</script>
                        </script>';
        }else{
            $message = '<script>alert("Password gagal direset")</script>';
            }
    }echo $message;
?>