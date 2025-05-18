<?php
// print_report.php
include "proses/connect.php";
require_once "proses/report_functions.php";

// Cek apakah ID laporan ada
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('ID Laporan tidak valid!'); window.location.href='?page=report';</script>";
    exit;
}

$reportId = $_GET['id'];
$reportType = isset($_GET['type']) ? $_GET['type'] : '';

// Dapatkan data laporan
$report = getReportById($reportId);

if (!$report) {
    echo "<script>alert('Laporan tidak ditemukan!'); window.location.href='?page=report';</script>";
    exit;
}

// Fungsi untuk mendapatkan data yang diperlukan untuk laporan
$reportData = getReportData($reportId, $reportType);

// Header halaman cetak
header("Content-Type: text/html");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan - <?php echo htmlspecialchars($report['period']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .report-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        .report-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .report-subtitle {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .report-period {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .report-content {
            margin-bottom: 30px;
        }
        .report-footer {
            margin-top: 50px;
            text-align: right;
            padding-top: 20px;
        }
        .signature-area {
            margin-top: 80px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 20px;
            }
            a {
                text-decoration: none;
                color: #000;
            }
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Tombol Print hanya tampil di browser -->
        <div class="row mb-4 no-print">
            <div class="col-12 text-end">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bi bi-printer"></i> Cetak Sekarang
                </button>
                <a href="?page=report" class="btn btn-secondary ms-2">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        
        <!-- Header Laporan -->
        <div class="report-header">
            <div class="report-title">SDKB SYSTEM</div>
            <div class="report-subtitle">
                <?php echo ($reportType == 'monthly') ? 'LAPORAN BULANAN' : 'LAPORAN SEMESTER'; ?>
            </div>
            <div class="report-period">Periode: <?php echo htmlspecialchars($report['period']); ?></div>
        </div>
        
        <!-- Konten Laporan -->
        <div class="report-content">
            <?php if ($reportType == 'monthly'): ?>
                <!-- Konten untuk laporan bulanan -->
                <h4>Data Laporan Bulanan</h4>
                
                <?php if (!empty($reportData)): ?>
                    <table class="table table-bordered mt-3">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Hasil Assessment</th>
                                <th>Rekomendasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1; 
                            foreach ($reportData as $data): 
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($data['nama_siswa']); ?></td>
                                <td><?php echo htmlspecialchars($data['kelas']); ?></td>
                                <td><?php echo htmlspecialchars($data['hasil_assessment']); ?></td>
                                <td><?php echo htmlspecialchars($data['rekomendasi']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Tidak ada data tersedia untuk laporan ini.</p>
                <?php endif; ?>
                
                <!-- Tambahkan statistik atau informasi tambahan jika diperlukan -->
                <div class="mt-4">
                    <h5>Ringkasan:</h5>
                    <p>Total Siswa: <?php echo count($reportData); ?></p>
                    <!-- Tambahkan informasi ringkasan lainnya sesuai kebutuhan -->
                </div>
                
            <?php else: ?>
                <!-- Konten untuk laporan semester -->
                <h4>Data Laporan Semester</h4>
                
                <?php if (!empty($reportData)): ?>
                    <table class="table table-bordered mt-3">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Semester</th>
                                <th>Evaluasi</th>
                                <th>Rekomendasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1; 
                            foreach ($reportData as $data): 
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($data['nama_siswa']); ?></td>
                                <td><?php echo htmlspecialchars($data['kelas']); ?></td>
                                <td><?php echo htmlspecialchars($data['semester']); ?></td>
                                <td><?php echo htmlspecialchars($data['evaluasi']); ?></td>
                                <td><?php echo htmlspecialchars($data['rekomendasi']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Tidak ada data tersedia untuk laporan ini.</p>
                <?php endif; ?>
                
                <!-- Tambahkan statistik atau informasi tambahan jika diperlukan -->
                <div class="mt-4">
                    <h5>Ringkasan:</h5>
                    <p>Total Siswa: <?php echo count($reportData); ?></p>
                    <!-- Tambahkan informasi ringkasan lainnya sesuai kebutuhan -->
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer Laporan -->
        <div class="report-footer">
            <p>Tanggal Cetak: <?php echo date('d-m-Y'); ?></p>
            
            <div class="signature-area">
                <p>Mengetahui,</p>
                <p>Kepala Sekolah</p>
                <br><br><br>
                <p>______________________</p>
                <p>(Nama Kepala Sekolah)</p>
            </div>
        </div>
    </div>

    <script>
        // Auto print ketika halaman dimuat (opsional)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>