<?php
include "proses/connect.php";

// Get student ID from URL parameter
$student_id = isset($_GET['id']) ? $_GET['id'] : null;

// If no student ID provided, get list of all students
if ($student_id === null) {
    $query = mysqli_query($conn, "SELECT * FROM students ORDER BY name ASC");
    $students = [];
    while ($record = mysqli_fetch_array($query)) {
        $students[] = $record;
    }
    $student_name = "Semua Siswa";
    
    // Get all assessment results, ordered by date
    $query = "SELECT s.student_id, s.name, 
            a.id as assessment_id, 
            a.motorik_halus, a.motorik_kasar, 
            a.komunikasi, a.membaca, a.pra_akademik, 
            a.sosial_skill, a.ekspresif, a.menyimak,
            a.prediction, a.rekomendasi, a.tanggal
            FROM students s
            INNER JOIN assessment_results a ON s.student_id = a.student_id
            ORDER BY s.name ASC, a.tanggal DESC";
} else {
    // Get student name
    $query = mysqli_query($conn, "SELECT name FROM students WHERE student_id = '$student_id'");
    $student = mysqli_fetch_array($query);
    $student_name = $student['name'];
    
    // Get all assessment results for this student, ordered by date
    $query = "SELECT s.student_id, s.name, 
            a.id as assessment_id, 
            a.motorik_halus, a.motorik_kasar, 
            a.komunikasi, a.membaca, a.pra_akademik, 
            a.sosial_skill, a.ekspresif, a.menyimak,
            a.prediction, a.rekomendasi, a.tanggal
            FROM students s
            INNER JOIN assessment_results a ON s.student_id = a.student_id
            WHERE s.student_id = '$student_id'
            ORDER BY a.tanggal DESC";
}

$result = mysqli_query($conn, $query);
$assessments = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $assessments[] = $row;
    }
}
?>

