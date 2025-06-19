<?php
// Secure session configuration
ini_set('session.cookie_httponly', 1);//Prevents JavaScript from accessing session cookies (document.cookie).
ini_set('session.cookie_secure', 1);//Ensures cookies are sent over HTTPS only (💡 has no effect on localhost unless using https://).
ini_set('session.cookie_samesite', 'Strict');//Prevents cookies from being sent on cross-site requests (CSRF protection).
ini_set('session.use_only_cookies', 1);//Disables session ID in URLs. Only allows session via cookies.
ini_set('session.cookie_lifetime', 0); // Session cookie expires when browser closes
ini_set('session.gc_maxlifetime', 1800); // 30 minutes

// Set secure session name
session_name('SECURE_SESSION');

// Start session with secure parameters
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    } //Starts session only if not already started.
    
    // Regenerate session ID periodically to prevent session fixation
    if (!isset($_SESSION['last_regeneration']) || 
        time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    // Set security headers
    header('X-Frame-Options: DENY'); //Prevent clickjacking (X-Frame)
    header('X-XSS-Protection: 1; mode=block');//Enable XSS filter in browsers
    header('X-Content-Type-Options: nosniff');//Disallow MIME-type sniffing
    header('Referrer-Policy: strict-origin-when-cross-origin');//Restrict referrer info leakage
    
    // Updated Content Security Policy to allow necessary external resources
    //Controls what external resources (CDNs, fonts, scripts) are allowed. Helps prevent:
    header("Content-Security-Policy: " .
        "default-src 'self'; " .
        "script-src 'self' 'unsafe-inline' 'unsafe-eval' " .
        "https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com https://cdn.datatables.net; " .
        "style-src 'self' 'unsafe-inline' " .
        "https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com https://cdn.datatables.net; " .
        "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; " .
        "img-src 'self' data: https:; " .
        "connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.datatables.net;"
    );
}

// Function to validate session
function validateSession() {
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
    }
    
    $timeout = 1800; // 30 minutes
    
    if (time() - $_SESSION['last_activity'] > $timeout) {
        session_unset();
        session_destroy();
        return false;
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}
?> 