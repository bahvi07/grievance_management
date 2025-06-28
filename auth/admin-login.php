<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/session-config.php';
startSecureSession();
require_once '../config/config.php';

// Check if admin is already logged in and redirect to dashboard
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_name']) && isset($_SESSION['admin_email'])) {
    // Admin is already logged in, redirect to dashboard
    header("Location: ../admin/admin-dashboard.php");
    exit();
}

// If not logged in via session, check for remember me token
if (isset($_COOKIE['admin_token']) && strpos($_COOKIE['admin_token'], ':') !== false) {
    list($selector, $validator) = explode(':', $_COOKIE['admin_token'], 2);
    
    if (!empty($selector) && !empty($validator)) {
        $sql = "SELECT * FROM admin_auth_tokens WHERE selector = ? AND expires_at >= NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $selector);
        $stmt->execute();
        $token_data = $stmt->get_result()->fetch_assoc();
        
        if ($token_data && hash_equals($token_data['validator_hash'], hash('sha256', $validator))) {
            // Token is valid, log the user in
            $admin_id = $token_data['admin_id'];
            
            $admin_stmt = $conn->prepare("SELECT * FROM admin WHERE admin_id = ?");
            $admin_stmt->bind_param("s", $admin_id);
            $admin_stmt->execute();
            $admin = $admin_stmt->get_result()->fetch_assoc();
            
            if ($admin) {
                // Set session data
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['last_activity'] = time();
                
                // Update last used time on the token
                $update_stmt = $conn->prepare("UPDATE admin_auth_tokens SET last_used_at = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $token_data['id']);
                $update_stmt->execute();
                
                // Redirect to dashboard
                header("Location: ../admin/admin-dashboard.php");
                exit();
            }
        }
    }
}

$errorMsg = '';

// Get IP of User
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]; // first IP
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    // Verify CSRF token
    if (!CSRFProtection::verifyPostToken()) {
        $errorMsg = "Security validation failed. Please refresh the page and try again.";
    } else {
        $id = isset($_POST['adminId']) ? trim($_POST['adminId']) : '';
        $pswd = isset($_POST['password']) ? trim($_POST['password']) : '';

        // Check for lockout first
        $stmt = $conn->prepare("SELECT attempt_count, is_locked, lock_expiry FROM login_attempts WHERE admin_id = ? AND ip_address = ?");
        $ipAddress = getUserIP();
        $stmt->bind_param("ss", $id, $ipAddress);
        $stmt->execute();
        $attemptResult = $stmt->get_result();
        
        if ($attemptResult->num_rows > 0) {
            $attemptData = $attemptResult->fetch_assoc();
            if ($attemptData['is_locked'] == 1) {
                $lockExpiry = strtotime($attemptData['lock_expiry']);
                $currentTime = time();
                if ($lockExpiry > $currentTime) {
                    $remainingMinutes = ceil(($lockExpiry - $currentTime) / 60);
                    $errorMsg = "Account is locked. Please try again in $remainingMinutes minutes.";
                }
            }
        }

        if (empty($errorMsg)) {
            if (empty($id) || empty($pswd)) {
                $errorMsg = "Please enter both admin ID and password.";
            } else if (!preg_match('/^admin[a-zA-Z0-9_]{2,16}$/', $id)) {
                $errorMsg = "Admin ID must start with 'admin' and be 5-20 characters, using only letters, numbers, or underscores.";
            } else {
                try {
                    $stmt = $conn->prepare("SELECT * FROM admin WHERE admin_id=?");
                    if (!$stmt) {
                        throw new Exception("Database prepare error: " . $conn->error);
                    }
                    
                    $stmt->bind_param('s', $id);
                    if (!$stmt->execute()) {
                        throw new Exception("Database execute error: " . $stmt->error);
                    }
                    
                    $result = $stmt->get_result();
                   
                    if ($result->num_rows > 0) {
                        $admin = $result->fetch_assoc();
                        
                        if (password_verify($pswd, $admin['password'])) {
                            // Reset login attempts on successful login
                            $conn->query("DELETE FROM login_attempts WHERE admin_id='$id'");
                            $_SESSION['admin_id'] = $admin['admin_id'];
                            $_SESSION['admin_name'] = $admin['name'];
                            $_SESSION['admin_email'] = $admin['email'];
                            $_SESSION['last_activity'] = time();
                            
                            // Check if "Remember Me" is selected
                            if (isset($_POST['remember_me']) && $_POST['remember_me'] === 'on') {
                                $selector = bin2hex(random_bytes(16));
                                $validator = bin2hex(random_bytes(32));
                                $validator_hash = hash('sha256', $validator);
                                $expires_at = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 days
                                
                                // Store the new token in the database
                                $stmt = $conn->prepare(
                                    "INSERT INTO admin_auth_tokens (admin_id, selector, validator_hash, expires_at, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)"
                                );
                                $ip_address = getUserIP();
                                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                                $stmt->bind_param("ssssss", $admin['admin_id'], $selector, $validator_hash, $expires_at, $ip_address, $user_agent);
                                $stmt->execute();

                                // Set the cookie on the user's browser
                                $cookie_value = $selector . ':' . $validator;
                                setcookie("admin_token", $cookie_value, [
                                    'expires' => time() + (86400 * 30),
                                    'path' => '/',
                                    'secure' => true,
                                    'httponly' => true,
                                    'samesite' => 'Strict',
                                ]);
                                error_log("Admin 'Remember Me' token set for admin_id: " . $admin['admin_id'], 3, LOG_FILE);
                            }
                            
                            error_log("Admin login successful for ID: " . $admin['admin_id'], 3, LOG_FILE);
                            
                            header("Location: ../admin/admin-dashboard.php");
                            exit();
                        } else {
                            // Handle failed login attempts
                            $newAttemptCount = 1;
                            if ($attemptResult->num_rows > 0) {
                                $newAttemptCount = $attemptData['attempt_count'] + 1;
                                
                                // Lock account after 5 failed attempts
                                if ($newAttemptCount >= 5) {
                                    $lockExpiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                                    $stmt = $conn->prepare("UPDATE login_attempts SET 
                                        attempt_count = ?,
                                        is_locked = 1,
                                        lock_expiry = ?,
                                        last_attempt = NOW(),
                                        user_agent = ?
                                        WHERE admin_id = ? AND ip_address = ?");
                                    $userAgent = $_SERVER['HTTP_USER_AGENT'];
                                    $stmt->bind_param("issss", $newAttemptCount, $lockExpiry, $userAgent, $id, $ipAddress);
                                    $errorMsg = "Account locked due to multiple failed attempts. Please try again in 30 minutes.";
                                } else {
                                    // Just update attempt count
                                    $stmt = $conn->prepare("UPDATE login_attempts SET 
                                        attempt_count = ?,
                                        last_attempt = NOW(),
                                        user_agent = ?
                                        WHERE admin_id = ? AND ip_address = ?");
                                    $userAgent = $_SERVER['HTTP_USER_AGENT'];
                                    $stmt->bind_param("isss", $newAttemptCount, $userAgent, $id, $ipAddress);
                                    $errorMsg = "Invalid admin ID or password.";
                                }
                            } else {
                                // Create new record
                                $stmt = $conn->prepare("INSERT INTO login_attempts 
                                    (admin_id, ip_address, attempt_count, is_locked, last_attempt, user_agent) 
                                    VALUES (?, ?, 1, 0, NOW(), ?)");
                                $userAgent = $_SERVER['HTTP_USER_AGENT'];
                                $stmt->bind_param("sss", $id, $ipAddress, $userAgent);
                                $errorMsg = "Invalid admin ID or password.";
                            }
                            $stmt->execute();
                        }
                    } else {
                        error_log("No admin found with ID: " . $id, 3, LOG_FILE);
                        $errorMsg = "Invalid admin ID or password.";
                    }
                } catch (Exception $e) {
                    error_log("Admin login error: " . $e->getMessage(), 3, LOG_FILE);
                    $errorMsg = "An error occurred during login. Please try again.";
                }
            }
        }
    }
}

include '../includes/admin-header.php';
?>
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
    <!-- Image section -->
    <div class="image-section">
      <img src="../assets/images/general_images/loginImg.png" alt="Login Illustration" />
    </div>

    <div class="form-section">
      <h2>Login</h2>

      <div id="countdown" class="text-danger mb-3"></div>
      <form method="post" action="" id="loginForm">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label for="adminId" class="form-label">Admin Id</label>
          <input type="text" class="form-control" id="adminId" name="adminId" maxlength="50" placeholder="complainAdmin123" required />
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <div class="input-group">
            <input type="password" class="form-control" id="password" name="password" required>
            <span class="input-group-text bg-white" style="cursor:pointer;" id="togglePassword">
              <i class="fa fa-eye-slash" id="passwordIcon"></i>
            </span>
          </div>
        </div>

        <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me" checked>
          <label class="form-check-label" for="remember_me">Remember Me</label>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">Sign in</button>
      </form>
    </div>
  </div>
<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if (!empty($errorMsg)) : ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: '<?= addslashes($errorMsg) ?>',
            timer: 5000,
            timerProgressBar: true,
            showConfirmButton: false
        });
    </script>
