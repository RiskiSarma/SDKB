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

// Ambil Perlu Perhatian Khusus (jumlah siswa yang terdeteksi terlambat)
$sql_special_attention = "SELECT COUNT(*) as total FROM assessment_results WHERE prediction LIKE 'Terlambat pada:%'";
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
    $labels[] = date('M', mktime(0, 0, 0, $row['month'], 10));
    $chart_data[] = $row['total'];
}

// Ambil Total Siswa yang Sudah Dinilai
$sql_siswa_dinilai = "SELECT COUNT(DISTINCT student_id) as total FROM assessment_results";
$result_siswa_dinilai = $conn->query($sql_siswa_dinilai);
if (!$result_siswa_dinilai) {
    die("Query failed: " . $conn->error);
}
$totalSiswaDinilai = $result_siswa_dinilai->fetch_assoc()['total'];


// Hitung Confusion Matrix dan Metrik Evaluasi secara otomatis
$sql_metrics_data = "SELECT 
    CASE 
        WHEN ar.prediction LIKE 'Terlambat pada:%' THEN 'Terlambat'
        ELSE 'Normal'
    END as predicted_label,
    da.status_aktual as actual_label,
    COUNT(*) as count
FROM assessment_results ar
JOIN data_aktual da ON ar.student_id = da.student_id
WHERE da.status_aktual IS NOT NULL AND da.status_aktual != ''
GROUP BY predicted_label, actual_label";

$result_metrics_data = $conn->query($sql_metrics_data);
if (!$result_metrics_data) {
    die("Query failed: " . $conn->error);
}

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

if ($result_metrics_data) {
    while ($row = $result_metrics_data->fetch_assoc()) {
        $count = $row['count'];
        $metrics['total_predictions'] += $count;
        
        if ($row['actual_label'] == 'Perlu Pembelajaran Khusus' && $row['predicted_label'] == 'Terlambat') {
            $metrics['true_positive'] += $count;
        } else if ($row['actual_label'] == 'Normal' && $row['predicted_label'] == 'Normal') {
            $metrics['true_negative'] += $count;
        } else if ($row['actual_label'] == 'Normal' && $row['predicted_label'] == 'Terlambat') {
            $metrics['false_positive'] += $count;
        } else if ($row['actual_label'] == 'Perlu Pembelajaran Khusus' && $row['predicted_label'] == 'Normal') {
            $metrics['false_negative'] += $count;
        }
    }
    
    $totalDataAktual = $metrics['total_predictions']; // Total Data Aktual = jumlah perbandingan yang valid
    
    if ($totalDataAktual > 0) {
        $metrics['accuracy'] = ($metrics['true_positive'] + $metrics['true_negative']) / $totalDataAktual;
        if (($metrics['true_positive'] + $metrics['false_positive']) > 0) {
            $metrics['precision'] = $metrics['true_positive'] / ($metrics['true_positive'] + $metrics['false_positive']);
        }
        if (($metrics['true_positive'] + $metrics['false_negative']) > 0) {
            $metrics['recall'] = $metrics['true_positive'] / ($metrics['true_positive'] + $metrics['false_negative']);
        }
        if (($metrics['precision'] + $metrics['recall']) > 0) {
            $metrics['f1_score'] = 2 * ($metrics['precision'] * $metrics['recall']) / ($metrics['precision'] + $metrics['recall']);
        }
    }
}

// Hitung Deteksi Benar
$prediksiBenar = $metrics['true_positive'] + $metrics['true_negative'];

// Hitung Akurasi Model berdasarkan data terbaru
$akurasiModel = ($totalDataAktual > 0) ? round(($prediksiBenar / $totalDataAktual) * 100, 2) : 0;

// Confusion Matrix untuk visualisasi
$sql_confusion = "SELECT 
    da.status_aktual as actual_label,
    CASE 
        WHEN ar.prediction LIKE 'Terlambat pada:%' THEN 'Terlambat'
        ELSE 'Normal'
    END as predicted_label,
    COUNT(*) as count
FROM assessment_results ar
JOIN data_aktual da ON ar.student_id = da.student_id
WHERE da.status_aktual IS NOT NULL AND da.status_aktual != ''
GROUP BY actual_label, predicted_label";

