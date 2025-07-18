<?php include 'proses/connect.php'; // sesuaikan dengan koneksi kamu

// Ambil data hasil prediksi
$query = "SELECT ar.id, ar.student_id, s.name AS name, ar.prediction 
          FROM assessment_results ar 
          LEFT JOIN students s ON ar.student_id = s.student_id
          LEFT JOIN data_aktual da ON ar.student_id = da.student_id
          WHERE da.student_id IS NULL"; // hanya yang belum diisi aktual

$result = mysqli_query($conn, $query);

// Cek jika query gagal
if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}

// Query untuk melihat distribusi data aktual vs prediksi (untuk statistik evaluasi model)
$eval_query = "SELECT 
    COUNT(*) as total_data,
    SUM(CASE 
        WHEN (da.status_aktual = 'Perlu Pembelajaran Khusus' AND ar.prediction = 'Terlambat') OR
             (da.status_aktual = 'Normal' AND ar.prediction = 'Normal') 
        THEN 1 ELSE 0 END) as total_correct
FROM data_aktual da
JOIN assessment_results ar ON da.student_id = ar.student_id";

$eval_result = mysqli_query($conn, $eval_query);
$eval_data = mysqli_fetch_assoc($eval_result);

$total_data = $eval_data['total_data'] ?? 0;
$correct_predictions = $eval_data['total_correct'] ?? 0;
$accuracy = ($total_data > 0) ? round(($correct_predictions / $total_data) * 100, 2) : 0;
?> 

<!-- content --> 
<div class="col-lg-9 mt-2"> 
    <div class="card">
        <div class="card-header">
            <h4>Input Data Aktual</h4>
        </div>
        <div class="card-body">
            <form action="proses/simpan_data_aktual.php" method="POST">
                <?php if(mysqli_num_rows($result) > 0) { ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Nama Siswa</th>
                                <th scope="col">Deteksi Sistem</th>
                                <th scope="col">Data Real</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while($row = mysqli_fetch_assoc($result)) { ?>
                            <tr>
                                <th scope="row"><?php echo $no++; ?></th>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td>
                                    <span class="badge <?php echo ($row['prediction'] == 'Normal') ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo htmlspecialchars($row['prediction']); ?>
                                    </span>
                                </td>
                                <td>
                                    <select class="form-select" name="actual_label[<?php echo $row['student_id']; ?>]" required>
                                        <option value="" selected>-- Pilih --</option>
                                        <option value="Normal">Normal</option>
                                        <option value="Perlu Pembelajaran Khusus">Terlambat</option>
                                    </select>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary">Simpan Data Aktual</button>
                </div>
                <?php } else { 
                    echo '<div class="alert alert-info">Tidak ada data yang perlu diisi.</div>'; 
                } ?>
            </form>
        </div>
    </div>
</div>
<!-- end content -->