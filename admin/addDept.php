<?php
session_start();
ob_start(); // Buffer output to prevent accidental whitespace/errors
require '../config/config.php';
header('Content-Type: application/json');
error_reporting(0);
$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid Request Method');
    }
    $category = isset($_POST['category']) ? htmlspecialchars(trim($_POST['category'])) : '';
    $deptName = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';
    $area = isset($_POST['area']) ? htmlspecialchars(trim($_POST['area'])) : '';

    $errorMsg = [];

    // Validate inputs
    if (empty($deptName)) $errorMsg['name'] = "Department Name is required.";
    if (empty($category)) $errorMsg['category'] = "Select a category.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errorMsg['email'] = "Valid email is required.";
    if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) $errorMsg['phone'] = "Valid 10-digit phone number is required.";
    if (empty($area)) $errorMsg['area'] = "Area is required.";

        if (!empty($errorMsg)) {
        $response['errors'] = $errorMsg;
        throw new Exception('Validation failed');
    }else{
        $stmt=$conn->prepare("INSERT INTO departments(category,name,email,phone,area) VALUES (?,?,?,?,?)");
        $stmt->bind_param('sssss',$category,$deptName,$email,$phone,$area);
        if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }

    $stmt->close();
    
    $response = [
        'success' => true,
        'message' => "Department Added Successfully "
    ];
    }

} catch (Exception $e) {
    error_log($e->getMessage(), 3, 'logs/error.log'); 
    $response['message'] = $e->getMessage();
}

// Ensure no output before this
echo json_encode($response);
exit;
