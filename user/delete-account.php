<?php
require_once '../config/session-config.php';
startSecureSession();
require_once '../config/config.php';
header('Content-Type: application/json');

$response = [];

// Debug information
// error_log("Delete account request received");
// error_log("POST data: " . print_r($_POST, true));
// error_log("Session data: " . print_r($_SESSION, true));

// Verify CSRF token
if (!CSRFProtection::verifyPostToken()) {
    error_log("CSRF validation failed");
    http_response_code(403);
    $response = [
        'success' => false,
        'message' => 'Security validation failed. Please refresh the page and try again.',
        'debug' => [
            'post_token' => $_POST['csrf_token'] ?? 'NOT_SET',
            'session_token' => $_SESSION['csrf_token'] ?? 'NOT_SET',
            'session_time' => $_SESSION['csrf_token_time'] ?? 'NOT_SET'
        ]
    ];
    echo json_encode($response);
    exit;
}

if (!isset($_POST['phone']) || empty(trim($_POST['phone']))) {
    http_response_code(400);
    $response = [
        'success' => false,
        'message' => 'Phone number is required.'
    ];
    echo json_encode($response);
    exit;
}

$phone = trim($_POST['phone']);

// Confirm that the phone matches the logged-in user
if (!isset($_SESSION['user_phone']) || $_SESSION['user_phone'] !== $phone) {
    http_response_code(403);
    $response = [
        'success' => false,
        'message' => 'Unauthorized request.'
    ];
    echo json_encode($response);
    exit;
}

// Start transaction for data integrity
$conn->begin_transaction();

try {
    // Delete from complaints table
    $stmt1 = $conn->prepare("DELETE FROM complaints WHERE phone = ?");
    $stmt1->bind_param("s", $phone);
    $stmt1->execute();
    $complaintsDeleted = $stmt1->affected_rows;
    $stmt1->close();

    // Delete from otp_requests table
    $stmt2 = $conn->prepare("DELETE FROM otp_requests WHERE phone = ?");
    $stmt2->bind_param("s", $phone);
    $stmt2->execute();
    $otpDeleted = $stmt2->affected_rows;
    $stmt2->close();

    // Delete from user_login_attempts table
    $stmt3 = $conn->prepare("DELETE FROM user_login_attempts WHERE phone = ?");
    $stmt3->bind_param("s", $phone);
    $stmt3->execute();
    $attemptsDeleted = $stmt3->affected_rows;
    $stmt3->close();

    // Delete from feedback table
    $stmt4 = $conn->prepare("DELETE FROM feedback WHERE user_phone = ?");
    $stmt4->bind_param("s", $phone);
    $stmt4->execute();
    $feedbackDeleted = $stmt4->affected_rows;
    $stmt4->close();

    // Commit transaction
    $conn->commit();

    // Clear all session data
    $_SESSION = array();

    // Destroy the session cookie with proper parameters
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
    }

    // Destroy the user_token cookie with proper parameters
    if (isset($_COOKIE['user_token'])) {
        setcookie('user_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
    }

    // Destroy the session
    session_destroy();

    // Force clear any remaining cookies
    if (isset($_SERVER['HTTP_COOKIE'])) {
        $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
        foreach($cookies as $cookie) {
            $parts = explode('=', $cookie);
            $name = trim($parts[0]);
            if ($name == 'user_token' || $name == session_name()) {
                setcookie($name, '', [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict',
                ]);
            }
        }
    }

    error_log("Account deletion successful - Complaints: $complaintsDeleted, OTP: $otpDeleted, Attempts: $attemptsDeleted, Feedback: $feedbackDeleted");

    $response = [
        'success' => true,
        'message' => 'Your account has been deleted successfully.',
        'deleted_data' => [
            'complaints' => $complaintsDeleted,
            'otp_requests' => $otpDeleted,
            'login_attempts' => $attemptsDeleted,
            'feedback' => $feedbackDeleted
        ]
    ];

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log("Account deletion failed: " . $e->getMessage());
    
    http_response_code(500);
    $response = [
        'success' => false,
        'message' => 'Failed to delete the account. Please try again later.',
        'error' => $e->getMessage()
    ];
}

$conn->close();

echo json_encode($response);
?>
