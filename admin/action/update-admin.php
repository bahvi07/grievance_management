<?php
require '../../config/config.php';
include '../../auth/admin-auth-check.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Enable error reporting for debugging
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request Method!');
    }
    
    // Verify CSRF token
    if (!CSRFProtection::verifyPostToken()) {
        throw new Exception('Security validation failed. Please refresh the page and try again.');
    }
    
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Validate name (letters and spaces, 2-50 chars)
    $name = trim($name);
    if (strlen($name) < 2 || strlen($name) > 50 || !preg_match('/^[a-zA-Z\s]+$/', $name)) {
        throw new Exception('Name must be 2-50 letters and spaces only.');
    }
    // Validate email
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }
    // Validate phone (10 digits)
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) !== 10) {
        throw new Exception('Invalid phone number. It must be 10 digits.');
    }

    if (empty($id)) {
        throw new Exception('Admin Id is Required');
    }
    $stmt = $conn->prepare("UPDATE admin SET name=?, email=?, phone=? WHERE admin_id=? ");
    $stmt->bind_param('ssss', $name, $email, $phone, $id);
    if ($stmt->execute()) {
        $response = [
            'success' => true,
            'message' => 'Admin Details Updated Successfully'
        ];
        // Update session name if this is the current admin
        if (isset($_SESSION['admin_id']) && $_SESSION['admin_id'] === $id) {
            $_SESSION['admin_name'] = $name;
        }
    } else {
        throw new Exception('Failed to update Admin Details: ' . $stmt->error);
    }
} catch (Exception $e) {
    error_log("Error in update-admin.php: " . $e->getMessage(), 3, LOG_FILE);
    $response['message'] = 'An error occurred while updating admin details. Please try again.';
}

echo json_encode($response);
?>