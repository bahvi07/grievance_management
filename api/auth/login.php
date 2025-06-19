<?php
include '../../config/config.php';
// Handle CORS headers FIRST
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;

header('Content-Type: application/json');

// Validate phone input
if (!isset($_POST['phone']) || !preg_match('/^[6-9]\d{9}$/', $_POST['phone'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid phone number.']);
    exit;
}

$phone = $_POST['phone'];
$otp = rand(100000, 999999);
$is_used = 0;

$stmt = $conn->prepare("INSERT INTO otp_requests (phone, otp, is_used) VALUES (?, ?, ?)");
$stmt->bind_param("ssi", $phone, $otp, $is_used);

if ($stmt->execute()) {
    // In production, send the OTP via SMS
    echo json_encode(['status' => 'success', 'otp' => $otp]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to send OTP.']);
}

$stmt->close();
$conn->close();
?>
