<?php
// Simple API landing page for deployment
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complaint Management API</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #FF4500; }
        .endpoint { background: #f8f9fa; padding: 10px; margin: 10px 0; border-left: 4px solid #FF4500; }
        .method { font-weight: bold; color: #28a745; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Complaint Management API</h1>
        <p>API is running successfully!</p>
        
        <h2>📋 Available Endpoints:</h2>
        
        <div class="endpoint">
            <span class="method">POST</span> /api/auth/login.php - Send OTP
        </div>
        
        <div class="endpoint">
            <span class="method">POST</span> /api/auth/verify.php - Verify OTP & Get Token
        </div>
        
        <div class="endpoint">
            <span class="method">POST</span> /api/complaints/submit.php - Submit Complaint
        </div>
        
        <div class="endpoint">
            <span class="method">GET</span> /api/complaints/list.php - Get All Complaints
        </div>
        
        <div class="endpoint">
            <span class="method">GET</span> /api/complaints/view.php?refId=123456 - Get Single Complaint
        </div>
        
        <h2>🌐 Web Application:</h2>
        <p><a href="index.php">Access Web Application</a></p>
        
        <h2>📊 Server Info:</h2>
        <p>PHP Version: <?php echo phpversion(); ?></p>
        <p>Server Time: <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
</body>
</html>