<?php
require __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Include CSRF protection
require_once __DIR__ . '/csrf.php';

// Set the default timezone for the application
date_default_timezone_set('Asia/Kolkata');

// Define a secure log file path
define('LOG_FILE', __DIR__ . '/../logs/error.log');

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