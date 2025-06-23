<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$sql = "SELECT COUNT(*) AS total FROM complaints WHERE status='pending'";
$result = mysqli_query($conn, $sql);
$pendingCount = ($result && $row = mysqli_fetch_assoc($result)) ? $row['total'] : 0;
?>
<style>
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
<div class="sidebar mb-5" id="sidebar">
  <ul class="nav flex-column pt-4">
    <li class="nav-item">
      <a class="nav-link <?= ($currentPage == 'admin-dashboard.php') ? 'active' : '' ?>" href="./admin-dashboard.php">
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
      <a class="nav-link <?= ($currentPage == 'show_departments.php') ? 'active' : '' ?>" href="./show_departments.php">
        <i class="fas fa-eye"></i> Show Department
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= ($currentPage == 'feed.php') ? 'active' : '' ?>" href="./feed.php">
        <i class="fas fa-chart-bar"></i> Feedback
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="../auth/admin-logout.php?manual=true">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </li>
      <li class="nav-item">
      <a class="nav-link <?= ($currentPage == 'admin-edit.php') ? 'active' : '' ?>" href="./admin-edit.php">
        <i class="fas fa-gear"></i> Setting
      </a>
    </li>
  </ul>
</div>
