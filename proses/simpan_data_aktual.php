<?php 
session_start(); 
include "../proses/connect.php"; // Perhatikan jalur koneksi yang benar

// Inisialisasi variabel pesan 
$message = "";

// Cek apakah ada data yang dikirim 
if (isset($_POST['actual_label'])) { 
    $success = true; // Flag untuk mengecek keberhasilan semua operasi 
    $failed_students = []; // Array untuk menyimpan nama siswa yang gagal disimpan 
    $saved_count = 0; // Menghitung jumlah data yang berhasil disimpan

    foreach ($_POST['actual_label'] as $student_id => $label) { 
        if (!empty($label)) { 
            // Sanitasi input 
            $student_id = intval($student_id); 
            $label = mysqli_real_escape_string($conn, $label);

            // Check if this student already has an entry in data_aktual 
            $check_query = "SELECT * FROM data_aktual WHERE student_id = '$student_id'"; 
            $check_result = mysqli_query($conn, $check_query);

            if (mysqli_num_rows($check_result) > 0) { 
                // Update existing record 
                $update_query = "UPDATE data_aktual SET status_aktual = '$label' WHERE student_id = '$student_id'"; 
                if (mysqli_query($conn, $update_query)) { 
                    $saved_count++; 
                } else { 
                    $success = false; 
                    $failed_students[] = "ID: $student_id (update failed)"; 
                } 
            } else { 
                // Insert new record 
                $insert_query = "INSERT INTO data_aktual (student_id, nama, status_aktual) 
                                SELECT '$student_id', name, '$label' FROM students WHERE student_id = '$student_id'";

                if (mysqli_query($conn, $insert_query)) { 
                    $saved_count++; 
                } else { 
                    $success = false; 
                    $failed_students[] = "ID: $student_id (insert failed)"; 
                } 
            } 
        } 
    }

    // Function to update model evaluation metrics
    function updateModelMetrics($conn) {
        // Calculate confusion matrix and metrics
        $sql = "SELECT 
                    ar.prediction as predicted_label,
                    da.status_aktual as actual_label,
                    COUNT(*) as count
                FROM assessment_results ar
                JOIN data_aktual da ON ar.student_id = da.student_id
                GROUP BY predicted_label, actual_label";
                
        $result = mysqli_query($conn, $sql);
        
        // Initialize metrics
        $metrics = [
            'total_predictions' => 0,
            'true_positive' => 0,
            'true_negative' => 0,
            'false_positive' => 0,
            'false_negative' => 0,
            'accuracy' => 0,
            'precision' => 0,
            'recall' => 0,
            'f1_score' => 0
        ];
        
        // Calculate confusion matrix
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $count = $row['count'];
                $metrics['total_predictions'] += $count;
                
                if ($row['actual_label'] == 'Perlu Pembelajaran Khusus' && $row['predicted_label'] == 'Terlambat') {
                    $metrics['true_positive'] = $count;
                } else if ($row['actual_label'] == 'Normal' && $row['predicted_label'] == 'Normal') {
                    $metrics['true_negative'] = $count;
                } else if ($row['actual_label'] == 'Normal' && $row['predicted_label'] == 'Terlambat') {
                    $metrics['false_positive'] = $count;
                } else if ($row['actual_label'] == 'Perlu Pembelajaran Khusus' && $row['predicted_label'] == 'Normal') {
                    $metrics['false_negative'] = $count;
                }
            }
            
            // Calculate metrics
            if ($metrics['total_predictions'] > 0) {
                $metrics['accuracy'] = ($metrics['true_positive'] + $metrics['true_negative']) / $metrics['total_predictions'];
                
                if (($metrics['true_positive'] + $metrics['false_positive']) > 0) {
                    $metrics['precision'] = $metrics['true_positive'] / ($metrics['true_positive'] + $metrics['false_positive']);
                }
                
                if (($metrics['true_positive'] + $metrics['false_negative']) > 0) {
                    $metrics['recall'] = $metrics['true_positive'] / ($metrics['true_positive'] + $metrics['false_negative']);
                }
                
                if (($metrics['precision'] + $metrics['recall']) > 0) {
                    $metrics['f1_score'] = 2 * ($metrics['precision'] * $metrics['recall']) / ($metrics['precision'] + $metrics['recall']);
                }
            }
            
            // Store metrics in database for dashboard
            // Check if a model_metrics table exists, if not, create it
            $check_table = "SHOW TABLES LIKE 'model_metrics'";
            $table_exists = mysqli_query($conn, $check_table);
            
            if (mysqli_num_rows($table_exists) == 0) {
                // Create table if not exists
                $create_table = "CREATE TABLE model_metrics (
                    id INT(11) AUTO_INCREMENT PRIMARY KEY,
                    total_predictions INT(11) NOT NULL,
                    true_positive INT(11) NOT NULL,
                    true_negative INT(11) NOT NULL,
                    false_positive INT(11) NOT NULL,
                    false_negative INT(11) NOT NULL,
                    accuracy DECIMAL(10,4) NOT NULL,
                    precision_val DECIMAL(10,4) NOT NULL,
                    recall DECIMAL(10,4) NOT NULL,
                    f1_score DECIMAL(10,4) NOT NULL,
                    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )";
                mysqli_query($conn, $create_table);
            }
            
            // Check if there are existing metrics
            $check_metrics = "SELECT id FROM model_metrics LIMIT 1";
            $metrics_exist = mysqli_query($conn, $check_metrics);
            
            if (mysqli_num_rows($metrics_exist) > 0) {
                // Update existing metrics
                $row = mysqli_fetch_assoc($metrics_exist);
                $metrics_id = $row['id'];
                
                $update_metrics = "UPDATE model_metrics SET
                    total_predictions = {$metrics['total_predictions']},
                    true_positive = {$metrics['true_positive']},
                    true_negative = {$metrics['true_negative']},
                    false_positive = {$metrics['false_positive']},
                    false_negative = {$metrics['false_negative']},
                    accuracy = {$metrics['accuracy']},
                    precision_val = {$metrics['precision']},
                    recall = {$metrics['recall']},
                    f1_score = {$metrics['f1_score']},
                    last_updated = NOW()
                    WHERE id = $metrics_id";
                mysqli_query($conn, $update_metrics);
            } else {
                // Insert new metrics
                $insert_metrics = "INSERT INTO model_metrics (
                    total_predictions, true_positive, true_negative, false_positive, false_negative,
                    accuracy, precision_val, recall, f1_score
                ) VALUES (
                    {$metrics['total_predictions']}, {$metrics['true_positive']}, {$metrics['true_negative']},
                    {$metrics['false_positive']}, {$metrics['false_negative']}, {$metrics['accuracy']},
                    {$metrics['precision']}, {$metrics['recall']}, {$metrics['f1_score']}
                )";
                mysqli_query($conn, $insert_metrics);
            }
        }
        
        return $metrics;
    }

    // Update metrics after saving data
    if ($saved_count > 0) {
        updateModelMetrics($conn);
    }

    // Menentukan pesan berdasarkan hasil operasi 
    if ($saved_count > 0) { 
        if (count($failed_students) > 0) { 
            // Beberapa berhasil, beberapa gagal 
            $failed_list = implode(", ", $failed_students); 
            $message = '<script>alert("Berhasil menyimpan ' . $saved_count . ' data, tetapi gagal menyimpan data untuk: ' . $failed_list . '"); window.location="../data_aktual"</script>'; 
        } else { 
            // Semua berhasil 
            $message = '<script>alert("' . $saved_count . ' data aktual berhasil disimpan! Evaluasi model berhasil diperbarui."); window.location="../data_aktual"</script>'; 
        } 
    } else { 
        // Semua gagal 
        if (count($failed_students) > 0) { 
            $failed_list = implode(", ", $failed_students); 
            $message = '<script>alert("Gagal menyimpan semua data! Error pada: ' . $failed_list . '"); window.location="../data_aktual"</script>'; 
        } else { 
            $message = '<script>alert("Gagal menyimpan data!"); window.location="../data_aktual"</script>'; 
        } 
    } 
} else { 
    $message = '<script>alert("Tidak ada data yang diproses."); window.location="../data_aktual"</script>'; 
}

echo $message; 
?>