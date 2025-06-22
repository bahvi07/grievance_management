<?php
include '../includes/admin-init.php';
include '../includes/admin-header.php';
include '../includes/admin-nav.php';
?>
</head>

<body>
<div class="top-head bg-light">
    <div class="brand">
        <img src="<?php echo BASE_URL; ?>assets/images/general_images/Bjplogo.jpg" alt="Logo">
        Edit Admin Details
    </div>
    <div class="">
        Admin, <?= $_SESSION['admin_name'] ?? 'Admin' ?>
    </div>
</div>

<div class="complaint-center row p-2">
    <div class="admin-edit-wrapper">
        <div class="admin-edit-card card shadow p-4">
            <h4 class="mb-3">Admin Details</h4>
            <?php
            $admin_id = $_SESSION['admin_id'] ?? '';
            $sql = "SELECT * FROM admin WHERE admin_id = '$admin_id'";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0):
                $admin = $result->fetch_assoc();
            ?>
                <form action="update-admin.php" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $admin['admin_id'] ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($admin['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" name="phone" id="phone" class="form-control" value="<?= htmlspecialchars($admin['phone'] ?? '') ?>">
                    </div>
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                        Reset Password
                    </button>
                    <button type="submit" class="btn btn-warning">Update Details</button>
                </form>

                <!-- Change Password Modal -->
                <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form id="changePasswordForm">
                                <?= csrf_field() ?>
                                <div class="modal-header">
                                    <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- Hidden email input (from session) -->
                                    <input type="hidden" id="adminEmail" name="email" value="<?= htmlspecialchars($admin['email']) ?>">
                                    <input type="hidden" name="admin_id" id="adminId"  value="<?= $admin['admin_id'] ?>">
                                    <!-- Send Code -->
                                    <div class="mb-3">
                                        <label for="verificationCode" class="form-label">Verification Code</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="verificationCode" name="verificationCode" maxlength="6" placeholder="Enter 6-digit code" disabled>
                                            <button type="button" class="btn btn-warning" id="sendCode">Send Code</button>
                                        </div>
                                        <small class="text-muted">Code will be sent to your email</small>
                                    </div>
                                    <!-- New Password -->
                                    <div class="mb-3 position-relative">
                                        <label for="newPassword" class="form-label">New Password</label>
                                        <div class="input-group">
                                            <input type="password" id="newPassword" name="newPassword" class="form-control" required disabled>
                                            <span class="input-group-text bg-white" style="cursor:pointer;" id="toggleNewPassword">
                                                <i class="fa fa-eye-slash" id="newPasswordIcon"></i>
                                            </span>
                                        </div>
                                        <div id="passwordStrengthContainer" style="margin-top: 4px;">
                                            <div id="passwordStrengthBar" style="height: 6px; width: 0; background: red; border-radius: 3px; transition: width 0.3s, background 0.3s;"></div>
                                            <small id="passwordStrengthText" style="font-weight: bold; display: block; margin-top: 2px;"></small>
                                        </div>
                                    </div>
                                    <!-- Confirm Password -->
                                    <div class="mb-3">
                                        <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required disabled>
                                            <span class="input-group-text bg-white" style="cursor:pointer;" id="toggleConfirmPassword">
                                                <i class="fa fa-eye-slash" id="confirmPasswordIcon"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary" id="reset_pswd" disabled>Change Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action="update-admin.php"]');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            try {
                const response = await fetch('update-admin.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                Swal.fire({
                    icon: result.success ? 'success' : 'error',
                    title: result.success ? 'Success' : 'Error',
                    text: result.message
                }).then(() => {
                    if (result.success) {
                        window.location.reload();
                    }
                });
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.'
                });
            }
        });
    }
});
</script>

<?php include '../includes/admin-footer.php'; ?>
