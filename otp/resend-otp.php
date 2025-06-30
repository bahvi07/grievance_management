<?php

require_once '../config/config.php';
header('Content-Type: application/json');

// --- Configuration ---
define('MAX_ATTEMPTS', 5);
define('LOCK_DURATION_MINUTES', 10);
define('OTP_EXPIRY_MINUTES', 1); // 1 minute expiry for OTP
define('RESEND_COOLDOWN_SECONDS', 60);

// --- Input Validation ---
if (!isset($_POST['phone']) || !preg_match('/^[6-9]\d{9}$/', $_POST['phone'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid phone number provided.']);
    exit;
}

$phone = $_POST['phone'];
$ip_address = $_SERVER['REMOTE_ADDR'];

$bypass_cooldown = isset($_POST['failed_verification']) && $_POST['failed_verification'] === 'true';

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
    echo json_encode([
        'status' => 'error', 
        'message' => "Your account is temporarily locked. Please try again in {$minutes}m {$seconds}s.",
        'remaining_attempts' => 0
    ]);
    exit;
}

// Don't increment attempt count for resend - only count verification attempts
// This prevents double counting and allows users to resend OTP without penalty
// --- Resend Cooldown Check (✅ MODIFIED: Skip if failed verification) ---
if (!$bypass_cooldown) {
    $stmt = $conn->prepare("SELECT created_at FROM otp_requests WHERE phone = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $lastOtp = $result->fetch_assoc();
        $timeSinceLastOtp = time() - strtotime($lastOtp['created_at']);
        
        if ($timeSinceLastOtp < RESEND_COOLDOWN_SECONDS) {
            $remainingCooldown = RESEND_COOLDOWN_SECONDS - $timeSinceLastOtp;
            echo json_encode([
                'status' => 'error', 
                'message' => "Please wait {$remainingCooldown} more seconds before requesting a new OTP."
            ]);
            exit;
        }
    }
}

// --- Generate and Store New OTP ---
$otp = rand(100000, 999999);
$expires_at = date('Y-m-d H:i:s', strtotime("+" . OTP_EXPIRY_MINUTES . " minutes"));
$stmt = $conn->prepare("INSERT INTO otp_requests (phone, otp, expires_at) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $phone, $otp, $expires_at);

if ($stmt->execute()) {
    // ✅ NEW: Log the bypass reason for debugging
    if ($bypass_cooldown) {
        error_log("OTP resend bypassed cooldown for phone: $phone (failed verification)", 3, LOG_FILE);
    }
    echo json_encode([
        'status' => 'success',
        'message' => 'A new OTP has been sent.',
        'otp' => $otp, // For development only. REMOVE in production.
        'remaining_attempts' => 0
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to generate a new OTP. Please try again.', 'remaining_attempts' => 0]);
}

$stmt->close();
$conn->close();
?>