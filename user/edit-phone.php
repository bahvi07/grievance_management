<?php
require_once '../config/session-config.php';
startSecureSession();
include '../config/config.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verify CSRF token
        if (!CSRFProtection::verifyPostToken()) {
            throw new Exception('Security validation failed. Please refresh the page and try again.');
        }
        
        $editPhone = $_POST['newPhone'];
        $oldPhone = $_SESSION['user_phone'];

        $stmt = $conn->prepare('UPDATE complaints SET phone=? WHERE phone=?');
        $stmt->bind_param('ss', $editPhone, $oldPhone);

        if ($stmt->execute()) {
            // ✅ Update the session phone
            $_SESSION['user_phone'] = $editPhone;

            $response = [
                'success' => true,
                'message' => 'Phone updated successfully'
            ];
        } else {
            throw new Exception('Failed to update phone number');
        }
    } else {
        throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    error_log("Failed to update phone number: " . $e->getMessage(), 3, LOG_FILE);
    $response['message'] = 'Failed to update phone number. Please try again later.';
}

echo json_encode($response);
?>
