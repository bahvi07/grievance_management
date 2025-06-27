<?php
require_once '../../config/session-config.php';
startSecureSession();
ob_start(); // Buffer output to prevent accidental whitespace/errors
require_once '../../config/config.php';
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
    
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $category = isset($_POST['category']) ? htmlspecialchars(trim($_POST['category'])) : '';
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 0;

    if (empty($id) || empty($category) || $status === '') {
        throw new Exception('Category ID, name and status are required.');
    }
    
    $stmt = $conn->prepare("UPDATE dept_category SET name = ?, status = ? WHERE id = ?");
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $stmt->bind_param("sii", $category, $status, $id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Category updated successfully!';
    } else {
        throw new Exception('Failed to update category.');
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log($e->getMessage(), 3, LOG_FILE);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
?> 