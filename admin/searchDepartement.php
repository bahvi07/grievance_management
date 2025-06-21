<?php
require_once '../config/session-config.php';
startSecureSession();

require_once '../config/config.php';
include '../auth/admin-auth-check.php';

// Verify CSRF token
if (!CSRFProtection::verifyPostToken()) {
    echo "<div class='text-danger'>Security validation failed. Please refresh the page and try again.</div>";
    exit;
}

$area = $_POST['area'] ?? '';
$category = $_POST['category'] ?? '';
$refid = $_POST['refid'] ?? '';

// Get the complaint details first to ensure we have the correct image
$complaintStmt = $conn->prepare("SELECT * FROM complaints WHERE refid = ?");
$complaintStmt->bind_param("s", $refid);
$complaintStmt->execute();
$complaintResult = $complaintStmt->get_result();
$complaintData = $complaintResult->fetch_assoc();

if (!$complaintData) {
    echo "<div class='text-danger'>Complaint not found.</div>";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM departments WHERE area LIKE ? AND category = ?");
$likeArea = "%$area%";
$stmt->bind_param("ss", $likeArea, $category);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    echo "<div class='card p-2 mb-2'>
            <strong>{$row['name']}</strong> ({$row['email']})<br>
            <small><b>Area:</b> {$row['area']}</small><br>
          <form method='POST' action='../mail_api/forward-mail.php' class='d-inline forwardForm'>"
            . csrf_field() .
            "<input type='hidden' name='refid' value='" . htmlspecialchars($refid) . "'>
            <input type='hidden' name='dept_email' value='" . htmlspecialchars($row['email']) . "'>
            <input type='hidden' name='name' value='" . htmlspecialchars($complaintData['name'] ?? '') . "'>
            <input type='hidden' name='email' value='" . htmlspecialchars($complaintData['email'] ?? '') . "'>
            <input type='hidden' name='phone' value='" . htmlspecialchars($complaintData['phone'] ?? '') . "'>
            <input type='hidden' name='location' value='" . htmlspecialchars($complaintData['location'] ?? '') . "'>
            <input type='hidden' name='description' value='" . htmlspecialchars($complaintData['complaint'] ?? '') . "'>
            <input type='hidden' name='image' value='" . htmlspecialchars(basename($complaintData['image'] ?? '')) . "'>
            <button type='submit' class='btn btn-sm btn-success mt-2'>Forward</button>
          </form>
        </div>";
  }
} else {
  echo "<div class='text-muted'>No matching department found.</div>";
}
?>

<script>
$(document).ready(function() {
  $('.forwardForm').on('submit', function(e) {
    var $form = $(this);
    var $btn = $form.find('button[type="submit"]');
    $btn.prop('disabled', true);
    var originalText = $btn.html();
    $btn.html('<span class="spinner-border spinner-border-sm"></span> Forwarding...');

    // Let the form submit normally (page reloads or modal closes on success)
    // If you want to handle via AJAX, preventDefault and handle response here

    // If you want to re-enable/reset the button after a delay (for demo):
    // setTimeout(function() {
    //   $btn.prop('disabled', false);
    //   $btn.html(originalText);
    // }, 3000);
  });
});
</script>