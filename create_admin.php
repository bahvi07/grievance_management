
<?php
require_once '../grievance_management/config/config.php'; // Adjust path if needed

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = $_POST['admin_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO admin (admin_id, name, email, phone, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $admin_id, $name, $email, $phone, $password);

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Admin created successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Add New Admin</h4>
        </div>
        <div class="card-body">
            <?php echo $message; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="admin_id" class="form-label">Admin ID</label>
                    <input type="text" class="form-control" id="admin_id" name="admin_id" required>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Create Admin</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>