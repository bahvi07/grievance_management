<?php
require '../config/session-config.php';
startSecureSession();
include '../includes/admin-header.php';
include '../config/config.php';
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
                        
                        error_log("Admin login successful for ID: " . $admin['admin_id']);
                        
                        header("Location: ../admin/admin-dashboard.php");
                        exit();
                    } else {
                        // Check if record exists
                        $stmt = $conn->prepare("SELECT attempt_count FROM login_attempts WHERE admin_id = ? AND ip_address = ?");
                        $stmt->bind_param("ss", $id, $ipAddress);
                        $stmt->execute();
                        $attemptResult = $stmt->get_result();
                        
                        if ($attemptResult->num_rows > 0) {
                            // Update existing record
                            $attemptData = $attemptResult->fetch_assoc();
                            $newAttemptCount = $attemptData['attempt_count'] + 1;
                            
                            if ($newAttemptCount >= 5) {
                                // Lock account for exactly 5 minutes
                                $stmt = $conn->prepare("UPDATE login_attempts SET 
                                    attempt_count = ?, 
                                    is_locked = 1, 
                                    lock_expiry = NOW() + INTERVAL 5 MINUTE,
                                    last_attempt = NOW(),
                                    user_agent = ?
                                    WHERE admin_id = ? AND ip_address = ?");
                                $userAgent = $_SERVER['HTTP_USER_AGENT'];
                                $stmt->bind_param("isss", $newAttemptCount, $userAgent, $id, $ipAddress);
                                $errorMsg = "Too many failed attempts. Account locked for 5 minutes.";
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
                    error_log("No admin found with ID: " . $id);
                    $errorMsg = "Invalid admin ID or password.";
                }
            } catch (Exception $e) {
                error_log("Admin login error: " . $e->getMessage());
                $errorMsg = "An error occurred during login. Please try again.";
            }
        }
    }
}
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
