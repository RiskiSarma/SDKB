<?php
include "connect.php";

// Log untuk memastikan file PHP yang benar digunakan
error_log("Memproses proses_assessment.php pada " . date('Y-m-d H:i:s') . " di " . __FILE__);

// Validasi input form
if (!isset($_POST['student']) || empty(trim($_POST['student']))) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Tidak ada siswa yang dipilih. Silakan pilih siswa.'
    ]));
}

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Ambil dan validasi data form
$student_id = trim($_POST['student']);
$inputs = [
    'motorik_halus' => isset($_POST['motorik_halus']) ? (int)$_POST['motorik_halus'] : 0,
    'motorik_kasar' => isset($_POST['motorik_kasar']) ? (int)$_POST['motorik_kasar'] : 0,
    'komunikasi' => isset($_POST['komunikasi']) ? (int)$_POST['komunikasi'] : 0,
    'membaca' => isset($_POST['membaca']) ? (int)$_POST['membaca'] : 0,
    'Kemampuan_Pra_Akademik' => isset($_POST['Kemampuan_Pra_Akademik']) ? (int)$_POST['Kemampuan_Pra_Akademik'] : 0,
    'Sosial_Skill' => isset($_POST['Sosial_Skill']) ? (int)$_POST['Sosial_Skill'] : 0,
    'Ekspresif' => isset($_POST['Ekspresif']) ? (int)$_POST['Ekspresif'] : 0,
    'Menyimak' => isset($_POST['Menyimak']) ? (int)$_POST['Menyimak'] : 0
];

// Validasi rentang skor (0-100)
foreach ($inputs as $key => $value) {
    if ($value < 0 || $value > 100) {
        die(json_encode([
            'status' => 'error',
            'message' => "Skor untuk $key harus antara 0 dan 100."
        ]));
    }
}

// Hitung skor rata-rata dalam persentase (0-100)
$motorik_score = (($inputs['motorik_halus'] + $inputs['motorik_kasar']) / 8) * 100;
$bahasa_score = (($inputs['komunikasi'] + $inputs['membaca'] + $inputs['Kemampuan_Pra_Akademik']) / 12) * 100;
$kognitif_score = (($inputs['Sosial_Skill'] + $inputs['Ekspresif'] + $inputs['Menyimak']) / 12) * 100;

// Log nilai skor untuk debugging
error_log("Skor dihitung: motorik=$motorik_score, bahasa=$bahasa_score, kognitif=$kognitif_score");

// Ambil tanggal saat ini
$tanggal = date('Y-m-d');

// Ambil usia dan nama siswa
$stmt = $conn->prepare("SELECT usia, name FROM students WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $stmt->close();
    die(json_encode([
        'status' => 'error',
        'message' => "Data siswa dengan ID $student_id tidak ditemukan."
    ]));
}
$student_data = $result->fetch_assoc();
$usia = (int)$student_data['usia'];
$student_name = $student_data['name'];
$stmt->close();

// Siapkan data untuk deteksi
$data = [
    'motorik_score' => $motorik_score,
    'bahasa_score' => $bahasa_score,
    'kognitif_score' => $kognitif_score,
    'usia' => $usia
];

// Log data yang dikirim ke predict.py
error_log("Data dikirim ke predict.py: " . json_encode($data));

// Panggil fungsi deteksi
$detection_result = detect_using_model($data);

// Periksa hasil deteksi
if ($detection_result['status'] !== 'success') {
    die(json_encode([
        'status' => 'error',
        'message' => 'Gagal melakukan deteksi: ' . htmlspecialchars($detection_result['message'], ENT_QUOTES, 'UTF-8')
    ]));
}

$prediction = $detection_result['prediction'];
$rekomendasi = json_encode($detection_result['recommendations'], JSON_UNESCAPED_UNICODE);

// Log hasil deteksi sebelum disimpan
error_log("Hasil deteksi: prediction=$prediction, rekomendasi=$rekomendasi");

