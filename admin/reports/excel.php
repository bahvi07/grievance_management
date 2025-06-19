<?php
include '../../config/config.php';

$type = $_GET['type'] ?? $_POST['type'] ?? 'complaints';
$filter = $_GET['filter'] ?? $_POST['complaint_status'] ?? 'all';

header('Content-Type: text/csv');

$output = fopen('php://output', 'w');

if ($type === 'departments') {
    header('Content-Disposition: attachment; filename="departments_' . date('Y-m-d_H-i-s') . '.csv"');

    // Write headers
    fputcsv($output, ['ID', 'Department Name', 'Category', 'Email', 'Phone', 'Area', 'Created At', 'Updated At']);

    $sql = "SELECT * FROM departments";
    if (is_numeric($filter)) {
        $sql .= " WHERE id = " . intval($filter);
    }

    $result = mysqli_query($conn, $sql);
    $id = 1;

    while ($row = mysqli_fetch_assoc($result)) {
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

} elseif ($type === 'complaints') {
    header('Content-Disposition: attachment; filename="complaints_' . $filter . '_' . date('Y-m-d_H-i-s') . '.csv"');

    fputcsv($output, [
        'ID', 'Ref ID', 'Name', 'Father Name', 'Email', 'Phone',
        'Location', 'Complain Category', 'Complaint Description',
        'Status', 'Response', 'Created At', 'Updated At'
    ]);

    $sql = "SELECT * FROM complaints";
    if ($filter !== 'all') {
        $filterSafe = $conn->real_escape_string($filter);
        $sql .= " WHERE status = '$filterSafe'";
    }

    $result = mysqli_query($conn, $sql);
    $id = 1;

    while ($row = mysqli_fetch_assoc($result)) {
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
}

fclose($output);
$conn->close();
exit;
?>
