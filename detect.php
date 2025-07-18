<?php
include "proses/connect.php";

// Function to calculate score and predicate based on raw data (for display only)
function calculate_score_and_predicate($score, $max_score) {
    $numeric_score = ($score / $max_score) * 100;
    if ($numeric_score >= 90) {
        return ['score' => round($numeric_score, 1), 'predicate' => 'A', 'label' => 'Sangat Baik'];
    } elseif ($numeric_score >= 75) {
        return ['score' => round($numeric_score, 1), 'predicate' => 'B', 'label' => 'Baik'];
    } elseif ($numeric_score >= 60) {
        return ['score' => round($numeric_score, 1), 'predicate' => 'C', 'label' => 'Cukup'];
    } else {
        return ['score' => round($numeric_score, 1), 'predicate' => 'D', 'label' => 'Kurang'];
    }
}

// Query to fetch data (simplified to match previous behavior)
$query = "SELECT s.student_id, s.name, 
          a.id as assessment_id, 
          a.motorik_halus, a.motorik_kasar, 
          a.komunikasi, a.membaca, a.pra_akademik, 
          a.sosial_skill, a.ekspresif, a.menyimak,
          a.prediction, a.rekomendasi, a.tanggal
          FROM students s
          LEFT JOIN assessment_results a ON s.student_id = a.student_id
          ORDER BY a.tanggal DESC";

$result = mysqli_query($conn, $query);

// Convert to array for consistent handling
if ($result) {
    $result_array = array();
    while ($row = mysqli_fetch_assoc($result)) {
        if (!empty($row['student_id'])) { // Ensure row has data
            // Use raw scores as stored
            $motorik_score = $row['motorik_halus'] + $row['motorik_kasar'];
            $bahasa_score = $row['komunikasi'] + $row['membaca'] + $row['pra_akademik'];
            $kognitif_score = $row['sosial_skill'] + $row['ekspresif'] + $row['menyimak'];

            // Calculate scores and predicates for display
            $motorik_result = calculate_score_and_predicate($motorik_score, 8);
            $bahasa_result = calculate_score_and_predicate($bahasa_score, 12);
            $kognitif_result = calculate_score_and_predicate($kognitif_score, 12);

            // Extract delayed aspects from prediction
            $delayed_aspects = [];
            if (strpos($row['prediction'], 'Terlambat') === 0) {
                $parts = explode(': ', $row['prediction']);
                if (count($parts) > 1) {
                    $delayed_aspects = explode(', ', trim($parts[1]));
                }
            }

            // Parse rekomendasi JSON from database
            $recommendations = [];
            if (isset($row['rekomendasi']) && !empty($row['rekomendasi'])) {
                $recommendations = json_decode($row['rekomendasi'], true);
            }

            $row['motorik_score'] = $motorik_result['score'];
            $row['motorik_predicate'] = $motorik_result['predicate'];
            $row['motorik_label'] = $motorik_result['label'];
            $row['bahasa_score'] = $bahasa_result['score'];
            $row['bahasa_predicate'] = $bahasa_result['predicate'];
            $row['bahasa_label'] = $bahasa_result['label'];
            $row['kognitif_score'] = $kognitif_result['score'];
            $row['kognitif_predicate'] = $kognitif_result['predicate'];
            $row['kognitif_label'] = $kognitif_result['label'];
            $row['delayed_aspects'] = $delayed_aspects;
            $row['recommendations'] = $recommendations;

            $result_array[] = $row;
        }
    }
    mysqli_free_result($result);
    $result = $result_array;
} else {
    $result = array(); // Empty array in case of query error
}

// Handle new assessment highlight (simplified)
$new_assessment_alert = '';
$auto_open_modal = '';
if (isset($_GET['new_assessment']) && isset($_GET['student_id']) && isset($_GET['status'])) {
    $student_id = mysqli_real_escape_string($conn, $_GET['student_id']);
    $status = $_GET['status'];
    $query_name = mysqli_query($conn, "SELECT name FROM students WHERE student_id = '$student_id'");
    $student_name = mysqli_fetch_assoc($query_name)['name'];
    $alert_class = ($status == 'Normal') ? 'alert-success' : 'alert-warning';
    $new_assessment_alert = "<div class='alert $alert_class'>New assessment for <strong>$student_name</strong>: Status <strong>$status</strong></div>";
    if ($status == 'Terlambat' && isset($_GET['open_modal']) && $_GET['open_modal'] == '1') {
        $latest_assessment_query = "SELECT id FROM assessment_results WHERE student_id = '$student_id' ORDER BY tanggal DESC LIMIT 1";
        $latest_assessment_result = mysqli_query($conn, $latest_assessment_query);
        if ($latest_assessment_result && $latest_assessment = mysqli_fetch_assoc($latest_assessment_result)) {
            $auto_open_modal = "ModalView_{$student_id}_{$latest_assessment['id']}";
        }
    }
}
?>

