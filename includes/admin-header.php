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
  <link rel="stylesheet" href="https://cdn.datatables.net/2.3.1/css/dataTables.dataTables.min.css">

  <!-- Custom Admin CSS -->
  <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
  <link rel="stylesheet" href="../assets/css/table.css">
</head>
<body>