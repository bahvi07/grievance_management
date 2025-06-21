<?php
header('Content-Type: application/json');
require '../config/config.php'; 
include '../auth/admin-auth-check.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!CSRFProtection::verifyPostToken()) {
        $response['message'] = "Security validation failed. Please refresh the page and try again.";
        echo json_encode($response);
        exit;
    }
    
    // Sanitize and validate inputs
    $id = $_POST['id'] ?? '';
    $category = trim($_POST['category'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $area = trim($_POST['area'] ?? '');

    if (!$id || !$category || !$name || !$email || !$phone || !$area) {
        $response['message'] = "All fields are required.";
        echo json_encode($response);
        exit;
    }

    // Prepare and execute update query
    $stmt = $conn->prepare("UPDATE departments SET category=?, name=?, email=?, phone=?, area=? WHERE id=?");
    $stmt->bind_param("sssssi", $category, $name, $email, $phone, $area, $id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = "Department updated successfully.";
    } else {
        $response['message'] = "Failed to update department.";
    }

    $stmt->close();
    $conn->close();
} else {
    $response['message'] = "Invalid request method.";
}

echo json_encode($response);
?>