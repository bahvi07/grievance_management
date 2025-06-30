<?php
require_once __DIR__ . '/../config/session-config.php';
startSecureSession();

// Log logout
if (isset($_SESSION['mla_id'])) {
    error_log("MLA logout - ID: " . $_SESSION['mla_id']);
}

// Destroy all session data
session_destroy();

// Redirect to login page
header('Location: mla-login.php');
exit();
?> 