<?php
// Delete Category Action
header('Content-Type: application/json');

require_once '../../config/config.php';
require_once '../../config/csrf.php';

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit();
}

// Debug: Log the received data
error_log("Delete category request: " . json_encode($input));

// Check if ID is provided
if (!isset($input['id']) || empty($input['id'])) {
    echo json_encode(['success' => false, 'message' => 'Category ID is required']);
    exit();
}

// Temporarily disable CSRF for debugging - remove this in production
if (!isset($input['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'CSRF token is required']);
    exit();
}

// Verify CSRF token
if (!CSRFProtection::validateToken($input['csrf_token'])) {
    error_log("CSRF validation failed. Received token: " . $input['csrf_token']);
    error_log("Session CSRF token: " . ($_SESSION['csrf_token'] ?? 'NOT SET'));
    echo json_encode(['success' => false, 'message' => 'Security validation failed']);
    exit();
}

$categoryId = intval($input['id']);

try {
    // First check if category exists
    $checkStmt = $conn->prepare("SELECT id, name FROM dept_category WHERE id = ?");
    $checkStmt->bind_param("i", $categoryId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Category not found or already deleted']);
        exit();
    }
    
    $category = $result->fetch_assoc();
    $categoryName = $category['name'];
    
    // Check if category is being used in departments
    $checkUsageStmt = $conn->prepare("SELECT COUNT(*) as count FROM departments WHERE category = ?");
    $checkUsageStmt->bind_param("s", $categoryName);
    $checkUsageStmt->execute();
    $usageResult = $checkUsageStmt->get_result();
    $usageCount = $usageResult->fetch_assoc()['count'];
    
    if ($usageCount > 0) {
        echo json_encode([
            'success' => false, 
            'message' => "Cannot delete category '$categoryName' because it is being used by $usageCount department(s). Please remove or reassign the departments first."
        ]);
        exit();
    }
    
    // Check if category is being used in complaints
    $checkComplaintsStmt = $conn->prepare("SELECT COUNT(*) as count FROM complaints WHERE category = ?");
    $checkComplaintsStmt->bind_param("s", $categoryName);
    $checkComplaintsStmt->execute();
    $complaintsResult = $checkComplaintsStmt->get_result();
    $complaintsCount = $complaintsResult->fetch_assoc()['count'];
    
    if ($complaintsCount > 0) {
        echo json_encode([
            'success' => false, 
            'message' => "Cannot delete category '$categoryName' because it is being used by $complaintsCount complaint(s). Please reassign the complaints first."
        ]);
        exit();
    }
    
    // Delete the category
    $deleteStmt = $conn->prepare("DELETE FROM dept_category WHERE id = ?");
    $deleteStmt->bind_param("i", $categoryId);
    
    if ($deleteStmt->execute()) {
        if ($deleteStmt->affected_rows > 0) {
            echo json_encode([
                'success' => true, 
                'message' => "Category '$categoryName' deleted successfully"
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $deleteStmt->error]);
    }
    
} catch (Exception $e) {
    error_log("Delete category error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while deleting the category']);
}
?> 