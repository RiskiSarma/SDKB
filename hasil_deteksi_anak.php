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

    // Jika data anak tidak ditemukan, tampilkan pesan error dan hentikan eksekusi
    if (!$anak) {
        die("Data anak tidak ditemukan untuk orang tua ini.");
    }

    // Ambil ID anak
    $id_anak = $anak['student_id'];
    
    // Query untuk mengambil SEMUA data deteksi beserta rekomendasi, tanpa LIMIT 1
    $query_deteksi = "SELECT motorik_halus, motorik_kasar, komunikasi, membaca, pra_akademik, sosial_skill, ekspresif, menyimak, prediction, tanggal, rekomendasi, id
                      FROM assessment_results 
                      WHERE student_id = '$id_anak' 
                      ORDER BY tanggal DESC";
    $result_deteksi = mysqli_query($conn, $query_deteksi);

    if (!$result_deteksi) {
        die("Query error: " . mysqli_error($conn));
    }

    // Hitung jumlah deteksi yang pernah dilakukan
    $jumlah_deteksi = mysqli_num_rows($result_deteksi);
?>
<!-- Konten yang akan dimasukkan ke dalam main.php -->
<div class="col-lg-9 mt-2">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="card-title h5">Riwayat Deteksi <?= htmlspecialchars($anak['name']) ?></h3>
                <span class="badge bg-primary"><?= $jumlah_deteksi ?> kali deteksi</span>
            </div>

            <?php if ($jumlah_deteksi == 0): ?>
                <div class="alert alert-info">
                    Belum ada riwayat deteksi untuk anak Anda.
                </div>
            <?php else: ?>
                <!-- Tampilkan setiap deteksi dalam collapse accordion -->
                <div class="accordion" id="detectionHistory">
                    <?php $counter = 1; ?>
                    <?php while($row_deteksi = mysqli_fetch_assoc($result_deteksi)): ?>
                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header" id="heading<?= $counter ?>">
                                <button class="accordion-button <?= ($counter > 1) ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#collapse<?= $counter ?>" aria-expanded="<?= ($counter == 1) ? 'true' : 'false' ?>" 
                                        aria-controls="collapse<?= $counter ?>">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <span>Deteksi <?= $jumlah_deteksi - $counter + 1 ?></span>
                                        <div>
                                            <span class="badge <?= strtolower($row_deteksi['prediction']) == 'terlambat' ? 'bg-warning' : 'bg-success' ?> me-2">
                                                <?= htmlspecialchars($row_deteksi['prediction']) ?>
                                            </span>
                                            <small class="text-muted"><?= htmlspecialchars($row_deteksi['tanggal']) ?></small>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse<?= $counter ?>" class="accordion-collapse collapse <?= ($counter == 1) ? 'show' : '' ?>" 
                                 aria-labelledby="heading<?= $counter ?>" data-bs-parent="#detectionHistory">
                                <div class="accordion-body" id="printableArea-<?= $counter ?>">
                                    <div class="d-flex justify-content-end mb-3">
                                        <button class="btn btn-sm btn-outline-secondary print-button " 
                                                onclick="return printSpecificContent('printableArea-<?= $counter ?>')">
                                            <i class="bi bi-printer"></i> Cetak
                                        </button>
                                    </div>
                                    
                                    <!-- Motorik Halus -->
                                    <div class="mb-2">
                                        <span class="text-muted">Motorik Halus</span>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar" role="progressbar" 
                                                style="width: <?= htmlspecialchars($row_deteksi['motorik_halus']) ?>%;" 
                                                aria-valuenow="<?= htmlspecialchars($row_deteksi['motorik_halus']) ?>" 
                                                aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <p class="text-end text-muted small"><?= htmlspecialchars($row_deteksi['motorik_halus']) ?>%</p>
                                    </div>
                                    
                                    <!-- Motorik Kasar -->
                                    <div class="mb-2">
                                        <span class="text-muted">Motorik Kasar</span>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar" role="progressbar" 
                                                style="width: <?= htmlspecialchars($row_deteksi['motorik_kasar']) ?>%;" 
                                                aria-valuenow="<?= htmlspecialchars($row_deteksi['motorik_kasar']) ?>" 
                                                aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <p class="text-end text-muted small"><?= htmlspecialchars($row_deteksi['motorik_kasar']) ?>%</p>
                                    </div>
                                    
                                    <!-- Komunikasi -->
                                    <div class="mb-2">
                                        <span class="text-muted">Komunikasi</span>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar" role="progressbar" 
                                                style="width: <?= htmlspecialchars($row_deteksi['komunikasi']) ?>%;" 
                                                aria-valuenow="<?= htmlspecialchars($row_deteksi['komunikasi']) ?>" 
                                                aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <p class="text-end text-muted small"><?= htmlspecialchars($row_deteksi['komunikasi']) ?>%</p>
                                    </div>
                                    
                                    <!-- Membaca -->
                                    <div class="mb-2">
                                        <span class="text-muted">Membaca</span>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar" role="progressbar" 
                                                style="width: <?= htmlspecialchars($row_deteksi['membaca']) ?>%;" 
                                                aria-valuenow="<?= htmlspecialchars($row_deteksi['membaca']) ?>" 
                                                aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <p class="text-end text-muted small"><?= htmlspecialchars($row_deteksi['membaca']) ?>%</p>
                                    </div>
                                    
                                    <!-- Pra Akademik -->
                                    <div class="mb-2">
                                        <span class="text-muted">Pra Akademik</span>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar" role="progressbar" 
                                                style="width: <?= htmlspecialchars($row_deteksi['pra_akademik']) ?>%;" 
                                                aria-valuenow="<?= htmlspecialchars($row_deteksi['pra_akademik']) ?>" 
                                                aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <p class="text-end text-muted small"><?= htmlspecialchars($row_deteksi['pra_akademik']) ?>%</p>
                                    </div>
                                    
                                    <!-- Sosial Skill -->
                                    <div class="mb-2">
                                        <span class="text-muted">Sosial Skill</span>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar" role="progressbar" 
                                                style="width: <?= htmlspecialchars($row_deteksi['sosial_skill']) ?>%;" 
                                                aria-valuenow="<?= htmlspecialchars($row_deteksi['sosial_skill']) ?>" 
                                                aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <p class="text-end text-muted small"><?= htmlspecialchars($row_deteksi['sosial_skill']) ?>%</p>
                                    </div>
                                    
                                    <!-- Ekspresif -->
                                    <div class="mb-2">
                                        <span class="text-muted">Ekspresif</span>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar" role="progressbar" 
                                                style="width: <?= htmlspecialchars($row_deteksi['ekspresif']) ?>%;" 
                                                aria-valuenow="<?= htmlspecialchars($row_deteksi['ekspresif']) ?>" 
                                                aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <p class="text-end text-muted small"><?= htmlspecialchars($row_deteksi['ekspresif']) ?>%</p>
                                    </div>
                                    
                                    <!-- Menyimak -->
                                    <div class="mb-2">
                                        <span class="text-muted">Menyimak</span>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar" role="progressbar" 
                                                style="width: <?= htmlspecialchars($row_deteksi['menyimak']) ?>%;" 
                                                aria-valuenow="<?= htmlspecialchars($row_deteksi['menyimak']) ?>" 
                                                aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <p class="text-end text-muted small"><?= htmlspecialchars($row_deteksi['menyimak']) ?>%</p>
                                    </div>
                                    
                                    <?php 
                                    // Tambahkan rekomendasi jika status perkembangan adalah "Terlambat"
                                    if (strtolower($row_deteksi['prediction']) == "terlambat"): 
                                        // Ambil rekomendasi dari kolom rekomendasi (dalam format JSON)
                                        $rekomendasi_json = $row_deteksi['rekomendasi'];
                                        if (!empty($rekomendasi_json)) {
                                            $rekomendasi = json_decode($rekomendasi_json, true); // Decode JSON ke array
                                            
                                            if (json_last_error() === JSON_ERROR_NONE && !empty($rekomendasi)) {
                                    ?>
                                    <div class="mt-4">
                                        <h5 class="h6 mb-3">Rekomendasi Perbaikan:</h5>
                                        <?php foreach ($rekomendasi as $area => $items): ?>
                                            <div class="mb-3">
                                                <h6 class="text-primary">Rekomendasi <?= ucfirst($area) ?>:</h6>
                                                <ul class="list-group">
                                                    <?php foreach ($items as $item): ?>
                                                        <li class="list-group-item"><?= htmlspecialchars($item) ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php
                                            } else {
                                                echo "<p class='text-danger'>Error: Format rekomendasi tidak valid.</p>";
                                            }
                                        }
                                    endif; 
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php $counter++; ?>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    /* CSS untuk tampilan progress bar */
    .progress-bar {
        background-color: #0d6efd;
    }
    
    /* CSS untuk tampilan cetak */
    @media print {
        /* Sembunyikan elemen dengan class no-print */
        .no-print, .accordion-button, .print-button, button {
            display: none !important;
        }
        
        /* Tampilkan collapse content saat cetak */
        .accordion-collapse {
            display: block !important;
        }
        
        /* Tampilkan progress bar pada saat cetak */
        .progress {
            display: block !important;
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
            background-color: #e9ecef !important;
            border-radius: 0.25rem !important;
            overflow: hidden !important;
        }
        
        .progress-bar {
            display: block !important;
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
            background-color: #0d6efd !important;
            height: 100% !important;
            text-align: center !important;
        }
        
        /* Tambahkan warna pada teks */
        .text-primary {
            color: #0d6efd !important;
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        .text-muted {
            color: #6c757d !important;
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        /* Pastikan list-group juga tampil dengan benar */
        .list-group-item {
            border: 1px solid rgba(0, 0, 0, 0.125) !important;
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        /* Hapus border dan shadow dari card untuk cetak */
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        
        /* Tambahkan header halaman cetak */
        @page {
            size: A4;
            margin: 1cm;
        }
    }
</style>

<!-- JavaScript untuk Cetak -->
<script>
    function printSpecificContent(divId) {
        // Simpan konten sebelumnya
        const originalContent = document.body.innerHTML;
        
        // Ambil konten yang akan dicetak
        const printContent = document.getElementById(divId).innerHTML;
        
        // Tambahkan header deteksi
        const childName = "<?= htmlspecialchars($anak['name']) ?>";
        const printHeader = `
            <div class="mb-4">
                <h3 class="text-center">Hasil Deteksi Perkembangan</h3>
                <h4 class="text-center mb-4">${childName}</h4>
            </div>
        `;
        
        // Ganti konten body dengan konten yang akan dicetak
        document.body.innerHTML = `
            <div class="container mt-4">
                ${printHeader}
                ${printContent}
            </div>
        `;
        
        // Panggil fungsi cetak browser
        window.print();
        
        // Kembalikan konten asli
        document.body.innerHTML = originalContent;
        
        return false;
    }
</script>