$result_confusion = $conn->query($sql_confusion);
if (!$result_confusion) {
    die("Query failed: " . $conn->error);
}
$confusion_matrix = [
    'TN' => 0,
    'FP' => 0,
    'FN' => 0,
    'TP' => 0
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

// Ambil data rata-rata skor per kategori
$sql_categories = "SELECT 
    ar.prediction,
    AVG((ar.motorik_halus + ar.motorik_kasar)/2) as avg_motorik,
    AVG((ar.komunikasi + ar.membaca + ar.pra_akademik)/3) as avg_bahasa,
    AVG((ar.sosial_skill + ar.ekspresif + ar.menyimak)/3) as avg_kognitif
FROM assessment_results ar
JOIN data_aktual da ON ar.student_id = da.student_id
GROUP BY ar.prediction";
$result_categories = $conn->query($sql_categories);
if (!$result_categories) {
    die("Query failed: " . $conn->error);
}
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

<!-- content -->
<div class="col-lg-9 mt-2">
    <div class="row">
        <!-- Box pertama (3 kolom) -->
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Keseluruhan Data Siswa</h5>
                    <p class="card-text display-6"><?php echo $totalSiswa; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Jumlah Siswa Yang Sudah Dinilai (Bulan Ini)</h5>
                    <p class="card-text display-6"><?php echo $assessmentBulanIni; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Jumlah Siswa Terdeteksi Terlambat Belajar</h5>
                    <p class="card-text display-6"><?php echo $perluPerhatian; ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
    <div class="card text-white bg-info mb-3">
        <div class="card-body text-center">
            <h5 class="card-title">Total Siswa Yang Sudah Dinilai</h5>
            <p class="card-text display-6"><?php echo $totalSiswaDinilai; ?></p>
        </div>
    </div>
</div>

    <!-- Box kedua (3 kolom) -->
    <!-- <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-info mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Data Evaluasi</h5>
                    <p class="card-text display-6"><?php echo $totalDataAktual; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Jumlah Deteksi Benar</h5>
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
    </div> -->
    <script>
    const evaluasiModel = {
        total_data_evaluasi: <?php echo $totalDataAktual; ?>,
        jumlah_deteksi_benar: <?php echo $prediksiBenar; ?>,
        akurasi_model: <?php echo $akurasiModel; ?>
    };

    console.log("=========== PANEL EVALUASI MODEL ===========");
    console.log("Total Data Evaluasi     :", evaluasiModel.total_data_evaluasi);
    console.log("Jumlah Deteksi Benar    :", evaluasiModel.jumlah_deteksi_benar);
    console.log("Akurasi Model            :", evaluasiModel.akurasi_model + "%");
    console.log("============================================");
</script>

    <!-- Grafik 2: Bar Chart -->
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">Grafik Statistik Penilaian</h5>
            <div style="height: 300px;">
                <canvas id="barChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Grafik 3: Pie Chart -->
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">Hasil Deteksi Sistem</h5>
            <div style="height: 200;">
                <canvas id="pieChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Model Evaluation Metrics Section -->
    <!-- <div class="row mt-4">
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
                                    <td><?php echo number_format($metrics['accuracy'] * 100, 2); ?>%</td>
                                </tr>
                                <tr>
                                    <td>Precision</td>
                                    <td><?php echo number_format($metrics['precision'] * 100, 2); ?>%</td>
                                </tr>
                                <tr>
                                    <td>Recall (Sensitivity)</td>
                                    <td><?php echo number_format($metrics['recall'] * 100, 2); ?>%</td>
                                </tr>
                                <tr>
                                    <td>F1 Score</td>
                                    <td><?php echo number_format($metrics['f1_score'] * 100, 2); ?>%</td>
                                </tr>
                                <tr>
                                    <td>Total Evaluasi Deteksi</td>
                                    <td><?php echo $totalDataAktual; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div> -->
    <script>
    const confusion = {
        TP: <?php echo $metrics['true_positive']; ?>,
        TN: <?php echo $metrics['true_negative']; ?>,
        FP: <?php echo $metrics['false_positive']; ?>,
        FN: <?php echo $metrics['false_negative']; ?>,
    };

    const total = confusion.TP + confusion.TN + confusion.FP + confusion.FN;

    // Hitung metrik untuk kelas "Terlambat" (positif)
    const precisionT = (confusion.TP + confusion.FP) > 0 ? confusion.TP / (confusion.TP + confusion.FP) : 0;
    const recallT = (confusion.TP + confusion.FN) > 0 ? confusion.TP / (confusion.TP + confusion.FN) : 0;
    const f1T = (precisionT + recallT) > 0 ? 2 * (precisionT * recallT) / (precisionT + recallT) : 0;
    const supportT = confusion.TP + confusion.FN;

    // Hitung metrik untuk kelas "Normal" (negatif)
    const precisionN = (confusion.TN + confusion.FN) > 0 ? confusion.TN / (confusion.TN + confusion.FN) : 0;
    const recallN = (confusion.TN + confusion.FP) > 0 ? confusion.TN / (confusion.TN + confusion.FP) : 0;
    const f1N = (precisionN + recallN) > 0 ? 2 * (precisionN * recallN) / (precisionN + recallN) : 0;
    const supportN = confusion.TN + confusion.FP;

    // Accuracy
    const accuracy = total > 0 ? (confusion.TP + confusion.TN) / total : 0;

    // Macro average
    const macroPrecision = (precisionT + precisionN) / 2;
    const macroRecall = (recallT + recallN) / 2;
    const macroF1 = (f1T + f1N) / 2;

    // Weighted average
    const weightedPrecision = ((supportT * precisionT) + (supportN * precisionN)) / total;
    const weightedRecall = ((supportT * recallT) + (supportN * recallN)) / total;
    const weightedF1 = ((supportT * f1T) + (supportN * f1N)) / total;

    // Print Report
    console.log("Classification Report:\n");
    console.log("%c               precision    recall  f1-score  support", "font-weight:bold");
    console.log(`Terlambat       ${precisionT.toFixed(2).padEnd(11)}${recallT.toFixed(2).padEnd(8)}${f1T.toFixed(2).padEnd(9)}${supportT}`);
    console.log(`Normal          ${precisionN.toFixed(2).padEnd(11)}${recallN.toFixed(2).padEnd(8)}${f1N.toFixed(2).padEnd(9)}${supportN}\n`);
    console.log(`\naccuracy                             ${accuracy.toFixed(2)}      ${total}`);
    console.log(`macro avg       ${macroPrecision.toFixed(2).padEnd(11)}${macroRecall.toFixed(2).padEnd(8)}${macroF1.toFixed(2).padEnd(9)}${total}`);
    console.log(`weighted avg    ${weightedPrecision.toFixed(2).padEnd(11)}${weightedRecall.toFixed(2).padEnd(8)}${weightedF1.toFixed(2).padEnd(9)}${total}`);
</script>


        <!-- <div class="col-md-6">
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
    </div> -->
<!-- // Tampilkan Confusion Matrix -->
<script>
    // Tampilkan Confusion Matrix
console.log("\nConfusion Matrix:");
console.log("                 Predicted");
console.log("          Terlambat     Normal");
console.log(`Actual Terlambat  ${confusion.TP.toString().padEnd(7)} ${confusion.FN}`);
console.log(`Actual Normal     ${confusion.FP.toString().padEnd(7)} ${confusion.TN}`);

</script>

    <!-- Performance by Category -->
    <!-- <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Performance by Category</h5>
        </div>
        <div class="card-body">
            <div style="height: 300px;">
                <canvas id="categoryPerformance"></canvas>
            </div>
        </div>
    </div> -->

    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
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
                        text: 'Grafik Statistik Penilaian'
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

        // Data untuk Pie Chart 
        const pieData = {
            labels: ['Normal', 'Terlambat Belajar'],
            datasets: [{
                label: 'Hasil Deteksi Sistem',
                data: [<?php echo $totalSiswa - $perluPerhatian; ?>, <?php echo $perluPerhatian; ?>],
                borderColor: ['rgb(75, 192, 192)', 'rgb(255, 99, 132)'],
                backgroundColor: ['rgba(75, 192, 192, 0.6)', 'rgba(255, 99, 132, 0.6)'],
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
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: 'Hasil Deteksi'
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
        // const confusionMatrix = {
        //     labels: ['Deteksi Normal', 'Deteksi Terlambat'],
        //     datasets: [
        //         {
        //             label: 'Actual Normal',
        //             data: [<?php echo $confusion_matrix['TN']; ?>, <?php echo $confusion_matrix['FP']; ?>],
        //             backgroundColor: ['rgba(75, 192, 192, 0.6)', 'rgba(255, 99, 132, 0.6)'],
        //             borderColor: ['rgb(75, 192, 192)', 'rgb(255, 99, 132)'],
        //             borderWidth: 1
        //         },
        //         {
        //             label: 'Actual Terlambat',
        //             data: [<?php echo $confusion_matrix['FN']; ?>, <?php echo $confusion_matrix['TP']; ?>],
        //             backgroundColor: ['rgba(255, 205, 86, 0.6)', 'rgba(54, 162, 235, 0.6)'],
        //             borderColor: ['rgb(255, 205, 86)', 'rgb(54, 162, 235)'],
        //             borderWidth: 1
        //         }
        //     ]
        // };

        // const cmConfig = {
        //     type: 'bar',
        //     data: confusionMatrix,
        //     options: {
        //         responsive: true,
        //         maintainAspectRatio: false,
        //         plugins: {
        //             legend: {
        //                 position: 'top',
        //             },
        //             title: {
        //                 display: true,
        //                 text: 'Confusion Matrix'
        //             },
        //             tooltip: {
        //                 callbacks: {
        //                     label: function(context) {
        //                         const datasetIndex = context.datasetIndex;
        //                         const index = context.dataIndex;
        //                         const value = context.raw;
        //                         if (datasetIndex === 0 && index === 0) return `True Negative: ${value}`;
        //                         if (datasetIndex === 0 && index === 1) return `False Positive: ${value}`;
        //                         if (datasetIndex === 1 && index === 0) return `False Negative: ${value}`;
        //                         return `True Positive: ${value}`;
        //                     }
        //                 }
        //             }
        //         },
        //         scales: {
        //             x: {
        //                 stacked: true,
        //             },
        //             y: {
        //                 stacked: true,
        //                 beginAtZero: true
        //             }
        //         }
        //     }
        // };

        // // Initialize Confusion Matrix Chart
        // const cmChart = new Chart(
        //     document.getElementById('confusionMatrix'),
        //     cmConfig
        // );

        // // Category Performance Chart
        // const categoryData = {
        //     labels: ['Motorik', 'Bahasa', 'Kognitif'],
        //     datasets: [
        //         {
        //             label: 'Normal',
        //             data: [
        //                 <?php echo $category_data['Normal']['motorik']; ?>,
        //                 <?php echo $category_data['Normal']['bahasa']; ?>,
        //                 <?php echo $category_data['Normal']['kognitif']; ?>
        //             ],
        //             backgroundColor: 'rgba(75, 192, 192, 0.6)',
        //             borderColor: 'rgb(75, 192, 192)',
        //             borderWidth: 1,
        //             fill: true
        //         },
        //         {
        //             label: 'Terlambat',
        //             data: [
        //                 <?php echo $category_data['Terlambat']['motorik']; ?>,
        //                 <?php echo $category_data['Terlambat']['bahasa']; ?>,
        //                 <?php echo $category_data['Terlambat']['kognitif']; ?>
        //             ],
        //             backgroundColor: 'rgba(255, 99, 132, 0.6)',
        //             borderColor: 'rgb(255, 99, 132)',
        //             borderWidth: 1,
        //             fill: true
        //         }
        //     ]
        // };

        // const categoryConfig = {
        //     type: 'radar',
        //     data: categoryData,
        //     options: {
        //         responsive: true,
        //         maintainAspectRatio: false,
        //         plugins: {
        //             legend: {
        //                 position: 'top',
        //             },
        //             title: {
        //                 display: true,
        //                 text: 'Average Scores by Category and Prediction'
        //             }
        //         },
        //         scales: {
        //             r: {
        //                 min: 0,
        //                 max: 10,
        //                 ticks: {
        //                     stepSize: 2
        //                 }
        //             }
        //         }
        //     }
        // };

        // // Initialize Category Performance Chart
        // const categoryChart = new Chart(
        //     document.getElementById('categoryPerformance'),
        //     categoryConfig
        // );
    });
    </script>
</div>
<!-- end content -->