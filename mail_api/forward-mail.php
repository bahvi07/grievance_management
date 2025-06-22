<?php
require_once '../config/session-config.php';
startSecureSession();

require_once __DIR__ . '/../vendor/autoload.php';
require_once '../auth/admin-auth-check.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set header to JSON for all responses from this script
header('Content-Type: application/json');

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Email configuration
$mail_host = $_ENV['MAIL_HOST'];
$mail_port = $_ENV['MAIL_PORT'];
$mail_username = $_ENV['MAIL_USERNAME'];
$mail_password = $_ENV['MAIL_PASSWORD'];
$mail_encryption = $_ENV['MAIL_ENCRYPTION'];
$mail_from_address = $_ENV['MAIL_FROM_ADDRESS'];
$mail_from_name = $_ENV['MAIL_FROM_NAME'];

function sendJsonResponse($success, $message, $data = []) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token from POST data
    if (!CSRFProtection::verifyPostToken()) {
        sendJsonResponse(false, 'Security validation failed. Please refresh the page and try again.');
    }
    
    $to = $_POST['dept_email'] ?? '';
    $refid = $_POST['refid'] ?? '';
    $name = $_POST['name'] ?? 'Unknown';
    $phone = $_POST['phone'] ?? '';
    $location = $_POST['location'] ?? '';
    $description = $_POST['description'] ?? '';
    $image = $_POST['image'] ?? '';
    $user_email = $_POST['email'] ?? '';

    if (empty($to) || empty($refid)) {
        sendJsonResponse(false, 'Email or Reference ID is missing!');
    }

    try {
        $mail = new PHPMailer(true);

        // Embed logo image
        $logoPath = '../assets/images/general_images/Bjplogo.jpg';
        if (file_exists($logoPath)) {
            $mail->addEmbeddedImage($logoPath, 'logo_cid');
            $logoSrc = 'cid:logo_cid';
        } else {
            $logoSrc = ''; // Fallback if logo not found
        }
        
        $mail->isSMTP();
        $mail->Host = $mail_host;
        $mail->SMTPAuth = true;
        $mail->Username = $mail_username;
        $mail->Password = $mail_password;
        $mail->SMTPSecure = $mail_encryption;
        $mail->Port = $mail_port;

        $mail->setFrom($mail_from_address, $mail_from_name);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $subject = "Complaint Forwarded (Ref ID: $refid)";
        $mail->Subject = $subject;
        
        // Update email body to use the embedded logo CID
        $message = '
        <div style="font-family: Arial, sans-serif; background: #fff6f2; padding: 24px; border-radius: 12px; max-width: 480px; margin: auto; border: 1px solid #ffd6c1;">
          <div style="text-align: center; margin-bottom: 16px;">
            <img src="' . $logoSrc . '" alt="Logo" style="height: 48px;">
            <h2 style="color: #FF4500; margin: 12px 0 0 0;">Vidhayak Sewa Kendra</h2>
          </div>
          <h3 style="color: #f15a29; text-align: center;">Complaint Forwarded to Your Department</h3>
          <table style="width: 100%; margin: 18px 0; font-size: 1.05em;">
            <tr><td><b>Reference ID:</b></td><td>' . htmlspecialchars($refid) . '</td></tr>
            <tr><td><b>Name:</b></td><td>' . htmlspecialchars($name) . '</td></tr>
            <tr><td><b>Email:</b></td><td>' . htmlspecialchars($user_email) . '</td></tr>
            <tr><td><b>Phone:</b></td><td>' . htmlspecialchars($phone) . '</td></tr>
            <tr><td><b>Location:</b></td><td>' . htmlspecialchars($location) . '</td></tr>
          </table>
          <div style="background: #fff; border: 2px dashed #f15a29; border-radius: 8px; padding: 18px; margin: 18px 0;">
            <b>Complaint Description:</b>
            <div style="margin-top: 8px; color: #333;">' . nl2br(htmlspecialchars($description)) . '</div>
          </div>
          <hr style="border: none; border-top: 1px solid #ffd6c1; margin: 24px 0;">
          <div style="font-size: 0.95em; color: #888; text-align: center;">
            Please contact admin for more details or to take action.<br>
            Need help? Contact <a href="mailto:support@yourdomain.com" style="color: #f15a29;">support@yourdomain.com</a>
          </div>
        </div>
        ';
        $mail->Body = $message;

        // Add complaint image as attachment if it exists
        if (!empty($image)) {
            // Construct the correct path to the complaint image
            $basePath = realpath(__DIR__ . '/..');
            $imagePath = $basePath . '/assets/images/complain_upload/' . basename($image);
            
            if (file_exists($imagePath)) {
                $mail->addAttachment($imagePath, 'complaint_image.jpg');
            } else {
                // Log if image not found for debugging
                error_log("Complaint image not found: $imagePath", 3, '../logs/error.log');
            }
        }
        
        $mail->send();
        
        // If mail is sent, update the status
        updateStatus($refid, $conn);

        sendJsonResponse(true, 'Complaint forwarded successfully!');

    } catch (Exception $e) {
        error_log('Mail sending failed: ' . $e->getMessage(), 3, '../logs/error.log'); 
        sendJsonResponse(false, 'Email could not be sent. Please check server logs.');
    }
} else {
    sendJsonResponse(false, 'Invalid request method.');
}

function updateStatus($ref, $conn) {
    if ($conn) {
        $stmt = $conn->prepare("UPDATE complaints SET status = 'forward' WHERE refid = ?");
        $stmt->bind_param("s", $ref);
        $stmt->execute();
        $stmt->close();
    }
}
?>