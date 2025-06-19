<?php
// File: /api/complaints/view.php
include '../helpers/auth.php';
include '../../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    // Verify user is authenticated
    $authResult = verifyToken($conn);
    if (!$authResult['success']) {
        sendResponse([
            'status' => 'error',
            'message' => $authResult['message']
        ], 401);
    }

    // Get user's phone number from token
    $phone = $authResult['phone'];

    // Get refId from query parameter
    $refId = isset($_GET['refId']) ? trim($_GET['refId']) : '';
    
    if (empty($refId) || strlen($refId) !== 6) {
        sendResponse([
            'status' => 'error',
            'message' => 'Invalid Reference ID. Must be 6 digits.'
        ], 400);
    }

    // Get specific complaint for this user
    $stmt = $conn->prepare("SELECT * FROM complaints WHERE refid = ? AND phone = ?");
    $stmt->bind_param('ss', $refId, $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        $complaint = [
            'refId' => $row['refid'],
            'name' => $row['name'],
            'father' => $row['father'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'complaint' => $row['complaint'],
            'category' => $row['category'],
            'location' => $row['location'],
            'status' => $row['status'] ?? 'Pending',
            'response' => $row['response'] ?? '',
            'image' => $row['image'] ? $row['image'] : null,
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];

        sendResponse([
            'status' => 'success',
            'message' => 'Complaint found',
            'data' => $complaint
        ]);
    } else {
        sendResponse([
            'status' => 'error',
            'message' => 'No complaint found with this Reference ID'
        ], 404);
    }

    $stmt->close();

} catch (Exception $e) {
    sendResponse([
        'status' => 'error',
        'message' => $e->getMessage()
    ], 500);
}
?>