<?php
include "connect.php";

$student_id = $_POST['student_id'];

$query = "SELECT tanggal, 
          (motorik_halus + motorik_kasar)/2 as motorik,
          (sosial_skill + ekspresif + menyimak)/3 as kognitif,
          (komunikasi + membaca + pra_akademik)/3 as bahasa
          FROM assessment_results 
          WHERE student_id = '$student_id'
          ORDER BY tanggal ASC";

$result = mysqli_query($conn, $query);

$data = [
    'dates' => [],
    'motorik' => [],
    'kognitif' => [],
    'bahasa' => []
];

while($row = mysqli_fetch_assoc($result)) {
    $data['dates'][] = date('d M', strtotime($row['tanggal']));
    $data['motorik'][] = $row['motorik'];
    $data['kognitif'][] = $row['kognitif'];
    $data['bahasa'][] = $row['bahasa'];
}

echo json_encode($data);
?>