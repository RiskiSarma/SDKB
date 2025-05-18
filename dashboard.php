<?php
include 'proses/connect.php';

// Ambil Total Siswa
$sql_total_students = "SELECT COUNT(*) as total FROM students";
$result_total_students = $conn->query($sql_total_students);
if (!$result_total_students) {
    die("Query failed: " . $conn->error);
}
$totalSiswa = $result_total_students->fetch_assoc()['total'];

// Ambil Assessment Bulan Ini
$sql_assessments = "SELECT COUNT(*) as total FROM assessment_results WHERE MONTH(tanggal) = MONTH(CURRENT_DATE())";
$result_assessments = $conn->query($sql_assessments);
if (!$result_assessments) {
    die("Query failed: " . $conn->error);
}
$assessmentBulanIni = $result_assessments->fetch_assoc()['total'];

// Ambil Perlu Perhatian Khusus
$sql_special_attention = "SELECT COUNT(*) as total FROM assessment_results WHERE prediction = 'Terlambat'";
$result_special_attention = $conn->query($sql_special_attention);
if (!$result_special_attention) {
    die("Query failed: " . $conn->error);
}
$perluPerhatian = $result_special_attention->fetch_assoc()['total'];

// Ambil Data Grafik (Jumlah Assessment per Bulan)
$sql_chart_data = "SELECT MONTH(tanggal) as month, COUNT(*) as total FROM assessment_results GROUP BY MONTH(tanggal)";
$result_chart_data = $conn->query($sql_chart_data);
if (!$result_chart_data) {
    die("Query failed: " . $conn->error);
}
$chart_data = [];
$labels = [];
while ($row = $result_chart_data->fetch_assoc()) {
    $labels[] = date('M', mktime(0, 0, 0, $row['month'], 10)); // Format bulan (Jan, Feb, dst)
    $chart_data[] = $row['total']; // Jumlah assessment per bulan
}

// Calculate model evaluation metrics
function calculateModelMetrics($conn) {
    // Initialize metrics
    $metrics = [
        'total_predictions' => 0,
        'true_positive' => 0,
        'true_negative' => 0,
        'false_positive' => 0,
        'false_negative' => 0,
        'accuracy' => 0,
        'precision' => 0,
        'recall' => 0,
        'f1_score' => 0
    ];
    
    // Use actual data from data_aktual table to calculate metrics
    $sql = "SELECT 
                ar.student_id,
                ar.prediction as predicted_label,
                da.status_aktual as actual_label
            FROM assessment_results ar
            JOIN data_aktual da ON ar.student_id = da.student_id";
    
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $metrics['total_predictions']++;
            
            // Count TP, TN, FP, FN
            // Adjust these conditions based on your label values
            if ($row['actual_label'] == 'Perlu Pembelajaran Khusus' && $row['predicted_label'] == 'Terlambat') {
                $metrics['true_positive']++;
            } else if ($row['actual_label'] == 'Normal' && $row['predicted_label'] == 'Normal') {
                $metrics['true_negative']++;
            } else if ($row['actual_label'] == 'Normal' && $row['predicted_label'] == 'Terlambat') {
                $metrics['false_positive']++;
            } else if ($row['actual_label'] == 'Perlu Pembelajaran Khusus' && $row['predicted_label'] == 'Normal') {
                $metrics['false_negative']++;
            }
        }
        
        // Calculate metrics if we have predictions
        if ($metrics['total_predictions'] > 0) {
            // Accuracy
            $metrics['accuracy'] = ($metrics['true_positive'] + $metrics['true_negative']) / $metrics['total_predictions'];
            
            // Precision
            if (($metrics['true_positive'] + $metrics['false_positive']) > 0) {
                $metrics['precision'] = $metrics['true_positive'] / ($metrics['true_positive'] + $metrics['false_positive']);
            }
            
            // Recall
            if (($metrics['true_positive'] + $metrics['false_negative']) > 0) {
                $metrics['recall'] = $metrics['true_positive'] / ($metrics['true_positive'] + $metrics['false_negative']);
            }
            
            // F1 Score
            if (($metrics['precision'] + $metrics['recall']) > 0) {
                $metrics['f1_score'] = 2 * ($metrics['precision'] * $metrics['recall']) / ($metrics['precision'] + $metrics['recall']);
            }
        }
    }
    
    return $metrics;
}

