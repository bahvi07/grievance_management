-- Delete unused MLA login attempts table
-- This table is not used in the current login system

DROP TABLE IF EXISTS `mla_login_attempts`;

-- Keep mla_auth_tokens table (used for Remember Me functionality)
-- Keep mla_users table (used for user accounts)

-- Verify tables after deletion
SHOW TABLES LIKE 'mla_%'; 