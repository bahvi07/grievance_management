<?php
// send_code.php
require_once '../config/session-config.php';
startSecureSession();
require_once '../vendor/phpmailer/phpmailer/src/Exception.php';
require_once '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once '../vendor/phpmailer/phpmailer/src/SMTP.php';
require __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../config/config.php';
header('Content-Type: application/json');

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
$response = ['success' => false, 'message' => ''];
try {
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        throw new Exception('Invalid Request Method');
    }
    // Verify CSRF token from JSON request
    if (!CSRFProtection::verifyJsonToken()) {
        throw new Exception('Security validation failed. Please refresh the page and try again.');
    } 
    // Get JSON data from request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);  
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    $email = $data['email'] ?? '';
    $id = $data['id'] ?? '';
    
    if (empty($email)) {
        throw new Exception('Email is required');
    }
    
    $code = rand(100000, 999999);
    // Store code in session for verification
    $_SESSION['reset_code'] = $code;
    $_SESSION['reset_email'] = $email;
    $_SESSION['reset_admin_id'] = $id;
    $_SESSION['reset_time'] = time(); // For expiration check
    
    // Email Config from .env file
    $host = $_ENV['MAIL_HOST'];
    $port = $_ENV['MAIL_PORT'];
    $username = $_ENV['MAIL_USERNAME'];
    $pswd = $_ENV['MAIL_PASSWORD'];
    $enc = $_ENV['MAIL_ENCRYPTION'];
    $sender = $_ENV['MAIL_FROM_ADDRESS'];
    $sender_name = $_ENV['MAIL_FROM_NAME'];

    $subject = "Password Reset Verification Code";
    $logoPath = '../assets/images/general_images/Bjplogo.jpg';
    if (file_exists($logoPath)) {
        $mail = new PHPMailer(true);
        $mail->addEmbeddedImage($logoPath, 'logo_cid');
        $logoSrc = 'cid:logo_cid';
    } else {
        $logoSrc = '../assets/images/general_images/Bjplogo.jpg'; // fallback
    }

    $mail->isSMTP();
    $mail->Host = $host;
    $mail->SMTPAuth = true;
    $mail->Username = $username;
    $mail->Password = $pswd;
    $mail->SMTPSecure = $enc;
    $mail->Port = $port;

    $mail->setFrom($sender, $sender_name);
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    
    // Use embedded logo in the email body
    $message = '
        <div style="font-family: Arial, sans-serif; background: #fff6f2; padding: 24px; border-radius: 12px; max-width: 480px; margin: auto; border: 1px solid #ffd6c1;">
          <div style="text-align: center; margin-bottom: 16px;">
            <img src="' . $logoSrc . '" alt="Logo" style="height: 48px;">
            <h2 style="color: #FF4500; margin: 12px 0 0 0;">Vidhayak Sewa Kendra</h2>
          </div>
          <h3 style="color: #f15a29; text-align: center;">Password Reset Verification Code</h3>
          <table style="width: 100%; margin: 18px 0; font-size: 1.05em;">
            <tr><td><b>Admin ID:</b></td><td>' . htmlspecialchars($id) . '</td></tr>
            <tr><td colspan="2" style="text-align:center;"><b>This code will expire in 10 minutes.</b></td></tr>
          </table>
          <div style="background: #fff; border: 2px dashed #f15a29; border-radius: 8px; padding: 24px; margin: 18px 0; text-align: center;">
            <span style="font-size: 1.1em; color: #333;">Your Verification Code:</span><br>
            <span style="font-size: 2em; letter-spacing: 6px; color: #f15a29; font-weight: bold; display: inline-block; margin-top: 10px;">' . htmlspecialchars($code) . '</span>
          </div>
          <hr style="border: none; border-top: 1px solid #ffd6c1; margin: 24px 0;">
          <div style="font-size: 0.95em; color: #888; text-align: center;">
            If you did not request this, please ignore this email.<br>
            Need help? Contact <a href="mailto:support@yourdomain.com" style="color: #f15a29;">support@yourdomain.com</a>
          </div>
        </div>
        ';
    $mail->Body = $message;

    $mail->send();
    
    $response = [
        'success' => true, 
        'message' => 'Verification code sent to your email successfully!'
    ];
    
} catch (Exception $e) {
    error_log('Failed to send OTP code: ' . $e->getMessage(), 3, LOG_FILE);
    $response = [
        'success' => false, 
        'message' => 'Could not send verification code. Please try again later.'
    ];
}

echo json_encode($response);
?>