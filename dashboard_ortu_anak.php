<?php
    // Mulai session jika belum dimulai
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Pastikan pengguna sudah login dan levelnya adalah 2 (orang tua)
    if (!isset($_SESSION['level_deteksi']) || $_SESSION['level_deteksi'] != 2) {
        header("Location: login.php"); // Redirect ke halaman login jika belum login atau bukan level 2
        exit();
    }

    // Include file koneksi database
    include "proses/connect.php"; // Pastikan file koneksi database sudah ada dan bernama "connect.php"

    // Ambil ID orang tua dari session
    if (!isset($_SESSION['user_id'])) {
        die("Session user_id tidak ditemukan. Pastikan session di-set dengan benar saat login.");
    }
    $user_id = $_SESSION['user_id'];

    // Query untuk mengambil data anak berdasarkan ID orang tua
    $query_anak = "SELECT s.*, u.fullname as parent_name 
                   FROM students s 
                   LEFT JOIN users u ON s.parent_id = u.user_id 
                   WHERE s.parent_id = '$user_id'";
    $result_anak = mysqli_query($conn, $query_anak);

    if (!$result_anak) {
        die("Query error: " . mysqli_error($conn));
    }

    $anak = mysqli_fetch_assoc($result_anak); // Ambil data anak

    // Jika data anak tidak ditemukan, beri pesan error
    if (!$anak) {
        die("Data anak tidak ditemukan untuk orang tua ini.");
    }

    // Query untuk mengambil hasil deteksi terbaru
    $id_anak = $anak['student_id'];
    
    // Perubahan pada query untuk mengambil data terbaru dengan ORDER BY tanggal DESC dan LIMIT 1
    $query_deteksi = "SELECT motorik_halus, motorik_kasar, komunikasi, membaca, pra_akademik, sosial_skill, ekspresif, menyimak, prediction, tanggal 
                      FROM assessment_results 
                      WHERE student_id = '$id_anak' 
                      ORDER BY tanggal DESC 
                      LIMIT 1";
    $result_deteksi = mysqli_query($conn, $query_deteksi);

    if (!$result_deteksi) {
        die("Query error: " . mysqli_error($conn));
    }

    $deteksi = mysqli_fetch_assoc($result_deteksi); // Ambil data deteksi terbaru

    // Query untuk menghitung jumlah deteksi yang dilakukan
    $query_jumlah_deteksi = "SELECT COUNT(*) as total FROM assessment_results WHERE student_id = '$id_anak'";
    $result_jumlah = mysqli_query($conn, $query_jumlah_deteksi);
    $jumlah_deteksi = mysqli_fetch_assoc($result_jumlah)['total'];

    // Jika tidak ada hasil deteksi, inisialisasi variabel deteksi dengan nilai default
    if (!$deteksi) {
        $deteksi = [
            'prediction' => 'Belum ada data',
            'motorik_halus' => 0,
            'motorik_kasar' => 0,
            'komunikasi' => 0,
            'membaca' => 0,
            'pra_akademik' => 0,
            'sosial_skill' => 0,
            'ekspresif' => 0,
            'menyimak' => 0,
            'tanggal' => '-'
        ];
        $jumlah_deteksi = 0;
    }

    // Hitung skor motorik, bahasa, dan kognitif berdasarkan data terbaru
    $motorik_score = ($deteksi['motorik_halus'] + $deteksi['motorik_kasar']) / 2;
    $bahasa_score = ($deteksi['komunikasi'] + $deteksi['membaca'] + $deteksi['pra_akademik']) / 3;
    $kognitif_score = ($deteksi['sosial_skill'] + $deteksi['ekspresif'] + $deteksi['menyimak']) / 3;

    // Hitung total penilaian dan rata-rata nilai
    $total_penilaian = $motorik_score + $bahasa_score + $kognitif_score;
    $rata_nilai = $total_penilaian / 3;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Orang Tua</title>