<!-- content -->
<div class="col-lg-9 mt-2">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4>Riwayat Deteksi <?= $student_name ?></h4>
                <div>
                    <a href="detect" class="btn btn-primary btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali ke Hasil Deteksi
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if ($student_id === null): ?>
            <!-- Student selector -->
            <div class="row mb-3">
                <div class="col-lg-6">
                    <form action="" method="GET" class="d-flex">
                        <select class="form-select" id="student_selector" name="id">
                            <option value="">Semua Siswa</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= $student['student_id']; ?>"><?= $student['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary ms-2">Filter</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (empty($assessments)): ?>
                <div class="alert alert-info">
                    Tidak ada data riwayat deteksi ditemukan.
                </div>
            <?php else: ?>
                <!-- Graph placeholder -->
                <?php if ($student_id !== null): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Grafik Perkembangan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <canvas id="motorikChart"></canvas>
                            </div>
                            <div class="col-md-4">
                                <canvas id="bahasaChart"></canvas>
                            </div>
                            <div class="col-md-4">
                                <canvas id="kognitifChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Table with history -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="historyTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <?php if ($student_id === null): ?>
                                <th>Nama Siswa</th>
                                <?php endif; ?>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Motorik</th>
                                <th>Bahasa</th>
                                <th>Kognitif</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach ($assessments as $row): 
                                // Calculate scores
                                $motorik_score = isset($row['motorik_halus']) && isset($row['motorik_kasar']) ? 
                                    ($row['motorik_halus'] + $row['motorik_kasar']) / 2 : 0;
                                
                                $bahasa_score = isset($row['komunikasi']) && isset($row['membaca']) && isset($row['pra_akademik']) ? 
                                    ($row['komunikasi'] + $row['membaca'] + $row['pra_akademik']) / 3 : 0;
                                
                                $kognitif_score = isset($row['sosial_skill']) && isset($row['ekspresif']) && isset($row['menyimak']) ? 
                                    ($row['sosial_skill'] + $row['ekspresif'] + $row['menyimak']) / 3 : 0;
                                
                                // Format tanggal
                                $formatted_date = date('d-m-Y', strtotime($row['tanggal']));
                                $status_class = ($row['prediction'] == 'Normal') ? 'bg-success' : 'bg-danger';
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <?php if ($student_id === null): ?>
                                <td>
                                    <a href="?id=<?= $row['student_id'] ?>"><?= $row['name'] ?></a>
                                </td>
                                <?php endif; ?>
                                <td><?= $formatted_date ?></td>
                                <td><span class="badge <?= $status_class ?>"><?= $row['prediction'] == 'Normal' ? 'Normal' : 'Perlu Pembelajaran Khusus' ?></span></td>
                                <td><?= number_format($motorik_score, 1) ?></td>
                                <td><?= number_format($bahasa_score, 1) ?></td>
                                <td><?= number_format($kognitif_score, 1) ?></td>
                                <td>
                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#ModalDetail_<?= $row['assessment_id'] ?>">
                                        <i class="bi bi-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Modals for each assessment -->
                <?php foreach ($assessments as $row): 
                    // Calculate scores
                    $motorik_score = isset($row['motorik_halus']) && isset($row['motorik_kasar']) ? 
                        ($row['motorik_halus'] + $row['motorik_kasar']) / 2 : 0;
                    
                    $bahasa_score = isset($row['komunikasi']) && isset($row['membaca']) && isset($row['pra_akademik']) ? 
                        ($row['komunikasi'] + $row['membaca'] + $row['pra_akademik']) / 3 : 0;
                    
                    $kognitif_score = isset($row['sosial_skill']) && isset($row['ekspresif']) && isset($row['menyimak']) ? 
                        ($row['sosial_skill'] + $row['ekspresif'] + $row['menyimak']) / 3 : 0;
                    
                    // Parse rekomendasi JSON if available
                    $recommendations = [];
                    if (isset($row['rekomendasi']) && !empty($row['rekomendasi'])) {
                        $recommendations = json_decode($row['rekomendasi'], true);
                    }
                ?>
                <!-- Modal Detail -->
                <div class="modal fade" id="ModalDetail_<?= $row['assessment_id'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Detail Assessment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Nama Siswa</div>
                                    <div class="col-md-8"><?= $row['name'] ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Tanggal Assessment</div>
                                    <div class="col-md-8"><?= date('d-m-Y', strtotime($row['tanggal'])) ?></div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">Nilai Assessment</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <h6>Motorik</h6>
                                                <ul class="list-group">
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        <span>Motorik Halus:</span>
                                                        <span><?= $row['motorik_halus'] ?></span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        <span>Motorik Kasar:</span>
                                                        <span><?= $row['motorik_kasar'] ?></span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between fw-bold">
                                                        <span>Rata-rata:</span>
                                                        <span><?= number_format($motorik_score, 1) ?></span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-md-4">
                                                <h6>Bahasa</h6>
                                                <ul class="list-group">
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        <span>Komunikasi:</span>
                                                        <span><?= $row['komunikasi'] ?></span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        <span>Membaca:</span>
                                                        <span><?= $row['membaca'] ?></span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        <span>Pra Akademik:</span>
                                                        <span><?= $row['pra_akademik'] ?></span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between fw-bold">
                                                        <span>Rata-rata:</span>
                                                        <span><?= number_format($bahasa_score, 1) ?></span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-md-4">
                                                <h6>Kognitif</h6>
                                                <ul class="list-group">
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        <span>Sosial Skill:</span>
                                                        <span><?= $row['sosial_skill'] ?></span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        <span>Ekspresif:</span>
                                                        <span><?= $row['ekspresif'] ?></span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        <span>Menyimak:</span>
                                                        <span><?= $row['menyimak'] ?></span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between fw-bold">
                                                        <span>Rata-rata:</span>
                                                        <span><?= number_format($kognitif_score, 1) ?></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Hasil Deteksi</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <h6>Status:</h6>
                                            <div class="alert <?= ($row['prediction'] == 'Normal') ? 'alert-success' : 'alert-danger' ?>">
                                                <?= $row['prediction'] == 'Normal' ? 'Normal' : 'Perlu Pembelajaran Khusus' ?>
                                            </div>
                                        </div>
                                        
                                        <?php if($row['prediction'] != 'Normal' && !empty($recommendations)): ?>
                                        <div class="mt-3">
                                            <h6>Rekomendasi:</h6>
                                            
                                            <?php if(isset($recommendations['motorik']) && !empty($recommendations['motorik'])): ?>
                                            <div class="mb-3">
                                                <h6 class="text-primary">Motorik:</h6>
                                                <ul>
                                                    <?php foreach($recommendations['motorik'] as $rec): ?>
                                                    <li><?= htmlspecialchars($rec) ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if(isset($recommendations['bahasa']) && !empty($recommendations['bahasa'])): ?>
                                            <div class="mb-3">
                                                <h6 class="text-primary">Bahasa:</h6>
                                                <ul>
                                                    <?php foreach($recommendations['bahasa'] as $rec): ?>
                                                    <li><?= htmlspecialchars($rec) ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if(isset($recommendations['kognitif']) && !empty($recommendations['kognitif'])): ?>
                                            <div class="mb-3">
                                                <h6 class="text-primary">Kognitif:</h6>
                                                <ul>
                                                    <?php foreach($recommendations['kognitif'] as $rec): ?>
                                                    <li><?= htmlspecialchars($rec) ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if(isset($recommendations['umum']) && !empty($recommendations['umum'])): ?>
                                            <div>
                                                <h6 class="text-primary">Umum:</h6>
                                                <ul>
                                                    <?php foreach($recommendations['umum'] as $rec): ?>
                                                    <li><?= htmlspecialchars($rec) ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- end content -->

