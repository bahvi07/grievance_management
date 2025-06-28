<?php
// MLA Initialization
// This file handles MLA authentication and database connection

session_start();

// Include database configuration
require_once __DIR__ . '/../config/config.php';

// Check if MLA is logged in
function check_mla_auth() {
    if (!isset($_SESSION['mla_id']) || !isset($_SESSION['mla_name'])) {
        header('Location: ../auth/mla-login.php');
        exit();
    }
}

// Get current page for navigation highlighting
function get_current_page() {
    $current_file = basename($_SERVER['PHP_SELF'], '.php');
    return $current_file;
}

// Check if MLA has permission for specific action
function mla_can($action) {
    // MLA has read-only access, so only allow 'read' actions
    return $action === 'read';
}

// Include CSRF protection
require_once __DIR__ . '/../config/csrf.php';
?> 