<?php 
// Load session configuration before starting session
require_once '../config/session-config.php';
startSecureSession();
require_once '../config/config.php';
require_once '../auth/auth-check.php';
$title = "My Complaints";

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
  echo "  <script>
    alert('NOT LOGGED IN');
    
    </script>";
    header("Location:../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $title; ?> - Vidhayak Seva Kendra</title>
  <?php include '../includes/header.php'; ?>
</head>
<body style="min-height: 100vh; display: flex; flex-direction: column;">
<!-- Top Header Bar -->
<div class="top-header ">
  <div class="brand ">
    <img src="../assets/images/general_images/Bjplogo.jpg" alt="Logo">
    Vidhayak Seva Kendra
  </div>


</div>
  <div class="content container-fluid" style="flex: 1 0 auto;">
  <div class="table-responsive">
    <table class="table table-bordered table-hover" id="myComplainTable">
      <thead class="table-dark">
        <tr>
          <th>Ref ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Complaint</th>
          <th>Category</th>
          <th>Location</th>
          <th>Status</th>
          <th>Response</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $phone = $_SESSION['user_phone'];
        
        // Debug information - remove this after fixing
        echo "<!-- Debug: Session phone = " . htmlspecialchars($phone) . " -->";
        
        $stmt = $conn->prepare("SELECT * FROM complaints WHERE phone=?");
        $stmt->bind_param('s', $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        // Debug: Show query results count
        echo "<!-- Debug: Found " . $result->num_rows . " complaints -->";

        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            $badgeClass = ($row['status'] === 'Resolved') ? 'success' : 'warning';
            echo "<tr>
                    <td>{$row['refid']}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['phone']}</td>
                    <td>{$row['complaint']}</td>
                    <td>{$row['category']}</td>
                    <td>{$row['location']}</td>
                    <td><span class='badge bg-{$badgeClass}'>{$row['status']}</span></td>
                    <td>{$row['response']}</td>
                  </tr>";
          }
        } else {
          echo "<tr><td colspan='9' class='text-center'>No complaints found for your number: " . htmlspecialchars($phone) . "</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
     <div class='card-body text-center '>
  <button  class='btn ' id='return'>🔙 Return to Dashboard</button>
  <?php include '../includes/footer.php'; ?>
</div>
</div>
<script>
    // Return
    document.getElementById('return').addEventListener('click',()=>{
window.location.href='../user/user-dashboard.php';
    });
</script>



