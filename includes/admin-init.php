<?php
// Admin initialization - must be included before any output
require_once __DIR__ . '/../config/session-config.php';
startSecureSession();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/admin-auth-check.php';
?> 