<?php 
include "proses/connect.php";

// Query untuk mendapatkan daftar siswa beserta status assessment terakhir
$query = "SELECT s.*, 
          (SELECT ar.prediction FROM assessment_results ar 
           WHERE ar.student_id = s.student_id 
           ORDER BY ar.tanggal DESC LIMIT 1) as last_prediction,
          (SELECT ar.tanggal FROM assessment_results ar 
           WHERE ar.student_id = s.student_id 
           ORDER BY ar.tanggal DESC LIMIT 1) as last_assessment_date
          FROM students s";
$students_result = mysqli_query($conn, $query);
$students = [];
while ($record = mysqli_fetch_array($students_result)) {
    $students[] = $record;
}
?>

<div class="col-lg-9 mt-2">
    <div class="card">
        <div class="card-header">
            Assessment Baru
        </div>
        <div class="card-body">
            <form action="proses/proses_assessment.php" method="POST">
                <div class="mb-3">
                    <label for="student" class="form-label">Pilih Siswa</label>
                    <select class="form-select" id="student" name="student" required>
                        <option value="" selected disabled>Pilih Siswa</option>
                        <?php foreach ($students as $student): 
                            $last_status = isset($student['last_prediction']) ? 
                                ($student['last_prediction'] == 'Normal' ? 'Normal' : 'Perlu Intervensi') : 'Belum diassessment';
                            $last_date = isset($student['last_assessment_date']) ? 
                                date('d-m-Y', strtotime($student['last_assessment_date'])) : '';
                        ?>
                            <option value="<?= $student['student_id']; ?>">
                                <?= htmlspecialchars($student['name']); ?> 
                                (Terakhir: <?= $last_status; ?> - <?= $last_date; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="row">
                    <label for="text"> Skor Motorik</label>
                    <div class="col-lg-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="motorik_halus" name="motorik_halus" required>
                                <option value="" selected disabled>Pilih skor</option>
                                <option value="4">4 (Excellent)</option>
                                <option value="3">3 (Good)</option>
                                <option value="2">2 (Fair)</option>
                                <option value="1">1 (Poor)</option>
                            </select>
                            <label for="motorik_halus" class="form-label">Motorik Halus</label>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="motorik_kasar" name="motorik_kasar" required>
                                <option value="" selected disabled>Pilih skor</option>
                                <option value="4">4 (Excellent)</option>
                                <option value="3">3 (Good)</option>
                                <option value="2">2 (Fair)</option>
                                <option value="1">1 (Poor)</option>
                            </select>
                            <label for="motorik_kasar" class="form-label">Motorik Kasar</label>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <label for="text"> Skor Bahasa</label>
                    <div class="col-lg-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="komunikasi" name="komunikasi" required>
                                <option value="" selected disabled>Pilih skor</option>
                                <option value="4">4 (Excellent)</option>
                                <option value="3">3 (Good)</option>
                                <option value="2">2 (Fair)</option>
                                <option value="1">1 (Poor)</option>
                            </select>
                            <label for="komunikasi" class="form-label">Komunikasi/Bahasa Lisan</label>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="membaca" name="membaca" required>
                                <option value="" selected disabled>Pilih skor</option>
                                <option value="4">4 (Excellent)</option>
                                <option value="3">3 (Good)</option>
                                <option value="2">2 (Fair)</option>
                                <option value="1">1 (Poor)</option>
                            </select>
                            <label for="membaca" class="form-label">Membaca/Menulis</label>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="Kemampuan_Pra_Akademik" name="Kemampuan_Pra_Akademik" required>
                                <option value="" selected disabled>Pilih skor</option>
                                <option value="4">4 (Excellent)</option>
                                <option value="3">3 (Good)</option>
                                <option value="2">2 (Fair)</option>
                                <option value="1">1 (Poor)</option>
                            </select>
                            <label for="Kemampuan_Pra_Akademik" class="form-label">Kemampuan Pra Akademik</label>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <label for="text"> Skor Kognitif</label>
                    <div class="col-lg-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="Sosial_Skill" name="Sosial_Skill" required>
                                <option value="" selected disabled>Pilih skor</option>
                                <option value="4">4 (Excellent)</option>
                                <option value="3">3 (Good)</option>
                                <option value="2">2 (Fair)</option>
                                <option value="1">1 (Poor)</option>
                            </select>
                            <label for="Sosial_Skill" class="form-label">Sosial Skill</label>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="Ekspresif" name="Ekspresif" required>
                                <option value="" selected disabled>Pilih skor</option>
                                <option value="4">4 (Excellent)</option>
                                <option value="3">3 (Good)</option>
                                <option value="2">2 (Fair)</option>
                                <option value="1">1 (Poor)</option>
                            </select>
                            <label for="Ekspresif" class="form-label">Ekspresif</label>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="Menyimak" name="Menyimak" required>
                                <option value="" selected disabled>Pilih skor</option>
                                <option value="4">4 (Excellent)</option>
                                <option value="3">3 (Good)</option>
                                <option value="2">2 (Fair)</option>
                                <option value="1">1 (Poor)</option>
                            </select>
                            <label for="Menyimak" class="form-label">Menyimak</label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Proses Assessment</button>
            </form>
        </div>
    </div>
</div>

<!-- Tambahan CSS dan JS untuk Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
    // Inisialisasi Select2
    $('#student').select2({
        placeholder: "Pilih Siswa",
        width: '100%',
        allowClear: true
    });
    
    // Validasi form sebelum submit
    $('form').submit(function(e) {
        let isValid = true;
        $('select[required]').each(function() {
            if ($(this).val() === null || $(this).val() === '') {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Harap lengkapi semua field assessment!');
        }
    });
});
</script>