<?php
session_start();
include '../includes/admin-header.php';
include '../config/config.php';
include '../includes/admin-nav.php';
include '../auth/admin-auth-check.php';
?>
<style>
    .report-center {
            margin-top: 80px;
            margin-right: 0;
            margin-left: 245px;
        }

</style>
</head>

<body class='custom-body'>
    <div class="top-head bg-light">
        <div class="brand">
            <img src="../assets/images/general_images/Bjplogo.jpg" alt="Logo">
            Reports Panel
        </div>
        <div class="">
            Admin, <?= $_SESSION['admin_name'] ?? 'Admin' ?>
        </div>
    </div>
    <div class="report-center row p-2">
  <h3 class="fw-bold" style="color: #F15922;">All Report</h3>
  <p class="text-muted mb-4">Access complaint, scheme, and user reports</p>

  <!-- Filters -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <select class="form-select">
        <option selected>Category</option>
        <option>Water</option>
        <option>Road</option>
      </select>
    </div>
    <div class="col-md-3">
      <select class="form-select">
        <option selected>All</option>
        <option>Resolved</option>
        <option>Unresolved</option>
      </select>
    </div>
    <div class="col-md-3">
      <select class="form-select">
        <option selected>All</option>
        <option>Last 7 days</option>
        <option>Last 30 days</option>
      </select>
    </div>
    <div class="col-md-3 text-end">
      <button class="btn btn-success w-100">Apply</button>
    </div>
  </div>

  <!-- Stats Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="bg-white shadow-sm rounded p-4 text-center">
        <h6 class="text-muted">Total complaints</h6>
        <h3 class="fw-bold text-orange">20</h3>
      </div>
    </div>
    <div class="col-md-4">
      <div class="bg-white shadow-sm rounded p-4 text-center">
        <h6 class="text-muted">Resolved</h6>
        <h3 class="fw-bold text-orange">20%</h3>
      </div>
    </div>
    <div class="col-md-4">
      <div class="bg-white shadow-sm rounded p-4 text-center">
        <h6 class="text-muted">Avg. Response Time</h6>
        <h3 class="fw-bold text-orange">1 Hrs</h3>
      </div>
    </div>
  </div>

  <!-- Chart Section -->
  <h5 class="fw-bold mb-3">Complaints Overview</h5>
  <div class="row g-4">
    <div class="col-md-6">
      <div class="bg-white shadow-sm rounded p-3">
        Resolved vs Unresolved Complaints
        <canvas id="resolvedPie"></canvas>
      </div>
    </div>
    <div class="col-md-6">
      <div class="bg-white shadow-sm rounded p-3">
        Avg. Response Time by Month
        <canvas id="responseBar"></canvas>
      </div>
    </div>
  </div>
</div>

    </div>

    <?php
    include '../includes/admin-footer.php';
    ?>
        <script>
      // Pie Chart
const pieCtx = document.getElementById('resolvedPie');
new Chart(pieCtx, {
  type: 'pie',
  data: {
    labels: ['Resolved', 'Unresolved'],
    datasets: [{
      data: [70, 30],
      backgroundColor: ['#FFCD56', '#FF6384']
    }]
  }
});

// Bar Chart
const barCtx = document.getElementById('responseBar');
new Chart(barCtx, {
  type: 'bar',
  data: {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    datasets: [{
      label: 'Avg. Response Time (hrs)',
      data: [3, 4, 3.5, 3.8, 4.2, 6.5, 2.5, 4.1, 3.9, 3.7, 4, 3.8],
      backgroundColor: function(context) {
        return context.dataIndex === 5 ? '#3B82F6' : '#E5E7EB'; // June highlighted
      }
    }]
  },
  options: {
    plugins: {
      legend: { display: false }
    },
    scales: {
      y: {
        beginAtZero: true
      }
    }
  }
});

    </script>