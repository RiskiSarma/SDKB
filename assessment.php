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
                    <label for="text">Skor Motorik</label>
                    <div class="col-lg-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="motorik_halus" name="motorik_halus" required>
                                <option value="" selected disabled>Pilih skor</option>
                                <option value="4">4 (Sangat Baik)</option>
                                <option value="3">3 (Baik)</option>
                                <option value="2">2 (Cukup)</option>
                                <option value="1">1 (Kurang)</option>
                            </select>
                            <label for="motorik_halus" class="form-label">Motorik Halus</label>
                        </div>
                        <div id="motorik_halus_desc" class="description-panel">
                            Pilih skor untuk melihat deskripsi.
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="motorik_kasar" name="motorik_kasar" required>
                                <option value="" selected disabled>Pilih skor</option>
                                <option value="4">4 (Sangat Baik)</option>
                                <option value="3">3 (Baik)</option>
                                <option value="2">2 (Cukup)</option>
                                <option value="1">1 (Kurang)</option>
                            </select>
                            <label for="motorik_kasar" class="form-label">Motorik Kasar</label>
                        </div>
                        <div id="motorik_kasar_desc" class="description-panel mb-3">
                            Pilih skor untuk melihat deskripsi.
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <label for="text">Skor Bahasa</label>
                    <div class="col-lg-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="komunikasi" name="komunikasi" required>
                                <option value="" selected disabled>Pilih skor</option>
                                <option value="4">4 (Sangat Baik)</option>
                                <option value="3">3 (Baik)</option>
                                <option value="2">2 (Cukup)</option>
                                <option value="1">1 (Kurang)</option>
                            </select>
                            <label for="komunikasi" class="form-label">Komunikasi/Bahasa Lisan</label>
                        </div>
                        <div id="komunikasi_desc" class="description-panel mb-3">
                            Pilih skor untuk melihat deskripsi.
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="membaca" name="membaca" required>
                                <option value="" selected disabled>Pilih skor</option>
                                <option value="4">4 (Sangat Baik)</option>
                                <option value="3">3 (Baik)</option>
                                <option value="2">2 (Cukup)</option>
                                <option value="1">1 (Kurang)</option>
                            </select>
                            <label for="membaca" class="form-label">Membaca/Menulis</label>
                        </div>
                        <div id="membaca_desc" class="description-panel">
                            Pilih skor untuk melihat deskripsi.
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="Kemampuan_Pra_Akademik" name="Kemampuan_Pra_Akademik" required>
                                <option value="" selected disabled>Pilih skor</option>
                                <option value="4">4 (Sangat Baik)</option>
                                <option value="3">3 (Baik)</option>
                                <option value="2">2 (Cukup)</option>
                                <option value="1">1 (Kurang)</option>
                            </select>
                            <label for="Kemampuan_Pra_Akademik" class="form-label">Kemampuan Pra Akademik</label>
                        </div>
                        <div id="Kemampuan_Pra_Akademik_desc" class="description-panel mb-3">
                            Pilih skor untuk melihat deskripsi.
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <label for="text">Skor Kognitif</label>
                    <div class="col-lg-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="Sosial_Skill" name="Sosial_Skill" required>
                                <option value="" selected disabled>Pilih skor</option>
                                <option value="4">4 (Sangat Baik)</option>
                                <option value="3">3 (Baik)</option>
                                <option value="2">2 (Cukup)</option>
                                <option value="1">1 (Kurang)</option>
                            </select>
                            <label for="Sosial_Skill" class="form-label">Sosial Skill</label>
                        </div>
                        <div id="Sosial_Skill_desc" class="description-panel mb-3">
                            Pilih skor untuk melihat deskripsi.
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="Ekspresif" name="Ekspresif" required>
                                <option value="" selected disabled>Pilih skor</option>
                                <option value="4">4 (Sangat Baik)</option>
                                <option value="3">3 (Baik)</option>
                                <option value="2">2 (Cukup)</option>
                                <option value="1">1 (Kurang)</option>
                            </select>
                            <label for="Ekspresif" class="form-label">Ekspresif</label>
                        </div>
                        <div id="Ekspresif_desc" class="description-panel">
                            Pilih skor untuk melihat deskripsi.
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="Menyimak" name="Menyimak" required>
                                <option value="" selected disabled>Pilih skor</option>
                                <option value="4">4 (Sangat Baik)</option>
                                <option value="3">3 (Baik)</option>
                                <option value="2">2 (Cukup)</option>
                                <option value="1">1 (Kurang)</option>
                            </select>
                            <label for="Menyimak" class="form-label">Menyimak</label>
                        </div>
                        <div id="Menyimak_desc" class="description-panel mb-3">
                            Pilih skor untuk melihat deskripsi.
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

<style>
/* Custom CSS untuk dropdown yang panjang */
.form-select option {
    padding: 8px;
    line-height: 1.4;
    white-space: normal;
}

.form-select {
    height: auto !important;
    min-height: 38px;
}

