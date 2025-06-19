<?php
session_start();
include '../config/config.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $editPhone = $_POST['edit-phone'];
        $oldPhone = $_SESSION['user_phone'];

        $stmt = $conn->prepare('UPDATE complaints SET phone=? WHERE phone=?');
        $stmt->bind_param('ss', $editPhone, $oldPhone);

        if ($stmt->execute()) {
            // ✅ Update the session phone
            $_SESSION['user_phone'] = $editPhone;

            $response = [
                'success' => true,
                'message' => 'Phone updated successfully'
            ];
        } else {
            throw new Exception('Failed to update phone number');
        }
    } else {
        throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
