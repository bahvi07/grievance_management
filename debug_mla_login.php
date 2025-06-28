<?php
// Debug script for MLA login
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>MLA Login Debug</h2>";

// Test database connection
require_once 'config/config.php';
echo "✅ Database connection: " . ($conn ? "SUCCESS" : "FAILED") . "<br>";

// Check if mla_users table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'mla_users'");
echo "✅ mla_users table exists: " . ($tableCheck->num_rows > 0 ? "YES" : "NO") . "<br>";

if ($tableCheck->num_rows > 0) {
    // Show table structure
    echo "<h3>Table Structure:</h3>";
    $structure = $conn->query("DESCRIBE mla_users");
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Show all users
    echo "<h3>All Users in mla_users:</h3>";
    $users = $conn->query("SELECT * FROM mla_users");
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Username</th><th>Password</th><th>Status</th></tr>";
    while ($row = $users->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['username'] . "</td>";
        echo "<td>" . substr($row['password'], 0, 20) . "...</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Test login logic
    echo "<h3>Testing Login Logic:</h3>";
    $testUsername = 'mlademo';
    $testPassword = 'password123';
    
    echo "Testing with username: $testUsername<br>";
    echo "Testing with password: $testPassword<br>";
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM mla_users WHERE username=? AND status=1");
    $stmt->bind_param('s', $testUsername);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "User found: " . ($result->num_rows > 0 ? "YES" : "NO") . "<br>";
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "User data: " . json_encode($user) . "<br>";
        
        // Test password
        $passwordMatch = ($testPassword === $user['password']) || password_verify($testPassword, $user['password']);
        echo "Password match: " . ($passwordMatch ? "YES" : "NO") . "<br>";
        
        if ($passwordMatch) {
            echo "✅ Login should work!<br>";
        } else {
            echo "❌ Password doesn't match<br>";
        }
    } else {
        echo "❌ User not found<br>";
    }
}

// Test CSRF
echo "<h3>CSRF Test:</h3>";
if (function_exists('csrf_token')) {
    echo "CSRF function exists: YES<br>";
    echo "CSRF token: " . csrf_token() . "<br>";
} else {
    echo "CSRF function exists: NO<br>";
}

// Test session
echo "<h3>Session Test:</h3>";
session_start();
echo "Session started: " . (session_status() === PHP_SESSION_ACTIVE ? "YES" : "NO") . "<br>";
echo "Session ID: " . session_id() . "<br>";

// Check if mla_login_attempts table exists
$attemptsCheck = $conn->query("SHOW TABLES LIKE 'mla_login_attempts'");
echo "✅ mla_login_attempts table exists: " . ($attemptsCheck->num_rows > 0 ? "YES" : "NO") . "<br>";

// Check if mla_auth_tokens table exists
$tokensCheck = $conn->query("SHOW TABLES LIKE 'mla_auth_tokens'");
echo "✅ mla_auth_tokens table exists: " . ($tokensCheck->num_rows > 0 ? "YES" : "NO") . "<br>";

echo "<br><a href='auth/mla-login.php'>Go to MLA Login</a>";
?> 