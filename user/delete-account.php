<?php
session_start();
header('Content-Type: application/json');
include '../config/config.php';

$response = [];

if (!isset($_POST['phone']) || empty(trim($_POST['phone']))) {
    http_response_code(400);
    $response = [
        'success' => false,
        'message' => 'Phone number is required.'
    ];
    echo json_encode($response);
    exit;
}

$phone = trim($_POST['phone']);

// OPTIONAL: Confirm that the phone matches the logged-in user
if (!isset($_SESSION['user_phone']) || $_SESSION['user_phone'] !== $phone) {
    http_response_code(403);
    $response = [
        'success' => false,
        'message' => 'Unauthorized request.'
    ];
    echo json_encode($response);
    exit;
}

// Proceed to delete the user
$stmt = $conn->prepare("DELETE FROM complaints WHERE phone = ?");
$stmt->bind_param("s", $phone);

if ($stmt->execute()) {
    // Optional: Destroy session after account deletion
    session_destroy();

    $response = [
        'success' => true,
        'message' => 'Your account has been deleted successfully.'
    ];
} else {
    http_response_code(500);
    $response = [
        'success' => false,
        'message' => 'Failed to delete the account. Please try again later.'
    ];
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
