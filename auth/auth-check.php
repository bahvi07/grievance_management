<?php
require '../config/session-config.php';
startSecureSession();
require '../config/config.php';

if (!validateSession()) {
    header("Location: ../login.php");
    exit;
}

if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    return; // Session is valid
}

if (isset($_COOKIE['user_token'])) {
    $token = $_COOKIE['user_token'];

    $stmt = $conn->prepare("
        SELECT phone, user_name FROM otp_requests 
        WHERE user_token = ? AND is_logged_in = 1 
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $_SESSION['is_logged_in'] = true;
        $_SESSION['user_phone'] = $row['phone'];
        $_SESSION['user_name'] = $row['user_name'];
        return;
    }
}

// If called from a protected page, you can choose to redirect
if (!defined('ALLOW_ANONYMOUS')) {
    header("Location: ../login.php");
    exit;
}
