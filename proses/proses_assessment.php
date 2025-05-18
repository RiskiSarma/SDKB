<?php 
include "connect.php";

// Check if form is submitted
if (!isset($_POST['student']) || empty($_POST['student'])) {
    die("Error: No student selected");
}

// Set timezone to match your location
date_default_timezone_set('Asia/Jakarta'); // Sesuaikan dengan timezone Anda

// Get form data
$student_id = $_POST['student'];
$motorik_halus = isset($_POST['motorik_halus']) ? (int)$_POST['motorik_halus'] : 0;
$motorik_kasar = isset($_POST['motorik_kasar']) ? (int)$_POST['motorik_kasar'] : 0;
$komunikasi = isset($_POST['komunikasi']) ? (int)$_POST['komunikasi'] : 0;
$membaca = isset($_POST['membaca']) ? (int)$_POST['membaca'] : 0;
$kemampuan_pra_akademik = isset($_POST['Kemampuan_Pra_Akademik']) ? (int)$_POST['Kemampuan_Pra_Akademik'] : 0;
$sosial_skill = isset($_POST['Sosial_Skill']) ? (int)$_POST['Sosial_Skill'] : 0;
$ekspresif = isset($_POST['Ekspresif']) ? (int)$_POST['Ekspresif'] : 0;
$menyimak = isset($_POST['Menyimak']) ? (int)$_POST['Menyimak'] : 0;

// Calculate average scores for each category
$motorik_score = ($motorik_halus + $motorik_kasar) / 2;
$bahasa_score = ($komunikasi + $membaca + $kemampuan_pra_akademik) / 3;
$kognitif_score = ($sosial_skill + $ekspresif + $menyimak) / 3;

// Get current date in the YYYY-MM-DD format
$tanggal = date('Y-m-d');

// Prepare data for prediction
$data = array(
    'motorik_score' => $motorik_score,
    'bahasa_score' => $bahasa_score,
    'kognitif_score' => $kognitif_score
);
// Get student age
$query_age = mysqli_query($conn, "SELECT usia FROM students WHERE student_id = '$student_id'");
if (!$query_age) {
    die("Error querying student age: " . mysqli_error($conn));
}
$age_data = mysqli_fetch_assoc($query_age);
if (!$age_data) {
    die("Error: Age data not found for student ID $student_id");
}
$usia = $age_data['usia'];

// Prepare data for prediction (tambahkan usia)
$data = array(
    'motorik_score' => $motorik_score,
    'bahasa_score' => $bahasa_score,
    'kognitif_score' => $kognitif_score,
    'usia' => $usia  // Kirim usia ke Python
);
// Call the prediction function
$prediction_result = predict_using_model($data);

// Check if prediction was successful
if ($prediction_result['status'] !== 'success') {
    die("Error in prediction: " . $prediction_result['message']);
}

$prediction = $prediction_result['prediction'];
$rekomendasi = json_encode($prediction_result['recommendations']); // Convert recommendations to JSON

// Get student name for display
$query_name = mysqli_query($conn, "SELECT name FROM students WHERE student_id = '$student_id'");
if (!$query_name) {
    die("Error querying student: " . mysqli_error($conn));
}
$student_data = mysqli_fetch_assoc($query_name);
if (!$student_data) {
    die("Error: Student with ID $student_id not found");
}
$student_name = $student_data['name'];

// Save assessment results to database with current date
// Simpan assessment sebagai record baru
$query = "INSERT INTO assessment_results 
          (student_id, motorik_halus, motorik_kasar, komunikasi, membaca, pra_akademik, 
           sosial_skill, ekspresif, menyimak, prediction, rekomendasi, tanggal) 
          VALUES ('$student_id', '$motorik_halus', '$motorik_kasar', '$komunikasi', 
                 '$membaca', '$kemampuan_pra_akademik', '$sosial_skill', 
                 '$ekspresif', '$menyimak', '$prediction', '$rekomendasi', '$tanggal')";

$result = mysqli_query($conn, $query);
if ($result) {
    // Redirect to detection results page
    header("Location: ../detect");
    exit(); // Make sure to exit after redirect
} else {
    echo "Error: " . mysqli_error($conn);
}

/**
 * Function to predict using the saved model
 * This function calls a Python script that loads the model and makes a prediction
 * Will return either "Normal" or "Terlambat" based on the model's prediction
 * @return array with status, prediction, and recommendations
 */
function predict_using_model($data) {
    // Prepare data for Python script
    $json_data = json_encode($data);
    
    // Save data to a temporary file
    $temp_file = tempnam(sys_get_temp_dir(), 'predict_');
    file_put_contents($temp_file, $json_data);
    
    // Call Python script with the data file
    $python_command = "python"; // Use "python3" on Linux/Mac
    $script_path = dirname(__DIR__) . "/predict.py"; // Go up one directory level
    
    // Verify Python script exists
    if (!file_exists($script_path)) {
        return [
            'status' => 'error',
            'message' => "Python script not found at: $script_path",
            'prediction' => '',
            'recommendations' => []
        ];
    }
    
    $command = "$python_command $script_path $temp_file 2>&1";
    
    // Execute command and capture both output and error
    $output = [];
    $return_var = 0;
    exec($command, $output, $return_var);
    
    // Clean up
    if (file_exists($temp_file)) {
        unlink($temp_file);
    }
    
    // Check for execution errors
    if ($return_var !== 0) {
        // Python script exited with an error
        $error_message = implode("\n", $output);
        error_log("Python prediction error: $error_message");
        
        return [
            'status' => 'error',
            'message' => "Model prediction failed: $error_message",
            'prediction' => '',
            'recommendations' => []
        ];
    }
    
    // The last line should be our prediction (JSON format)
    $prediction_result = end($output);
    
    // Decode JSON result
    $prediction_data = json_decode($prediction_result, true);
    
    // Validate prediction
    if (!isset($prediction_data['prediction']) || ($prediction_data['prediction'] !== "Normal" && $prediction_data['prediction'] !== "Terlambat")) {
        error_log("Invalid prediction result: " . implode("\n", $output));
        
        return [
            'status' => 'error',
            'message' => "Invalid prediction result: $prediction_result",
            'prediction' => '',
            'recommendations' => []
        ];
    }
    
    // Log debug info
    $debug_info = implode("\n", $output);
    error_log("Prediction debug info: $debug_info");
    
    return [
        'status' => 'success',
        'message' => '',
        'prediction' => trim($prediction_data['prediction']),
        'recommendations' => isset($prediction_data['recommendations']) ? $prediction_data['recommendations'] : []
    ];
}
?>