-- Add missing user_login_attempts table
CREATE TABLE IF NOT EXISTS user_login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(15) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempt_count INT NOT NULL DEFAULT 1,
    is_locked TINYINT(1) DEFAULT 0,
    lock_expiry TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_name (user_name),
    INDEX idx_ip_address (ip_address)
);

-- Add missing columns to otp_requests table if they don't exist
ALTER TABLE otp_requests 
ADD COLUMN IF NOT EXISTS user_token VARCHAR(32) AFTER is_logged_in,
ADD COLUMN IF NOT EXISTS user_name VARCHAR(50) AFTER user_token; 