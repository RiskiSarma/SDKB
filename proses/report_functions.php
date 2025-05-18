<?php
// report_functions.php

/**
 * Get all saved reports from the database
 * @return array Array of reports
 */
function getSavedReports() {
    global $conn;
    $reports = [];
    
    $query = "SELECT * FROM reports ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $reports[] = $row;
        }
        mysqli_free_result($result);
    }
    
    return $reports;
}

/**
 * Get a specific report by ID
 * @param int $id Report ID
 * @return array|null Report data or null if not found
 */
function getReportById($id) {
    global $conn;
    
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT * FROM reports WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $report = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        return $report;
    }
    
    return null;
}

/**
 * Save a new report to the database
 * @param string $type Report type (monthly or semester)
 * @param string $period Report period (e.g. "March 2025")
 * @param string $content HTML content of the report
 * @return bool True if saved successfully, false otherwise
 */
function saveReport($type, $period, $content) {
    global $conn;
    
    $type = mysqli_real_escape_string($conn, $type);
    $period = mysqli_real_escape_string($conn, $period);
    $content = mysqli_real_escape_string($conn, $content);
    
    $query = "INSERT INTO reports (type, period, content, created_at) 
              VALUES ('$type', '$period', '$content', NOW())";
    
    return mysqli_query($conn, $query);
}