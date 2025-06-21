<?php
require_once '../config/session-config.php';
startSecureSession();
require_once '../config/config.php';

if (isset($_SESSION['user_phone'])) {
    $phone = $_SESSION['user_phone'];

    // Clear token from DB
    $stmt = $conn->prepare("UPDATE otp_requests SET is_logged_in = 0, user_token = NULL WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
}

session_unset();
session_destroy();

// Remove cookie
setcookie("user_token", "", time() - 3600, "/");

header("Location: login.php");

exit;
?>
