<?php
include '../includes/admin-init.php';
include '../includes/admin-header.php';
include '../includes/admin-nav.php';
?>

<style>
/* Star Rating Display Styles */
.star-rating {
    display: inline-flex;
    gap: 2px;
}

.star-rating .star {
    font-size: 1.2rem;
    color: #ddd;
}

.star-rating .star.filled {
    color: #ffc107;
}

/* DataTables Styling */
.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.375rem 0.75rem;
    margin-left: 2px;
    border: 1px solid #dee2e6;
    background-color: #fff;
    color: #007bff !important;
    cursor: pointer;
    border-radius: 0.25rem;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background-color: #e9ecef;
    border-color: #dee2e6;
    color: #0056b3 !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background-color: #007bff;
    border-color: #007bff;
    color: #fff !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
    color: #6c757d !important;
    cursor: not-allowed;
    background-color: #fff;
    border-color: #dee2e6;
}

.dataTables_wrapper .dataTables_length select,
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    padding: 0.375rem 0.75rem;
}

.dataTables_wrapper .dataTables_info {
    padding-top: 0.5rem;
}
</style>

</head>

<body class='custom-body'>
    <div class="top-head bg-light">
        <div class="brand">
            <img src="<?php echo BASE_URL; ?>assets/images/general_images/Bjplogo.jpg" alt="Logo">
            Feedback Panel (Last 7 Days)
        </div>
        <div class="">
            Admin, <?= $_SESSION['admin_name'] ?? 'Admin' ?>
        </div>
    </div>
    
    <div class="container" style="margin-top: 100px; margin-right: 0; margin-left: 255px; padding-bottom: 85px;">
        
        <div class="table-responsive p-3" style="width:90%;">
            <table id="feedbackTable" class="table table-hover table-borderless feedback-table">
                <thead class="table-dark">
                    <tr>
                        <th>Sr No.</th>
                        <th>User Name</th>
                        <th>Phone</th>
                        <th>Rating</th>
                        <th>Feedback</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Show only last 7 days of feedback
                    $result = $conn->query("SELECT * FROM feedback WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY created_at DESC");
                    $i = 1;
                    while ($row = $result->fetch_assoc()) {
                        // Generate star rating HTML
                        $rating = $row['rating'] ?? 5;
                        $starsHtml = '';
                        for ($star = 1; $star <= 5; $star++) {
                            $starClass = $star <= $rating ? 'filled' : '';
                            $starsHtml .= "<i class='fas fa-star star $starClass'></i>";
                        }
                        
                        echo "<tr>
                            <td>{$i}</td>
                            <td>" . htmlspecialchars($row['user_name']) . "</td>
                            <td class='phone-col'>" . htmlspecialchars($row['user_phone']) . "</td>
                            <td>
                                <div class='star-rating' title='Rating: $rating/5'>
                                    $starsHtml
                                </div>
                            </td>
                            <td class='feedback-col'>" . nl2br(htmlspecialchars($row['feedback'])) . "</td>
                            <td>" . date('d M Y, h:i A', strtotime($row['created_at'])) . "</td>
                        </tr>";
                        $i++;
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php
    include '../includes/admin-footer.php';
    ?>
    
<script>
document.addEventListener("DOMContentLoaded", function() {
    $('#feedbackTable').DataTable({
        "order": [[ 5, "desc" ]], // Date column (6th column)
        "pageLength": 10,
        "language": {
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        }
    });
});
</script>