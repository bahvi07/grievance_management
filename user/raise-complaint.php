<?php
require_once '../config/session-config.php';
startSecureSession();
ob_start(); // Buffer output to prevent accidental whitespace/errors
require_once '../config/config.php';
header('Content-Type: application/json');
error_reporting(0); 
$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Verify CSRF token
    if (!CSRFProtection::verifyPostToken()) {
        throw new Exception('Security validation failed. Please refresh the page and try again.');
    }

    // Sanitize inputs
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $fname = isset($_POST['fName']) ? htmlspecialchars(trim($_POST['fName'])) : '';
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $category = isset($_POST['category']) ? htmlspecialchars(trim($_POST['category'])) : '';
    $complaint = isset($_POST['complaint']) ? htmlspecialchars(trim($_POST['complaint'])) : '';
    $location = isset($_POST['location']) ? htmlspecialchars(trim($_POST['location'])) : '';

    $errorMsg = [];
    $imagePath = null;

    // Validate inputs
    if (empty($name)) $errorMsg['name'] = "Name is required.";
    if (empty($fname)) $errorMsg['fName'] = "Father Name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errorMsg['email'] = "Valid email is required.";
    if (empty($category)) $errorMsg['category'] = "Select a category.";
    if (empty($complaint)) $errorMsg['complaint'] = "Write the complaint.";
    if (empty($location)) $errorMsg['location'] = "Please select your location.";

    // Handle file upload
    if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/images/complain_upload/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            throw new Exception("Could not create upload directory");
        }

        // Strict extension check
        $ext = strtolower(pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($ext, $allowed_ext)) {
            $errorMsg['img'] = "Invalid file extension. Only JPG, JPEG, PNG, and GIF allowed.";
        }

        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($fileInfo, $_FILES['img']['tmp_name']);
        finfo_close($fileInfo);
        $allowed_mimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];
        if (!isset($allowed_mimes[$ext]) || $mime !== $allowed_mimes[$ext]) {
            $errorMsg['img'] = "File extension and MIME type do not match or are not allowed.";
        } elseif ($_FILES['img']['size'] > 2 * 1024 * 1024) {
            $errorMsg['img'] = "Image must be less than 2MB.";
        } else {
            $newFileName = uniqid('img_', true) . '.' . $ext;
            $dest = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['img']['tmp_name'], $dest)) {
                chmod($dest, 0644); // Set file permissions
                $imagePath = '../assets/images/complain_upload/' . $newFileName;
            } else {
                $errorMsg['img'] = "Failed to upload the image.";
            }
        }
    }

    if (!empty($errorMsg)) {
        $response['errors'] = $errorMsg;
        throw new Exception('Validation failed');
    }
    // Generate unique refId
    do {
        $refId = random_int(100000, 999999);
        $checkQuery = $conn->prepare("SELECT refid FROM complaints WHERE refid = ?");
        $checkQuery->bind_param("i", $refId);
        $checkQuery->execute();
        $checkQuery->store_result();
    } while ($checkQuery->num_rows > 0);
    $checkQuery->close();
 if (!isset($_SESSION['user_phone'])) {
    throw new Exception('User not authenticated');
}
$phone = $_SESSION['user_phone'];

   $stmt = $conn->prepare("INSERT INTO complaints (refid, name, father, email, phone, location, category, complaint, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssssss", $refId, $name, $fname, $email, $phone, $location, $category, $complaint, $imagePath);

    
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }

    $stmt->close();
    
    $response = [
        'success' => true,
        'message' => "Complaint submitted successfully! Your reference ID is: $refId",
        'refId' => $refId
    ];

} catch (Exception $e) {
    error_log($e->getMessage(), 3, LOG_FILE); // log to centralized file
    $response['message'] = "An error occurred while submitting your complaint. Please try again.";
}

// Ensure no output before this
echo json_encode($response);
exit;
?>