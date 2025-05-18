<?php
include "proses/connect.php";
// Check if assessment_results table has data
$check_query = "SELECT COUNT(*) as count FROM assessment_results";
$check_result = mysqli_query($conn, $check_query);
$count_data = mysqli_fetch_assoc($check_result);

// Force clear any potential cached results
mysqli_free_result($check_result);

// If there's no data in assessment_results, return empty result
if ($count_data['count'] == 0) {
    $result = array(); // Empty array to indicate no results
} else {
    // Query that joins students table with their latest assessment results
    $query = "SELECT s.student_id, s.name, 
            a.id as assessment_id, 
            a.motorik_halus, a.motorik_kasar, 
            a.komunikasi, a.membaca, a.pra_akademik, 
            a.sosial_skill, a.ekspresif, a.menyimak,
            a.prediction, a.rekomendasi, a.tanggal
            FROM students s
            INNER JOIN ( 
                  SELECT * FROM assessment_results ar1
                    WHERE ar1.tanggal = (
                    SELECT MAX(ar2.tanggal) 
                    FROM assessment_results ar2 
                    WHERE ar2.student_id = ar1.student_id
                )
            ) a ON s.student_id = a.student_id
            ORDER BY a.tanggal DESC";

    $result = mysqli_query($conn, $query);
    
    // Convert to array for consistent handling
    if ($result) {
        $result_array = array();
        while ($row = mysqli_fetch_assoc($result)) {
            // Jangan ubah tanggal, gunakan tanggal yang diambil dari database
            $result_array[] = $row;
        }
        mysqli_free_result($result);
        $result = $result_array;
    } else {
        $result = array(); // Empty array in case of query error
    }
}
?>

