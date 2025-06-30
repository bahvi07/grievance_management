<?php
// MLA Initialization
// This file handles MLA authentication and database connection
require_once __DIR__ . '/../config/session-config.php';
startSecureSession();
// Include database configuration
require_once __DIR__ . '/../config/config.php';

// Check if MLA is logged in
function check_mla_auth() {
    // Validate session first
    if (!validateSession()) {
        session_unset();
        session_destroy();
        header('Location: ../auth/mla-login.php');
        exit();
    }
    
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