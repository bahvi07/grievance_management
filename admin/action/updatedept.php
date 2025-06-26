<?php
header('Content-Type: application/json');
require '../../config/config.php'; 
include '../../auth/admin-auth-check.php';

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

    // Validate and sanitize fields
    // Name: 2-50 letters/spaces
    if (strlen($name) < 2 || strlen($name) > 50 || !preg_match('/^[a-zA-Z\s]+$/', $name)) {
        $response['message'] = "Name must be 2-50 letters and spaces only.";
        echo json_encode($response);
        exit;
    }
    // Email
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Invalid email format.";
        echo json_encode($response);
        exit;
    }
    // Phone: 10 digits
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) !== 10) {
        $response['message'] = "Phone must be 10 digits.";
        echo json_encode($response);
        exit;
    }
    // Category: 2-50 chars
    if (strlen($category) < 2 || strlen($category) > 50) {
        $response['message'] = "Category must be 2-50 characters.";
        echo json_encode($response);
        exit;
    }
    // Area: 2-100 chars
    if (strlen($area) < 2 || strlen($area) > 100) {
        $response['message'] = "Area must be 2-100 characters.";
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