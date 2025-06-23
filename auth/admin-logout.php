<?php
require_once '../config/session-config.php';
startSecureSession();
require_once '../config/config.php';

// Check if this is a manual logout (not session expiry)
$manualLogout = isset($_GET['manual']) && $_GET['manual'] === 'true';

if ($manualLogout) {
    // If a "remember me" cookie is set, delete the specific token from the database
    if (isset($_COOKIE['admin_token']) && strpos($_COOKIE['admin_token'], ':') !== false) {
        list($selector, $validator) = explode(':', $_COOKIE['admin_token'], 2);
        if (!empty($selector)) {
            $stmt = $conn->prepare("DELETE FROM admin_auth_tokens WHERE selector = ?");
            $stmt->bind_param("s", $selector);
            $stmt->execute();
            error_log("Admin token for selector {$selector} deleted on manual logout.", 3, LOG_FILE);
        }
    }
    
    // Clear the remember me cookie
    setcookie("admin_token", "", [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

session_unset();
session_destroy();

header("Location: admin-login.php");
exit;
