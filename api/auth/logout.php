<?php
include '../../config/config.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Get token from Authorization header
$headers = getallheaders();
$auth = isset($headers['Authorization']) ? $headers['Authorization'] : '';
$token = str_replace('Bearer ', '', $auth);

if (empty($token)) {
    echo json_encode(['status' => 'error', 'message' => 'No token provided']);
    exit;
}

// Invalidate token (you'll need to add this table)
// $stmt = $conn->prepare("UPDATE user_tokens SET is_valid = 0 WHERE token = ?");
// $stmt->bind_param("s", $token);
// $stmt->execute();

echo json_encode(['status' => 'success', 'message' => 'Logged out successfully']);
?>