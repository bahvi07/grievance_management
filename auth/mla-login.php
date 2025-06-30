<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/session-config.php';
startSecureSession();
require_once '../config/config.php';

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

// Debug: Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    // Debug output
    error_log("MLA Login: POST request received");
    error_log("POST data: " . json_encode($_POST));
    
    $username = isset($_POST['mlaId']) ? trim($_POST['mlaId']) : '';
    $pswd = isset($_POST['password']) ? trim($_POST['password']) : '';

    error_log("Username: $username, Password: $pswd");

    if (empty($username) || empty($pswd)) {
        $errorMsg = "Please enter both MLA ID and password.";
        error_log("MLA Login: Empty username or password");
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
           
            error_log("MLA Login: Query executed, found rows: " . $result->num_rows);
           
            if ($result->num_rows > 0) {
                $mla = $result->fetch_assoc();
                error_log("MLA Login: User found - " . json_encode($mla));
                
                // Check password
                if ($pswd === $mla['password'] || password_verify($pswd, $mla['password'])) {
                    error_log("MLA Login: Password correct, setting session");
                    
                    $_SESSION['mla_id'] = $mla['username'];
                    $_SESSION['mla_name'] = $mla['name'];
                    $_SESSION['mla_email'] = $mla['email'];
                    $_SESSION['last_activity'] = time();
                    
                    error_log("MLA Login: Session set - " . json_encode($_SESSION));
                    
                    // Check if "Remember Me" is selected
                    if (isset($_POST['remember_me']) && $_POST['remember_me'] === 'on') {
                        $selector = bin2hex(random_bytes(16));
                        $validator = bin2hex(random_bytes(32));
                        $validator_hash = hash('sha256', $validator);
                        $expires_at = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 days
                        
                        // Store the new token in the database
                        $stmt = $conn->prepare(
                            "INSERT INTO mla_auth_tokens (mla_id, selector, validator_hash, expires_at, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)"
                        );
                        $ip_address = getUserIP();
                        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                        $stmt->bind_param("ssssss", $mla['username'], $selector, $validator_hash, $expires_at, $ip_address, $user_agent);
                        $stmt->execute();

                        // Set the cookie on the user's browser
                        $cookie_value = $selector . ':' . $validator;
                        setcookie("mla_token", $cookie_value, [
                            'expires' => time() + (86400 * 30),
                            'path' => '/',
                            'secure' => false, // Changed to false for localhost
                            'httponly' => true,
                            'samesite' => 'Lax', // Changed to Lax for localhost
                        ]);
                    }
                    
                    error_log("MLA Login: Redirecting to dashboard");
                    
                    header("Location: ../mla/dashboard.php");
                    exit();
                } else {
                    $errorMsg = "Invalid MLA ID or password.";
                    error_log("MLA Login: Password incorrect");
                }
            } else {
                $errorMsg = "Invalid MLA ID or password.";
                error_log("MLA Login: User not found");
            }
        } catch (Exception $e) {
            error_log("MLA login error: " . $e->getMessage());
            $errorMsg = "An error occurred during login. Please try again.";
        }
    }
}

// Include header after processing
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
      <h2>MLA Login</h2>

      <!-- Debug info -->
      <?php if ($_SERVER['REQUEST_METHOD'] === "POST"): ?>
        <div class="alert alert-info">
          Form submitted! Processing login...
        </div>
      <?php endif; ?>

      <form method="post" action="" id="loginForm">
        <div class="mb-3">
          <label for="mlaId" class="form-label">MLA ID</label>
          <input type="text" class="form-control" id="mlaId" name="mlaId" maxlength="50" placeholder="mlademo" value="<?= htmlspecialchars($_POST['mlaId'] ?? '') ?>" required />
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
          <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
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
$(document).ready(()=>{
$('#togglePassword').on('click', () => {
    const passwordInput = $('#password');
    const icon = $('#passwordIcon');
    const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
    passwordInput.attr('type', type);
    icon.toggleClass('fa-eye fa-eye-slash');
});

// Add form submission debug
$('#loginForm').on('submit', function() {
    console.log('Form submitted!');
    console.log('Username:', $('#mlaId').val());
    console.log('Password:', $('#password').val());
});
});
</script>

</body>
</html> 