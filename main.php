<?php  
    // session_start(); 
    if(empty($_SESSION['username_deteksi'])) {
        header('Location:login');
    }

    include "proses/connect.php";
    $query = mysqli_query($conn, "SELECT * FROM users WHERE username = '$_SESSION[username_deteksi]'");
    $hasil = mysqli_fetch_array($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDKB System</title>
    <!-- <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" 
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css">
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js"></script>

    <!-- <style>
        .offcanvas {
            width: 250px;
            background-color: #1E3A8A;
            color: white;
            min-height: 100vh;
            position: fixed;
            top: 55px;
            left: 0;
        }
        .offcanvas .nav-link {
            color: white;
        }
        .offcanvas .nav-link.active, .offcanvas .nav-link:hover {
            background-color: #1E3A8A;
        }
        .content {
            flex: 1;
            padding: 20px;
        }
    </style> -->
</head>
<body style="height: 300px">
<!-- header -->
<?php include "header.php"; ?>
<!-- end header -->

<div class="container-lg">
    <div class="row">
        <!-- sidebar -->
        <?php include "sidebar.php";?>
        <!-- end sidebar -->
        <!-- content -->
        <?php 
            include $page
        ?>
        <!-- end content -->
    </div>
</div>
<div class="fixed-bottom text-center mb-2">
    2025 SDKB System
</div>


<!-- <div class="d-flex"> -->
        <!-- Sidebar -->
        <!-- <div class="sidebar p-3">
            <h2 class="text-center">SDKB System</h2>
            <nav class="nav flex-column mt-4">
                <a href="#" class="nav-link active" id="dashboard-link"><i class="fas fa-home"></i> Dashboard</a>
                <a href="#" class="nav-link" id="students-link"><i class="fas fa-users"></i> Data Siswa</a>
                <a href="#" class="nav-link" id="assessment-link"><i class="fas fa-plus-circle"></i> Assessment</a>
                <a href="#" class="nav-link" id="detection-link"><i class="fas fa-database"></i> Hasil Deteksi</a>
                <a href="#" class="nav-link" id="reports-link"><i class="fas fa-file-alt"></i> Laporan</a>
                <a href="#" class="nav-link" id="settings-link"><i class="fas fa-cog"></i> Pengaturan</a>
                <a href="#" class="nav-link text-danger mt-4" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div> -->
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script> -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous">
    </script>
<script>
    (function () {
        'use strict';

        // Ambil semua form yang memiliki class 'needs-validation'
        var forms = document.querySelectorAll('.needs-validation');

        // Loop melalui semua form dan tambahkan event listener untuk validasi
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault(); // Mencegah form disubmit jika tidak valid
                    event.stopPropagation();
                }

                form.classList.add('was-validated'); // Tambahkan class 'was-validated' untuk menampilkan pesan error
            }, false);
        });
    })();
</script>
<script>
        $(document).ready( function () {
          $('#example').DataTable();
        } );
</script>
</body>
</html>