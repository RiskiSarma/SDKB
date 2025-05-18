<?php
// proses_save_report.php
include "../proses/connect.php";
require_once "../proses/report_functions.php";

// Set the response header to JSON
header('Content-Type: application/json');

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get the report data
$report_type = isset($_POST['report_type']) ? $_POST['report_type'] : '';
$report_period = isset($_POST['report_period']) ? $_POST['report_period'] : '';
$report_content = isset($_POST['report_content']) ? $_POST['report_content'] : '';

// Validate the input
if (empty($report_type) || empty($report_period) || empty($report_content)) {
    echo json_encode([
        'success' => false,
        'message' => 'Incomplete data provided'
    ]);
    exit;
}

// Validate report type
if ($report_type !== 'monthly' && $report_type !== 'semester') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid report type'
    ]);
    exit;
}

// Save the report
$result = saveReport($report_type, $report_period, $report_content);

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => 'Report saved successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save report: ' . mysqli_error($conn)
    ]);
}