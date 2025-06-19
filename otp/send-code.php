<?php
// send_otp.php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../phpmailer/src/Exception.php';
require_once '../phpmailer/src/PHPMailer.php';
require_once '../phpmailer/src/SMTP.php';
require __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../config/config.php';
header('Content-Type: application/json');

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$response = ['success' => false, 'message' => ''];

try {
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        throw new Exception('Invalid Request Method');
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
    
    $host = $_ENV['MAIL_HOST'];
    $port = $_ENV['MAIL_PORT'];
    $username = $_ENV['MAIL_USERNAME'];
    $pswd = $_ENV['MAIL_PASSWORD'];
    $enc = $_ENV['MAIL_ENCRYPTION'];
    $sender = $_ENV['MAIL_FROM_ADDRESS'];
    $sender_name = $_ENV['MAIL_FROM_NAME'];

    $subject = "Password Reset Verification Code";
    $message = "
        <h3>Verification Code</h3>
        <p><strong>Admin ID:</strong> $id</p>
        <p><strong>Code:</strong> $code</p>
        <p>This code will expire in 10 minutes.</p>
        ";
    
    $mail = new PHPMailer(true);
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
    $mail->Body = $message;

    $mail->send();
    
    $response = [
        'success' => true, 
        'message' => 'Verification code sent to your email successfully!'
    ];
    
} catch (Exception $e) {
    $response = [
        'success' => false, 
        'message' => 'Email could not be sent. Error: ' . $e->getMessage()
    ];
}

echo json_encode($response);
?>