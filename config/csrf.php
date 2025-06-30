<?php
/**
 * CSRF Protection Utility
 * Provides CSRF token generation, validation, and management
 */
class CSRFProtection { 
    /**
     * Generate a new CSRF token
     * @return string The generated token
     */
    public static function generateToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Get the current CSRF token
     * @return string The current token
     */
    public static function getToken() {
        return self::generateToken();
    }
    
    /**
     * Validate a CSRF token
     * @param string $token The token to validate
     * @param int $maxAge Maximum age of token in seconds (default: 3600 = 1 hour)
     * @return bool True if valid, false otherwise
     */
    public static function validateToken($token, $maxAge = 3600) {
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }
        
        // Check if token has expired
        if (time() - $_SESSION['csrf_token_time'] > $maxAge) {
            self::regenerateToken();
            return false;
        }
        
        // Validate token
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Regenerate CSRF token
     */
    public static function regenerateToken() {
        unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
        self::generateToken();
    }
    
    /**
     * Get CSRF token HTML input field
     * @return string HTML input field with CSRF token
     */
    public static function getTokenField() {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Verify CSRF token from POST request
     * @param int $maxAge Maximum age of token in seconds
     * @return bool True if valid, false otherwise
     */
    public static function verifyPostToken($maxAge = 3600) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }
        
        $token = $_POST['csrf_token'] ?? '';
        return self::validateToken($token, $maxAge);
    }
    
    /**
     * Verify CSRF token from JSON request
     * @param int $maxAge Maximum age of token in seconds
     * @return bool True if valid, false otherwise
     */
    public static function verifyJsonToken($maxAge = 3600) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!$data || !isset($data['csrf_token'])) {
            return false;
        }
        
        return self::validateToken($data['csrf_token'], $maxAge);
    }
    
    /**
     * Require CSRF token validation (throws exception if invalid)
     * @param int $maxAge Maximum age of token in seconds
     * @throws Exception If CSRF token is invalid
     */
    public static function requireValidToken($maxAge = 3600) {
        if (!self::verifyPostToken($maxAge) && !self::verifyJsonToken($maxAge)) {
            throw new Exception('CSRF token validation failed. Please refresh the page and try again.');
        }
    }
    
    /**
     * Get CSRF token for AJAX requests
     * @return array Array with token for JSON response
     */
    public static function getTokenForAjax() {
        return ['csrf_token' => self::getToken()];
    }
}

/**
 * Helper function to get CSRF token field
 * @return string HTML input field with CSRF token
 */
function csrf_field() {
    return CSRFProtection::getTokenField();
}

/**
 * Helper function to get CSRF token value
 * @return string CSRF token value
 */
function csrf_token() {
    return CSRFProtection::getToken();
}
?> 