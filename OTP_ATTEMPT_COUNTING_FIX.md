# OTP Attempt Counting and Field Clearing Fixes

## Issues Identified and Fixed

### 1. **Double Counting Attempts (CRITICAL)**

**Problem:** The system was counting attempts twice:
- Sending OTP = 1 attempt
- Verifying OTP = 2nd attempt (even if correct)

This meant a successful login counted as 2 attempts instead of 1.

**Root Cause:** Both `send-otp.php` and `verify-otp.php` were incrementing attempt counts.

**Solution Applied:**
- **Removed attempt counting from `send-otp.php`** - Only count when actually trying to verify
- **Removed attempt counting from `resend-otp.php`** - Resending OTP shouldn't count as an attempt
- **Only count attempts in `verify-otp.php`** - Only when user actually tries to verify OTP

### 2. **OTP Fields Not Clearing on Failed Verification**

**Problem:** When OTP verification failed, the input fields remained filled, requiring manual clearing.

**Solution Applied:**
- **Added `clearOtpFields()` function** in JavaScript
- **Auto-clear fields on failed verification**
- **Auto-clear fields when new OTP is sent**

## Files Modified

### 1. **otp/send-otp.php**
**Before:**
```php
// Now handle login attempts tracking after OTP is successfully stored
if ($attempt) {
    $new_count = $attempt['attempt_count'] + 1;
    // ... attempt counting logic
}
```

**After:**
```php
// Don't increment attempt count here - only count attempts when verifying OTP
// This prevents double counting (send OTP = 1 attempt, verify OTP = 2 attempts)
```

### 2. **otp/verify-otp.php**
**Enhanced with proper lockout logic:**
```php
// Check if we should lock the account
if ($new_count >= 5) { // MAX_ATTEMPTS
    $lock_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    $update = $conn->prepare("UPDATE user_login_attempts SET attempt_count = ?, is_locked = 1, lock_expiry = ?, last_attempt = NOW() WHERE id = ?");
    $update->bind_param("isi", $new_count, $lock_expiry, $attempt['id']);
    $update->execute();
    echo json_encode(['status' => 'error', 'message' => 'Too many failed attempts. Account locked for 10 minutes.']);
} else {
    // Normal attempt increment
}
```

### 3. **otp/resend-otp.php**
**Removed attempt counting:**
```php
// Don't increment attempt count for resend - only count verification attempts
// This prevents double counting and allows users to resend OTP without penalty
```

### 4. **assets/js/otp.js**
**Added field clearing functionality:**
```javascript
// Function to clear all OTP input fields
function clearOtpFields() {
  const otpFields = document.querySelectorAll(".otp-input");
  otpFields.forEach(field => {
    field.value = "";
  });
  // Focus on first field after clearing
  if (otpFields.length > 0) {
    otpFields[0].focus();
  }
}
```

**Auto-clear on failed verification:**
```javascript
} else {
  showToastr("error", data.message || "OTP verification failed.")
  // Clear OTP fields on failed verification
  clearOtpFields();
  // ... rest of error handling
}
```

**Auto-clear on new OTP sent:**
```javascript
if (data.status === 'success') {
  showToastr("success", "A new OTP has been sent!");
  // Clear OTP fields when new OTP is sent
  clearOtpFields();
  // ... rest of success handling
}
```

## New OTP Flow

### **Before (Broken):**
1. User enters phone → Send OTP → **Attempt count: 1**
2. User enters OTP → Verify OTP → **Attempt count: 2** ❌
3. User fails verification → Fields remain filled ❌
4. User resends OTP → **Attempt count: 3** ❌

### **After (Fixed):**
1. User enters phone → Send OTP → **Attempt count: 0** ✅
2. User enters OTP → Verify OTP → **Attempt count: 1** ✅
3. User fails verification → **Fields auto-clear** ✅
4. User resends OTP → **Attempt count: 1** (unchanged) ✅

## Benefits

✅ **Accurate attempt counting** - Only counts actual verification attempts  
✅ **Better user experience** - Fields auto-clear on failure  
✅ **Fair lockout system** - Users can resend OTP without penalty  
✅ **Consistent behavior** - Same attempt count for send + verify  
✅ **Improved security** - Proper lockout after 5 failed verifications  

## Testing Scenarios

### **Scenario 1: Successful Login**
1. Send OTP → Attempt count: 0
2. Verify correct OTP → Attempt count: 0 (reset on success)
3. **Result:** ✅ Success, no attempts counted

### **Scenario 2: Failed Verification**
1. Send OTP → Attempt count: 0
2. Verify wrong OTP → Attempt count: 1
3. **Result:** ✅ Fields cleared, attempt counted

### **Scenario 3: Multiple Failures**
1. Send OTP → Attempt count: 0
2. Verify wrong OTP → Attempt count: 1
3. Send new OTP → Attempt count: 1 (unchanged)
4. Verify wrong OTP → Attempt count: 2
5. Repeat until 5 attempts → Account locked
6. **Result:** ✅ Proper lockout after 5 failed verifications

### **Scenario 4: Resend After Failure**
1. Send OTP → Attempt count: 0
2. Verify wrong OTP → Attempt count: 1
3. Resend OTP → Attempt count: 1 (unchanged)
4. **Result:** ✅ Resend doesn't count as attempt

## Security Features

- **5 failed verifications** = Account locked for 10 minutes
- **Attempts reset** on successful login
- **IP-based tracking** for additional security
- **Lockout bypass prevention** during lockout period 