// Call the function to get metrics
$modelMetrics = calculateModelMetrics($conn);

// Updated query for confusion matrix visualization
$sql_confusion = "SELECT 
    da.status_aktual as actual_label,
    ar.prediction as predicted_label,
    COUNT(*) as count
FROM assessment_results ar
JOIN data_aktual da ON ar.student_id = da.student_id
GROUP BY actual_label, predicted_label";

$result_confusion = $conn->query($sql_confusion);
$confusion_matrix = [
    'TN' => 0, // Normal predicted as Normal
    'FP' => 0, // Normal predicted as Terlambat
    'FN' => 0, // Terlambat predicted as Normal
    'TP' => 0  // Terlambat predicted as Terlambat
];

if ($result_confusion) {
    while ($row = $result_confusion->fetch_assoc()) {
        if ($row['actual_label'] == 'Normal' && $row['predicted_label'] == 'Normal') {
            $confusion_matrix['TN'] = $row['count'];
        } else if ($row['actual_label'] == 'Normal' && $row['predicted_label'] == 'Terlambat') {
            $confusion_matrix['FP'] = $row['count'];
        } else if ($row['actual_label'] == 'Perlu Pembelajaran Khusus' && $row['predicted_label'] == 'Normal') {
            $confusion_matrix['FN'] = $row['count'];
        } else if ($row['actual_label'] == 'Perlu Pembelajaran Khusus' && $row['predicted_label'] == 'Terlambat') {
            $confusion_matrix['TP'] = $row['count'];
        }
    }
}

// Ambil data aktual dari database - Mengganti nilai statis
$sql_total_aktual = "SELECT COUNT(*) as total FROM data_aktual";
$result_total_aktual = $conn->query($sql_total_aktual);
if ($result_total_aktual) {
    $totalDataAktual = $result_total_aktual->fetch_assoc()['total'];
} else {
    $totalDataAktual = 0;
}

// Hitung prediksi benar (TP + TN)
$prediksiBenar = $confusion_matrix['TP'] + $confusion_matrix['TN'];

// Hitung akurasi jika data ada
if ($totalDataAktual > 0) {
    $akurasiModel = round(($prediksiBenar / $totalDataAktual) * 100, 1);
} else {
    $akurasiModel = 0;
}

?>

<!-- content -->
<div class="col-lg-9 mt-2">
    <div class="row">
        <!-- Box pertama (3 kolom) -->
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Siswa</h5>
                    <p class="card-text display-6"><?php echo $totalSiswa; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Penilaian Bulan Ini</h5>
                    <p class="card-text display-6"><?php echo $assessmentBulanIni; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Terlambat</h5>
                    <p class="card-text display-6"><?php echo $perluPerhatian; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Box kedua (3 kolom) -->
    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-info mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Data Aktual</h5>
                    <p class="card-text display-6"><?php echo $totalDataAktual; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Prediksi Benar</h5>
                    <p class="card-text display-6"><?php echo $prediksiBenar; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-secondary mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Akurasi Model</h5>
                    <p class="card-text display-6"><?php echo $akurasiModel; ?>%</p>
                </div>
            </div>
        </div>
    </div>

<!-- Grafik 2: Bar Chart -->
<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title">Grafik Statistik Assessment (Bar Chart)</h5>
        <div style="height: 300px;">
            <canvas id="barChart"></canvas>
        </div>
    </div>
</div>

<!-- Grafik 3: Pie Chart -->
<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title">Distribusi Prediksi (Pie Chart)</h5>
        <div style="height: 300px;">
            <canvas id="pieChart"></canvas>
        </div>
    </div>
