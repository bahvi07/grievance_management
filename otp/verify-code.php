<?php
// send_otp.php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../config/config.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid Method');
    }

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('Invalid JSON data');
    }

    $email = $_SESSION['reset_email'] ?? '';
    $id = $_SESSION['reset_admin_id'] ?? '';
    $send_time = $_SESSION['reset_time'] ?? 0; // When code was sent
    $orgCode = $_SESSION['reset_code'] ?? '';
    $code = $data['verificationCode'] ?? '';
    $newPswd = $data['newPassword'] ?? '';
    $confirmPswd = $data['confirmPassword'] ?? '';

    // Check if code expired (10 minutes = 600 seconds)
    if (!$send_time || (time() - $send_time) > 600) {
        $response = ['success' => false, 'message' => 'Code is expired, please resend a new code.'];
    } elseif ($code != $orgCode) {
        $response = ['success' => false, 'message' => 'Invalid verification code.'];
    } elseif ($newPswd !== $confirmPswd) {
        $response = ['success' => false, 'message' => 'Passwords do not match.'];
    } else {
        // Update password (hash it in production!)
        $hashedPassword = password_hash($newPswd, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admin SET password=? WHERE admin_id=? AND email=?");
        $stmt->bind_param("sss", $hashedPassword, $id, $email);
        if ($stmt->execute()) {
            $response = [
                'success' => true,
                'message' => 'Password reset successfully!'
            ];
            // Optionally clear session reset data
            unset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_admin_id'], $_SESSION['reset_time']);
        } else {
            $response = [
                'success' => false,
                'message' => 'Failed to update password.'
            ];
        }
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

echo json_encode($response);
?>