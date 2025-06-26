-- Users Table
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(15) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Admins Table
    CREATE TABLE IF NOT EXISTS admin(
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id VARCHAR(50) NOT NULL DEFAULT 'complainAdmin123',
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(15),
        password VARCHAR(255) NOT NULL DEFAULT '$2y$10$kPwaFSzNmd6rTXz27Wb8OeMPoV/9/FkU7Ceu2cdv8E7QBW7eIhEB6',
        status TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (email)
    );

    -- Departments Table
    CREATE TABLE IF NOT EXISTS departments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        category VARCHAR(100),
        email VARCHAR(255),
        phone VARCHAR(20),
        area VARCHAR(255),
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_phone (phone)
    );

    -- Complaints Table
    CREATE TABLE IF NOT EXISTS complaints (
        id INT AUTO_INCREMENT PRIMARY KEY,
        refid INT(10) NOT NULL,
        name VARCHAR(20) NOT NULL,
        father VARCHAR(20) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(15) NOT NULL,
        location TEXT NOT NULL,
        category VARCHAR(30) NOT NULL,
        complaint TEXT NOT NULL,
        image TEXT,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        status ENUM('pending', 'resolve', 'reject', 'forward') DEFAULT 'pending',
        response TEXT DEFAULT 'No Response Recieved Yet',
        UNIQUE KEY (refid)
    );

    -- OTP Requests Table
    CREATE TABLE IF NOT EXISTS otp_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        phone VARCHAR(15) NOT NULL,
        otp VARCHAR(255) NOT NULL, -- Increased length for hash
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME GENERATED ALWAYS AS (DATE_ADD(created_at, INTERVAL 10 MINUTE)) STORED,
        is_used TINYINT(1) DEFAULT 0,
        is_logged_in TINYINT(1) DEFAULT 0,
        user_token VARCHAR(32),
        user_name VARCHAR(50),
        INDEX idx_phone (phone),
        INDEX idx_otp (otp)
    );

    -- User Login Attempts Table
    CREATE TABLE IF NOT EXISTS user_login_attempts (
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

    -- Admin Login Attempts Table
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

    -- Feedback Table
    CREATE TABLE IF NOT EXISTS feedback (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_name VARCHAR(100) NOT NULL,
        user_phone VARCHAR(20),
        feedback TEXT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    );

    /*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
    /*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
    /*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
    /*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
    /*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

    -- API Rate Limiting Table
    CREATE TABLE `api_rate_limits` (
    `ip_address` VARCHAR(45) NOT NULL,
    `request_count` INT NOT NULL DEFAULT 1,
    `last_request_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ip_address`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- End of schema.sql
