<!-- sidebar -->
<div class="col-lg-3">
    <nav class="navbar navbar-expand-lg bg-light rounded border mt-2">
        <div class="container-fluid">
            <button class="navbar-toggler no-print" type="button" data-bs-toggle="offcanvas" 
            data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel"
            style="width:200px">
                <div class="offcanvas-header">
                    <h5 class="text-center">SDKB System</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav flex-column nav-pills justify-content-start flex-grow-1">
                        <!-- Menu untuk Level 1 (Admin) -->
                        <?php if ($_SESSION['level_deteksi'] == 1) { ?>
                            <li class="nav-item">
                                <a class="nav-link ps-2 <?php echo (isset($_GET['x']) && $_GET['x'] == 'dashboard') ? 'active link-light' : 'link-dark'; ?>"
                                href="dashboard"><i class="bi bi-house-door-fill"></i> Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link ps-2 <?php echo (isset($_GET['x']) && $_GET['x'] == 'user') ? 'active link-light' : 'link-dark'; ?>"
                                href="user"><i class="bi bi-people"></i> Data User</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link ps-2 <?php echo (isset($_GET['x']) && $_GET['x'] == 'student') ? 'active link-light' : 'link-dark'; ?>"
                                href="student"><i class="bi bi-person"></i> Data Siswa</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link ps-2 <?php echo (isset($_GET['x']) && $_GET['x'] == 'assessment') ? 'active link-light' : 'link-dark'; ?>"
                                href="assessment"><i class="bi bi-plus-circle"></i> Assessment</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link ps-2 <?php echo (isset($_GET['x']) && $_GET['x'] == 'detect') ? 'active link-light' : 'link-dark'; ?>"
                                href="detect"><i class="bi bi-database"></i> Hasil Deteksi</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link ps-2 <?php echo (isset($_GET['x']) && $_GET['x'] == 'report') ? 'active link-light' : 'link-dark'; ?>"
                                href="report"><i class="bi bi-file-earmark-text-fill"></i> Laporan</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link ps-2 <?php echo (isset($_GET['x']) && $_GET['x'] == 'data_aktual') ? 'active link-light' : 'link-dark'; ?>"
                                href="data_aktual"><i class="bi bi-database"></i> Data Aktual</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link ps-2 <?php echo (isset($_GET['x']) && $_GET['x'] == 'panduan') ? 'active link-light' : 'link-dark'; ?>"
                                href="panduan"><i class="bi bi-book"></i> Panduan Assessment</a>
                            </li>
                        <?php } ?>

                        <!-- Menu untuk Level 2 (Orang Tua) -->
                        <?php if ($_SESSION['level_deteksi'] == 2) { ?>
                            <li class="nav-item">
                                <a class="nav-link ps-2 <?php echo (isset($_GET['x']) && $_GET['x'] == 'dashboard_ortu_anak') ? 'active link-light' : 'link-dark'; ?>"
                                href="dashboard_ortu_anak"><i class="bi bi-house-door-fill"></i> Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link ps-2 <?php echo (isset($_GET['x']) && $_GET['x'] == 'hasil_deteksi_anak') ? 'active link-light' : 'link-dark'; ?>"
                                href="hasil_deteksi_anak"><i class="bi bi-database"></i> Hasil Deteksi Anak</a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</div>
<!-- end sidebar -->