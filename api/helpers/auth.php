<?php
// File: /api/helpers/auth.php

function verifyToken($conn) {
    // Get token from Authorization header
    $headers = getallheaders();
    $auth = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    
    // Handle different header formats
    if (empty($auth)) {
        // Try alternative header names
        $auth = isset($headers['authorization']) ? $headers['authorization'] : '';
    }
    
    $token = str_replace('Bearer ', '', $auth);
    
    if (empty($token)) {
        return [
            'success' => false,
            'message' => 'No authentication token provided'
        ];
    }
    
    // Check if token exists and is valid
    $stmt = $conn->prepare("
        SELECT phone, expires_at 
        FROM user_tokens 
        WHERE token = ? AND is_valid = 1 AND expires_at > NOW()
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return [
            'success' => true,
            'phone' => $row['phone'],
            'expires_at' => $row['expires_at']
        ];
    } else {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Invalid or expired token'
        ];
    }
}

// Helper function to send JSON response and exit
function sendResponse($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data);
    exit;
}
?>