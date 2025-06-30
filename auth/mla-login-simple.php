<?php
// Simplified MLA login for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/session-config.php';
startSecureSession();

require_once '../config/config.php';

$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $username = isset($_POST['mlaId']) ? trim($_POST['mlaId']) : '';
    $pswd = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($username) || empty($pswd)) {
        $errorMsg = "Please enter both MLA ID and password.";
    } else {
        try {
            // Simple query without status check
            $stmt = $conn->prepare("SELECT * FROM mla_users WHERE username=?");
            if (!$stmt) {
                throw new Exception("Database prepare error: " . $conn->error);
            }
            
            $stmt->bind_param('s', $username);
            if (!$stmt->execute()) {
                throw new Exception("Database execute error: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
           
            if ($result->num_rows > 0) {
                $mla = $result->fetch_assoc();
                
                // Check password
                if ($pswd === $mla['password'] || password_verify($pswd, $mla['password'])) {
                    $_SESSION['mla_id'] = $mla['username'];
                    $_SESSION['mla_name'] = $mla['name'];
                    $_SESSION['mla_email'] = $mla['email'];
                    $_SESSION['last_activity'] = time();
                    
                    echo "✅ Login successful! Redirecting...<br>";
                    echo "Session data: " . json_encode($_SESSION) . "<br>";
                    
                    header("Location: ../mla/dashboard.php");
                    exit();
                } else {
                    $errorMsg = "Invalid password. Expected: " . $mla['password'] . ", Got: " . $pswd;
                }
            } else {
                $errorMsg = "User not found with username: " . $username;
            }
        } catch (Exception $e) {
            $errorMsg = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MLA Login - Simple Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .custom-body{
            background: linear-gradient(to bottom right, #fde4dc, #f8cfc3);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: #fff6f2;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
            overflow: hidden;
            display: flex;
            flex-direction: row;
            max-width: 900px;
            width: 100%;
        }
        .image-section {
            flex: 1;
            background-color: #fff6f2;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }
        .image-section img {
            max-width: 100%;
            height: auto;
        }
        .form-section {
            flex: 1;
            background-color: #ffe5da;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .form-section h2 {
            margin-bottom: 30px;
            color:#FF4500;
        }
        .form-section .btn {
            background-color: #f15a29;
            border: none;
        }
        .form-section .btn:hover {
            background-color: #e14b20;
        }
    </style>
</head>

<body class="custom-body">
    <div class="login-container">
        <div class="image-section">
            <img src="../assets/images/general_images/loginImg.png" alt="Login Illustration" />
        </div>

        <div class="form-section">
            <h2>MLA Login (Simple Test)</h2>

            <?php if (!empty($errorMsg)) : ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($errorMsg) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="mb-3">
                    <label for="mlaId" class="form-label">MLA ID</label>
                    <input type="text" class="form-control" id="mlaId" name="mlaId" value="<?= htmlspecialchars($_POST['mlaId'] ?? '') ?>" placeholder="mlademo" required />
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">Sign in</button>
            </form>
            
            <div class="mt-3">
                <small class="text-muted">
                    Test credentials: mlademo / password123
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 