<?php
include '../../config/config.php'; // your DB connection
header('Content-Type: application/json');

$cityLabels = [];
$cityCounts = [];

$sql = "SELECT location, COUNT(*) as total FROM complaints GROUP BY location ORDER BY total DESC LIMIT 7 ";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $cityLabels[] = $row['location'];
    $cityCounts[] = (int)$row['total'];
}



echo json_encode([
    'labels' => $cityLabels,
    'data' => $cityCounts
]);
?>
