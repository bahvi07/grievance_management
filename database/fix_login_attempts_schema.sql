-- Fix user_login_attempts table schema for existing databases
-- This script should be run if the table already exists with the wrong structure

-- Drop existing table if it has the wrong structure
DROP TABLE IF EXISTS user_login_attempts;

-- Create the correct table structure
CREATE TABLE user_login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(15) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempt_count INT NOT NULL DEFAULT 1,
    is_locked TINYINT(1) DEFAULT 0,
    lock_expiry TIMESTAMP NULL,
    last_attempt TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_phone (phone),
    INDEX idx_ip_address (ip_address)
);

-- Create admin login_attempts table if it doesn't exist
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempt_count INT NOT NULL DEFAULT 1,
    is_locked TINYINT(1) DEFAULT 0,
    lock_expiry TIMESTAMP NULL,
    last_attempt TIMESTAMP NULL,
    user_agent TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_admin_id (admin_id),
    INDEX idx_ip_address (ip_address)
);

-- Add missing columns to otp_requests table if they don't exist
ALTER TABLE otp_requests 
ADD COLUMN IF NOT EXISTS user_token VARCHAR(32) AFTER is_logged_in,
ADD COLUMN IF NOT EXISTS user_name VARCHAR(50) AFTER user_token; 