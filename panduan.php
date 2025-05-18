<?php
// Cek session
if (!isset($_SESSION['level_deteksi'])) {
    header("location:login");
    exit;
}
?>

<div class="col-lg-9 mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white text-center py-3">
            <h3 class="mb-0"><i class="bi bi-book"></i> Panduan Penilaian Perkembangan Anak PAUD/TK</h3>
        </div>
        <div class="card-body p-4">
            <!-- Header Banner -->
            <div class="header-banner text-center mb-5">
                <h1 class="display-5 fw-bold">Cara Menilai Perkembangan Anak</h1>
                <p class="lead text-muted">Panduan sederhana untuk guru PAUD/TK dalam mendeteksi dini risiko keterlambatan perkembangan anak</p>
            </div>

            <!-- Apa Itu Penilaian Ini? -->
            <section class="mb-5">
                <h4 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle"></i> Apa Itu Penilaian Ini?</h4>
                <p class="text-muted">Penilaian ini membantu guru PAUD/TK mendeteksi dini risiko keterlambatan perkembangan anak dalam tiga bidang utama: 
                    <strong>Gerakan Tubuh (Motorik)</strong>, 
                    <strong>Berbicara dan Belajar (Bahasa)</strong>, serta 
                    <strong>Berpikir dan Bersosialisasi (Kognitif)</strong>. 
                    Guru memberikan skor 1 sampai 4 untuk setiap aspek berdasarkan kemampuan anak sesuai usianya.
                    Sistem akan menghitung rata-rata skor untuk setiap bidang dan menggunakan model prediksi untuk
                    menentukan apakah anak "Normal" atau "Berisiko Terlambat". Sistem ini menggunakan data anak TK
                    sebagai acuan utama, dengan variabel disesuaikan dari data anak normal, terlambat, dan anak berkebutuhan khusus. 
                    Hasil harus didiskusikan dengan orang tua dan profesional (psikolog atau dokter anak).</p>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> <strong>Catatan:</strong> Guru perlu mengikuti pelatihan untuk memberikan skor yang konsisten berdasarkan panduan ini.
                </div>
            </section>

            <!-- Skala Penilaian dan Batas Nilai -->
            <div class="row mb-5">
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 score-card">
                        <div class="card-body">
                            <h5 class="fw-bold text-primary"><i class="bi bi-star-fill"></i> Arti Nilai</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    4 - Sangat Baik <span class="badge bg-primary rounded-pill">Berkembang Sangat Baik</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    3 - Baik <span class="badge bg-success rounded-pill">Berkembang Sesuai Harapan</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    2 - Cukup <span class="badge bg-warning rounded-pill">Mulai Berkembang</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    1 - Kurang <span class="badge bg-danger rounded-pill">Belum Muncul</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 threshold-card">
                        <div class="card-body">
                            <h5 class="fw-bold text-danger"><i class="bi bi-exclamation-triangle"></i> Batas Nilai</h5>
                            <p class="text-muted">Sistem menghitung rata-rata skor untuk setiap bidang. Nilai rata-rata <strong>3.0</strong> atau lebih menunjukkan perkembangan anak <strong>Normal</strong>. Jika rata-rata kurang dari 3.0 di salah satu bidang, anak mungkin berisiko <strong>Terlambat</strong>, tetapi hasil akhir ditentukan oleh model prediksi. Observasi lebih lanjut dan konsultasi diperlukan untuk anak yang berisiko.</p>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle text-success"></i> <strong>Normal</strong>: Nilai rata-rata 3.0–4.0 (Berkembang Sesuai Harapan atau Sangat Baik).</li>
                                <li><i class="bi bi-x-circle text-danger"></i> <strong>Berisiko Terlambat</strong>: Nilai rata-rata 1.0–2.99 (Belum Muncul atau Mulai Berkembang).</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bidang yang Dinilai -->
            <section class="mb-5">
                <h4 class="fw-bold text-primary mb-4 text-center"><i class="bi bi-list-check"></i> Apa Saja yang Dinilai?</h4>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Gerakan Tubuh (Motorik)</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Kemampuan anak menggerakkan tubuhnya.</p>
                                <h6 class="fw-bold">Bagian yang Dinilai:</h6>
                                <ul>
                                    <li><strong>Motorik Halus</strong>: Menggambar bentuk sederhana (garis, lingkaran), menyusun puzzle, memegang pensil.</li>
                                    <li><strong>Motorik Kasar</strong>: Berlari dengan seimbang, melompat, menendang bola.</li>
                                </ul>
                                <p><strong>Cara Hitung</strong>: (Motorik Halus + Motorik Kasar) ÷ 2</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Berbicara & Belajar (Bahasa)</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Kemampuan anak berkomunikasi dan memahami konsep dasar.</p>
                                <h6 class="fw-bold">Bagian yang Dinilai:</h6>
                                <ul>
                                    <li><strong>Komunikasi</strong>: Berbicara dengan kalimat pendek, menyanyi lagu anak.</li>
                                    <li><strong>Membaca/Menulis</strong>: Mengenal huruf, menulis nama sendiri.</li>
                                    <li><strong>Kemampuan Pra-Akademik</strong>: Menghitung angka 1-10, mengenal warna/bentuk.</li>
                                </ul>
                                <p><strong>Cara Hitung</strong>: (Komunikasi + Membaca/Menulis + Kemampuan Pra-Akademik) ÷ 3</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0">Berpikir & Bersosialisasi (Kognitif)</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Kemampuan anak berpikir dan berinteraksi dengan orang lain.</p>
                                <h6 class="fw-bold">Bagian yang Dinilai:</h6>
                                <ul>
                                    <li><strong>Sosial Skill</strong>: Bermain bersama teman, berbagi mainan.</li>
                                    <li><strong>Ekspresif</strong>: Mengungkapkan perasaan (senang, sedih) dengan kata atau gerakan.</li>
                                    <li><strong>Menyimak</strong>: Mengikuti cerita pendek, memahami instruksi sederhana.</li>
                                </ul>
                                <p><strong>Cara Hitung</strong>: (Sosial Skill + Ekspresif + Menyimak) ÷ 3</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Nilai Moral & Kreativitas</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Kemampuan anak memahami nilai baik dan berkreasi, diamati secara informal untuk mendukung penilaian holistik (tidak dicatat di sistem).</p>
                        <h6 class="fw-bold">Bagian yang Diamati:</h6>
                        <ul>
                            <li><strong>Moral</strong>: Mengucap salam, berdoa sederhana, membantu teman.</li>
                            <li><strong>Seni</strong>: Menggambar atau mewarnai, bernyanyi, menari sederhana.</li>
                        </ul>
                        <p><strong>Cara Amati</strong>: Perhatikan perilaku atau karya anak selama kegiatan belajar, tanpa perlu mencatat di sistem.</p>
                    </div>
                </div> -->
            </section>

            <!-- Panduan Berdasarkan Usia -->
            <section class="mb-5">
                <h4 class="fw-bold text-primary mb-4"><i class="bi bi-calendar"></i> Kemampuan yang Diharapkan Sesuai Usia</h4>
                <div class="accordion" id="ageGuidelines">
                    <div class="accordion-item border-0 shadow-sm">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Usia 3-4 Tahun
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#ageGuidelines">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <h6 class="fw-bold">Gerakan Tubuh</h6>
                                        <ul class="text-muted">
                                            <li>Menggambar garis lurus atau lingkaran</li>
                                            <li>Berjalan seimbang di garis lurus</li>
                                            <li>Menendang bola kecil tanpa jatuh</li>
                                            <li>Menyusun puzzle 4-6 potong</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <h6 class="fw-bold">Berbicara & Belajar</h6>
                                        <ul class="text-muted">
                                            <li>Menyanyi lagu anak pendek (misalnya, "Balonku")</li>
                                            <li>Mengenal 5-10 huruf alfabet</li>
                                            <li>Menyebutkan 3-5 warna</li>
                                            <li>Menjawab pertanyaan sederhana ("Apa ini?")</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <h6 class="fw-bold">Berpikir & Bersosialisasi</h6>
                                        <ul class="text-muted">
                                            <li>Bermain bersama 1-2 teman</li>
                                            <li>Menunjukkan perasaan (tertawa, menangis)</li>
                                            <li>Mengikuti instruksi 1 langkah (misalnya, "Ambil buku")</li>
                                            <li>Mengenal nama benda sehari-hari</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <h6 class="fw-bold">Nilai Moral & Kreativitas</h6>
                                        <ul class="text-muted">
                                            <li>Mengucap "selamat pagi" atau "terima kasih"</li>
                                            <li>Berbagi mainan dengan teman</li>
                                            <li>Menggambar bentuk sederhana (misalnya, matahari)</li>
                                            <li>Bernyanyi lagu anak dengan gembira</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 shadow-sm">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Usia 5-6 Tahun
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#ageGuidelines">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <h6 class="fw-bold">Gerakan Tubuh</h6>
                                        <ul class="text-muted">
                                            <li>Menggunting kertas mengikuti garis</li>
                                            <li>Menulis huruf besar atau angka sederhana</li>
                                            <li>Melompat dengan satu kaki 2-3 kali</li>
                                            <li>Menyusun balok dengan pola sederhana</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <h6 class="fw-bold">Berbicara & Belajar</h6>
                                        <ul class="text-muted">
                                            <li>Menceritakan pengalaman singkat</li>
                                            <li>Mengenal semua huruf alfabet</li>
                                            <li>Menghitung sampai 20</li>
                                            <li>Membaca kata sederhana (misalnya, "buku")</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <h6 class="fw-bold">Berpikir & Bersosialisasi</h6>
                                        <ul class="text-muted">
                                            <li>Berbagi mainan tanpa konflik</li>
                                            <li>Mengungkapkan perasaan dengan kalimat</li>
                                            <li>Mengikuti instruksi 2 langkah</li>
                                            <li>Membedakan besar/kecil atau panjang/pendek</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <h6 class="fw-bold">Nilai Moral & Kreativitas</h6>
                                        <ul class="text-muted">
                                            <li>Mengucap doa sederhana</li>
                                            <li>Menghormati teman atau guru</li>
                                            <li>Mewarnai gambar dengan rapi</li>
                                            <li>Menari atau bermain peran sederhana</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Contoh Penilaian -->
            <section class="mb-5">
                <h4 class="fw-bold text-primary mb-4"><i class="bi bi-table"></i> Contoh Cara Menilai</h4>
                <p class="text-muted">Guru memasukkan skor untuk setiap aspek, lalu sistem menghitung rata-rata untuk Motorik, Bahasa, dan Kognitif sebelum memprediksi hasil.</p>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Anak Normal</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-assessment">
                                        <thead>
                                            <tr>
                                                <th>Bidang</th>
                                                <th>Skor</th>
                                                <th>Rata-rata</th>
                                                <th>Hasil</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Gerakan: Motorik Halus (4), Motorik Kasar (3)</td>
                                                <td>(4+3)÷2</td>
                                                <td>3.5</td>
                                                <td class="text-success">Berkembang Sesuai Harapan</td>
                                            </tr>
                                            <tr>
                                                <td>Bahasa: Komunikasi (3), Membaca/Menulis (3), Pra-Akademik (4)</td>
                                                <td>(3+3+4)÷3</td>
                                                <td>3.33</td>
                                                <td class="text-success">Berkembang Sesuai Harapan</td>
                                            </tr>
                                            <tr>
                                                <td>Kognitif: Sosial Skill (3), Ekspresif (3), Menyimak (3)</td>
                                                <td>(3+3+3)÷3</td>
                                                <td>3.0</td>
                                                <td class="text-success">Berkembang Sesuai Harapan</td>
                                            </tr>
                                            <tr class="table-success">
                                                <td colspan="3"><strong>Hasil Prediksi:</strong></td>
                                                <td><strong>Normal</strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0">Anak Berisiko Terlambat</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-assessment">
                                        <thead>
                                            <tr>
                                                <th>Bidang</th>
                                                <th>Skor</th>
                                                <th>Rata-rata</th>
                                                <th>Hasil</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Gerakan: Motorik Halus (3), Motorik Kasar (2)</td>
                                                <td>(3+2)÷2</td>
                                                <td>2.5</td>
                                                <td class="text-danger">Mulai Berkembang</td>
                                            </tr>
                                            <tr>
                                                <td>Bahasa: Komunikasi (3), Membaca/Menulis (3), Pra-Akademik (3)</td>
                                                <td>(3+3+3)÷3</td>
                                                <td>3.0</td>
                                                <td class="text-success">Berkembang Sesuai Harapan</td>
                                            </tr>
                                            <tr>
                                                <td>Kognitif: Sosial Skill (4), Ekspresif (3), Menyimak (3)</td>
                                                <td>(4+3+3)÷3</td>
                                                <td>3.33</td>
                                                <td class="text-success">Berkembang Sesuai Harapan</td>
                                            </tr>
                                            <tr class="table-danger">
                                                <td colspan="3"><strong>Hasil Prediksi:</strong></td>
                                                <td><strong>Berisiko Terlambat</strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="alert alert-warning mt-3">
                                    <i class="bi bi-info-circle"></i> <strong>Catatan:</strong> Anak berisiko terlambat karena nilai rata-rata Gerakan Tubuh kurang dari 3.0. Lakukan observasi lebih lanjut, diskusikan dengan orang tua, dan konsultasikan dengan psikolog atau dokter anak.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Langkah Melakukan Penilaian -->
            <section class="mb-5">
                <h4 class="fw-bold text-primary mb-4"><i class="bi bi-check2-circle"></i> Cara Melakukan Penilaian</h4>
                <ol class="list-group list-group-numbered">
                    <li class="list-group-item">Ikuti pelatihan untuk memahami cara memberikan skor 1–4 berdasarkan panduan ini.</li>
                    <li class="list-group-item">Pilih nama anak dari daftar di sistem.</li>
                    <li class="list-group-item">Amati kemampuan anak sesuai usianya (lihat panduan usia di atas).</li>
                    <li class="list-group-item">Beri nilai 1–4 untuk setiap bagian: Motorik Halus, Motorik Kasar, Komunikasi, Membaca/Menulis, Kemampuan Pra-Akademik, Sosial Skill, Ekspresif, Menyimak.</li>
                    <li class="list-group-item">Kirim penilaian dengan klik tombol "Proses Assessment".</li>
                    <li class="list-group-item">Lihat hasil (Normal/Berisiko Terlambat), diskusikan dengan orang tua, dan rencanakan tindak lanjut jika perlu.</li>
                </ol>
            </section>

            <!-- Footer -->
            <div class="text-center mt-5">
                <p class="text-muted">Punya pertanyaan? Tanyakan ke kepala sekolah atau hubungi psikolog sekolah.</p>
            </div>
        </div>
    </div>
