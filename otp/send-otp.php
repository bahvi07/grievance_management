<?php
// send_otp.php
require_once '../config/config.php';
header('Content-Type: application/json');

define('MAX_ATTEMPTS', 5);
define('LOCK_DURATION_MINUTES', 10);

$otp = rand(100000, 999999);
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
if (empty($otp) || !preg_match('/^\d{6}$/', $otp)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid OTP']);
    exit;
}
if (empty($phone) || !preg_match('/^[6-9]\d{9}$/', $phone)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid phone number.']);
    exit;
}

$ip_address = $_SERVER['REMOTE_ADDR'];
// --- Lockout Check ---
$stmt = $conn->prepare("SELECT * FROM user_login_attempts WHERE phone = ? OR ip_address = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("ss", $phone, $ip_address);
$stmt->execute();
$result = $stmt->get_result();
$attempt = $result->fetch_assoc();

if ($attempt && $attempt['is_locked'] && strtotime($attempt['lock_expiry']) > time()) {
    $remaining_time = strtotime($attempt['lock_expiry']) - time();
    $minutes = floor($remaining_time / 60);
    $seconds = $remaining_time % 60;
    echo json_encode(['status' => 'error', 'message' => "Account locked. Try again in {$minutes}m {$seconds}s."]);
    exit;
}

// Check table
$tableCheck = $conn->query("SHOW TABLES LIKE 'otp_requests'");
if (!$tableCheck || $tableCheck->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'otp_requests table missing.']);
    exit;
}

// First, insert OTP into otp_requests table
$otp_hash = password_hash($otp, PASSWORD_DEFAULT);
$is_used = 0;

$stmt = $conn->prepare("INSERT INTO otp_requests (phone, otp, is_used) VALUES (?, ?, ?)");
$stmt->bind_param("ssi", $phone, $otp_hash, $is_used);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'otp' => $otp]); // For development only, remove `otp` in production
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to store OTP']);
}

$stmt->close();
$conn->close();
?>
