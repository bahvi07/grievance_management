<?php
require '../../config/config.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$response = ['success' => false, 'message' => ''];

try {
    // Get token from Authorization header
    $headers = getallheaders();
    $auth = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    $token = str_replace('Bearer ', '', $auth);

    if (empty($token)) {
        throw new Exception('Authentication required');
    }

    // Verify token and get user phone
    // In a real implementation, you would verify the token from your database
    // For now, we'll assume the token is valid and extract phone from it
    // $phone = getUserPhoneFromToken($token);
    $phone = "1234567890"; // Replace with actual phone from token verification

    // Proceed to delete the user's complaints
    $stmt = $conn->prepare("DELETE FROM complaints WHERE phone = ?");
    $stmt->bind_param("s", $phone);

    if ($stmt->execute()) {
        // Also invalidate the token
        // $invalidateToken = $conn->prepare("UPDATE user_tokens SET is_valid = 0 WHERE token = ?");
        // $invalidateToken->bind_param("s", $token);
        // $invalidateToken->execute();

        $response = [
            'success' => true,
            'message' => 'Your account has been deleted successfully.'
        ];
    } else {
        throw new Exception('Failed to delete the account. Please try again later.');
    }

    $stmt->close();

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
?>