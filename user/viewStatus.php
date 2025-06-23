<?php
// Load session configuration before starting session
require_once '../config/session-config.php';
startSecureSession();
require_once '../config/config.php';
require_once '../auth/auth-check.php';

// Safely get the refid parameter
$ref_id = isset($_GET['refid']) ? $_GET['refid'] : '';
if ($ref_id !== '' && (!ctype_digit($ref_id) || strlen($ref_id) !== 6)) {
    echo json_encode(['success' => false, 'message' => 'Invalid reference ID format.']);
    exit;
}
include '../includes/header.php';
?>
</head>

<body>
    <!-- Top Header Bar -->
    <div class="top-header">
        <div class="brand">
            <img src="../assets/images/general_images/Bjplogo.jpg" alt="Logo" style="height:40px;">
            <span style="font-weight:600; font-size:1.2rem; margin-left:8px;">Vidhayak Seva Kendra</span>
        </div>
    </div>
    <main style="min-height: 100vh; background:whitesmoke; padding-top: 40px;" class="footer-space">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10 col-12">
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        // Verify CSRF token
                        if (!CSRFProtection::verifyPostToken()) {
                            echo "<div class='alert alert-danger text-center rounded-3 mt-5'><i class='fas fa-times-circle me-2'></i>Security validation failed. Please refresh the page and try again.</div>
                              <div class='card-body text-center'>
    <button class='btn btn-outline-danger rounded-pill px-4' id='return' style='font-weight:600;'><i class='fas fa-arrow-left'></i> Return to Dashboard</button>
  </div>";
                        } else {
                            $refId = isset($_POST['refid']) ? trim($_POST['refid']) : '';
                            if (empty($refId) || strlen($refId) !== 6) {
                                echo "<div class='alert alert-danger text-center rounded-3 mt-5'><i class='fas fa-times-circle me-2'></i>Invalid Reference ID. Please enter a 6-digit ID.</div>
                                  <div class='card-body text-center'>
    <button class='btn btn-outline-danger rounded-pill px-4' id='return' style='font-weight:600;'><i class='fas fa-arrow-left'></i> Return to Dashboard</button>
  </div>";
                            } else {
                                $phone=$_SESSION['user_phone'];
                                $stmt = $conn->prepare("SELECT * FROM complaints WHERE refid=? AND phone=?");
                                $stmt->bind_param('ss', $refId,$phone);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows > 0) {
                                    $row = $result->fetch_assoc();
                                    $badgeClass = strtolower($row['status']) === 'resolved' ? 'success' : (strtolower($row['status']) === 'rejected' ? 'danger' : 'warning');
                                    echo "
<div class='card mt-5 shadow-sm border-0 rounded-4'>
  <div class='card-header text-white rounded-top-4' style='background:#F15922; font-weight:600; font-size:1.15rem;'>📄 Complaint Details</div>
  <ul class='list-group list-group-flush'>
    <li class='list-group-item'><strong>📄 Ref ID:</strong> " . htmlspecialchars($row['refid']) . "</li>
    <li class='list-group-item'><strong>🙍 Name:</strong> " . htmlspecialchars($row['name']) . "</li>
    <li class='list-group-item'><strong>📧 Email:</strong> " . htmlspecialchars($row['email']) . "</li>
    <li class='list-group-item'><strong>📞 Phone:</strong> " . htmlspecialchars($row['phone']) . "</li>
    <li class='list-group-item'><strong>📍 Location:</strong> " . htmlspecialchars($row['location']) . "</li>
    <li class='list-group-item'><strong>🏷️ Category:</strong> " . htmlspecialchars($row['category']) . "</li>
    <li class='list-group-item'><strong>📝 Complaint:</strong> " . htmlspecialchars($row['complaint']) . "</li>
    <li class='list-group-item'><strong>📊 Status:</strong> 
      <span class='badge bg-{$badgeClass} px-3 py-2' style='font-size:1rem;'>" . htmlspecialchars($row['status']) . "</span>
    </li>
    <li class='list-group-item'><strong>💬 Response:</strong> " . htmlspecialchars($row['response']) . "</li>
    <li class='list-group-item'><strong>📅 Last Update:</strong> " . htmlspecialchars($row['updated_at']) . "</li>
  </ul>
  <div class='card-body text-center'>
    <button class='btn btn-outline-danger rounded-pill px-4' id='return' style='font-weight:600;'><i class='fas fa-arrow-left'></i> Return to Dashboard</button>
  </div>
</div>";
                                } else {
                                    echo "<div class='alert alert-warning text-center rounded-3 mt-5'><i class='fas fa-info-circle me-2'></i>No complaint found with this Reference ID.</div> 
                                     <div class='card-body text-center'>
    <button class='btn btn-outline-danger rounded-pill px-4' id='return' style='font-weight:600;'><i class='fas fa-arrow-left'></i> Return to Dashboard</button>
  </div>";
                                }
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const retBtn = document.getElementById('return');
            if (retBtn) {
                retBtn.addEventListener('click', () => {
                    window.location.href = './user-dashboard.php';
                });
            }
        });
    </script>
</body>
</html>