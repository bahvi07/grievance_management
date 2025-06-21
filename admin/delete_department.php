<?php
header('Content-Type: application/json');
require_once '../config/session-config.php';
startSecureSession();
require_once '../auth/admin-auth-check.php';
require_once '../config/config.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || !isset($input['csrf_token'])) {
    $response['message'] = 'Missing required parameters.';
    echo json_encode($response);
    exit;
}

if (!CSRFProtection::validateToken($input['csrf_token'])) {
    $response['message'] = 'Security validation failed. Please refresh the page.';
    http_response_code(403); // Forbidden
    echo json_encode($response);
    exit;
}

$department_id = filter_var($input['id'], FILTER_VALIDATE_INT);

if ($department_id === false) {
    $response['message'] = 'Invalid department ID.';
    echo json_encode($response);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->bind_param('i', $department_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response['success'] = true;
            $response['message'] = 'Department deleted successfully.';
        } else {
            $response['message'] = 'Department not found or already deleted.';
        }
    } else {
        $response['message'] = 'Database error: Could not delete department.';
    }
    $stmt->close();
} catch (Exception $e) {
    error_log('Delete Department Error: ' . $e->getMessage());
    $response['message'] = 'A server error occurred. Please try again later.';
}

$conn->close();
echo json_encode($response);
?> 