<?php if ($student_id !== null && !empty($assessments)): ?>
<!-- Chart.js for visualizing progress -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prepare data for charts
    const dates = [];
    const motorikData = [];
    const bahasaData = [];
    const kognitifData = [];
    
    <?php 
    // Get the assessment data in reverse order to show progress from oldest to newest
    $chart_data = array_reverse($assessments);
    foreach ($chart_data as $row): 
        $date = date('d/m/y', strtotime($row['tanggal']));
        $motorik = isset($row['motorik_halus']) && isset($row['motorik_kasar']) ? 
            ($row['motorik_halus'] + $row['motorik_kasar']) / 2 : 0;
        $bahasa = isset($row['komunikasi']) && isset($row['membaca']) && isset($row['pra_akademik']) ? 
            ($row['komunikasi'] + $row['membaca'] + $row['pra_akademik']) / 3 : 0;
        $kognitif = isset($row['sosial_skill']) && isset($row['ekspresif']) && isset($row['menyimak']) ? 
            ($row['sosial_skill'] + $row['ekspresif'] + $row['menyimak']) / 3 : 0;
    ?>
        dates.push('<?= $date ?>');
        motorikData.push(<?= number_format($motorik, 1) ?>);
        bahasaData.push(<?= number_format($bahasa, 1) ?>);
        kognitifData.push(<?= number_format($kognitif, 1) ?>);
    <?php endforeach; ?>
    
    // Create charts
    const ctx1 = document.getElementById('motorikChart').getContext('2d');
    const motorikChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Motorik',
                data: motorikData,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: false,
                    min: 0,
                    max: 4,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Perkembangan Motorik'
                }
            }
        }
    });
    
    const ctx2 = document.getElementById('bahasaChart').getContext('2d');
    const bahasaChart = new Chart(ctx2, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Bahasa',
                data: bahasaData,
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: false,
                    min: 0,
                    max: 4,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Perkembangan Bahasa'
                }
            }
        }
    });
    
    const ctx3 = document.getElementById('kognitifChart').getContext('2d');
    const kognitifChart = new Chart(ctx3, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Kognitif',
                data: kognitifData,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: false,
                    min: 0,
                    max: 4,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Perkembangan Kognitif'
                }
            }
        }
    });
    
    // Enable datatables for better table functionality
    $(document).ready(function() {
        $('#historyTable').DataTable({
            "order": [[0, "desc"]]
        });
    });
});
</script>
<?php endif; ?>

<script>
// Enable Select2 for better student selection
$(document).ready(function() {
    $('#student_selector').select2({
        placeholder: "Pilih Siswa",
        width: '100%',
        allowClear: true
    });
});
</script>