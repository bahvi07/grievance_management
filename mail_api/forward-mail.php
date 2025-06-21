<?php
require_once '../config/session-config.php';
startSecureSession();

require_once __DIR__ . '/../vendor/autoload.php';
require_once '../auth/admin-auth-check.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's a JSON request or form submission
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    
    if (strpos($contentType, 'application/json') !== false) {
        // Handle JSON request
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if ($data === null) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
            exit;
        }

        $to = $data['to'] ?? '';
        $subject = $data['subject'] ?? '';
        $message = $data['message'] ?? '';
        $attachments = $data['attachments'] ?? [];
    } else {
        // Handle form submission - Verify CSRF token
        if (!CSRFProtection::verifyPostToken()) {
            echo "<!DOCTYPE html>
            <html>
            <head>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Security Error!',
                    text: 'Security validation failed. Please refresh the page and try again.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '../admin/complaints.php';
                });
            </script>
            </body>
            </html>";
            exit;
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
            echo "<!DOCTYPE html>
            <html>
            <head>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Information!',
                    text: 'Email or Reference ID is missing!',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '../admin/complaints.php';
                });
            </script>
            </body>
            </html>";
            exit;
        }

        $subject = "Complaint Forwarded (Ref ID: $refid)";
        $message = '
        <div style="font-family: Arial, sans-serif; background: #fff6f2; padding: 24px; border-radius: 12px; max-width: 480px; margin: auto; border: 1px solid #ffd6c1;">
          <div style="text-align: center; margin-bottom: 16px;">
            <img src=../assets/images/complain_upload/Bjplogo.jpg" alt="Logo" style="height: 48px;">
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
            Please to admin for more details or to take action.<br>
            Need help? Contact <a href="mailto:support@yourdomain.com" style="color: #f15a29;">support@yourdomain.com</a>
          </div>
        </div>
        ';
        $attachments = [];
        
        if (!empty($image) && file_exists("../assets/images/complain_upload/" . $image)) {
            $attachments[] = [
                'path' => "../assets/images/complain_upload/" . $image,
                'name' => "complaint_image.jpg"
            ];
        }
    }

    if (empty($to) || empty($subject) || empty($message)) {
        if (strpos($contentType, 'application/json') !== false) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        } else {
            echo "<!DOCTYPE html>
            <html>
            <head>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Missing required fields!',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '../admin/complaints.php';
                });
            </script>
            </body>
            </html>";
        }
        exit;
    }

    try {
        $mail = new PHPMailer(true);
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
        $mail->Subject = $subject;
        $mail->Body = $message;

        // Add attachments if any
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                if (isset($attachment['path']) && file_exists($attachment['path'])) {
                    $mail->addAttachment($attachment['path'], $attachment['name'] ?? '');
                }
            }
        }

        $mail->send();
        
        // If this was a complaint forwarding, update the status
        if (isset($refid)) {
            updateStatus($refid, $conn);
        }

        if (strpos($contentType, 'application/json') !== false) {
            echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
        } else {
            echo "<!DOCTYPE html>
            <html>
            <head>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Complaint forwarded successfully!',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    window.location.href = '../admin/complaints.php';
                });
            </script>
            </body>
            </html>";
        }
    } catch (Exception $e) {
        error_log('Mail sending failed: ' . $e->getMessage(), 3, LOG_FILE); // Log the actual error
        if (strpos($contentType, 'application/json') !== false) {
            echo json_encode(['success' => false, 'message' => 'Email could not be sent. Please check logs for details.']);
        } else {
            echo "<!DOCTYPE html>
            <html>
            <head>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Email Failed!',
                    text: 'Failed to send email. Please try again.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '../admin/complaints.php';
                });
            </script>
            </body>
            </html>";
        }
    }
} else {
    if (strpos($contentType, 'application/json') !== false) {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    } else {
        header('Location: ../admin/complaints.php');
        exit;
    }
}

function updateStatus($ref, $conn) {
    $stmt = $conn->prepare("UPDATE complaints SET status='forward',response='Your complaint is forwared to department to take quick action' WHERE refid=?");
    $stmt->bind_param('s', $ref);
    return $stmt->execute();
}
?>