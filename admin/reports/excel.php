<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include '../../config/config.php';

$allowedTypes = ['complaints', 'resolved', 'pending', 'rejected', 'forwarded', 'departments','forwarded_complaints'];
$type = $_GET['type'] ?? $_POST['type'] ?? 'complaints';
$filter = $_GET['filter'] ?? $_POST['complaint_status'] ?? 'all';
$tp = $_POST['complaint_status'] ?? $_GET['complaint_status'] ?? '';
if (!in_array($type, $allowedTypes)) {
    die('Invalid type parameter.');
}
if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $filter)) {
    die('Invalid filter parameter.');
}

header('Content-Type: text/csv');

$output = fopen('php://output', 'w');

if ($type === 'departments'||$tp==='departments') {
    error_log('DEBUG: Downloading departments. Filter: ' . $filter);
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
    if ($result && $result->num_rows > 0) {
        error_log('DEBUG: Found ' . $result->num_rows . ' department rows.');
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $id++, $row['name'] ?? 'N/A', $row['category'] ?? 'N/A', $row['email'] ?? 'N/A',
                $row['phone'] ?? 'N/A', $row['area'] ?? 'N/A', $row['created_at'] ?? '', $row['updated_at'] ?? ''
            ]);
        }
    } else {
        error_log('DEBUG: No department data found for filter: ' . $filter);
    }
    $stmt->close();

}elseif($tp==='forwarded_complaints'){
    header('Content-Disposition: attachment; filename="forwarded_complaints_' . date('Y-m-d_H-i-s') . '.csv"');
    // Write headers
    fputcsv($output, ['ID','Ref Id','Department Name', 'Dept Category', 'Complaint', 'User Name', 'User Location', 'Forwarded At', 'Status','Department Phone']);
    $stmt = $conn->prepare("SELECT * FROM complaint_forwarded");
    $stmt->execute();
    $result = $stmt->get_result();
    $id = 1;
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $id++,
                $row['complaint_ref_id'] ?? 'N/A',
                $row['dept_name'] ?? 'N/A',
                $row['dept_category'] ?? 'N/A',
                $row['complaint'] ?? 'N/A',
                $row['user_name'] ?? 'N/A',
                $row['user_location'] ?? '',
                $row['forwarded_at'] ?? '',
                $row['status'] ?? '',
                $row['dept_phone'] ?? '',
            ]);
        }
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
