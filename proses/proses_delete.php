<?php
    session_start();
    include "connect.php";
    $id =  (isset($_POST['id'])) ? htmlentities($_POST['id']) : "" ;

    if(!empty($_POST['input_user_validate'])){
        $query = mysqli_query($conn, "DELETE FROM users WHERE user_id='$id'");
        if($query){
            $message = '<script>alert("Data berhasil diupdate");
                        window.location="../user"</script>
                        </script>';
        }else{
            $message = '<script>alert("Data gagal diupdate")</script>';
            }
    }echo $message;
?>