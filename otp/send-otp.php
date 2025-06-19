<?php
// send_otp.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../config/config.php';
header('Content-Type: application/json');

if (!isset($_POST['phone']) || !preg_match('/^[6-9]\d{9}$/', $_POST['phone'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid phone number.']);
    exit;
}

$phone = $_POST['phone'];
$otp = rand(100000, 999999);
$is_used = 0;

// Check table
$tableCheck = $conn->query("SHOW TABLES LIKE 'otp_requests'");
if (!$tableCheck || $tableCheck->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'otp_requests table missing.']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO otp_requests (phone, otp, is_used) VALUES (?, ?, ?)");
$stmt->bind_param("ssi", $phone, $otp, $is_used);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'otp' => $otp]); // For development only, remove `otp` in production
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to store OTP']);
}

$stmt->close();
$conn->close();
?>
