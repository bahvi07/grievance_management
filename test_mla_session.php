<?php
// Test MLA session and dashboard access
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>MLA Session Test</h2>";

// Test 1: Basic session
echo "<h3>Test 1: Basic Session</h3>";
session_start();
echo "Session ID: " . session_id() . "<br>";
echo "Session status: " . session_status() . "<br>";

// Test 2: Set session data
echo "<h3>Test 2: Set Session Data</h3>";
$_SESSION['mla_id'] = 'mlademo';
$_SESSION['mla_name'] = 'MLA Demo';
$_SESSION['mla_email'] = 'mla@example.com';
$_SESSION['last_activity'] = time();

echo "Session data set: " . json_encode($_SESSION) . "<br>";

// Test 3: Check if dashboard file exists
echo "<h3>Test 3: Check Dashboard File</h3>";
$dashboardFile = 'mla/dashboard.php';
if (file_exists($dashboardFile)) {
    echo "✅ Dashboard file exists: $dashboardFile<br>";
} else {
    echo "❌ Dashboard file missing: $dashboardFile<br>";
}

// Test 4: Check if mla-init.php exists
echo "<h3>Test 4: Check MLA Init File</h3>";
$initFile = 'includes/mla-init.php';
if (file_exists($initFile)) {
    echo "✅ MLA init file exists: $initFile<br>";
} else {
    echo "❌ MLA init file missing: $initFile<br>";
}

// Test 5: Test database connection
echo "<h3>Test 5: Database Connection</h3>";
require_once 'config/config.php';
if ($conn) {
    echo "✅ Database connection successful<br>";
    
    // Test user query
    $stmt = $conn->prepare("SELECT * FROM mla_users WHERE username=?");
    $username = 'mlademo';
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "✅ User found: " . $user['name'] . "<br>";
    } else {
        echo "❌ User not found<br>";
    }
} else {
    echo "❌ Database connection failed<br>";
}

// Test 6: Test redirect
echo "<h3>Test 6: Test Redirect</h3>";
echo "Current URL: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "Target URL: mla/dashboard.php<br>";

// Test 7: Check session cookies
echo "<h3>Test 7: Session Cookies</h3>";
echo "Session name: " . session_name() . "<br>";
echo "Session cookie params: " . json_encode(session_get_cookie_params()) . "<br>";

// Test 8: Manual redirect test
echo "<h3>Test 8: Manual Redirect Test</h3>";
echo "<a href='mla/dashboard.php'>Click here to test dashboard access</a><br>";
echo "<a href='auth/mla-login.php'>Click here to go back to login</a><br>";

// Test 9: Check if we can access the dashboard directly
echo "<h3>Test 9: Direct Dashboard Access</h3>";
if (isset($_SESSION['mla_id'])) {
    echo "✅ Session has mla_id: " . $_SESSION['mla_id'] . "<br>";
    echo "✅ Should be able to access dashboard<br>";
} else {
    echo "❌ No mla_id in session<br>";
}
?> 