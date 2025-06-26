<?php
require_once '../config/session-config.php';
startSecureSession();
ob_start(); // Buffer output to prevent accidental whitespace/errors
require_once '../config/config.php';
header('Content-Type: application/json');
error_reporting(0);
$response = ['success' => false, 'message' => ''];
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid Request Method');
    }
    if (!CSRFProtection::verifyPostToken()) {
        throw new Exception('Security validation failed. Please refresh the page and try again.');
    }
    $category = isset($_POST['category']) ? htmlspecialchars(trim($_POST['category'])) : '';
    $status = isset($_POST['status']) ? htmlspecialchars(trim($_POST['status'])) : '';

    if (empty($category) || $status === '') {
        throw new Exception('Category and status are required.');
    }
    $stmt = $conn->prepare("INSERT INTO dept_category (name, status) VALUES (?, ?)");
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    $stmt->bind_param("si", $category, $status);
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Category added successfully!';
    } else {
        throw new Exception('Failed to add category.');
    }
    $stmt->close();
} catch (Exception $e) {
    error_log($e->getMessage(), 3, LOG_FILE);
    $response['message'] = $e->getMessage();
}
echo json_encode($response);
exit;
?>