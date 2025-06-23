<?php
include '../../config/config.php';

$allowedTypes = ['complaints', 'resolved', 'pending', 'rejected', 'forwarded'];
$type = $_GET['type'] ?? $_POST['type'] ?? 'complaints';
$filter = $_GET['filter'] ?? $_POST['complaint_status'] ?? 'all';
if (!in_array($type, $allowedTypes)) {
    die('Invalid type parameter.');
}
if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $filter)) {
    die('Invalid filter parameter.');
}

header('Content-Type: text/csv');

$output = fopen('php://output', 'w');

if ($type === 'departments') {
    header('Content-Disposition: attachment; filename="departments_' . date('Y-m-d_H-i-s') . '.csv"');

    // Write headers
    fputcsv($output, ['ID', 'Department Name', 'Category', 'Email', 'Phone', 'Area', 'Created At', 'Updated At']);
    $id = 1;

    // Check if $filter is numeric and use prepared statement accordingly
    if (is_numeric($filter)) {
        $stmt = $conn->prepare("SELECT * FROM departments WHERE id = ?");
        $stmt->bind_param('i', $filter); // 'i' is used for integer
    } else {
        $stmt = $conn->prepare("SELECT * FROM departments");
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Export to CSV (or use your output stream)
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $id++,
            $row['name'] ?? 'N/A',
            $row['category'] ?? 'N/A',
            $row['email'] ?? 'N/A',
            $row['phone'] ?? 'N/A',
            $row['area'] ?? 'N/A',
            $row['created_at'] ?? '',
            $row['updated_at'] ?? '',
        ]);
    }
    
    $stmt->close();
    
} elseif ($type === 'complaints') {
    header('Content-Disposition: attachment; filename="complaints_' . $filter . '_' . date('Y-m-d_H-i-s') . '.csv"');

    fputcsv($output, [
        'ID', 'Ref ID', 'Name', 'Father Name', 'Email', 'Phone',
        'Location', 'Complain Category', 'Complaint Description',
        'Status', 'Response', 'Created At', 'Updated At'
    ]);

    $id = 1;

// Check if a specific filter is provided
if ($filter !== 'all') {
    $stmt = $conn->prepare("SELECT * FROM complaints WHERE status = ?");
    $stmt->bind_param('s', $filter);
} else {
    $stmt = $conn->prepare("SELECT * FROM complaints");
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $id++,
        $row['refid'] ?? 'N/A',
        $row['name'] ?? 'N/A',
        $row['father'] ?? 'N/A',
        $row['email'] ?? 'N/A',
        $row['phone'] ?? 'N/A',
        $row['location'] ?? 'N/A',
        $row['category'] ?? 'N/A',
        $row['complaint'] ?? 'N/A',
        $row['status'] ?? 'N/A',
        $row['response'] ?? 'N/A',
        $row['created_at'] ?? '',
        $row['updated_at'] ?? ''
    ]);
}

$stmt->close();

}

fclose($output);
$conn->close();
exit;
?>
