-- MLA Login Attempts Table
CREATE TABLE IF NOT EXISTS `mla_login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mla_id` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_count` int(11) DEFAULT 1,
  `is_locked` tinyint(1) DEFAULT 0,
  `lock_expiry` datetime DEFAULT NULL,
  `last_attempt` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_agent` text,
  PRIMARY KEY (`id`),
  KEY `mla_id_ip` (`mla_id`, `ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- MLA Auth Tokens Table (for Remember Me functionality)
CREATE TABLE IF NOT EXISTS `mla_auth_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mla_id` varchar(50) NOT NULL,
  `selector` varchar(255) NOT NULL,
  `validator_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `selector` (`selector`),
  KEY `mla_id` (`mla_id`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- MLA Users Table (if not already exists)
CREATE TABLE IF NOT EXISTS `mla_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mla_id` varchar(50) NOT NULL UNIQUE,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mla_id` (`mla_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample MLA user (password: mla123)
INSERT INTO `mla_users` (`mla_id`, `name`, `email`, `password`) VALUES 
('mla123', 'Sample MLA', 'mla@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); 