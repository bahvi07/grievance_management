<?php
require_once '../config/session-config.php';
startSecureSession();
session_unset();
session_destroy();

header("Location:admin-login.php");
exit;
