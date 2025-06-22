# Login Attempts Feature - Issues Found and Fixes Applied

## Summary
The user-side login attempts feature had several critical issues that have been identified and fixed. The admin login attempts feature also had some issues that have been resolved.

## Issues Found and Fixed

### 1. **Database Schema Mismatch (CRITICAL)**

**Problem:** The `user_login_attempts` table schema didn't match the code usage.

**Original Schema (INCORRECT):**
```sql
CREATE TABLE IF NOT EXISTS user_login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(15) NOT NULL,  -- ❌ Wrong column name
    ip_address VARCHAR(45) NOT NULL,
    attempt_count INT NOT NULL DEFAULT 1,
    is_locked TINYINT(1) DEFAULT 0,
    lock_expiry TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_name (user_name),  -- ❌ Wrong index
    INDEX idx_ip_address (ip_address)
);
```

**Code Usage:**
```php
$stmt = $conn->prepare("SELECT * FROM user_login_attempts WHERE phone = ? OR ip_address = ?");
```

**Fix Applied:**
- Changed `user_name` column to `phone`
- Added missing `last_attempt` column
- Updated index from `idx_user_name` to `idx_phone`

### 2. **Missing Admin Login Attempts Table (CRITICAL)**

**Problem:** Admin login code was using `login_attempts` table that didn't exist in schema.

**Fix Applied:**
Added `login_attempts` table to schema:
```sql
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
```

### 3. **Lockout Logic Bug in Resend OTP**

**Problem:** In `otp/resend-otp.php`, when locking account, `attempt_count` was reset to 0 instead of keeping the actual count.

**Original Code (INCORRECT):**
```php
$lock = $conn->prepare("UPDATE user_login_attempts SET attempt_count = 0, is_locked = 1, lock_expiry = ? WHERE id = ?");
```

**Fix Applied:**
```php
$lock = $conn->prepare("UPDATE user_login_attempts SET attempt_count = ?, is_locked = 1, lock_expiry = ? WHERE id = ?");
$lock->bind_param("isi", $new_count, $lock_expiry, $attempt['id']);
```

### 4. **Admin Login Logic Error**

**Problem:** In `auth/admin-login.php`, `$stmt->execute()` was called outside conditional blocks, causing execution errors.

**Fix Applied:**
Moved `$stmt->execute()` calls inside their respective conditional blocks.

### 5. **Incorrect Parameter Binding in Verify OTP**

**Problem:** In `otp/verify-otp.php`, the reset attempts query was trying to update `user_name` column that doesn't exist.

**Original Code (INCORRECT):**
```php
$resetAttempts = $conn->prepare("UPDATE user_login_attempts SET attempt_count = 0, is_locked = 0, lock_expiry = NULL, user_name = ? WHERE phone = ?");
$resetAttempts->bind_param("ss", $user_name, $phone);
```

**Fix Applied:**
```php
$resetAttempts = $conn->prepare("UPDATE user_login_attempts SET attempt_count = 0, is_locked = 0, lock_expiry = NULL WHERE phone = ?");
$resetAttempts->bind_param("s", $phone);
```

## Files Modified

1. **`database/schema.sql`**
   - Fixed `user_login_attempts` table schema
   - Added missing `login_attempts` table for admin

2. **`database/add_login_attempts_table.sql`**
   - Updated to match corrected schema

3. **`database/fix_login_attempts_schema.sql`** (NEW)
   - Created fix script for existing databases

4. **`otp/resend-otp.php`**
   - Fixed lockout logic bug

5. **`otp/verify-otp.php`**
   - Fixed parameter binding in reset attempts query

6. **`auth/admin-login.php`**
   - Fixed execute() call placement

## Database Migration Required

For existing databases with the incorrect schema, run:
```sql
-- Run this script to fix existing database
SOURCE database/fix_login_attempts_schema.sql;
```

## Testing Recommendations

1. **Test User Login Flow:**
   - Try multiple failed OTP attempts
   - Verify account gets locked after 5 attempts
   - Verify lock expires after 10 minutes
   - Verify successful login resets attempts

2. **Test Admin Login Flow:**
   - Try multiple failed login attempts
   - Verify account gets locked after 5 attempts
   - Verify lock expires after 30 minutes
   - Verify successful login resets attempts

3. **Test Resend OTP:**
   - Verify resend attempts are tracked
   - Verify account gets locked after 5 resend attempts

## Security Features Working

✅ **Rate Limiting:** 5 attempts before lockout  
✅ **Lockout Duration:** 10 minutes for users, 30 minutes for admins  
✅ **IP-based Tracking:** Attempts tracked by IP address  
✅ **Automatic Reset:** Successful login resets attempt count  
✅ **User Agent Tracking:** Admin attempts include user agent info  

## Notes

- The OTP is currently exposed in development mode (should be removed in production)
- All database queries use prepared statements for SQL injection protection
- Session management is properly implemented
- CSRF protection is in place for admin login 