</head>
<body class="bg-light">
<!-- <div class="col-lg-9 mt-2"> -->
    <div class="col-lg-9 mt-2">

    <div class="container-fluid">
        <div class="row">
            <!-- Main Content -->
                <div class="space-y-4">
                    <!-- Informasi Siswa -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="card-title h5 mb-0">Informasi Siswa</h2>
                                <?php if ($jumlah_deteksi > 0): ?>
                                <span class="badge bg-info"><?= $jumlah_deteksi ?> kali deteksi</span>
                                <?php endif; ?>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="text-muted">Nama Siswa</p>
                                    <p class="fw-bold"><?= htmlspecialchars($anak['name']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted">Kelas</p>
                                    <p class="fw-bold"><?= htmlspecialchars($anak['class']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted">Jenis Kelamin</p>
                                    <p class="fw-bold"><?= htmlspecialchars($anak['gender']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted">Nama Orang Tua</p>
                                    <p class="fw-bold"><?= htmlspecialchars($anak['parent_name']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Penilaian Terkini -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="card-title h5">Penilaian Terkini</h2>
                                <?php if ($deteksi['tanggal'] != '-'): ?>
                                <span class="badge bg-secondary"><?= htmlspecialchars($deteksi['tanggal']) ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="text-muted">Status Perkembangan</p>
                            <p class="<?= strtolower($deteksi['prediction']) == 'terlambat' ? 'text-warning' : 'text-success' ?> fw-bold mb-4">
                                <?= htmlspecialchars($deteksi['prediction']) ?>
                            </p>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-muted">Kognitif</p>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar" role="progressbar" style="width: <?= htmlspecialchars($kognitif_score) ?>%;" aria-valuenow="<?= htmlspecialchars($kognitif_score) ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-end text-muted"><?= number_format($kognitif_score, 1) ?>%</p>
                                </div>
                                <div>
                                    <p class="text-muted">Bahasa</p>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar" role="progressbar" style="width: <?= htmlspecialchars($bahasa_score) ?>%;" aria-valuenow="<?= htmlspecialchars($bahasa_score) ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-end text-muted"><?= number_format($bahasa_score, 1) ?>%</p>
                                </div>
                                <div>
                                    <p class="text-muted">Motorik</p>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar" role="progressbar" style="width: <?= htmlspecialchars($motorik_score) ?>%;" aria-valuenow="<?= htmlspecialchars($motorik_score) ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-end text-muted"><?= number_format($motorik_score, 1) ?>%</p>
                                </div>
                            </div>
                            <?php if ($jumlah_deteksi > 1): ?>
                            <div class="mt-3">
                                <a href="hasil_deteksi_anak" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-clock-history"></i> Lihat Riwayat Deteksi
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Statistik dalam satu card -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Statistik Perkembangan</h5>
                            <div class="row g-3 mt-2">
                                <!-- Total Penilaian -->
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-bar-chart-fill text-primary fs-3 me-3"></i>
                                            <div>
                                                <p class="text-muted mb-0">Total Penilaian</p>
                                                <p class="h4 fw-bold"><?= number_format($total_penilaian, 1) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Rata-rata Nilai -->
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-graph-up text-success fs-3 me-3"></i>
                                            <div>
                                                <p class="text-muted mb-0">Rata-rata Nilai</p>
                                                <p class="h4 fw-bold"><?= number_format($rata_nilai, 1) ?>%</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Perkembangan -->
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-person-check <?= strtolower($deteksi['prediction']) == 'terlambat' ? 'text-warning' : 'text-success' ?> fs-3 me-3"></i>
                                            <div>
                                                <p class="text-muted mb-0">Perkembangan</p>
                                                <p class="h4 fw-bold <?= strtolower($deteksi['prediction']) == 'terlambat' ? 'text-warning' : 'text-success' ?>">
                                                    <?= htmlspecialchars($deteksi['prediction']) ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Tambahan -->
                    <?php if ($jumlah_deteksi > 0 && strtolower($deteksi['prediction']) == 'normal'): ?>
                    <div class="card shadow-sm bg-success-subtle">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-check-circle-fill text-success me-2"></i> Perkembangan Normal</h5>
                            <p class="card-text">
                                Berdasarkan hasil deteksi terbaru, anak Anda berada dalam kategori perkembangan normal. Terus dukung perkembangan anak dengan aktivitas yang menstimulasi semua aspek perkembangan.
                            </p>
                        </div>
                    </div>
                    <?php elseif ($jumlah_deteksi > 0 && strtolower($deteksi['prediction']) == 'terlambat'): ?>
                    <div class="card shadow-sm bg-warning-subtle">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> Perhatian Diperlukan</h5>
                            <p class="card-text">
                                Berdasarkan hasil deteksi terbaru, anak Anda memerlukan perhatian tambahan pada beberapa area perkembangan. Silakan lihat rekomendasi yang diberikan di halaman hasil deteksi.
                            </p>
                            <a href="hasil_deteksi_anak" class="btn btn-warning btn-sm mt-2">
                                Lihat Rekomendasi
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<?php
    // Tutup koneksi database
    mysqli_close($conn);
?>