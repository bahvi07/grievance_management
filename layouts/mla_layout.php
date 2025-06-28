<?php
// Include MLA initialization
include '../includes/mla-init.php';

// Check MLA authentication
check_mla_auth();

// Get current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);

// Get pending complaints count for badge
$status = 'pending';
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM complaints WHERE status = ?");
$stmt->bind_param("s", $status);
$stmt->execute();
$result = $stmt->get_result();
$pendingCount = ($result && $row = $result->fetch_assoc()) ? $row['total'] : 0;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MLA Dashboard - Complaint Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- CSRF Token Meta Tag -->
    <?php if (function_exists('csrf_token')): ?>
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <?php endif; ?>

    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative&family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Datatables -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/datatables/datatables.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/datatables/DataTables-1.13.8/css/responsive.bootstrap5.min.css">

    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/table.css">

    <!-- DataTables Custom Styling -->
    <style>
        /* Custom styles for MLA dashboard */
        .mla-content {
            padding: 20px;
        }
        
        /* DataTables positioning and styling */
        .dataTables_wrapper .dataTables_filter {
            float: right !important;
            margin-bottom: 10px;
        }
        
        .dataTables_wrapper .dataTables_paginate {
            float: right !important;
            margin-top: 10px;
        }
        
        .dataTables_wrapper .dataTables_length {
            float: left !important;
        }
        
        .dataTables_wrapper .dataTables_info {
            float: left !important;
            padding-top: 10px;
        }
        
        /* Style pagination buttons */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 8px 12px !important;
            margin-left: 4px !important;
            border: 1px solid #dee2e6 !important;
            background-color: #ffffff !important;
            color: #007bff !important;
            text-decoration: none !important;
            border-radius: 4px !important;
            font-size: 14px !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background-color: #e9ecef !important;
            border-color: #007bff !important;
            color: #0056b3 !important;
            text-decoration: none !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background-color: #007bff !important;
            border-color: #007bff !important;
            color: #ffffff !important;
            text-decoration: none !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            color: #6c757d !important;
            cursor: not-allowed !important;
            background-color: #f8f9fa !important;
            border-color: #dee2e6 !important;
            text-decoration: none !important;
        }
        
        /* Style search input */
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #ced4da !important;
            border-radius: 4px !important;
            padding: 6px 12px !important;
            font-size: 14px !important;
        }
        
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #007bff !important;
            outline: none !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
        }
        
        /* Style length select */
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #ced4da !important;
            border-radius: 4px !important;
            padding: 6px 12px !important;
            font-size: 14px !important;
        }

        /* MLA Sidebar Styles */
        .sidebar {
            height: 100vh;
            width: 245px;
            position: fixed;
            top: 0;
            left: 0;
            background: linear-gradient(#F15922);
            padding-top: 60px;
            z-index: 1040;
            transition: transform 0.3s ease;
        }

        .sidebar .nav-link {
            color: white;
            font-weight: 500;
            padding: 0.85rem 1.25rem;
            display: flex;
            align-items: center;
            transition: background-color 0.3s ease, color 0.3s ease, transform 0.3s ease;
            border-radius: 0 25px 25px 0;
            margin-right: 10px;
        }

        .sidebar .nav-link i {
            margin-right: 0.75rem;
            transition: transform 0.3s ease;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            background-color: white;
            color: black;
            transform: translateX(8px);
            font-weight: bold;
        }

        .sidebar .nav-link.active i {
            transform: rotate(10deg);
            color: #FD3A69;
        }

        .badge-custom {
            background-color: white;
            color: #FD3A69;
            font-size: 0.75rem;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 8px;
            margin-left: 12px;
        }

        .modal-header.bg-warning {
            background: linear-gradient(to bottom, #FF7E5F, #FD3A69);
        }

        .btn-warning.text-white {
            background: linear-gradient(to bottom, #FF7E5F, #FD3A69);
            color: white;
        }
    </style>
</head>

<body>
    <!-- Top Header -->
    <div class="top-head bg-light">
        <div class="brand">
            <img src="<?php echo BASE_URL; ?>assets/images/general_images/Bjplogo.jpg" alt="Logo">
            MLA Dashboard
        </div>
        <div class="">
            Welcome, <?= $_SESSION['mla_name'] ?? 'MLA' ?>
        </div>
    </div>

    <!-- Sidebar Navigation -->
    <div class="sidebar mb-5" id="sidebar">
        <ul class="nav flex-column pt-4">
            <li class="nav-item">
                <a class="nav-link <?= ($currentPage == 'dashboard.php') ? 'active' : '' ?>" href="./dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($currentPage == 'complaints.php') ? 'active' : '' ?>" href="./complaints.php">
                    <i class="fas fa-comment-dots"></i> Complaints
                    <span class="badge-custom"><?= $pendingCount ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($currentPage == 'departments.php') ? 'active' : '' ?>" href="./departments.php">
                    <i class="fas fa-eye"></i> Departments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($currentPage == 'progress.php') ? 'active' : '' ?>" href="./progress.php">
                    <i class="fas fa-chart-line"></i> Admin Progress
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../auth/mla-logout.php?manual=true">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="dashboard-content row">
        <div class="col main-content">
            <?php include $content_file; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-3 mt-3" style="background-color: #fff3ef; font-family: 'Poppins', sans-serif; font-size: 0.9rem; color: #555;">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date("Y"); ?> Vidhayak Sewa Kendra. All rights reserved.</p>
            <small class="text-muted">Powerd By AH&V SOFTWARE PRIVATE LIMITED</small>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- DataTables JS -->
    <script src="<?php echo BASE_URL; ?>assets/datatables/datatables.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/datatables/DataTables-1.13.8/js/responsive.bootstrap5.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom Admin JS -->
    <script src="<?php echo BASE_URL; ?>assets/js/admin-script.js"></script>

    <script>
        // Toastr configuration
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        // Initialize DataTables
        $(document).ready(function() {
            if ($.fn.DataTable) {
                $('.datatable').DataTable({
                    responsive: true,
                    language: {
                        search: "Search:",
                        lengthMenu: "Show _MENU_ entries per page",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "Showing 0 to 0 of 0 entries",
                        infoFiltered: "(filtered from _MAX_ total entries)",
                        paginate: {
                            first: "First",
                            last: "Last",
                            next: "Next",
                            previous: "Previous"
                        }
                    }
                });
            }
        });
    </script>
</body>
</html> 