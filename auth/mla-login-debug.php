<?php
// Debug version of MLA login
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/session-config.php';
startSecureSession();

require_once '../config/config.php';

echo "<h2>MLA Login Debug</h2>";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    echo "<h3>POST Request Received</h3>";
    echo "POST data: " . json_encode($_POST) . "<br>";
    
    $username = isset($_POST['mlaId']) ? trim($_POST['mlaId']) : '';
    $pswd = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    echo "Username: '$username'<br>";
    echo "Password: '$pswd'<br>";

    if (empty($username) || empty($pswd)) {
        echo "❌ Empty username or password<br>";
    } else {
        try {
            echo "🔍 Checking database...<br>";
            
            // Check if user exists
            $stmt = $conn->prepare("SELECT * FROM mla_users WHERE username=?");
            if (!$stmt) {
                echo "❌ Prepare failed: " . $conn->error . "<br>";
            } else {
                $stmt->bind_param('s', $username);
                $stmt->execute();
                $result = $stmt->get_result();
                
                echo "Query executed. Found rows: " . $result->num_rows . "<br>";
                
                if ($result->num_rows > 0) {
                    $mla = $result->fetch_assoc();
                    echo "✅ User found: " . json_encode($mla) . "<br>";
                    
                    // Check password
                    $passwordMatch = ($pswd === $mla['password']) || password_verify($pswd, $mla['password']);
                    echo "Password match: " . ($passwordMatch ? "✅ YES" : "❌ NO") . "<br>";
                    echo "Expected password: '" . $mla['password'] . "'<br>";
                    echo "Provided password: '$pswd'<br>";
                    
                    if ($passwordMatch) {
                        echo "🎉 Login successful! Setting session...<br>";
                        
                        $_SESSION['mla_id'] = $mla['username'];
                        $_SESSION['mla_name'] = $mla['name'];
                        $_SESSION['mla_email'] = $mla['email'];
                        $_SESSION['last_activity'] = time();
                        
                        echo "Session data: " . json_encode($_SESSION) . "<br>";
                        echo "Redirecting to dashboard...<br>";
                        
                        // Don't redirect yet, let's see the session
                        echo "<script>setTimeout(function(){ window.location.href='../mla/dashboard.php'; }, 3000);</script>";
                    } else {
                        echo "❌ Password doesn't match<br>";
                    }
                } else {
                    echo "❌ User not found<br>";
                }
            }
        } catch (Exception $e) {
            echo "❌ Exception: " . $e->getMessage() . "<br>";
        }
    }
} else {
    echo "<h3>No POST request</h3>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MLA Login Debug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <h2>MLA Login (Debug)</h2>

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