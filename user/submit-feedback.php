<?php
// Prevent any output
ob_start();

// Start session
require_once '../config/session-config.php';
startSecureSession();

// Include configuration
require_once '../config/config.php';

// Clear any output buffer
ob_clean();

// Set JSON header
header('Content-Type: application/json');

// Disable error display
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // Verify CSRF token
    if (!CSRFProtection::verifyPostToken()) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Security validation failed. Please refresh the page and try again.'
        ]);
        exit;
    }

    // Get POST data
    $name = trim($_POST['feed_u_name'] ?? '');
    $feedback = trim($_POST['feedback'] ?? '');
    $user_phone = trim($_POST['user_phone'] ?? '');

    // Validate required fields
    if ($name === '' || $feedback === '') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Name and feedback are required.'
        ]);
        exit;
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO feedback (user_name, user_phone, feedback) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $user_phone, $feedback);

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Feedback submitted successfully.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to submit feedback. Please try again.'
        ]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred. Please try again later.'
    ]);
}
?>