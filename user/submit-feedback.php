<?php
require '../config/config.php';

// Set response header
header('Content-Type: application/json');

// Get POST data and sanitize
$name = trim($_POST['feed_u_name'] ?? '');
$feedback = trim($_POST['feedback'] ?? '');
$user_phone = trim($_POST['user_phone'] ?? '');

// Validate required fields
if ($name === '' || $feedback === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Name and feedback are required.'
    ]);
    exit;
}

// Prepare SQL
$stmt = $conn->prepare("INSERT INTO feedback (user_name, user_phone, feedback) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $user_phone, $feedback);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Feedback submitted successfully.'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to submit feedback. Please try again.'
    ]);
}

$stmt->close();
$conn->close();
?>