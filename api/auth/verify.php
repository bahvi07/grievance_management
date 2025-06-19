<?php
include '../../config/config.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$response = ['status' => 'error', 'message' => ''];

try {
    // Sanitize and get OTP
    $otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';
    if (empty($otp) || !preg_match('/^\d{6}$/', $otp)) {
        throw new Exception('Invalid OTP format');
    }

    // Get latest matching OTP (not used, not expired)
    $stmt = $conn->prepare("
        SELECT * FROM otp_requests
        WHERE otp = ? AND is_used = 0 AND expires_at >= NOW()
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->bind_param("s", $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $otp_id = $row['id'];
        $phone = $row['phone'];

        // Start transaction
        $conn->begin_transaction();

        try {
            // Mark OTP as used
            $update = $conn->prepare("UPDATE otp_requests SET is_used = 1 WHERE id = ?");
            $update->bind_param("i", $otp_id);
            $update->execute();

            // Mark user as logged in
            $loginUpdate = $conn->prepare("UPDATE otp_requests SET is_logged_in = 1 WHERE phone = ?");
            $loginUpdate->bind_param("s", $phone);
            $loginUpdate->execute();

            // Generate a secure token for API authentication
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours')); // Token expires in 24 hours

            // Create user_tokens table if it doesn't exist
            $createTable = "CREATE TABLE IF NOT EXISTS user_tokens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                phone VARCHAR(15) NOT NULL,
                token VARCHAR(64) NOT NULL UNIQUE,
                is_valid TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NOT NULL,
                INDEX idx_token (token),
                INDEX idx_phone (phone)
            )";
            $conn->query($createTable);

            // Invalidate any existing tokens for this phone
            $invalidateOld = $conn->prepare("UPDATE user_tokens SET is_valid = 0 WHERE phone = ? AND is_valid = 1");
            $invalidateOld->bind_param("s", $phone);
            $invalidateOld->execute();

            // Store new token in database
            $storeToken = $conn->prepare("INSERT INTO user_tokens (phone, token, expires_at) VALUES (?, ?, ?)");
            $storeToken->bind_param("sss", $phone, $token, $expires_at);
            
            if (!$storeToken->execute()) {
                throw new Exception('Failed to store authentication token');
            }

            // Commit transaction
            $conn->commit();

            $response = [
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'phone' => $phone,
                    'expires_at' => $expires_at
                ]
            ];

        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            throw $e;
        }

    } else {
        throw new Exception('OTP is incorrect or expired');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Close database connection
if (isset($stmt)) $stmt->close();
if (isset($update)) $update->close();
if (isset($loginUpdate)) $loginUpdate->close();
if (isset($storeToken)) $storeToken->close();
$conn->close();

echo json_encode($response);
exit;
?>