<?php
require __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env file if it exists
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} else {
    // Set default values if .env file doesn't exist
    $_ENV['DB_HOST'] = 'localhost';
    $_ENV['DB_USERNAME'] = 'root';
    $_ENV['DB_PASSWORD'] = '';
    $_ENV['DB_DATABASE'] = 'cms';
    $_ENV['DB_PORT'] = '3306';
}

// Include CSRF protection
require_once __DIR__ . '/csrf.php';

// Set the default timezone for the application
date_default_timezone_set('Asia/Kolkata');

// Define base URL for assets (only if not already defined)
if (!defined('BASE_URL')) {
    define('BASE_URL', '/grievance_management/');
}

// Define a secure log file path (only if not already defined)
if (!defined('LOG_FILE')) {
    define('LOG_FILE', __DIR__ . '/../logs/error.log');
}

// Database configuration
$db_host = $_ENV['DB_HOST'];
$db_username = $_ENV['DB_USERNAME'];
$db_password = $_ENV['DB_PASSWORD'];
$db_name = $_ENV['DB_DATABASE'];
$db_port = $_ENV['DB_PORT'];

// Create connection
$conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);

// Check connection - log error instead of dying
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    // Don't die here, let the calling script handle the error
}

// Set charset to utf8 if connection is successful
if (!$conn->connect_error) {
    $conn->set_charset("utf8");
}
?>