<div class="col-lg-9 mt-2">
    <div class="card">
        <div class="card-header">
            <h4>Hasil Deteksi</h4>
        </div>
        <div class="card-body">
            <?php echo $new_assessment_alert; ?>
            <div class="row">
                <div class="col d-flex justify-content-end">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalCetakLaporan">Cetak Laporan Bulanan</button>
                    <button class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#ModalSemesterReport">Cetak Laporan Semester</button>
                </div>
            </div>
            <?php
            if (!is_array($result)) {
                echo '<div class="alert alert-warning">Terjadi kesalahan saat mengambil data.</div>';
            } elseif (empty($result)) {
                echo '<div class="alert alert-info">Tidak ada data assessment untuk ditampilkan.</div>';
            }
            
            if (is_array($result)) {
                foreach ($result as $row) {
                    $modal_id = "ModalView_" . $row['student_id'] . "_" . $row['assessment_id'];
            ?>

            <div class="modal fade" id="<?php echo $modal_id; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-fullscreen-md-down">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Detail Hasil Assessment</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form class="needs-validation" novalidate>
                                <!-- Nama Siswa -->
                                <div class="form-floating mb-3">
                                    <input disabled type="text" class="form-control" value="<?php echo $row['name']; ?>">
                                    <label>Nama Siswa</label>
                                </div>

                                <!-- Skor Assessment -->
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-floating mb-3">
                                            <input disabled type="text" class="form-control" value="<?php echo $row['motorik_score'] . ' (' . $row['motorik_predicate'] . ' - ' . $row['motorik_label'] . ')'; ?>">
                                            <label>Skor Total Motorik</label>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-floating mb-3">
                                            <input disabled type="text" class="form-control" value="<?php echo $row['kognitif_score'] . ' (' . $row['kognitif_predicate'] . ' - ' . $row['kognitif_label'] . ')'; ?>">
                                            <label>Skor Total Kognitif</label>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-floating mb-3">
                                            <input disabled type="text" class="form-control" value="<?php echo $row['bahasa_score'] . ' (' . $row['bahasa_predicate'] . ' - ' . $row['bahasa_label'] . ')'; ?>">
                                            <label>Skor Total Bahasa</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status Assessment -->
                                <div class="form-floating mb-3">
                                    <?php 
                                    $statusText = $row['prediction'];
                                    $statusClass = (strpos($statusText, 'Terlambat') === 0) ? 'bg-danger text-white' : 'bg-success text-white';
                                    ?>
                                    <input disabled type="text" class="form-control <?php echo $statusClass; ?>" value="<?php echo $statusText; ?>">
                                    <label>Status</label>
                                </div>

                                <!-- Rekomendasi Section -->
                                <?php if (strpos($statusText, 'Terlambat') === 0 && !empty($row['delayed_aspects']) && !empty($row['recommendations'])): ?>
                                <div class="card mb-3">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">Rekomendasi</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (in_array('motorik', array_map('strtolower', $row['delayed_aspects'])) && isset($row['recommendations']['motorik'])): ?>
                                        <div class="mb-3">
                                            <h6 class="text-primary">Rekomendasi Motorik:</h6>
                                            <ul>
                                                <?php foreach($row['recommendations']['motorik'] as $rec): ?>
                                                <li><?php echo htmlspecialchars($rec); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (in_array('kognitif', array_map('strtolower', $row['delayed_aspects'])) && isset($row['recommendations']['kognitif'])): ?>
                                        <div class="mb-3">
                                            <h6 class="text-primary">Rekomendasi Kognitif:</h6>
                                            <ul>
                                                <?php foreach($row['recommendations']['kognitif'] as $rec): ?>
                                                <li><?php echo htmlspecialchars($rec); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (in_array('bahasa', array_map('strtolower', $row['delayed_aspects'])) && isset($row['recommendations']['bahasa'])): ?>
                                        <div class="mb-3">
                                            <h6 class="text-primary">Rekomendasi Bahasa:</h6>
                                            <ul>
                                                <?php foreach($row['recommendations']['bahasa'] as $rec): ?>
                                                <li><?php echo htmlspecialchars($rec); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (isset($row['recommendations']['umum'])): ?>
                                        <div>
                                            <h6 class="text-primary">Rekomendasi Umum:</h6>
                                            <ul>
                                                <?php foreach($row['recommendations']['umum'] as $rec): ?>
                                                <li><?php echo htmlspecialchars($rec); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Modal Cetak Laporan Bulanan -->
        <div class="modal fade" id="ModalCetakLaporan" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-fullscreen-md-down">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Cetak Laporan Bulanan</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form class="needs-validation" novalidate>
                            <div class="row mb-3">
                                <div class="col-lg-6">
                                    <div class="form-floating mb-3">
                                        <input type="month" class="form-control" id="reportMonth" name="reportMonth" required value="<?php echo date('Y-m'); ?>">
                                        <label for="reportMonth">Pilih Bulan</label>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div id="monthlyReportPreview" class="mt-4">
                            <div id="printableMonthlyReport">
                                <div class="text-center mb-4">
                                    <h2>Laporan Bulanan Assessment Siswa</h2>
                                    <h4 id="reportPeriodTitle"><?php echo date('F Y'); ?></h4>
                                </div>

                                <!-- Kategori Skor -->
                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        <h5>Kategori Skor</h5>
                                    </div>
                                    <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="border p-3 mb-2 bg-light">
                                                <strong>Normal</strong><br>
                                                - Skor Motorik, Kognitif, Bahasa: ≥60 (Predikat C atau lebih tinggi)<br>
                                                (Semua aspek di atas ambang batas normal.)
                                            </div>
                                            <div class="border p-3 bg-light">
                                                <strong>Terlambat</strong><br>
                                                - Skor Motorik, Kognitif, atau Bahasa: <60 (Predikat D)<br>
                                                (Keterlambatan jika satu atau lebih aspek di bawah ambang batas.)
                                            </div>
                                            <div class="border p-3 bg-light">
                                                <strong>Skala Predikat</strong><br>
                                                - A (Sangat Baik): 90–100<br>
                                                - B (Baik): 75–89<br>
                                                - C (Cukup): 60–74<br>
                                                - D (Kurang): <60
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </div>

                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Siswa</th>
                                            <th>Tanggal</th>
                                            <th>Status</th>
                                            <th>Motorik</th>
                                            <th>Kognitif</th>
                                            <th>Bahasa</th>
                                        </tr>
                                    </thead>
                                    <tbody id="monthlyReportData">
                                        <!-- Data will be populated via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="reportFeedback" class="mt-3"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" id="generateReport">Tampilkan Data</button>
                        <button type="button" class="btn btn-success" id="printMonthlyReport">
                            <i class="bi bi-printer"></i> Cetak Laporan
                        </button>
                        <button type="button" class="btn btn-info" id="saveMonthlyReport">
                            <i class="bi bi-save"></i> Simpan Laporan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Semester Report -->
        <div class="modal fade" id="ModalSemesterReport" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-fullscreen-md-down">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Cetak Laporan Semester</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form class="needs-validation" novalidate>
                            <div class="row mb-3">
                                <div class="col-lg-6">
                                    <div class="form-floating mb-3">
                                        <select class="form-select" id="semesterSelect" required>
                                            <option value="1">Semester 1</option>
                                            <option value="2">Semester 2</option>
                                        </select>
                                        <label for="semesterSelect">Pilih Semester</label>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="academicYear" required value="<?php echo (date('n') >= 7 ? date('Y') : date('Y')-1).'/'.((date('n') >= 7 ? date('Y') : date('Y')-1)+1); ?>">
                                        <label for="academicYear">Tahun Akademik</label>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div id="semesterReportPreview" class="mt-4">
                            <div id="printableSemesterReport">
                                <div class="text-center mb-4">
                                    <h2>Laporan Semester Assessment Siswa</h2>
                                    <h4 id="semesterReportTitle">Semester 1 - <?php echo (date('n') >= 7 ? date('Y') : date('Y')-1).'/'.((date('n') >= 7 ? date('Y') : date('Y')-1)+1); ?></h4>
                                </div>

                                <!-- Kategori Skor -->
                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        <h5>Kategori Skor</h5>
                                    </div>
                                    <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="border p-3 mb-2 bg-light">
                                                <strong>Normal</strong><br>
                                                - Skor Motorik, Kognitif, Bahasa: ≥60 (Predikat C atau lebih tinggi)<br>
                                                (Semua aspek di atas ambang batas normal.)
                                            </div>
                                            <div class="border p-3 bg-light">
                                                <strong>Terlambat</strong><br>
                                                - Skor Motorik, Kognitif, atau Bahasa: <60 (Predikat D)<br>
                                                (Keterlambatan jika satu atau lebih aspek di bawah ambang batas.)
                                            </div>
                                            <div class="border p-3 bg-light">
                                                <strong>Skala Predikat</strong><br>
                                                - A (Sangat Baik): 90–100<br>
                                                - B (Baik): 75–89<br>
                                                - C (Cukup): 60–74<br>
                                                - D (Kurang): <60
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </div>

                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Siswa</th>
                                            <th>Status</th>
                                            <th>Motorik</th>
                                            <th>Kognitif</th>
                                            <th>Bahasa</th>
                                            <th>Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody">
                                        <!-- Data will be populated via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="semesterReportFeedback" class="mt-3"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" id="generateSemesterReport">Tampilkan Data</button>
                        <button type="button" class="btn btn-success" id="printSemesterReport">
                            <i class="bi bi-printer"></i> Cetak Laporan
                        </button>
                        <button type="button" class="btn btn-info" id="saveSemesterReport">
                            <i class="bi bi-save"></i> Simpan Laporan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Hapus Siswa -->
        <div class="modal fade" id="ModalDelete<?php echo $row['student_id']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md modal-fullscreen-md-down">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Hapus Data Hasil Deteksi</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form class="needs-validation" novalidate action="proses/proses_delete_detect.php" method="POST">
                            <input type="hidden" name="student_id" value="<?php echo $row['student_id']?>">
                            <div class="col-lg-12">
                                Apakah anda yakin ingin menghapus data hasil deteksi siswa? <b><?php echo $row['name']?></b>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                <button type="submit" class="btn btn-danger" name="hapus_detect_validate" value="satu">Hapus Data</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Print Siswa -->
<div class="modal fade" id="ModalPrint_<?php echo $row['student_id'] . '_' . $row['assessment_id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Cetak Hasil Assessment</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="printableArea_<?php echo $row['student_id'] . '_' . $row['assessment_id']; ?>">
                    <div class="mb-4">
                        <h2 class="text-center">Hasil Assessment Siswa</h2>
                        <h4 class="text-center mb-4"><?php echo date('d-m-Y', strtotime($row['tanggal'])); ?></h4>
                    </div>

                    <!-- Nama Siswa -->
                    <div class="row mb-3">
                        <div class="col-4"><strong>Nama Siswa</strong></div>
                        <div class="col-8">: <?php echo htmlspecialchars($row['name']); ?></div>
                    </div>

                    <!-- Skor Assessment -->
                    <div class="row mb-3">
                        <div class="col-4"><strong>Skor Total Motorik</strong></div>
                        <div class="col-8">: <?php echo $row['motorik_score'] . ' (' . $row['motorik_predicate'] . ' - ' . $row['motorik_label'] . ')'; ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-4"><strong>Skor Total Kognitif</strong></div>
                        <div class="col-8">: <?php echo $row['kognitif_score'] . ' (' . $row['kognitif_predicate'] . ' - ' . $row['kognitif_label'] . ')'; ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-4"><strong>Skor Total Bahasa</strong></div>
                        <div class="col-8">: <?php echo $row['bahasa_score'] . ' (' . $row['bahasa_predicate'] . ' - ' . $row['bahasa_label'] . ')'; ?></div>
                    </div>

                    <!-- Status Assessment -->
                    <div class="row mb-3">
                        <div class="col-4"><strong>Status</strong></div>
                        <div class="col-8">: <span class="<?php echo (strpos($row['prediction'], 'Terlambat') === 0) ? 'text-danger' : 'text-success'; ?>">
                            <?php echo $row['prediction']; ?>
                        </span></div>
                    </div>

                    <!-- Kategori Skor -->
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">
                            <h5>Kategori Skor</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="border p-3 mb-2 bg-light">
                                        <strong>Normal</strong><br>
                                        - Skor Motorik, Kognitif, Bahasa: ≥60 (Predikat C atau lebih tinggi)<br>
                                        (Semua aspek di atas ambang batas normal.)
                                    </div>
                                    <div class="border p-3 bg-light">
                                        <strong>Terlambat</strong><br>
                                        - Skor Motorik, Kognitif, atau Bahasa: <60 (Predikat D)<br>
                                        (Keterlambatan jika satu atau lebih aspek di bawah ambang batas.)
                                    </div>
                                    <div class="border p-3 bg-light">
                                        <strong>Skala Predikat</strong><br>
                                        - A (Sangat Baik): 90–100<br>
                                        - B (Baik): 75–89<br>
                                        - C (Cukup): 60–74<br>
                                        - D (Kurang): <60
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rekomendasi Section -->
                    <?php if (strpos($row['prediction'], 'Terlambat') === 0 && !empty($row['delayed_aspects']) && !empty($row['recommendations'])): ?>
                    <div class="mt-4">
                        <h4 class="mb-3">Rekomendasi</h4>

                        <?php if (in_array('motorik', array_map('strtolower', $row['delayed_aspects'])) && isset($row['recommendations']['motorik'])): ?>
                        <div class="mb-3">
                            <h5>Rekomendasi Motorik:</h5>
                            <ul>
                                <?php foreach ($row['recommendations']['motorik'] as $rec): ?>
                                <li><?php echo htmlspecialchars($rec); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <?php if (in_array('kognitif', array_map('strtolower', $row['delayed_aspects'])) && isset($row['recommendations']['kognitif'])): ?>
                        <div class="mb-3">
                            <h5>Rekomendasi Kognitif:</h5>
                            <ul>
                                <?php foreach ($row['recommendations']['kognitif'] as $rec): ?>
                                <li><?php echo htmlspecialchars($rec); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <?php if (in_array('bahasa', array_map('strtolower', $row['delayed_aspects'])) && isset($row['recommendations']['bahasa'])): ?>
                        <div class="mb-3">
                            <h5>Rekomendasi Bahasa:</h5>
                            <ul>
                                <?php foreach ($row['recommendations']['bahasa'] as $rec): ?>
                                <li><?php echo htmlspecialchars($rec); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($row['recommendations']['umum'])): ?>
                        <div>
                            <h5>Rekomendasi Umum:</h5>
                            <ul>
                                <?php foreach ($row['recommendations']['umum'] as $rec): ?>
                                <li><?php echo htmlspecialchars($rec); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary print-btn" data-print-id="printableArea_<?php echo $row['student_id'] . '_' . $row['assessment_id']; ?>">
                    <i class="bi bi-printer"></i> Cetak
                </button>
            </div>
        </div>
    </div>
</div>

        <!-- Modal History -->
        <div class="modal fade" id="ModalHistory_<?php echo $row['student_id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Riwayat Assessment <?php echo $row['name']; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Motorik</th>
                                        <th>Kognitif</th>
                                        <th>Bahasa</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $history_query = "SELECT * FROM assessment_results 
                                                    WHERE student_id = '".$row['student_id']."' 
                                                    ORDER BY tanggal DESC";
                                    $history_result = mysqli_query($conn, $history_query);

                                    while($history = mysqli_fetch_assoc($history_result)) {
                                        $motorik = ($history['motorik_halus'] + $history['motorik_kasar']);
                                        $kognitif = ($history['sosial_skill'] + $history['ekspresif'] + $history['menyimak']);
                                        $bahasa = ($history['komunikasi'] + $history['membaca'] + $history['pra_akademik']);
                                        
                                        $motorik_h = calculate_score_and_predicate($motorik, 8);
                                        $kognitif_h = calculate_score_and_predicate($kognitif, 12);
                                        $bahasa_h = calculate_score_and_predicate($bahasa, 12);

                                        $isDelayed = false;
                                        if ($motorik_h['score'] < 60 || $kognitif_h['score'] < 60 || $bahasa_h['score'] < 60) {
                                            $isDelayed = true;
                                        }
                                        $statusClass = $isDelayed ? 'bg-danger' : 'bg-success';
                                    ?>
                                    <tr>
                                        <td><?php echo date('d-m-Y', strtotime($history['tanggal'])); ?></td>
                                        <td><?php echo $motorik_h['score'] . ' (' . $motorik_h['predicate'] . ')'; ?></td>
                                        <td><?php echo $kognitif_h['score'] . ' (' . $kognitif_h['predicate'] . ')'; ?></td>
                                        <td><?php echo $bahasa_h['score'] . ' (' . $bahasa_h['predicate'] . ')'; ?></td>
                                        <td>
                                            <span class="badge <?php echo $statusClass; ?>">
                                                <?php echo $statusText; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-info btn-sm me-1" data-bs-toggle="modal" data-bs-target="#ModalView_<?php echo $row['student_id'].'_'.$history['id']; ?>">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
        <?php } // End of foreach loop
        } // End of is_array check 
        ?>
        <div class="table-responsive mt-2">
            <table class="table table-hover" id="example">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    if (is_array($result) && !empty($result)): 
                        foreach($result as $row): 
                        $modal_id = "ModalView_" . $row['student_id'] . "_" . $row['assessment_id'];

                        // Hitung skor untuk menentukan status
                        $motorik_score = ($row['motorik_halus'] + $row['motorik_kasar']);
                        $bahasa_score = ($row['komunikasi'] + $row['membaca'] + $row['pra_akademik']);
                        $kognitif_score = ($row['sosial_skill'] + $row['ekspresif'] + $row['menyimak']);
                        $motorik_result = calculate_score_and_predicate($motorik_score, 8);
                        $bahasa_result = calculate_score_and_predicate($bahasa_score, 12);
                        $kognitif_result = calculate_score_and_predicate($kognitif_score, 12);

                        $isDelayed = false;
                        if ($motorik_result['score'] < 60 || $bahasa_result['score'] < 60 || $kognitif_result['score'] < 60) {
                            $isDelayed = true;
                        }
                        $statusClass = $isDelayed ? 'bg-danger' : 'bg-success';
                    ?>
                    <tr>
                        <td><?php echo $no++?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo isset($row['tanggal']) ? date('d-m-Y', strtotime($row['tanggal'])) : ''; ?></td>
                        <td>
                            <span class="badge <?php echo $statusClass; ?>">
                                <?php echo $isDelayed ? 'Terlambat' : 'Normal'; ?>
                            </span>
                        </td>
                        <td class="d-flex">
                            <button class="btn btn-info btn-sm me-1" data-bs-toggle="modal" data-bs-target="#<?php echo $modal_id; ?>">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#ModalHistory_<?php echo $row['student_id']; ?>">
                                <i class="bi bi-clock-history"></i>
                            </button>
                            <button class="btn btn-warning btn-sm me-1" data-bs-toggle="modal" data-bs-target="#ModalPrint_<?php echo $row['student_id'].'_'.$row['assessment_id']; ?>">
                                <i class="bi bi-printer"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#ModalDelete<?php echo $row['student_id']?>">
                                <i class="bi bi-trash2"></i>
                            </button>
                        </td>
                    </tr>
                    <?php 
                        endforeach;
                    else: 
                    ?>
                    <tr>
                        <td colspan="5" class="text-center py-3">Belum ada data hasil deteksi</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-open modal for new Terlambat assessment
    <?php if (!empty($auto_open_modal)): ?>
        var modal = new bootstrap.Modal(document.getElementById('<?php echo htmlspecialchars($auto_open_modal); ?>'));
        modal.show();
    <?php endif; ?>

    const generateReportBtn = document.getElementById('generateReport');
    const printMonthlyReportBtn = document.getElementById('printMonthlyReport');
    const saveReportBtn = document.getElementById('saveMonthlyReport');
    const monthSelector = document.getElementById('reportMonth');
    const reportPeriodTitle = document.getElementById('reportPeriodTitle');
    const monthlyReportData = document.getElementById('monthlyReportData');

    if (generateReportBtn) {
        generateReportBtn.addEventListener('click', function() {
            const selectedMonth = monthSelector.value; // Format: YYYY-MM
            const date = new Date(selectedMonth);
            const monthName = date.toLocaleString('id-ID', { month: 'long', year: 'numeric' });
            reportPeriodTitle.textContent = monthName;

            let reportData = [];

            <?php if (is_array($result) && !empty($result)): ?>
            const allData = [
                <?php foreach($result as $index => $row): 
                    $motorik_score = ($row['motorik_halus'] + $row['motorik_kasar']);
                    $bahasa_score = ($row['komunikasi'] + $row['membaca'] + $row['pra_akademik']);
                    $kognitif_score = ($row['sosial_skill'] + $row['ekspresif'] + $row['menyimak']);
                    $motorik_result = calculate_score_and_predicate($motorik_score, 8);
                    $bahasa_result = calculate_score_and_predicate($bahasa_score, 12);
                    $kognitif_result = calculate_score_and_predicate($kognitif_score, 12);
                ?>
                {
                    name: "<?php echo addslashes($row['name']); ?>",
                    date: "<?php echo isset($row['tanggal']) ? $row['tanggal'] : ''; ?>",
                    status: "<?php echo addslashes($row['prediction']); ?>",
                    motorik: <?php echo $motorik_result['score']; ?>,
                    motorik_predicate: "<?php echo $motorik_result['predicate']; ?>",
                    kognitif: <?php echo $kognitif_result['score']; ?>,
                    kognitif_predicate: "<?php echo $kognitif_result['predicate']; ?>",
                    bahasa: <?php echo $bahasa_result['score']; ?>,
                    bahasa_predicate: "<?php echo $bahasa_result['predicate']; ?>"
                },
                <?php endforeach; ?>
            ];

            reportData = allData.filter(item => {
                const itemDate = new Date(item.date);
                const itemMonth = itemDate.getFullYear() + '-' + String(itemDate.getMonth() + 1).padStart(2, '0');
                return itemMonth === selectedMonth;
            });
            <?php endif; ?>

            let tableContent = '';
            if (reportData.length === 0) {
                tableContent = '<tr><td colspan="7" class="text-center">Tidak ada data untuk bulan yang dipilih</td></tr>';
            } else {
                reportData.forEach((item, index) => {
                    const statusClass = item.status.includes('Normal') ? 'text-success' : 'text-danger';
                    tableContent += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.name}</td>
                            <td>${new Date(item.date).toLocaleDateString('id-ID')}</td>
                            <td class="${statusClass}">${item.status}</td>
                            <td>${item.motorik} (${item.motorik_predicate})</td>
                            <td>${item.kognitif} (${item.kognitif_predicate})</td>
                            <td>${item.bahasa} (${item.bahasa_predicate})</td>
                        </tr>
                    `;
                });
            }

            monthlyReportData.innerHTML = tableContent;
            printMonthlyReportBtn.style.display = reportData.length > 0 ? 'inline-block' : 'none';
            if (saveReportBtn) {
                saveReportBtn.style.display = reportData.length > 0 ? 'inline-block' : 'none';
            }
        });
    }

    if (printMonthlyReportBtn) {
        printMonthlyReportBtn.addEventListener('click', function() {
            const printContents = document.getElementById('printableMonthlyReport').innerHTML;

            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Laporan Bulanan Assessment Siswa</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        h2, h4 { text-align: center; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid black; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                        .text-success { color: green; }
                        .text-danger { color: red; }
                        .card { margin-bottom: 20px; }
                        .card-header { padding: 10px; }
                        .card-body { padding: 15px; }
                        .border { border: 1px solid #ddd; }
                        .bg-light { background-color: #f8f9fa; }
                    </style>
                </head>
                <body>
                    ${printContents}
                </body>
                </html>
            `);

            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        });
    }

    if (saveReportBtn) {
        saveReportBtn.addEventListener('click', function() {
            const selectedMonth = document.getElementById('reportMonth').value;
            const date = new Date(selectedMonth);
            const monthName = date.toLocaleString('id-ID', { month: 'long', year: 'numeric' });
            const reportType = 'monthly';
            const reportContent = document.getElementById('printableMonthlyReport').innerHTML;

            document.getElementById('reportFeedback').innerHTML = '<div class="alert alert-info">Menyimpan laporan...</div>';

            fetch('proses/proses_save_report.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'report_type': reportType,
                    'report_period': monthName,
                    'report_content': reportContent
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    document.getElementById('reportFeedback').innerHTML = '<div class="alert alert-success">Laporan berhasil disimpan...</div>';
                    setTimeout(() => {
                        $('#ModalCetakLaporan').modal('hide');
                    }, 1500);
                } else {
                    document.getElementById('reportFeedback').innerHTML = 
                        `<div class="alert alert-danger">Gagal menyimpan laporan: ${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('reportFeedback').innerHTML = 
                    '<div class="alert alert-danger">Terjadi kesalahan saat menyimpan laporan.</div>';
            });
        });
    }

    const generateSemesterReportBtn = document.getElementById('generateSemesterReport');
    const printSemesterReportBtn = document.getElementById('printSemesterReport');
    const saveSemesterReportBtn = document.getElementById('saveSemesterReport');
    const semesterReportData = document.getElementById('tbody');
    const semesterReportTitle = document.getElementById('semesterReportTitle');

    if (generateSemesterReportBtn) {
        generateSemesterReportBtn.addEventListener('click', function() {
            const selectedSemester = document.getElementById('semesterSelect').value;
            const academicYear = document.getElementById('academicYear').value;
            semesterReportTitle.textContent = `Semester ${selectedSemester} - ${academicYear}`;

            let reportData = [];

            <?php if (is_array($result) && !empty($result)): ?>
            const allData = [
                <?php foreach($result as $index => $row): 
                    $motorik_score = ($row['motorik_halus'] + $row['motorik_kasar']);
                    $bahasa_score = ($row['komunikasi'] + $row['membaca'] + $row['pra_akademik']);
                    $kognitif_score = ($row['sosial_skill'] + $row['ekspresif'] + $row['menyimak']);
                    $motorik_result = calculate_score_and_predicate($motorik_score, 8);
                    $bahasa_result = calculate_score_and_predicate($bahasa_score, 12);
                    $kognitif_result = calculate_score_and_predicate($kognitif_score, 12);
                ?>
                {
                    name: "<?php echo addslashes($row['name']); ?>",
                    date: "<?php echo isset($row['tanggal']) ? $row['tanggal'] : ''; ?>",
                    status: "<?php echo addslashes($row['prediction']); ?>",
                    motorik: <?php echo $motorik_result['score']; ?>,
                    motorik_predicate: "<?php echo $motorik_result['predicate']; ?>",
                    kognitif: <?php echo $kognitif_result['score']; ?>,
                    kognitif_predicate: "<?php echo $kognitif_result['predicate']; ?>",
                    bahasa: <?php echo $bahasa_result['score']; ?>,
                    bahasa_predicate: "<?php echo $bahasa_result['predicate']; ?>"
                },
                <?php endforeach; ?>
            ];

            reportData = allData.filter(item => {
                const itemDate = new Date(item.date);
                const semester = (itemDate.getMonth() >= 6) ? 1 : 2;
                const year = itemDate.getFullYear();
                const academicYearStart = parseInt(academicYear.split('/')[0]);
                return semester === parseInt(selectedSemester) && year === academicYearStart + (selectedSemester == 2 ? 1 : 0);
            });
            <?php endif; ?>

            let tableContent = '';
            if (reportData.length === 0) {
                tableContent = '<tr><td colspan="7" class="text-center">Tidak ada data untuk semester yang dipilih</td></tr>';
            } else {
                reportData.forEach((item, index) => {
                    const statusClass = item.status.includes('Normal') ? 'text-success' : 'text-danger';
                    tableContent += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.name}</td>
                            <td class="${statusClass}">${item.status}</td>
                            <td>${item.motorik} (${item.motorik_predicate})</td>
                            <td>${item.kognitif} (${item.kognitif_predicate})</td>
                            <td>${item.bahasa} (${item.bahasa_predicate})</td>
                            <td>-</td>
                        </tr>
                    `;
                });
            }

            semesterReportData.innerHTML = tableContent;
            printSemesterReportBtn.style.display = reportData.length > 0 ? 'inline-block' : 'none';
            if (saveSemesterReportBtn) {
                saveSemesterReportBtn.style.display = reportData.length > 0 ? 'inline-block' : 'none';
            }
        });
    }

    if (printSemesterReportBtn) {
        printSemesterReportBtn.addEventListener('click', function() {
            const printContents = document.getElementById('printableSemesterReport').innerHTML;

            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Laporan Semester Assessment Siswa</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        h2, h4 { text-align: center; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid black; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                        .text-success { color: green; }
                        .text-danger { color: red; }
                        .card { margin-bottom: 20px; }
                        .card-header { padding: 10px; }
                        .card-body { padding: 15px; }
                        .border { border: 1px solid #ddd; }
                        .bg-light { background-color: #f8f9fa; }
                    </style>
                </head>
                <body>
                    ${printContents}
                </body>
                </html>
            `);

            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        });
    }

    if (saveSemesterReportBtn) {
        saveSemesterReportBtn.addEventListener('click', function() {
            const selectedSemester = document.getElementById('semesterSelect').value;
            const academicYear = document.getElementById('academicYear').value;
            const reportType = 'semester';
            const reportPeriod = `Semester ${selectedSemester} - ${academicYear}`;
            const reportContent = document.getElementById('printableSemesterReport').innerHTML;

            document.getElementById('semesterReportFeedback').innerHTML = '<div class="alert alert-info">Menyimpan laporan...</div>';

            fetch('proses/proses_save_report.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'report_type': reportType,
                    'report_period': reportPeriod,
                    'report_content': reportContent
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    document.getElementById('semesterReportFeedback').innerHTML = '<div class="alert alert-success">Laporan berhasil disimpan!</div>';
                    setTimeout(() => {
                        $('#ModalSemesterReport').modal('hide');
                    }, 1500);
                } else {
                    document.getElementById('semesterReportFeedback').innerHTML = 
                        `<div class="alert alert-danger">Gagal menyimpan laporan: ${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('semesterReportFeedback').innerHTML = 
                    '<div class="alert alert-danger">Terjadi kesalahan saat menyimpan laporan.</div>';
            });
        });
    }

    const printButtons = document.querySelectorAll('.print-btn');
    printButtons.forEach(button => {
        button.addEventListener('click', function() {
            const elementId = this.getAttribute('data-print-id');
            const printContents = document.getElementById(elementId).innerHTML;

            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Cetak Hasil Assessment</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        h2, h4 { text-align: center; }
                        .row { display: flex; margin-bottom: 10px; }
                        .col-4 { width: 33.33%; font-weight: bold; }
                        .col-8 { width: 66.67%; }
                        ul { margin: 0; padding-left: 20px; }
                        .text-success { color: green; }
                        .text-danger { color: red; }
                        .card { margin-bottom: 20px; }
                        .card-header { background-color: #333; color: white; padding: 10px; }
                        .card-body { padding: 15px; }
                        .border { border: 1px solid #ddd; border-radius: 5px; }
                        .bg-light { background-color: #f8f9fa; }
                    </style>
                </head>
                <body>
                    ${printContents}
                </body>
                </html>
            `);

            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        });
    });
});
</script>