<div class="col-lg-9 mt-2">
    <div class="card">
    <div class="card-header">
        <h4>Hasil Deteksi</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col d-flex justify-content-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalCetakLaporan">Cetak Laporan</button>
            </div>
        </div>
    <?php
            // Debug info
            if (!is_array($result)) {
                echo '<div class="alert alert-warning">Terjadi kesalahan saat mengambil data.</div>';
            } elseif (empty($result)) {
                echo '<div class="alert alert-info">Tidak ada data assessment untuk ditampilkan.</div>';
            }
            
            // Modal View, Edit, Delete akan dibuat serupa dengan modal tambah
            if (is_array($result)) {
                foreach ($result as $row) {
                    // Calculate scores
                    $motorik_score = isset($row['motorik_halus']) && isset($row['motorik_kasar']) ? 
                        ($row['motorik_halus'] + $row['motorik_kasar']) / 2 : 0;
                    
                    $bahasa_score = isset($row['komunikasi']) && isset($row['membaca']) && isset($row['pra_akademik']) ? 
                        ($row['komunikasi'] + $row['membaca'] + $row['pra_akademik']) / 3 : 0;
                    
                    $kognitif_score = isset($row['sosial_skill']) && isset($row['ekspresif']) && isset($row['menyimak']) ? 
                        ($row['sosial_skill'] + $row['ekspresif'] + $row['menyimak']) / 3 : 0;
                    
                    // Saved prediction from database
                    $saved_prediction = isset($row['prediction']) ? $row['prediction'] : '';
                    
                    // Parse rekomendasi JSON jika ada dan bukan null
                    $recommendations = [];
                    if (isset($row['rekomendasi']) && !empty($row['rekomendasi'])) {
                        $recommendations = json_decode($row['rekomendasi'], true);
                    }
                    
                    // Generate a unique modal ID using student_id AND assessment_id
                    $modal_id = "ModalView_" . $row['student_id'] . "_" . $row['assessment_id'];
            ?>
        
        <!-- Modal View Siswa -->
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
                        <input disabled type="text" class="form-control" value="<?php echo $row['name']?>">
                        <label>Nama Siswa</label>
                    </div>

                    <!-- Skor Assessment -->
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-floating mb-3">
                                <input disabled type="text" class="form-control" value="<?php echo number_format($motorik_score, 1); ?>">
                                <label>Skor Total Motorik</label>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-floating mb-3">
                                <input disabled type="text" class="form-control" value="<?php echo number_format($kognitif_score, 1); ?>">
                                <label>Skor Total Kognitif</label>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-floating mb-3">
                                <input disabled type="text" class="form-control" value="<?php echo number_format($bahasa_score, 1); ?>">
                                <label>Skor Total Bahasa</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Assessment -->
                    <div class="form-floating mb-3">
                        <input disabled type="text" class="form-control <?php echo ($saved_prediction == 'Normal') ? 'bg-success text-white' : 'bg-danger text-white'; ?>" 
                               value="<?php echo $saved_prediction == 'Normal' ? 'Normal' : 'Perlu Pembelajaran Khusus'; ?>">
                        <label>Status</label>
                    </div>
                    
                    <!-- Rekomendasi Section (hanya ditampilkan jika status Terlambat/Perlu Pembelajaran Khusus) -->
                    <?php if($saved_prediction != 'Normal' && !empty($recommendations)): ?>
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Rekomendasi</h5>
                        </div>
                        <div class="card-body">
                            <?php if(isset($recommendations['motorik']) && !empty($recommendations['motorik'])): ?>
                            <div class="mb-3">
                                <h6 class="text-primary">Rekomendasi Motorik:</h6>
                                <ul>
                                    <?php foreach($recommendations['motorik'] as $rec): ?>
                                    <li><?php echo htmlspecialchars($rec); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                            
                            <?php if(isset($recommendations['bahasa']) && !empty($recommendations['bahasa'])): ?>
                            <div class="mb-3">
                                <h6 class="text-primary">Rekomendasi Bahasa:</h6>
                                <ul>
                                    <?php foreach($recommendations['bahasa'] as $rec): ?>
                                    <li><?php echo htmlspecialchars($rec); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                            
                            <?php if(isset($recommendations['kognitif']) && !empty($recommendations['kognitif'])): ?>
                            <div class="mb-3">
                                <h6 class="text-primary">Rekomendasi Kognitif:</h6>
                                <ul>
                                    <?php foreach($recommendations['kognitif'] as $rec): ?>
                                    <li><?php echo htmlspecialchars($rec); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                            
                            <?php if(isset($recommendations['umum']) && !empty($recommendations['umum'])): ?>
                            <div>
                                <h6 class="text-primary">Rekomendasi Umum:</h6>
                                <ul>
                                    <?php foreach($recommendations['umum'] as $rec): ?>
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
                        
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Siswa</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Skor Motorik</th>
                                    <th>Skor Kognitif</th>
                                    <th>Skor Bahasa</th>
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
                            <h4 id="semesterReportTitle">Semester 1 - 2023/2024</h4>
                        </div>
                        
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Siswa</th>
                                    <th>Status</th>
                                    <th>Skor Motorik</th>
                                    <th>Skor Kognitif</th>
                                    <th>Skor Bahasa</th>
                                    <th>Perubahan</th>
                                </tr>
                            </thead>
                            <tbody id="semesterReportData">
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
                <h1 class="modal-title fs-5" id="exampleModalLabel">Hapus Data Hasil deteksi</h1>
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
<!-- Akhir Modal hapus deteksi -->
<!-- Modal Print Siswa -->
<div class="modal fade" id="ModalPrint_<?php echo $row['student_id'].'_'.$row['assessment_id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Cetak Hasil Assessment</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="printableArea_<?php echo $row['student_id'].'_'.$row['assessment_id']; ?>">
                    <div class="mb-4">
                        <h2 class="text-center">Hasil Assessment Siswa</h2>
                        <h4 class="text-center mb-4"><?php echo date('d-m-Y', strtotime($row['tanggal'])); ?></h4>
                    </div>
                    
                    <!-- Nama Siswa -->
                    <div class="row mb-3">
                        <div class="col-4"><strong>Nama Siswa</strong></div>
                        <div class="col-8">: <?php echo $row['name']?></div>
                    </div>

                    <!-- Skor Assessment -->
                    <div class="row mb-3">
                        <div class="col-4"><strong>Skor Total Motorik</strong></div>
                        <div class="col-8">: <?php echo number_format($motorik_score, 1); ?></div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-4"><strong>Skor Total Kognitif</strong></div>
                        <div class="col-8">: <?php echo number_format($kognitif_score, 1); ?></div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-4"><strong>Skor Total Bahasa</strong></div>
                        <div class="col-8">: <?php echo number_format($bahasa_score, 1); ?></div>
                    </div>
                    
                    <!-- Status Assessment -->
                    <div class="row mb-3">
                        <div class="col-4"><strong>Status</strong></div>
                        <div class="col-8">: <span class="<?php echo ($saved_prediction == 'Normal') ? 'text-success' : 'text-danger'; ?>">
                            <?php echo $saved_prediction == 'Normal' ? 'Normal' : 'Perlu Pembelajaran Khusus'; ?>
                        </span></div>
                    </div>
                    
                    <!-- Rekomendasi Section (hanya ditampilkan jika status Terlambat/Perlu Pembelajaran Khusus) -->
                    <?php if($saved_prediction != 'Normal' && !empty($recommendations)): ?>
                    <div class="mt-4">
                        <h4 class="mb-3">Rekomendasi</h4>
                        
                        <?php if(isset($recommendations['motorik']) && !empty($recommendations['motorik'])): ?>
                        <div class="mb-3">
                            <h5>Rekomendasi Motorik:</h5>
                            <ul>
                                <?php foreach($recommendations['motorik'] as $rec): ?>
                                <li><?php echo htmlspecialchars($rec); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <?php if(isset($recommendations['bahasa']) && !empty($recommendations['bahasa'])): ?>
                        <div class="mb-3">
                            <h5>Rekomendasi Bahasa:</h5>
                            <ul>
                                <?php foreach($recommendations['bahasa'] as $rec): ?>
                                <li><?php echo htmlspecialchars($rec); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <?php if(isset($recommendations['kognitif']) && !empty($recommendations['kognitif'])): ?>
                        <div class="mb-3">
                            <h5>Rekomendasi Kognitif:</h5>
                            <ul>
                                <?php foreach($recommendations['kognitif'] as $rec): ?>
                                <li><?php echo htmlspecialchars($rec); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <?php if(isset($recommendations['umum']) && !empty($recommendations['umum'])): ?>
                        <div>
                            <h5>Rekomendasi Umum:</h5>
                            <ul>
                                <?php foreach($recommendations['umum'] as $rec): ?>
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
                <button type="button" class="btn btn-primary" onclick="printContent('printableArea_<?php echo $row['student_id'].'_'.$row['assessment_id']; ?>')">
                    <i class="bi bi-printer"></i> Cetak
                </button>
            </div>
        </div>
    </div>
</div>
 <!-- Tambahkan modal untuk riwayat assessment -->
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
                                $motorik = ($history['motorik_halus'] + $history['motorik_kasar']) / 2;
                                $kognitif = ($history['sosial_skill'] + $history['ekspresif'] + $history['menyimak']) / 3;
                                $bahasa = ($history['komunikasi'] + $history['membaca'] + $history['pra_akademik']) / 3;
                            ?>
                            <tr>
                                <td><?php echo date('d-m-Y', strtotime($history['tanggal'])); ?></td>
                                <td><?php echo number_format($motorik, 1); ?></td>
                                <td><?php echo number_format($kognitif, 1); ?></td>
                                <td><?php echo number_format($bahasa, 1); ?></td>
                                <td>
                                    <?php if($history['prediction'] == 'Normal'): ?>
                                        <span class="badge bg-success">Normal</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Perlu Intervensi</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-info btn-sm me-1" data-bs-toggle="modal" data-bs-target="#<?php echo $modal_id; ?>">
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
                        // Generate the same unique modal ID for referencing in the button
                        $modal_id = "ModalView_" . $row['student_id'] . "_" . $row['assessment_id'];
                    ?>
                    <tr>
                        <th scope="row"><?php echo $no++?></th>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo isset($row['tanggal']) ? date('d-m-Y', strtotime($row['tanggal'])) : ''; ?></td>
                        <td>
                            <?php if(isset($row['prediction']) && $row['prediction'] == 'Normal'): ?>
                                <span class="badge bg-success">Normal</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Perlu Pembelajaran Khusus</span>
                            <?php endif; ?>
                        </td>
                        <td class="d-flex">
                            <button class="btn btn-info btn-sm me-1" data-bs-toggle="modal" data-bs-target="#<?php echo $modal_id; ?>">
                                <i class="bi bi-eye"></i> 
                            </button>
                            <!-- Tambahkan tombol riwayat -->
                            <button class="btn btn-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#ModalHistory_<?php echo $row['student_id']; ?>">
                                <i class="bi bi-clock-history"></i>
                            </button>
                            <button class="btn btn-warning btn-sm me-1" data-bs-toggle="modal" data-bs-target="#ModalPrint_<?php echo $row['student_id'].'_'.$row['assessment_id']; ?>">
                                <i class="bi bi-printer"></i>
                            </button>
                            <button class="btn btn-danger btn-sm me-1" data-bs-toggle="modal" data-bs-target="#ModalDelete<?php echo $row['student_id']?>">
                                <i class="bi bi-trash2"></i> 
                            </button>
                        </td>
                    </tr>
                    <?php 
                        endforeach;
                    else: 
                    ?>
                    <tr>
                        <td colspan="4" class="text-center py-3">Belum ada data hasil deteksi</td>
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
    const generateReportBtn = document.getElementById('generateReport');
    const printMonthlyReportBtn = document.getElementById('printMonthlyReport');
    const saveReportBtn = document.getElementById('saveMonthlyReport');
    const monthSelector = document.getElementById('reportMonth');
    const reportPeriodTitle = document.getElementById('reportPeriodTitle');
    const monthlyReportData = document.getElementById('monthlyReportData');
    const reportButtonsContainer = document.querySelector('.col.d-flex.justify-content-end');
    
    if (reportButtonsContainer) {
        const semesterButton = document.createElement('button');
        semesterButton.className = 'btn btn-success ms-2';
        semesterButton.setAttribute('data-bs-toggle', 'modal');
        semesterButton.setAttribute('data-bs-target', '#ModalSemesterReport');
        semesterButton.textContent = 'Cetak Laporan Semester';
        reportButtonsContainer.appendChild(semesterButton);
    }
    
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
                    $motorik_score = isset($row['motorik_halus']) && isset($row['motorik_kasar']) ? 
                        ($row['motorik_halus'] + $row['motorik_kasar']) / 2 : 0;
                    
                    $bahasa_score = isset($row['komunikasi']) && isset($row['membaca']) && isset($row['pra_akademik']) ? 
                        ($row['komunikasi'] + $row['membaca'] + $row['pra_akademik']) / 3 : 0;
                    
                    $kognitif_score = isset($row['sosial_skill']) && isset($row['ekspresif']) && isset($row['menyimak']) ? 
                        ($row['sosial_skill'] + $row['ekspresif'] + $row['menyimak']) / 3 : 0;
                    
                    $saved_prediction = isset($row['prediction']) ? $row['prediction'] : '';
                ?>
                {
                    name: "<?php echo addslashes($row['name']); ?>",
                    date: "<?php echo isset($row['tanggal']) ? $row['tanggal'] : ''; ?>",
                    status: "<?php echo $saved_prediction == 'Normal' ? 'Normal' : 'Perlu Pembelajaran Khusus'; ?>",
                    motorik: <?php echo number_format($motorik_score, 1); ?>,
                    kognitif: <?php echo number_format($kognitif_score, 1); ?>,
                    bahasa: <?php echo number_format($bahasa_score, 1); ?>
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
                    const statusClass = item.status === 'Normal' ? 'text-success' : 'text-danger';
                    tableContent += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.name}</td>
                            <td>${new Date(item.date).toLocaleDateString('id-ID')}</td>
                            <td class="${statusClass}">${item.status}</td>
                            <td>${item.motorik}</td>
                            <td>${item.kognitif}</td>
                            <td>${item.bahasa}</td>
                        </tr>
                    `;
                });
            }
            
            monthlyReportData.innerHTML = tableContent;
            printMonthlyReportBtn.style.display = reportData.length > 0 ? 'block' : 'none';
            if (saveReportBtn) {
                saveReportBtn.style.display = reportData.length > 0 ? 'block' : 'none';
            }
        });
    }
    
    if (printMonthlyReportBtn) {
        printMonthlyReportBtn.addEventListener('click', function() {
            const printContents = document.getElementById('printableMonthlyReport').innerHTML;
            const originalContents = document.body.innerHTML;
            
            document.body.innerHTML = `
                <div style="padding: 20px;">
                    ${printContents}
                </div>
            `;
            
            window.print();
            
            document.body.innerHTML = originalContents;
            window.location.reload();
        });
    }

    if (saveReportBtn) {
    saveReportBtn.addEventListener('click', function() {
        const selectedMonth = document.getElementById('reportMonth').value;
        const date = new Date(selectedMonth);
        const monthName = date.toLocaleString('id-ID', { month: 'long', year: 'numeric' });
        const reportType = 'monthly';
        const reportContent = document.getElementById('printableMonthlyReport').innerHTML;

        // Show loading indicator
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
                // Clear any previous feedback
                document.getElementById('reportFeedback').innerHTML = '<div class="alert alert-success">Laporan berhasil disimpan!</div>';
                // Wait a bit before closing the modal
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

    // Tambahkan event listener untuk tombol "Simpan Laporan" semester
    const generateSemesterReportBtn = document.getElementById('generateSemesterReport');
    const printSemesterReportBtn = document.getElementById('printSemesterReport');
    if (generateSemesterReportBtn) {
        generateSemesterReportBtn.addEventListener('click', function() {
            const selectedSemester = document.getElementById('semesterSelect').value;
            const academicYear = document.getElementById('academicYear').value;
            const semesterReportTitle = document.getElementById('semesterReportTitle');
            const semesterReportData = document.getElementById('semesterReportData');
            
            semesterReportTitle.textContent = `Semester ${selectedSemester} - ${academicYear}`;
            
            // Similar logic to the monthly report generator...
            // This would need to be implemented based on how your semester data is structured
            
            // For now, just showing a placeholder message
            semesterReportData.innerHTML = '<tr><td colspan="7" class="text-center">Data semester sedang dimuat...</td></tr>';
            
            // Show the print and save buttons
            printSemesterReportBtn.style.display = 'block';
            if (document.getElementById('saveSemesterReport')) {
                document.getElementById('saveSemesterReport').style.display = 'block';
            }
        });
    }
    
    if (printSemesterReportBtn) {
        printSemesterReportBtn.addEventListener('click', function() {
            const printContents = document.getElementById('printableSemesterReport').innerHTML;
            const originalContents = document.body.innerHTML;
            
            document.body.innerHTML = `
                <div style="padding: 20px;">
                    ${printContents}
                </div>
            `;
            
            window.print();
            
            document.body.innerHTML = originalContents;
            window.location.reload();
        });
    }
});
</script>