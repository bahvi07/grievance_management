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

    // Debug logging
    error_log("Attempting to update complaint with refid: " . $refid);

    if (empty($refid)) {
        throw new Exception('Reference ID is required');
    }

    // First check if complaint exists
    $check_stmt = $conn->prepare("SELECT refid, status FROM complaints WHERE refid = ?");
    $check_stmt->bind_param("s", $refid);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("No complaint found with refid: " . $refid);
        throw new Exception('Complaint not found');
    }
    
    // Debug: Log current status
    $current = $result->fetch_assoc();
    error_log("Current complaint status: " . $current['status']);
    
    $check_stmt->close();

    // Now update the complaint
    $stmt = $conn->prepare("UPDATE complaints SET status = 'resolve', response = ?, updated_at = CURRENT_TIMESTAMP WHERE refid = ?");
    $stmt->bind_param("ss", $note, $refid);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            error_log("Successfully updated complaint status for refid: " . $refid);
            $response = [
                'success' => true,
                'message' => 'Complaint marked as resolved successfully'
            ];
        } else {
            error_log("No rows affected when updating refid: " . $refid);
            throw new Exception('No changes were made to the complaint');
        }
    } else {
        error_log("Failed to update complaint. SQL Error: " . $stmt->error);
        throw new Exception('Failed to update complaint status: ' . $stmt->error);
    }
    $stmt->close();
} catch (Exception $e) {
    error_log('Error in update-status.php: ' . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>