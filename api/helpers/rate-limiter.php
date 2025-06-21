<?php
// api/helpers/rate-limiter.php

// Rate limiting settings
define('RATE_LIMIT_TIME_WINDOW', 60); // in seconds (1 minute)
define('RATE_LIMIT_MAX_REQUESTS', 60); // max requests per window

function handle_rate_limiting($conn) {
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Check existing record
    $stmt = $conn->prepare("SELECT * FROM api_rate_limits WHERE ip_address = ?");
    $stmt->bind_param('s', $ip_address);
    $stmt->execute();
    $result = $stmt->get_result();
    $record = $result->fetch_assoc();

    if ($record) {
        $last_request_time = strtotime($record['last_request_at']);
        $current_time = time();

        // If the first request was made within the last minute
        if ($current_time - $last_request_time < RATE_LIMIT_TIME_WINDOW) {
            if ($record['request_count'] >= RATE_LIMIT_MAX_REQUESTS) {
                // Block the request
                header("HTTP/1.1 429 Too Many Requests");
                echo json_encode(['error' => 'Too Many Requests. Please try again later.']);
                exit;
            }
            // Increment the request count
            $update_stmt = $conn->prepare("UPDATE api_rate_limits SET request_count = request_count + 1, last_request_at = CURRENT_TIMESTAMP WHERE ip_address = ?");
            $update_stmt->bind_param('s', $ip_address);
            $update_stmt->execute();
        } else {
            // Reset the count as the time window has passed
            $reset_stmt = $conn->prepare("UPDATE api_rate_limits SET request_count = 1, last_request_at = CURRENT_TIMESTAMP WHERE ip_address = ?");
            $reset_stmt->bind_param('s', $ip_address);
            $reset_stmt->execute();
        }
    } else {
        // Insert new record for this IP
        $insert_stmt = $conn->prepare("INSERT INTO api_rate_limits (ip_address) VALUES (?)");
        $insert_stmt->bind_param('s', $ip_address);
        $insert_stmt->execute();
    }
} 