<?php
require_once '../config/session-config.php';
startSecureSession();
require_once '../config/config.php';

error_log("=== Admin Auth Check Started ===", 3, LOG_FILE);
error_log("Session ID: " . session_id(), 3, LOG_FILE);

// Check if we have admin session data
$hasAdminSession = isset($_SESSION['admin_id']) && isset($_SESSION['admin_name']) && isset($_SESSION['admin_email']);

// First check if we have a valid session with admin data
$sessionValid = validateSession() && $hasAdminSession;
error_log("Session Valid: " . ($sessionValid ? 'Yes' : 'No'), 3, LOG_FILE);
error_log("Has Admin Session: " . ($hasAdminSession ? 'Yes' : 'No'), 3, LOG_FILE);

// If the session is not valid, try to log in via "remember me" token
if (!$sessionValid) {
    if (isset($_COOKIE['admin_token']) && strpos($_COOKIE['admin_token'], ':') !== false) {
        list($selector, $validator) = explode(':', $_COOKIE['admin_token'], 2);

        if (!empty($selector) && !empty($validator)) {
            $sql = "SELECT * FROM admin_auth_tokens WHERE selector = ? AND expires_at >= NOW()";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $selector);
            $stmt->execute();
            $token_data = $stmt->get_result()->fetch_assoc();

            if ($token_data && hash_equals($token_data['validator_hash'], hash('sha256', $validator))) {
                // Token is valid, log the user in.
                $admin_id = $token_data['admin_id'];

                $admin_stmt = $conn->prepare("SELECT * FROM admin WHERE admin_id = ?");
                $admin_stmt->bind_param("s", $admin_id);
                $admin_stmt->execute();
                $admin = $admin_stmt->get_result()->fetch_assoc();

                if ($admin) {
                    // Set session data
                    $_SESSION['admin_id'] = $admin['admin_id'];
                    $_SESSION['admin_name'] = $admin['name'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $_SESSION['last_activity'] = time();

                    // Update last used time on the token
                    $update_stmt = $conn->prepare("UPDATE admin_auth_tokens SET last_used_at = NOW() WHERE id = ?");
                    $update_stmt->bind_param("i", $token_data['id']);
                    $update_stmt->execute();

                    error_log("Admin auto-login successful via token for admin_id: " . $admin['admin_id'], 3, LOG_FILE);
                }
            } elseif ($token_data) {
                // Validator was incorrect. This is a theft attempt. Delete all tokens for this admin.
                $conn->query("DELETE FROM admin_auth_tokens WHERE admin_id = '" . $conn->real_escape_string($token_data['admin_id']) . "'");
                setcookie("admin_token", "", time() - 3600, "/"); // Clear cookie
                error_log("Invalid validator for admin_id: {$token_data['admin_id']}. All tokens deleted.", 3, LOG_FILE);
            }
        }
    }
}

// Final check: if after all that, there's no admin session, redirect to login.
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin-login.php");
    exit;
}

// Validate admin session data (double check)
if (
    !isset($_SESSION['admin_id']) ||
    !isset($_SESSION['admin_name']) ||
    !isset($_SESSION['admin_email'])
) {
    error_log("Missing admin session data, redirecting to login", 3, LOG_FILE);
    session_unset();
    session_destroy();
    header("Location: ../auth/admin-login.php");
    exit;
}

$timeout = 1800; // 30 minutes

// Refresh last activity time for the active session
$_SESSION['last_activity'] = time();
error_log("Admin auth check completed successfully for: " . $_SESSION['admin_id'], 3, LOG_FILE);
?>
