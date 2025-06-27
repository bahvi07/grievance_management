<?php
// Handle complaint rejection before any output
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rejId'])) {
    // Include config first for database connection
    require_once '../config/config.php';
    
    $rej = $_POST['rejId'];
    $stmt = $conn->prepare("UPDATE complaints SET status='reject' WHERE refid=?");
    $stmt->bind_param('i', $rej);
    if ($stmt->execute()) {
        // Redirect to avoid form resubmission on refresh
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

include '../includes/admin-init.php';
include '../includes/admin-header.php';
include '../includes/admin-nav.php';
?>
<style>
    .priority-badge, .priority-select {
        color: #fff !important;
        border-radius: 0.25rem;
        padding: 0.35em 0.65em;
        font-size: .75em;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border: none;
    }
    .priority-select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
    }
    .bg-low { background-color: #0dcaf0 !important; } /* Bootstrap info */
    .bg-medium { background-color: #0d6efd !important; } /* Bootstrap primary */
    .bg-high { background-color: #ffc107 !important; color: #000 !important; } /* Bootstrap warning */
    .bg-urgent { background-color: #dc3545 !important; } /* Bootstrap danger */
</style>
</head>

<body class='custom-body'>
    <div class="top-head bg-light">
        <div class="brand">
            <img src="<?php echo BASE_URL; ?>assets/images/general_images/Bjplogo.jpg" alt="Logo">
            Complaints Panel
        </div>
        <div class="">
            Admin, <?= $_SESSION['admin_name'] ?? 'Admin' ?>
        </div>
    </div>
    <div class="complaint-center row">
        <!-- Display session messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error_message'] ?></div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>



        <!-- Nav Tabs -->
        <ul class="nav nav-tabs mb-4" id="complaintTabs">
            <li class="nav-item">
                <a class="nav-link active" href="#" id="newComp">New Complaints</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" id="rejComp">Rejected</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" id="forwardComp">Forwarded</a>
            </li>
            <button class="btn btn-success w-20" data-bs-toggle="modal" data-bs-target="#downloadModal">
                    <i class="fa fa-download"></i>
                </button>
        </ul>

        <!-- Complaints Table -->
        <div class="table-responsive bg-light rounded shadow-sm ">
            <!-- Pending Complaints Container -->
            <div id="pendingTableContainer" >
                <table id="pendingTable" class="table complaintTable table-hover table-borderless">
                    <thead class="table-dark">
                        <tr>
                            <th>Sr No.</th>
                            <th>Ref ID</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Category</th>
                            <th>Address</th>
                            <th>Details</th>
                            <th>Image</th>
                            <th>Date</th>
                            <th>Priority</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="pending">
                        <?php
                        $stmt = $conn->prepare("SELECT * FROM complaints WHERE status='pending'");
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $id = 1;

                        while ($row = $result->fetch_assoc()) {
                            // Extract and sanitize data
                            $refId = htmlspecialchars($row['refid']);
                            $name = htmlspecialchars($row['name']);
                            $phone = htmlspecialchars($row['phone']);
                            $email = htmlspecialchars($row['email']);
                            $category = htmlspecialchars($row['category']);
                            $location = htmlspecialchars($row['location']);
                            $complaint = htmlspecialchars($row['complaint']);
                            $imagePath = !empty($row['image']) ? htmlspecialchars($row['image']) : '';
                            $createdAt = htmlspecialchars(date('d-m-Y', strtotime($row['created_at'])));
                            $priority = htmlspecialchars($row['priority'] ?? 'medium');
                            
                            // Map priority to a background color class
                            $priorityClass = 'bg-' . strtolower($priority);

                            echo "<tr>
                                <td>" . $id++ . "</td>
                                <td>$refId</td>
                                <td>$name</td>
                                <td>$phone<br>$email</td>
                                <td>$category</td>
                                <td>$location</td>
                                <td>$complaint</td>
                                <td>
                                    <button class='btn btn-warning btn-sm viewImageBtn text-white' 
                                        data-bs-toggle='modal' 
                                        data-bs-target='#showImageModal' 
                                        data-image='$imagePath'>
                                        <i class='fa fa-eye' aria-hidden='true'></i>
                                    </button>
                                </td>
                                <td>$createdAt</td>
                                <td><span class='priority-badge $priorityClass'>" . ucfirst($priority) . "</span></td>
                                <td>
                                <button 
                                    class='btn btn-success btn-sm forwardBtn text-white' 
                                    data-bs-toggle='modal' 
                                    data-bs-target='#forwardModal' 
                                    data-category='$category' 
                                    data-refid='$refId'
                                    data-name='$name' 
                                    data-area='$location'
                                    data-phone='$phone'
                                    data-img='$imagePath'>
                                    <i class='fa fa-share' aria-hidden='true'></i>
                                </button>
                                <form method='POST' style='display: inline;'>
                                <input type='hidden' value='$refId' name='rejId'>
                                    <button type='submit' class='btn btn-danger btn-sm mt-1 reject-complaint-btn'>
                                        <i class='fa fa-times' aria-hidden='true'></i>
                                    </button>
                                </form>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Rejected Complaints Container -->
            <div id="rejectedTableContainer" class="d-none">
                <table id="rejectedTable" class="table complaintTable table-hover table-borderless">
                    <thead class="table-dark">
                        <tr>
                            <th>Sr No.</th>
                            <th>Ref ID</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Category</th>
                            <th>Address</th>
                            <th>Details</th>
                            <th>Image</th>
                            <th>Date</th>
                            <th>Priority</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="">
                        <?php
                        $stmt = $conn->prepare("SELECT * FROM complaints WHERE status='reject'");
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $id = 1;

                        while ($row = $result->fetch_assoc()) {
                            // Extract and sanitize data
                            $refId = htmlspecialchars($row['refid']);
                            $name = htmlspecialchars($row['name']);
                            $phone = htmlspecialchars($row['phone']);
                            $email = htmlspecialchars($row['email']);
                            $category = htmlspecialchars($row['category']);
                            $location = htmlspecialchars($row['location']);
                            $complaint = htmlspecialchars($row['complaint']);
                            $imagePath = !empty($row['image']) ? htmlspecialchars($row['image']) : '';
                            $createdAt = htmlspecialchars(date('d-m-Y', strtotime($row['created_at'])));
                            $priority = htmlspecialchars($row['priority'] ?? 'medium');
                            $priorityClass = 'bg-' . strtolower($priority);

                            echo "<tr>
                                <td>" . $id++ . "</td>
                                <td>$refId</td>
                                <td>$name</td>
                                <td>$phone<br>$email</td>
                                <td>$category</td>
                                <td>$location</td>
                                <td>$complaint</td>
                                <td>
                                    <button class='btn btn-warning btn-sm viewImageBtn text-white' 
                                        data-bs-toggle='modal' 
                                        data-bs-target='#showImageModal' 
                                        data-image='$imagePath'>
                                        <i class='fa fa-eye' aria-hidden='true'></i>
                                    </button>
                                </td>
                                <td>$createdAt</td>
                                <td><span class='priority-badge $priorityClass'>" . ucfirst($priority) . "</span></td>
                                <td>
                                <button 
    class='btn btn-success btn-sm forwardBtn text-white' 
    data-bs-toggle='modal' 
    data-bs-target='#forwardModal' 
    data-category='$category' 
    data-refid='$refId'
    data-name='$name' 
    data-area='$location'
    data-phone='$phone'
    data-img='$imagePath'
    >
    <i class='fa fa-share' aria-hidden='true'></i>
    Resend
</button>
        </td>
    </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Forwarded Complaints Container -->
            <div id="forwardedTableContainer" class="d-none">
                <table id="forwardedTable" class="table table-hover complaintTable table-borderless">
                    <thead class="table-dark">
                        <tr>
                            <!-- <th>Sr No.</th> -->
                            <th>Ref ID</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Category</th>
                            <th>Address</th>
                            <th>Details</th>
                            <th>Image</th>
                            <th>Date</th>
                            <th>Priority</th>
                            <th>Actions</th>    
                        </tr>
                    </thead>
                    <tbody class="">
                        <?php
                        $stmt = $conn->prepare("SELECT * FROM complaints WHERE status='forward'");
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $id = 1;

                        while ($row = $result->fetch_assoc()) {
                            $refId = htmlspecialchars($row['refid']);
                            $name = htmlspecialchars($row['name']);
                            $phone = htmlspecialchars($row['phone']);
                            $email = htmlspecialchars($row['email']);
                            $category = htmlspecialchars($row['category']);
                            $location = htmlspecialchars($row['location']);
                            $complaint = htmlspecialchars($row['complaint']);
                            $imagePath = !empty($row['image']) ? htmlspecialchars($row['image']) : '';
                            $createdAt = htmlspecialchars(date('d-m-Y', strtotime($row['created_at'])));
                            $priority = htmlspecialchars($row['priority'] ?? 'medium');
                            $priorityClass = 'bg-' . strtolower($priority);


                            echo "<tr>
                               
                                <td>$refId</td>
                                <td>$name</td>
                                <td>$phone<br>$email</td>
                                <td>$category</td>
                                <td>$location</td>
                                <td>$complaint</td>
                                <td>
                                    <button class='btn btn-warning btn-sm viewImageBtn text-white' 
                                        data-bs-toggle='modal' 
                                        data-bs-target='#showImageModal' 
                                        data-image='$imagePath'>
                                        <i class='fa fa-eye' aria-hidden='true'></i>
                                    </button>
                                </td>
                                <td>$createdAt</td>
                                <td><span class='priority-badge $priorityClass'>" . ucfirst($priority) . "</span></td>
                                <td>
                                <button 
    class='btn btn-success btn-sm text-white resBt' 
    data-bs-toggle='modal' 
    data-bs-target='#resolveModal' 
    data-resid='{$refId}'
>
    <i class='fa fa-envelope'></i>
</button>
        </td>
    </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    
    <!--Modal for view image-->
    <div class="modal fade" id="showImageModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #FD3A69; color:whitesmoke;">
                    <h5 class="modal-title">Complaint Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="complaintImage" src="" alt="Complaint Image" class="img-fluid rounded" style="max-width: 350px; max-height: 400px;">
                </div>
            </div>
        </div>
    </div>

    <!--Forward Complaint -->
    <div class="modal fade" id="forwardModal" tabindex="-1" aria-labelledby="forwardLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">Forward Complaint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Hidden CSRF token for AJAX requests -->
                    <?= csrf_field() ?>
                    
                    <input type="hidden" id="forward_category">
                    <input type="hidden" id="forward_refid">
                    <input type="hidden" id="forward_name">
                    <input type="hidden" id="forward_location">
                    <input type="hidden" id="forward_phone">
                    <input type="hidden" id="forward_image">
                    <div class="mb-3">
                        <label for="search_area" class="form-label">Search by Area</label>
                        <input type="text" id="search_area" class="form-control" placeholder="Enter area name">
                    </div>

                    <div id="departmentList">
                        <!-- Results appear here -->
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Resolve Complaint Modal -->
    <div class="modal fade" id="resolveModal" tabindex="-1" aria-labelledby="resolveLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form id="resolveForm" method="POST">
                    <?= csrf_field() ?>
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="resolveLabel">Mark Complaint as Resolved</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="resolve_refid" name="r" value="">
                        <div class="mb-3">
                            <label for="resolve_note" class="form-label">Resolution Note (optional)</label>
                            <textarea class="form-control" name="n" id="resolve_note" rows="3" placeholder="Add a note for the user..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="markRes">Mark as Resolved</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

   <!-- Download Modal -->
<div class="modal fade" id="downloadModal" tabindex="-1" aria-labelledby="downloadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="../admin/reports/excel.php" method="POST">
                <!-- Hidden field to specify type -->
                <input type="hidden" name="type" value="complaints">

                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="downloadModalLabel">
                        <i class="fas fa-download me-2"></i>Download Complaints Data
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="complaintStatus" class="form-label fw-semibold">Select Complaint Type</label>
                        <select class="form-select" id="complaintStatus" name="complaint_status" required>
                            <option value="all">All Complaints</option>
                            <option value="pending">New Complaints</option>
                            <option value="reject">Rejected Complaints</option>
                            <option value="forward">Forwarded Complaints</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file-download me-1"></i>Download
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    <?php
    include '../includes/admin-footer.php';
    ?>
    <script src="../assets/js/forward-complaint.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var showImageModal = document.getElementById('showImageModal');
            showImageModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var imageUrl = button.getAttribute('data-image');
                var modalImage = showImageModal.querySelector('#complaintImage');
                modalImage.src = imageUrl;
            });
        });
    </script>
</body>
</html>