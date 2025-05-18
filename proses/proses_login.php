<?php
session_start();
include "connect.php";

$username = (isset($_POST['username'])) ? htmlentities($_POST['username']) : "";
$password = (isset($_POST['password'])) ? md5(htmlentities($_POST['password'])) : "";
$user_type = (isset($_POST['user_type'])) ? htmlentities($_POST['user_type']) : ""; // Tipe user yang dipilih (parent atau teacher)

if (!empty($_POST['submit_validate'])) {
    // Query untuk mencari user berdasarkan username dan password
    $query = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username' && password = '$password'");
    $hasil = mysqli_fetch_array($query);

    if ($hasil) {
        // Validasi level user
        if (($user_type == 'parent' && $hasil['level'] == 2) || ($user_type == 'teacher' && $hasil['level'] == 1)) {
            // Set session
            $_SESSION['username_deteksi'] = $username;
            $_SESSION['user_id'] = $hasil['user_id']; // Set user_id
            $_SESSION['level_deteksi'] = $hasil['level'];

            // Redirect berdasarkan level
            if ($hasil['level'] == 1) {
                header('location:../index.php?x=dashboard'); // Redirect ke dashboard admin/guru
            } elseif ($hasil['level'] == 2) {
                header('location:../index.php?x=dashboard_ortu_anak'); // Redirect ke dashboard orang tua
            }
        } else {
            // Jika level user tidak sesuai
            ?>
            <script>
                alert('Akun yang Anda masukkan tidak sesuai dengan tipe user yang dipilih.');
                window.location='../login';
            </script>
            <?php
        }
    } else {
        // Jika username atau password salah
        ?>
        <script>
            alert('Username atau password salah.');
            window.location='../login';
        </script>
        <?php
    }
}
?>