/* Untuk Select2 dropdown */
.select2-results__option {
    padding: 10px;
    line-height: 1.4;
    white-space: normal;
    word-wrap: break-word;
}

.select2-container .select2-selection--single {
    height: auto !important;
    min-height: 38px;
    padding: 6px 12px;
}

.select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 1.4;
    padding: 0;
}

/* Styling panel deskripsi */
.description-panel {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 10px;
    margin-top: 5px;
    font-size: 0.9rem;
    color: #495057;
    transition: all 0.3s ease;
    min-height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Animasi saat deskripsi berubah */
.description-panel.updated {
    background-color: #e9ecef;
    border-color: #6c757d;
    animation: highlight 1s ease-out;
}

@keyframes highlight {
    0% { background-color: #fff3cd; }
    100% { background-color: #e9ecef; }
}

/* Responsivitas */
@media (max-width: 768px) {
    .col-lg-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}
</style>

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

    // Array deskripsi untuk setiap kategori
    const descriptions = {
        motorik_halus: {
            4: "Dapat menggunakan alat tulis dengan baik, menggambar bentuk kompleks, menggunting dengan tepat",
            3: "Dapat melakukan sebagian besar aktivitas motorik halus dengan sedikit bantuan",
            2: "Kesulitan dalam beberapa aktivitas motorik halus, memerlukan bantuan sedang",
            1: "Sangat kesulitan dalam aktivitas motorik halus, memerlukan bantuan penuh"
        },
        motorik_kasar: {
            4: "Dapat berlari, melompat, memanjat, dan bergerak dengan koordinasi yang baik",
            3: "Dapat melakukan sebagian besar gerakan motorik kasar dengan koordinasi cukup baik",
            2: "Terkadang kesulitan dalam koordinasi gerakan, memerlukan bantuan ringan",
            1: "Sangat kesulitan dalam koordinasi gerakan motorik kasar, memerlukan bantuan penuh"
        },
        komunikasi: {
            4: "Berbicara dengan jelas, menggunakan kalimat lengkap, dapat bercerita dengan baik",
            3: "Dapat berkomunikasi dengan baik, sesekali perlu bantuan dalam ekspresi",
            2: "Kesulitan dalam mengekspresikan pikiran, kosakata terbatas",
            1: "Sangat kesulitan berkomunikasi, kosakata sangat terbatas atau tidak berbicara"
        },
        membaca: {
            4: "Dapat membaca kata-kata sederhana, menulis huruf dan angka dengan benar",
            3: "Mengenal sebagian besar huruf dan angka, dapat menulis nama sendiri",
            2: "Mengenal beberapa huruf, kesulitan dalam menulis",
            1: "Belum mengenal huruf atau angka, tidak dapat menulis"
        },
        Kemampuan_Pra_Akademik: {
            4: "Mengenal warna, bentuk, angka 1-10, dapat menghitung sederhana",
            3: "Mengenal sebagian besar warna dan bentuk, dapat menghitung 1-5",
            2: "Mengenal beberapa warna dan bentuk dasar, kesulitan menghitung",
            1: "Belum mengenal warna, bentuk, atau konsep angka"
        },
        Sosial_Skill: {
            4: "Dapat berinteraksi dengan baik, berbagi, bekerjasama, dan bermain bersama",
            3: "Dapat berinteraksi dengan teman, terkadang perlu bantuan dalam situasi sosial",
            2: "Kesulitan berinteraksi, cenderung menyendiri, perlu bimbingan sosial",
            1: "Sangat kesulitan berinteraksi, menghindari kontak sosial, perilaku tidak sesuai"
        },
        Ekspresif: {
            4: "Dapat mengekspresikan emosi dengan tepat, kreatif dalam berekspresi",
            3: "Dapat mengekspresikan perasaan dengan cukup baik, sesekali perlu bantuan",
            2: "Kesulitan mengekspresikan emosi, ekspresi terbatas",
            1: "Sangat kesulitan mengekspresikan emosi, tidak responsif atau berlebihan"
        },
        Menyimak: {
            4: "Dapat menyimak dengan baik, mengikuti instruksi multi-langkah, fokus lama",
            3: "Dapat menyimak dan mengikuti instruksi sederhana dengan baik",
            2: "Kesulitan mempertahankan perhatian, perlu pengulangan instruksi",
            1: "Sangat kesulitan menyimak, tidak dapat mengikuti instruksi sederhana"
        }
    };

    // Fungsi untuk memperbarui deskripsi
    $('select').on('change', function() {
        const id = $(this).attr('id');
        const value = $(this).val();
        const descId = `${id}_desc`;
        if (value && descriptions[id] && descriptions[id][value]) {
            $(`#${descId}`).text(descriptions[id][value]).addClass('updated');
            setTimeout(() => $(`#${descId}`).removeClass('updated'), 1000);
        } else {
            $(`#${descId}`).text('Pilih skor untuk melihat deskripsi.');
        }
    });
});
</script>