</div>

<style>
body {
    font-family: 'Poppins', sans-serif;
    background-color: #f8f9fa;
}

.card {
    border-radius: 15px;
    transition: transform 0.3s;
}

.card:hover {
    transform: translateY(-5px);
}

.card-header {
    border-radius: 15px 15px 0 0;
    font-weight: 600;
}

.score-card, .threshold-card {
    border-left: 5px solid #4e73df;
}

.accordion-button {
    font-weight: 600;
    border-radius: 10px !important;
    background-color: #f8f9fa;
}

.accordion-item {
    border-radius: 10px;
    margin-bottom: 15px;
}

.table th, .table td {
    vertical-align: middle;
    font-size: 0.9rem;
    padding: 8px;
}

.table-assessment {
    width: 100%;
    table-layout: fixed;
}

.table-assessment th, .table-assessment td {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: normal;
    word-wrap: break-word;
}

.table-assessment th:nth-child(1), .table-assessment td:nth-child(1) {
    width: 40%;
}

.table-assessment th:nth-child(2), .table-assessment td:nth-child(2) {
    width: 20%;
}

.table-assessment th:nth-child(3), .table-assessment td:nth-child(3) {
    width: 15%;
}

.table-assessment th:nth-child(4), .table-assessment td:nth-child(4) {
    width: 25%;
}

.badge {
    font-size: 0.9em;
    padding: 8px 12px;
}

.header-banner h1 {
    font-size: 2.5rem;
    color: #343a40;
}

.list-group-numbered {
    font-size: 1.1rem;
}

.list-group-item {
    border: none;
    padding: 10px 0;
}

@media (max-width: 768px) {
    .header-banner h1 {
        font-size: 1.8rem;
    }
    .card-body {
        padding: 15px;
    }
    .table-assessment th, .table-assessment td {
        font-size: 0.8rem;
        padding: 6px;
    }
}
</style>