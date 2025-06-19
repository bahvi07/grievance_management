<?php
// File: /api/complaints/list.php
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

    // Get all complaints for this user
    $stmt = $conn->prepare("SELECT * FROM complaints WHERE phone = ? ORDER BY created_at DESC");
    $stmt->bind_param('s', $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    $complaints = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $complaints[] = [
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
        }
    }

    $stmt->close();

    sendResponse([
        'status' => 'success',
        'message' => 'Complaints retrieved successfully',
        'count' => count($complaints),
        'data' => $complaints
    ]);

} catch (Exception $e) {
    sendResponse([
        'status' => 'error',
        'message' => $e->getMessage()
    ], 500);
}
?>