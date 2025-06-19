<?php
include '../../config/config.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Get token from Authorization header
$headers = getallheaders();
$auth = isset($headers['Authorization']) ? $headers['Authorization'] : '';
$token = str_replace('Bearer ', '', $auth);

if (empty($token)) {
    echo json_encode(['status' => 'error', 'message' => 'No token provided']);
    exit;
}

// Check if token is valid (you'll need to add this table)
// $stmt = $conn->prepare("SELECT * FROM user_tokens WHERE token = ? AND is_valid = 1 AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
// $stmt->bind_param("s", $token);
// $stmt->execute();
// $result = $stmt->get_result();

// if ($result->num_rows === 1) {
//     $row = $result->fetch_assoc();
//     echo json_encode(['status' => 'success', 'is_valid' => true]);
// } else {
//     echo json_encode(['status' => 'error', 'is_valid' => false]);
// }

// For testing, we'll just return success
echo json_encode(['status' => 'success', 'is_valid' => true]);
?>