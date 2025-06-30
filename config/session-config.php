<?php
// Secure session configuration functions
// Start session with secure parameters
function startSecureSession()
{
    // Check if session is already active
    if (session_status() === PHP_SESSION_ACTIVE) {
        // Session is already active, just set security headers
        header('X-Frame-Options: DENY'); //Prevent clickjacking (X-Frame), Prevent to embbeded in iframe 
        header('X-XSS-Protection: 1; mode=block'); //Enable XSS filter in browsers
        header('X-Content-Type-Options: nosniff'); //Disallow MIME-type sniffing
        header('Referrer-Policy: strict-origin-when-cross-origin'); //Restrict referrer info leakage,Prevents leaking full URLs to third-party sites

        // Updated Content Security Policy to allow necessary external resources
        header(
            "Content-Security-Policy: " .   //main defense against XSS and injection attacks
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' " .
                "https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com https://cdn.datatables.net; " .
                "style-src 'self' 'unsafe-inline' " .
                "https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com https://cdn.datatables.net; " .
                "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; " .
                "img-src 'self' data: https:; " .
                "connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.datatables.net;"
        );
        return;
    }

    // Configure session settings BEFORE starting the session
    ini_set('session.cookie_httponly', 1); //Prevents JavaScript from accessing session cookies (document.cookie).
    // Only set secure cookie if using HTTPS
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1); //Ensures cookies are sent over HTTPS only
    }

    // Set SameSite to Lax for localhost, Strict for production
    if (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1')) {
        ini_set('session.cookie_samesite', 'Lax'); //More permissive for localhost
    } else {
        ini_set('session.cookie_samesite', 'Strict'); //Prevents cookies from being sent on cross-site requests (CSRF protection).
    }

    ini_set('session.use_only_cookies', 1); //Disables session ID in URLs. Only allows session via cookies.
    ini_set('session.cookie_lifetime', 86400); // Session cookie expires in 24 hours instead of when browser closes
    ini_set('session.gc_maxlifetime', 86400); // 24 hours instead of 30 minutes

    // Set secure session name
    session_name('SECURE_SESSION');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    } //Starts session only if not already started.

    // Regenerate session ID periodically to prevent session fixation
    if (
        !isset($_SESSION['last_regeneration']) ||
        time() - $_SESSION['last_regeneration'] > 300
    ) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }

    // Set security headers
    header('X-Frame-Options: DENY'); //Prevent clickjacking (X-Frame)
    header('X-XSS-Protection: 1; mode=block'); //Enable XSS filter in browsers
    header('X-Content-Type-Options: nosniff'); //Disallow MIME-type sniffing
    header('Referrer-Policy: strict-origin-when-cross-origin'); //Restrict referrer info leakage

    // Updated Content Security Policy to allow necessary external resources
    //Controls what external resources (CDNs, fonts, scripts) are allowed. Helps prevent:
    header(
        "Content-Security-Policy: " .
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
function validateSession()
{
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
    }

    $timeout = 86400; // 24 hours instead of 30 minutes

    if (time() - $_SESSION['last_activity'] > $timeout) {
        session_unset();
        session_destroy();
        return false;
    }

    $_SESSION['last_activity'] = time();
    return true;
}
