<?php
include "proses/connect.php";

// Get the student_id and assessment_id from the URL
$student_id = isset($_GET['student_id']) ? htmlspecialchars($_GET['student_id']) : '';
$assessment_id = isset($_GET['assessment_id']) ? htmlspecialchars($_GET['assessment_id']) : '';

// If both IDs are provided, fetch the student and assessment data
if (!empty($student_id) && !empty($assessment_id)) {
    // Query to get student and their assessment details
    $query = "SELECT s.student_id, s.name, s.birthdate, s.gender, s.address,
              a.id as assessment_id, 
              a.motorik_halus, a.motorik_kasar, 
              a.komunikasi, a.membaca, a.pra_akademik, 
              a.sosial_skill, a.ekspresif, a.menyimak,
              a.prediction, a.tanggal
              FROM students s
              INNER JOIN assessment_results a ON s.student_id = a.student_id
              WHERE s.student_id = '$student_id' AND a.id = '$assessment_id'";

    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // Calculate scores
        $motorik_score = isset($row['motorik_halus']) && isset($row['motorik_kasar']) ? 
            ($row['motorik_halus'] + $row['motorik_kasar']) / 2 : 0;
        
        $bahasa_score = isset($row['komunikasi']) && isset($row['membaca']) && isset($row['pra_akademik']) ? 
            ($row['komunikasi'] + $row['membaca'] + $row['pra_akademik']) / 3 : 0;
        
        $kognitif_score = isset($row['sosial_skill']) && isset($row['ekspresif']) && isset($row['menyimak']) ? 
            ($row['sosial_skill'] + $row['ekspresif'] + $row['menyimak']) / 3 : 0;
    } else {
        // No data found
        header("Location: index.php?page=detection");
        exit;
    }
} else {
    // If parameters are missing, redirect back
    header("Location: index.php?page=detection");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Assessment - <?php echo $row['name']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .logo {
            max-height: 80px;
            margin-bottom: 10px;
        }
        .student-info {
            margin-bottom: 30px;
        }
        .assessment-results {
            margin-bottom: 30px;
        }
        .result-box {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .status-normal {
            background-color: #d4edda;
            color: #155724;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .status-special {
            background-color: #f8d7da;
            color: #721c24;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .score-detail {
            margin-top: 20px;
        }
        .signature-section {
            margin-top: 50px;
            text-align: right;
        }
        .no-print {
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
                margin: 0;
            }
            .print-container {
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="no-print">
            <button class="btn btn-primary" onclick="window.print()">Cetak</button>
            <a href="index.php?page=detection" class="btn btn-secondary">Kembali</a>
        </div>

        <div class="header">
            <h3>HASIL ASSESSMENT PERKEMBANGAN ANAK</h3>
            <p>Tanggal: <?php echo date('d F Y', strtotime($row['tanggal'])); ?></p>
        </div>

        <div class="student-info">
            <h4>Data Siswa</h4>
            <table>
                <tr>
                    <th width="30%">Nama</th>
                    <td><?php echo $row['name']; ?></td>
                </tr>
                <?php if(isset($row['birthdate']) && !empty($row['birthdate'])): ?>
                <tr>
                    <th>Tanggal Lahir</th>
                    <td><?php echo date('d F Y', strtotime($row['birthdate'])); ?></td>
                </tr>
                <?php endif; ?>
                <?php if(isset($row['gender']) && !empty($row['gender'])): ?>
                <tr>
                    <th>Jenis Kelamin</th>
                    <td><?php echo ($row['gender'] == 'L') ? 'Laki-laki' : 'Perempuan'; ?></td>
                </tr>
                <?php endif; ?>
                <?php if(isset($row['address']) && !empty($row['address'])): ?>
                <tr>
                    <th>Alamat</th>
                    <td><?php echo $row['address']; ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="assessment-results">
            <h4>Hasil Assessment</h4>
            <div class="result-box">
                <h5>Status Perkembangan: 
                    <span class="<?php echo ($row['prediction'] == 'Normal') ? 'status-normal' : 'status-special'; ?>">
                        <?php echo ($row['prediction'] == 'Normal') ? 'Normal' : 'Perlu Pembelajaran Khusus'; ?>
                    </span>
                </h5>
                
                <div class="score-detail">
                    <h6>Detail Skor:</h6>
                    <table>
                        <tr>
                            <th colspan="2">Aspek Perkembangan</th>
                            <th>Skor</th>
                        </tr>
                        <tr>
                            <td rowspan="2">Motorik</td>
                            <td>Motorik Halus</td>
                            <td><?php echo number_format($row['motorik_halus'], 1); ?></td>
                        </tr>
                        <tr>
                            <td>Motorik Kasar</td>
                            <td><?php echo number_format($row['motorik_kasar'], 1); ?></td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>Rata-rata Motorik</strong></td>
                            <td><strong><?php echo number_format($motorik_score, 1); ?></strong></td>
                        </tr>
                        
                        <tr>
                            <td rowspan="3">Bahasa</td>
                            <td>Komunikasi</td>
                            <td><?php echo number_format($row['komunikasi'], 1); ?></td>
                        </tr>
                        <tr>
                            <td>Membaca</td>
                            <td><?php echo number_format($row['membaca'], 1); ?></td>
                        </tr>
                        <tr>
                            <td>Pra Akademik</td>
                            <td><?php echo number_format($row['pra_akademik'], 1); ?></td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>Rata-rata Bahasa</strong></td>
                            <td><strong><?php echo number_format($bahasa_score, 1); ?></strong></td>
                        </tr>
                        
                        <tr>
                            <td rowspan="3">Kognitif</td>
                            <td>Sosial Skill</td>
                            <td><?php echo number_format($row['sosial_skill'], 1); ?></td>
                        </tr>
                        <tr>
                            <td>Ekspresif</td>
                            <td><?php echo number_format($row['ekspresif'], 1); ?></td>
                        </tr>
                        <tr>
                            <td>Menyimak</td>
                            <td><?php echo number_format($row['menyimak'], 1); ?></td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>Rata-rata Kognitif</strong></td>
                            <td><strong><?php echo number_format($kognitif_score, 1); ?></strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="recommendations">
            <h4>Rekomendasi</h4>
            <p>
                <?php if($row['prediction'] == 'Normal'): ?>
                    Berdasarkan hasil assessment, perkembangan anak dalam kategori normal. Lanjutkan stimulasi sesuai usia dan pantau perkembangan secara berkala.
                <?php else: ?>
                    Berdasarkan hasil assessment, anak memerlukan pembelajaran khusus untuk mengoptimalkan perkembangannya. Disarankan untuk:
                    <ul>
                        <?php if($motorik_score < 3): ?>
                        <li>Meningkatkan aktivitas yang merangsang perkembangan motorik halus dan kasar</li>
                        <?php endif; ?>
                        <?php if($bahasa_score < 3): ?>
                        <li>Memperbanyak stimulasi bahasa melalui komunikasi, membaca, dan aktivitas pra-akademik</li>
                        <?php endif; ?>
                        <?php if($kognitif_score < 3): ?>
                        <li>Meningkatkan aktivitas yang mendukung perkembangan sosial, ekspresif, dan kemampuan menyimak</li>
                        <?php endif; ?>
                        <li>Konsultasi dengan ahli perkembangan anak untuk mendapatkan program intervensi yang sesuai</li>
                    </ul>
                <?php endif; ?>
            </p>
        </div>

        <div class="signature-section">
            <p><?php echo date('d F Y'); ?></p>
            <br><br><br>
            <p>______________________</p>
            <p>Penilai</p>
        </div>
    </div>

    <script>
        // Auto print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>