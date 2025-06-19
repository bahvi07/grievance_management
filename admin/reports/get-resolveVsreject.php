<?php
include '../../config/config.php';
header('Content-Type: application/json');

$statuses = ['resolve', 'reject', 'pending'];
$data = [
    'resolve' => 0,
    'reject' => 0,
    'pending' => 0
];

// Use GROUP BY to get all 3 counts in one query
$sql = "SELECT status, COUNT(*) as total FROM complaints WHERE status IN ('resolve', 'reject', 'pending') GROUP BY status";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $status = $row['status'];
    $count = (int)$row['total'];
    if (isset($data[$status])) {
        $data[$status] = $count;
    }
}

// Send JSON response
echo json_encode([
    'labels' => array_keys($data),
    'data' => array_values($data)
]);
?>