</div>

<!-- Model Evaluation Metrics Section -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Model Evaluation Metrics</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Metric</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Accuracy</td>
                                <td><?php echo number_format($modelMetrics['accuracy'] * 100, 2); ?>%</td>
                            </tr>
                            <tr>
                                <td>Precision</td>
                                <td><?php echo number_format($modelMetrics['precision'] * 100, 2); ?>%</td>
                            </tr>
                            <tr>
                                <td>Recall (Sensitivity)</td>
                                <td><?php echo number_format($modelMetrics['recall'] * 100, 2); ?>%</td>
                            </tr>
                            <tr>
                                <td>F1 Score</td>
                                <td><?php echo number_format($modelMetrics['f1_score'] * 100, 2); ?>%</td>
                            </tr>
                            <tr>
                                <td>Total Predictions Evaluated</td>
                                <td><?php echo $modelMetrics['total_predictions']; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Confusion Matrix</h5>
            </div>
            <div class="card-body">
                <div style="height: 300px;">
                    <canvas id="confusionMatrix"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Performance by Category -->
<div class="card mt-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Performance by Category</h5>
    </div>
    <div class="card-body">
        <div style="height: 300px;">
            <canvas id="categoryPerformance"></canvas>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data untuk Line Chart
    const lineLabels = <?php echo json_encode($labels); ?>;
    const lineData = {
        labels: lineLabels,
        datasets: [{
            label: 'Jumlah Assessment',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgb(75, 192, 192)',
            data: <?php echo json_encode($chart_data); ?>,
        }]
    };

    // Konfigurasi Line Chart
    const lineConfig = {
        type: 'line',
        data: lineData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Grafik Perkembangan Hasil Assessment'
                }
            }
        },
    };

    // Inisialisasi Line Chart
    const lineChart = new Chart(
        document.getElementById('lineChart'),
        lineConfig
    );

    // Data untuk Bar Chart
    const barLabels = <?php echo json_encode($labels); ?>;
    const barData = {
        labels: barLabels,
        datasets: [{
            label: 'Jumlah Assessment',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgb(54, 162, 235)',
            borderWidth: 1,
            data: <?php echo json_encode($chart_data); ?>,
        }]
    };

    // Konfigurasi Bar Chart
    const barConfig = {
        type: 'bar',
        data: barData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Grafik Statistik Assessment'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        },
    };

    // Inisialisasi Bar Chart
    const barChart = new Chart(
        document.getElementById('barChart'),
        barConfig
    );

    // Data untuk Pie Chart (Contoh: Distribusi Prediksi)
    const pieData = {
        labels: ['Normal', 'Perlu Perhatian'], // Label untuk Pie Chart
        datasets: [{
            label: 'Distribusi Prediksi',
            data: [<?php echo $totalSiswa - $perluPerhatian; ?>, <?php echo $perluPerhatian; ?>], // Data distribusi
            backgroundColor: ['rgba(75, 192, 192, 0.6)', 'rgba(255, 99, 132, 0.6)'],
            borderColor: ['rgb(75, 192, 192)', 'rgb(255, 99, 132)'],
            borderWidth: 1
        }]
    };

    // Konfigurasi Pie Chart
    const pieConfig = {
        type: 'pie',
        data: pieData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Distribusi Prediksi'
                }
            }
        },
    };

    // Inisialisasi Pie Chart
    const pieChart = new Chart(
        document.getElementById('pieChart'),
        pieConfig
    );
    
    // Confusion Matrix Visualization
    const confusionMatrix = {
        labels: ['Predicted Normal', 'Predicted Terlambat'],
        datasets: [
            {
                label: 'Actual Normal',
                data: [<?php echo $confusion_matrix['TN']; ?>, <?php echo $confusion_matrix['FP']; ?>],
                backgroundColor: ['rgba(75, 192, 192, 0.6)', 'rgba(255, 99, 132, 0.6)'],
                borderColor: ['rgb(75, 192, 192)', 'rgb(255, 99, 132)'],
                borderWidth: 1
            },
            {
                label: 'Actual Terlambat',
                data: [<?php echo $confusion_matrix['FN']; ?>, <?php echo $confusion_matrix['TP']; ?>],
                backgroundColor: ['rgba(255, 205, 86, 0.6)', 'rgba(54, 162, 235, 0.6)'],
                borderColor: ['rgb(255, 205, 86)', 'rgb(54, 162, 235)'],
                borderWidth: 1
            }
        ]
    };

    const cmConfig = {
        type: 'bar',
        data: confusionMatrix,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Confusion Matrix'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const datasetIndex = context.datasetIndex;
                            const index = context.dataIndex;
                            const value = context.raw;
                            
                            if (datasetIndex === 0 && index === 0) return `True Negative: ${value}`;
                            if (datasetIndex === 0 && index === 1) return `False Positive: ${value}`;
                            if (datasetIndex === 1 && index === 0) return `False Negative: ${value}`;
                            return `True Positive: ${value}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    stacked: true,
                },
                y: {
                    stacked: true,
                    beginAtZero: true
                }
            }
        }
    };

    // Initialize Confusion Matrix Chart
    const cmChart = new Chart(
        document.getElementById('confusionMatrix'),
        cmConfig
    );

    // Category Performance Chart
    <?php
    // Get average scores by category and prediction result
    $sql_categories = "SELECT 
        ar.prediction,
        AVG((ar.motorik_halus + ar.motorik_kasar)/2) as avg_motorik,
        AVG((ar.komunikasi + ar.membaca + ar.pra_akademik)/3) as avg_bahasa,
        AVG((ar.sosial_skill + ar.ekspresif + ar.menyimak)/3) as avg_kognitif
    FROM assessment_results ar
    JOIN data_aktual da ON ar.student_id = da.student_id
    GROUP BY ar.prediction";
    
    $result_categories = $conn->query($sql_categories);
    $category_data = [
        'Normal' => ['motorik' => 0, 'bahasa' => 0, 'kognitif' => 0],
        'Terlambat' => ['motorik' => 0, 'bahasa' => 0, 'kognitif' => 0]
    ];
    
    if ($result_categories) {
        while ($row = $result_categories->fetch_assoc()) {
            $category_data[$row['prediction']]['motorik'] = round($row['avg_motorik'], 2);
            $category_data[$row['prediction']]['bahasa'] = round($row['avg_bahasa'], 2);
            $category_data[$row['prediction']]['kognitif'] = round($row['avg_kognitif'], 2);
        }
    }
    ?>

    const categoryData = {
        labels: ['Motorik', 'Bahasa', 'Kognitif'],
        datasets: [
            {
                label: 'Normal',
                data: [
                    <?php echo $category_data['Normal']['motorik']; ?>,
                    <?php echo $category_data['Normal']['bahasa']; ?>,
                    <?php echo $category_data['Normal']['kognitif']; ?>
                ],
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgb(75, 192, 192)',
                borderWidth: 1,
                fill: true
            },
            {
                label: 'Terlambat',
                data: [
                    <?php echo $category_data['Terlambat']['motorik']; ?>,
                    <?php echo $category_data['Terlambat']['bahasa']; ?>,
                    <?php echo $category_data['Terlambat']['kognitif']; ?>
                ],
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: 'rgb(255, 99, 132)',
                borderWidth: 1,
                fill: true
            }
        ]
    };

    const categoryConfig = {
        type: 'radar',
        data: categoryData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Average Scores by Category and Prediction'
                }
            },
            scales: {
                r: {
                    min: 0,
                    max: 10,
                    ticks: {
                        stepSize: 2
                    }
                }
            }
        }
    };

    // Initialize Category Performance Chart
    const categoryChart = new Chart(
        document.getElementById('categoryPerformance'),
        categoryConfig
    );
});
</script>
</div>
<!-- end content -->