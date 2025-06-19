<?php
include '../includes/admin-header.php';
include '../config/config.php';
include '../includes/admin-nav.php';
include '../auth/admin-auth-check.php';
?>
</head>

<body class='custom-body'>
    <div class="top-head bg-light">
        <div class="brand">
            <img src="../assets/images/general_images/Bjplogo.jpg" alt="Logo">
            Feedback Panel
        </div>
        <div class="">
            Admin, <?= $_SESSION['admin_name'] ?? 'Admin' ?>
        </div>
    </div>
    <div class="container " style=" margin-top: 100px;
  margin-right: 0;
  margin-left: 255px;
  padding-bottom: 85px;">
        <h3 class="mb-4 text-center" style="color:#fd3a69; font-weight:700; letter-spacing:1px; font-family:'Poppins',sans-serif;">
            User Feedback
        </h3>
        <div class="table-responsive" style="width: 95%;">
            <table id="feedbackTable" class="table table-bordered table-hover align-middle" >
                <thead class="table-dark">
                    <tr>
                        <th>Sr No.</th>
                        <th>User Name</th>
                        <th>Phone</th>
                        <th>Feedback</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $today = date('Y-m-d');
                    $result = $conn->query("SELECT * FROM feedback WHERE DATE(created_at) = '$today' ORDER BY created_at DESC");
                    $i = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$i}</td>
                            <td>" . htmlspecialchars($row['user_name']) . "</td>
                            <td class='phone-col'>" . htmlspecialchars($row['user_phone']) . "</td>
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
        "order": [[ 5, "desc" ]],
        "pageLength": 10
    });
});
</script>