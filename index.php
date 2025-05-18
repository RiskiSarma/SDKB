<?php
session_start();

// Periksa level pengguna dan set parameter URL dengan benar
// Custom routing untuk homepage (ketika mengakses root atau dashboard)
if ((!isset($_GET['x']) || $_GET['x'] == '' || $_GET['x'] == 'dashboard' || $_GET['x'] == '.') && isset($_SESSION['level_deteksi'])) {
    // Redirect sesuai level pengguna
    if ($_SESSION['level_deteksi'] == 2) {
        $page = "dashboard_ortu_anak.php";
        include "main.php";
        exit;
    }
}

// Custom routing untuk hasil deteksi
if (isset($_GET['x']) && $_GET['x'] == 'hasil' && isset($_SESSION['level_deteksi']) && $_SESSION['level_deteksi'] == 2) {
    $_GET['x'] = 'hasil_deteksi_anak';
}

// Routing berdasarkan parameter URL
if (isset($_GET['x']) && $_GET['x'] == 'dashboard') {
    $page = "dashboard.php";
    include "main.php";
} elseif (isset($_GET['x']) && $_GET['x'] == 'user') {
    if ($_SESSION['level_deteksi'] == 1) {
        $page = "user.php";
        include "main.php";
    } else {
        $page = "dashboard.php";
        include "main.php";
    }
} elseif (isset($_GET['x']) && $_GET['x'] == 'student') {
    if ($_SESSION['level_deteksi'] == 1) {
        $page = "student.php";
        include "main.php";
    } else {
        $page = "dashboard.php";
        include "main.php";
    }
} elseif (isset($_GET['x']) && $_GET['x'] == 'assessment') {
    if ($_SESSION['level_deteksi'] == 1) {
        $page = "assessment.php";
        include "main.php";
    } else {
        $page = "dashboard.php";
        include "main.php";
    }
} elseif (isset($_GET['x']) && $_GET['x'] == 'detect') {
    if ($_SESSION['level_deteksi'] == 1) { // Hanya level 1 yang bisa mengakses
        $page = "detect.php";
        include "main.php";
    } else {
        $page = "dashboard.php";
        include "main.php";
    }
} elseif (isset($_GET['x']) && $_GET['x'] == 'report') {
    if ($_SESSION['level_deteksi'] == 1) {
        $page = "report.php";
        include "main.php";
    } else {
        $page = "dashboard.php";
        include "main.php";
    }
} elseif (isset($_GET['x']) && $_GET['x'] == 'data_aktual') {
    if ($_SESSION['level_deteksi'] == 1) {
        $page = "data_aktual.php";
        include "main.php";
    } else {
        $page = "dashboard.php";
        include "main.php";
    }
    }elseif (isset($_GET['x']) && $_GET['x'] == 'panduan') {
        if ($_SESSION['level_deteksi'] == 1) {
        $page = "panduan.php";
        include "main.php";
    } else {
        $page = "dashboard.php";
        include "main.php";
    }
}elseif (isset($_GET['x']) && $_GET['x'] == 'dashboard_ortu_anak') {
    if ($_SESSION['level_deteksi'] == 2) { // Hanya level 2 yang bisa mengakses
        $page = "dashboard_ortu_anak.php";
        include "main.php";
    } else {
        $page = "dashboard.php";
        include "main.php";
    }
} elseif (isset($_GET['x']) && $_GET['x'] == 'hasil_deteksi_anak') {
    if ($_SESSION['level_deteksi'] == 2) { // Hanya level 2 yang bisa mengakses
        $page = "hasil_deteksi_anak.php";
        include "main.php";
    } else {
        $page = "dashboard.php";
        include "main.php";
    }
} elseif (isset($_GET['x']) && $_GET['x'] == 'login') {
    include "login.php";
} elseif (isset($_GET['x']) && $_GET['x'] == 'logout') {
    include "proses/proses_logout.php";
} else {
    // Default page berdasarkan level pengguna
    if (isset($_SESSION['level_deteksi']) && $_SESSION['level_deteksi'] == 2) {
        $page = "dashboard_ortu_anak.php";
    } else {
        $page = "dashboard.php";
    }
    include "main.php";
}
?>