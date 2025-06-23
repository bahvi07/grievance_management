-- This script creates a new table for managing admin "remember me" tokens
-- and removes the old single-token column from the `admin` table.

-- Create the new table for storing admin authentication tokens
CREATE TABLE IF NOT EXISTS `admin_auth_tokens` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `admin_id` VARCHAR(50) NOT NULL,
  `selector` VARCHAR(255) NOT NULL,
  `validator_hash` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_used_at` TIMESTAMP NULL,
  `ip_address` VARCHAR(45),
  `user_agent` TEXT,
  INDEX `idx_selector` (`selector`),
  FOREIGN KEY (`admin_id`) REFERENCES `admin`(`admin_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Remove the old admin_token column from the admin table
-- Check if the column exists before trying to drop it to avoid errors.
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'admin'
    AND COLUMN_NAME = 'admin_token'
);

SET @sql = IF(
    @column_exists > 0,
    'ALTER TABLE `admin` DROP COLUMN `admin_token`',
    'SELECT "Column admin_token does not exist."'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- You can now run this SQL on your database to apply the changes.
-- For example, using phpMyAdmin's SQL tab or from the command line:
-- mysql -u your_user -p your_database < database/add_admin_auth_tokens_table.sql 