<?php endif; ?>

<!-- jQuery CDN FIRST -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<!-- Then your custom scripts that use $ -->
<script>
function updateCountdown() {
    const lockExpiry = <?php 
        $stmt = $conn->prepare("SELECT UNIX_TIMESTAMP(lock_expiry) as expiry FROM login_attempts WHERE admin_id = ? AND ip_address = ? AND is_locked = 1");
        $id = isset($_POST['adminId']) ? trim($_POST['adminId']) : '';
        $ipAddress = getUserIP();
        $stmt->bind_param("ss", $id, $ipAddress);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            echo $data['expiry'] * 1000; // Convert to milliseconds
        } else {
            echo 'null';
        }
    ?>;

    if (lockExpiry) {
        const now = new Date().getTime();
        const distance = lockExpiry - now;
        
        if (distance > 0) {
            // Calculate minutes and seconds
            const minutes = Math.floor(distance / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            // Format the message
            let message = 'Please wait ';
            if (minutes > 0) {
                message += minutes + 'm ';
            }
            message += seconds + 's';
            
            document.getElementById('countdown').innerHTML = message;
            document.getElementById('loginForm').style.opacity = '0.5';
            document.getElementById('loginForm').style.pointerEvents = 'none';
            
            setTimeout(updateCountdown, 1000);
        } else {
            // Lock expired - update database first
            fetch('unlock-account.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'admin_id=<?php echo isset($_POST["adminId"]) ? $_POST["adminId"] : ""; ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('countdown').innerHTML = '';
                    document.getElementById('loginForm').style.opacity = '1';
                    document.getElementById('loginForm').style.pointerEvents = 'auto';
                }
            });
        }
    }
}

// Start countdown when page loads
window.onload = updateCountdown;
$(document).ready(()=>{
$('#togglePassword').on('click', () => {
    const passwordInput = $('#password');
    const icon = $('#passwordIcon');
    const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
    passwordInput.attr('type', type);
    icon.toggleClass('fa-eye fa-eye-slash');
});
});
</script>

</body>
</html>
