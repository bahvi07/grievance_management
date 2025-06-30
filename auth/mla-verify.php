<?php
// MLA Verification Page - Manual check
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/session-config.php';
startSecureSession();

require_once '../config/config.php';

echo "<h2>MLA Verification Page</h2>";
echo "<p>This page helps you manually verify MLA login status and test dashboard access.</p>";

// Check current session status
echo "<h3>Current Session Status:</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . session_status() . "<br>";

if (isset($_SESSION['mla_id'])) {
    echo "✅ <strong>LOGGED IN</strong><br>";
    echo "MLA ID: " . $_SESSION['mla_id'] . "<br>";
    echo "MLA Name: " . $_SESSION['mla_name'] . "<br>";
    echo "MLA Email: " . $_SESSION['mla_email'] . "<br>";
    echo "Last Activity: " . date('Y-m-d H:i:s', $_SESSION['last_activity']) . "<br>";
    
    echo "<h3>Actions:</h3>";
    echo "<a href='../mla/dashboard.php' class='btn btn-success'>Go to Dashboard</a><br><br>";
    echo "<a href='mla-logout.php' class='btn btn-danger'>Logout</a><br><br>";
    
} else {
    echo "❌ <strong>NOT LOGGED IN</strong><br>";
    echo "No MLA session found.<br>";
    
    echo "<h3>Actions:</h3>";
    echo "<a href='mla-login.php' class='btn btn-primary'>Go to Login</a><br><br>";
}

// Manual login test
echo "<h3>Manual Login Test:</h3>";
echo "<form method='post' action=''>";
echo "<input type='hidden' name='action' value='manual_login'>";
echo "<label>MLA ID: <input type='text' name='mla_id' value='mlademo'></label><br>";
echo "<label>Password: <input type='password' name='password' value='password123'></label><br>";
echo "<input type='submit' value='Manual Login' class='btn btn-warning'>";
echo "</form>";

// Handle manual login
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['action']) && $_POST['action'] === 'manual_login') {
    echo "<h3>Manual Login Result:</h3>";
    
    $username = $_POST['mla_id'];
    $password = $_POST['password'];
    
    echo "Attempting login with: $username / $password<br>";
    
    $stmt = $conn->prepare("SELECT * FROM mla_users WHERE username=?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "✅ User found: " . $user['name'] . "<br>";
        
        if ($password === $user['password'] || password_verify($password, $user['password'])) {
            echo "✅ Password correct!<br>";
            
            $_SESSION['mla_id'] = $user['username'];
            $_SESSION['mla_name'] = $user['name'];
            $_SESSION['mla_email'] = $user['email'];
            $_SESSION['last_activity'] = time();
            
            echo "✅ Session set!<br>";
            echo "Session data: " . json_encode($_SESSION) . "<br>";
            
            echo "<script>setTimeout(function(){ window.location.reload(); }, 2000);</script>";
            echo "Page will reload in 2 seconds to show updated status...<br>";
            
        } else {
            echo "❌ Password incorrect!<br>";
        }
    } else {
        echo "❌ User not found!<br>";
    }
}

// Test dashboard access
echo "<h3>Dashboard Access Test:</h3>";
if (isset($_SESSION['mla_id'])) {
    echo "<a href='../mla/dashboard.php' target='_blank'>Open Dashboard in New Tab</a><br>";
    echo "<a href='../mla/dashboard.php'>Open Dashboard in Same Tab</a><br>";
} else {
    echo "❌ Must be logged in to access dashboard<br>";
}

// Show all session data
echo "<h3>All Session Data:</h3>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

// Show all cookies
echo "<h3>All Cookies:</h3>";
echo "<pre>" . print_r($_COOKIE, true) . "</pre>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.btn { 
    display: inline-block; 
    padding: 10px 20px; 
    margin: 5px; 
    text-decoration: none; 
    border-radius: 5px; 
    color: white; 
}
.btn-success { background-color: #28a745; }
.btn-danger { background-color: #dc3545; }
.btn-primary { background-color: #007bff; }
.btn-warning { background-color: #ffc107; color: black; }
pre { background-color: #f8f9fa; padding: 10px; border-radius: 5px; }
</style> 