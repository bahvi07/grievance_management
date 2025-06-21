<?php
require_once '../config/session-config.php';
include '../config/config.php';

header('Content-Type: application/json');

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

$admin_id = isset($_POST['admin_id']) ? trim($_POST['admin_id']) : '';
$ip_address = getUserIP();
$user_agent = $_SERVER['HTTP_USER_AGENT'];

if (!empty($admin_id)) {
    // Update the database to unlock the account but preserve user agent
    $stmt = $conn->prepare("UPDATE login_attempts SET 
        is_locked = 0, 
        lock_expiry = NULL,
        attempt_count = 0,
        user_agent = ?
        WHERE admin_id = ? AND ip_address = ?");
    
    $stmt->bind_param("sss", $user_agent, $admin_id, $ip_address);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to unlock account']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid admin ID']);
}
?> 