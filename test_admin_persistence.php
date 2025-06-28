<?php
require_once 'config/session-config.php';
startSecureSession();
require_once 'config/config.php';

echo "<h2>Admin Session & Token Test</h2>";

// Check current session
echo "<h3>Current Session Status:</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "Admin ID in session: " . ($_SESSION['admin_id'] ?? 'NOT SET') . "<br>";
echo "Admin Name in session: " . ($_SESSION['admin_name'] ?? 'NOT SET') . "<br>";
echo "Last Activity: " . ($_SESSION['last_activity'] ?? 'NOT SET') . "<br>";

// Check for remember me token
echo "<h3>Remember Me Token Status:</h3>";
if (isset($_COOKIE['admin_token'])) {
    echo "Admin token cookie exists: " . htmlspecialchars($_COOKIE['admin_token']) . "<br>";
    
    if (strpos($_COOKIE['admin_token'], ':') !== false) {
        list($selector, $validator) = explode(':', $_COOKIE['admin_token'], 2);
        echo "Selector: " . htmlspecialchars($selector) . "<br>";
        echo "Validator: " . htmlspecialchars(substr($validator, 0, 10)) . "...<br>";
        
        // Check if token exists in database
        $stmt = $conn->prepare("SELECT * FROM admin_auth_tokens WHERE selector = ?");
        $stmt->bind_param("s", $selector);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $token_data = $result->fetch_assoc();
            echo "Token found in database<br>";
            echo "Admin ID: " . htmlspecialchars($token_data['admin_id']) . "<br>";
            echo "Expires at: " . htmlspecialchars($token_data['expires_at']) . "<br>";
            echo "Is expired: " . (strtotime($token_data['expires_at']) < time() ? 'YES' : 'NO') . "<br>";
            
            // Validate the token
            if (hash_equals($token_data['validator_hash'], hash('sha256', $validator))) {
                echo "Token validation: VALID<br>";
            } else {
                echo "Token validation: INVALID<br>";
            }
        } else {
            echo "Token NOT found in database<br>";
        }
    } else {
        echo "Invalid token format<br>";
    }
} else {
    echo "No admin token cookie found<br>";
}

// Check all tokens for current admin
if (isset($_SESSION['admin_id'])) {
    echo "<h3>All Tokens for Current Admin:</h3>";
    $stmt = $conn->prepare("SELECT * FROM admin_auth_tokens WHERE admin_id = ?");
    $stmt->bind_param("s", $_SESSION['admin_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Selector</th><th>Expires</th><th>Last Used</th><th>IP</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['selector']) . "</td>";
            echo "<td>" . $row['expires_at'] . "</td>";
            echo "<td>" . $row['last_used_at'] . "</td>";
            echo "<td>" . htmlspecialchars($row['ip_address']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No tokens found for this admin<br>";
    }
}

echo "<br><a href='auth/admin-login.php'>Go to Admin Login</a>";
echo "<br><a href='admin/admin-dashboard.php'>Go to Admin Dashboard</a>";
?> 