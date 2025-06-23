<?php
require_once '../config/session-config.php';
startSecureSession();
require_once '../config/config.php';
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kolkata');

$otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
if (empty($otp) || !preg_match('/^\d{6}$/', $otp)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid OTP']);
    exit;
}
if (empty($phone) || !preg_match('/^[6-9]\d{9}$/', $phone)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid phone number.']);
    exit;
}

// Check for lockout BEFORE attempting to verify OTP
$ip_address = $_SERVER['REMOTE_ADDR'];
$stmt = $conn->prepare("SELECT * FROM user_login_attempts WHERE phone = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("s", $phone);
$stmt->execute();
$attempt = $stmt->get_result()->fetch_assoc();

if ($attempt && $attempt['is_locked'] && strtotime($attempt['lock_expiry']) > time()) {
    $remaining_time = strtotime($attempt['lock_expiry']) - time();
    $minutes = floor($remaining_time / 60);
    $seconds = $remaining_time % 60;
    echo json_encode(['status' => 'error', 'message' => "Account is locked. Please try again in {$minutes}m {$seconds}s."]);
    exit;
}

// Find the latest unused, unexpired OTP for this phone
$stmt = $conn->prepare("
    SELECT * FROM otp_requests
    WHERE phone = ? AND is_used = 0 AND expires_at >= NOW()
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $otp_id = $row['id'];
    $phone = $row['phone'];
    $otp_hash = $row['otp'];

    // Verify OTP
    if (password_verify($otp, $otp_hash)) {
        // Generate secure values
        $token = bin2hex(random_bytes(16));
        $user_name = 'user_' . substr($phone, -4) . '_' . substr(bin2hex(random_bytes(2)), 0, 4);

        // Update user_token and user_name
        $update = $conn->prepare("
            UPDATE otp_requests 
            SET is_used = 1, is_logged_in = 1, user_token = ?, user_name = ?
            WHERE id = ?
        ");
        $update->bind_param("ssi", $token, $user_name, $otp_id);
        $update->execute();

        // Optional: mark all previous phone entries as logged in
        $loginUpdate = $conn->prepare("UPDATE otp_requests SET is_logged_in = 1 WHERE phone = ?");
        $loginUpdate->bind_param("s", $phone);
        $loginUpdate->execute();

        // Reset login attempts on successful login
        $resetAttempts = $conn->prepare("UPDATE user_login_attempts SET attempt_count = 0, is_locked = 0, lock_expiry = NULL WHERE phone = ?");
        $resetAttempts->bind_param("s", $phone);
        $resetAttempts->execute();

        // Set session and cookie
        $_SESSION['is_logged_in'] = true;
        $_SESSION['user_phone'] = $phone;
        $_SESSION['user_name'] = $user_name;
        $_SESSION['user_token'] = $token;
        setcookie("user_token", $token, [
            'expires' => time() + (86400 * 30),
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);

        echo json_encode(['status' => 'success']);
    } else {
        // Increment failed login attempts
        if ($attempt) {
            $new_count = $attempt['attempt_count'] + 1;
            
            // Check if we should lock the account AFTER this failed attempt
            if ($new_count >= 5) { // MAX_ATTEMPTS
                $lock_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes')); // LOCK_DURATION_MINUTES
                $update = $conn->prepare("UPDATE user_login_attempts SET attempt_count = ?, is_locked = 1, lock_expiry = ?, last_attempt = NOW() WHERE id = ?");
                $update->bind_param("isi", $new_count, $lock_expiry, $attempt['id']);
                $update->execute();
                echo json_encode(['status' => 'error', 'message' => 'Too many failed attempts. Account locked for 10 minutes.']);
            } else {
                $update = $conn->prepare("UPDATE user_login_attempts SET attempt_count = ?, last_attempt = NOW() WHERE id = ?");
                $update->bind_param("ii", $new_count, $attempt['id']);
                $update->execute();
                echo json_encode(['status' => 'error', 'message' => 'OTP is incorrect or expired']);
            }
        } else {
            // First failed attempt
            $insert = $conn->prepare("INSERT INTO user_login_attempts (phone, ip_address, attempt_count, is_locked, last_attempt) VALUES (?, ?, 1, 0, NOW())");
            $insert->bind_param("ss", $phone, $ip_address);
            $insert->execute();
            echo json_encode(['status' => 'error', 'message' => 'OTP is incorrect or expired']);
        }
    }
} else {
    // Increment failed login attempts for invalid OTP
    if ($attempt) {
        $new_count = $attempt['attempt_count'] + 1;
        
        // Check if we should lock the account AFTER this failed attempt
        if ($new_count >= 5) { // MAX_ATTEMPTS
            $lock_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes')); // LOCK_DURATION_MINUTES
            $update = $conn->prepare("UPDATE user_login_attempts SET attempt_count = ?, is_locked = 1, lock_expiry = ?, last_attempt = NOW() WHERE id = ?");
            $update->bind_param("isi", $new_count, $lock_expiry, $attempt['id']);
            $update->execute();
            echo json_encode(['status' => 'error', 'message' => 'Too many failed attempts. Account locked for 10 minutes.']);
        } else {
            $update = $conn->prepare("UPDATE user_login_attempts SET attempt_count = ?, last_attempt = NOW() WHERE id = ?");
            $update->bind_param("ii", $new_count, $attempt['id']);
            $update->execute();
            echo json_encode(['status' => 'error', 'message' => 'OTP is incorrect or expired']);
        }
    } else {
        // First failed attempt
        $insert = $conn->prepare("INSERT INTO user_login_attempts (phone, ip_address, attempt_count, is_locked, last_attempt) VALUES (?, ?, 1, 0, NOW())");
        $insert->bind_param("ss", $phone, $ip_address);
        $insert->execute();
        echo json_encode(['status' => 'error', 'message' => 'OTP is incorrect or expired']);
    }
}
?>