// Simpan hasil deteksi ke database
$stmt = $conn->prepare("
    INSERT INTO assessment_results 
    (student_id, motorik_halus, motorik_kasar, komunikasi, membaca, pra_akademik, 
     sosial_skill, ekspresif, menyimak, prediction, rekomendasi, tanggal) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param(
    "siiiiiiiisss",
    $student_id,
    $inputs['motorik_halus'],
    $inputs['motorik_kasar'],
    $inputs['komunikasi'],
    $inputs['membaca'],
    $inputs['Kemampuan_Pra_Akademik'],
    $inputs['Sosial_Skill'],
    $inputs['Ekspresif'],
    $inputs['Menyimak'],
    $prediction,
    $rekomendasi,
    $tanggal
);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: ../detect");
    exit();
} else {
    $error = htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8');
    $stmt->close();
    die(json_encode([
        'status' => 'error',
        'message' => "Gagal menyimpan hasil deteksi: $error"
    ]));
}

/**
 * Fungsi untuk mendeteksi menggunakan model yang telah disimpan
 * Memanggil skrip Python yang memuat model dan melakukan deteksi
 * Mengembalikan "Normal" atau "Terlambat pada: [area]" berdasarkan hasil deteksi
 * @param array $data Data input berisi motorik_score, bahasa_score, kognitif_score, usia
 * @return array Hasil deteksi dengan status, prediction, dan recommendations
 */
function detect_using_model($data) {
    // Siapkan data untuk skrip Python
    $json_data = json_encode($data, JSON_UNESCAPED_UNICODE);
    
    // Simpan data ke file sementara
    $temp_file = tempnam(sys_get_temp_dir(), 'detect_');
    if ($temp_file === false) {
        error_log("Gagal membuat file sementara untuk deteksi");
        return [
            'status' => 'error',
            'message' => 'Gagal membuat file sementara untuk deteksi',
            'prediction' => '',
            'recommendations' => []
        ];
    }
    file_put_contents($temp_file, $json_data);
    
    // Panggil skrip Python
    $python_command = "python"; // Gunakan "python3" di Linux/Mac
    $script_path = dirname(__DIR__) . "/predict.py"; // Path ke predict.py
    
    // Log path untuk debugging
    error_log("Base path: " . dirname(__DIR__));
    error_log("Mencoba mengakses skrip Python di: $script_path");
    
    if (!file_exists($script_path)) {
        $error_message = "Skrip Python tidak ditemukan di: $script_path";
        error_log($error_message);
        if (file_exists($temp_file)) {
            unlink($temp_file);
        }
        return [
            'status' => 'error',
            'message' => $error_message,
            'prediction' => '',
            'recommendations' => []
        ];
    }
    
    $command = "$python_command $script_path $temp_file 2>&1";
    error_log("Menjalankan perintah: $command");
    
    $output = [];
    $return_var = 0;
    exec($command, $output, $return_var);
    
    // Bersihkan file sementara
    if (file_exists($temp_file)) {
        unlink($temp_file);
    }
    
    // Periksa error eksekusi
    if ($return_var !== 0) {
        $error_message = implode("\n", $output);
        error_log("Error deteksi Python: $error_message");
        return [
            'status' => 'error',
            'message' => "Deteksi model gagal: $error_message",
            'prediction' => '',
            'recommendations' => []
        ];
    }
    
    // Ambil hasil JSON (baris terakhir)
    $detection_result = end($output);
    $detection_data = json_decode($detection_result, true);
    
    // Validasi hasil deteksi
    if (!is_array($detection_data) || !isset($detection_data['prediction'])) {
        $error_message = "Hasil deteksi tidak valid: Kunci 'prediction' tidak ditemukan - " . implode("\n", $output);
        error_log($error_message);
        return [
            'status' => 'error',
            'message' => $error_message,
            'prediction' => '',
            'recommendations' => []
        ];
    }
    
    // Validasi nilai prediction
    if ($detection_data['prediction'] !== "Normal" && strpos($detection_data['prediction'], "Terlambat pada:") !== 0) {
        $error_message = "Hasil deteksi tidak valid: Format tidak sesuai - " . implode("\n", $output);
        error_log($error_message);
        return [
            'status' => 'error',
            'message' => $error_message,
            'prediction' => '',
            'recommendations' => []
        ];
    }
    
    // Log info debug
    $debug_info = implode("\n", $output);
    error_log("Info debug deteksi: $debug_info");
    
    return [
        'status' => 'success',
        'message' => '',
        'prediction' => trim($detection_data['prediction']),
        'recommendations' => isset($detection_data['recommendations']) ? $detection_data['recommendations'] : []
    ];
}
?>