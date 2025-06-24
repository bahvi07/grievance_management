<?php
require '../../config/config.php';
header('Content-Type: application/json');

// This script retrieves forwarded complaints from the database
try {
    $sql = "SELECT  
                cf.complaint_ref_id,
                cf.dept_name,
                cf.dept_category,
                cf.complaint,
                cf.user_name,
                cf.user_location,
                cf.forwarded_at,
                cf.status
            FROM 
                complaint_forwarded cf
            ORDER BY 
                cf.forwarded_at DESC";

    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('SQL statement preparation failed: ' . $conn->error);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    $stmt->close();
    
    // Datatables expects the data in a "data" object
    echo json_encode(['data' => $data]);

} catch (Exception $e) {
    // Log the error and return an error message
    error_log($e->getMessage());
    echo json_encode(['error' => 'An error occurred while fetching data.']);
}
?> 