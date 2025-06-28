<?php
// Script to create sample MLA user
require_once 'config/config.php';

// Check if mla_users table exists, if not create it
$tableCheck = $conn->query("SHOW TABLES LIKE 'mla_users'");
if ($tableCheck->num_rows == 0) {
    // Create mla_users table
    $createTable = "CREATE TABLE IF NOT EXISTS `mla_users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `mla_id` varchar(50) NOT NULL UNIQUE,
        `name` varchar(100) NOT NULL,
        `email` varchar(100) NOT NULL,
        `password` varchar(255) NOT NULL,
        `status` tinyint(1) DEFAULT 1,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `mla_id` (`mla_id`),
        UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($createTable)) {
        echo "✅ mla_users table created successfully<br>";
    } else {
        echo "❌ Error creating mla_users table: " . $conn->error . "<br>";
    }
}

// Check if user already exists
$checkUser = $conn->prepare("SELECT id FROM mla_users WHERE mla_id = ?");
$mlaId = 'mla123';
$checkUser->bind_param("s", $mlaId);
$checkUser->execute();
$result = $checkUser->get_result();

if ($result->num_rows > 0) {
    echo "⚠️ MLA user 'mla123' already exists<br>";
} else {
    // Insert sample MLA user
    $insertUser = $conn->prepare("INSERT INTO mla_users (mla_id, name, email, password) VALUES (?, ?, ?, ?)");
    $mlaId = 'mla123';
    $name = 'Sample MLA';
    $email = 'mla@example.com';
    $password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // password: mla123
    
    $insertUser->bind_param("ssss", $mlaId, $name, $email, $password);
    
    if ($insertUser->execute()) {
        echo "✅ Sample MLA user created successfully!<br>";
        echo "📋 Login credentials:<br>";
        echo "   - MLA ID: <strong>mla123</strong><br>";
        echo "   - Password: <strong>mla123</strong><br>";
        echo "   - Email: <strong>mla@example.com</strong><br>";
    } else {
        echo "❌ Error creating MLA user: " . $insertUser->error . "<br>";
    }
}

// Also create the login_attempts table if it doesn't exist
$attemptsCheck = $conn->query("SHOW TABLES LIKE 'mla_login_attempts'");
if ($attemptsCheck->num_rows == 0) {
    $createAttempts = "CREATE TABLE IF NOT EXISTS `mla_login_attempts` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `mla_id` varchar(50) NOT NULL,
        `ip_address` varchar(45) NOT NULL,
        `attempt_count` int(11) DEFAULT 1,
        `is_locked` tinyint(1) DEFAULT 0,
        `lock_expiry` datetime DEFAULT NULL,
        `last_attempt` datetime DEFAULT CURRENT_TIMESTAMP,
        `user_agent` text,
        PRIMARY KEY (`id`),
        KEY `mla_id_ip` (`mla_id`, `ip_address`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($createAttempts)) {
        echo "✅ mla_login_attempts table created successfully<br>";
    } else {
        echo "❌ Error creating mla_login_attempts table: " . $conn->error . "<br>";
    }
}

// Also create the auth_tokens table if it doesn't exist
$tokensCheck = $conn->query("SHOW TABLES LIKE 'mla_auth_tokens'");
if ($tokensCheck->num_rows == 0) {
    $createTokens = "CREATE TABLE IF NOT EXISTS `mla_auth_tokens` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `mla_id` varchar(50) NOT NULL,
        `selector` varchar(255) NOT NULL,
        `validator_hash` varchar(255) NOT NULL,
        `expires_at` datetime NOT NULL,
        `ip_address` varchar(45) DEFAULT NULL,
        `user_agent` text,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `selector` (`selector`),
        KEY `mla_id` (`mla_id`),
        KEY `expires_at` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($createTokens)) {
        echo "✅ mla_auth_tokens table created successfully<br>";
    } else {
        echo "❌ Error creating mla_auth_tokens table: " . $conn->error . "<br>";
    }
}

echo "<br>🎉 MLA authentication system setup complete!<br>";
echo "You can now login at: <a href='auth/mla-login.php'>MLA Login</a>";
?> 