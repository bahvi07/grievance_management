<?php
require_once '../phpmailer/src/Exception.php';
require_once '../phpmailer/src/PHPMailer.php';
require_once '../phpmailer/src/SMTP.php';
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

require '../config/config.php';

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
        // Handle form submission
        $to = $_POST['dept_email'] ?? '';
        $refid = $_POST['refid'] ?? '';
        $name = $_POST['name'] ?? 'Unknown';
        $phone = $_POST['phone'] ?? '';
        $location = $_POST['location'] ?? '';
        $image = $_POST['image'] ?? '';

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
        $message = "
        <h3>Complaint Forwarded</h3>
        <p><strong>Reference ID:</strong> $refid</p>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Phone:</strong> $phone</p>
        <p><strong>Location:</strong> $location</p>
        <p>This complaint has been forwarded to your department for review and action.</p>
        ";
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
        if (strpos($contentType, 'application/json') !== false) {
            echo json_encode(['success' => false, 'message' => 'Email could not be sent. Error: ' . $mail->ErrorInfo]);
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