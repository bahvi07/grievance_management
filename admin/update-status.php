<?php
require '../config/config.php';
include '../auth/admin-auth-check.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Verify CSRF token
    if (!CSRFProtection::verifyPostToken()) {
        throw new Exception('Security validation failed. Please refresh the page and try again.');
    }

    $refid = $_POST['r'] ?? '';
    $note = $_POST['n'] ?? '';

    if (empty($refid)) {
        throw new Exception('Reference ID is required');
    }

    $stmt = $conn->prepare("UPDATE complaints SET status='resolve', response=? WHERE refid=?");
    $stmt->bind_param("ss", $note, $refid);
    
    if ($stmt->execute()) {
        $response = [
            'success' => true,
            'message' => 'Complaint marked as resolved successfully'
        ];
    } else {
        throw new Exception('Failed to update complaint status');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>