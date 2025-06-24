<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - Complaint Management System</title>
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
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

  <!-- Custom Admin CSS -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin-dashboard.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/table.css">

  <!-- DataTables Custom Styling -->
  <style>
    /* Custom styles for admin dashboard */
    .admin-content {
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
  </style>
</head>
<body>