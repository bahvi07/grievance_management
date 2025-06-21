<?php
require_once '../config/session-config.php';
startSecureSession();
require_once '../config/config.php';

if (!validateSession()) {
    session_unset();
    session_destroy();
    header("Location: ../auth/admin-login.php");
    exit;
}

// Validate admin session data
if (
    !isset($_SESSION['admin_id']) ||
    !isset($_SESSION['admin_name']) ||
    !isset($_SESSION['admin_email'])
) {
    session_unset();
    session_destroy();
    header("Location: ../auth/admin-login.php");
    exit;
}

$timeout = 1800; // 30 minutes

// Refresh last activity time
$_SESSION['last_activity'] = time();
?>
